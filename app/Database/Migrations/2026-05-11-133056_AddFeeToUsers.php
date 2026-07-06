<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFeeToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'fee_percent' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 10.00,
                'after'      => 'password'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'fee_percent');
    }
}
