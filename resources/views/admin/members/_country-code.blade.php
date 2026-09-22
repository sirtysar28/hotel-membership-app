@php
    $countryCodes = [
        '+62' => '🇮🇩 +62 ID', '+60' => '🇲🇾 +60 MY', '+65' => '🇸🇬 +65 SG', '+66' => '🇹🇭 +66 TH',
        '+63' => '🇵🇭 +63 PH', '+84' => '🇻🇳 +84 VN', '+855' => '🇰🇭 +855 KH', '+81' => '🇯🇵 +81 JP',
        '+82' => '🇰🇷 +82 KR', '+86' => '🇨🇳 +86 CN', '+852' => '🇭🇰 +852 HK', '+886' => '🇹🇼 +886 TW',
        '+91' => '🇮🇳 +91 IN', '+971' => '🇦🇪 +971 UEA', '+966' => '🇸🇦 +966 SA', '+61' => '🇦🇺 +61 AU',
        '+64' => '🇳🇿 +64 NZ', '+44' => '🇬🇧 +44 UK', '+49' => '🇩🇪 +49 DE', '+33' => '🇫🇷 +33 FR',
        '+31' => '🇳🇱 +31 NL', '+39' => '🇮🇹 +39 IT', '+90' => '🇹🇷 +90 TR', '+7' => '🇷🇺 +7 RU',
        '+1' => '🇺🇸 +1 US/CA',
    ];
    $ccValue = $value ?? old('phone_country_code', '+62');
@endphp
<select name="phone_country_code" title="Kode negara nomor HP" class="border border-gray-300 rounded-lg px-2 py-2.5 bg-white" style="min-width:118px">
    @foreach($countryCodes as $code => $label)
        <option value="{{ $code }}" @selected($ccValue === $code)>{{ $label }}</option>
    @endforeach
</select>
