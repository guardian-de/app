<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddProofTextToTransactions extends Migration
{
    public function up()
    {
        $fields = [
            'proof_text' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'proof_path'
            ],
        ];
        $this->forge->addColumn('transactions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', 'proof_text');
    }
}
