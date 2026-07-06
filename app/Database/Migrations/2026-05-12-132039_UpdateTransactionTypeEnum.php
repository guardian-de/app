<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateTransactionTypeEnum extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('transactions', [
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['buy', 'sell', 'payment', 'interest'],
                'default'    => 'buy',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('transactions', [
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['buy', 'sell'],
                'default'    => 'buy',
            ],
        ]);
    }
}
