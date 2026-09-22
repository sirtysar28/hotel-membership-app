<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Email otomatis: welcome, upgrade level, konfirmasi transaksi, renewal & expiration reminder.
 * Semua email dicatat ke email_logs (audit) dan dikirim via mailer aktif (default: log).
 */
class EmailService
{
    public function sendWelcome(Member $member): void
    {
        $this->send(
            member: $member,
            type: 'welcome',
            subject: 'Welcome to Hotel Ciputra Membership',
            body: view('emails.welcome', ['member' => $member])->render(),
        );
    }

    public function sendLevelUpgrade(Member $member, $oldLevel, $newLevel): void
    {
        $this->send(
            member: $member,
            type: 'upgrade',
            subject: 'Congratulations – Membership Upgrade',
            body: view('emails.upgrade', ['member' => $member, 'oldLevel' => $oldLevel, 'newLevel' => $newLevel])->render(),
        );
    }

    public function sendTransactionConfirmation(Member $member, Transaction $transaction): void
    {
        $this->send(
            member: $member,
            type: 'transaction',
            subject: 'Visit / Transaction Confirmation – ' . $transaction->transaction_no,
            body: view('emails.transaction', ['member' => $member, 'transaction' => $transaction])->render(),
        );
    }

    public function sendRenewalReminder(Member $member): void
    {
        $this->send(
            member: $member,
            type: 'renewal',
            subject: 'Membership Renewal Reminder',
            body: view('emails.renewal', ['member' => $member])->render(),
        );
    }

    /** Update #13/#23 — reminder perpanjangan sesuai paket (3 bulan / 1 bulan / 2 minggu sebelum berakhir). */
    public function sendExpirationReminder(Member $member, ?string $milestone = null, ?int $daysLeft = null): void
    {
        $milestoneLabel = match ($milestone) {
            '3_bulan' => '3 Bulan',
            '1_bulan' => '1 Bulan',
            '2_minggu' => '2 Minggu',
            default => '',
        };

        $this->send(
            member: $member,
            type: 'expiration',
            subject: ($milestone ? "[{$milestone}] " : '') . 'Membership Akan Segera Berakhir — CPC',
            body: view('emails.expiration', ['member' => $member, 'milestone' => $milestoneLabel, 'daysLeft' => $daysLeft])->render(),
        );
    }

    public function sendPaymentVerified(Member $member, $payment): void
    {
        $this->send(
            member: $member,
            type: 'payment',
            subject: 'Payment Verified – Membership Activated',
            body: view('emails.payment', ['member' => $member, 'payment' => $payment])->render(),
        );
    }

    /*
    |------------------------------------------------------------------
    | CPC v2.0 — email tambahan
    |------------------------------------------------------------------
    */

    /** Update #20 — OTP registrasi (anti-spambot). */
    public function sendOtp(string $email, \App\Models\OtpCode $otp): void
    {
        $subject = 'Kode OTP Verifikasi Registrasi — CPC';
        $body = view('emails.otp', ['email' => $email, 'otp' => $otp])->render();

        $log = EmailLog::create([
            'member_id' => null,
            'to_email' => $email,
            'type' => 'otp',
            'subject' => $subject,
            'body' => $body,
            'status' => true,
            'sent_at' => now(),
        ]);

        try {
            Mail::html($body, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
        } catch (\Throwable $e) {
            $log->update(['status' => false]);
            report($e);
        }
    }

    /** Update #9 — notifikasi keamanan (akun/registrasi baru) ke email member. */
    public function sendSecurityAlert(Member $member, string $event = 'registration'): void
    {
        $this->send(
            member: $member,
            type: 'security_alert',
            subject: 'Security Alert — Aktivitas Akun CPC Anda',
            body: view('emails.security-alert', ['member' => $member, 'event' => $event])->render(),
        );
    }

    /** CPC §14 — notifikasi penukaran voucer SETELAH disetujui manajer. */
    public function sendRedemptionApproved(Member $member, \App\Models\Voucher $voucher, \App\Models\RedemptionRequest $request, User $manager): void
    {
        $this->send(
            member: $member,
            type: 'voucher_redeemed',
            subject: 'Voucher Redeemed — ' . $voucher->voucher_no,
            body: view('emails.voucher-redeemed', [
                'member' => $member, 'voucher' => $voucher, 'request' => $request, 'manager' => $manager,
            ])->render(),
        );
    }

    /** Email paket voucer diterbitkan (12 voucer). */
    public function sendVouchersIssued(Member $member, array $vouchers, $period = null): void
    {
        $this->send(
            member: $member,
            type: 'vouchers_issued',
            subject: 'Paket Voucer CPC Anda Telah Diterbitkan',
            body: view('emails.vouchers-issued', ['member' => $member, 'vouchers' => $vouchers, 'period' => $period])->render(),
        );
    }

    /** §8 — notifikasi turun tier karena ketidakaktifan. */
    public function sendTierDowngraded(Member $member, $oldLevel, $newLevel): void
    {
        $this->send(
            member: $member,
            type: 'tier_downgraded',
            subject: 'Perubahan Tingkat Keanggotaan — ' . $newLevel->name,
            body: view('emails.tier-downgraded', ['member' => $member, 'oldLevel' => $oldLevel, 'newLevel' => $newLevel])->render(),
        );
    }

    /** §7 — Diamond kedaluwarsa → konversi Signature. */
    public function sendDiamondExpired(Member $member): void
    {
        $this->send(
            member: $member,
            type: 'diamond_expired',
            subject: 'Membership Diamond Berakhir — Konversi ke Ciputra Signature',
            body: view('emails.diamond-expired', ['member' => $member])->render(),
        );
    }

    /** Update #18 — akun staff/manajer disetujui. */
    public function sendAccountApproved(User $user, User $approver): void
    {
        $subject = 'Akun Anda Disetujui — CPC Membership';
        $body = view('emails.account-approved', ['user' => $user, 'approver' => $approver])->render();

        $log = EmailLog::create([
            'member_id' => $user->member_id,
            'to_email' => $user->email,
            'type' => 'account_approved',
            'subject' => $subject,
            'body' => $body,
            'status' => true,
            'sent_at' => now(),
        ]);

        try {
            Mail::html($body, function ($message) use ($user, $subject) {
                $message->to($user->email, $user->name)->subject($subject);
            });
        } catch (\Throwable $e) {
            $log->update(['status' => false]);
            report($e);
        }
    }

    /**
     * Email reset password (lupa password) — untuk user admin/staff/member portal.
     */
    public function sendPasswordReset(User $user, string $resetUrl, int $expiresMinutes): void
    {
        $subject = 'Reset Password — Hotel Ciputra Membership';
        $body = view('emails.forgot-password', [
            'user' => $user,
            'resetUrl' => $resetUrl,
            'expires' => $expiresMinutes,
        ])->render();

        $log = EmailLog::create([
            'member_id' => $user->member_id,
            'to_email' => $user->email,
            'type' => 'password_reset',
            'subject' => $subject,
            'body' => $body,
            'status' => true,
            'sent_at' => now(),
        ]);

        try {
            Mail::html($body, function ($message) use ($user, $subject) {
                $message->to($user->email, $user->name)->subject($subject);
            });
        } catch (\Throwable $e) {
            $log->update(['status' => false]);
            throw $e;
        }
    }

    /**
     * Email percobaan dari halaman Settings (test SMTP).
     */
    public function sendTest(string $toEmail, string $toName): void
    {
        $subject = 'SMTP Test — Hotel Ciputra Membership';
        $body = view('emails.test', [
            'recipientName' => $toName,
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'encryption' => config('mail.mailers.smtp.encryption'),
        ])->render();

        Mail::html($body, function ($message) use ($toEmail, $subject) {
            $message->to($toEmail)->subject($subject);
        });
    }

    private function send(Member $member, string $type, string $subject, string $body): void
    {
        $log = EmailLog::create([
            'member_id' => $member->id,
            'to_email' => $member->email,
            'type' => $type,
            'subject' => $subject,
            'body' => $body,
            'status' => true,
            'sent_at' => now(),
        ]);

        try {
            Mail::html($body, function ($message) use ($member, $subject) {
                $message->to($member->email, $member->full_name)->subject($subject);
            });
        } catch (\Throwable $e) {
            $log->update(['status' => false]);
            report($e);
        }
    }
}
