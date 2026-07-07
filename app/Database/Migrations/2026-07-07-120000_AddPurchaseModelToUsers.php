<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPurchaseModelToUsers extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('purchase_model', 'users')) {
            $this->forge->addColumn('users', [
                'purchase_model' => [
                    'type'       => 'ENUM',
                    'constraint' => ['usdt', 'brl', 'both'],
                    'default'    => 'usdt',
                    'null'       => false,
                    'after'      => 'allowed_delivery_types'
                ],
            ]);
        }

        if (!$this->db->fieldExists('last_purchase_mode', 'users')) {
            $this->forge->addColumn('users', [
                'last_purchase_mode' => [
                    'type'       => 'ENUM',
                    'constraint' => ['usdt', 'brl'],
                    'null'       => true,
                    'after'      => 'purchase_model'
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('last_purchase_mode', 'users')) {
            $this->forge->dropColumn('users', 'last_purchase_mode');
        }
        if ($this->db->fieldExists('purchase_model', 'users')) {
            $this->forge->dropColumn('users', 'purchase_model');
        }
    }
}
