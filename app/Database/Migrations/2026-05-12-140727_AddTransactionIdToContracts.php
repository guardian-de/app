<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTransactionIdToContracts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('contracts', [
            'transaction_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'id'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('contracts', 'transaction_id');
    }
}
