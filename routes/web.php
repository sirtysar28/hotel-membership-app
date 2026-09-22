<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/register', [RegistrationController::class, 'create'])->name('register.create');
Route::post('/register/check-duplicate', [RegistrationController::class, 'checkDuplicate'])->name('register.check-duplicate');

// OTP verifikasi email saat registrasi (anti-spambot, update #20/#22)
Route::post('/register/otp/send', [RegistrationController::class, 'sendOtp'])->name('register.otp.send');
Route::post('/register/otp/verify', [RegistrationController::class, 'verifyOtp'])->name('register.otp.verify');

Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');
Route::get('/register/success/{member_no}', [RegistrationController::class, 'success'])->name('register.success');

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Captcha (gambar PNG 5 karakter)
Route::get('/captcha', fn () => \App\Support\Captcha::image())->name('captcha');

// Lupa password / reset password
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

/*
|--------------------------------------------------------------------------
| Staff (Front Office) — search member, scan QR, redeem visit
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin,hotel_admin,membership_admin,staff'])
    ->prefix('staff')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/search', [StaffController::class, 'search'])->name('staff.search');
        Route::get('/scan', [StaffController::class, 'scan'])->name('staff.scan');
        Route::get('/members/{member}', [StaffController::class, 'show'])->name('staff.members.show');
        Route::post('/members/{member}/redeem', [StaffController::class, 'redeem'])->name('staff.members.redeem');

        // Penukaran voucer (CPC §10) — staff mengajukan, manajer menyetujui
        Route::get('/vouchers', [StaffController::class, 'vouchers'])->name('staff.vouchers');
        Route::post('/vouchers/{voucher}/request', [StaffController::class, 'requestVoucherRedemption'])->name('staff.vouchers.request');
    });

/*
|--------------------------------------------------------------------------
| Member Portal
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:member'])->prefix('portal')->group(function () {
    Route::get('/', [PortalController::class, 'dashboard'])->name('portal.dashboard');
    Route::get('/card', [PortalController::class, 'card'])->name('portal.card');
    Route::get('/visits', [PortalController::class, 'visits'])->name('portal.visits');
    Route::get('/transactions', [PortalController::class, 'transactions'])->name('portal.transactions');
    Route::get('/vouchers', [PortalController::class, 'vouchers'])->name('portal.vouchers');
    Route::get('/benefits', [PortalController::class, 'benefits'])->name('portal.benefits');
    Route::get('/profile', [PortalController::class, 'profile'])->name('portal.profile');
    Route::put('/profile', [PortalController::class, 'updateProfile'])->name('portal.profile.update');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin,hotel_admin,membership_admin,finance,management,manager'])
    ->prefix('admin')->group(function () {
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('admin.dashboard');

        // Members
        Route::middleware('role:super_admin,hotel_admin,membership_admin')->group(function () {
            Route::resource('members', Admin\MemberController::class)->except(['destroy'])->names('admin.members');
            Route::post('/members/{member}/status', [Admin\MemberController::class, 'updateStatus'])->name('admin.members.status');
            Route::get('/members/{member}/card', [Admin\MemberController::class, 'card'])->name('admin.members.card');
            Route::post('/members/{member}/reissue-card', [Admin\MemberController::class, 'reissueCard'])->name('admin.members.reissue-card');
            Route::get('/members/{member}/renewal', [Admin\MemberController::class, 'sendRenewal'])->name('admin.members.renewal');

            // Duplicate review
            Route::get('/duplicates', [Admin\DuplicateCheckController::class, 'index'])->name('admin.duplicates.index');
            Route::post('/duplicates/{duplicateCheck}/resolve', [Admin\DuplicateCheckController::class, 'resolve'])->name('admin.duplicates.resolve');

            // Levels & rules (Rule Engine)
            Route::get('/levels', [Admin\LevelController::class, 'index'])->name('admin.levels.index');
            Route::put('/levels/rules/{rule}', [Admin\LevelController::class, 'updateRule'])->name('admin.levels.rules.update');

            // Benefits
            Route::resource('benefits', Admin\BenefitController::class)->except(['show', 'destroy'])->names('admin.benefits');
            Route::post('/benefits/{benefit}/grant', [Admin\BenefitController::class, 'grant'])->name('admin.benefits.grant');

            // Voucer management (CPC v2.0 §4–§6)
            Route::get('/vouchers', [Admin\VoucherController::class, 'index'])->name('admin.vouchers.index')
                ->middleware('role:super_admin,hotel_admin,membership_admin,manager');
            Route::post('/vouchers/{voucher}/cancel', [Admin\VoucherController::class, 'cancel'])->name('admin.vouchers.cancel')
                ->middleware('role:super_admin,membership_admin');
        });

        // Approval penukaran voucer (CPC §10/§12) — manajer menyetujui/menolak
        Route::get('/redemptions', [Admin\VoucherController::class, 'redemptions'])->name('admin.redemptions')
            ->middleware('role:super_admin,hotel_admin,manager,membership_admin');
        Route::post('/redemptions/{redemption}/approve', [Admin\VoucherController::class, 'approve'])->name('admin.redemptions.approve')
            ->middleware('role:super_admin,hotel_admin,manager');
        Route::post('/redemptions/{redemption}/reject', [Admin\VoucherController::class, 'reject'])->name('admin.redemptions.reject')
            ->middleware('role:super_admin,hotel_admin,manager');
        // CPC §13 — slip cetak/keluaran voucer setelah persetujuan
        Route::get('/redemptions/{redemption}/slip', [Admin\VoucherController::class, 'slip'])->name('admin.redemptions.slip')
            ->middleware('role:super_admin,hotel_admin,manager,membership_admin');

        // Payments & finance
        Route::get('/payments', [Admin\PaymentController::class, 'index'])->name('admin.payments.index')
            ->middleware('role:super_admin,hotel_admin,finance,membership_admin');
        Route::post('/payments/{payment}/submit', [Admin\PaymentController::class, 'markSubmitted'])->name('admin.payments.submit')
            ->middleware('role:super_admin,hotel_admin,finance,membership_admin');
        Route::post('/payments/{payment}/verify', [Admin\PaymentController::class, 'verify'])->name('admin.payments.verify')
            ->middleware('role:super_admin,finance');
        Route::post('/payments/{payment}/reject', [Admin\PaymentController::class, 'reject'])->name('admin.payments.reject')
            ->middleware('role:super_admin,finance');

        // Reports
        Route::get('/reports/{type}', [Admin\ReportController::class, 'index'])->name('admin.reports.index')
            ->whereIn('type', ['members', 'revenue', 'visits', 'duplicates']);
        Route::get('/reports/{type}/export', [Admin\ReportController::class, 'export'])->name('admin.reports.export')
            ->whereIn('type', ['members', 'revenue', 'visits', 'duplicates']);

        // Hotels (super admin only)
        Route::resource('hotels', Admin\HotelController::class)->except(['show'])->names('admin.hotels')
            ->middleware('role:super_admin');

        // Users (super admin only)
        Route::resource('users', Admin\UserController::class)->except(['show'])->names('admin.users')
            ->middleware('role:super_admin');
        // Update #18 — approval akun staff/manager
        Route::post('/users/{user}/approve', [Admin\UserController::class, 'approve'])->name('admin.users.approve')
            ->middleware('role:super_admin');
        Route::post('/users/{user}/reject', [Admin\UserController::class, 'reject'])->name('admin.users.reject')
            ->middleware('role:super_admin');

        // Logs (read-only)
        Route::get('/audit-logs', [Admin\LogController::class, 'audit'])->name('admin.audit-logs')
            ->middleware('role:super_admin,hotel_admin,management');
        Route::get('/email-logs', [Admin\LogController::class, 'email'])->name('admin.email-logs')
            ->middleware('role:super_admin,hotel_admin,management,membership_admin');

        // Settings (super admin only)
        Route::get('/settings', [Admin\SettingController::class, 'index'])->name('admin.settings.index')
            ->middleware('role:super_admin');
        Route::put('/settings', [Admin\SettingController::class, 'update'])->name('admin.settings.update')
            ->middleware('role:super_admin');
        Route::post('/settings/test-email', [Admin\SettingController::class, 'sendTestEmail'])->name('admin.settings.test-email')
            ->middleware('role:super_admin');
    });
