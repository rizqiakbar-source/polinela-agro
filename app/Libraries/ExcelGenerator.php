<?php

namespace App\Libraries;

class ExcelGenerator
{
    public static function exportCsv(array $headers, array $data, string $filename = 'laporan_export')
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename . '_' . date('Ymd_His') . '.csv');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Tambahkan UTF-8 BOM agar terbaca rapi di Microsoft Excel
        fputs($output, "\xEF\xBB\xBF");

        // Tulis header
        fputcsv($output, $headers);

        // Tulis data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit();
    }
}
