<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatMessagesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'sender' => [
                'type'       => 'ENUM',
                'constraint' => ['user', 'bot'],
                'default'    => 'user',
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'show_buy' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,4',
                'null'       => true,
            ],
            'suggested_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('chat_messages');
    }

    public function down()
    {
        $this->forge->dropTable('chat_messages');
    }
}
