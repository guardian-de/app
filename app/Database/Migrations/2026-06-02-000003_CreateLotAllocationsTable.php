<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLotAllocationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'lot_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'contract_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'transaction_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'usdt_amount'    => ['type' => 'DECIMAL', 'constraint' => '18,4'],
            'status'         => ['type' => 'ENUM', 'constraint' => ['reserved', 'delivered', 'cancelled'], 'default' => 'reserved'],
            'profit_brl'     => ['type' => 'DECIMAL', 'constraint' => '18,2', 'null' => true],
            'allocated_by'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'delivered_by'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('lot_id', 'usdt_lots', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('contract_id', 'contracts', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('transaction_id', 'transactions', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('allocated_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('delivered_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('lot_allocations');
    }

    public function down()
    {
        $this->forge->dropTable('lot_allocations');
    }
}
