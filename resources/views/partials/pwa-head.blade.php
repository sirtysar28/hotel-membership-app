{{-- Meta & link PWA — include di <head> semua layout --}}
{{-- Favicon bisa diganti dari Admin → Settings (Branding) --}}
@php
    $faviconMime = \App\Support\Brand::mime('favicon');
    $hasCustomFavicon = \App\Support\Brand::hasCustom('favicon');
@endphp
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="theme-color" content="#0f2a4a">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="CPC">
@if ($hasCustomFavicon)
    <link rel="icon" {{ $faviconMime ? 'type="' . $faviconMime . '"' : '' }} href="{{ \App\Support\Brand::favicon() }}">
@else
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
@endif
<link rel="apple-touch-icon" href="{{ \App\Support\Brand::appleTouchIcon() }}">
