<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Services\EmailService;
use App\Support\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::orderBy('key')->get()->keyBy('key');

        return view('admin.settings.index', ['settings' => $settings]);
    }

    /**
     * Setiap card di halaman Settings mengirim field `section` sendiri,
     * sehingga hanya bagian itu yang divalidasi & disimpan — misal ganti
     * logo tidak akan kena validasi SMTP, dan sebaliknya.
     */
    public function update(Request $request): RedirectResponse
    {
        return match ($request->input('section')) {
            'branding' => $this->updateBranding($request),
            'smtp' => $this->updateSmtp($request),
            default => $this->updateGeneral($request),
        };
    }

    /**
     * Section: General (harga, prefix, masa berlaku).
     */
    private function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('general', [
            'paid_membership_price' => ['required', 'numeric', 'min:0'],
            'member_no_prefix' => ['required', 'string', 'max:10'],
            'membership_validity_years' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        AuditLog::record('settings_updated', 'Setting', null,
            'Settings General diperbarui',
            $validated,
        );

        return back()->with('success', 'Settings General berhasil disimpan.');
    }

    /**
     * Section: SMTP / Email.
     */
    private function updateSmtp(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('smtp', [
            'mail_mailer' => ['required', 'in:log,smtp'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'], // kosong = tidak diubah
            'smtp_encryption' => ['required', 'in:none,tls,ssl'],
            'mail_from_address' => ['nullable', 'email'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
        ], [
            'smtp_port.integer' => 'Port SMTP harus berupa angka.',
            'smtp_port.min' => 'Port SMTP minimal 1.',
            'smtp_port.max' => 'Port SMTP maksimal 65535.',
            'mail_from_address.email' => 'Format From Address tidak valid.',
            'mail_mailer.in' => 'Mailer harus log atau smtp.',
            'smtp_encryption.in' => 'Enkripsi harus none, tls, atau ssl.',
        ]);

        // Aturan tambahan yang tidak bisa divalidasi deklaratif
        if ($validated['mail_mailer'] === 'smtp' && empty($validated['smtp_host'])) {
            return back()->with('error', 'Mailer SMTP dipilih — SMTP Host wajib diisi.')->withInput();
        }

        $smtpChanged = [];

        $smtpKeys = [
            'mail_mailer', 'smtp_host', 'smtp_port',
            'smtp_username', 'smtp_encryption', 'mail_from_address', 'mail_from_name',
        ];

        foreach ($smtpKeys as $key) {
            $value = array_key_exists($key, $validated) ? $validated[$key] : null;
            if (Setting::get($key) != $value) {
                $smtpChanged[] = $key;
            }
            Setting::set($key, $value);
        }

        // Password SMTP dienkripsi; hanya di-update jika diisi (tidak menimpa yang lama)
        if (! empty($validated['smtp_password'])) {
            Setting::set('smtp_password', Crypt::encryptString($validated['smtp_password']));
            $smtpChanged[] = 'smtp_password';
        }

        AuditLog::record('settings_updated', 'Setting', null,
            'Settings SMTP diperbarui' . ($smtpChanged ? ' (' . implode(', ', $smtpChanged) . ')' : ''),
        );

        $note = $validated['mail_mailer'] === 'smtp'
            ? ' Email notifikasi akan dikirim via SMTP.'
            : ' Email hanya dicatat di log (MAIL_MAILER=log).';

        return back()->with('success', 'Pengaturan SMTP berhasil disimpan.' . $note);
    }

    /**
     * Section: Branding (logo login/landing/admin & favicon).
     * Tidak menyentuh field General/SMTP sama sekali.
     */
    private function updateBranding(Request $request): RedirectResponse
    {
        $request->validateWithBag('branding', [
            'logo_login' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'logo_landing' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'logo_admin' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg,ico', 'max:1024'],
            'remove_logo_login' => ['nullable', 'boolean'],
            'remove_logo_landing' => ['nullable', 'boolean'],
            'remove_logo_admin' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
        ], [
            'logo_login.mimes' => 'Logo login harus berupa file PNG, JPG, WEBP, atau SVG.',
            'logo_landing.mimes' => 'Logo landing harus berupa file PNG, JPG, WEBP, atau SVG.',
            'logo_admin.mimes' => 'Logo admin harus berupa file PNG, JPG, WEBP, atau SVG.',
            'favicon.mimes' => 'Favicon harus berupa file ICO, PNG, JPG, WEBP, atau SVG.',
            'logo_login.max' => 'Ukuran logo login maksimal 2 MB.',
            'logo_landing.max' => 'Ukuran logo landing maksimal 2 MB.',
            'logo_admin.max' => 'Ukuran logo admin maksimal 2 MB.',
            'favicon.max' => 'Ukuran favicon maksimal 1 MB.',
        ]);

        $changed = [];

        foreach (Brand::MANAGED_KEYS as $key) {
            if ($request->boolean('remove_' . $key)) {
                Brand::remove($key);
                $changed[] = $key . ' (kembali ke default)';
                continue;
            }

            if ($request->hasFile($key)) {
                Brand::store($key, $request->file($key));
                $changed[] = $key;
            }
        }

        if (empty($changed)) {
            return back()->with('error', 'Tidak ada perubahan branding — pilih file logo/favicon terlebih dahulu.');
        }

        AuditLog::record('settings_updated', 'Setting', null,
            'Settings Branding diperbarui (' . implode(', ', $changed) . ')',
        );

        return back()->with('success', 'Branding berhasil disimpan (' . implode(', ', $changed) . '). Refresh browser (Ctrl/Cmd+Shift+R) jika tampilan belum berubah.');
    }

    /**
     * Kirim email percobaan untuk memverifikasi konfigurasi SMTP.
     */
    public function sendTestEmail(Request $request, EmailService $emailService): RedirectResponse
    {
        $validated = $request->validate([
            'test_email' => ['required', 'email'],
        ], [
            'test_email.required' => 'Alamat email tujuan wajib diisi.',
            'test_email.email' => 'Format email tujuan tidak valid.',
        ]);

        try {
            // Pastikan config runtime ter-apply (AppServiceProvider sudah boot, tapi
            // refresh untuk memastikan pengaturan terbaru dipakai).
            $emailService->sendTest($validated['test_email'], auth()->user()->name ?? 'Admin');

            AuditLog::record('test_email_sent', 'Setting', null, 'Test email SMTP ke ' . $validated['test_email']);

            return back()->with('success', 'Email percobaan berhasil dikirim ke ' . $validated['test_email'] . '. Silakan cek inbox Anda.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal mengirim email percobaan: ' . $e->getMessage())->withInput();
        }
    }
}
