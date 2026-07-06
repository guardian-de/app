<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table      = 'activity_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'user_id', 'action', 'entity_type', 'entity_id', 'payload', 'ip_address', 'created_at',
    ];

    public function record(string $action, string $entityType, int $entityId, array $payload = []): int|string
    {
        return $this->insert([
            'user_id'     => session()->get('user_id'),
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'payload'     => json_encode($payload),
            'ip_address'  => \Config\Services::request()->getIPAddress(),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function getForEntity(string $entityType, int $entityId): array
    {
        return $this->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
