<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLanguageToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'language' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'default'    => 'pt-BR',
                'after'      => 'fee_percent'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'language');
    }
}
