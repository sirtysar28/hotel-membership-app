<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * Helper branding (logo & favicon) yang bisa diganti dari Admin → Settings.
 *
 * File custom disimpan di disk `public` (storage/app/public/branding/…)
 * dan dipublikasikan via symlink public/storage. Jika setting belum ada
 * (atau filenya hilang), otomatis fallback ke aset bawaan aplikasi.
 */
class Brand
{
    /** Aset bawaan aplikasi (dipakai jika belum ada upload custom). */
    private const DEFAULTS = [
        'logo_login' => 'images/logo-header-light.png',
        'logo_landing' => 'images/logo.png',
        'logo_admin' => 'images/logo.png',
        'favicon' => 'favicon.ico',
        'favicon_32' => 'favicon-32.png',
        'apple_touch_icon' => 'icons/apple-touch-icon.png',
    ];

    /** Key branding yang dikelola di halaman Settings. */
    public const MANAGED_KEYS = ['logo_login', 'logo_landing', 'logo_admin', 'favicon'];

    /**
     * URL aset branding. Prioritas: upload custom → aset bawaan.
     * Query ?v= ditambahkan agar browser tidak memakai cache lama
     * setelah logo/favicon diganti.
     */
    public static function url(string $key): string
    {
        $path = Setting::get($key);

        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path) . '?v=' . self::version($path);
        }

        return asset(self::DEFAULTS[$key] ?? 'images/logo.png');
    }

    /** URL logo untuk halaman tertentu: login|landing|admin. */
    public static function logo(string $where): string
    {
        return self::url('logo_' . $where);
    }

    /** URL favicon utama (ico/png custom, atau favicon.ico bawaan). */
    public static function favicon(): string
    {
        return self::url('favicon');
    }

    /** URL favicon 32x32 — pakai custom jika ada. */
    public static function favicon32(): string
    {
        return self::hasCustom('favicon') ? self::url('favicon') : asset(self::DEFAULTS['favicon_32']);
    }

    /** URL apple-touch-icon — pakai custom jika ada. */
    public static function appleTouchIcon(): string
    {
        return self::hasCustom('favicon') ? self::url('favicon') : asset(self::DEFAULTS['apple_touch_icon']);
    }

    /** Apakah ada favicon/logo custom yang aktif untuk key tersebut. */
    public static function hasCustom(string $key): bool
    {
        $path = Setting::get($key);

        return $path !== null && Storage::disk('public')->exists($path);
    }

    /**
     * MIME type aset custom (untuk atribut type= pada <link>).
     * Null jika tidak dikenali — biarkan browser mendeteksi sendiri.
     */
    public static function mime(string $key): ?string
    {
        $path = Setting::get($key);

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            default => null,
        };
    }

    /** Simpan file upload ke disk public dan update setting. */
    public static function store(string $key, \Illuminate\Http\UploadedFile $file): string
    {
        $name = $key . '_' . now()->format('Ymd_His') . '.' . strtolower($file->getClientOriginalExtension());

        $file->storeAs('branding', $name, 'public');

        // Hapus file lama agar storage tidak menumpuk
        self::deleteFile(Setting::get($key));

        $path = 'branding/' . $name;
        Setting::set($key, $path);

        return $path;
    }

    /** Hapus setting + file branding custom untuk key tertentu. */
    public static function remove(string $key): void
    {
        self::deleteFile(Setting::get($key));
        Setting::set($key, null);
    }

    private static function deleteFile(?string $path): void
    {
        if ($path && str_starts_with($path, 'branding/') && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /** Versi cache-buster berdasarkan waktu modifikasi file. */
    private static function version(string $path): string
    {
        try {
            return (string) Storage::disk('public')->lastModified($path);
        } catch (\Throwable) {
            return '1';
        }
    }
}
