<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAllowedDeliveryTypesToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'allowed_delivery_types' => [
                'type'       => 'ENUM',
                'constraint' => ['D+0', 'D+1', 'D+2', 'all'],
                'default'    => 'all',
                'after'      => 'daily_interest_rate'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'allowed_delivery_types');
    }
}
