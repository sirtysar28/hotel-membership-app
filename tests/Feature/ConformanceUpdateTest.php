<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Member;
use App\Models\MembershipLevel;
use App\Models\RedemptionRequest;
use App\Models\Setting;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherPackage;
use App\Models\VoucherPackageItem;
use App\Models\VoucherType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Conformance test — CPC v2.0 + daftar update 22 Sept 2026:
 * - #3/#7/#14 : filter dashboard (unit + tanggal/preset)
 * - #8/#19    : dropdown kode negara no HP saat registrasi
 * - #10       : field bukti fisik accounting saat registrasi
 * - #17       : paket 12 voucer utk registrasi paid & free
 * - §11       : daftar status voucer lengkap (termasuk APPROVED & REJECTED)
 * - §13       : slip cetak voucer setelah persetujuan manajer
 * - #15       : preset laporan per tanggal / per bulan
 * - #18       : akun staff/manager butuh approval sebelum login
 */
class ConformanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $hotel;
    private User $superAdmin;
    private User $manager;
    private User $staff;
    private Member $member;
    private Voucher $voucher;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('member_no_prefix', 'HCM');
        Setting::set('membership_validity_months', 12);

        $this->hotel = Hotel::create([
            'code' => 'HCJ', 'name' => 'Hotel Ciputra Jakarta', 'city' => 'Jakarta',
            'address' => 'Jl. Test', 'phone' => '021', 'email' => 'test@hotel.test',
        ]);
        $level = MembershipLevel::create(['code' => 'classic', 'name' => 'Classic', 'sort_order' => 1, 'card_color' => '#666']);

        $this->superAdmin = User::create([
            'name' => 'Super Admin', 'email' => 'superadmin@test.test', 'password' => 'password',
            'role' => 'super_admin',
        ]);
        $this->manager = User::create([
            'name' => 'Manager', 'email' => 'manager@test.test', 'password' => 'password',
            'role' => 'manager', 'hotel_id' => $this->hotel->id,
        ]);
        $this->staff = User::create([
            'name' => 'Staff FO', 'email' => 'staff@test.test', 'password' => 'password',
            'role' => 'staff', 'hotel_id' => $this->hotel->id,
        ]);

        $this->member = Member::create([
            'member_no' => 'HCM-000001',
            'full_name' => 'Conformance Tester',
            'email' => 'conformance@test.test',
            'phone' => '81234500011',
            'phone_country_code' => '+65',
            'hotel_id' => $this->hotel->id,
            'membership_type' => 'free',
            'level_id' => $level->id,
            'status' => 'active',
            'joined_at' => now()->toDateString(),
            'valid_until' => now()->addYear()->toDateString(),
        ]);

        $breakfast = VoucherType::create(['code' => 'breakfast', 'name' => 'Voucer Sarapan', 'sort_order' => 1]);
        $package = VoucherPackage::create(['name' => 'Paket Free', 'applies_to' => 'free', 'is_default' => true]);
        VoucherPackageItem::create(['voucher_package_id' => $package->id, 'voucher_type_id' => $breakfast->id, 'qty' => 1]);

        $this->voucher = Voucher::create([
            'voucher_no' => 'VCH-260001-0001', 'member_id' => $this->member->id,
            'voucher_type_id' => $breakfast->id, 'voucher_package_id' => $package->id,
            'source' => 'registration', 'status' => Voucher::STATUS_AVAILABLE,
            'issued_at' => now(), 'expires_at' => now()->addYear(),
        ]);
    }

    private function login(User $user): \Illuminate\Testing\TestResponse
    {
        return $this->withSession(['captcha_code' => 'TESTS'])
            ->post(route('login.attempt'), [
                'email' => $user->email,
                'password' => 'password',
                'captcha' => 'tests',
            ]);
    }

    /*
    |------------------ #3/#7/#14 — Filter dashboard ------------------
    */

    public function test_dashboard_menampilkan_filter_dan_menerima_filter(): void
    {
        $this->login($this->superAdmin);

        // Filter bar tampil (unit + periode)
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Unit / Hotel')
            ->assertSee('Periode');

        // Filter preset bulan_ini + hotel — halaman tetap OK & indikator rentang tampil
        $this->get(route('admin.dashboard', [
            'hotel_id' => $this->hotel->id,
            'preset' => 'bulan_ini',
        ]))->assertOk()->assertSee('Data:');

        // Filter rentang kustom
        $this->get(route('admin.dashboard', [
            'preset' => 'rentang',
            'from' => now()->toDateString(),
            'to' => now()->toDateString(),
        ]))->assertOk();
    }

    /*
    |------------------ #8/#19 — Kode negara no HP ------------------
    */

    public function test_form_registrasi_memiliki_dropdown_kode_negara(): void
    {
        $this->get(route('register.create'))
            ->assertOk()
            ->assertSee('name="phone_country_code"', false)
            ->assertSee('+62 ID')
            ->assertSee('+65 SG');
    }

    public function test_registrasi_menyimpan_kode_negara_dan_bukti_fisik(): void
    {
        // Simulasi email sudah terverifikasi OTP
        \App\Models\OtpCode::create([
            'email' => 'intl@test.test',
            'code' => '123456',
            'purpose' => 'registration',
            'verified_at' => now(),
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->post(route('register.store'), [
            'full_name' => 'International Guest',
            'email' => 'intl@test.test',
            'phone_country_code' => '+81',
            'phone' => '9012345678',
            'proof_reference' => 'BUKTI-ACC-00123',
            'otp_code' => '123456',
            'hotel_id' => $this->hotel->id,
            'membership_type' => 'free',
        ]);

        $response->assertRedirect();

        $member = Member::where('email', 'intl@test.test')->first();
        $this->assertNotNull($member);
        $this->assertSame('+81', $member->phone_country_code);
        $this->assertSame('BUKTI-ACC-00123', $member->proof_reference);
    }

    /*
    |------------------ #17 — 12 voucer utk free & paid ------------------
    */

    public function test_registrasi_free_menerima_12_voucer(): void
    {
        // Susun paket free 12 item (komposisi contoh dokumen §21)
        $pkg = VoucherPackage::defaultFor('free');
        VoucherPackageItem::where('voucher_package_id', $pkg->id)->delete();
        $composition = ['breakfast' => 2, 'cl' => 1, 'gym' => 2, 'room_upgrade' => 3, 'swimming_pool' => 2, 'room' => 2];
        foreach ($composition as $code => $qty) {
            $type = VoucherType::firstOrCreate(['code' => $code], ['name' => ucfirst($code), 'sort_order' => 99]);
            VoucherPackageItem::create(['voucher_package_id' => $pkg->id, 'voucher_type_id' => $type->id, 'qty' => $qty]);
        }

        $issued = app(\App\Services\VoucherService::class)->issuePackage($this->member, null, 'registration');

        $this->assertCount(12, $issued);
        $this->assertCount(12, array_unique(array_column($issued, 'voucher_no'))); // semua ID unik
    }

    /*
    |------------------ §11 — Status voucer lengkap ------------------
    */

    public function test_daftar_status_voucer_sesuai_dokumen(): void
    {
        $expected = ['AVAILABLE', 'PENDING_APPROVAL', 'APPROVED', 'REDEEMED', 'REJECTED', 'EXPIRED', 'CANCELLED'];
        $this->assertSame($expected, array_keys(Voucher::STATUSES));
    }

    /*
    |------------------ §13 — Slip cetak voucer ------------------
    */

    public function test_slip_cetak_tersedia_setelah_approval_manajer(): void
    {
        $service = app(\App\Services\VoucherService::class);
        $request = $service->requestRedemption($this->voucher, $this->staff, $this->hotel->id, 'Cipta Restaurant');
        $service->approveRedemption($request, $this->manager);

        $this->login($this->manager);

        $this->get(route('admin.redemptions.slip', $request))
            ->assertOk()
            ->assertSee($this->voucher->voucher_no)
            ->assertSee($this->member->member_no)
            ->assertSee('APPROVED &amp; REDEEMED', false);

        // Slip tidak tersedia utk permintaan yang masih pending
        $v2 = Voucher::create([
            'voucher_no' => 'VCH-260001-0002', 'member_id' => $this->member->id,
            'voucher_type_id' => $this->voucher->voucher_type_id,
            'voucher_package_id' => $this->voucher->voucher_package_id,
            'source' => 'registration', 'status' => Voucher::STATUS_AVAILABLE,
            'issued_at' => now(), 'expires_at' => now()->addYear(),
        ]);
        $pending = $service->requestRedemption($v2, $this->staff, $this->hotel->id);

        $this->get(route('admin.redemptions.slip', $pending))->assertNotFound();
    }

    /*
    |------------------ #15 — Preset laporan per tanggal/bulan ------------------
    */

    public function test_laporan_mendukung_preset_per_tanggal_dan_per_bulan(): void
    {
        $this->login($this->superAdmin);

        $this->get(route('admin.reports.index', ['type' => 'members', 'preset' => 'hari_ini']))
            ->assertOk()
            ->assertSee('Periode');

        $this->get(route('admin.reports.index', [
            'type' => 'members', 'preset' => 'bulan', 'month' => now()->format('Y-m'),
        ]))->assertOk();
    }

    /*
    |------------------ #18 — Approval akun staff/manager ------------------
    */

    public function test_akun_staff_baru_butuh_approval_dan_login_diblokir(): void
    {
        $this->login($this->superAdmin);

        // Buat akun staff baru via admin
        $this->post(route('admin.users.store'), [
            'name' => 'Staff Baru',
            'email' => 'staff.baru@test.test',
            'password' => 'password123',
            'role' => 'staff',
            'hotel_id' => $this->hotel->id,
        ])->assertRedirect();

        $newUser = User::where('email', 'staff.baru@test.test')->first();
        $this->assertNotNull($newUser);
        $this->assertSame('pending', $newUser->approval_status); // #18 — menunggu approval

        // Login ditolak selama pending
        $this->post(route('logout'));
        $this->withSession(['captcha_code' => 'TESTS'])
            ->post(route('login.attempt'), [
                'email' => 'staff.baru@test.test',
                'password' => 'password123',
                'captcha' => 'tests',
            ])->assertSessionHasErrors('email');

        $this->assertGuest();

        // Super admin menyetujui → akun aktif & bisa login
        $this->login($this->superAdmin);
        $this->post(route('admin.users.approve', $newUser))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('approved', $newUser->refresh()->approval_status);
        $this->assertNotNull($newUser->approved_at);

        $this->post(route('logout'));
        $this->withSession(['captcha_code' => 'TESTS'])
            ->post(route('login.attempt'), [
                'email' => 'staff.baru@test.test',
                'password' => 'password123',
                'captcha' => 'tests',
            ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($newUser);
    }

    public function test_akun_super_admin_baru_tanpa_approval(): void
    {
        $this->login($this->superAdmin);

        $this->post(route('admin.users.store'), [
            'name' => 'Super Dua',
            'email' => 'super2@test.test',
            'password' => 'password123',
            'role' => 'super_admin',
            'hotel_id' => null,
        ])->assertRedirect();

        $this->assertSame(
            'approved',
            User::where('email', 'super2@test.test')->first()->approval_status
        );
    }
}
