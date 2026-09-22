<?php

if (!function_exists('safe_excerpt')) {
    function safe_excerpt(?string $text, int $limit = 100, string $end = '...'): string
    {
        if (empty($text)) return '';
        $text = strip_tags($text);
        if (mb_strlen($text) <= $limit) {
            return $text;
        }
        return mb_substr($text, 0, $limit) . $end;
    }
}

if (!function_exists('generate_slug')) {
    function generate_slug(string $title): string
    {
        // Ganti karakter non huruf/angka dengan strip
        $slug = preg_replace('~[^\pL\d]+~u', '-', $title);
        // Transliterate
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        // Hapus karakter yang tidak diinginkan
        $slug = preg_replace('~[^-\w]+~', '', $slug);
        $slug = trim($slug, '-');
        $slug = preg_replace('~-+~', '-', $slug);
        $slug = strtolower($slug);

        return empty($slug) ? 'n-a' : $slug;
    }
}
