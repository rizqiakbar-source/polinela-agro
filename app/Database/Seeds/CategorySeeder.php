<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'id'            => 1,
                'nama_kategori' => 'Kopi Nusantara Polinela',
                'slug'          => 'kopi-nusantara',
                'icon'          => 'bi-cup-hot',
                'deskripsi'     => 'Biji kopi sangrai, bubuk robusta, dan produk olahan kopi spesial dari kebun Politeknik Negeri Lampung.',
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'id'            => 2,
                'nama_kategori' => 'Biji & Olahan Kakao',
                'slug'          => 'biji-olahan-kakao',
                'icon'          => 'bi-box-seam',
                'deskripsi'     => 'Kakao nibs organik, bubuk cokelat murni, serta cokelat artisan berkualitas hasil riset Tefa.',
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'id'            => 3,
                'nama_kategori' => 'Rempah & Minyak Atsiri',
                'slug'          => 'rempah-minyak-atsiri',
                'icon'          => 'bi-flower1',
                'deskripsi'     => 'Lada hitam asli Lampung, minyak serai wangi murni, dan simplisia rempah perkebunan.',
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'id'            => 4,
                'nama_kategori' => 'Bibit & Pupuk Perkebunan',
                'slug'          => 'bibit-pupuk-perkebunan',
                'icon'          => 'bi-tree',
                'deskripsi'     => 'Bibit tanaman perkebunan unggul bersertifikat serta pupuk organik kompos Tefa Polinela.',
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'id'            => 5,
                'nama_kategori' => 'Produk Pangan Olahan Tefa',
                'slug'          => 'produk-pangan-olahan',
                'icon'          => 'bi-basket',
                'deskripsi'     => 'Produk makanan dan minuman sehat berbahan baku komoditas hasil perkebunan kampus.',
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('categories')->insertBatch($categories);
    }
}
