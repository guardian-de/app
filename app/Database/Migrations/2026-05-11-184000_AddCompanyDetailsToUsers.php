<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompanyDetailsToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'company_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'name'
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'after'      => 'company_name'
            ],
            'cnpj' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'after'      => 'phone'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['company_name', 'phone', 'cnpj']);
    }
}
