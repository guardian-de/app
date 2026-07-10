<?php

namespace App\Models;

use CodeIgniter\Model;

class UsdtLotModel extends Model
{
    protected $table      = 'usdt_lots';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'supplier', 'purchase_hash', 'delivery_type',
        'usdt_amount', 'conversion_rate', 'total_brl', 'total_brl_overridden',
        'usdt_reserved', 'usdt_delivered', 'profit_brl',
        'status', 'created_by', 'is_promotional', 'target_type', 'target_group', 'target_users', 'promo_rate'
    ];

    public function getAvailable(int $lotId): float
    {
        $lot = $this->find($lotId);
        if (!$lot) return 0.0;
        return max(0, (float)$lot['usdt_amount'] - (float)$lot['usdt_reserved'] - (float)$lot['usdt_delivered']);
    }

    public function recalculateTotals(int $lotId): void
    {
        $db = \Config\Database::connect();

        $row = $db->query("
            SELECT
                COALESCE(SUM(CASE WHEN status = 'reserved'  THEN usdt_amount ELSE 0 END), 0) AS reserved,
                COALESCE(SUM(CASE WHEN status = 'delivered' THEN usdt_amount ELSE 0 END), 0) AS delivered,
                COALESCE(SUM(CASE WHEN status = 'delivered' THEN profit_brl  ELSE 0 END), 0) AS profit
            FROM lot_allocations
            WHERE lot_id = ?
        ", [$lotId])->getRow();

        $lot     = $this->find($lotId);
        $status  = $lot['status'];
        $total   = (float)$lot['usdt_amount'];
        $delivered = (float)$row->delivered;

        if ($status !== 'cancelled' && $delivered >= $total) {
            $status = 'depleted';
        }

        $this->update($lotId, [
            'usdt_reserved'  => (float)$row->reserved,
            'usdt_delivered' => $delivered,
            'profit_brl'     => (float)$row->profit,
            'status'         => $status,
        ]);
    }

    public function getSummary(): array
    {
        $db  = \Config\Database::connect();
        $row = $db->query("
            SELECT
                COUNT(*) AS total_lots,
                COALESCE(SUM(usdt_amount), 0)   AS total_usdt,
                COALESCE(SUM(usdt_delivered), 0) AS total_delivered,
                COALESCE(SUM(usdt_reserved), 0)  AS total_reserved,
                COALESCE(SUM(profit_brl), 0)     AS total_profit
            FROM usdt_lots
        ")->getRow();

        return $row ? (array)$row : [];
    }
}
