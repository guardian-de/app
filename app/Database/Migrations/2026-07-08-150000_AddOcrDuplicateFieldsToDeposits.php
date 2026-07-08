<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOcrDuplicateFieldsToDeposits extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('ocr_code', 'deposits')) {
            $this->forge->addColumn('deposits', [
                'ocr_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                    'null'       => true,
                    'after'      => 'ocr_raw_text',
                ],
            ]);
        }
        if (!$this->db->fieldExists('is_duplicate', 'deposits')) {
            $this->forge->addColumn('deposits', [
                'is_duplicate' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'null'       => false,
                    'after'      => 'ocr_code',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('is_duplicate', 'deposits')) {
            $this->forge->dropColumn('deposits', 'is_duplicate');
        }
        if ($this->db->fieldExists('ocr_code', 'deposits')) {
            $this->forge->dropColumn('deposits', 'ocr_code');
        }
    }
}
