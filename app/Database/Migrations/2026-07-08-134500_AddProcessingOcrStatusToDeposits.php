<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProcessingOcrStatusToDeposits extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('deposits', [
            'ocr_status' => [
                'type'       => 'ENUM',
                'constraint' => ['processing', 'ok', 'needs_review'],
                'default'    => 'processing',
                'null'       => false,
            ],
        ]);
    }

    public function down()
    {
        $this->db->query("UPDATE deposits SET ocr_status = 'needs_review' WHERE ocr_status = 'processing'");
        $this->forge->modifyColumn('deposits', [
            'ocr_status' => [
                'type'       => 'ENUM',
                'constraint' => ['ok', 'needs_review'],
                'default'    => 'needs_review',
                'null'       => false,
            ],
        ]);
    }
}
