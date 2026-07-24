<?php

namespace App\Controllers;

use App\Models\UsdtLotModel;
use App\Models\LotAllocationModel;
use App\Models\ActivityLogModel;
use App\Models\SupplierModel;

class LotsController extends BaseController
{
    public function index()
    {
        if ($response = $this->checkPermission('lots')) return $response;
        $lotModel      = new UsdtLotModel();
        $supplierModel = new SupplierModel();

        $filters = [
            'start_date'    => $this->request->getGet('start_date') ?? '',
            'end_date'      => $this->request->getGet('end_date')   ?? '',
            'supplier'      => $this->request->getGet('supplier')   ?? '',
            'delivery_type' => $this->request->getGet('delivery_type') ?? '',
            'status'        => $this->request->getGet('status')     ?? '',
        ];
        $perPage = (int)($this->request->getGet('per_page') ?? 15);
        if (!in_array($perPage, [15, 25, 50, 100])) { $perPage = 15; }

        $builder = $lotModel->select('usdt_lots.*, users.login as created_by_name')
            ->join('users', 'users.id = usdt_lots.created_by', 'left')
            ->where('usdt_lots.is_promotional', 0);

        if (!empty($filters['start_date'])) {
            $builder->where('usdt_lots.created_at >=', $filters['start_date'] . ' 00:00:00');
        }
        if (!empty($filters['end_date'])) {
            $builder->where('usdt_lots.created_at <=', $filters['end_date'] . ' 23:59:59');
        }
        if (!empty($filters['supplier'])) {
            $builder->like('usdt_lots.supplier', $filters['supplier']);
        }
        if (!empty($filters['delivery_type'])) {
            $builder->where('usdt_lots.delivery_type', $filters['delivery_type']);
        }
        if (!empty($filters['status'])) {
            $builder->where('usdt_lots.status', $filters['status']);
        }

        $lots = $builder->orderBy('usdt_lots.created_at', 'DESC')->paginate($perPage);

        return view('admin/lots/index', [
            'lots'        => $lots,
            'pager'       => $lotModel->pager,
            'filters'     => $filters,
            'per_page'    => $perPage,
            'suppliers'   => $supplierModel->getEnabled(),
            'summary'     => $lotModel->getSummary(),
            'active_menu' => 'lots',
        ]);
    }

    public function create()
    {
        if ($response = $this->checkPermission('lots')) return $response;
        $supplierModel = new SupplierModel();
        $userModel     = new \App\Models\UserModel();
        $users         = $userModel->where('role', 'user')->orderBy('login', 'ASC')->findAll();

        return view('admin/lots/new', [
            'suppliers'   => $supplierModel->getEnabled(),
            'users'       => $users,
            'active_menu' => 'lots',
        ]);
    }

    public function store()
    {
        if ($response = $this->checkPermission('lots')) return $response;
        $lotModel  = new UsdtLotModel();
        $logModel  = new ActivityLogModel();

        $usdtAmount     = (float)$this->request->getPost('usdt_amount');
        $conversionRate = (float)$this->request->getPost('conversion_rate');
        $supplier       = trim($this->request->getPost('supplier'));
        $purchaseHash   = trim($this->request->getPost('purchase_hash') ?? '');
        $deliveryType   = $this->request->getPost('delivery_type');

        $isPromotional  = (bool)$this->request->getPost('is_promotional');
        $targetType     = $this->request->getPost('target_type');
        $targetGroup    = $this->request->getPost('target_group');
        $targetUsersArr = $this->request->getPost('target_users');
        $promoRate      = $isPromotional ? (float)$this->request->getPost('promo_rate') : null;
        $closingDate    = $this->request->getPost('closing_date') ?: null;

        if ($usdtAmount <= 0 || $conversionRate <= 0 || empty($supplier)) {
            return redirect()->back()->withInput()->with('error', 'Preencha todos os campos obrigatórios.');
        }
        $totalBrl = round($usdtAmount * $conversionRate, 2);

        $targetUsers = null;
        if ($isPromotional && $targetType === 'users' && is_array($targetUsersArr)) {
            $targetUsers = json_encode(array_map('intval', $targetUsersArr));
        }

        $lotId = $lotModel->insert([
            'supplier'      => $supplier,
            'purchase_hash' => $purchaseHash ?: null,
            'delivery_type' => in_array($deliveryType, ['d+0', 'd+1', 'd+2']) ? $deliveryType : null,
            'usdt_amount'   => $usdtAmount,
            'conversion_rate' => $conversionRate,
            'total_brl'     => $totalBrl,
            'status'        => 'active',
            'created_by'    => session()->get('user_id'),
            'is_promotional' => $isPromotional ? 1 : 0,
            'target_type'    => $isPromotional ? $targetType : null,
            'target_group'   => ($isPromotional && $targetType === 'group') ? $targetGroup : null,
            'target_users'   => $targetUsers,
            'promo_rate'     => $promoRate,
            'closing_date'   => !empty($closingDate) ? $closingDate : null,
        ]);

        $logModel->record('lot.created', 'lot', $lotId, [
            'supplier'        => $supplier,
            'purchase_hash'   => $purchaseHash ?: null,
            'delivery_type'   => $deliveryType,
            'usdt_amount'     => $usdtAmount,
            'conversion_rate' => $conversionRate,
            'total_brl'       => $totalBrl,
            'is_promotional'  => $isPromotional,
            'target_type'     => $targetType,
            'target_group'    => $targetGroup,
            'target_users'    => $targetUsers,
            'promo_rate'      => $promoRate,
            'closing_date'    => $closingDate ?: null,
        ]);

        return redirect()->to("/admin/lots/{$lotId}")->with('success', 'Lote registrado com sucesso!');
    }

    public function show(int $id)
    {
        if ($response = $this->checkPermission('lots')) return $response;
        $lotModel = new UsdtLotModel();
        $logModel = new ActivityLogModel();

        $lot = $lotModel->select('usdt_lots.*, users.login as created_by_name')
            ->join('users', 'users.id = usdt_lots.created_by', 'left')
            ->where('usdt_lots.id', $id)
            ->first();

        if (!$lot) {
            return redirect()->to('/admin/lots')->with('error', 'Lote não encontrado.');
        }

        $db = \Config\Database::connect();
        $allocations = $db->query("
            SELECT
                la.*,
                CASE WHEN la.contract_id IS NOT NULL THEN 'contrato' ELSE 'transacao' END AS entity_label,
                COALESCE(uc.login, ut.login) AS client_name,
                allocators.login AS allocated_by_name,
                deliverers.login AS delivered_by_name
            FROM lot_allocations la
            LEFT JOIN contracts c       ON c.id = la.contract_id
            LEFT JOIN transactions t    ON t.id = la.transaction_id
            LEFT JOIN users uc          ON uc.id = c.user_id
            LEFT JOIN users ut          ON ut.id = t.user_id
            LEFT JOIN users allocators  ON allocators.id = la.allocated_by
            LEFT JOIN users deliverers  ON deliverers.id = la.delivered_by
            WHERE la.lot_id = ?
            ORDER BY la.created_at DESC
        ", [$id])->getResultArray();

        $logs = $logModel->getForEntity('lot', $id);

        $targetClients = [];
        if (isset($lot['is_promotional']) && $lot['is_promotional'] && ($lot['target_type'] ?? '') === 'users' && !empty($lot['target_users'])) {
            $userIds = json_decode($lot['target_users'], true);
            if (is_array($userIds) && !empty($userIds)) {
                $userModel = new \App\Models\UserModel();
                $targetClients = $userModel->select('login')->whereIn('id', $userIds)->findAll();
            }
        }

        $activeMenu = (isset($lot['is_promotional']) && $lot['is_promotional']) ? 'promotions' : 'lots';

        return view('admin/lots/show', [
            'lot'         => $lot,
            'allocations' => $allocations,
            'logs'        => $logs,
            'targetClients' => $targetClients,
            'active_menu' => $activeMenu,
        ]);
    }

    public function allocate()
    {
        if ($response = $this->checkPermission('lots')) return $response;
        $allocationModel = new LotAllocationModel();
        $lotModel        = new UsdtLotModel();
        $logModel        = new ActivityLogModel();

        $lotId         = (int)$this->request->getPost('lot_id');
        $usdtAmount    = (float)$this->request->getPost('usdt_amount');
        $contractId    = $this->request->getPost('contract_id')    ?: null;
        $transactionId = $this->request->getPost('transaction_id') ?: null;
        $retroactive   = (bool)$this->request->getPost('retroactive');
        $adminId       = session()->get('user_id');

        if ($retroactive) {
            $error = $this->validateRetroactiveAllocation($lotModel, $allocationModel, $lotId, $usdtAmount, $contractId);
        } else {
            $error = $this->validateAllocation($lotModel, $lotId, $usdtAmount, $contractId, $transactionId);
        }
        if ($error) {
            return $this->response->setJSON(['success' => false, 'message' => $error]);
        }

        if ($retroactive) {
            $lot            = $lotModel->find($lotId);
            $revenuePerUsdt = $allocationModel->getRevenuePerUsdt('contract', (int)$contractId);
            $profitBrl      = round(($revenuePerUsdt - (float)$lot['conversion_rate']) * $usdtAmount, 2);

            $allocationId = $allocationModel->insert([
                'lot_id'       => $lotId,
                'contract_id'  => $contractId,
                'usdt_amount'  => $usdtAmount,
                'status'       => 'delivered',
                'profit_brl'   => $profitBrl,
                'allocated_by' => $adminId,
                'delivered_by' => $adminId,
            ]);
        } else {
            $allocationId = $allocationModel->insert([
                'lot_id'         => $lotId,
                'contract_id'    => $contractId,
                'transaction_id' => $transactionId,
                'usdt_amount'    => $usdtAmount,
                'status'         => 'reserved',
                'allocated_by'   => $adminId,
            ]);
        }

        $lotModel->recalculateTotals($lotId);

        $entityType = $contractId ? 'contract' : 'transaction';
        $entityId   = (int)($contractId ?? $transactionId);

        $logModel->record('lot.allocated', 'lot', $lotId, [
            'allocation_id' => $allocationId,
            'entity_type'   => $entityType,
            'entity_id'     => $entityId,
            'usdt_amount'   => $usdtAmount,
            'retroactive'   => $retroactive,
        ]);

        return $this->response->setJSON(['success' => true, 'allocation_id' => $allocationId]);
    }

    private function validateAllocation(UsdtLotModel $lotModel, int $lotId, float $usdtAmount, ?string $contractId, ?string $transactionId): ?string
    {
        if (!$lotId || $usdtAmount <= 0 || (!$contractId && !$transactionId)) {
            return 'Dados inválidos.';
        }

        $error     = null;
        $available = $lotModel->getAvailable($lotId);

        if ($contractId && $transactionId) {
            $error = 'Informe apenas contrato ou transação, não ambos.';
        } elseif ($usdtAmount > $available) {
            $error = "Disponível no lote: {$available} USDT. Solicitado: {$usdtAmount} USDT.";
        } elseif ($contractId) {
            $db       = \Config\Database::connect();
            $contract = $db->table('contracts')->where('id', $contractId)->get()->getRow();
            if ($contract) {
                $alreadyReserved = (float)$db->query("
                    SELECT COALESCE(SUM(usdt_amount), 0) AS total
                    FROM lot_allocations
                    WHERE contract_id = ? AND status = 'reserved'
                ", [$contractId])->getRow()->total;

                $remainingToAllocate = max(0, (float)$contract->total_amount - (float)$contract->delivered_usdt - $alreadyReserved);

                if ($usdtAmount > $remainingToAllocate + 0.001) {
                    $error = 'O contrato precisa de apenas ' . number_format($remainingToAllocate, 2, '.', ',') . ' USDT. Não é possível alocar ' . number_format($usdtAmount, 2, '.', ',') . ' USDT.';
                }
            }
        }

        return $error;
    }

    private function validateRetroactiveAllocation(UsdtLotModel $lotModel, LotAllocationModel $allocationModel, int $lotId, float $usdtAmount, ?string $contractId): ?string
    {
        if (!$lotId || $usdtAmount <= 0 || !$contractId) {
            return 'Dados inválidos.';
        }

        $available = $lotModel->getAvailable($lotId);
        if ($usdtAmount > $available) {
            return "Disponível no lote: {$available} USDT. Solicitado: {$usdtAmount} USDT.";
        }

        $db       = \Config\Database::connect();
        $contract = $db->table('contracts')->where('id', $contractId)->get()->getRow();
        if (!$contract) {
            return 'Operação não encontrada.';
        }

        $unlinked = $allocationModel->getUnlinkedDelivered('contract', (int)$contractId, (float)$contract->delivered_usdt);
        if ($usdtAmount > $unlinked + 0.001) {
            return 'A operação possui apenas ' . number_format($unlinked, 2, '.', ',') . ' USDT entregue sem lote vinculado.';
        }

        return null;
    }

    public function cancelAllocation(int $id)
    {
        if ($response = $this->checkPermission('lots')) return $response;
        $allocationModel = new LotAllocationModel();
        $lotModel        = new UsdtLotModel();
        $logModel        = new ActivityLogModel();

        $allocation = $allocationModel->find($id);
        if (!$allocation) {
            return $this->response->setJSON(['success' => false, 'message' => 'Alocação não encontrada.']);
        }

        if ($allocation['status'] === 'delivered') {
            return $this->response->setJSON(['success' => false, 'message' => 'Alocação já entregue não pode ser cancelada.']);
        }

        $allocationModel->update($id, ['status' => 'cancelled']);
        $lotModel->recalculateTotals((int)$allocation['lot_id']);

        $logModel->record('lot.allocation_cancelled', 'lot', (int)$allocation['lot_id'], [
            'allocation_id' => $id,
            'usdt_amount'   => $allocation['usdt_amount'],
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    public function quickBuy()
    {
        if ($response = $this->checkPermission('lots')) return $response;
        $lotModel        = new UsdtLotModel();
        $allocationModel = new LotAllocationModel();
        $logModel        = new ActivityLogModel();

        $contractId     = (int)$this->request->getPost('contract_id');
        $usdtAmount     = (float)$this->request->getPost('usdt_amount');
        $conversionRate = (float)$this->request->getPost('conversion_rate');
        $totalBrl       = (float)$this->request->getPost('total_brl');
        $supplier       = trim($this->request->getPost('supplier'));
        $deliveryType   = trim($this->request->getPost('delivery_type') ?? '');
        $purchaseHash   = trim($this->request->getPost('purchase_hash') ?? '');
        $retroactive    = (bool)$this->request->getPost('retroactive');
        $adminId        = session()->get('user_id');

        $validationError = match(true) {
            !$contractId || $usdtAmount <= 0 || $conversionRate <= 0 || empty($supplier)
                => 'Preencha todos os campos obrigatórios.',
            !in_array($deliveryType, ['d+0', 'd+1', 'd+2'])
                => 'Informe o fluxo do fornecedor (D+0, D+1 ou D+2).',
            default => null,
        };
        if ($validationError) {
            return $this->response->setJSON(['success' => false, 'message' => $validationError]);
        }

        $db = \Config\Database::connect();
        $contract = $db->table('contracts')->where('id', $contractId)->get()->getRow();
        if (!$contract) {
            return $this->response->setJSON(['success' => false, 'message' => 'Contrato não encontrado.']);
        }

        if ($retroactive) {
            $unlinked = $allocationModel->getUnlinkedDelivered('contract', $contractId, (float)$contract->delivered_usdt);
            if ($usdtAmount > $unlinked + 0.001) {
                return $this->response->setJSON(['success' => false, 'message' => 'A operação possui apenas ' . number_format($unlinked, 2, '.', ',') . ' USDT entregue sem lote vinculado.']);
            }
        }

        $autoTotal    = round($usdtAmount * $conversionRate, 2);
        $isOverridden = abs($totalBrl - $autoTotal) > 0.01;
        $finalBrl     = $totalBrl > 0 ? $totalBrl : $autoTotal;

        $lotId = $lotModel->insert([
            'supplier'             => $supplier,
            'purchase_hash'        => $purchaseHash,
            'delivery_type'        => $deliveryType,
            'usdt_amount'          => $usdtAmount,
            'conversion_rate'      => $conversionRate,
            'total_brl'            => $finalBrl,
            'total_brl_overridden' => $isOverridden ? 1 : 0,
            'status'               => 'active',
            'created_by'           => $adminId,
        ]);

        $logModel->record('lot.created', 'lot', $lotId, [
            'supplier'        => $supplier,
            'usdt_amount'     => $usdtAmount,
            'conversion_rate' => $conversionRate,
            'total_brl'       => $finalBrl,
            'quick_buy'       => true,
            'contract_id'     => $contractId,
        ]);

        if ($retroactive) {
            $revenuePerUsdt = $allocationModel->getRevenuePerUsdt('contract', $contractId);
            $profitBrl      = round(($revenuePerUsdt - $conversionRate) * $usdtAmount, 2);

            $allocationId = $allocationModel->insert([
                'lot_id'       => $lotId,
                'contract_id'  => $contractId,
                'usdt_amount'  => $usdtAmount,
                'status'       => 'delivered',
                'profit_brl'   => $profitBrl,
                'allocated_by' => $adminId,
                'delivered_by' => $adminId,
            ]);
        } else {
            $allocationId = $allocationModel->insert([
                'lot_id'       => $lotId,
                'contract_id'  => $contractId,
                'usdt_amount'  => $usdtAmount,
                'status'       => 'reserved',
                'allocated_by' => $adminId,
            ]);
        }

        $lotModel->recalculateTotals($lotId);

        $logModel->record('lot.allocated', 'lot', $lotId, [
            'allocation_id' => $allocationId,
            'entity_type'   => 'contract',
            'entity_id'     => $contractId,
            'usdt_amount'   => $usdtAmount,
            'quick_buy'     => true,
            'retroactive'   => $retroactive,
        ]);

        return $this->response->setJSON(['success' => true, 'lot_id' => $lotId]);
    }

    public function availableLots()
    {
        $lotModel = new UsdtLotModel();

        $lots = $lotModel->select('id, supplier, usdt_amount, usdt_reserved, usdt_delivered, conversion_rate, total_brl, status')
            ->where('status', 'active')
            ->where('is_promotional', 0)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $result = array_map(function ($lot) use ($lotModel) {
            $lot['usdt_available'] = $lotModel->getAvailable((int)$lot['id']);
            return $lot;
        }, $lots);

        return $this->response->setJSON($result);
    }

    public function promotions()
    {
        if ($response = $this->checkPermission('lots')) return $response;
        $lotModel      = new UsdtLotModel();
        $supplierModel = new SupplierModel();

        $filters = [
            'start_date'    => $this->request->getGet('start_date') ?? '',
            'end_date'      => $this->request->getGet('end_date')   ?? '',
            'supplier'      => $this->request->getGet('supplier')   ?? '',
            'delivery_type' => $this->request->getGet('delivery_type') ?? '',
        ];
        $perPage = (int)($this->request->getGet('per_page') ?? 15);
        if (!in_array($perPage, [15, 25, 50, 100])) { $perPage = 15; }

        $builder = $lotModel->select('usdt_lots.*, users.login as created_by_name')
            ->join('users', 'users.id = usdt_lots.created_by', 'left')
            ->where('usdt_lots.is_promotional', 1)
            ->where('usdt_lots.status', 'active')
            ->where('(usdt_lots.usdt_amount - usdt_lots.usdt_reserved - usdt_lots.usdt_delivered) >', 0);

        if (!empty($filters['start_date'])) {
            $builder->where('usdt_lots.created_at >=', $filters['start_date'] . ' 00:00:00');
        }
        if (!empty($filters['end_date'])) {
            $builder->where('usdt_lots.created_at <=', $filters['end_date'] . ' 23:59:59');
        }
        if (!empty($filters['supplier'])) {
            $builder->like('usdt_lots.supplier', $filters['supplier']);
        }
        if (!empty($filters['delivery_type'])) {
            $builder->where('usdt_lots.delivery_type', $filters['delivery_type']);
        }

        $lots = $builder->orderBy('usdt_lots.created_at', 'DESC')->paginate($perPage);

        return view('admin/promotions/index', [
            'lots'        => $lots,
            'pager'       => $lotModel->pager,
            'filters'     => $filters,
            'per_page'    => $perPage,
            'suppliers'   => $supplierModel->getEnabled(),
            'summary'     => $lotModel->getSummary(true),
            'active_menu' => 'promotions',
        ]);
    }

    public function createPromotion()
    {
        if ($response = $this->checkPermission('lots')) return $response;
        $supplierModel = new SupplierModel();
        $userModel     = new \App\Models\UserModel();
        $users         = $userModel->where('role', 'user')->orderBy('login', 'ASC')->findAll();

        return view('admin/promotions/new', [
            'suppliers'   => $supplierModel->getEnabled(),
            'users'       => $users,
            'active_menu' => 'promotions',
        ]);
    }

    public function storePromotion()
    {
        if ($response = $this->checkPermission('lots')) return $response;
        $lotModel  = new UsdtLotModel();
        $logModel  = new ActivityLogModel();

        $usdtAmount     = (float)$this->request->getPost('usdt_amount');
        $conversionRate = (float)$this->request->getPost('conversion_rate');
        $promoRate      = $conversionRate;
        $supplier       = trim($this->request->getPost('supplier') ?? '');
        $purchaseHash   = trim($this->request->getPost('purchase_hash') ?? '');
        $deliveryType   = $this->request->getPost('delivery_type');

        $targetType     = $this->request->getPost('target_type') ?: 'all';
        $targetUsersArr = $this->request->getPost('target_users');

        if ($usdtAmount <= 0 || $conversionRate <= 0) {
            return redirect()->back()->withInput()->with('error', 'Preencha todos os campos obrigatórios (Quantidade e Taxa por USDT).');
        }

        if (empty($supplier)) {
            $supplier = 'Promoção';
        }
        $totalBrl = round($usdtAmount * $conversionRate, 2);

        $targetUsers = null;
        if ($targetType === 'users' && is_array($targetUsersArr)) {
            $targetUsers = json_encode(array_map('intval', $targetUsersArr));
        }

        $lotId = $lotModel->insert([
            'supplier'        => $supplier,
            'purchase_hash'   => $purchaseHash ?: null,
            'delivery_type'   => in_array($deliveryType, ['d+0', 'd+1', 'd+2']) ? $deliveryType : null,
            'usdt_amount'     => $usdtAmount,
            'conversion_rate' => $conversionRate,
            'total_brl'       => $totalBrl,
            'status'          => 'active',
            'created_by'      => session()->get('user_id'),
            'is_promotional'  => 1,
            'target_type'     => $targetType,
            'target_group'    => null,
            'target_users'    => $targetUsers,
            'promo_rate'      => $promoRate,
        ]);

        $logModel->record('lot.created', 'lot', $lotId, [
            'supplier'        => $supplier,
            'purchase_hash'   => $purchaseHash ?: null,
            'delivery_type'   => $deliveryType,
            'usdt_amount'     => $usdtAmount,
            'conversion_rate' => $conversionRate,
            'total_brl'       => $totalBrl,
            'is_promotional'  => true,
            'target_type'     => $targetType,
            'target_group'    => null,
            'target_users'    => $targetUsers,
            'promo_rate'      => $promoRate,
        ]);

        return redirect()->to("/admin/lots/{$lotId}")->with('success', 'Promoção lançada com sucesso!');
    }

    public function deactivatePromotion(int $id)
    {
        if ($response = $this->checkPermission('lots')) return $response;
        $lotModel = new UsdtLotModel();
        $logModel = new ActivityLogModel();

        $lot = $lotModel->find($id);
        if (!$lot || $lot['is_promotional'] != 1) {
            return redirect()->to('/admin/promotions')->with('error', 'Promoção não encontrada.');
        }

        $lotModel->update($id, ['status' => 'cancelled']);

        $logModel->record('lot.deactivated', 'lot', $id, [
            'is_promotional' => true,
        ]);

        return redirect()->to('/admin/promotions')->with('success', 'Promoção desativada com sucesso!');
    }
}
