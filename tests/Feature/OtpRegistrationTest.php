<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Member;
use App\Models\MembershipLevel;
use App\Models\OtpCode;
use App\Models\Setting;
use App\Models\User;
use App\Models\VoucherPackage;
use App\Models\VoucherType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Update #20/#22 — OTP verifikasi email saat registrasi (anti-spambot).
 */
class OtpRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function seedBasics(): void
    {
        Setting::set('member_no_prefix', 'HCM');
        Setting::set('membership_validity_months', 12);

        Hotel::create([
            'code' => 'HCJ', 'name' => 'Hotel Ciputra Jakarta', 'city' => 'Jakarta',
            'address' => 'Jl. Test', 'phone' => '021', 'email' => 'test@hotel.test',
        ]);

        MembershipLevel::create(['code' => 'classic', 'name' => 'Classic', 'sort_order' => 1, 'card_color' => '#666']);

        // Paket voucer default free
        $breakfast = VoucherType::create(['code' => 'breakfast', 'name' => 'Voucer Sarapan', 'sort_order' => 1]);
        $package = VoucherPackage::create(['name' => 'Paket Free', 'applies_to' => 'free', 'is_default' => true]);
        $package->items()->create(['voucher_type_id' => $breakfast->id, 'qty' => 2]);
    }

    public function test_halaman_registrasi_menampilkan_step_otp(): void
    {
        $this->seedBasics();

        $response = $this->get(route('register.create'));

        $response->assertOk()
            ->assertSee('Kirim Kode OTP', false)
            ->assertSee('otp_code', false);
    }

    public function test_kirim_otp_membuat_record_dan_email_log(): void
    {
        $this->seedBasics();

        $response = $this->postJson(route('register.otp.send'), ['email' => 'newmember@email.com']);

        $response->assertOk()->assertJsonPath('status', 'sent');

        $this->assertDatabaseHas('otp_codes', [
            'email' => 'newmember@email.com',
            'purpose' => 'registration',
        ]);

        $otp = OtpCode::latest()->first();
        $this->assertNotNull($otp->expires_at);
        $this->assertEquals(6, strlen($otp->code));

        $this->assertDatabaseHas('email_logs', [
            'to_email' => 'newmember@email.com',
            'type' => 'otp',
        ]);
    }

    public function test_resend_otp_dithrottle_60_detik(): void
    {
        $this->seedBasics();

        $this->postJson(route('register.otp.send'), ['email' => 'spam@email.com']);
        $second = $this->postJson(route('register.otp.send'), ['email' => 'spam@email.com']);

        $second->assertOk()->assertJsonPath('status', 'throttled');
        $this->assertEquals(1, OtpCode::where('email', 'spam@email.com')->count());
    }

    public function test_verifikasi_otp_salah_dan_benar(): void
    {
        $this->seedBasics();

        $this->postJson(route('register.otp.send'), ['email' => 'verify@email.com']);
        $otp = OtpCode::latest()->first();

        // Salah
        $wrong = $this->postJson(route('register.otp.verify'), ['email' => 'verify@email.com', 'code' => '000000']);
        $wrong->assertOk()->assertJsonPath('status', 'invalid');

        // Benar
        $right = $this->postJson(route('register.otp.verify'), ['email' => 'verify@email.com', 'code' => $otp->code]);
        $right->assertOk()->assertJsonPath('status', 'verified');

        $this->assertNotNull($otp->refresh()->consumed_at);
    }

    public function test_registrasi_ditolak_tanpa_otp_valid(): void
    {
        $this->seedBasics();
        $hotel = Hotel::first();

        $payload = [
            'full_name' => 'Tanpa Otp',
            'email' => 'nootp@email.com',
            'phone' => '08111111111',
            'hotel_id' => $hotel->id,
            'membership_type' => 'free',
            'otp_code' => '123456',
        ];

        $response = $this->from(route('register.create'))->post(route('register.store'), $payload);

        $response->assertSessionHasErrors('otp_code');
        $this->assertEquals(0, Member::count());
    }

    public function test_registrasi_lengkap_dengan_otp_valid(): void
    {
        $this->seedBasics();
        $hotel = Hotel::first();

        $this->postJson(route('register.otp.send'), ['email' => 'complete@email.com']);
        $otp = OtpCode::latest()->first();

        $response = $this->post(route('register.store'), [
            'full_name' => 'Member Baru',
            'email' => 'complete@email.com',
            'phone' => '08122222222',
            'hotel_id' => $hotel->id,
            'membership_type' => 'free',
            'otp_code' => $otp->code,
        ]);

        $response->assertRedirect();

        $member = Member::where('email', 'complete@email.com')->first();
        $this->assertNotNull($member, 'Member harus tercatat');
        $this->assertEquals('active', $member->status);
        $this->assertEquals(1, $member->periods()->count(), 'Periode keanggotaan dibuat');
        $this->assertEquals(2, $member->vouchers()->count(), 'Paket voucer free (2) diterbitkan');

        // Email: welcome + vouchers_issued + security_alert
        $this->assertDatabaseHas('email_logs', ['member_id' => $member->id, 'type' => 'welcome']);
        $this->assertDatabaseHas('email_logs', ['member_id' => $member->id, 'type' => 'vouchers_issued']);
        $this->assertDatabaseHas('email_logs', ['member_id' => $member->id, 'type' => 'security_alert']);

        // OTP terkonsumsi
        $this->assertNotNull($otp->refresh()->consumed_at);
    }

    public function test_registrasi_setelah_verifikasi_ajax_berhasil(): void
    {
        // Regression: dulu verifikasi AJAX mengonsumsi OTP, lalu submit form
        // memanggil verify() lagi → selalu error "OTP tidak ditemukan".
        $this->seedBasics();
        $hotel = Hotel::first();

        $this->postJson(route('register.otp.send'), ['email' => 'ajaxflow@email.com']);
        $otp = OtpCode::latest()->first();

        // Step 1 — user verifikasi via AJAX (OTP terkonsumsi)
        $verify = $this->postJson(route('register.otp.verify'), ['email' => 'ajaxflow@email.com', 'code' => $otp->code]);
        $verify->assertOk()->assertJsonPath('status', 'verified');
        $this->assertNotNull($otp->refresh()->consumed_at);

        // Step 2 — klik "Daftar Membership" → HARUS berhasil (tidak error OTP lagi)
        $response = $this->post(route('register.store'), [
            'full_name' => 'Ajax Flow',
            'email' => 'ajaxflow@email.com',
            'phone' => '08133333333',
            'hotel_id' => $hotel->id,
            'membership_type' => 'free',
            'otp_code' => $otp->code,
        ]);

        $response->assertRedirect(route('register.success', Member::where('email', 'ajaxflow@email.com')->value('member_no')));
        $this->assertNotNull(Member::where('email', 'ajaxflow@email.com')->first());
    }

    public function test_registrasi_ditolak_jika_kode_otp_berbeda_dari_yang_terverifikasi(): void
    {
        $this->seedBasics();
        $hotel = Hotel::first();

        $this->postJson(route('register.otp.send'), ['email' => 'mismatch@email.com']);
        $otp = OtpCode::latest()->first();
        $this->postJson(route('register.otp.verify'), ['email' => 'mismatch@email.com', 'code' => $otp->code]);

        // Submit dengan kode BERBEDA → tetap ditolak
        $response = $this->from(route('register.create'))->post(route('register.store'), [
            'full_name' => 'Kode Beda',
            'email' => 'mismatch@email.com',
            'phone' => '08134444444',
            'hotel_id' => $hotel->id,
            'membership_type' => 'free',
            'otp_code' => '999999',
        ]);

        $response->assertSessionHasErrors('otp_code');
        $this->assertEquals(0, Member::where('email', 'mismatch@email.com')->count());
    }

    public function test_otp_kedaluwarsa_ditolak(): void
    {
        $this->seedBasics();

        $otp = OtpCode::create([
            'email' => 'expired@email.com',
            'code' => '111222',
            'purpose' => 'registration',
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->postJson(route('register.otp.verify'), ['email' => 'expired@email.com', 'code' => '111222']);

        $response->assertOk()->assertJsonPath('status', 'invalid')
            ->assertJsonPath('message', 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.');
    }
}
