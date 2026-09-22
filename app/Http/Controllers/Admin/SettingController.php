<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Services\EmailService;
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

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // General
            'paid_membership_price' => ['required', 'numeric', 'min:0'],
            'member_no_prefix' => ['required', 'string', 'max:10'],
            'membership_validity_years' => ['required', 'integer', 'min:1'],

            // SMTP / Email
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

        $general = collect($validated)->only([
            'paid_membership_price', 'member_no_prefix', 'membership_validity_years',
        ])->all();

        foreach ($general as $key => $value) {
            Setting::set($key, $value);
        }

        // --- SMTP ---
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

        if ($validated['mail_mailer'] === 'smtp' && empty($validated['smtp_host'])) {
            return back()->with('error', 'Mailer SMTP dipilih — SMTP Host wajib diisi.')->withInput();
        }

        AuditLog::record('settings_updated', 'Setting', null,
            'Settings diperbarui' . ($smtpChanged ? ' (termasuk SMTP: ' . implode(', ', $smtpChanged) . ')' : ''),
            $general,
        );

        $note = $validated['mail_mailer'] === 'smtp'
            ? ' Email notifikasi akan dikirim via SMTP.'
            : ' Email hanya dicatat di log (MAIL_MAILER=log).';

        return back()->with('success', 'Settings berhasil disimpan.' . $note);
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
