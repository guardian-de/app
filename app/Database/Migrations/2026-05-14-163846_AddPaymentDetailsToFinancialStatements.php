<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentDetailsToFinancialStatements extends Migration
{
    public function up()
    {
        $this->forge->addColumn('financial_statements', [
            'attachment' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'description'
            ],
            'payment_method' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'after' => 'attachment'
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'payment_method'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('financial_statements', ['attachment', 'payment_method', 'notes']);
    }
}
