<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFeeAndDeliveryColumnsToContracts extends Migration
{
    public function up()
    {
        $columns = [];

        if (! $this->db->fieldExists('delivery_blocked', 'contracts')) {
            $columns['delivery_blocked'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
                'after'      => 'delivered_usdt',
            ];
        }
        if (! $this->db->fieldExists('delivery_block_reason', 'contracts')) {
            $columns['delivery_block_reason'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'delivery_blocked',
            ];
        }
        if (! $this->db->fieldExists('fee_percent', 'contracts')) {
            $columns['fee_percent'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,4',
                'null'       => true,
                'default'    => null,
                'after'      => 'locked_at',
            ];
        }
        if (! $this->db->fieldExists('comercial_brl', 'contracts')) {
            $columns['comercial_brl'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'fee_percent',
            ];
        }
        if (! $this->db->fieldExists('fee_brl', 'contracts')) {
            $columns['fee_brl'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'comercial_brl',
            ];
        }

        if (! empty($columns)) {
            $this->forge->addColumn('contracts', $columns);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('contracts', ['delivery_blocked', 'delivery_block_reason', 'fee_percent', 'comercial_brl', 'fee_brl']);
    }
}
