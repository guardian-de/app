<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClosingDateToUsdtLots extends Migration
{
    public function up()
    {
        $fields = [
            'closing_date' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'status',
            ],
        ];
        $this->forge->addColumn('usdt_lots', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('usdt_lots', 'closing_date');
    }
}
