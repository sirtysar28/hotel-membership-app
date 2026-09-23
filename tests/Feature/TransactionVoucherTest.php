<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Member;
use App\Models\MembershipBenefit;
use App\Models\MembershipLevel;
use App\Models\RedemptionRequest;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Voucer diskon otomatis dari transaksi:
 * member bertransaksi (mis. F&B/restaurant) → sistem baca benefit diskon sesuai
 * level member & jenis transaksi → generate voucer (PENDING_APPROVAL) → manajer approve.
 */
class TransactionVoucherTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $hotel;
    private MembershipLevel $classic;
    private MembershipLevel $privilege;
    private User $staff;
    private User $manager;
    private Member $classicMember;
    private Member $privilegeMember;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('member_no_prefix', 'HCM');
        Setting::set('membership_validity_months', 12);

        $this->hotel = Hotel::create([
            'code' => 'HCJ', 'name' => 'Hotel Ciputra Jakarta', 'city' => 'Jakarta',
            'address' => 'Jl. Test', 'phone' => '021', 'email' => 'test@hotel.test',
        ]);

        $this->classic = MembershipLevel::create(['code' => 'classic', 'name' => 'Classic', 'sort_order' => 1, 'card_color' => '#666']);
        $this->privilege = MembershipLevel::create(['code' => 'privilege', 'name' => 'Privilege', 'sort_order' => 2, 'card_color' => '#167']);

        $this->staff = User::create([
            'name' => 'Staff FO', 'email' => 'staff@test.test', 'password' => 'password',
            'role' => 'staff', 'hotel_id' => $this->hotel->id, 'is_active' => true,
        ]);
        $this->manager = User::create([
            'name' => 'Manager', 'email' => 'manager@test.test', 'password' => 'password',
            'role' => 'manager', 'hotel_id' => $this->hotel->id, 'is_active' => true,
        ]);

        $mkMember = fn (MembershipLevel $level, string $no) => Member::create([
            'member_no' => $no,
            'full_name' => 'Tester ' . $level->name,
            'email' => strtolower($level->code) . '@test.test',
            'phone' => '0812345000' . random_int(10, 99),
            'hotel_id' => $this->hotel->id,
            'membership_type' => 'free',
            'level_id' => $level->id,
            'status' => 'active',
            'joined_at' => now()->toDateString(),
            'valid_until' => now()->addYear()->toDateString(),
        ]);

        $this->classicMember = $mkMember($this->classic, 'HCM-000001');
        $this->privilegeMember = $mkMember($this->privilege, 'HCM-000002');

        // Jenis voucer hasil transaksi (biasanya via migrasi/seeder)
        VoucherType::firstOrCreate(['code' => 'fnb_discount'], ['name' => 'Voucer Diskon F&B', 'sort_order' => 7]);
        VoucherType::firstOrCreate(['code' => 'room_discount'], ['name' => 'Voucer Diskon Kamar', 'sort_order' => 8]);
    }

    private function redeem(Member $member, array $overrides = [])
    {
        return $this->actingAs($this->staff)->post(route('staff.members.redeem', $member), array_merge([
            'visit_date' => now()->toDateString(),
            'hotel_id' => $this->hotel->id,
            'type' => 'restaurant',
            'amount' => 1000000,
            'counts_as_visit' => '1',
        ], $overrides));
    }

    public function test_transaksi_fnb_generate_voucer_diskon_sesuai_benefit_level(): void
    {
        // Classic: disc F&B 5%
        MembershipBenefit::create([
            'level_id' => $this->classic->id, 'name' => 'Discount Restaurant',
            'category' => 'discount', 'value' => '5%', 'is_active' => true,
        ]);

        $response = $this->redeem($this->classicMember, ['amount' => 1000000, 'type' => 'restaurant', 'outlet' => 'Cipta Restaurant']);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $transaction = Transaction::where('member_id', $this->classicMember->id)->first();
        $this->assertNotNull($transaction);

        $voucher = Voucher::where('transaction_id', $transaction->id)->first();
        $this->assertNotNull($voucher, 'Voucer diskon harus digenerate dari transaksi F&B');
        $this->assertEquals('fnb_discount', $voucher->type->code);
        $this->assertEquals(5.0, (float) $voucher->discount_percent);
        $this->assertEquals(50000.0, (float) $voucher->discount_amount);
        $this->assertEquals(Voucher::STATUS_PENDING_APPROVAL, $voucher->status);
        $this->assertEquals('transaction', $voucher->source);
        $this->assertEquals($this->classicMember->valid_until->toDateString(), $voucher->expires_at->toDateString());

        // Langsung masuk antrian approval manajer
        $request = RedemptionRequest::where('voucher_id', $voucher->id)->first();
        $this->assertNotNull($request);
        $this->assertEquals(RedemptionRequest::PENDING, $request->status);
        $this->assertEquals($this->staff->id, $request->requested_by);
        $this->assertEquals('Cipta Restaurant', $request->outlet);

        // Audit trail
        $this->assertDatabaseHas('audit_logs', ['action' => 'transaction_voucher_issued']);
    }

    public function test_manajer_menyetujui_voucer_hasil_transaksi(): void
    {
        MembershipBenefit::create([
            'level_id' => $this->classic->id, 'name' => 'Discount F&B',
            'category' => 'fnb', 'value' => '5%', 'is_active' => true,
        ]);

        $this->redeem($this->classicMember);

        $request = RedemptionRequest::first();
        $this->assertNotNull($request);

        $response = $this->actingAs($this->manager)
            ->post(route('admin.redemptions.approve', $request));

        $response->assertRedirect();
        $this->assertEquals(RedemptionRequest::APPROVED, $request->refresh()->status);
        $this->assertEquals(Voucher::STATUS_REDEEMED, $request->voucher->refresh()->status);
    }

    public function test_benefit_spesifik_hotel_mengungguli_benefit_umum(): void
    {
        MembershipBenefit::create([
            'level_id' => $this->classic->id, 'name' => 'Discount F&B Global',
            'category' => 'discount', 'value' => '5%', 'is_active' => true,
        ]);
        MembershipBenefit::create([
            'level_id' => $this->classic->id, 'hotel_id' => $this->hotel->id,
            'name' => 'Discount F&B Jakarta', 'category' => 'discount', 'value' => '10%', 'is_active' => true,
        ]);

        $this->redeem($this->classicMember, ['amount' => 200000]);

        $voucher = Voucher::firstWhere('source', 'transaction');
        $this->assertNotNull($voucher);
        $this->assertEquals(10.0, (float) $voucher->discount_percent, 'Benefit hotel-spesifik (10%) harus dipakai');
        $this->assertEquals(20000.0, (float) $voucher->discount_amount);
    }

    public function test_transaksi_hotel_stay_pakai_jenis_voucer_kamar(): void
    {
        MembershipBenefit::create([
            'level_id' => $this->privilege->id, 'name' => 'Discount Kamar',
            'category' => 'room', 'value' => '10%', 'is_active' => true,
        ]);

        $this->redeem($this->privilegeMember, ['type' => 'hotel_stay', 'amount' => 5000000]);

        $voucher = Voucher::firstWhere('source', 'transaction');
        $this->assertNotNull($voucher);
        $this->assertEquals('room_discount', $voucher->type->code);
        $this->assertEquals(500000.0, (float) $voucher->discount_amount);
    }

    public function test_tanpa_benefit_diskon_tidak_generate_voucer(): void
    {
        // Classic hanya punya benefit non-diskon
        MembershipBenefit::create([
            'level_id' => $this->classic->id, 'name' => 'Welcome Drink',
            'category' => 'fnb', 'value' => '2 pax', 'is_active' => true,
        ]);

        $this->redeem($this->classicMember);

        $this->assertEquals(0, Voucher::where('source', 'transaction')->count());
    }

    public function test_transaksi_lainnya_tidak_generate_voucer(): void
    {
        MembershipBenefit::create([
            'level_id' => $this->classic->id, 'name' => 'Discount Restaurant',
            'category' => 'discount', 'value' => '5%', 'is_active' => true,
        ]);

        $this->redeem($this->classicMember, ['type' => 'other']);

        $this->assertEquals(0, Voucher::where('source', 'transaction')->count());
    }

    public function test_benefit_non_aktif_diabaikan(): void
    {
        MembershipBenefit::create([
            'level_id' => $this->classic->id, 'name' => 'Discount Restaurant',
            'category' => 'discount', 'value' => '5%', 'is_active' => false,
        ]);

        $this->redeem($this->classicMember);

        $this->assertEquals(0, Voucher::where('source', 'transaction')->count());
    }
}
