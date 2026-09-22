<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureMailFromSettings();
    }

    /**
     * Terapkan konfigurasi SMTP dari tabel `settings` (diatur via Admin → Settings).
     * Jika tabel belum tersedia (misal saat migrate pertama), abaikan secara aman.
     */
    private function configureMailFromSettings(): void
    {
        try {
            $mailer = Setting::get('mail_mailer');

            if ($mailer === null) {
                return; // belum dikonfigurasi, pakai default .env
            }

            config(['mail.default' => $mailer]);

            if ($mailer === 'smtp') {
                config([
                    'mail.mailers.smtp.host' => Setting::get('smtp_host', config('mail.mailers.smtp.host')),
                    'mail.mailers.smtp.port' => (int) Setting::get('smtp_port', config('mail.mailers.smtp.port')),
                    'mail.mailers.smtp.username' => Setting::get('smtp_username', config('mail.mailers.smtp.username')),
                    'mail.mailers.smtp.password' => $this->decryptPassword(Setting::get('smtp_password')) ?? config('mail.mailers.smtp.password'),
                    'mail.mailers.smtp.encryption' => Setting::get('smtp_encryption') ?: config('mail.mailers.smtp.encryption'),
                    'mail.mailers.smtp.timeout' => 15,
                ]);
            }

            $fromAddress = Setting::get('mail_from_address');
            $fromName = Setting::get('mail_from_name');

            if ($fromAddress) {
                config(['mail.from.address' => $fromAddress]);
            }
            if ($fromName) {
                config(['mail.from.name' => $fromName]);
            }
        } catch (\Throwable) {
            // Tabel settings belum ada (fresh install / migrate) — biarkan konfigurasi .env.
        }
    }

    private function decryptPassword(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value; // kompatibilitas: nilai plain-text lama
        }
    }
}
