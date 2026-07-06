<?php

namespace App\Models;

use CodeIgniter\Model;

class LotAllocationModel extends Model
{
    protected $table            = 'lot_allocations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'lot_id', 'contract_id', 'transaction_id', 'usdt_amount',
        'status', 'profit_brl', 'allocated_by', 'delivered_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowCallbacks = true;
    protected $beforeUpdate   = ['guardDelivered'];

    protected function guardDelivered(array $data): array
    {
        if (!isset($data['id'])) return $data;

        foreach ((array)$data['id'] as $id) {
            $existing = $this->find($id);
            if ($existing && $existing['status'] === 'delivered') {
                unset($data['data']['status']);
                unset($data['data']['usdt_amount']);
                unset($data['data']['lot_id']);
            }
        }

        return $data;
    }

    public function getAllocatedForEntity(string $entityType, int $entityId): array
    {
        $field = $entityType === 'contract' ? 'contract_id' : 'transaction_id';

        return $this->select('lot_allocations.*, usdt_lots.supplier, usdt_lots.conversion_rate, usdt_lots.total_brl as lot_total_brl, usdt_lots.usdt_amount as lot_usdt_amount, allocators.name as allocated_by_name, deliverers.name as delivered_by_name')
            ->join('usdt_lots', 'usdt_lots.id = lot_allocations.lot_id')
            ->join('users as allocators', 'allocators.id = lot_allocations.allocated_by', 'left')
            ->join('users as deliverers', 'deliverers.id = lot_allocations.delivered_by', 'left')
            ->where("lot_allocations.{$field}", $entityId)
            ->whereIn('lot_allocations.status', ['reserved', 'delivered'])
            ->orderBy('lot_allocations.created_at', 'ASC')
            ->findAll();
    }

    public function getTotalAllocatedUsdt(string $entityType, int $entityId): float
    {
        $field = $entityType === 'contract' ? 'contract_id' : 'transaction_id';

        $row = $this->selectSum('usdt_amount')
            ->where($field, $entityId)
            ->whereIn('status', ['reserved', 'delivered'])
            ->first();

        return (float)($row['usdt_amount'] ?? 0);
    }

    /**
     * USDT já entregue à operação (delivered_usdt) mas ainda sem lote vinculado —
     * ocorre quando o envio foi registrado fora do fluxo normal, sem lote reservado.
     */
    public function getUnlinkedDelivered(string $entityType, int $entityId, float $deliveredUsdt): float
    {
        $field = $entityType === 'contract' ? 'contract_id' : 'transaction_id';

        $row = $this->selectSum('usdt_amount')
            ->where($field, $entityId)
            ->where('status', 'delivered')
            ->first();

        $linkedDelivered = (float)($row['usdt_amount'] ?? 0);

        return max(0, round($deliveredUsdt - $linkedDelivered, 4));
    }

    public function markDelivered(int $allocationId, int $deliveredBy): bool
    {
        $allocation = $this->find($allocationId);
        if (!$allocation || $allocation['status'] !== 'reserved') return false;

        $lotModel = new UsdtLotModel();
        $lot = $lotModel->find($allocation['lot_id']);
        if (!$lot) return false;

        $costPerUsdt    = (float)$lot['conversion_rate'];
        $entityType     = $allocation['contract_id'] ? 'contract' : 'transaction';
        $entityId       = (int)($allocation['contract_id'] ?? $allocation['transaction_id']);
        $revenuePerUsdt = $this->getRevenuePerUsdt($entityType, $entityId);
        $profitBrl      = round(($revenuePerUsdt - $costPerUsdt) * (float)$allocation['usdt_amount'], 2);

        $this->update($allocationId, [
            'status'       => 'delivered',
            'profit_brl'   => $profitBrl,
            'delivered_by' => $deliveredBy,
        ]);

        $lotModel->recalculateTotals((int)$allocation['lot_id']);

        return true;
    }

    /**
     * Entrega (total ou parcial) a partir de uma reserva, gravando profit_brl.
     * Em entrega parcial, divide a alocação e mantém o saldo como 'reserved'.
     * Espera a linha da reserva com 'conversion_rate' do lote já joinado.
     */
    public function deliverFromReservation(array $res, float $amountUsdt, int $deliveredBy, float $revenuePerUsdt): void
    {
        $resAmount   = (float)$res['usdt_amount'];
        $costPerUsdt = (float)$res['conversion_rate'];
        $amountUsdt  = min($amountUsdt, $resAmount);
        $profitBrl   = round(($revenuePerUsdt - $costPerUsdt) * $amountUsdt, 2);

        if ($resAmount - $amountUsdt < 0.0001) {
            $this->db->query(
                "UPDATE lot_allocations SET status = 'delivered', profit_brl = ?, delivered_by = ?, updated_at = NOW() WHERE id = ?",
                [$profitBrl, $deliveredBy, $res['id']]
            );
        } else {
            $this->db->query(
                "UPDATE lot_allocations SET usdt_amount = ?, status = 'delivered', profit_brl = ?, delivered_by = ?, updated_at = NOW() WHERE id = ?",
                [$amountUsdt, $profitBrl, $deliveredBy, $res['id']]
            );
            $this->db->query(
                "INSERT INTO lot_allocations (lot_id, contract_id, transaction_id, usdt_amount, status, allocated_by, created_at, updated_at)
                 VALUES (?, ?, ?, ?, 'reserved', ?, NOW(), NOW())",
                [$res['lot_id'], $res['contract_id'], $res['transaction_id'] ?? null, round($resAmount - $amountUsdt, 4), $res['allocated_by']]
            );
        }

        (new UsdtLotModel())->recalculateTotals((int)$res['lot_id']);
    }

    /**
     * Reservas de lote dos contratos entregáveis de um cliente, ordenadas pela
     * maior margem (taxa do cliente − custo do lote). Usa FOR UPDATE: chamar
     * dentro de uma transação.
     */
    public function getReservedForUserByMargin(int $userId): array
    {
        return $this->db->query("
            SELECT
                la.id, la.lot_id, la.contract_id, la.transaction_id, la.usdt_amount, la.allocated_by,
                ul.conversion_rate,
                c.comercial_brl, c.total_amount, c.total_brl, c.paid_amount, c.delivered_usdt,
                (c.comercial_brl / NULLIF(c.total_amount, 0)) - ul.conversion_rate AS margin_per_usdt
            FROM lot_allocations la
            JOIN usdt_lots ul ON ul.id = la.lot_id
            JOIN contracts c ON c.id = la.contract_id
            WHERE la.status = 'reserved'
              AND c.user_id = ?
              AND (c.delivery_blocked IS NULL OR c.delivery_blocked = 0)
              AND c.total_brl > 0
              AND c.paid_amount > 0
            ORDER BY margin_per_usdt DESC, la.created_at ASC
            FOR UPDATE
        ", [$userId])->getResultArray();
    }

    public function getRevenuePerUsdt(string $entityType, int $entityId): float
    {
        $db = \Config\Database::connect();

        if ($entityType === 'contract') {
            $row = $db->table('contracts')->select('comercial_brl, total_amount')->where('id', $entityId)->get()->getRow();
            if ($row && (float)$row->total_amount > 0) {
                return (float)$row->comercial_brl / (float)$row->total_amount;
            }
        } else {
            $row = $db->table('transactions')->select('comercial_brl, amount_usdt')->where('id', $entityId)->get()->getRow();
            if ($row && (float)$row->amount_usdt > 0) {
                return (float)$row->comercial_brl / (float)$row->amount_usdt;
            }
        }

        return 0.0;
    }
}
