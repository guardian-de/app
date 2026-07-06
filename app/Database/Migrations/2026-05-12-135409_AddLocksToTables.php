<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLocksToTables extends Migration
{
    public function up()
    {
        $fields = [
            'locked_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'locked_at' => ['type' => 'DATETIME', 'null' => true],
        ];
        
        $this->forge->addColumn('transactions', $fields);
        $this->forge->addColumn('contracts', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', ['locked_by', 'locked_at']);
        $this->forge->dropColumn('contracts', ['locked_by', 'locked_at']);
    }
}
