<?php

namespace App\Libraries;

class CsvGenerator
{
    /**
     * Generate dan stream berkas CSV ber-BOM UTF-8
     */
    public static function stream(array $headers, array $rows, string $filename = 'export'): void
    {
        ExcelGenerator::exportCsv($headers, $rows, $filename);
    }
}
