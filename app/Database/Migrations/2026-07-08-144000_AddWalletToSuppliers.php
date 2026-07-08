<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWalletToSuppliers extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('wallet', 'suppliers')) {
            $this->forge->addColumn('suppliers', [
                'wallet' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => null,
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('wallet', 'suppliers')) {
            $this->forge->dropColumn('suppliers', 'wallet');
        }
    }
}
