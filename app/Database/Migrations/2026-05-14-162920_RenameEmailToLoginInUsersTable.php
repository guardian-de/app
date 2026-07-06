<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameEmailToLoginInUsersTable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('users', [
            'email' => [
                'name' => 'login',
                'type' => 'VARCHAR',
                'constraint' => '150',
                'unique'     => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('users', [
            'login' => [
                'name' => 'email',
                'type' => 'VARCHAR',
                'constraint' => '150',
                'unique'     => true,
            ],
        ]);
    }
}
