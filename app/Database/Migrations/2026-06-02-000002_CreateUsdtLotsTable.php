<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsdtLotsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'supplier'            => ['type' => 'VARCHAR', 'constraint' => 150],
            'purchase_hash'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'delivery_type'       => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'usdt_amount'         => ['type' => 'DECIMAL', 'constraint' => '18,4'],
            'conversion_rate'     => ['type' => 'DECIMAL', 'constraint' => '18,6'],
            'total_brl'           => ['type' => 'DECIMAL', 'constraint' => '18,2'],
            'total_brl_overridden'=> ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'usdt_reserved'       => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => 0],
            'usdt_delivered'      => ['type' => 'DECIMAL', 'constraint' => '18,4', 'default' => 0],
            'profit_brl'          => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'status'              => ['type' => 'ENUM', 'constraint' => ['active', 'depleted', 'cancelled'], 'default' => 'active'],
            'created_by'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('usdt_lots');
    }

    public function down()
    {
        $this->forge->dropTable('usdt_lots');
    }
}
