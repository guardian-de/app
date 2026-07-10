<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPromotionalFieldsToUsdtLots extends Migration
{
    public function up()
    {
        $this->forge->addColumn('usdt_lots', [
            'is_promotional' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'target_type'    => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'target_group'   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'target_users'   => ['type' => 'TEXT', 'null' => true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('usdt_lots', ['is_promotional', 'target_type', 'target_group', 'target_users']);
    }
}
