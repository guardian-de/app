<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddTextReadToTransactions extends Migration
{
    public function up()
    {
        $fields = [
            'text_read' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'proof_text'
            ],
        ];
        $this->forge->addColumn('transactions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', 'text_read');
    }
}
