<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGlobalDollarHistory extends Migration
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
            'base_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,4',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('dollar_history');
    }

    public function down()
    {
        $this->forge->dropTable('dollar_history');
    }
}
