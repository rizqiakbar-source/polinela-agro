<?php

namespace App\Libraries;

class BarcodeGenerator
{
    /**
     * Menghasilkan barcode tag untuk nomor resi atau surat jalan
     */
    public static function renderHtml(string $code, int $height = 40): string
    {
        $safeCode = htmlspecialchars($code);
        return "<div class=\"text-center\"><div style=\"font-family: 'Courier New', monospace; font-size: 1.5rem; letter-spacing: 4px; font-weight: bold; padding: 6px; border: 1px dashed #666; display: inline-block; background: #fff;\">||| | | |||| | ||| | |||</div><div class=\"small font-monospace text-muted mt-1\">{$safeCode}</div></div>";
    }
}
