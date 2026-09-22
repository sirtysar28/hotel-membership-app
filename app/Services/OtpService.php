<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Mail;

/**
 * Update #20/#22 — OTP anti-spambot saat registrasi awal.
 * Kode 6 digit, berlaku 10 menit, maks 5 percobaan, resend throttle 60 detik.
 */
class OtpService
{
    public const TTL_MINUTES = 10;
    public const MAX_ATTEMPTS = 5;
    public const RESEND_SECONDS = 60;

    public function __construct(private EmailService $email) {}

    /** Kirim OTP ke email (registration). */
    public function send(string $email, string $purpose = 'registration'): OtpCode
    {
        $email = strtolower(trim($email));

        // Throttle resend
        $last = OtpCode::where('email', $email)->where('purpose', $purpose)->latest()->first();
        if ($last && ! $last->isConsumed() && $last->created_at->diffInSeconds(now()) < self::RESEND_SECONDS) {
            throw new \DomainException('Kode OTP baru dapat diminta ' . (self::RESEND_SECONDS - $last->created_at->diffInSeconds(now())) . ' detik lagi.');
        }

        $code = (string) random_int(100000, 999999);

        $otp = OtpCode::create([
            'email' => $email,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);

        $this->email->sendOtp($email, $otp);

        return $otp;
    }

    /** Verifikasi OTP. Return OtpCode bila valid (dan langsung dikonsumsi). */
    public function verify(string $email, string $code, string $purpose = 'registration'): OtpCode
    {
        $otp = OtpCode::where('email', strtolower(trim($email)))
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->latest()
            ->first();

        if (! $otp) {
            throw new \DomainException('Kode OTP tidak ditemukan. Silakan minta kode baru.');
        }

        if ($otp->isExpired()) {
            throw new \DomainException('Kode OTP sudah kedaluwarsa. Silakan minta kode baru.');
        }

        if ($otp->tooManyAttempts()) {
            throw new \DomainException('Terlalu banyak percobaan salah. Silakan minta kode baru.');
        }

        if ($otp->code !== trim($code)) {
            $otp->increment('attempts');
            throw new \DomainException('Kode OTP salah (' . (self::MAX_ATTEMPTS - $otp->attempts) . ' percobaan tersisa).');
        }

        $otp->update(['consumed_at' => now()]);

        return $otp;
    }

    /** Verifikasi tanpa konsumsi (pre-check sebelum submit form). */
    public function isValid(string $email, string $code, string $purpose = 'registration'): bool
    {
        try {
            $otp = OtpCode::where('email', strtolower(trim($email)))
                ->where('purpose', $purpose)
                ->where('code', trim($code))
                ->whereNull('consumed_at')
                ->latest()
                ->first();

            return $otp !== null && ! $otp->isExpired() && ! $otp->tooManyAttempts();
        } catch (\Throwable) {
            return false;
        }
    }
}
