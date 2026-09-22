<?php

if (!function_exists('product_image_url')) {
    function product_image_url(?string $filename): string
    {
        if (empty($filename)) {
            return 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=600&auto=format&fit=crop&q=80';
        }

        $localPath = FCPATH . 'assets/img/products/' . $filename;
        if (file_exists($localPath)) {
            return base_url('assets/img/products/' . $filename);
        }

        return 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=600&auto=format&fit=crop&q=80';
    }
}

if (!function_exists('user_avatar_url')) {
    function user_avatar_url(?string $filename): string
    {
        if (empty($filename) || $filename === 'default-avatar.png') {
            return 'https://ui-avatars.com/api/?name=Polinela+User&background=1b5e20&color=fff&size=128';
        }

        $localPath = FCPATH . 'assets/img/avatars/' . $filename;
        if (file_exists($localPath)) {
            return base_url('assets/img/avatars/' . $filename);
        }

        return 'https://ui-avatars.com/api/?name=Polinela+User&background=1b5e20&color=fff&size=128';
    }
}
