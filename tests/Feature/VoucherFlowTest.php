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
 * CPC v2.0 §10–§12 — alur penukaran voucer:
 * staff mengajukan (PENDING_APPROVAL) → manajer menyetujui (REDEEMED) / menolak (kembali AVAILABLE).
 */
class VoucherFlowTest extends TestCase
{
    use RefreshDatabase;

    private Member $member;
    private Voucher $voucherA;
    private Voucher $voucherB;
    private User $staff;
    private User $manager;
    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('member_no_prefix', 'HCM');
        Setting::set('membership_validity_months', 12);

        $hotel = Hotel::create([
            'code' => 'HCJ', 'name' => 'Hotel Ciputra Jakarta', 'city' => 'Jakarta',
            'address' => 'Jl. Test', 'phone' => '021', 'email' => 'test@hotel.test',
        ]);
        $level = MembershipLevel::create(['code' => 'classic', 'name' => 'Classic', 'sort_order' => 1, 'card_color' => '#666']);

        $this->staff = User::create([
            'name' => 'Staff FO', 'email' => 'staff@test.test', 'password' => 'password',
            'role' => 'staff', 'hotel_id' => $hotel->id,
        ]);
        $this->manager = User::create([
            'name' => 'Manager', 'email' => 'manager@test.test', 'password' => 'password',
            'role' => 'manager', 'hotel_id' => $hotel->id,
        ]);
        $this->superAdmin = User::create([
            'name' => 'Super Admin', 'email' => 'superadmin@test.test', 'password' => 'password',
            'role' => 'super_admin', 'hotel_id' => null,
        ]);

        $this->member = Member::create([
            'member_no' => 'HCM-000001',
            'full_name' => 'Voucer Tester',
            'email' => 'tester@test.test',
            'phone' => '081234500011',
            'hotel_id' => $hotel->id,
            'membership_type' => 'free',
            'level_id' => $level->id,
            'status' => 'active',
            'joined_at' => now()->toDateString(),
            'valid_until' => now()->addYear()->toDateString(),
        ]);

        // Member portal user (untuk halaman portal)
        User::create([
            'name' => 'Voucer Tester', 'email' => 'tester@test.test', 'password' => 'password',
            'role' => 'member', 'member_id' => $this->member->id,
        ]);

        $breakfast = VoucherType::create(['code' => 'breakfast', 'name' => 'Voucer Sarapan', 'sort_order' => 1]);
        $gym = VoucherType::create(['code' => 'gym', 'name' => 'Voucer Gym', 'sort_order' => 2]);
        $package = VoucherPackage::create(['name' => 'Paket Free', 'applies_to' => 'free', 'is_default' => true]);
        VoucherPackageItem::create(['voucher_package_id' => $package->id, 'voucher_type_id' => $breakfast->id, 'qty' => 1]);
        VoucherPackageItem::create(['voucher_package_id' => $package->id, 'voucher_type_id' => $gym->id, 'qty' => 1]);

        $this->voucherA = Voucher::create([
            'voucher_no' => 'VCH-260001-0001', 'member_id' => $this->member->id,
            'voucher_type_id' => $breakfast->id, 'voucher_package_id' => $package->id,
            'source' => 'registration', 'status' => Voucher::STATUS_AVAILABLE,
            'issued_at' => now(), 'expires_at' => now()->addYear(),
        ]);
        $this->voucherB = Voucher::create([
            'voucher_no' => 'VCH-260001-0002', 'member_id' => $this->member->id,
            'voucher_type_id' => $gym->id, 'voucher_package_id' => $package->id,
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

    public function test_staff_bisa_membuka_halaman_penukaran_voucer(): void
    {
        $this->login($this->staff);

        $this->get(route('staff.vouchers'))->assertOk()->assertSee('Penukaran Voucer Member');
    }

    public function test_staff_cari_voucer_by_id(): void
    {
        $this->login($this->staff);

        $this->get(route('staff.vouchers', ['q' => 'VCH-260001-0001']))
            ->assertOk()
            ->assertSee('VCH-260001-0001')
            ->assertSee('AJUKAN PENUKARAN', false);
    }

    public function test_staff_mengajukan_penukaran(): void
    {
        $this->login($this->staff);

        $response = $this->post(route('staff.vouchers.request', $this->voucherA), [
            'hotel_id' => $this->member->hotel_id,
            'outlet' => 'Cipta Restaurant',
            'request_note' => 'Breakfast tamu meja 5',
        ]);

        $response->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('vouchers', [
            'id' => $this->voucherA->id,
            'status' => Voucher::STATUS_PENDING_APPROVAL,
            'outlet' => 'Cipta Restaurant',
        ]);

        $this->assertDatabaseHas('redemption_requests', [
            'voucher_id' => $this->voucherA->id,
            'member_id' => $this->member->id,
            'requested_by' => $this->staff->id,
            'status' => RedemptionRequest::PENDING,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'redemption_requested',
            'model_id' => $this->voucherA->id,
        ]);
    }

    public function test_voucer_pending_tidak_bisa_diajukan_lagi(): void
    {
        $this->login($this->staff);

        $this->post(route('staff.vouchers.request', $this->voucherA), [
            'hotel_id' => $this->member->hotel_id,
            'outlet' => 'Cipta Restaurant',
        ]);

        $second = $this->post(route('staff.vouchers.request', $this->voucherA), [
            'hotel_id' => $this->member->hotel_id,
            'outlet' => 'Cipta Restaurant',
        ]);

        $second->assertSessionHas('error');
        $this->assertEquals(1, RedemptionRequest::where('voucher_id', $this->voucherA->id)->count());
    }

    public function test_staff_tidak_bisa_mengakses_approval(): void
    {
        $this->login($this->staff);

        $this->get(route('admin.redemptions'))->assertForbidden();
        $this->get(route('admin.vouchers.index'))->assertForbidden();
    }

    public function test_manajer_menyetujui_penukaran(): void
    {
        $this->login($this->staff);
        $this->post(route('staff.vouchers.request', $this->voucherA), [
            'hotel_id' => $this->member->hotel_id,
            'outlet' => 'Sky Bar',
        ]);

        $this->login($this->manager);
        $request = RedemptionRequest::first();

        $this->post(route('admin.redemptions.approve', $request))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vouchers', [
            'id' => $this->voucherA->id,
            'status' => Voucher::STATUS_REDEEMED,
            'redeemed_by' => $this->manager->id,
        ]);

        $this->assertDatabaseHas('redemption_requests', [
            'id' => $request->id,
            'status' => RedemptionRequest::APPROVED,
            'decided_by' => $this->manager->id,
        ]);

        // Email notifikasi §14 — terkirim SETELAH persetujuan
        $this->assertDatabaseHas('email_logs', [
            'member_id' => $this->member->id,
            'type' => 'voucher_redeemed',
        ]);
    }

    public function test_manajer_menolak_dengan_alasan_wajib(): void
    {
        $this->login($this->staff);
        $this->post(route('staff.vouchers.request', $this->voucherA), [
            'hotel_id' => $this->member->hotel_id,
            'outlet' => 'Gym',
        ]);

        $this->login($this->manager);
        $request = RedemptionRequest::first();

        // Tanpa alasan → error validasi
        $this->post(route('admin.redemptions.reject', $request), [])
            ->assertSessionHasErrors('rejection_reason');

        // Dengan alasan → ditolak, voucer kembali AVAILABLE
        $this->post(route('admin.redemptions.reject', $request), [
            'rejection_reason' => 'Voucer sudah dipakai reservasi lain',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('vouchers', [
            'id' => $this->voucherA->id,
            'status' => Voucher::STATUS_AVAILABLE,
        ]);

        $this->assertDatabaseHas('redemption_requests', [
            'id' => $request->id,
            'status' => RedemptionRequest::REJECTED,
            'rejection_reason' => 'Voucer sudah dipakai reservasi lain',
        ]);
    }

    public function test_pengaju_tidak_bisa_menyetujui_sendiri(): void
    {
        // Hotel admin (manager-like) mengajukan sendiri
        $hotelAdmin = User::create([
            'name' => 'Hotel Admin', 'email' => 'hoteladmin@test.test', 'password' => 'password',
            'role' => 'hotel_admin', 'hotel_id' => $this->member->hotel_id,
        ]);
        $this->login($hotelAdmin);
        $this->post(route('staff.vouchers.request', $this->voucherB), [
            'hotel_id' => $this->member->hotel_id,
            'outlet' => 'Front Office',
        ]);

        // Mencoba menyetujui permintaan sendiri → ditolak service
        $request = RedemptionRequest::first();
        $this->post(route('admin.redemptions.approve', $request))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('vouchers', [
            'id' => $this->voucherB->id,
            'status' => Voucher::STATUS_PENDING_APPROVAL,
        ]);
    }

    public function test_halaman_approval_manajer(): void
    {
        $this->login($this->staff);
        $this->post(route('staff.vouchers.request', $this->voucherA), [
            'hotel_id' => $this->member->hotel_id,
            'outlet' => 'Cipta Restaurant',
        ]);

        $this->login($this->manager);

        $this->get(route('admin.redemptions'))
            ->assertOk()
            ->assertSee('VCH-260001-0001')
            ->assertSee('SETUJUI', false);
    }

    public function test_halaman_voucer_management_super_admin(): void
    {
        $this->login($this->superAdmin);

        $this->get(route('admin.vouchers.index'))
            ->assertOk()
            ->assertSee('Voucer Management')
            ->assertSee('VCH-260001-0001');
    }

    public function test_member_portal_halaman_voucer(): void
    {
        $memberUser = User::where('email', 'tester@test.test')->where('role', 'member')->first();
        $this->login($memberUser);

        $this->get(route('portal.vouchers'))
            ->assertOk()
            ->assertSee('MY VOUCHERS')
            ->assertSee('VCH-260001-0001')
            ->assertSee('Riwayat Penukaran');
    }

    public function test_super_admin_bisa_membatalkan_voucer(): void
    {
        $this->login($this->superAdmin);

        $this->post(route('admin.vouchers.cancel', $this->voucherB), ['reason' => 'Salah terbit'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vouchers', [
            'id' => $this->voucherB->id,
            'status' => Voucher::STATUS_CANCELLED,
        ]);
    }

    public function test_voucer_kedaluwarsa_tidak_bisa_diajukan(): void
    {
        $this->voucherA->update(['expires_at' => now()->subDay()]);
        $this->login($this->staff);

        $this->post(route('staff.vouchers.request', $this->voucherA), [
            'hotel_id' => $this->member->hotel_id,
            'outlet' => 'Cipta Restaurant',
        ])->assertSessionHas('error');

        $this->assertDatabaseHas('vouchers', [
            'id' => $this->voucherA->id,
            'status' => Voucher::STATUS_AVAILABLE,
        ]);
    }
}
