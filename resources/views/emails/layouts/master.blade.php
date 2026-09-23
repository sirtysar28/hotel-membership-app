@php
    // URL aplikasi utk asset email: prioritaskan APP_URL (jika sudah dikonfigurasi benar),
    // fallback ke host request saat ini (untuk CLI/queue pakai config).
    $appUrl = rtrim(config('app.url'), '/');
    if ($appUrl === '' || $appUrl === 'http://localhost' || $appUrl === 'http://127.0.0.1') {
        $appUrl = rtrim(url('/'), '/');
    }

    // Logo email mengikuti Branding (Admin → Settings): logo halaman login
    // (lockup utk latar terang). Host disamakan dgn $appUrl karena email
    // dirender dari CLI/queue sehingga asset() bisa menghasilkan localhost.
    $logoUrl = \App\Support\Brand::url('logo_login');
    $logoParts = parse_url($logoUrl);
    $logoUrl = $appUrl . ($logoParts['path'] ?? '')
        . (isset($logoParts['query']) ? '?' . $logoParts['query'] : '');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>@yield('title')</title>
</head>
<body style="margin:0; padding:0; background:#eef1f5; font-family: 'Segoe UI', Arial, Helvetica, sans-serif; -webkit-font-smoothing:antialiased;">

    {{-- Preheader (teks preview di inbox) --}}
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all;">@yield('preheader', 'Pesan dari Ciputra Premiere Club (CPC)')</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef1f5; padding:24px 12px;">
        <tr>
            <td align="center">

                {{-- Logo di atas kartu --}}
                <table role="presentation" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
                    <tr>
                        <td align="center" style="padding-bottom:4px;">
                            <img src="{{ $logoUrl }}" alt="Ciputra Premiere Club (CPC)" width="220" style="display:block; border:0; outline:none; text-decoration:none; width:220px; max-width:100%; height:auto;">
                        </td>
                    </tr>
                </table>

                {{-- Kartu utama --}}
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:600px; max-width:100%; background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 2px 8px rgba(10,28,50,0.10);">

                    {{-- Header navy dengan aksen emas --}}
                    <tr>
                        <td style="background:#0f2a4a; padding:6px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr><td style="background:#fbbf24; height:4px; font-size:0; line-height:0;">&nbsp;</td></tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#0f2a4a; padding:20px 40px 24px; text-align:center;">
                            <div style="color:#fbbf24; font-size:11px; letter-spacing:4px; font-weight:bold; text-transform:uppercase;">@yield('eyebrow', 'Ciputra Premiere Club')</div>
                            <h1 style="color:#ffffff; font-size:22px; font-weight:bold; margin:10px 0 0; line-height:1.35;">@yield('title')</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:@hasSection('accent') @yield('accent') @else #fbbf24 @endif; height:4px; font-size:0; line-height:0;">&nbsp;</td>
                    </tr>

                    {{-- Konten --}}
                    <tr>
                        <td style="padding:36px 40px 8px; font-size:14px; color:#374151; line-height:1.7;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Tombol aksi (opsional) --}}
                    @hasSection('action_label')
                    <tr>
                        <td align="center" style="padding:28px 40px 36px;">
                            <a href="@yield('action_url')" style="display:inline-block; background:#0f2a4a; color:#ffffff; text-decoration:none; font-weight:bold; font-size:14px; padding:14px 36px; border-radius:8px; border-bottom:3px solid #fbbf24;">
                                @yield('action_label')
                            </a>
                            <div style="color:#9ca3af; font-size:11px; margin-top:14px; line-height:1.6;">
                                Jika tombol tidak berfungsi, salin &amp; tempel link berikut ke browser Anda:<br>
                                <a href="@yield('action_url')" style="color:#1e6bb8; word-break:break-all;">@yield('action_url')</a>
                            </div>
                        </td>
                    </tr>
                    @endif

                    {{-- Divider + greeting penutup --}}
                    <tr>
                        <td style="padding:0 40px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr><td style="border-top:1px solid #e5e7eb; font-size:0; line-height:0;">&nbsp;</td></tr>
                            </table>
                            <p style="color:#6b7280; font-size:13px; margin:18px 0 0;">@yield('closing', 'Terima kasih atas kepercayaan Anda.')<br><strong style="color:#0f2a4a;">Ciputra Premiere Club (CPC) Team</strong></p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#0a1c32; padding:26px 40px; text-align:center;">
                            <div style="color:#dbeefe; font-size:13px; font-weight:bold; letter-spacing:1px;">CIPUTRA PREMIERE CLUB</div>
                            <div style="color:#fbbf24; font-size:10px; letter-spacing:3px; margin-top:2px;">CPC &bull; JAKARTA &bull; SEMARANG</div>
                            <div style="border-top:1px solid rgba(255,255,255,0.12); margin:16px 0 12px; font-size:0; line-height:0;">&nbsp;</div>
                            <p style="color:#93a8c4; font-size:11px; margin:0 0 6px; line-height:1.7;">
                                Email otomatis dari sistem Ciputra Premiere Club (CPC).<br>
                                Mohon tidak membalas email ini secara langsung.
                            </p>
                            <p style="color:#64788f; font-size:11px; margin:0;">
                                &copy; {{ date('Y') }} Ciputra Premiere Club (CPC). All rights reserved.<br>
                                Developed by <a href="https://trijayasolution.com" target="_blank" rel="noopener" style="color:#fbbf24; text-decoration:none; font-weight:bold;">Trijaya Solution</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
