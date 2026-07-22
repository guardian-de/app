<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;

class AdminChatController extends BaseController
{
    public function index()
    {
        if ($response = $this->checkPermission('chat')) return $response;

        return view('admin/chat/index');
    }

    public function getUsers()
    {
        if ($response = $this->checkPermission('chat')) return $response;

        $db = Database::connect();
        
        // Retorna todos os usuários que possuem histórico de chat
        $sql = "
            SELECT DISTINCT u.id, u.login, u.role,
                   (SELECT message FROM chat_messages WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as last_message,
                   (SELECT sender FROM chat_messages WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as last_sender,
                   (SELECT created_at FROM chat_messages WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as last_message_time
            FROM users u
            JOIN chat_messages cm ON cm.user_id = u.id
            ORDER BY last_message_time DESC
        ";
        
        $query = $db->query($sql);
        $users = $query->getResultArray();

        return $this->response->setJSON($users);
    }

    public function getMessages($userId)
    {
        if ($response = $this->checkPermission('chat')) return $response;

        $db = Database::connect();
        $lastId = (int)$this->request->getGet('last_id');

        $builder = $db->table('chat_messages')
            ->where('user_id', $userId);

        if ($lastId > 0) {
            $builder->where('id >', $lastId);
        }

        $messages = $builder->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($messages);
    }

    public function send()
    {
        if ($response = $this->checkPermission('chat')) return $response;

        $json = $this->request->getJSON();
        $userId = $json->user_id ?? null;
        $message = $json->message ?? '';

        if (!$userId || empty($message)) {
            return $this->response->setJSON(['error' => 'Invalid parameters'])->setStatusCode(400);
        }

        $db = Database::connect();
        $senderRole = session()->get('user_role'); // 'admin' ou 'operator'

        $db->table('chat_messages')->insert([
            'user_id'    => $userId,
            'sender'     => $senderRole,
            'message'    => $message,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    public function close($userId)
    {
        if ($response = $this->checkPermission('chat')) return $response;

        $db = Database::connect();
        $db->table('chat_messages')->where('user_id', $userId)->delete();

        return $this->response->setJSON(['success' => true]);
    }
}
