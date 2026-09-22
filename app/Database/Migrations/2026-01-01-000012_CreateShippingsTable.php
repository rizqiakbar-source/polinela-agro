<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShippingsTable extends Migration
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
            'order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'kurir' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'layanan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'no_resi' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'ongkir' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ],
            'estimasi' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'penerima_nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'penerima_telepon' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
            ],
            'alamat_lengkap' => [
                'type' => 'TEXT',
            ],
            'kota' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'kode_pos' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'dikemas', 'dikirim', 'sampai'],
                'default'    => 'pending',
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
        $this->forge->addKey('order_id');
        $this->forge->createTable('shippings', true);
    }

    public function down()
    {
        $this->forge->dropTable('shippings', true);
    }
}
