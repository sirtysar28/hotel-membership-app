<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Transaction;

/**
 * Membership Rule Engine — CPC v2.0 §8, §9
 * -----------------------------------------
 * Level TIDAK hard-code; threshold dibaca dari membership_rules (configurable):
 *   Classic   : min_visit 0,  max_visit 25
 *   Privilege : min_visit 26, max_visit 50
 *   Signature : min_visit 51, max_visit null (unlimited)
 *   Diamond   : TIDAK via rule — hanya lewat pembayaran (§2).
 *
 * Upgrade  : hanya level gratis, berdasar kunjungan/transaksi memenuhi syarat.
 * Downgrade : satu level per 12 bulan tanpa aktivitas valid (Signature→Privilege→Classic;
 *             Classic tetap Classic) — dijalankan scheduler harian.
 */
class LevelEngine
{
    /**
     * Evaluasi level member berdasarkan rule aktif.
     * Return level baru jika naik, null jika tetap.
     */
    public function evaluate(Member $member): ?object
    {
        $qualified = $this->qualifiedLevel($member);

        if (! $qualified) {
            return null;
        }

        if ($qualified->level_id !== $member->level_id) {
            return $qualified->level;
        }

        return null;
    }

    /**
     * Level tertinggi yang saat ini dipenuhi member (hanya tier gratis).
     */
    public function qualifiedLevel(Member $member): ?object
    {
        $rules = \App\Models\MembershipRule::with('level')
            ->where('is_active', true)
            ->whereHas('level', fn ($q) => $q->where('is_active', true)->where('code', '!=', 'diamond'))
            ->get()
            ->sortBy(fn ($rule) => $rule->level->sort_order);

        $best = null;
        foreach ($rules as $rule) {
            $visits = $this->visitCountForRule($member, $rule);

            // §9 — rentang min_visit..max_visit memenuhi syarat
            if ($visits >= $rule->min_visit && ($rule->max_visit === null || $visits <= $rule->max_visit)) {
                $best = $rule;
            }
        }

        return $best;
    }

    /** Jumlah visit sesuai periode evaluasi rule (0 = lifetime) */
    public function visitCountForRule(Member $member, $rule): int
    {
        if ((int) $rule->evaluation_period_months <= 0) {
            return $member->total_visits;
        }

        $since = now()->subMonths((int) $rule->evaluation_period_months);

        return $member->visits()->where('visit_date', '>=', $since->toDateString())->count();
    }

    /** Total eligible spending sesuai periode evaluasi rule (0 = lifetime) */
    public function spendingForRule(Member $member, $rule): float
    {
        if ((int) $rule->evaluation_period_months <= 0) {
            return (float) $member->total_spending;
        }

        $since = now()->subMonths((int) $rule->evaluation_period_months);

        return (float) $member->transactions()
            ->where('transaction_date', '>=', $since->toDateString())
            ->sum('amount');
    }

    /** Proses upgrade level + kartu baru + email notifikasi. Return level baru atau null.
     *  Hanya utk member gratis — Diamond tidak bisa "di-upgrade" oleh kunjungan (§2). */
    public function processUpgrade(Member $member): ?object
    {
        if ($member->isDiamond() || $member->isPaid()) {
            return null;
        }

        $qualified = $this->qualifiedLevel($member);

        if (! $qualified || $qualified->level_id === $member->level_id) {
            return null;
        }

        // Hanya NAIK (update #4/#16: member lama bisa naik ke level atas), tidak turun via evaluasi
        if ($qualified->level->sort_order <= $member->level->sort_order) {
            return null;
        }

        $newLevel = $qualified->level;
        $oldLevel = $member->level;

        $member->update(['level_id' => $newLevel->id]);
        $member->refresh();

        app(MembershipService::class)->issueCard($member, "Upgrade level {$oldLevel->name} -> {$newLevel->name}");
        app(EmailService::class)->sendLevelUpgrade($member, $oldLevel, $newLevel);

        \App\Models\AuditLog::record('member_level_upgraded', 'Member', $member->id,
            "Upgrade level: {$oldLevel->name} -> {$newLevel->name} ({$member->total_visits} kunjungan memenuhi syarat)");

        return $newLevel;
    }

    /**
     * CPC §8 + update #5/#6 — Downgrade ketidakaktifan tier gratis:
     * tidak ada aktivitas valid 12 bulan berturut-turut → turun SATU level per 12 bulan.
     * (Signature → Privilege → Classic; Classic tetap Classic.)
     * Dipanggil scheduler harian. Return jumlah member yang diturunkan.
     */
    public function downgradeInactiveMembers(): int
    {
        $thresholdMonths = (int) \App\Models\Setting::get('inactivity_threshold_months', 12);
        $downgraded = 0;

        $freeLevels = \App\Models\MembershipLevel::where('code', '!=', 'diamond')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        Member::query()
            ->where('membership_type', 'free')
            ->where('status', 'active')
            ->chunk(200, function ($members) use ($freeLevels, $thresholdMonths, &$downgraded) {
                foreach ($members as $member) {
                    // Penghitung: sejak downgrade terakhir ATAU sejak aktivitas terakhir
                    $reference = $member->tier_downgraded_at ?? $member->inactivityReferenceDate();
                    $monthsIdle = $reference->diffInMonths(now());

                    if ($monthsIdle < $thresholdMonths) {
                        continue;
                    }

                    // Cari level di bawahnya
                    $currentSort = $member->level->sort_order;
                    $lower = $freeLevels->where('sort_order', '<', $currentSort)
                        ->sortByDesc('sort_order')->first();

                    if (! $lower || $lower->id === $member->level_id) {
                        continue; // Classic tetap Classic
                    }

                    $old = $member->level;
                    $member->update([
                        'level_id' => $lower->id,
                        'tier_downgraded_at' => now(),
                    ]);
                    $member->refresh();

                    app(MembershipService::class)->issueCard($member, "Downgrade inaktif {$old->name} -> {$lower->name}");
                    app(EmailService::class)->sendTierDowngraded($member, $old, $lower);

                    \App\Models\AuditLog::record('member_level_downgraded', 'Member', $member->id,
                        "Downgrade ketidakaktifan ({$monthsIdle} bln tanpa aktivitas valid): {$old->name} -> {$lower->name}");

                    $downgraded++;
                }
            });

        return $downgraded;
    }

    /**
     * CPC §7 — Kedaluwarsa Diamond (dipanggil scheduler harian):
     * valid_until terlewat & tidak ada perpanjangan → EXPIRED → konversi ke Signature.
     */
    public function expireDueDiamondMembers(): int
    {
        $count = 0;

        Member::query()
            ->where('membership_type', 'paid')
            ->where('status', 'active')
            ->whereDate('valid_until', '<', today())
            ->chunk(100, function ($members) use (&$count) {
                foreach ($members as $member) {
                    app(MembershipService::class)->expireDiamond($member);
                    $count++;
                }
            });

        return $count;
    }

    /** Progres menuju level berikutnya utk member portal */
    public function progressToNextLevel(Member $member): array
    {
        $current = $member->level;
        $next = \App\Models\MembershipLevel::where('is_active', true)
            ->where('sort_order', '>', $current->sort_order)
            ->orderBy('sort_order')
            ->first();

        if (! $next) {
            return ['next' => null, 'percent' => 100];
        }

        $rule = $next->activeRule;

        $percent = 0;
        if ($rule) {
            if ($rule->min_visit > 0 && $rule->min_spending > 0) {
                $pVisit = min(100, $member->total_visits / $rule->min_visit * 100);
                $pSpend = min(100, (float) $member->total_spending / (float) $rule->min_spending * 100);
                $percent = round(min($pVisit, $pSpend));
            } elseif ($rule->min_visit > 0) {
                $percent = round(min(100, $member->total_visits / $rule->min_visit * 100));
            } elseif ($rule->min_spending > 0) {
                $percent = round(min(100, (float) $member->total_spending / (float) $rule->min_spending * 100));
            }
        }

        return ['next' => $next, 'rule' => $rule, 'percent' => (int) $percent];
    }
}
