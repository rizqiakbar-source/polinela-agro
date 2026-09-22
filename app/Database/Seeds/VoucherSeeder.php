<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class VoucherSeeder extends Seeder
{
    public function run()
    {
        $vouchers = [
            [
                'kode'         => 'POLINELAJUARA',
                'nama'         => 'Diskon Civitas Polinela Juara',
                'tipe'         => 'fixed',
                'diskon'       => 20000.00,
                'min_belanja'  => 50000.00,
                'max_diskon'   => 20000.00,
                'kuota'        => 200,
                'terpakai'     => 12,
                'tgl_mulai'    => '2026-01-01',
                'tgl_berakhir' => '2026-12-31',
                'is_active'    => 1,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'kode'         => 'PANENRAYA10',
                'nama'         => 'Promo Panen Raya 10%',
                'tipe'         => 'persen',
                'diskon'       => 10.00,
                'min_belanja'  => 30000.00,
                'max_diskon'   => 25000.00,
                'kuota'        => 150,
                'terpakai'     => 28,
                'tgl_mulai'    => '2026-01-01',
                'tgl_berakhir' => '2026-12-31',
                'is_active'    => 1,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'kode'         => 'MAHASISWAAGRO',
                'nama'         => 'Subsidi Belanja Praktikum Mahasiswa',
                'tipe'         => 'fixed',
                'diskon'       => 15000.00,
                'min_belanja'  => 40000.00,
                'max_diskon'   => 15000.00,
                'kuota'        => 100,
                'terpakai'     => 5,
                'tgl_mulai'    => '2026-01-01',
                'tgl_berakhir' => '2026-12-31',
                'is_active'    => 1,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]
        ];

        $this->db->table('vouchers')->insertBatch($vouchers);
    }
}
