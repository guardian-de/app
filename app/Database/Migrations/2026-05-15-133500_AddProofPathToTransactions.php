<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddProofPathToTransactions extends Migration
{
    public function up()
    {
        $fields = [
            'proof_path' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'wallet_address'
            ],
        ];
        $this->forge->addColumn('transactions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', 'proof_path');
    }
}
