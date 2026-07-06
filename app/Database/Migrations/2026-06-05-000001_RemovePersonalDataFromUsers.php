<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemovePersonalDataFromUsers extends Migration
{
    public function up()
    {
        $fields = ['name', 'company_name', 'phone', 'cnpj'];
        foreach ($fields as $field) {
            if ($this->db->fieldExists($field, 'users')) {
                $this->forge->dropColumn('users', $field);
            }
        }
    }

    public function down()
    {
        $this->forge->addColumn('users', [
            'name'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true,  'after' => 'id'],
            'company_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'phone'        => ['type' => 'VARCHAR', 'constraint' => 20,  'null' => true],
            'cnpj'         => ['type' => 'VARCHAR', 'constraint' => 20,  'null' => true],
        ]);
    }
}
