<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaidClientToContracts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('contracts', [
            'paid_client' => [
                'type' => 'DECIMAL',
                'constraint' => '16,2',
                'default' => 0.00,
                'after' => 'paid_amount'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('contracts', 'paid_client');
    }
}
