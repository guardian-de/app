<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTypeToTransactions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transactions', [
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['buy', 'sell'],
                'default'    => 'buy',
                'after'      => 'user_id'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', 'type');
    }
}
