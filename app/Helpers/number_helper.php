<?php

if (!function_exists('terbilang')) {
    function terbilang(int|float $angka): string
    {
        $angka = abs((int) $angka);
        $baca  = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        $hasil = '';

        if ($angka < 12) {
            $hasil = ' ' . $baca[$angka];
        } elseif ($angka < 20) {
            $hasil = terbilang($angka - 10) . ' belas';
        } elseif ($angka < 100) {
            $hasil = terbilang($angka / 10) . ' puluh' . terbilang($angka % 10);
        } elseif ($angka < 200) {
            $hasil = ' seratus' . terbilang($angka - 100);
        } elseif ($angka < 1000) {
            $hasil = terbilang($angka / 100) . ' ratus' . terbilang($angka % 100);
        } elseif ($angka < 2000) {
            $hasil = ' seribu' . terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            $hasil = terbilang($angka / 1000) . ' ribu' . terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            $hasil = terbilang($angka / 1000000) . ' juta' . terbilang($angka % 1000000);
        } elseif ($angka < 1000000000000) {
            $hasil = terbilang($angka / 1000000000) . ' milyar' . terbilang(fmod($angka, 1000000000));
        }

        return trim($hasil);
    }
}

if (!function_exists('short_number')) {
    function short_number(int|float $num): string
    {
        if ($num >= 1000000000) {
            return round($num / 1000000000, 1) . 'M';
        }
        if ($num >= 1000000) {
            return round($num / 1000000, 1) . 'Jt';
        }
        if ($num >= 1000) {
            return round($num / 1000, 1) . 'Rb';
        }
        return (string) $num;
    }
}
