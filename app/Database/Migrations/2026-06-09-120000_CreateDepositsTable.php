<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDepositsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'         => ['type' => 'INT', 'unsigned' => true],
            'amount'          => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'proof_file'      => ['type' => 'VARCHAR', 'constraint' => 500],
            'status'          => ['type' => 'ENUM', 'constraint' => ['pending', 'accepted', 'reversed'], 'default' => 'pending'],
            'notes'           => ['type' => 'TEXT', 'null' => true],
            'accepted_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'accepted_at'     => ['type' => 'DATETIME', 'null' => true],
            'reversed_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'reversed_at'     => ['type' => 'DATETIME', 'null' => true],
            'reversal_reason' => ['type' => 'TEXT', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('status');
        $this->forge->createTable('deposits');
    }

    public function down()
    {
        $this->forge->dropTable('deposits');
    }
}
