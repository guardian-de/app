<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdjustmentTypesToFinancialStatements extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `financial_statements` MODIFY COLUMN `operation_type` ENUM(
            'deposit',
            'withdrawal',
            'margin_lock',
            'limit_release',
            'partial_amortization',
            'full_settlement',
            'late_fee',
            'adjustment_add',
            'adjustment_subtract'
        ) NOT NULL");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `financial_statements` MODIFY COLUMN `operation_type` ENUM(
            'deposit',
            'withdrawal',
            'margin_lock',
            'limit_release',
            'partial_amortization',
            'full_settlement',
            'late_fee'
        ) NOT NULL");
    }
}
