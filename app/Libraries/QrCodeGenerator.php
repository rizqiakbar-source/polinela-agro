<?php

namespace App\Libraries;

class QrCodeGenerator
{
    /**
     * Menghasilkan URL gambar QR Code menggunakan API standar untuk invoice & tracking
     */
    public static function getUrl(string $data, int $size = 200): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($data);
    }

    /**
     * Menghasilkan tag <img> QR Code siap pakai
     */
    public static function renderImg(string $data, int $size = 150, string $alt = 'QR Code'): string
    {
        $url = self::getUrl($data, $size);
        return "<img src=\"{$url}\" width=\"{$size}\" height=\"{$size}\" alt=\"{$alt}\" class=\"img-fluid border p-1 rounded bg-white\">";
    }
}
