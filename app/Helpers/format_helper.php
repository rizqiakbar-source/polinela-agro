<?php

if (!function_exists('format_weight')) {
    function format_weight(int|float $gram): string
    {
        if ($gram >= 1000) {
            $kg = $gram / 1000;
            return (floor($kg) == $kg ? number_format($kg, 0) : number_format($kg, 1, ',', '.')) . ' kg';
        }
        return number_format($gram, 0, ',', '.') . ' gram';
    }
}

if (!function_exists('format_discount')) {
    function format_discount(string $type, float $value): string
    {
        if ($type === 'persen') {
            return round($value) . '%';
        }
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}

if (!function_exists('sanitize_phone')) {
    function sanitize_phone(?string $phone): string
    {
        if (!$phone) return '';
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }
        return $clean;
    }
}
