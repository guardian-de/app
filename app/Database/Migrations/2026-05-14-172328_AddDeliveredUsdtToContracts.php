<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeliveredUsdtToContracts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('contracts', [
            'delivered_usdt' => [
                'type' => 'DECIMAL',
                'constraint' => '16,2',
                'default' => 0.00,
                'after' => 'total_amount'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('contracts', 'delivered_usdt');
    }
}
