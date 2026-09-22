<?php

namespace App\Services;

use App\Models\DigitalCard;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\MembershipPeriod;
use App\Models\Payment;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MembershipService
{
    public function generateMemberNo(): string
    {
        $prefix = Setting::get('member_no_prefix', 'HCM');

        $max = Member::withTrashed()
            ->where('member_no', 'like', $prefix . '-%')
            ->selectRaw("MAX(CAST(SUBSTRING(member_no, " . (strlen($prefix) + 2) . ") AS UNSIGNED)) as max_no")
            ->value('max_no') ?? 0;

        return sprintf('%s-%06d', $prefix, $max + 1);
    }

    public function generateTransactionNo(string $type): string
    {
        $map = [
            'hotel_stay' => 'HTL',
            'restaurant' => 'RST',
            'bar' => 'BAR',
            'banquet' => 'BQT',
            'other_fnb' => 'FNB',
            'other' => 'OTH',
        ];
        $prefix = $map[$type] ?? 'TRX';

        $count = \App\Models\Transaction::whereDate('created_at', today())->count() + 1;

        return sprintf('%s-%s-%03d', $prefix, now()->format('Ymd'), $count);
    }

    /**
     * Registrasi member baru (paid / free) — CPC v2.0 §19 + update #17.
     *
     * FREE : langsung aktif di level Classic + periode 1 thn + paket 12 voucer.
     * PAID : status pending_payment + invoice + payment pending;
     *        Diamond aktif saat payment diverifikasi (start = tgl pembayaran, §3).
     */
    public function registerMember(array $data): Member
    {
        return DB::transaction(function () use ($data) {
            $classic = \App\Models\MembershipLevel::where('code', 'classic')->first();

            $isPaid = ($data['membership_type'] ?? 'free') === 'paid';

            $member = Member::create([
                ...$this->personalFields($data),
                'member_no' => $this->generateMemberNo(),
                'hotel_id' => $data['hotel_id'],
                'membership_type' => $isPaid ? 'paid' : 'free',
                'level_id' => $classic?->id ?? \App\Models\MembershipLevel::ordered()->value('id'),
                'status' => $isPaid ? 'pending_payment' : 'active',
                'joined_at' => now()->toDateString(),
                'valid_until' => null, // diatur saat aktivasi / periode dibuat
                'last_activity_date' => now()->toDateString(),
                'created_by' => auth()->id(),
            ]);

            if ($isPaid) {
                $this->createPaymentPipeline($member);
                // Update #9 — security notification ke email member saat pendaftaran paid
                app(EmailService::class)->sendSecurityAlert($member, 'registration_received');
                \App\Models\AuditLog::record('member_registered_paid', 'Member', $member->id,
                    "Registrasi DIAMOND (menunggu pembayaran): {$member->member_no} - {$member->full_name}");
            } else {
                $this->activate($member);
                \App\Models\AuditLog::record('member_registered_free', 'Member', $member->id,
                    "Registrasi FREE: {$member->member_no} - {$member->full_name}");
            }

            return $member;
        });
    }

    private function personalFields(array $data): array
    {
        return collect($data)->only([
            'full_name', 'email', 'phone', 'phone_country_code', 'dob', 'gender', 'address',
            'id_number', 'id_type', 'company', 'occupation', 'proof_reference',
        ])->all();
    }

    /** Invoice + payment pending utk membership paid */
    public function createPaymentPipeline(Member $member): Payment
    {
        $amount = (float) Setting::get('paid_membership_price', 2200000);

        $invoice = Invoice::create([
            'invoice_no' => 'INV-' . now()->format('Ymd') . '-' . str_pad((string) (Invoice::count() + 1), 4, '0', STR_PAD_LEFT),
            'member_id' => $member->id,
            'amount' => $amount,
            'description' => 'Paid Membership - ' . Setting::get('membership_validity', '1 tahun'),
            'status' => 'unpaid',
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        return Payment::create([
            'invoice_id' => $invoice->id,
            'member_id' => $member->id,
            'amount' => $amount,
            'status' => 'pending',
            'method' => 'transfer',
        ]);
    }

    /**
     * Aktivasi member — CPC v2.0 §3, §19, update #17:
     * - FREE  : aktif di level gratis saat ini, periode 1 tahun, paket voucer registrasi.
     * - PAID  : level DIAMOND, periode mulai hari ini (tanggal pembayaran berhasil),
     *           kedaluwarsa = start + masa berlaku, paket 12 voucer Diamond.
     */
    public function activate(Member $member): void
    {
        DB::transaction(function () use ($member) {
            $isPaid = $member->isPaid();

            if ($isPaid) {
                $diamond = \App\Models\MembershipLevel::where('code', 'diamond')->first();
                if ($diamond) {
                    $member->update(['level_id' => $diamond->id]);
                }
            }

            $months = (int) Setting::get('membership_validity_months', 12);

            $member->update([
                'status' => 'active',
                'joined_at' => $member->joined_at ?? now()->toDateString(),
            ]);

            $period = $this->createPeriod($member, now()->toDateString(), $months);
            $member->update(['valid_until' => $period->end_date->toDateString()]);

            // Terbitkan paket voucer (paid → paket Diamond; free → paket registrasi)
            $issued = app(VoucherService::class)->issuePackage($member->refresh(), $period, $isPaid ? 'diamond_payment' : 'registration');

            $this->issueCard($member->refresh(), $isPaid ? 'Aktivasi DIAMOND' : 'Aktivasi membership');
            $this->ensurePortalUser($member);
            app(EmailService::class)->sendWelcome($member->refresh());

            // Notifikasi paket voucer diterbitkan (jika ada)
            if (count($issued) > 0) {
                app(EmailService::class)->sendVouchersIssued($member->refresh(), $issued, $period);
            }

            // Update #9 — notifikasi keamanan (security alert) ke email member
            app(EmailService::class)->sendSecurityAlert($member->refresh(), 'membership_activated');

            \App\Models\AuditLog::record('member_activated', 'Member', $member->id,
                "Member {$member->member_no} activated" . ($isPaid ? ' sebagai DIAMOND' : ' (FREE)') . " — periode {$period->label()}");
        });
    }

    /**
     * CPC v2.0 §3 — buat periode keanggotaan baru.
     * Contoh: bayar 22 Sep 2026 → start 22 Sep 2026 → end 21 Sep 2027.
     */
    public function createPeriod(Member $member, string $startDate, ?int $months = null, ?string $paymentReference = null): MembershipPeriod
    {
        $months ??= (int) Setting::get('membership_validity_months', 12);
        $price = $member->isPaid()
            ? (float) Setting::get('diamond_membership_price', (float) Setting::get('paid_membership_price', 2200000))
            : 0.0;

        $periodNo = ((int) $member->periods()->max('period_no')) + 1;

        return \App\Models\MembershipPeriod::create([
            'member_id' => $member->id,
            'period_no' => $periodNo,
            'membership_type' => $member->membership_type,
            'price' => $price,
            'start_date' => $startDate,
            'end_date' => \Illuminate\Support\Carbon::parse($startDate)->addMonths($months)->subDay()->toDateString(),
            'status' => 'active',
            'payment_reference' => $paymentReference,
        ]);
    }

    /**
     * CPC v2.0 §15 — Perpanjangan Diamond: pembayaran baru → periode baru +
     * paket voucer baru (ID Voucer unik baru). Voucer lama TETAP mempertahankan
     * masa kedaluwarsa aslinya (tidak diperpanjang otomatis).
     */
    public function renewDiamond(Member $member, ?\App\Models\Payment $payment = null): MembershipPeriod
    {
        return DB::transaction(function () use ($member, $payment) {
            // Tutup periode aktif lama (dipertahankan sebagai riwayat — tidak ditimpa)
            $member->periods()->where('status', 'active')->update([
                'status' => now()->gte($member->valid_until) ? 'expired' : 'cancelled',
                'expired_at' => now(),
            ]);

            $months = (int) Setting::get('membership_validity_months', 12);
            $period = $this->createPeriod($member, now()->toDateString(), $months, $payment?->reference_no);

            $member->update([
                'status' => 'active',
                'valid_until' => $period->end_date->toDateString(),
            ]);

            $issued = app(VoucherService::class)->issuePackage($member->refresh(), $period, 'renewal');
            if (count($issued) > 0) {
                app(EmailService::class)->sendVouchersIssued($member->refresh(), $issued, $period);
            }

            \App\Models\AuditLog::record('diamond_renewed', 'Member', $member->id,
                "Perpanjangan DIAMOND {$member->member_no} — periode #{$period->period_no} ({$period->start_date->format('d M Y')} s/d {$period->end_date->format('d M Y')})");

            return $period;
        });
    }

    /**
     * CPC v2.0 §7 — Diamond tidak diperpanjang saat akhir periode:
     * Diamond EXPIRED → konversi ke keanggotaan gratis di tingkat SIGNATURE.
     */
    public function expireDiamond(Member $member): Member
    {
        return DB::transaction(function () use ($member) {
            $signature = \App\Models\MembershipLevel::where('code', 'signature')->first();

            $member->periods()->where('status', 'active')->update([
                'status' => 'expired',
                'expired_at' => now(),
            ]);

            $member->update([
                'membership_type' => 'free',
                'level_id' => $signature?->id ?? $member->level_id,
                'status' => 'active',
                'tier_downgraded_at' => now(), // reset penghitung ketidakaktifan setelah konversi
            ]);

            $member->refresh();
            $this->issueCard($member, 'Konversi Diamond EXPIRED → Signature');
            app(EmailService::class)->sendDiamondExpired($member);

            \App\Models\AuditLog::record('diamond_expired', 'Member', $member->id,
                "Membership DIAMOND {$member->member_no} kedaluwarsa — dikonversi ke SIGNATURE (aturan tier gratis berlaku selanjutnya)");

            return $member;
        });
    }

    /** Buat / regenerasi kartu digital dengan QR token */
    public function issueCard(Member $member, string $note = ''): DigitalCard
    {
        $member->cards()->update(['is_active' => false]);

        $card = DigitalCard::create([
            'member_id' => $member->id,
            'card_no' => $member->member_no,
            'token' => (string) Str::uuid(),
            'level_name' => $member->level->name,
            'valid_until' => $member->valid_until,
            'is_active' => true,
            'issued_at' => now(),
        ]);

        if ($note) {
            \App\Models\AuditLog::record('card_issued', 'DigitalCard', $card->id,
                "Kartu digital {$card->card_no} ({$card->level_name}) - {$note}");
        }

        $member->refresh();

        return $card;
    }

    /** Akun portal untuk member (role: member) */
    public function ensurePortalUser(Member $member, ?string $password = null): User
    {
        $password ??= Str::random(10);

        $user = User::where('member_id', $member->id)->first();

        if ($user) {
            return $user;
        }

        return User::create([
            'name' => $member->full_name,
            'email' => $this->uniquePortalEmail($member),
            'password' => $password,
            'role' => 'member',
            'member_id' => $member->id,
        ]);
    }

    private function uniquePortalEmail(Member $member): string
    {
        $email = $member->email;
        $i = 1;
        while (User::where('email', $email)->exists()) {
            $email = preg_replace('/@.*$/', '', $member->email) . $i . '@members.hotelciputra.local';
            $i++;
        }

        return $email;
    }

    /**
     * Record visit/transaction oleh staff:
     * simpan transaksi + visit, update total, jalankan level engine, kirim email konfirmasi.
     */
    public function redeemVisit(Member $member, array $data, User $staff): array
    {
        $result = DB::transaction(function () use ($member, $data, $staff) {
            $before = [
                'total_visits' => $member->total_visits,
                'total_spending' => $member->total_spending,
                'level' => $member->level->name,
            ];

            $transaction = \App\Models\Transaction::create([
                'transaction_no' => $data['transaction_no'] ?? null ?: $this->generateTransactionNo($data['type']),
                'member_id' => $member->id,
                'hotel_id' => $data['hotel_id'],
                'type' => $data['type'],
                'outlet' => $data['outlet'] ?? null,
                'amount' => (float) $data['amount'],
                'transaction_date' => $data['visit_date'],
                'counts_as_visit' => $data['counts_as_visit'] ?? true,
                'notes' => $data['notes'] ?? null,
                'staff_id' => $staff->id,
            ]);

            $visit = null;
            if ($transaction->counts_as_visit) {
                $visit = \App\Models\Visit::create([
                    'member_id' => $member->id,
                    'hotel_id' => $data['hotel_id'],
                    'transaction_id' => $transaction->id,
                    'visit_date' => $data['visit_date'],
                    'staff_id' => $staff->id,
                ]);

                \App\Models\VisitRedemption::create([
                    'visit_id' => $visit->id,
                    'member_id' => $member->id,
                    'staff_id' => $staff->id,
                    'data_before' => $before,
                    'data_after' => null, // diisi setelah update total
                    'ip_address' => request()?->ip(),
                ]);
            }

            $member->update([
                'total_visits' => $member->total_visits + ($transaction->counts_as_visit ? 1 : 0),
                'total_spending' => $member->total_spending + $transaction->amount,
                // CPC §8 — aktivitas memenuhi syarat mengatur ulang periode ketidakaktifan
                'last_activity_date' => max($member->last_activity_date?->toDateString() ?? '', $transaction->transaction_date->toDateString()) ?: $transaction->transaction_date->toDateString(),
                'tier_downgraded_at' => null, // ada aktivitas → reset penanda downgrade
            ]);

            $member->refresh();

            if ($visit) {
                $visit->redemption->update([
                    'data_after' => [
                        'total_visits' => $member->total_visits,
                        'total_spending' => $member->total_spending,
                        'level' => $member->level->name,
                    ],
                ]);
            }

            app(EmailService::class)->sendTransactionConfirmation($member, $transaction);

            $newLevel = app(LevelEngine::class)->processUpgrade($member);

            \App\Models\AuditLog::record('visit_redeemed', 'Transaction', $transaction->id,
                "Redeem visit {$member->member_no}: {$transaction->transaction_no} Rp" . number_format($transaction->amount, 0, ',', '.'));

            return [
                'transaction' => $transaction,
                'visit' => $visit,
                'upgraded_to' => $newLevel,
            ];
        });

        return $result;
    }
}
