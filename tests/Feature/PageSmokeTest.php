<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function seedQuick(): void
    {
        $this->artisan('migrate:fresh', ['--force' => true]);
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_all_member_portal_pages_render(): void
    {
        $this->seedQuick();

        $member = User::where('email', 'john@email.com')->first();

        $pages = [
            '/portal',
            '/portal/card',
            '/portal/visits',
            '/portal/transactions',
            '/portal/vouchers',
            '/portal/benefits',
            '/portal/profile',
        ];

        foreach ($pages as $url) {
            $response = $this->actingAs($member)->get($url);
            $this->assertSame(200, $response->status(), "Halaman {$url} harus 200, dapat {$response->status()}");
        }
    }

    public function test_all_staff_pages_render(): void
    {
        $this->seedQuick();

        $staff = User::where('email', 'fo.jakarta@hotelciputra.test')->first();

        $pages = [
            '/staff',
            '/staff/vouchers',
        ];

        foreach ($pages as $url) {
            $response = $this->actingAs($staff)->get($url);
            $this->assertSame(200, $response->status(), "Halaman {$url} harus 200, dapat {$response->status()}");
        }

        // /staff/search & /staff/scan adalah handler redirect (by-design)
        $this->actingAs($staff)->get('/staff/search?q=john')
            ->assertRedirect('/staff?q=john');
        $this->actingAs($staff)->get('/staff/scan?t=invalid-token')
            ->assertRedirect('/staff');
    }

    public function test_all_admin_pages_render(): void
    {
        $this->seedQuick();

        $superAdmin = User::where('email', 'superadmin@hotelciputra.test')->first();

        $pages = [
            '/admin/dashboard',
            '/admin/members',
            '/admin/members/create',
            '/admin/members/1',
            '/admin/members/1/edit',
            '/admin/members/1/card',
            '/admin/duplicates',
            '/admin/levels',
            '/admin/benefits',
            '/admin/benefits/create',
            '/admin/vouchers',
            '/admin/redemptions',
            '/admin/payments',
            '/admin/reports/members',
            '/admin/reports/revenue',
            '/admin/reports/visits',
            '/admin/reports/duplicates',
            '/admin/hotels',
            '/admin/hotels/create',
            '/admin/users',
            '/admin/users/create',
            '/admin/audit-logs',
            '/admin/email-logs',
            '/admin/settings',
        ];

        foreach ($pages as $url) {
            $response = $this->actingAs($superAdmin)->get($url);
            $this->assertSame(200, $response->status(), "Halaman {$url} harus 200, dapat {$response->status()}");
        }
    }

    public function test_full_member_login_flow_with_captcha(): void
    {
        $this->seedQuick();

        // Buka halaman login
        $this->get('/login')->assertOk();

        // Login member demo dengan captcha yang benar (disimulasikan via session)
        $member = User::where('email', 'john@email.com')->first();

        $response = $this->withSession(['captcha_code' => 'ABCDE'])
            ->post('/login', [
                'email' => 'john@email.com',
                'password' => 'password123',
                'captcha' => 'abcde',
            ]);

        $response->assertRedirect('/portal');
        $this->assertAuthenticatedAs($member);
    }

    public function test_public_registration_creates_working_portal_login(): void
    {
        $this->seedQuick();

        // Registrasi publik lengkap: OTP verified (consumed_at terisi) + password portal buatan member
        \App\Models\OtpCode::create([
            'email' => 'newmember@email.com',
            'code' => '123456',
            'purpose' => 'registration',
            'consumed_at' => now(),
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->post('/register', [
            'full_name' => 'New Member Test',
            'email' => 'newmember@email.com',
            'phone' => '081200011122',
            'otp_code' => '123456',
            'hotel_id' => 1,
            'membership_type' => 'free',
            'portal_password' => 'rahasia123',
            'portal_password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect();

        // Akun portal langsung tersedia dengan password buatan member
        $user = User::where('email', 'newmember@email.com')->first();
        $this->assertNotNull($user, 'Akun portal harus dibuat saat registrasi');
        $this->assertSame('member', $user->role);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('rahasia123', $user->password));

        // Member baru bisa langsung login via halaman login (captcha disimulasikan)
        $this->post('/logout');
        $login = $this->withSession(['captcha_code' => 'ABCDE'])->post('/login', [
            'email' => 'newmember@email.com',
            'password' => 'rahasia123',
            'captcha' => 'abcde',
        ]);

        $login->assertRedirect('/portal');
        $this->assertAuthenticatedAs($user);
    }
}
