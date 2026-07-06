<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTotalBrlToContracts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('contracts', [
            'total_brl' => [
                'type'       => 'DECIMAL',
                'constraint' => '16,2',
                'default'    => 0.00,
                'after'      => 'total_amount'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('contracts', 'total_brl');
    }
}
