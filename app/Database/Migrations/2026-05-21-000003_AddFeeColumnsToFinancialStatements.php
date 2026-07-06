<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFeeColumnsToFinancialStatements extends Migration
{
    public function up()
    {
        $columns = [];

        if (! $this->db->fieldExists('fee_percent', 'financial_statements')) {
            $columns['fee_percent'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,4',
                'null'       => true,
                'default'    => null,
                'after'      => 'created_at',
            ];
        }
        if (! $this->db->fieldExists('comercial_brl', 'financial_statements')) {
            $columns['comercial_brl'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'fee_percent',
            ];
        }
        if (! $this->db->fieldExists('fee_brl', 'financial_statements')) {
            $columns['fee_brl'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'comercial_brl',
            ];
        }

        if (! empty($columns)) {
            $this->forge->addColumn('financial_statements', $columns);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('financial_statements', ['fee_percent', 'comercial_brl', 'fee_brl']);
    }
}
