<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWalletToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'usdt_wallet' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'language'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'usdt_wallet');
    }
}
