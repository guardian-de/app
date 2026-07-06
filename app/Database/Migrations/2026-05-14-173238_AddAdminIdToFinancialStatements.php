<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdminIdToFinancialStatements extends Migration
{
    public function up()
    {
        $this->forge->addColumn('financial_statements', [
            'admin_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'user_id'
            ],
        ]);
        
        $this->db->query("ALTER TABLE `financial_statements` ADD CONSTRAINT `fk_statement_admin` FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE SET NULL");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `financial_statements` DROP FOREIGN KEY `fk_statement_admin` ");
        $this->forge->dropColumn('financial_statements', 'admin_id');
    }
}
