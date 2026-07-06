<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExtratoFinanceiroTable extends Migration
{
    public function up()
    {
        $sql = "CREATE TABLE `financial_statements` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `contract_id` INT UNSIGNED NULL COMMENT 'Null if standard account deposit/withdrawal',
            `operation_type` ENUM(
                'deposit',
                'withdrawal',
                'margin_lock', 
                'limit_release', 
                'partial_amortization',
                'full_settlement',
                'late_fee'
            ) NOT NULL,
            `nature` ENUM('C', 'D') NOT NULL COMMENT 'C = Credit (In), D = Debit (Out)',
            `amount` DECIMAL(15, 2) NOT NULL,
            `description` VARCHAR(255) NOT NULL,
            `transaction_date` DATETIME NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_statement_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_statement_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $this->db->query($sql);
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS `financial_statements`;");
    }
}
