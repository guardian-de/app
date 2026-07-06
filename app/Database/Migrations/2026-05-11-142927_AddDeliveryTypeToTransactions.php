<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeliveryTypeToTransactions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transactions', [
            'delivery_type' => [
                'type'       => 'ENUM',
                'constraint' => ['D+0', 'D+1', 'D+2'],
                'default'    => 'D+0',
                'after'      => 'status'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', 'delivery_type');
    }
}
