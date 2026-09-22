<?php

namespace App\Libraries;

class SlugGenerator
{
    /**
     * Membuat slug unik terhadap suatu tabel basis data
     */
    public static function createUnique(string $text, string $table = 'products', string $field = 'slug', ?int $excludeId = null): string
    {
        $slug = url_title($text, '-', true);
        if (empty($slug)) {
            $slug = 'item-' . time();
        }

        $db = \Config\Database::connect();
        $builder = $db->table($table);

        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $builder->where($field, $slug);
            if ($excludeId) {
                $builder->where('id !=', $excludeId);
            }

            if ($builder->countAllResults() === 0) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
