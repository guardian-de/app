<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCreditFieldsToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'credit_limit' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'after'      => 'fee_percent'
            ],
            'default_contract_type' => [
                'type'       => 'ENUM',
                'constraint' => ['d+1', 'd+2'],
                'default'    => 'd+1',
                'after'      => 'credit_limit'
            ],
            'daily_interest_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
                'after'      => 'default_contract_type'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['credit_limit', 'default_contract_type', 'daily_interest_rate']);
    }
}
