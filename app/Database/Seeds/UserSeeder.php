<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'nama'       => 'Super Administrator',
                'email'      => 'admin@polinela.ac.id',
                'password'   => password_hash('admin123', PASSWORD_BCRYPT),
                'no_hp'      => '081234567890',
                'role'       => 'superadmin',
                'foto'       => 'default-avatar.png',
                'unit_id'    => null,
                'alamat'     => 'Gedung Rektorat Polinela, Bandar Lampung',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama'       => 'Admin Unit Kopi Polinela',
                'email'      => 'adminkopi@polinela.ac.id',
                'password'   => password_hash('admin123', PASSWORD_BCRYPT),
                'no_hp'      => '081234567891',
                'role'       => 'admin_unit',
                'foto'       => 'default-avatar.png',
                'unit_id'    => 1,
                'alamat'     => 'Kebun Percobaan & Tefa Pengolahan Kopi Polinela',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama'       => 'Admin Unit Kakao Polinela',
                'email'      => 'adminkakao@polinela.ac.id',
                'password'   => password_hash('admin123', PASSWORD_BCRYPT),
                'no_hp'      => '081234567892',
                'role'       => 'admin_unit',
                'foto'       => 'default-avatar.png',
                'unit_id'    => 2,
                'alamat'     => 'Laboratorium Pengolahan Hasil Kakao Polinela',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama'       => 'Admin Unit Lada & Rempah',
                'email'      => 'adminlada@polinela.ac.id',
                'password'   => password_hash('admin123', PASSWORD_BCRYPT),
                'no_hp'      => '081234567893',
                'role'       => 'admin_unit',
                'foto'       => 'default-avatar.png',
                'unit_id'    => 3,
                'alamat'     => 'Unit Produksi Rempah Unggulan Lampung Polinela',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama'       => 'Admin Unit Olahan & Atsiri',
                'email'      => 'adminatsiri@polinela.ac.id',
                'password'   => password_hash('admin123', PASSWORD_BCRYPT),
                'no_hp'      => '081234567895',
                'role'       => 'admin_unit',
                'foto'       => 'default-avatar.png',
                'unit_id'    => 4,
                'alamat'     => 'Pusat Bio-Industri & Tefa Hortikultura Polinela',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama'       => 'Pimpinan Polinela',
                'email'      => 'pimpinan@polinela.ac.id',
                'password'   => password_hash('pimpinan123', PASSWORD_BCRYPT),
                'no_hp'      => '081234567894',
                'role'       => 'pimpinan',
                'foto'       => 'default-avatar.png',
                'unit_id'    => null,
                'alamat'     => 'Ruang Manajemen & Direktur Polinela',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama'       => 'Budi Pratama (Civitas Polinela)',
                'email'      => 'budi@gmail.com',
                'password'   => password_hash('konsumen123', PASSWORD_BCRYPT),
                'no_hp'      => '085278901234',
                'role'       => 'konsumen',
                'foto'       => 'default-avatar.png',
                'unit_id'    => null,
                'alamat'     => 'Jl. Flamboyan Blok B No. 4, Kemiling, Bandar Lampung',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama'       => 'Siti Rahmawati',
                'email'      => 'siti@gmail.com',
                'password'   => password_hash('konsumen123', PASSWORD_BCRYPT),
                'no_hp'      => '085712345678',
                'role'       => 'konsumen',
                'foto'       => 'default-avatar.png',
                'unit_id'    => null,
                'alamat'     => 'Jl. ZA Pagar Alam No. 45, Rajabasa, Bandar Lampung',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ];

        $this->db->table('users')->insertBatch($users);
    }
}
