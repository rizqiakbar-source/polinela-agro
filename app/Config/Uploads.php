<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Uploads extends BaseConfig
{
    /**
     * Folder penyimpanan gambar produk relatif terhadap FCPATH (public/)
     */
    public string $productPath = 'assets/img/products/';

    /**
     * Folder penyimpanan bukti bayar
     */
    public string $paymentProofPath = 'uploads/bukti_bayar/';

    /**
     * Folder penyimpanan banner hero
     */
    public string $bannerPath = 'assets/img/banners/';

    /**
     * Folder penyimpanan avatar profil
     */
    public string $avatarPath = 'assets/img/avatars/';

    /**
     * Ekstensi berkas gambar yang diizinkan
     */
    public array $allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];

    /**
     * Batas ukuran berkas maksimum dalam Kilobytes (5120 KB = 5 MB)
     */
    public int $maxSizeKb = 5120;
}
