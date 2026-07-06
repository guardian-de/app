<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserDollarHistoryTable extends Migration
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
            ],
            'rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,4',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'created_at']);
        $this->forge->createTable('user_dollar_history');
    }

    public function down()
    {
        $this->forge->dropTable('user_dollar_history');
    }
}
