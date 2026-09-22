<?php

namespace App\Console\Commands;

use App\Models\EmailLog;
use App\Models\Member;
use App\Services\EmailService;
use App\Services\LevelEngine;
use App\Services\VoucherService;
use Illuminate\Console\Command;

/**
 * CPC v2.0 — scheduler harian:
 *  1. Voucer kedaluwarsa otomatis di akhir periode (§6)
 *  2. Diamond tidak diperpanjang → EXPIRED → konversi Signature (§7)
 *  3. Tier gratis turun satu level per 12 bulan tanpa aktivitas (§8)
 *  4. Reminder perpanjangan 3 bulan / 1 bulan sebelum berakhir (update #13/#23)
 */
class ProcessCpcSchedules extends Command
{
    protected $signature = 'cpc:process {--dry : Jalankan tanpa mengubah data}';

    protected $description = 'Proses otomatisasi CPC: expire vouchers, expire Diamond, downgrade inaktif, reminder perpanjangan';

    public function handle(
        VoucherService $vouchers,
        LevelEngine $engine,
        EmailService $email,
    ): int {
        $dry = (bool) $this->option('dry');

        // 1. Expire voucer yang masa berlakunya terlewat
        if (! $dry) {
            $expired = $vouchers->expireDueVouchers();
        } else {
            $expired = \App\Models\Voucher::whereIn('status', ['AVAILABLE', 'PENDING_APPROVAL'])
                ->whereDate('expires_at', '<', today())->count();
        }
        $this->info("[1/4] Voucer EXPIRED otomatis : {$expired}");

        // 2. Diamond kedaluwarsa → Signature
        $diamondExpired = $dry
            ? Member::where('membership_type', 'paid')->where('status', 'active')
                ->whereDate('valid_until', '<', today())->count()
            : $engine->expireDueDiamondMembers();
        $this->info("[2/4] Diamond EXPIRED → Signature : {$diamondExpired}");

        // 3. Downgrade tier gratis (12 bulan tanpa aktivitas)
        $downgraded = $dry ? null : $engine->downgradeInactiveMembers();
        $this->info("[3/4] Tier diturunkan (inaktif 12 bln) : " . ($downgraded ?? '(dry)'));

        // 4. Reminder perpanjangan (3 bulan & 1 bulan & 2 minggu sebelum berakhir)
        $reminderMonths = (int) \App\Models\Setting::get('expiry_reminder_months', 3);
        $sent = 0;

        if (! $dry) {
            Member::query()
                ->where('status', 'active')
                ->whereNotNull('valid_until')
                ->whereBetween('valid_until', [today(), today()->addMonths($reminderMonths)])
                ->chunk(100, function ($members) use ($email, &$sent) {
                    foreach ($members as $member) {
                        $daysLeft = today()->diffInDays($member->valid_until, false);

                        // Kirim pada titik: ~90 hari, ~30 hari, ~14 hari (hindari duplikat via email_logs)
                        $milestone = match (true) {
                            $daysLeft > 85 && $daysLeft <= 90 => '3_bulan',
                            $daysLeft > 25 && $daysLeft <= 30 => '1_bulan',
                            $daysLeft > 10 && $daysLeft <= 14 => '2_minggu',
                            default => null,
                        };

                        if (! $milestone) {
                            continue;
                        }

                        $already = EmailLog::where('member_id', $member->id)
                            ->where('type', 'expiration')
                            ->where('subject', 'like', "%[{$milestone}]%")
                            ->exists();

                        if ($already) {
                            continue;
                        }

                        $email->sendExpirationReminder($member, $milestone, $daysLeft);
                        $sent++;
                    }
                });
        }
        $this->info("[4/4] Reminder perpanjangan terkirim : {$sent}");

        $this->info($dry ? '(DRY RUN — tidak ada data yang diubah)' : 'Selesai.');

        return self::SUCCESS;
    }
}
