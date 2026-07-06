<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameCreditLimitToScore extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('credit_limit', 'users') && !$this->db->fieldExists('score', 'users')) {
            $this->forge->modifyColumn('users', [
                'credit_limit' => [
                    'name'       => 'score',
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => false,
                    'default'    => 0.00,
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('score', 'users') && !$this->db->fieldExists('credit_limit', 'users')) {
            $this->forge->modifyColumn('users', [
                'score' => [
                    'name'       => 'credit_limit',
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => false,
                    'default'    => 0.00,
                ],
            ]);
        }
    }
}
