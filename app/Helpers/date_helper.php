<?php

if (!function_exists('indo_date')) {
    function indo_date(?string $datetime, bool $withTime = false): string
    {
        if (empty($datetime)) {
            return '-';
        }

        $timestamp = strtotime($datetime);
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $tgl = date('d', $timestamp);
        $bln = $bulan[(int) date('m', $timestamp)];
        $thn = date('Y', $timestamp);

        if ($withTime) {
            $jam = date('H:i', $timestamp);
            return "{$tgl} {$bln} {$thn}, {$jam} WIB";
        }

        return "{$tgl} {$bln} {$thn}";
    }
}

if (!function_exists('time_ago')) {
    function time_ago(?string $datetime): string
    {
        if (empty($datetime)) return '-';
        $time = time() - strtotime($datetime);

        if ($time < 60) return 'Baru saja';
        if ($time < 3600) return floor($time / 60) . ' menit yang lalu';
        if ($time < 86400) return floor($time / 3600) . ' jam yang lalu';
        if ($time < 2592000) return floor($time / 86400) . ' hari yang lalu';
        if ($time < 31536000) return floor($time / 2592000) . ' bulan yang lalu';

        return floor($time / 31536000) . ' tahun yang lalu';
    }
}
