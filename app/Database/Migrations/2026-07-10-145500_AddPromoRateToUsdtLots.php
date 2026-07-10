<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPromoRateToUsdtLots extends Migration
{
    public function up()
    {
        $this->forge->addColumn('usdt_lots', [
            'promo_rate' => ['type' => 'DECIMAL', 'constraint' => '18,6', 'null' => true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('usdt_lots', ['promo_rate']);
    }
}
