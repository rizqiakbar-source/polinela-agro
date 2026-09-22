<?php

if (!function_exists('clean_csv_cell')) {
    function clean_csv_cell($data): string
    {
        if (is_null($data)) return '';
        $data = (string) $data;
        // Mencegah Formula Injection di spreadsheet (=, +, -, @)
        if (preg_match('/^[=\+\-@]/', $data)) {
            $data = "'" . $data;
        }
        return $data;
    }
}
