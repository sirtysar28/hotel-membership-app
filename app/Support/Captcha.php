<?php

namespace App\Support;

use Illuminate\Http\Response;

/**
 * CAPTCHA 5 karakter (image PNG via GD, tanpa dependency eksternal).
 * Kode disimpan di session dan diverifikasi case-insensitive (one-time use).
 */
class Captcha
{
    /** Karakter tanpa ambigu (tanpa I, O, 0, 1) */
    private const CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public const LENGTH = 5;

    public static function generateCode(int $length = self::LENGTH): string
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= self::CHARS[random_int(0, strlen(self::CHARS) - 1)];
        }

        session(['captcha_code' => $code]);

        return $code;
    }

    /**
     * Verifikasi input user terhadap kode di session (case-insensitive).
     * Kode langsung dihapus setelah dicek (one-time use).
     */
    public static function verify(?string $input): bool
    {
        $code = session('captcha_code');
        session()->forget('captcha_code');

        return is_string($code)
            && $input !== null
            && mb_strtoupper(trim($input)) === mb_strtoupper($code);
    }

    /** Render gambar captcha PNG ke response */
    public static function image(): Response
    {
        $code = session('captcha_code') ?: self::generateCode();

        $width = 200;
        $height = 60;

        $im = imagecreatetruecolor($width, $height);

        // Background
        $bg = imagecolorallocate($im, 240, 246, 253); // #f0f6fd
        imagefilledrectangle($im, 0, 0, $width, $height, $bg);

        // Border
        $border = imagecolorallocate($im, 190, 205, 225);
        imagerectangle($im, 0, 0, $width - 1, $height - 1, $border);

        // Noise: garis acak
        $lineColors = [
            imagecolorallocate($im, 30, 107, 184),
            imagecolorallocate($im, 251, 191, 36),
            imagecolorallocate($im, 147, 165, 190),
        ];
        for ($i = 0; $i < 5; $i++) {
            imageline(
                $im,
                random_int(-10, $width / 2),
                random_int(-5, $height + 5),
                random_int($width / 2, $width + 10),
                random_int(-5, $height + 5),
                $lineColors[array_rand($lineColors)],
            );
        }

        // Noise: titik acak
        for ($i = 0; $i < 60; $i++) {
            imagesetpixel($im, random_int(0, $width), random_int(0, $height), $lineColors[array_rand($lineColors)]);
        }

        // Karakter: render satu-per-satu dengan rotasi & jitter
        $textColors = [
            imagecolorallocate($im, 15, 42, 74),   // navy
            imagecolorallocate($im, 18, 85, 143),  // blue
            imagecolorallocate($im, 23, 55, 96),
        ];

        $slot = ($width - 20) / strlen($code);

        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            $color = $textColors[array_rand($textColors)];
            $angle = random_int(-12, 12);

            self::drawChar($im, $char, 10 + $i * $slot + random_int(-2, 4), random_int(38, 46), $angle, $color);
        }

        ob_start();
        imagepng($im, null, 6);
        $data = ob_get_clean();

        return response($data, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Gambar satu karakter memakai built-in font GD yang di-scale + rotate,
     * sehingga tidak butuh file font TTF di server.
     */
    private static function drawChar($im, string $char, int $x, int $y, int $angle, int $rgb): void
    {
        // Render char dengan built-in font (5 = ukuran terbesar) langsung dengan warna target
        $fw = imagefontwidth(5);
        $fh = imagefontheight(5);

        $tmp = imagecreatetruecolor($fw, $fh);
        $white = imagecolorallocate($tmp, 255, 255, 255);
        imagefill($tmp, 0, 0, $white);
        imagestring($tmp, 5, 0, 0, $char, $rgb);
        imagecolortransparent($tmp, $white);

        // Scale ~2x agar terlihat besar
        $scale = 2;
        $sw = $fw * $scale;
        $sh = $fh * $scale;
        $scaled = imagecreatetruecolor($sw, $sh);
        imagealphablending($scaled, false);
        imagesavealpha($scaled, true);
        $transparent = imagecolorallocatealpha($scaled, 0, 0, 0, 127);
        imagefill($scaled, 0, 0, $transparent);
        imagecopyresized($scaled, $tmp, 0, 0, 0, 0, $sw, $sh, $fw, $fh);

        // Rotasi dengan background transparan
        if ($angle !== 0) {
            imagealphablending($scaled, true);
            $bgAlpha = imagecolorallocatealpha($scaled, 0, 0, 0, 127);
            $rotated = imagerotate($scaled, $angle, $bgAlpha);
            $scaled = $rotated;
        }

        imagecopy($im, $scaled, (int) round($x - imagesx($scaled) / 2), (int) round($y - imagesy($scaled) / 2), 0, 0, imagesx($scaled), imagesy($scaled));
    }
}
