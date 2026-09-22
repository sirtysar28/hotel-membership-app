# Hotel Ciputra Membership Management System

Aplikasi **Hotel Membership Management System** berbasis web — Laravel 11 + PHP 8.2+ + MySQL + Blade + Tailwind CSS, dibangun sesuai workflow BRD (Paid & Free/Earned Membership, Rule Engine, multi-hotel Jakarta & Semarang).

## Cara Menjalankan

```bash
cd hotel-membership-app

# 1. Konfigurasi .env (sudah diset untuk MySQL lokal: database hotel_membership, user root)
#    Buat database jika belum ada:
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS hotel_membership CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Install dependency (jika belum)
composer install

# 3. Migrate + seed data demo
php artisan migrate:fresh --seed

# 4. Jalankan server
php artisan serve
# buka http://127.0.0.1:8000
```

## Akun Demo


## Fitur Utama (sesuai workflow BRD)

1. **Dua jalur membership**
   - **Paid** — Rp2.200.000 (harga configurable di Settings) → invoice → payment → verifikasi Finance → aktivasi.
   - **Free / Earned** — langsung aktif di level Classic, naik level otomatis.
2. **Duplicate member checking** — cek otomatis via AJAX (email, phone, ID number, nama+DOB) saat registrasi; exact match diblokir dengan alert "MEMBER DATA MATCH", potensi false-positive masuk antrean **Duplicate Review** admin (field mana yang match ditampilkan).
3. **Membership Rule Engine** — threshold level (min visit / max visit / min spending / periode evaluasi) **tidak hard-coded**, diambil dari tabel `membership_rules` dan dapat diubah dari dashboard *Levels & Rules*.
4. **Visit redemption / manual input oleh staff** — cari member (ID/QR/email/phone/nama), input transaksi (Hotel Stay, Restaurant, Bar, Banquet, Other F&B), semua tercatat lengkap dengan staff, waktu, data before/after di `visit_redemptions` + `audit_logs`.
5. **Automatic level upgrade** — setiap transaksi baru → update total → Rule Engine → jika memenuhi threshold → level naik → kartu digital baru → email "Congratulations – Membership Upgrade".
6. **Digital membership card** — QR code berisi token (bukan data pribadi); staff scan QR → profil member terbuka.
7. **Email otomatis** — welcome, upgrade, transaction confirmation, renewal & expiration reminder, payment verified, reset password. Semua template memakai layout HTML profesional (`emails/layouts/master`) dengan logo, header & footer. Tercatat di `email_logs` (MAIL_MAILER=log default; konfigurasi SMTP via **Admin → Settings → SMTP**).
8. **Member portal** — dashboard (level, total visit/spending, progress ke level berikut), kartu digital, riwayat visit & transaksi, benefit/voucher, profil.
9. **Admin dashboard** — KPI (total/paid/free members, revenue), chart member growth, paid vs free, level distribution, revenue (Chart.js), distribusi per hotel, member terbaru.
10. **Reports & export** — Member, Revenue, Visit, Duplicate report; filter (hotel, type, level, status, tanggal); export **CSV & PDF**.
11. **Role & access management** — Super Admin, Hotel Admin (scoped per hotel), Membership Admin, Front Office/Staff, Finance (verifikasi payment), Management (read-only dashboard & report).
12. **Multi-hotel** — Hotel Ciputra Jakarta & Semarang; member terikat `hotel_id`; hotel admin hanya melihat data hotelnya.
13. **Audit trail** — login, registrasi, perubahan status member, verifikasi payment, redeem visit, perubahan rule, dst.
14. **Captcha 5 karakter** — halaman login & lupa password dilindungi captcha gambar (GD, tanpa dependency), klik gambar untuk mengganti kode.
15. **Lupa password** — `/forgot-password` → email link reset (berlaku 60 menit) → `/reset-password/{token}` → password baru. Terintegrasi audit log & email logs.
16. **Pengaturan SMTP via dashboard** — Admin → Settings: mailer (log/smtp), host, port, enkripsi (TLS/SSL/none), username, password (tersimpan terenkripsi), From Address/Name + tombol **Test Email**.
17. **Dashboard statistik interaktif** — 6 chart Chart.js (grouped bar, 3 donut dengan teks tengah, area, horizontal bar). Chart.js di-self-host di `public/js/` (tanpa CDN).
18. **Sidebar collapse & responsive** — desktop: minimize ke ikon (persist localStorage); mobile: off-canvas drawer + backdrop.
19. **Toggle intip password** — ikon mata di semua input password.
20. **Landing page animasi** — parallax blobs, gradient animasi, scroll reveal (IntersectionObserver), count-up stats, floating cards, wave divider; `prefers-reduced-motion` friendly.
21. **PWA** — manifest (`public/manifest.webmanifest`), service worker (`public/sw.js`, network-first utk halaman + offline fallback `offline.html`, stale-while-revalidate utk aset), ikon 192/512/maskable + apple-touch, shortcuts Login & Daftar.
22. **Sistem Voucer (CPC v2.0)** — jenis voucer & komposisi paket configurable (tabel `voucher_types` / `voucher_packages`, tanpa hard-code; default Diamond = 12 voucer, free = paket registrasi). Setiap instans punya **ID Voucer unik** format `VCH-YYPPPP-NNNN`. Voucer terbit otomatis saat aktivasi & perpanjangan Diamond, berlaku mengikuti masa periode keanggotaan (tidak bisa diakumulasikan), dan ditandai EXPIRED otomatis oleh scheduler harian (`cpc:process`, jam 00:30).
23. **Alur penukaran voucer dua tingkat** — staf mengajukan (cari by ID Voucer/member → PENDING_APPROVAL) → **manajer menyetujui** (REDEEMED + email notifikasi ke member) / **menolak dengan alasan wajib** (voucer kembali AVAILABLE, riwayat penolakan terjaga). Staf/pengaju tidak dapat menyetujui sendiri; antrean approval ada badge jumlah pending di sidebar admin.
24. **Periode keanggotaan (multi-periode)** — setiap aktivasi/perpanjangan membuat baris `membership_periods` (contoh #1: 22 Sep 2026 – 21 Sep 2027); riwayat periode + harga tampil di detail member admin. Diamond tidak diperpanjang → otomatis expired → konversi Signature (§7); tier gratis turun satu level setelah 12 bulan tanpa aktivitas (§8).
25. **OTP verifikasi email saat registrasi** (anti-spambot) — kode 6 digit berlaku 10 menit, maks 5 percobaan, resend dibatasi 60 detik; form registrasi memblokir submit sebelum email terverifikasi dan server memverifikasi ulang saat submit.
26. **Notifikasi keamanan** — email security alert ke member saat aktivitas penting (registrasi/aktivasi membership).

## Struktur Penting

```
app/
├── Http/Controllers/
│   ├── Admin/          # Dashboard, Member, Payment, DuplicateCheck, Level (Rule Engine),
│   │                   # Benefit, Hotel, User, Report, Log, Setting, Voucher (voucer + approval)
│   ├── StaffController.php   # search member, QR scan, redeem visit, ajukan penukaran voucer
│   ├── PortalController.php  # member portal (dashboard, card, visits, transactions, vouchers, benefit, profil)
│   ├── RegistrationController.php  # registrasi publik + AJAX duplicate check + OTP email
│   └── AuthController.php
├── Models/             # Hotel, Member, MembershipLevel/Rule/Benefit/Period, MemberBenefit,
│                       # Transaction, Visit, VisitRedemption, Invoice, Payment,
│                       # DigitalCard, DuplicateCheck, AuditLog, EmailLog, Setting, User,
│                       # VoucherType/Package/PackageItem/Voucher, RedemptionRequest, OtpCode
├── Services/
│   ├── DuplicateCheckService.php  # logic pencocokan (match/similar/different)
│   ├── MembershipService.php      # registrasi, member ID, kartu, payment, redeem, periode, renewal
│   ├── LevelEngine.php            # Rule Engine: evaluasi, upgrade, progress, downgrade inaktif
│   ├── VoucherService.php         # penerbitan paket voucer, alur approval, expire, pembatalan
│   ├── OtpService.php             # OTP registrasi (kirim/verifikasi/throttle)
│   └── EmailService.php           # email otomatis + logging
database/migrations/    # users, hotels, members, levels/rules/benefits,
                       # transactions/visits/redemptions, invoices/payments,
                       # digital_cards, duplicate_checks, audit_logs, email_logs, settings,
                       # membership_periods, voucher_tables (types/packages/vouchers/redemption_requests),
                       # otp_codes
tests/Feature/         # OtpRegistrationTest (alur OTP + registrasi),
                       # VoucherFlowTest (ajukan → setujui/tolak, hak akses, portal, pembatalan)
resources/views/        # layouts (guest/admin/portal), halaman admin/staff/portal, emails
```

## Status Pembayaran

Pending → Payment Submitted → **Paid** / Failed / Expired / Refunded / Cancelled

## Catatan

- Default prefix Member ID: `HCM` (contoh: `HCM-000001`) — configurable di Settings.
- Membership berlaku 1 tahun (configurable di Settings: `membership_validity_months`).
- Scheduler otomatisasi (`php artisan schedule:work` di produksi): expire voucer, Diamond expired → Signature, downgrade inaktif, reminder perpanjangan (3 bln/1 bln/2 mgg). Bisa dijalankan manual: `php artisan cpc:process` (opsi `--dry` utk simulasi).
- Menjalankan test: `composer test` atau `./vendor/bin/phpunit` (DB test: `hotel_membership_test`; deprecation PDO PHP 8.5 di-redam via `phpunit-baseline.xml`).
- Email menggunakan `MAIL_MAILER=log`; cek `storage/logs/laravel.log` atau halaman **Email Logs**. Untuk pengiriman nyata: **Admin → Settings → Pengaturan SMTP** (di-apply runtime dari database, tidak perlu edit .env).
- Logo aplikasi & email: `public/images/logo.png`, `logo-header.png`, `logo-header-light.png`. Untuk logo tampil benar di email, set `APP_URL=https://hotel-membership.trijayasolution.com` di .env produksi.
- Untuk produksi: set APP_ENV=production, APP_DEBUG=false, konfigurasi SMTP, dan ganti semua password demo.
# hotel-membership-app

