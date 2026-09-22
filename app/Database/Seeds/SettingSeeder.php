<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            [
                'setting_key'   => 'site_name',
                'setting_value' => 'Polinela Agro Digital',
                'setting_group' => 'general',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'setting_key'   => 'site_tagline',
                'setting_value' => 'Pasar Digital Produk Perkebunan Kampus',
                'setting_group' => 'general',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'setting_key'   => 'campus_address',
                'setting_value' => 'Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung 35141, Lampung - Indonesia',
                'setting_group' => 'general',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'setting_key'   => 'contact_phone',
                'setting_value' => '0721-703995',
                'setting_group' => 'general',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'setting_key'   => 'contact_wa',
                'setting_value' => '081234567890',
                'setting_group' => 'general',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'setting_key'   => 'contact_email',
                'setting_value' => 'agro@polinela.ac.id',
                'setting_group' => 'general',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'setting_key'   => 'bank_accounts',
                'setting_value' => json_encode([
                    [
                        'bank'        => 'Bank Mandiri',
                        'no_rekening' => '114-00-8899123-4',
                        'atas_nama'   => 'POLITEKNIK NEGERI LAMPUNG TEFA',
                    ],
                    [
                        'bank'        => 'Bank BRI',
                        'no_rekening' => '0098-01-000456-30-2',
                        'atas_nama'   => 'POLINELA AGRO DIGITAL',
                    ],
                    [
                        'bank'        => 'Bank Syariah Indonesia (BSI)',
                        'no_rekening' => '711-234-5678',
                        'atas_nama'   => 'BLU POLITEKNIK NEGERI LAMPUNG',
                    ]
                ]),
                'setting_group' => 'payment',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'setting_key'   => 'midtrans_client_key',
                'setting_value' => 'SB-Mid-client-polinela-demo',
                'setting_group' => 'payment',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'setting_key'   => 'shipping_rates',
                'setting_value' => json_encode([
                    ['wilayah' => 'Ambil di Tefa Polinela (Gratis)', 'tarif' => 0, 'estimasi' => 'Langsung ambil di kampus'],
                    ['wilayah' => 'Kurir Kampus (Lingkungan Polinela / Rajabasa)', 'tarif' => 5000, 'estimasi' => 'Hari yang sama'],
                    ['wilayah' => 'Bandar Lampung (Kota)', 'tarif' => 12000, 'estimasi' => '1 hari'],
                    ['wilayah' => 'Luar Kota / Luar Provinsi (JNE/SiCepat)', 'tarif' => 25000, 'estimasi' => '2-3 hari'],
                ]),
                'setting_group' => 'shipping',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]
        ];

        $this->db->table('settings')->insertBatch($settings);
    }
}
