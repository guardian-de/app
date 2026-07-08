<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAiFieldsToDeposits extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('deposits', [
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
        ]);

        if (!$this->db->fieldExists('ai_amount', 'deposits')) {
            $this->forge->addColumn('deposits', [
                'ai_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true, 'after' => 'amount'],
            ]);
        }
        if (!$this->db->fieldExists('ocr_status', 'deposits')) {
            $this->forge->addColumn('deposits', [
                'ocr_status' => ['type' => 'ENUM', 'constraint' => ['ok', 'needs_review'], 'default' => 'needs_review', 'null' => false, 'after' => 'ai_amount'],
            ]);
        }
        if (!$this->db->fieldExists('ocr_raw_text', 'deposits')) {
            $this->forge->addColumn('deposits', [
                'ocr_raw_text' => ['type' => 'TEXT', 'null' => true, 'after' => 'ocr_status'],
            ]);
        }
        if (!$this->db->fieldExists('amount_edited_reason', 'deposits')) {
            $this->forge->addColumn('deposits', [
                'amount_edited_reason' => ['type' => 'TEXT', 'null' => true, 'after' => 'ocr_raw_text'],
            ]);
        }
        if (!$this->db->fieldExists('amount_edited_by', 'deposits')) {
            $this->forge->addColumn('deposits', [
                'amount_edited_by' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'after' => 'amount_edited_reason'],
            ]);
        }
        if (!$this->db->fieldExists('amount_edited_at', 'deposits')) {
            $this->forge->addColumn('deposits', [
                'amount_edited_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'amount_edited_by'],
            ]);
        }
    }

    public function down()
    {
        foreach (['amount_edited_at', 'amount_edited_by', 'amount_edited_reason', 'ocr_raw_text', 'ocr_status', 'ai_amount'] as $col) {
            if ($this->db->fieldExists($col, 'deposits')) {
                $this->forge->dropColumn('deposits', $col);
            }
        }
        $this->forge->modifyColumn('deposits', [
            'amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => false],
        ]);
    }
}
