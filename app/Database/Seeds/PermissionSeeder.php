<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Superadmin modules
            ['role' => 'superadmin', 'module' => 'dashboard', 'can_create' => 1, 'can_read' => 1, 'can_update' => 1, 'can_delete' => 1],
            ['role' => 'superadmin', 'module' => 'produk', 'can_create' => 1, 'can_read' => 1, 'can_update' => 1, 'can_delete' => 1],
            ['role' => 'superadmin', 'module' => 'kategori', 'can_create' => 1, 'can_read' => 1, 'can_update' => 1, 'can_delete' => 1],
            ['role' => 'superadmin', 'module' => 'unit', 'can_create' => 1, 'can_read' => 1, 'can_update' => 1, 'can_delete' => 1],
            ['role' => 'superadmin', 'module' => 'pesanan', 'can_create' => 1, 'can_read' => 1, 'can_update' => 1, 'can_delete' => 1],
            ['role' => 'superadmin', 'module' => 'pembayaran', 'can_create' => 1, 'can_read' => 1, 'can_update' => 1, 'can_delete' => 1],
            ['role' => 'superadmin', 'module' => 'user', 'can_create' => 1, 'can_read' => 1, 'can_update' => 1, 'can_delete' => 1],
            ['role' => 'superadmin', 'module' => 'laporan', 'can_create' => 1, 'can_read' => 1, 'can_update' => 1, 'can_delete' => 1],
            ['role' => 'superadmin', 'module' => 'backup', 'can_create' => 1, 'can_read' => 1, 'can_update' => 1, 'can_delete' => 1],
            ['role' => 'superadmin', 'module' => 'pengaturan', 'can_create' => 1, 'can_read' => 1, 'can_update' => 1, 'can_delete' => 1],

            // Admin unit modules (terbatas unit sendiri)
            ['role' => 'admin_unit', 'module' => 'dashboard', 'can_create' => 0, 'can_read' => 1, 'can_update' => 0, 'can_delete' => 0],
            ['role' => 'admin_unit', 'module' => 'produk', 'can_create' => 1, 'can_read' => 1, 'can_update' => 1, 'can_delete' => 1],
            ['role' => 'admin_unit', 'module' => 'stok', 'can_create' => 1, 'can_read' => 1, 'can_update' => 1, 'can_delete' => 0],
            ['role' => 'admin_unit', 'module' => 'pesanan', 'can_create' => 0, 'can_read' => 1, 'can_update' => 1, 'can_delete' => 0],
            ['role' => 'admin_unit', 'module' => 'pembayaran', 'can_create' => 0, 'can_read' => 1, 'can_update' => 1, 'can_delete' => 0],
            ['role' => 'admin_unit', 'module' => 'laporan', 'can_create' => 0, 'can_read' => 1, 'can_update' => 0, 'can_delete' => 0],

            // Pimpinan modules (read-only monitoring & export)
            ['role' => 'pimpinan', 'module' => 'dashboard', 'can_create' => 0, 'can_read' => 1, 'can_update' => 0, 'can_delete' => 0],
            ['role' => 'pimpinan', 'module' => 'laporan', 'can_create' => 0, 'can_read' => 1, 'can_update' => 0, 'can_delete' => 0],
        ];

        foreach ($permissions as &$p) {
            $p['created_at'] = date('Y-m-d H:i:s');
            $p['updated_at'] = date('Y-m-d H:i:s');
        }

        $this->db->table('permissions')->insertBatch($permissions);

        // Banners
        $banners = [
            [
                'judul'      => 'Panen Raya Kopi Robusta & Kakao Polinela',
                'subjudul'   => 'Nikmati cita rasa kopi petik merah dan produk olahan kakao asli kebun riset kampus vokasi terbaik Lampung.',
                'gambar'     => 'banner-kopi.jpg',
                'link'       => 'katalog',
                'urutan'     => 1,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'judul'      => 'Lada Hitam Lampung & Minyak Atsiri Alami',
                'subjudul'   => 'Rempah aromatik berkualitas ekspor dan ekstrak serai wangi hasil produksi Teaching Factory Polinela.',
                'gambar'     => 'banner-rempah.jpg',
                'link'       => 'katalog?kategori=rempah-atsiri',
                'urutan'     => 2,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('banners')->insertBatch($banners);
    }
}
