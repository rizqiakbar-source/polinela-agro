<?php

if (!function_exists('product_image_url')) {
    function product_image_url(?string $filename): string
    {
        if (empty($filename)) {
            return 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=600&auto=format&fit=crop&q=80';
        }

        if (file_exists(FCPATH . 'uploads/produk/' . $filename)) {
            return base_url('uploads/produk/' . $filename);
        }

        if (file_exists(FCPATH . 'uploads/products/' . $filename)) {
            return base_url('uploads/products/' . $filename);
        }

        if (file_exists(FCPATH . 'assets/img/products/' . $filename)) {
            return base_url('assets/img/products/' . $filename);
        }

        // Variasi fallback sesuai nama produk / komoditas
        $fn = strtolower($filename);
        if (str_contains($fn, 'bubuk') || str_contains($fn, 'ground')) {
            return 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=600&auto=format&fit=crop&q=80';
        }
        if (str_contains($fn, 'kakao') || str_contains($fn, 'nibs') || str_contains($fn, 'cocoa')) {
            return 'https://images.unsplash.com/photo-1542843137-8791a6904d14?w=600&auto=format&fit=crop&q=80';
        }
        if (str_contains($fn, 'chocolate') || str_contains($fn, 'cokelat') || str_contains($fn, 'dark')) {
            return 'https://images.unsplash.com/photo-1606312619070-d48b4c652a52?w=600&auto=format&fit=crop&q=80';
        }
        if (str_contains($fn, 'lada') || str_contains($fn, 'pepper') || str_contains($fn, 'rempah')) {
            return 'https://images.unsplash.com/photo-1599940824399-b87987ceb72a?w=600&auto=format&fit=crop&q=80';
        }
        if (str_contains($fn, 'minyak') || str_contains($fn, 'atsiri') || str_contains($fn, 'serai') || str_contains($fn, 'oil')) {
            return 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?w=600&auto=format&fit=crop&q=80';
        }
        if (str_contains($fn, 'pupuk') || str_contains($fn, 'organik') || str_contains($fn, 'hayati') || str_contains($fn, 'cair')) {
            return 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?w=600&auto=format&fit=crop&q=80';
        }
        if (str_contains($fn, 'bibit') || str_contains($fn, 'tanaman') || str_contains($fn, 'seedling')) {
            return 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=600&auto=format&fit=crop&q=80';
        }

        return 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=600&auto=format&fit=crop&q=80';
    }
}

if (!function_exists('user_avatar_url')) {
    function user_avatar_url(?string $filename): string
    {
        if (empty($filename) || $filename === 'default-avatar.png') {
            return 'https://ui-avatars.com/api/?name=Polinela+User&background=1b5e20&color=fff&size=128';
        }

        if (file_exists(FCPATH . 'uploads/avatar/' . $filename)) {
            return base_url('uploads/avatar/' . $filename);
        }

        if (file_exists(FCPATH . 'uploads/avatars/' . $filename)) {
            return base_url('uploads/avatars/' . $filename);
        }

        if (file_exists(FCPATH . 'assets/img/avatars/' . $filename)) {
            return base_url('assets/img/avatars/' . $filename);
        }

        return 'https://ui-avatars.com/api/?name=Polinela+User&background=1b5e20&color=fff&size=128';
    }
}
