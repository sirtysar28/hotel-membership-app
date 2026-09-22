<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Support\Captcha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'captcha' => ['required', 'string', 'size:' . Captcha::LENGTH],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'captcha.required' => 'Kode captcha wajib diisi.',
            'captcha.size' => 'Kode captcha harus :size karakter.',
        ]);

        // Validasi captcha (one-time use, langsung dihapus dari session)
        $captchaInput = $credentials['captcha'];
        unset($credentials['captcha']);

        if (! Captcha::verify($captchaInput)) {
            return back()->withErrors(['captcha' => 'Kode captcha salah. Silakan coba lagi.'])->onlyInput('email');
        }

        $remember = $request->boolean('remember');

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            return redirect()->route('login')->withErrors(['email' => 'Akun Anda tidak aktif.']);
        }

        // Update #18 — akun staff/manager harus disetujui lebih dulu oleh super admin
        if ($user->approval_status === 'pending') {
            Auth::logout();

            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda masih MENUNGGU PERSETUJUAN admin. Silakan hubungi Super Admin.']);
        }

        if ($user->approval_status === 'rejected') {
            Auth::logout();

            return redirect()->route('login')
                ->withErrors(['email' => 'Pendaftaran akun Anda DITOLAK. Silakan hubungi Super Admin.']);
        }

        AuditLog::record('login', 'User', $user->id, "Login: {$user->email}");

        if ($user->isMember()) {
            return redirect()->route('portal.dashboard');
        }

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        AuditLog::record('logout', 'User', auth()->id(), 'Logout');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /*
    |----------------------------------------------------------------------
    | Lupa Password / Reset Password
    |----------------------------------------------------------------------
    */

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'captcha' => ['required', 'string', 'size:' . Captcha::LENGTH],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'captcha.required' => 'Kode captcha wajib diisi.',
            'captcha.size' => 'Kode captcha harus :size karakter.',
        ]);

        if (! Captcha::verify($validated['captcha'])) {
            return back()->withErrors(['captcha' => 'Kode captcha salah. Silakan coba lagi.'])->onlyInput('email');
        }

        // Password broker: membuat token + kirim email (throttle otomatis 60 detik)
        $status = Password::broker()->sendResetLink(['email' => $validated['email']]);

        // Selalu tampilkan pesan sukses agar tidak membocorkan keberadaan akun (user enumeration)
        return back()->with('status', 'Jika email terdaftar, link reset password telah dikirim ke email Anda. Silakan cek inbox (atau folder spam).');
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', old('email', '')),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => \Illuminate\Support\Str::random(60),
                ])->save();

                AuditLog::record('password_reset', 'User', $user->id, "Reset password via email: {$user->email}");
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Password berhasil direset. Silakan login dengan password baru Anda.');
        }

        return back()
            ->withInput(['email' => $request->email])
            ->withErrors(['email' => 'Link reset password tidak valid atau sudah kedaluwarsa. Silakan ajukan reset password kembali.']);
    }
}
