<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPermissionsToUsers extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('permissions', 'users')) {
            $this->forge->addColumn('users', [
                'permissions' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('permissions', 'users')) {
            $this->forge->dropColumn('users', 'permissions');
        }
    }
}
