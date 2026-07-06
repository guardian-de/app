<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateContractTypeEnum extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('contracts', [
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['d+0', 'd+1', 'd+2'],
                'default'    => 'd+0',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('contracts', [
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['d+1', 'd+2'],
                'default'    => 'd+1',
            ],
        ]);
    }
}
