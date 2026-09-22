<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run()
    {
        $units = [
            [
                'id'         => 1,
                'nama_unit'  => 'Unit Kopi Polinela',
                'slug'       => 'unit-kopi-polinela',
                'deskripsi'  => 'Unit usaha dan Teaching Factory pengolahan biji kopi robusta dan arabika unggulan dari kebun riset Polinela.',
                'logo'       => 'unit-kopi.png',
                'pj_nama'    => 'Ir. Hendra Saputra, M.T.A.',
                'kontak'     => '0812-3456-7801',
                'lokasi'     => 'Gedung Tefa Pengolahan Hasil Kopi Polinela',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id'         => 2,
                'nama_unit'  => 'Unit Kakao & Cokelat Polinela',
                'slug'       => 'unit-kakao-polinela',
                'deskripsi'  => 'Unit pengolahan fermentasi biji kakao premium, cokelat batang artesanal, dan kakao nibs bermutu tinggi.',
                'logo'       => 'unit-kakao.png',
                'pj_nama'    => 'Dr. Maya Kartika, S.P., M.Si.',
                'kontak'     => '0812-3456-7802',
                'lokasi'     => 'Laboratorium Terpadu Kakao Polinela',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id'         => 3,
                'nama_unit'  => 'Unit Lada & Rempah Lampung',
                'slug'       => 'unit-lada-rempah',
                'deskripsi'  => 'Unit budidaya dan pemrosesan lada hitam (Lampung black pepper) dan rempah khas Lampung berstandar ekspor.',
                'logo'       => 'unit-lada.png',
                'pj_nama'    => 'Bambang Kusumo, S.St., M.Tr.P.',
                'kontak'     => '0812-3456-7803',
                'lokasi'     => 'Kebun Percobaan Rempah Polinela',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id'         => 4,
                'nama_unit'  => 'Unit Olahan & Atsiri Tefa',
                'slug'       => 'unit-olahan-atsiri',
                'deskripsi'  => 'Unit penyulingan minyak atsiri serai wangi, pupuk organik hayati, dan produk olahan hortikultura kampus.',
                'logo'       => 'unit-atsiri.png',
                'pj_nama'    => 'Nurul Hidayati, S.P., M.Sc.',
                'kontak'     => '0812-3456-7804',
                'lokasi'     => 'Pusat Bio-Industri & Tefa Hortikultura Polinela',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('units')->insertBatch($units);
    }
}
