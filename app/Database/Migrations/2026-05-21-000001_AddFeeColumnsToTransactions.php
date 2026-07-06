<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFeeColumnsToTransactions extends Migration
{
    public function up()
    {
        $columns = [];

        if (! $this->db->fieldExists('base_rate', 'transactions')) {
            $columns['base_rate'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,4',
                'null'       => true,
                'default'    => null,
                'after'      => 'locked_at',
            ];
        }
        if (! $this->db->fieldExists('fee_percent', 'transactions')) {
            $columns['fee_percent'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,4',
                'null'       => true,
                'default'    => null,
                'after'      => 'base_rate',
            ];
        }
        if (! $this->db->fieldExists('comercial_brl', 'transactions')) {
            $columns['comercial_brl'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'fee_percent',
            ];
        }
        if (! $this->db->fieldExists('fee_brl', 'transactions')) {
            $columns['fee_brl'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'comercial_brl',
            ];
        }

        if (! empty($columns)) {
            $this->forge->addColumn('transactions', $columns);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', ['base_rate', 'fee_percent', 'comercial_brl', 'fee_brl']);
    }
}
