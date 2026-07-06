<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddSecurityFieldsToTransactions extends Migration
{
    public function up()
    {
        $fields = [
            'proof_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => '64',
                'null'       => true,
                'after'      => 'text_read'
            ],
            'auth_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'after'      => 'proof_hash'
            ],
        ];
        $this->forge->addColumn('transactions', $fields);
        $this->forge->addKey('proof_hash');
        $this->forge->addKey('auth_code');
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', ['proof_hash', 'auth_code']);
    }
}
