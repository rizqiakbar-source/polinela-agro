<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSusResponsesTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'nama_responden' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'role_responden' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'q1' => ['type' => 'TINYINT', 'constraint' => 1],
            'q2' => ['type' => 'TINYINT', 'constraint' => 1],
            'q3' => ['type' => 'TINYINT', 'constraint' => 1],
            'q4' => ['type' => 'TINYINT', 'constraint' => 1],
            'q5' => ['type' => 'TINYINT', 'constraint' => 1],
            'q6' => ['type' => 'TINYINT', 'constraint' => 1],
            'q7' => ['type' => 'TINYINT', 'constraint' => 1],
            'q8' => ['type' => 'TINYINT', 'constraint' => 1],
            'q9' => ['type' => 'TINYINT', 'constraint' => 1],
            'q10' => ['type' => 'TINYINT', 'constraint' => 1],
            'total_score' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
            ],
            'kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'feedback' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('sus_responses', true);
    }

    public function down()
    {
        $this->forge->dropTable('sus_responses', true);
    }
}
