<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVouchersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'tipe' => [
                'type'       => 'ENUM',
                'constraint' => ['fixed', 'persen'],
                'default'    => 'fixed',
            ],
            'diskon' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'min_belanja' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ],
            'max_diskon' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
            ],
            'kuota' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 100,
            ],
            'terpakai' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'tgl_mulai' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'tgl_berakhir' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('vouchers', true);
    }

    public function down()
    {
        $this->forge->dropTable('vouchers', true);
    }
}
