<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Hotel;
use App\Models\Member;
use App\Models\MembershipBenefit;
use App\Models\MembershipLevel;
use App\Models\MembershipRule;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- Settings ----------
        Setting::set('paid_membership_price', 2200000);
        Setting::set('member_no_prefix', 'HCM');
        Setting::set('membership_validity_years', 1);
        Setting::set('membership_validity_months', 12);

        // ---------- Hotels ----------
        $jakarta = Hotel::create([
            'code' => 'HCJ', 'name' => 'Hotel Ciputra Jakarta', 'city' => 'Jakarta',
            'address' => 'Jl. Artha Sudirman Kav. 7, Jakarta', 'phone' => '021-50108888', 'email' => 'info.jakarta@hotelciputra.com',
        ]);
        $semarang = Hotel::create([
            'code' => 'HCS', 'name' => 'Hotel Ciputra Semarang', 'city' => 'Semarang',
            'address' => 'Jl. Pemuda No. 111, Semarang', 'phone' => '024-3540000', 'email' => 'info.semarang@hotelciputra.com',
        ]);

        // ---------- Levels & Rules (Rule Engine) ----------
        $levels = [
            ['code' => 'classic', 'name' => 'Classic', 'sort_order' => 1, 'card_color' => '#64748b', 'description' => 'Level awal member gratis', 'rule' => ['min_visit' => 0, 'max_visit' => 25, 'min_spending' => 0, 'evaluation_period_months' => 0]],
            ['code' => 'privilege', 'name' => 'Privilege', 'sort_order' => 2, 'card_color' => '#1e6bb8', 'description' => '26–50 visit', 'rule' => ['min_visit' => 26, 'max_visit' => 50, 'min_spending' => 0, 'evaluation_period_months' => 0]],
            ['code' => 'signature', 'name' => 'Signature', 'sort_order' => 3, 'card_color' => '#0f2a4a', 'description' => '51+ visit', 'rule' => ['min_visit' => 51, 'max_visit' => null, 'min_spending' => 0, 'evaluation_period_months' => 0]],
            ['code' => 'diamond', 'name' => 'Diamond', 'sort_order' => 4, 'card_color' => '#7c3aed', 'description' => 'Berdasarkan spending / ketentuan khusus', 'rule' => ['min_visit' => 0, 'max_visit' => null, 'min_spending' => 250000000, 'evaluation_period_months' => 0]],
        ];
        foreach ($levels as $level) {
            $rule = $level['rule'];
            unset($level['rule']);
            $levelModel = MembershipLevel::create($level);
            MembershipRule::create([...$rule, 'level_id' => $levelModel->id, 'is_active' => true]);
        }
        $classic = MembershipLevel::where('code', 'classic')->first();
        $privilege = MembershipLevel::where('code', 'privilege')->first();
        $signature = MembershipLevel::where('code', 'signature')->first();
        $diamond = MembershipLevel::where('code', 'diamond')->first();

        // ---------- Benefits ----------
        $benefits = [
            ['name' => 'Digital Membership Card', 'category' => 'other', 'description' => 'Kartu member digital dengan QR code', 'value' => null, 'level_id' => null],
            ['name' => 'Welcome Drink', 'category' => 'fnb', 'description' => 'Welcome drink setiap check-in', 'value' => '2 pax', 'level_id' => $classic->id],
            ['name' => 'Discount Restaurant', 'category' => 'discount', 'description' => 'Disket resto all outlet', 'value' => '10%', 'level_id' => $classic->id],
            ['name' => 'Discount Kamar', 'category' => 'room', 'description' => 'Diskon best available rate', 'value' => '10%', 'level_id' => $privilege->id],
            ['name' => 'Room Upgrade', 'category' => 'complimentary', 'description' => 'Upgrade tipe kamar subjek ketersediaan', 'value' => '1x', 'level_id' => $privilege->id],
            ['name' => 'Voucher F&B', 'category' => 'voucher', 'description' => 'Voucher F&B setiap bulan', 'value' => 'Rp250.000/bln', 'level_id' => $signature->id],
            ['name' => 'Early Check-in / Late Check-out', 'category' => 'complimentary', 'description' => 'ECI 12.00 / LCO 15.00', 'value' => null, 'level_id' => $signature->id],
            ['name' => 'Discount Kamar', 'category' => 'room', 'description' => 'Diskon best available rate', 'value' => '20%', 'level_id' => $diamond->id],
            ['name' => 'Complimentary Room Night', 'category' => 'complimentary', 'description' => '1 malam gratis per tahun', 'value' => '1x/thn', 'level_id' => $diamond->id],
            ['name' => 'Airport Pickup', 'category' => 'complimentary', 'description' => 'Antar-jemput bandara', 'value' => '1x/thn', 'level_id' => $diamond->id],
        ];
        foreach ($benefits as $b) {
            MembershipBenefit::create([...$b, 'is_active' => true]);
        }

        // ---------- Voucher master data (CPC v2.0 §4, §5 — configurable) ----------
        $voucherTypes = [
            ['code' => 'breakfast', 'name' => 'Voucer Sarapan', 'description' => 'Sarapan gratis utk 1 orang', 'sort_order' => 1],
            ['code' => 'cl', 'name' => 'Complimentary Letter (CL)', 'description' => 'Complimentary utk menu tertentu', 'sort_order' => 2],
            ['code' => 'gym', 'name' => 'Voucer Gym', 'description' => 'Akses gym 1x', 'sort_order' => 3],
            ['code' => 'room_upgrade', 'name' => 'Voucer Room Upgrade', 'description' => 'Upgrade tipe kamar subjek ketersediaan', 'sort_order' => 4],
            ['code' => 'swimming_pool', 'name' => 'Voucer Swimming Pool', 'description' => 'Akses kolam renang 1x', 'sort_order' => 5],
            ['code' => 'room', 'name' => 'Complimentary Room Night', 'description' => '1 malam menginap gratis', 'sort_order' => 6],
        ];
        foreach ($voucherTypes as $vt) {
            \App\Models\VoucherType::create([...$vt, 'is_active' => true]);
        }
        $typeId = fn (string $code) => \App\Models\VoucherType::where('code', $code)->value('id');

        // Paket Diamond (paid) — total 12 voucer (komposisi contoh CPC v2.0 §21, tetap configurable)
        $diamondPackage = \App\Models\VoucherPackage::create([
            'name' => 'Paket Diamond 12 Voucer', 'applies_to' => 'paid', 'is_default' => true, 'is_active' => true,
        ]);
        foreach ([
            ['breakfast', 2], ['cl', 1], ['gym', 2], ['room_upgrade', 3], ['swimming_pool', 2], ['room', 2],
        ] as [$code, $qty]) {
            \App\Models\VoucherPackageItem::create([
                'voucher_package_id' => $diamondPackage->id, 'voucher_type_id' => $typeId($code), 'qty' => $qty,
            ]);
        }

        // Paket registrasi gratis — juga 12 voucer (update #17: setiap pendaftaran
        // paid MAUPUN free menerima 12 voucer yang bisa di-redeem)
        $freePackage = \App\Models\VoucherPackage::create([
            'name' => 'Paket Registrasi Free 12 Voucer', 'applies_to' => 'free', 'is_default' => true, 'is_active' => true,
        ]);
        foreach ([
            ['breakfast', 2], ['cl', 1], ['gym', 2], ['room_upgrade', 3], ['swimming_pool', 2], ['room', 2],
        ] as [$code, $qty]) {
            \App\Models\VoucherPackageItem::create([
                'voucher_package_id' => $freePackage->id, 'voucher_type_id' => $typeId($code), 'qty' => $qty,
            ]);
        }

        // ---------- Users (semua role) ----------
        $users = [
            ['name' => 'Super Admin', 'email' => 'superadmin@hotelciputra.test', 'role' => 'super_admin', 'hotel_id' => null],
            ['name' => 'Admin Jakarta', 'email' => 'jakarta.admin@hotelciputra.test', 'role' => 'hotel_admin', 'hotel_id' => $jakarta->id],
            ['name' => 'Admin Semarang', 'email' => 'semarang.admin@hotelciputra.test', 'role' => 'hotel_admin', 'hotel_id' => $semarang->id],
            ['name' => 'Membership Admin', 'email' => 'membership@hotelciputra.test', 'role' => 'membership_admin', 'hotel_id' => null],
            ['name' => 'Front Office Jakarta', 'email' => 'fo.jakarta@hotelciputra.test', 'role' => 'staff', 'hotel_id' => $jakarta->id],
            ['name' => 'Front Office Semarang', 'email' => 'fo.semarang@hotelciputra.test', 'role' => 'staff', 'hotel_id' => $semarang->id],
            ['name' => 'Finance', 'email' => 'finance@hotelciputra.test', 'role' => 'finance', 'hotel_id' => null],
            ['name' => 'Management', 'email' => 'management@hotelciputra.test', 'role' => 'management', 'hotel_id' => null],
            ['name' => 'Manager Jakarta', 'email' => 'manager.jakarta@hotelciputra.test', 'role' => 'manager', 'hotel_id' => $jakarta->id],
            ['name' => 'Manager Semarang', 'email' => 'manager.semarang@hotelciputra.test', 'role' => 'manager', 'hotel_id' => $semarang->id],
        ];
        foreach ($users as $u) {
            User::create([...$u, 'password' => 'password']);
        }
        $superAdmin = User::where('email', 'superadmin@hotelciputra.test')->first();
        $staffJakarta = User::where('email', 'fo.jakarta@hotelciputra.test')->first();
        $staffSemarang = User::where('email', 'fo.semarang@hotelciputra.test')->first();
        $managerJakarta = User::where('email', 'manager.jakarta@hotelciputra.test')->first();

        // ---------- Demo members ----------
        $service = app(MembershipService::class);
        $engine = app(\App\Services\LevelEngine::class);

        $demo = [
            ['full_name' => 'John Doe', 'email' => 'john@email.com', 'phone' => '08123456789', 'dob' => '1985-06-15', 'gender' => 'male', 'id_type' => 'ktp', 'id_number' => '3172015506850001', 'company' => 'PT Maju Bersama', 'occupation' => 'Manager', 'hotel' => $jakarta, 'type' => 'free', 'visits' => 52, 'spend' => 78500000],
            ['full_name' => 'Jane Smith', 'email' => 'jane@email.com', 'phone' => '08129876543', 'dob' => '1990-11-20', 'gender' => 'female', 'id_type' => 'passport', 'id_number' => 'X1234567', 'company' => 'Widjaja Group', 'occupation' => 'Director', 'hotel' => $jakarta, 'type' => 'paid', 'visits' => 24, 'spend' => 18500000],
            ['full_name' => 'Budi Santoso', 'email' => 'budi@email.com', 'phone' => '081511122233', 'dob' => '1978-03-08', 'gender' => 'male', 'id_type' => 'ktp', 'id_number' => '3172010303780002', 'company' => 'CV Sinar Jaya', 'occupation' => 'Owner', 'hotel' => $semarang, 'type' => 'free', 'visits' => 30, 'spend' => 42000000],
            ['full_name' => 'Siti Rahayu', 'email' => 'siti@email.com', 'phone' => '081633344455', 'dob' => '1992-09-12', 'gender' => 'female', 'id_type' => 'ktp', 'id_number' => '3172091209920003', 'company' => null, 'occupation' => 'Dokter', 'hotel' => $semarang, 'type' => 'paid', 'visits' => 15, 'spend' => 9600000],
            ['full_name' => 'Robert Wijaya', 'email' => 'robert@email.com', 'phone' => '081177788899', 'dob' => '1970-01-25', 'gender' => 'male', 'id_type' => 'ktp', 'id_number' => '3172012501700004', 'company' => 'Wijaya Corp', 'occupation' => 'CEO', 'hotel' => $jakarta, 'type' => 'free', 'visits' => 75, 'spend' => 320000000],
            ['full_name' => 'Dewi Lestari', 'email' => 'dewi@email.com', 'phone' => '081822233344', 'dob' => '1988-07-30', 'gender' => 'female', 'id_type' => 'ktp', 'id_number' => '3172073007880005', 'company' => 'PT Kreatif Nusantara', 'occupation' => 'Marketing', 'hotel' => $jakarta, 'type' => 'free', 'visits' => 10, 'spend' => 7200000],
        ];

        foreach ($demo as $d) {
            $hotel = $d['hotel'];
            $member = $service->registerMember([
                'full_name' => $d['full_name'],
                'email' => $d['email'],
                'phone' => $d['phone'],
                'dob' => $d['dob'],
                'gender' => $d['gender'],
                'id_type' => $d['id_type'],
                'id_number' => $d['id_number'],
                'company' => $d['company'],
                'occupation' => $d['occupation'],
                'hotel_id' => $hotel->id,
                'membership_type' => $d['type'],
            ]);

            // Untuk member paid demo: verifikasi payment otomatis
            if ($d['type'] === 'paid') {
                $payment = $member->payments->first();
                $payment->update(['status' => 'paid', 'verified_by' => $superAdmin->id, 'verified_at' => now(), 'paid_at' => now(), 'method' => 'transfer', 'reference_no' => 'TRF-DEMO-' . $member->id]);
                $payment->invoice->update(['status' => 'paid']);
                $service->activate($member);
            }

            // Buat transaksi & visit historis sesuai target visits/spending
            $staff = $hotel->id === $jakarta->id ? $staffJakarta : $staffSemarang;
            $perVisitSpend = $d['visits'] > 0 ? round($d['spend'] / $d['visits'] / 1000) * 1000 : 0;
            for ($i = $d['visits']; $i >= 1; $i--) {
                $date = now()->subDays($i * 7)->toDateString();
                $typePool = ['hotel_stay', 'hotel_stay', 'restaurant', 'bar', 'banquet', 'other_fnb'];
                $type = $typePool[array_rand($typePool)];
                $service->redeemVisit($member->refresh(), [
                    'visit_date' => $date,
                    'hotel_id' => $hotel->id,
                    'type' => $type,
                    'outlet' => $type === 'hotel_stay' ? 'Front Office' : ($type === 'restaurant' ? 'Cipta Restaurant' : ($type === 'bar' ? 'Sky Bar' : 'Ballroom')),
                    'amount' => max(50000, $perVisitSpend + random_int(-200000, 400000)),
                    'counts_as_visit' => true,
                    'notes' => 'Data demo',
                ], $staff);
            }
        }

        // Satu member paid yang masih menunggu pembayaran (untuk demo verifikasi)
        $pending = $service->registerMember([
            'full_name' => 'Andi Pratama',
            'email' => 'andi@email.com',
            'phone' => '081955566677',
            'dob' => '1995-04-18',
            'gender' => 'male',
            'id_type' => 'ktp',
            'id_number' => '3172041804950006',
            'company' => 'PT Teknologi Indonesia',
            'occupation' => 'Software Engineer',
            'hotel_id' => $jakarta->id,
            'membership_type' => 'paid',
        ]);
        $pending->payments->first()->update(['status' => 'payment_submitted', 'method' => 'transfer', 'reference_no' => 'TRF-889912']);

        // Password portal member demo (produksi: kirim via email / lupa password)
        User::where('role', 'member')->get()->each(fn ($u) => $u->update(['password' => 'password123']));

        // Contoh potential duplicate untuk review admin
        \App\Models\DuplicateCheck::create([
            'submitted_data' => [
                'full_name' => 'Jhon Doe', // typo John Doe
                'email' => 'john.doe@other-email.com',
                'phone' => '08123456789',
                'id_number' => '3172015506859999',
                'dob' => '1985-06-15',
            ],
            'matched_member_id' => Member::where('email', 'john@email.com')->first()?->id,
            'matched_fields' => ['name' => 'similar', 'email' => 'different', 'phone' => 'match', 'dob' => 'match'],
            'status' => 'potential_duplicate',
        ]);

        // ---------- Demo alur penukaran voucer (CPC §10) ----------
        $voucherService = app(\App\Services\VoucherService::class);

        // 1 permintaan MENUNGGU persetujuan (Jane Smith — Diamond Jakarta)
        $janeVoucher = Member::where('email', 'jane@email.com')->first()
            ?->vouchers()->where('status', \App\Models\Voucher::STATUS_AVAILABLE)->first();
        if ($janeVoucher) {
            try {
                $voucherService->requestRedemption($janeVoucher, $staffJakarta, $jakarta->id, 'Cipta Restaurant', 'Breakfast voucher — meja 12');
            } catch (\Throwable) {
            }
        }

        // 1 penukaran sudah DISETUJUI (riwayat) — Robert Wijaya
        $robertVoucher = Member::where('email', 'robert@email.com')->first()
            ?->vouchers()->where('status', \App\Models\Voucher::STATUS_AVAILABLE)->first();
        if ($robertVoucher) {
            try {
                $req = $voucherService->requestRedemption($robertVoucher, $staffJakarta, $jakarta->id, 'Sky Bar');
                $voucherService->approveRedemption($req, $managerJakarta);
            } catch (\Throwable) {
            }
        }

        AuditLog::record('seeding', null, null, 'Database seeded dengan data demo');
    }
}
