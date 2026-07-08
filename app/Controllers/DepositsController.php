<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DepositModel;
use App\Models\FinancialStatementModel;
use App\Models\ActivityLogModel;
class DepositsController extends BaseController
{
    public function index()
    {
        if ($response = $this->checkPermission('deposits')) return $response;
        $db = \Config\Database::connect();
        try {
            $db->query("CREATE TABLE IF NOT EXISTS `deposits` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT UNSIGNED NOT NULL,
                `amount` DECIMAL(15,2) NOT NULL,
                `proof_file` VARCHAR(500) NOT NULL,
                `status` ENUM('pending','accepted','reversed','rejected') NOT NULL DEFAULT 'pending',
                `notes` TEXT NULL,
                `accepted_by` INT UNSIGNED NULL,
                `accepted_at` DATETIME NULL,
                `reversed_by` INT UNSIGNED NULL,
                `reversed_at` DATETIME NULL,
                `reversal_reason` TEXT NULL,
                `rejected_by` INT UNSIGNED NULL,
                `rejected_at` DATETIME NULL,
                `rejection_reason` TEXT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Throwable $e) { log_message('debug', 'deposits table guard: ' . $e->getMessage()); }
        try { $db->query("ALTER TABLE `deposits` MODIFY COLUMN `status` ENUM('pending','accepted','reversed','rejected') NOT NULL DEFAULT 'pending'"); } catch (\Throwable $e) {}
        try { $db->query("ALTER TABLE `deposits` ADD COLUMN `rejected_by` INT UNSIGNED NULL"); } catch (\Throwable $e) {}
        try { $db->query("ALTER TABLE `deposits` ADD COLUMN `rejected_at` DATETIME NULL"); } catch (\Throwable $e) {}
        try { $db->query("ALTER TABLE `deposits` ADD COLUMN `rejection_reason` TEXT NULL"); } catch (\Throwable $e) {}
        try { $db->query("ALTER TABLE `deposits` MODIFY COLUMN `amount` DECIMAL(15,2) NULL"); } catch (\Throwable $e) {}
        try { $db->query("ALTER TABLE `deposits` ADD COLUMN `ai_amount` DECIMAL(15,2) NULL"); } catch (\Throwable $e) {}
        try { $db->query("ALTER TABLE `deposits` ADD COLUMN `ocr_status` ENUM('processing','ok','needs_review') NOT NULL DEFAULT 'processing'"); } catch (\Throwable $e) {}
        try { $db->query("ALTER TABLE `deposits` MODIFY COLUMN `ocr_status` ENUM('processing','ok','needs_review') NOT NULL DEFAULT 'processing'"); } catch (\Throwable $e) {}
        try { $db->query("ALTER TABLE `deposits` ADD COLUMN `ocr_raw_text` TEXT NULL"); } catch (\Throwable $e) {}
        try { $db->query("ALTER TABLE `deposits` ADD COLUMN `amount_edited_reason` TEXT NULL"); } catch (\Throwable $e) {}
        try { $db->query("ALTER TABLE `deposits` ADD COLUMN `amount_edited_by` INT UNSIGNED NULL"); } catch (\Throwable $e) {}
        try { $db->query("ALTER TABLE `deposits` ADD COLUMN `amount_edited_at` DATETIME NULL"); } catch (\Throwable $e) {}

        $status    = $this->request->getGet('status') ?? '';
        $search    = $this->request->getGet('search') ?? '';
        $startDate = $this->request->getGet('start_date') ?? '';
        $endDate   = $this->request->getGet('end_date') ?? '';
        $perPage   = (int) ($this->request->getGet('per_page') ?? 50);

        $depositModel = new DepositModel();
        $builder = $depositModel->select('deposits.*, u.login as user_login')
            ->join('users u', 'u.id = deposits.user_id', 'left')
            ->orderBy('deposits.created_at', 'DESC');

        if ($status !== '') {
            $builder->where('deposits.status', $status);
        }
        if ($search !== '') {
            $builder->like('u.login', $search);
        }
        if ($startDate !== '') {
            $builder->where('DATE(deposits.created_at) >=', $startDate);
        }
        if ($endDate !== '') {
            $builder->where('DATE(deposits.created_at) <=', $endDate);
        }

        $deposits = $builder->paginate($perPage);

        $data = [
            'title'       => 'Depósitos',
            'active_menu' => 'deposits',
            'deposits'    => $deposits,
            'pager'       => $depositModel->pager,
            'per_page'    => $perPage,
            'filters'     => compact('status', 'search', 'startDate', 'endDate'),
        ];

        return view('admin/deposits/index', $data);
    }

    public function checkNew()
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('deposits')) {
            return $this->response->setJSON([]);
        }

        $depositModel = new DepositModel();
        $rows = $depositModel
            ->select('deposits.id, deposits.amount as amount_brl, deposits.user_id, deposits.created_at, u.login as user_name')
            ->join('users u', 'u.id = deposits.user_id', 'left')
            ->where('deposits.status', 'pending')
            ->orderBy('deposits.id', 'ASC')
            ->findAll();

        return $this->response->setJSON($rows);
    }

    public function show($id)
    {
        if ($response = $this->checkPermission('deposits')) return $response;
        $db = \Config\Database::connect();
        $deposit = $db->table('deposits d')
            ->select('d.*, u.login as user_login, a.login as accepted_by_login, r.login as reversed_by_login, rj.login as rejected_by_login')
            ->join('users u', 'u.id = d.user_id', 'left')
            ->join('users a', 'a.id = d.accepted_by', 'left')
            ->join('users r', 'r.id = d.reversed_by', 'left')
            ->join('users rj', 'rj.id = d.rejected_by', 'left')
            ->where('d.id', $id)
            ->get()->getRowArray();

        if (!$deposit) {
            return redirect()->to(url_to('admin_deposits'))->with('error', 'Depósito não encontrado.');
        }

        $history = $db->table('activity_logs al')
            ->select('al.*, u.login as actor_login')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->where('al.entity_type', 'deposit')
            ->where('al.entity_id', $id)
            ->orderBy('al.created_at', 'DESC')
            ->get()->getResultArray();

        foreach ($history as &$entry) {
            $entry['payload'] = json_decode($entry['payload'] ?? '', true) ?? [];
        }
        unset($entry);

        return view('admin/deposits/show', [
            'title'       => 'Depósito #' . $id,
            'active_menu' => 'deposits',
            'deposit'     => $deposit,
            'history'     => $history,
        ]);
    }

    public function accept($id)
    {
        if ($response = $this->checkPermission('deposits')) return $response;
        $depositModel = new DepositModel();
        $deposit = $depositModel->find($id);

        if (!$deposit) {
            return redirect()->back()->with('error', 'Depósito inválido ou já processado.');
        }

        if ($deposit['amount'] === null) {
            return redirect()->back()->with('error', 'Defina o valor do depósito antes de aceitar (o valor não pôde ser identificado automaticamente).');
        }

        $operatorId = session()->get('user_id');
        $now = date('Y-m-d H:i:s');

        $db = \Config\Database::connect();
        $db->table('deposits')
            ->where('id', $id)
            ->where('status', 'pending')
            ->update([
                'status'      => 'accepted',
                'accepted_by' => $operatorId,
                'accepted_at' => $now,
            ]);

        // Update condicional (WHERE status='pending') garante que, se dois operadores
        // clicarem ao mesmo tempo, só o primeiro afeta a linha — o segundo cai aqui.
        if ($db->affectedRows() === 0) {
            return redirect()->back()->with('error', 'Depósito inválido ou já processado.');
        }

        $description = 'Depósito confirmado #' . $id;
        if (!empty($deposit['amount_edited_reason'])) {
            $description .= ' (valor corrigido: ' . $deposit['amount_edited_reason'] . ')';
        }

        // Lança o crédito no extrato e distribui entre contratos em aberto (FIFO).
        $depositModel->applyAcceptedDeposit($deposit, $operatorId, $description);

        (new ActivityLogModel())->record('deposit.accepted', 'deposit', (int)$id, [
            'amount' => $deposit['amount'],
        ]);

        return redirect()->back()->with('success', 'Depósito aceito e pagamentos automáticos aplicados.');
    }

    public function reject($id)
    {
        if ($response = $this->checkPermission('deposits')) return $response;
        $depositModel = new DepositModel();
        $deposit = $depositModel->find($id);

        if (!$deposit) {
            return redirect()->back()->with('error', 'Apenas depósitos pendentes podem ser rejeitados.');
        }

        $reason = trim($this->request->getPost('rejection_reason') ?? '');
        if ($reason === '') {
            return redirect()->back()->with('error', 'O motivo da rejeição é obrigatório.');
        }

        $operatorId = session()->get('user_id');
        $now = date('Y-m-d H:i:s');

        $db = \Config\Database::connect();
        $db->table('deposits')
            ->where('id', $id)
            ->where('status', 'pending')
            ->update([
                'status'           => 'rejected',
                'rejected_by'      => $operatorId,
                'rejected_at'      => $now,
                'rejection_reason' => $reason,
            ]);

        if ($db->affectedRows() === 0) {
            return redirect()->back()->with('error', 'Apenas depósitos pendentes podem ser rejeitados.');
        }

        (new ActivityLogModel())->record('deposit.rejected', 'deposit', (int)$id, [
            'reason' => $reason,
        ]);

        return redirect()->back()->with('success', 'Depósito rejeitado.');
    }

    public function updateAmount($id)
    {
        if ($response = $this->checkPermission('deposits')) return $response;

        $role  = session()->get('user_role');
        $perms = session()->get('user_permissions') ?? [];
        if ($role !== 'admin' && !in_array('edit_deposit_amount', is_array($perms) ? $perms : [], true)) {
            return redirect()->back()->with('error', 'Você não tem permissão para corrigir o valor deste depósito.');
        }

        $depositModel = new DepositModel();
        $deposit = $depositModel->find($id);
        if (!$deposit) {
            return redirect()->back()->with('error', 'Depósito não encontrado.');
        }

        $newAmount = $this->request->getPost('amount');
        $reason    = trim($this->request->getPost('reason') ?? '');

        if ($newAmount === null || $newAmount === '' || (float) $newAmount <= 0) {
            return redirect()->back()->with('error', 'Informe um valor válido.');
        }
        if ($reason === '') {
            return redirect()->back()->with('error', 'O motivo da correção é obrigatório.');
        }

        $newAmount  = round((float) $newAmount, 2);
        $oldAmount  = $deposit['amount'];
        $operatorId = session()->get('user_id');
        $now        = date('Y-m-d H:i:s');

        $db = \Config\Database::connect();
        $db->table('deposits')
            ->where('id', $id)
            ->where('status', 'pending')
            ->update([
                'amount'               => $newAmount,
                'amount_edited_reason' => $reason,
                'amount_edited_by'     => $operatorId,
                'amount_edited_at'     => $now,
                'updated_at'           => $now,
            ]);

        if ($db->affectedRows() === 0) {
            return redirect()->back()->with('error', 'Só é possível corrigir o valor de depósitos ainda pendentes.');
        }

        (new ActivityLogModel())->record('deposit.amount_edited', 'deposit', (int) $id, [
            'old_amount' => $oldAmount,
            'new_amount' => $newAmount,
            'reason'     => $reason,
        ]);

        return redirect()->back()->with('success', 'Valor do depósito corrigido.');
    }

    public function reverse($id)
    {
        if ($response = $this->checkPermission('deposits')) return $response;
        if (session()->get('user_role') !== 'admin') {
            return redirect()->back()->with('error', 'Apenas administradores podem reverter depósitos.');
        }

        $depositModel = new DepositModel();
        $deposit = $depositModel->find($id);

        if (!$deposit) {
            return redirect()->back()->with('error', 'Apenas depósitos aceitos podem ser revertidos.');
        }

        $reason = $this->request->getPost('reversal_reason') ?? '';
        $adminId = session()->get('user_id');
        $now = date('Y-m-d H:i:s');

        $db = \Config\Database::connect();
        $db->table('deposits')
            ->where('id', $id)
            ->where('status', 'accepted')
            ->update([
                'status'          => 'reversed',
                'reversed_by'     => $adminId,
                'reversed_at'     => $now,
                'reversal_reason' => $reason,
            ]);

        if ($db->affectedRows() === 0) {
            return redirect()->back()->with('error', 'Apenas depósitos aceitos podem ser revertidos.');
        }

        $financialModel = new FinancialStatementModel();
        $financialModel->insert([
            'user_id'          => $deposit['user_id'],
            'admin_id'         => $adminId,
            'operation_type'   => 'adjustment_subtract',
            'nature'           => 'D',
            'amount'           => $deposit['amount'],
            'description'      => 'Estorno de depósito #' . $id . ($reason ? ' — ' . $reason : ''),
            'transaction_date' => $now,
        ]);

        (new ActivityLogModel())->record('deposit.reversed', 'deposit', (int)$id, [
            'amount' => $deposit['amount'],
            'reason' => $reason,
        ]);

        return redirect()->to(url_to('admin_deposits_show', $id))->with('success', 'Depósito revertido e estorno lançado no extrato do cliente.');
    }

    public function reverseRejection($id)
    {
        if ($response = $this->checkPermission('deposits')) return $response;
        if (session()->get('user_role') !== 'admin') {
            return redirect()->back()->with('error', 'Apenas administradores podem reverter rejeições.');
        }

        $depositModel = new DepositModel();
        $deposit = $depositModel->find($id);

        $db = \Config\Database::connect();
        $db->table('deposits')
            ->where('id', $id)
            ->where('status', 'rejected')
            ->update([
                'status'           => 'pending',
                'rejected_by'      => null,
                'rejected_at'      => null,
                'rejection_reason' => null,
            ]);

        if ($db->affectedRows() === 0) {
            return redirect()->back()->with('error', 'Apenas depósitos rejeitados podem ter a rejeição revertida.');
        }

        (new ActivityLogModel())->record('deposit.rejection_reverted', 'deposit', (int)$id, [
            'previous_reason' => $deposit['rejection_reason'] ?? null,
        ]);

        return redirect()->to(url_to('admin_deposits_show', $id))->with('success', 'Rejeição revertida. O depósito voltou para pendente e pode ser reavaliado.');
    }
}
