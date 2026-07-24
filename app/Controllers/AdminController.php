<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Controllers\BaseController;

class AdminController extends BaseController
{
    public function index()
    {
        if ($response = $this->checkPermission('usuarios')) return $response;
        $db = \Config\Database::connect();
        try { $db->query("CREATE TABLE IF NOT EXISTS `suppliers` (`id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(150) NOT NULL, `enabled` TINYINT(1) NOT NULL DEFAULT 1, `created_at` DATETIME NULL, `updated_at` DATETIME NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (\Throwable $e) {}
        try { $db->query("CREATE TABLE IF NOT EXISTS `usdt_lots` (`id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `supplier` VARCHAR(150) NOT NULL, `purchase_hash` VARCHAR(255) NULL, `delivery_type` VARCHAR(10) NULL, `usdt_amount` DECIMAL(18,4) NOT NULL, `conversion_rate` DECIMAL(18,6) NOT NULL, `total_brl` DECIMAL(18,2) NOT NULL, `total_brl_overridden` TINYINT(1) NOT NULL DEFAULT 0, `usdt_reserved` DECIMAL(18,4) NOT NULL DEFAULT 0, `usdt_delivered` DECIMAL(18,4) NOT NULL DEFAULT 0, `profit_brl` DECIMAL(18,2) NOT NULL DEFAULT 0, `status` ENUM('active','depleted','cancelled') NOT NULL DEFAULT 'active', `created_by` INT UNSIGNED NOT NULL, `created_at` DATETIME NULL, `updated_at` DATETIME NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (\Throwable $e) {}
        try { $db->query("CREATE TABLE IF NOT EXISTS `lot_allocations` (`id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `lot_id` INT UNSIGNED NOT NULL, `contract_id` INT UNSIGNED NULL, `transaction_id` INT UNSIGNED NULL, `usdt_amount` DECIMAL(18,4) NOT NULL, `status` ENUM('reserved','delivered','cancelled') NOT NULL DEFAULT 'reserved', `profit_brl` DECIMAL(18,2) NULL, `allocated_by` INT UNSIGNED NOT NULL, `delivered_by` INT UNSIGNED NULL, `created_at` DATETIME NULL, `updated_at` DATETIME NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (\Throwable $e) {}
        try { $db->query("CREATE TABLE IF NOT EXISTS `activity_logs` (`id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `user_id` INT UNSIGNED NULL, `action` VARCHAR(100) NOT NULL, `entity_type` VARCHAR(50) NOT NULL, `entity_id` INT UNSIGNED NOT NULL, `payload` TEXT NULL, `ip_address` VARCHAR(45) NULL, `created_at` DATETIME NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (\Throwable $e) {}
        try { if ($db->fieldExists('credit_limit', 'users') && !$db->fieldExists('score', 'users')) { $db->query("ALTER TABLE `users` CHANGE `credit_limit` `score` DECIMAL(15,2) NOT NULL DEFAULT 0.00"); } } catch (\Throwable $e) {}
        try {
            if (!$db->fieldExists('purchase_model', 'users')) { $db->query("ALTER TABLE `users` ADD COLUMN `purchase_model` ENUM('usdt','brl','both') NOT NULL DEFAULT 'usdt' AFTER `allowed_delivery_types`"); }
            if (!$db->fieldExists('last_purchase_mode', 'users')) { $db->query("ALTER TABLE `users` ADD COLUMN `last_purchase_mode` ENUM('usdt','brl') NULL AFTER `purchase_model`"); }
            if (!$db->fieldExists('lock_only_with_balance', 'users')) { $db->query("ALTER TABLE `users` ADD COLUMN `lock_only_with_balance` TINYINT(1) NOT NULL DEFAULT 0 AFTER `purchase_model`"); }
        } catch (\Throwable $e) {}

        $search = $this->request->getGet('search') ?? '';
        $role   = $this->request->getGet('role') ?? '';

        if (session()->get('user_role') === 'operator') {
            $role = 'user';
        }

        $userModel = new UserModel();
        $builder = $userModel->orderBy('role', 'ASC')->orderBy('login', 'ASC');

        if ($search !== '') {
            $builder->like('login', $search);
        }
        if ($role !== '') {
            $builder->where('role', $role);
        }

        $users = $builder->findAll();
        
        $financialModel = new \App\Models\FinancialStatementModel();
        foreach ($users as &$user) {
            $user['balance'] = $financialModel->getBalance((int)$user['id']);
        }
        $data['users'] = $users;
        $data['filters'] = compact('search', 'role');
        
        $db = \Config\Database::connect();
        $latestRecord = $db->table('dollar_history')
                           ->orderBy('created_at', 'DESC')
                           ->limit(1)
                           ->get()
                           ->getRow();
        $data['latest_rate'] = $latestRecord ? (float) $latestRecord->base_rate : 5.0000;
        
        return view('admin/users/index', $data);
    }

    public function create()
    {
        if ($response = $this->checkPermission('usuarios')) return $response;
        return view('admin/users/create');
    }

    public function store()
    {
        if ($response = $this->checkPermission('usuarios')) return $response;
        $userModel = new UserModel();
        
        $role = $this->request->getPost('role') ?: 'user';
        if (session()->get('user_role') === 'operator') {
            $role = 'user';
        }
        $permissions = $this->request->getPost('permissions');
        $canSetPurchaseModel = session()->get('user_role') === 'admin' || in_array('purchase_model', session()->get('user_permissions') ?? []);

        $data = [
            'login'                  => $this->request->getPost('login'),
            'password'               => $this->request->getPost('password'),
            'fee_percent'            => $role === 'user' ? ($this->request->getPost('fee_percent') ?: 10.00) : 0.00,
            'usdt_wallet'            => $role === 'user' ? $this->request->getPost('usdt_wallet') : null,
            'score'                  => 0.00,
            'default_contract_type'  => $role === 'user' ? ($this->request->getPost('default_contract_type') ?: 'd+1') : 'd+1',
            'daily_interest_rate'    => $role === 'user' ? ($this->request->getPost('daily_interest_rate') ?: 0.00) : 0.00,
            'allowed_delivery_types' => $role === 'user' ? ($this->request->getPost('allowed_delivery_types') ?: 'all') : 'all',
            'purchase_model'         => ($role === 'user' && $canSetPurchaseModel) ? ($this->request->getPost('purchase_model') ?: 'usdt') : 'usdt',

            'lock_only_with_balance' => $role === 'user' ? (int)$this->request->getPost('lock_only_with_balance') : 0,
            'role'                   => $role,
            'permissions'            => ($role !== 'user' && !empty($permissions)) ? json_encode($permissions) : null,
        ];

        if (!$userModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }

        $userId = $userModel->getInsertID();
        if ($userId && !empty($data['usdt_wallet'])) {
            $walletModel = new \App\Models\UserWalletModel();
            $walletModel->insert([
                'user_id' => $userId,
                'address' => $data['usdt_wallet'],
                'is_default' => 1
            ]);
        }

        return redirect()->to('/admin/users')->with('success', 'Usuário criado com sucesso!');
    }

    public function edit(int $id)
    {
        if ($response = $this->checkPermission('usuarios')) return $response;
        $userModel = new UserModel();
        $data['user'] = $userModel->find($id);
        
        if (!$data['user']) {
            return redirect()->to('/admin/users')->with('error', 'Usuário não encontrado.');
        }

        if (session()->get('user_role') === 'operator' && $data['user']['role'] !== 'user') {
            return redirect()->to('/admin/users')->with('error', 'Acesso negado: operadores só podem gerenciar clientes.');
        }

        $walletModel = new \App\Models\UserWalletModel();
        $wallets = $walletModel->where('user_id', $id)->findAll();
        // Fallback migration on the fly
        if (empty($wallets) && !empty($data['user']['usdt_wallet'])) {
            $walletModel->insert([
                'user_id'    => $id,
                'address'    => $data['user']['usdt_wallet'],
                'is_default' => 1
            ]);
            $wallets = $walletModel->where('user_id', $id)->findAll();
        }
        $data['wallets'] = $wallets;

        return view('admin/users/edit', $data);
    }

    public function view(int $id)
    {
        if ($response = $this->checkPermission('usuarios')) return $response;

        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'Usuário não encontrado.');
        }

        if (session()->get('user_role') === 'operator' && $user['role'] !== 'user') {
            return redirect()->to('/admin/users')->with('error', 'Acesso negado: operadores só podem gerenciar clientes.');
        }

        $financialModel = new \App\Models\FinancialStatementModel();
        $user['balance'] = $financialModel->getBalance($id);

        $walletModel = new \App\Models\UserWalletModel();
        $wallets = $walletModel->where('user_id', $id)->findAll();

        $db = \Config\Database::connect();
        $latestRecord = $db->table('dollar_history')
                           ->orderBy('created_at', 'DESC')
                           ->limit(1)
                           ->get()
                           ->getRow();
        $latestRate = $latestRecord ? (float) $latestRecord->base_rate : 5.0000;

        $data = [
            'user'        => $user,
            'wallets'     => $wallets,
            'latest_rate' => $latestRate
        ];

        return view('admin/users/view', $data);
    }

    public function update(int $id)
    {
        if ($response = $this->checkPermission('usuarios')) return $response;
        $userModel = new UserModel();
        $existingUser = $userModel->find($id);

        if (!$existingUser) {
            return redirect()->to('/admin/users')->with('error', 'Usuário não encontrado.');
        }

        if (session()->get('user_role') === 'operator' && $existingUser['role'] !== 'user') {
            return redirect()->to('/admin/users')->with('error', 'Acesso negado: operadores só podem gerenciar clientes.');
        }

        $role = $this->request->getPost('role') ?: 'user';
        if (session()->get('user_role') === 'operator') {
            $role = 'user';
        }
        $permissions = $this->request->getPost('permissions');
        $canSetPurchaseModel = session()->get('user_role') === 'admin' || in_array('purchase_model', session()->get('user_permissions') ?? []);

        // Processamento das carteiras
        $walletsPost = (array)$this->request->getPost('wallets');
        $walletStatusesPost = (array)$this->request->getPost('wallet_statuses');
        $defaultWalletPost = $this->request->getPost('default_wallet');
        
        $cleanedWallets = [];
        $cleanedStatuses = [];
        foreach ($walletsPost as $idx => $addr) {
            $trimmed = trim($addr);
            if ($trimmed !== '') {
                $cleanedWallets[] = $trimmed;
                $cleanedStatuses[] = isset($walletStatusesPost[$idx]) ? $walletStatusesPost[$idx] : 'active';
            }
        }

        $walletModel = new \App\Models\UserWalletModel();
        $existingWallets = $walletModel->where('user_id', $id)->findAll();

        foreach ($existingWallets as $ew) {
            if (!in_array($ew['address'], $cleanedWallets)) {
                $walletModel->delete($ew['id']);
            }
        }

        $defaultAddress = null;
        foreach ($cleanedWallets as $index => $addr) {
            $isDefault = ($addr === $defaultWalletPost) ? 1 : 0;
            $status = $cleanedStatuses[$index] === 'inactive' ? 'inactive' : 'active';
            if ($isDefault) {
                $defaultAddress = $addr;
            }

            $existing = $walletModel->where('user_id', $id)->where('address', $addr)->first();
            if ($existing) {
                $walletModel->update($existing['id'], [
                    'is_default' => $isDefault,
                    'status'     => $status
                ]);
            } else {
                $walletModel->insert([
                    'user_id'    => $id,
                    'address'    => $addr,
                    'is_default' => $isDefault,
                    'status'     => $status
                ]);
            }
        }

        if (!$defaultAddress && !empty($cleanedWallets)) {
            $firstAddr = reset($cleanedWallets);
            $existing = $walletModel->where('user_id', $id)->where('address', $firstAddr)->first();
            if ($existing) {
                $walletModel->update($existing['id'], ['is_default' => 1]);
                $defaultAddress = $firstAddr;
            }
        }

        $data = [
            'login'                  => $this->request->getPost('login'),
            'fee_percent'            => session()->get('user_role') === 'admin'
                ? ($role === 'user' ? ($this->request->getPost('fee_percent') ?: 10.00) : 0.00)
                : ($existingUser['fee_percent'] ?? 10.00),
            'usdt_wallet'            => $role === 'user' ? $defaultAddress : null,
            'default_contract_type'  => $role === 'user' ? ($this->request->getPost('default_contract_type') ?: 'd+1') : 'd+1',
            'daily_interest_rate'    => $role === 'user' ? ($this->request->getPost('daily_interest_rate') ?: 0.00) : 0.00,
            'allowed_delivery_types' => $role === 'user' ? ($this->request->getPost('allowed_delivery_types') ?: 'all') : 'all',
            'purchase_model'         => $role === 'user'
                ? ($canSetPurchaseModel ? ($this->request->getPost('purchase_model') ?: 'usdt') : ($existingUser['purchase_model'] ?? 'usdt'))
                : 'usdt',
            'lock_only_with_balance' => $role === 'user' ? (int)$this->request->getPost('lock_only_with_balance') : 0,
            'role'                   => $role,
            'permissions'            => ($role !== 'user' && !empty($permissions)) ? json_encode($permissions) : null,
        ];

        $rules = [
            'login' => "required|min_length[3]|max_length[150]|is_unique[users.login,id,$id]",
        ];

        $password = $this->request->getPost('password');
        if (!empty($password) && session()->get('user_role') === 'admin') {
            $data['password'] = $password;
            $rules['password'] = 'required|min_length[6]';
        }

        $userModel->setValidationRules($rules);

        if (!$userModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }

        return redirect()->to(url_to('admin_users_view', $id))->with('success', 'Usuário atualizado com sucesso!');
    }

    public function transactions()
    {
        if ($response = $this->checkPermission('transacoes')) return $response;
        $financialModel = new \App\Models\FinancialStatementModel();
        
        $transactions = $financialModel->select('financial_statements.*, users.login as user_name, contracts.id as contract_id, contracts.type as contract_type')
                                       ->join('users', 'users.id = financial_statements.user_id')
                                       ->join('contracts', 'contracts.id = financial_statements.contract_id', 'left')
                                       ->orderBy('financial_statements.transaction_date', 'DESC')
                                       ->findAll();

        return view('admin/transactions/index', ['transactions' => $transactions]);
    }

    public function updateTransactionStatus(int $id)
    {
        if ($response = $this->checkPermission('transacoes')) return $response;
        $transactionModel = new \App\Models\TransactionModel();
        $status = $this->request->getPost('status');
        $amountBrlFulfilled = $this->request->getPost('amount_brl_fulfilled'); 
        
        $transaction = $transactionModel->find($id);
        if (!$transaction) return redirect()->back()->with('error', 'Transação não encontrada.');

        // Verifica trava
        if ($transaction['locked_by'] && $transaction['locked_by'] != session()->get('user_id')) {
            $lockTime = strtotime($transaction['locked_at']);
            if (time() - $lockTime < 300) { 
                return redirect()->to('/admin/transactions')->with('error', 'Esta transação está sendo operada por outro administrador.');
            }
        }

        if ($status === 'completed') {
            $contractModel = new \App\Models\ContractModel();
            $financialModel = new \App\Models\FinancialStatementModel();
            
            $contract = $contractModel->where('transaction_id', $id)->first();
            $amountBrlFulfilledRaw = $this->request->getPost('amount_brl_fulfilled');

            if ($contract) {
                // Tem contrato (Crédito)
                // A transação original é confirmada integralmente (entregamos todo o USDT)
                $data = [
                    'updated_at' => date('Y-m-d H:i:s'),
                    'locked_by' => null,
                    'locked_at' => null
                ];
                $transactionModel->update($id, $data);

                // Determina quanto BRL foi pago
                if ($amountBrlFulfilledRaw !== '') {
                    $brlPaid = (float)$amountBrlFulfilledRaw;
                } else {
                    if ($transaction['amount_brl'] > 0) {
                        $brlPaid = (float)$transaction['amount_brl']; // D+0: Assume total
                    } else {
                        $brlPaid = 0; // D+1/D+2: Assume 0
                    }
                }

                // Registra o pagamento no contrato
                $rateToUse = (float)$transaction['rate'];
                if ($rateToUse <= 0) {
                    $settingsModel = new \App\Models\SettingsModel();
                    $dollarRate = (float)($settingsModel->where('key', 'dollar_rate')->first()['value'] ?? 5.00);
                    $fee = (float)($settingsModel->where('key', 'transaction_fee')->first()['value'] ?? 0);
                    $rateToUse = $dollarRate * (1 + ($fee / 100));
                }

                $totalBrlFinal = round((float)$transaction['amount_usdt'] * $rateToUse, 2);

                if ($brlPaid > 0) {
                    $contractModel->registerPayment($contract['id'], $brlPaid);
                    
                    $updatedContract = $contractModel->find($contract['id']);
                    $opType = ($updatedContract['remaining_balance'] <= 0) ? 'full_settlement' : 'partial_amortization';
                    
                    $res = $financialModel->insert([
                        'user_id' => $transaction['user_id'],
                        'admin_id' => session()->get('user_id'),
                        'contract_id' => $contract['id'],
                        'operation_type' => $opType,
                        'nature' => 'C',
                        'amount' => $brlPaid,
                        'description' => 'Pagamento R$ ' . number_format($brlPaid, 2, ',', '.'),
                        'transaction_date' => date('Y-m-d H:i:s')
                    ]);
                    if (!$res) log_message('error', 'FINANCIAL INSERT ERROR 1: ' . json_encode($financialModel->errors()));
                }

                // Registra o valor pago pelo cliente em BRL no contrato e atualiza o total do contrato para o valor final calculado
                $contractModel->update($contract['id'], [
                    'paid_client' => $brlPaid,
                    'total_brl'   => $totalBrlFinal
                ]);
                
                $res = $financialModel->insert([
                    'user_id' => $transaction['user_id'],
                    'admin_id' => session()->get('user_id'),
                    'contract_id' => $contract['id'],
                    'operation_type' => 'deposit',
                    'nature' => 'C',
                    'amount' => $transaction['amount_usdt'],
                    'description' => 'Depósito de USDT - Transação #' . $id,
                    'transaction_date' => date('Y-m-d H:i:s')
                ]);
                if (!$res) log_message('error', 'FINANCIAL INSERT ERROR 2: ' . json_encode($financialModel->errors()));

                // Verifica se o saldo zerou para mudar o status da transação para completed
                $updatedContract = $contractModel->find($contract['id']);
                if ($updatedContract['remaining_balance'] <= 0) {
                    $transactionModel->update($id, ['status' => 'completed']);
                } else {
                    $transactionModel->update($id, ['status' => 'pending']); // Mantém pending se houver saldo
                }

            } else {
                // NÃO tem contrato (Compra à vista ou Venda sem crédito)
                $requestedBrl = (float)$transaction['amount_brl'];
                $fulfilledBrl = ($amountBrlFulfilledRaw !== '') ? (float)$amountBrlFulfilledRaw : $requestedBrl;

                if ($fulfilledBrl < $requestedBrl && $fulfilledBrl > 0 && $transaction['type'] == 'buy') {
                    // Split da transação original
                    $remainder = $requestedBrl - $fulfilledBrl;
                    $transactionModel->insert([
                        'user_id'        => $transaction['user_id'],
                        'type'           => $transaction['type'],
                        'amount_brl'     => $remainder,
                        'amount_usdt'    => 0,
                        'rate'           => $transaction['rate'],
                        'base_rate'      => isset($transaction['base_rate']) ? $transaction['base_rate'] : null,
                        'fee_percent'    => isset($transaction['fee_percent']) ? $transaction['fee_percent'] : null,
                        'comercial_brl'  => isset($transaction['comercial_brl']) ? $transaction['comercial_brl'] : null,
                        'fee_brl'        => isset($transaction['fee_brl']) ? $transaction['fee_brl'] : null,
                        'status'         => 'pending',
                        'delivery_type'  => $transaction['delivery_type'],
                        'wallet_address' => $transaction['wallet_address'],
                        'created_at'     => date('Y-m-d H:i:s')
                    ]);
                    
                    $data = [
                        'status'     => 'completed',
                        'amount_brl' => $fulfilledBrl,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'locked_by'  => null,
                        'locked_at'  => null
                    ];
                    
                    if ($transaction['amount_usdt'] > 0) {
                        $data['amount_usdt'] = ($transaction['amount_usdt'] / $requestedBrl) * $fulfilledBrl;
                    }
                } else {
                    $data = ['status' => 'completed', 'updated_at' => date('Y-m-d H:i:s'), 'locked_by' => null, 'locked_at' => null];
                }
                $transactionModel->update($id, $data);

                $finalUsdt = $data['amount_usdt'] ?? $transaction['amount_usdt'];

                // Grava o pagamento em BRL (mesmo sem contrato)
                if ($fulfilledBrl > 0 && $transaction['rate'] > 0) {
                    $usdtPaid = $fulfilledBrl / $transaction['rate'];
                    $financialModel->insert([
                        'user_id' => $transaction['user_id'],
                        'contract_id' => null, // Sem contrato
                        'operation_type' => 'full_settlement', // Dinheiro vivo = liquidação à vista
                        'nature' => 'C',
                        'amount' => $usdtPaid,
                        'description' => 'Pagamento R$ ' . number_format($fulfilledBrl, 2, ',', '.'),
                        'transaction_date' => date('Y-m-d H:i:s')
                    ]);
                }
                
                if ($transaction['type'] == 'buy') {
                    $financialModel->insert([
                        'user_id' => $transaction['user_id'],
                        'contract_id' => null,
                        'operation_type' => 'deposit',
                        'nature' => 'C',
                        'amount' => $finalUsdt,
                        'description' => 'Depósito de USDT - Transação #' . $id,
                        'transaction_date' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    $financialModel->insert([
                        'user_id' => $transaction['user_id'],
                        'contract_id' => null,
                        'operation_type' => 'withdrawal',
                        'nature' => 'D',
                        'amount' => $finalUsdt,
                        'description' => 'Saque de USDT (Venda) - Transação #' . $id,
                        'transaction_date' => date('Y-m-d H:i:s')
                    ]);
                }

                // Se for compra à vista promocional, dá baixa na alocação de lote
                if ($transaction['type'] == 'buy') {
                    $allocationModel = new \App\Models\LotAllocationModel();
                    $lotModel = new \App\Models\UsdtLotModel();
                    $allocations = $allocationModel->where('transaction_id', $id)->where('status', 'reserved')->findAll();
                    foreach ($allocations as $alloc) {
                        $lot = $lotModel->find($alloc['lot_id']);
                        if ($lot) {
                            $costPerUsdt = (float)$lot['conversion_rate'];
                            $revenuePerUsdt = $allocationModel->getRevenuePerUsdt('transaction', $id);
                            $profitBrl = round(($revenuePerUsdt - $costPerUsdt) * (float)$alloc['usdt_amount'], 2);

                            $allocationModel->update($alloc['id'], [
                                'status' => 'delivered',
                                'profit_brl' => $profitBrl,
                                'delivered_by' => session()->get('user_id') ?? $alloc['allocated_by'],
                            ]);
                            $lotModel->recalculateTotals((int)$alloc['lot_id']);
                        }
                    }
                }
            }

            (new \App\Models\ActivityLogModel())->record('transaction.approved', 'transaction', (int)$id, [
                'amount_usdt' => $transaction['amount_usdt'] ?? null,
                'amount_brl'  => $transaction['amount_brl']  ?? null,
            ]);

            return redirect()->to('/admin/transactions')->with('success', 'Transação confirmada!');
        }
        
        if ($status === 'cancelled') {
            $transactionModel->update($id, ['status' => 'cancelled', 'updated_at' => date('Y-m-d H:i:s'), 'locked_by' => null, 'locked_at' => null]);
            
            // Cancela alocações de lote vinculadas à transação
            $allocationModel = new \App\Models\LotAllocationModel();
            $lotModel = new \App\Models\UsdtLotModel();
            $allocations = $allocationModel->where('transaction_id', $id)->findAll();
            foreach ($allocations as $alloc) {
                $allocationModel->update($alloc['id'], ['status' => 'cancelled']);
                $lotModel->recalculateTotals((int)$alloc['lot_id']);
            }
            
            // Se tiver contrato pendente, cancela e libera o limite
            $contractModel = new \App\Models\ContractModel();
            $contract = $contractModel->where('transaction_id', $id)->first();
            if ($contract && in_array($contract['status'], ['pending', 'partially_paid', 'overdue'])) {
                $contractModel->update($contract['id'], ['status' => 'paid']); // Ou 'cancelled', assumindo 'paid' por não ter enum 'cancelled'
                
                $financialModel = new \App\Models\FinancialStatementModel();
                $financialModel->insert([
                    'user_id' => $transaction['user_id'],
                    'contract_id' => $contract['id'],
                    'operation_type' => 'limit_release',
                    'nature' => 'C', // Crédito (Devolve o limite)
                    'amount' => $contract['remaining_balance'],
                    'description' => 'Liberação de Limite (Cancelamento) - Contrato #' . $contract['id'],
                    'transaction_date' => date('Y-m-d H:i:s')
                ]);
            }

            (new \App\Models\ActivityLogModel())->record('transaction.cancelled', 'transaction', (int)$id);

            return redirect()->to('/admin/transactions')->with('success', 'Transação cancelada.');
        }
        
        return redirect()->to('/admin/transactions')->with('error', 'Status inválido.');
    }

    public function transactionDetails(int $id)
    {
        $transactionModel = new \App\Models\TransactionModel();
        $contractModel = new \App\Models\ContractModel();

        $transaction = $transactionModel->select('transactions.*, users.login as user_name, users.score, users.fee_percent, users.usdt_wallet')
                                         ->join('users', 'users.id = transactions.user_id')
                                         ->where('transactions.id', $id)
                                         ->first();

        if (!$transaction) return redirect()->back()->with('error', 'Transação não encontrada.');

        // Tenta travar
        $userId = session()->get('user_id');
        if ($transaction['locked_by'] && $transaction['locked_by'] != $userId) {
            $lockTime = strtotime($transaction['locked_at']);
            if (time() - $lockTime < 300) { 
                return redirect()->back()->with('error', 'Esta transação está sendo operada por outro administrador.');
            }
        }

        $transactionModel->update($id, ['locked_by' => $userId, 'locked_at' => date('Y-m-d H:i:s')]);

        $contracts = $contractModel->where('transaction_id', $id)
                                   ->orGroupStart()
                                       ->where('user_id', $transaction['user_id'])
                                       ->whereIn('status', ['pending', 'partially_paid', 'overdue'])
                                   ->groupEnd()
                                   ->findAll();

        return view('admin/transactions/show', [
            't' => $transaction,
            'contracts' => $contracts
        ]);
    }

    public function unlockTransaction(int $id)
    {
        $transactionModel = new \App\Models\TransactionModel();
        $transactionModel->update($id, ['locked_by' => null, 'locked_at' => null]);
        return redirect()->to('/admin/transactions');
    }

    public function contractDetails(int $id)
    {
        $db = \Config\Database::connect();
        $currentUserId = session()->get('user_id');
        
        // Remove expired locks (older than 30 seconds)
        $db->table('contract_locks')
           ->where('updated_at <', date('Y-m-d H:i:s', strtotime('-30 seconds')))
           ->delete();
           
        // Check if there is an active lock
        $lock = $db->table('contract_locks')
                   ->where('contract_id', $id)
                   ->get()
                   ->getRow();
                   
        if ($lock) {
            if ((int)$lock->user_id !== (int)$currentUserId) {
                $lockingUser = $db->table('users')->where('id', $lock->user_id)->get()->getRow();
                $lockingUserName = $lockingUser ? $lockingUser->login : 'Outro operador';
                return redirect()->to('/admin/contracts')->with('error', "Este contrato #{$id} está sendo visualizado/operado por {$lockingUserName} no momento.");
            } else {
                // Refresh lock
                $db->table('contract_locks')
                   ->where('contract_id', $id)
                   ->update(['updated_at' => date('Y-m-d H:i:s')]);
            }
        } else {
            // Create lock
            $db->table('contract_locks')->insert([
                'contract_id' => $id,
                'user_id' => $currentUserId,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        $contractModel = new \App\Models\ContractModel();
        
        $contract = $contractModel->select('contracts.*, users.login as user_name, users.usdt_wallet, transactions.wallet_address as requested_wallet, transactions.rate as transaction_rate, users.score, users.fee_percent')
                                   ->join('users', 'users.id = contracts.user_id')
                                   ->join('transactions', 'transactions.id = contracts.transaction_id', 'left')
                                   ->where('contracts.id', $id)
                                   ->first();

        if (!$contract) return redirect()->back()->with('error', 'Contrato não encontrado.');

        $financialModel = new \App\Models\FinancialStatementModel();
        $history = $financialModel->select('financial_statements.*, admins.login as admin_name')
                                  ->join('users as admins', 'admins.id = financial_statements.admin_id', 'left')
                                  ->where('contract_id', $id)
                                  ->orderBy('transaction_date', 'DESC')
                                  ->findAll();

        $transactionModel = new \App\Models\TransactionModel();
        $clientProof = null;

        if (!empty($contract['transaction_id'])) {
            $clientProof = $transactionModel->find($contract['transaction_id']);
        } else {
            // Fallback: Busca transação recente do mesmo usuário com o mesmo valor BRL que possua comprovante
            $clientProof = $transactionModel->where('user_id', $contract['user_id'])
                                            ->where('amount_brl', $contract['total_brl'])
                                            ->where('proof_path IS NOT NULL')
                                            ->orderBy('created_at', 'DESC')
                                            ->first();
        }

        $db = \Config\Database::connect();

        $totalReservedUsdt = (float)$db->query(
            "SELECT COALESCE(SUM(usdt_amount), 0) AS total FROM lot_allocations WHERE contract_id = ? AND status = 'reserved'",
            [$id]
        )->getRow()->total;

        $paidRatio   = $contract['total_brl'] > 0 ? min(1.0, (float)$contract['paid_amount'] / (float)$contract['total_brl']) : 0;
        $usdtPending = max(0, round($paidRatio * (float)$contract['total_amount'], 4) - (float)$contract['delivered_usdt']);

        $unlinkedDelivered = (new \App\Models\LotAllocationModel())->getUnlinkedDelivered('contract', $id, (float)$contract['delivered_usdt']);

        $lotModel = new \App\Models\UsdtLotModel();
        $rawLots  = $lotModel->select('id, supplier, usdt_amount, usdt_reserved, usdt_delivered, conversion_rate, total_brl, status')
            ->where('status', 'active')
            ->where('is_promotional', 0)
            ->orderBy('created_at', 'DESC')
            ->findAll();
        $availableLots = array_map(function ($lot) use ($lotModel) {
            $lot['usdt_available'] = $lotModel->getAvailable((int)$lot['id']);
            return $lot;
        }, $rawLots);

        $currentBaseRate = (float)$db->table('dollar_history')->orderBy('created_at', 'DESC')->limit(1)->get()->getRow()?->base_rate ?? 0;

        $supplierModel = new \App\Models\SupplierModel();
        $suppliers     = $supplierModel->getEnabled();

        $contractAllocations = $db->query("
            SELECT la.*, ul.supplier, ul.conversion_rate, ul.delivery_type
            FROM lot_allocations la
            JOIN usdt_lots ul ON ul.id = la.lot_id
            WHERE la.contract_id = ?
        ", [$id])->getResultArray();

        $firstReservedLotId = null;
        foreach ($contractAllocations as $alloc) {
            if ($alloc['status'] === 'reserved') {
                $firstReservedLotId = (int)$alloc['lot_id'];
                break;
            }
        }

        return view('admin/contracts/show', [
            'c'                   => $contract,
            'history'             => $history,
            'clientProof'         => $clientProof,
            'totalReservedUsdt'   => $totalReservedUsdt,
            'usdtPending'         => $usdtPending,
            'unlinkedDelivered'   => $unlinkedDelivered,
            'availableLots'       => $availableLots,
            'currentBaseRate'     => $currentBaseRate,
            'suppliers'           => $suppliers,
            'contractAllocations' => $contractAllocations,
            'firstReservedLotId'  => $firstReservedLotId,
        ]);
    }

    public function contracts()
    {
        return $this->renderContractsList(false);
    }

    public function contractsCompleted()
    {
        return $this->renderContractsList(true);
    }

    private function renderContractsList(bool $completedOnly)
    {
        if ($response = $this->checkPermission('enviar_usdt')) return $response;
        $contractModel = new \App\Models\ContractModel();
        $settingsModel = new \App\Models\SettingsModel();

        $dollarRate = (float)($settingsModel->where('key', 'dollar_rate')->first()['value'] ?? 5.00);
        $fee = (float)($settingsModel->where('key', 'transaction_fee')->first()['value'] ?? 0);
        $fallbackRate = $dollarRate * (1 + ($fee / 100));

        $filters = [
            'id'             => $this->request->getGet('id'),
            'user'           => $this->request->getGet('user'),
            'start_date'     => $this->request->getGet('start_date'),
            'end_date'       => $this->request->getGet('end_date'),
            'status'         => $this->request->getGet('status'),
            'delivery_status'=> $this->request->getGet('delivery_status'),
        ];
        $perPage = (int)($this->request->getGet('per_page') ?? 15);
        if (!in_array($perPage, [15, 25, 50, 100])) $perPage = 15;

        $builder = $contractModel->select("contracts.*, users.login as user_name, users.usdt_wallet, transactions.wallet_address as requested_wallet, transactions.rate as tx_rate, transactions.base_rate as spot_rate, COALESCE(la_totals.total_lot_allocated, 0) AS total_lot_allocated")
                                 ->join('users', 'users.id = contracts.user_id')
                                 ->join('transactions', 'transactions.id = contracts.transaction_id', 'left')
                                 ->join("(SELECT contract_id, SUM(usdt_amount) AS total_lot_allocated FROM lot_allocations WHERE status IN ('reserved', 'delivered') GROUP BY contract_id) la_totals", 'la_totals.contract_id = contracts.id', 'left');

        // Filtrar concluídos ou não concluídos
        if ($completedOnly) {
            $builder->where('contracts.status', 'paid')
                    ->where('contracts.delivered_usdt >= contracts.total_amount', null, false)
                    ->where('COALESCE(la_totals.total_lot_allocated, 0) >= contracts.total_amount', null, false);
        } else {
            // Excluir concluídos: (status != 'paid' OR enviado < total OR lote < total)
            $builder->groupStart()
                        ->where('contracts.status !=', 'paid')
                        ->orWhere('contracts.delivered_usdt < contracts.total_amount', null, false)
                        ->orWhere('COALESCE(la_totals.total_lot_allocated, 0) < contracts.total_amount', null, false)
                    ->groupEnd();
        }

        if (!empty($filters['id'])) {
            $builder->where('contracts.id', (int)$filters['id']);
        }
        if (!empty($filters['user'])) {
            $builder->like('users.login', $filters['user']);
        }
        if (!empty($filters['start_date'])) {
            $builder->where('contracts.due_date >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $builder->where('contracts.due_date <=', $filters['end_date']);
        }
        if ($filters['status'] === 'sent') {
            $builder->where('contracts.total_amount > 0', null, false)
                     ->where('contracts.delivered_usdt >= contracts.total_amount', null, false);
        } elseif (!empty($filters['status'])) {
            $builder->where('contracts.status', $filters['status']);
        }
        if ($filters['delivery_status'] === 'em_aberto') {
            $builder->where('COALESCE(la_totals.total_lot_allocated, 0) < contracts.total_amount', null, false);
        } elseif ($filters['delivery_status'] === 'concluido') {
            $builder->where('COALESCE(la_totals.total_lot_allocated, 0) >= contracts.total_amount', null, false);
        }

        $contracts = $builder->orderBy("CASE
                                  WHEN contracts.status = 'overdue' THEN 1
                                  WHEN contracts.status = 'pending' THEN 2
                                  WHEN contracts.status = 'partially_paid' THEN 3
                                  WHEN contracts.status = 'paid' THEN 4
                                  ELSE 5
                                END", "ASC")
                             ->orderBy('contracts.created_at', 'DESC')
                             ->paginate($perPage);

        foreach ($contracts as &$c) {
            $rate = (float)($c['tx_rate'] ?? 0);
            if ($rate <= 0) $rate = $fallbackRate;
            $c['brl_owed'] = $c['remaining_balance'] * $rate;
        }

        $data['contracts']        = $contracts;
        $data['pager']            = $contractModel->pager;
        $data['filters']          = $filters;
        $data['per_page']         = $perPage;
        $data['active_menu']      = 'contracts';
        $data['is_completed']     = $completedOnly;

        return view('admin/contracts/index', $data);
    }

    public function contractsUpdates()
    {
        $since = $this->request->getGet('since') ?: date('Y-m-d H:i:s', strtotime('-5 seconds'));

        $db   = \Config\Database::connect();
        $rows = $db->query(
            "SELECT c.id, c.status, c.updated_at, u.login as user_name
             FROM contracts c
             JOIN users u ON u.id = c.user_id
             WHERE c.updated_at > ?
             ORDER BY c.updated_at ASC
             LIMIT 20",
            [$since]
        )->getResultArray();

        return $this->response->setJSON($rows);
    }

    public function contractRow(int $id)
    {
        $contractModel = new \App\Models\ContractModel();
        $contract = $contractModel
            ->select("contracts.*, users.login as user_name, users.usdt_wallet, transactions.wallet_address as requested_wallet, transactions.rate as tx_rate, transactions.base_rate as spot_rate, COALESCE(la_totals.total_lot_allocated, 0) AS total_lot_allocated")
            ->join('users', 'users.id = contracts.user_id')
            ->join('transactions', 'transactions.id = contracts.transaction_id', 'left')
            ->join("(SELECT contract_id, SUM(usdt_amount) AS total_lot_allocated FROM lot_allocations WHERE status IN ('reserved', 'delivered') GROUP BY contract_id) la_totals", 'la_totals.contract_id = contracts.id', 'left')
            ->where('contracts.id', $id)
            ->first();

        if (!$contract) {
            return $this->response->setStatusCode(404)->setBody('');
        }

        return view('admin/contracts/_row', ['contract' => $contract]);
    }

    public function deliverUsdt(int $id)
    {
        if ($response = $this->checkPermission('enviar_usdt')) return $response;
        $contractModel = new \App\Models\ContractModel();
        $amountUsdt = (float)$this->request->getPost('amount_usdt');
        $notes = $this->request->getPost('notes');

        $contract = $contractModel->find($id);
        if (!$contract) return redirect()->back()->with('error', 'Contrato não encontrado.');

        if ($amountUsdt > 0) {
            // Validação de segurança: não enviar mais USDT que o total do contrato
            $remainingUsdt = (float)$contract['total_amount'] - (float)$contract['delivered_usdt'];
            if ($amountUsdt > $remainingUsdt) {
                return redirect()->back()->withInput()->with('error', 'O valor de USDT (' . number_format($amountUsdt, 2, '.', ',') . ') não pode ser superior ao saldo restante a enviar (' . number_format($remainingUsdt, 2, '.', ',') . ').');
            }

            $newDelivered = (float)$contract['delivered_usdt'] + $amountUsdt;
            $contractModel->update($id, ['delivered_usdt' => $newDelivered]);

            // Consome alocações reservadas na ordem de criação, suportando entrega parcial
            $db = \Config\Database::connect();
            $allocationModel = new \App\Models\LotAllocationModel();
            $revenuePerUsdt  = $allocationModel->getRevenuePerUsdt('contract', (int)$id);
            $reservations = $db->query(
                "SELECT la.*, ul.conversion_rate
                 FROM lot_allocations la
                 JOIN usdt_lots ul ON ul.id = la.lot_id
                 WHERE (la.contract_id = ? OR (la.contract_id IS NULL AND la.transaction_id = ?)) AND la.status = 'reserved'
                 ORDER BY la.created_at ASC",
                [$id, $contract['transaction_id']]
            )->getResultArray();

            $adminId   = session()->get('user_id');
            $toConsume = $amountUsdt;
            foreach ($reservations as $res) {
                if ($toConsume <= 0) break;
                $consume = min($toConsume, (float)$res['usdt_amount']);
                $allocationModel->deliverFromReservation($res, $consume, $adminId, $revenuePerUsdt);
                $toConsume = round($toConsume - $consume, 4);
            }

            $financialModel = new \App\Models\FinancialStatementModel();
            $financialModel->insert([
                'user_id' => $contract['user_id'],
                'admin_id' => session()->get('user_id'),
                'contract_id' => $id,
                'operation_type' => 'withdrawal',
                'nature' => 'D',
                'amount' => $amountUsdt,
                'description' => 'Envio de USDT - Contrato #' . $id,
                'notes' => $notes,
                'transaction_date' => date('Y-m-d H:i:s')
            ]);

            (new \App\Models\ActivityLogModel())->record('contract.usdt_delivered', 'contract', (int)$id, [
                'usdt_amount' => $amountUsdt,
                'notes'       => $notes,
            ]);

            return redirect()->back()->with('success_modal', [
                'type' => 'delivery',
                'value' => number_format($amountUsdt, 2, '.', ',') . ' USDT'
            ]);
        }

        return redirect()->back()->with('error', 'Valor de USDT inválido.');
    }

    public function deliveryQueue()
    {
        $contractModel = new \App\Models\ContractModel();
        $deliveries    = $contractModel->getPendingDeliveries();
        $today         = date('Y-m-d');

        $grouped = [];
        foreach ($deliveries as $d) {
            $uid = $d['user_id'];
            $dueDate  = date('Y-m-d', strtotime($d['due_date']));
            $daysLate = (int) round((strtotime($today) - strtotime($dueDate)) / 86400);

            if (!isset($grouped[$uid])) {
                $grouped[$uid] = [
                    'user_id'        => $uid,
                    'user_name'      => $d['user_name'],
                    'usdt_wallet'    => $d['usdt_wallet'],
                    'requested_wallet' => $d['requested_wallet'],
                    'contract_count' => 0,
                    'pending_usdt'   => 0.0,
                    'delivered_usdt' => 0.0,
                    'total_amount'   => 0.0,
                    'paid_amount'    => 0.0,
                    'total_brl'      => 0.0,
                    'unlinked_usdt'  => 0.0,
                    'sendable_usdt'  => 0.0,
                    'max_days_late'  => $daysLate,
                    'earliest_due'   => $d['due_date'],
                ];
            }

            $g = &$grouped[$uid];
            $g['contract_count']++;
            $g['pending_usdt']   += (float) $d['pending_usdt'];
            $g['delivered_usdt'] += (float) $d['delivered_usdt'];
            $g['total_amount']   += (float) $d['total_amount'];
            $g['paid_amount']    += (float) $d['paid_amount'];
            $g['total_brl']      += (float) $d['total_brl'];
            $g['unlinked_usdt']  += (float) $d['unlinked_usdt'];
            $g['sendable_usdt']  += (float) ($d['sendable_usdt'] ?? 0);

            if ($daysLate > $g['max_days_late']) {
                $g['max_days_late'] = $daysLate;
            }
            if ($d['due_date'] < $g['earliest_due']) {
                $g['earliest_due'] = $d['due_date'];
            }
        }
        unset($g);

        usort($grouped, function($a, $b) {
            return $b['max_days_late'] <=> $a['max_days_late'] ?: strcmp($a['earliest_due'], $b['earliest_due']);
        });

        return view('admin/delivery/index', [
            'grouped'     => $grouped,
            'active_menu' => 'delivery',
        ]);
    }

    public function deliveryQueueClient(int $userId)
    {
        $contractModel = new \App\Models\ContractModel();
        $deliveries    = $contractModel->getPendingDeliveries($userId, 'profit');

        $userModel = new \App\Models\UserModel();
        $client    = $userModel->find($userId);

        return view('admin/delivery/client', [
            'deliveries'  => $deliveries,
            'client'      => $client,
            'active_menu' => 'delivery',
        ]);
    }

    /**
     * Envio em massa pela fila: distribui o valor entre os contratos do cliente
     * priorizando a maior margem (taxa do cliente − custo do lote reservado).
     * Só consome USDT coberto por lote reservado; o "sem lote" nunca é tocado.
     */
    public function deliverUsdtBulk(int $userId)
    {
        if ($response = $this->checkPermission('enviar_usdt')) return $response;
        $amountUsdt = round((float)$this->request->getPost('amount_usdt'), 2);
        $notes      = $this->request->getPost('notes');
        $adminId    = session()->get('user_id');

        if ($amountUsdt < 0.01) {
            return redirect()->back()->with('error', 'Valor de USDT inválido.');
        }

        $db              = \Config\Database::connect();
        $allocationModel = new \App\Models\LotAllocationModel();
        $contractModel   = new \App\Models\ContractModel();

        $deliveredByContract = [];

        $db->transBegin();
        try {
            $reservations = $allocationModel->getReservedForUserByMargin($userId);

            // Entregável por contrato = pendente proporcional ao pago, limitado ao reservado em lote
            $pendingByContract  = [];
            $reservedByContract = [];
            foreach ($reservations as $res) {
                $cid = (int)$res['contract_id'];
                if (!isset($pendingByContract[$cid])) {
                    $paidRatio = (float)$res['total_brl'] > 0
                        ? min(1.0, (float)$res['paid_amount'] / (float)$res['total_brl'])
                        : 0.0;
                    $pendingByContract[$cid]  = max(0, round($paidRatio * (float)$res['total_amount'], 2) - (float)$res['delivered_usdt']);
                    $reservedByContract[$cid] = 0.0;
                }
                $reservedByContract[$cid] += (float)$res['usdt_amount'];
            }

            $sendable = 0.0;
            foreach ($pendingByContract as $cid => $pending) {
                $sendable += min($pending, $reservedByContract[$cid]);
            }
            $sendable = round($sendable, 2);

            // Teto absoluto: soma do saldo restante (total - entregue) de todas as operações pendentes do cliente,
            // usado apenas quando o valor pedido ultrapassa o fluxo normal (lote + pagamento).
            $allPending  = $contractModel->getPendingDeliveries($userId, 'profit');
            $absoluteMax = 0.0;
            foreach ($allPending as $p) {
                $absoluteMax += max(0, (float)$p['total_amount'] - (float)$p['delivered_usdt']);
            }
            $absoluteMax = round($absoluteMax, 2);

            if ($absoluteMax < 0.01) {
                $db->transRollback();
                return redirect()->back()->with('error', 'Este cliente não possui USDT pendente de envio.');
            }
            if ($amountUsdt > $absoluteMax + 0.009) {
                $db->transRollback();
                return redirect()->back()->with('error', 'O valor informado (' . number_format($amountUsdt, 2, '.', ',') . ' USDT) excede o saldo total pendente do cliente (' . number_format($absoluteMax, 2, '.', ',') . ' USDT).');
            }

            $remaining = $amountUsdt;

            // 1) Consome primeiro o que está coberto por lote reservado (fluxo normal, prioridade por margem).
            foreach ($reservations as $res) {
                if ($remaining < 0.01) break;
                $cid = (int)$res['contract_id'];

                $deliver = round(min((float)$res['usdt_amount'], $pendingByContract[$cid], $remaining), 2);
                if ($deliver < 0.01) continue;

                $revenuePerUsdt = (float)$res['total_amount'] > 0
                    ? (float)$res['comercial_brl'] / (float)$res['total_amount']
                    : 0.0;
                $allocationModel->deliverFromReservation($res, $deliver, $adminId, $revenuePerUsdt);

                $pendingByContract[$cid]   = round($pendingByContract[$cid] - $deliver, 2);
                $deliveredByContract[$cid] = round(($deliveredByContract[$cid] ?? 0) + $deliver, 2);
                $remaining                 = round($remaining - $deliver, 2);
            }

            // 2) Excedente fora do fluxo normal (sem lote e/ou sem pagamento total ainda efetivado): distribui pela
            // mesma ordem de prioridade (maior margem primeiro, sem lote por último), sem consumir reservas de lote.
            if ($remaining >= 0.01) {
                foreach ($allPending as $p) {
                    if ($remaining < 0.01) break;
                    $cid          = (int)$p['id'];
                    $alreadySent  = $deliveredByContract[$cid] ?? 0.0;
                    $contractLeft = max(0, (float)$p['total_amount'] - (float)$p['delivered_usdt'] - $alreadySent);
                    $deliver      = round(min($contractLeft, $remaining), 2);
                    if ($deliver < 0.01) continue;

                    $deliveredByContract[$cid] = round($alreadySent + $deliver, 2);
                    $remaining                 = round($remaining - $deliver, 2);
                }
            }

            $financialModel = new \App\Models\FinancialStatementModel();
            $activityModel  = new \App\Models\ActivityLogModel();

            foreach ($deliveredByContract as $cid => $delivered) {
                $contract = $contractModel->find($cid);
                $contractModel->update($cid, ['delivered_usdt' => (float)$contract['delivered_usdt'] + $delivered]);

                $financialModel->insert([
                    'user_id'          => $userId,
                    'admin_id'         => $adminId,
                    'contract_id'      => $cid,
                    'operation_type'   => 'withdrawal',
                    'nature'           => 'D',
                    'amount'           => $delivered,
                    'description'      => 'Envio de USDT (fila de envio) - Contrato #' . $cid,
                    'notes'            => $notes,
                    'transaction_date' => date('Y-m-d H:i:s'),
                ]);

                $activityModel->record('contract.usdt_delivered', 'contract', $cid, [
                    'usdt_amount' => $delivered,
                    'bulk_send'   => true,
                    'notes'       => $notes,
                ]);
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'deliverUsdtBulk falhou: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Falha ao processar o envio. Nenhum valor foi registrado.');
        }

        $parts = array_map(
            fn($cid) => '#' . $cid . ': ' . number_format($deliveredByContract[$cid], 2, '.', ','),
            array_keys($deliveredByContract)
        );
        $total = round(array_sum($deliveredByContract), 2);

        return redirect()->back()->with('success',
            'Envio de ' . number_format($total, 2, '.', ',') . ' USDT distribuído por maior lucro — ' . implode(' · ', $parts)
        );
    }

    public function blockDelivery(int $id)
    {
        $contractModel = new \App\Models\ContractModel();
        $contractModel->update($id, ['delivery_blocked' => 1]);
        (new \App\Models\ActivityLogModel())->record('contract.delivery_blocked', 'contract', $id);
        $back = $this->request->getPost('redirect') ?: url_to('admin_delivery');
        return redirect()->to($back)->with('success', 'Entrega do contrato #' . $id . ' bloqueada.');
    }

    public function unblockDelivery(int $id)
    {
        $contractModel = new \App\Models\ContractModel();
        $contractModel->update($id, ['delivery_blocked' => 0]);
        (new \App\Models\ActivityLogModel())->record('contract.delivery_unblocked', 'contract', $id);
        return redirect()->to(url_to('admin_delivery'))->with('success', 'Entrega do contrato #' . $id . ' desbloqueada.');
    }

    public function conciliation()
    {
        if ($response = $this->checkPermission('conciliation')) return $response;
        $db       = \Config\Database::connect();
        $perPage  = (int)($this->request->getGet('per_page') ?? 20);
        $perPage  = in_array($perPage, [20, 50, 100]) ? $perPage : 20;
        $page     = max(1, (int)($this->request->getGet('page') ?? 1));
        $offset   = ($page - 1) * $perPage;

        $filterStatus    = $this->request->getGet('status')     ?? 'all';
        $filterSupplier  = $this->request->getGet('supplier')   ?? '';
        $filterClient    = $this->request->getGet('client')     ?? '';
        $filterStartDate = $this->request->getGet('start_date') ?? '';
        $filterEndDate   = $this->request->getGet('end_date')   ?? '';

        $statusValues = match($filterStatus) {
            'delivered' => ["'delivered'"],
            'reserved'  => ["'reserved'"],
            default     => ["'delivered'", "'reserved'"],
        };
        $statusIn = implode(',', $statusValues);

        $where  = "WHERE la.status IN ($statusIn)";
        $params = [];

        if ($filterStartDate) {
            $where   .= " AND la.created_at >= ?";
            $params[] = $filterStartDate . ' 00:00:00';
        }
        if ($filterEndDate) {
            $where   .= " AND la.created_at <= ?";
            $params[] = $filterEndDate . ' 23:59:59';
        }
        if ($filterSupplier) {
            $where   .= " AND ul.supplier = ?";
            $params[] = $filterSupplier;
        }
        if ($filterClient) {
            $where   .= " AND COALESCE(uc.login, ut.login) LIKE ?";
            $params[] = '%' . $filterClient . '%';
        }

        $selectCols = "
            la.id,
            la.lot_id,
            la.contract_id,
            la.transaction_id,
            la.usdt_amount,
            la.status,
            la.profit_brl,
            la.created_at,
            ul.conversion_rate   AS supplier_rate,
            ul.supplier,
            ul.delivery_type     AS supplier_delivery_type,
            COALESCE(uc.login, ut.login) AS client_name,
            COALESCE(tc.delivery_type, t.delivery_type) AS client_delivery_type,
            CASE
                WHEN la.contract_id    IS NOT NULL AND c.total_amount > 0 THEN ROUND(c.comercial_brl / c.total_amount, 4)
                WHEN la.transaction_id IS NOT NULL AND t.amount_usdt  > 0 THEN ROUND(t.comercial_brl / t.amount_usdt,  4)
                ELSE NULL
            END AS client_rate,
            CASE
                WHEN la.contract_id    IS NOT NULL AND c.total_amount > 0 THEN ROUND(c.comercial_brl / c.total_amount - ul.conversion_rate, 4)
                WHEN la.transaction_id IS NOT NULL AND t.amount_usdt  > 0 THEN ROUND(t.comercial_brl / t.amount_usdt  - ul.conversion_rate, 4)
                ELSE NULL
            END AS margin_per_usdt,
            CASE
                WHEN la.contract_id    IS NOT NULL AND c.total_amount > 0 THEN ROUND((c.comercial_brl / c.total_amount) * la.usdt_amount, 2)
                WHEN la.transaction_id IS NOT NULL AND t.amount_usdt  > 0 THEN ROUND((t.comercial_brl / t.amount_usdt)  * la.usdt_amount, 2)
                ELSE NULL
            END AS valor_cliente_brl,
            ROUND(ul.conversion_rate * la.usdt_amount, 2) AS valor_fornecedor_brl,
            du.login AS delivered_by_name
        ";

        $joins = "
            FROM lot_allocations la
            JOIN usdt_lots ul          ON ul.id = la.lot_id
            LEFT JOIN contracts c      ON c.id  = la.contract_id
            LEFT JOIN transactions t   ON t.id  = la.transaction_id
            LEFT JOIN transactions tc  ON tc.id = c.transaction_id
            LEFT JOIN users uc         ON uc.id = c.user_id
            LEFT JOIN users ut         ON ut.id = t.user_id
            LEFT JOIN users du         ON du.id = la.delivered_by
        ";

        $total = (int)$db->query("SELECT COUNT(*) AS cnt $joins $where", $params)->getRow()->cnt;
        $rows  = $db->query("SELECT $selectCols $joins $where ORDER BY la.created_at DESC LIMIT $perPage OFFSET $offset", $params)->getResultArray();

        $summary = $db->query("
            SELECT
                COALESCE(SUM(la.usdt_amount), 0)  AS total_usdt,
                COALESCE(SUM(CASE WHEN la.status = 'delivered' THEN la.profit_brl ELSE 0 END), 0) AS total_profit
            $joins $where
        ", $params)->getRow();

        $supplierModel = new \App\Models\SupplierModel();
        $suppliers     = $supplierModel->getEnabled();

        $totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;

        return view('admin/conciliation/index', [
            'rows'            => $rows,
            'total'           => $total,
            'total_pages'     => $totalPages,
            'page'            => $page,
            'per_page'        => $perPage,
            'summary'         => $summary,
            'suppliers'       => $suppliers,
            'filter_status'   => $filterStatus,
            'filter_supplier' => $filterSupplier,
            'filter_client'   => $filterClient,
            'filter_start'    => $filterStartDate,
            'filter_end'      => $filterEndDate,
            'active_menu'     => 'conciliation',
        ]);
    }

    public function settings()
    {
        if ($response = $this->checkPermission('settings')) return $response;
        $settingsModel = new \App\Models\SettingsModel();
        $data['start'] = $settingsModel->getConfig('business_hours_start', '08:00');
        $data['end'] = $settingsModel->getConfig('business_hours_end', '16:30');
        $data['start_d1'] = $settingsModel->getConfig('business_hours_d1_start', '08:00');
        $data['end_d1'] = $settingsModel->getConfig('business_hours_d1_end', '18:00');
        $data['start_d2'] = $settingsModel->getConfig('business_hours_d2_start', '08:00');
        $data['end_d2'] = $settingsModel->getConfig('business_hours_d2_end', '18:00');
        $data['quotation_flow'] = $settingsModel->getConfig('quotation_flow', 'direct');
        $data['operator_whatsapp'] = $settingsModel->getConfig('operator_whatsapp', '');
        $data['admin_alert_sound'] = $settingsModel->getConfig('admin_alert_sound', 'chime_premium');
        $data['disable_d1'] = $settingsModel->getConfig('disable_d1', '0') === '1';
        $data['disable_d2'] = $settingsModel->getConfig('disable_d2', '0') === '1';

        $userModel = new \App\Models\UserModel();
        $data['clients'] = $userModel->where('role', 'user')->orderBy('login', 'ASC')->findAll();
        $data['lock_only_with_balance_mode'] = $settingsModel->getConfig('lock_only_with_balance_mode', 'disabled');
        $data['lock_only_with_balance_clients'] = json_decode($settingsModel->getConfig('lock_only_with_balance_clients', '[]'), true) ?? [];
        
        return view('admin/settings', $data);
    }

    public function updateSettings()
    {
        if ($response = $this->checkPermission('settings')) return $response;
        $settingsModel = new \App\Models\SettingsModel();
        $start = $this->request->getPost('business_hours_start');
        $end = $this->request->getPost('business_hours_end');
        $startD1 = $this->request->getPost('business_hours_d1_start');
        $endD1 = $this->request->getPost('business_hours_d1_end');
        $startD2 = $this->request->getPost('business_hours_d2_start');
        $endD2 = $this->request->getPost('business_hours_d2_end');
        $quotationFlow = $this->request->getPost('quotation_flow');
        $operatorWhatsapp = $this->request->getPost('operator_whatsapp');
        $adminAlertSound = $this->request->getPost('admin_alert_sound');
        $disableD1 = $this->request->getPost('disable_d1') ? '1' : '0';
        $disableD2 = $this->request->getPost('disable_d2') ? '1' : '0';
        
        if ($start) $settingsModel->setConfig('business_hours_start', $start);
        if ($end) $settingsModel->setConfig('business_hours_end', $end);
        if ($startD1) $settingsModel->setConfig('business_hours_d1_start', $startD1);
        if ($endD1) $settingsModel->setConfig('business_hours_d1_end', $endD1);
        if ($startD2) $settingsModel->setConfig('business_hours_d2_start', $startD2);
        if ($endD2) $settingsModel->setConfig('business_hours_d2_end', $endD2);
        if ($quotationFlow) $settingsModel->setConfig('quotation_flow', $quotationFlow);

        if ($adminAlertSound) $settingsModel->setConfig('admin_alert_sound', $adminAlertSound);
        $settingsModel->setConfig('operator_whatsapp', $operatorWhatsapp ?? '');

        $settingsModel->setConfig('disable_d1', $disableD1);
        $settingsModel->setConfig('disable_d2', $disableD2);

        $lockOnlyWithBalanceMode = $this->request->getPost('lock_only_with_balance_mode') ?: 'disabled';
        $lockOnlyWithBalanceClients = $this->request->getPost('lock_only_with_balance_clients') ?: [];
        $settingsModel->setConfig('lock_only_with_balance_mode', $lockOnlyWithBalanceMode);
        $settingsModel->setConfig('lock_only_with_balance_clients', json_encode($lockOnlyWithBalanceClients));
        $logoFile = $this->request->getFile('logo');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            $validationRule = [
                'logo' => [
                    'label' => 'Logo',
                    'rules' => [
                        'uploaded[logo]',
                        'is_image[logo]',
                        'mime_in[logo,image/png,image/jpg,image/jpeg,image/svg+xml,image/webp]',
                        'max_size[logo,2048]',
                    ],
                ],
            ];
            if ($this->validate($validationRule)) {
                $uploadDir = FCPATH . 'uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $newName = $logoFile->getRandomName();
                $logoFile->move($uploadDir, $newName);
                $settingsModel->setConfig('logo_path', 'uploads/' . $newName);
            } else {
                return redirect()->back()->withInput()->with('error', 'Erro no upload da imagem: ' . implode(', ', $this->validator->getErrors()));
            }
        }
        
        return redirect()->back()->with('success', 'Configurações atualizadas com sucesso!');
    }

    public function checkNewTransactions()
    {
        $lastId = $this->request->getVar('last_id') ?: 0;

        $contractModel = new \App\Models\ContractModel();
        $newAlerts = $contractModel
            ->select('contracts.id, contracts.total_amount as amount_usdt, contracts.total_brl as amount_brl, contracts.type as delivery_type, users.login as user_name, contracts.id as contract_id')
            ->join('users', 'users.id = contracts.user_id')
            ->where('contracts.id >', $lastId)
            ->orderBy('contracts.id', 'ASC')
            ->findAll();

        // Normaliza para o formato esperado pelo frontend (type = 'buy')
        foreach ($newAlerts as &$row) {
            $row['type'] = 'buy';
        }

        return $this->response->setJSON($newAlerts);
    }

    public function adjustLimit(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Usuário não encontrado.');
        }

        if (session()->get('user_role') === 'operator' && $user['role'] !== 'user') {
            return redirect()->back()->with('error', 'Acesso negado: operadores só podem gerenciar clientes.');
        }

        $amount = (float)$this->request->getPost('amount');
        $operation = $this->request->getPost('operation');
        $notes = $this->request->getPost('notes') ?: 'Ajuste de Saldo/Limite';
        $adminId = session()->get('user_id');

        if ($amount <= 0) {
            return redirect()->back()->with('error', 'O valor do ajuste deve ser maior que zero.');
        }

        $financialModel = new \App\Models\FinancialStatementModel();

        if ($operation === 'add') {
            // Depósito lançado manualmente pelo admin: já entra aprovado (sem
            // comprovante) e fica com histórico em Depósitos > Aprovados.
            $depositModel = new \App\Models\DepositModel();
            $depositId = $depositModel->insert([
                'user_id'     => $id,
                'amount'      => $amount,
                'proof_file'  => '',
                'status'      => 'accepted',
                'notes'       => $notes,
                'accepted_by' => $adminId,
                'accepted_at' => date('Y-m-d H:i:s'),
            ]);
            $deposit = $depositModel->find($depositId);
            $depositModel->applyAcceptedDeposit($deposit, $adminId, $notes);
        } else {
            $financialModel->insert([
                'user_id'          => $id,
                'admin_id'         => $adminId,
                'contract_id'      => null,
                'operation_type'   => 'adjustment_subtract',
                'nature'           => 'D',
                'amount'           => $amount,
                'description'      => $notes,
                'transaction_date' => date('Y-m-d H:i:s')
            ]);
        }

        $newBalance = $financialModel->getBalance($id);
        return redirect()->back()->with('success', 'Ajuste aplicado para ' . $user['login'] . '. Novo saldo: R$ ' . number_format($newBalance, 2, ',', '.') . '.');
    }

    public function transferLimit(int $id)
    {
        $userModel = new UserModel();
        $sourceUser = $userModel->find($id);
        if (!$sourceUser) {
            return redirect()->back()->with('error', 'Usuário de origem não encontrado.');
        }

        $targetUserId = $this->request->getPost('target_user_id');
        if (empty($targetUserId) || $targetUserId == $id) {
            return redirect()->back()->with('error', 'Selecione um usuário de destino válido.');
        }

        $targetUser = $userModel->find($targetUserId);
        if (!$targetUser) {
            return redirect()->back()->with('error', 'Usuário de destino não encontrado.');
        }

        $amount = (float)$this->request->getPost('amount');
        if ($amount <= 0) {
            return redirect()->back()->with('error', 'O valor da transferência deve ser maior que zero.');
        }

        $financialModel = new \App\Models\FinancialStatementModel();
        $sourceBalance = $financialModel->getBalance($id);
        if (($sourceBalance - $amount) < 0) {
            return redirect()->back()->with('error', 'Saldo insuficiente para a transferência. Disponível: R$ ' . number_format(max(0, $sourceBalance), 2, ',', '.') . '.');
        }

        $notes = $this->request->getPost('notes') ?: 'Transferência de Saldo';

        $db = \Config\Database::connect();
        $db->transStart();
        
        // 1. Débito no extrato do usuário de origem
        $financialModel->insert([
            'user_id'          => $id,
            'admin_id'         => session()->get('user_id'),
            'contract_id'      => null,
            'operation_type'   => 'adjustment_subtract',
            'nature'           => 'D',
            'amount'           => $amount,
            'description'      => $notes . ' (Enviado para ' . $targetUser['login'] . ')',
            'transaction_date' => date('Y-m-d H:i:s')
        ]);

        // 2. Crédito no extrato do usuário de destino
        $financialModel->insert([
            'user_id'          => $targetUserId,
            'admin_id'         => session()->get('user_id'),
            'contract_id'      => null,
            'operation_type'   => 'adjustment_add',
            'nature'           => 'C',
            'amount'           => $amount,
            'description'      => $notes . ' (Recebido de ' . $sourceUser['login'] . ')',
            'transaction_date' => date('Y-m-d H:i:s')
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Ocorreu um erro ao processar a transferência de saldo.');
        }

        return redirect()->back()->with('success', 'Transferência de R$ ' . number_format($amount, 2, ',', '.') . ' realizada com sucesso de ' . $sourceUser['login'] . ' para ' . $targetUser['login'] . '!');
    }

    public function changeContractDeliveryType(int $id)
    {
        $contractModel = new \App\Models\ContractModel();
        $contract = $contractModel->find($id);
        if (!$contract) {
            return redirect()->back()->with('error', 'Contrato não encontrado.');
        }

        $newType = $this->request->getPost('delivery_type');
        if (!in_array(strtolower($newType), ['d+0', 'd+1', 'd+2'])) {
            return redirect()->back()->with('error', 'Prazo de entrega inválido.');
        }

        // Atualiza o prazo de entrega do contrato
        $contractModel->update($id, ['type' => $newType]);

        // Grava no extrato financeiro se existir uma transação vinculada por compatibilidade
        if (!empty($contract['transaction_id'])) {
            $transactionModel = new \App\Models\TransactionModel();
            $transactionModel->update($contract['transaction_id'], ['delivery_type' => strtoupper($newType)]);
        }

        return redirect()->back()->with('success', 'Prazo de entrega do contrato #' . $id . ' atualizado com sucesso para ' . strtoupper($newType) . '!');
    }

    public function registerPurchase(int $id)
    {
        if (session()->get('user_role') !== 'admin') {
            return redirect()->back()->with('error', 'Apenas administradores podem registrar compras para clientes.');
        }

        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Usuário não encontrado.');
        }

        $usdtAmount = (float)$this->request->getPost('usdt_amount');
        $deliveryType = $this->request->getPost('delivery_type'); // D+0, D+1, D+2
        $baseRate = (float)$this->request->getPost('base_rate');
        $notes = $this->request->getPost('notes') ?: 'Compra registrada via Admin';

        if ($usdtAmount <= 0) {
            return redirect()->back()->with('error', 'O valor da compra deve ser maior que zero.');
        }
        if ($baseRate <= 0) {
            return redirect()->back()->with('error', 'A cotação base deve ser maior que zero.');
        }
        if (!in_array($deliveryType, ['D+0', 'D+1', 'D+2'])) {
            return redirect()->back()->with('error', 'Prazo de entrega inválido.');
        }

        $feePercent = 0.0;
        $rate = $baseRate;
        $brlAmount = round($usdtAmount * $rate, 2);
        $comercialBrl = round($usdtAmount * $baseRate, 2);
        $feeBrl = round($brlAmount - $comercialBrl, 2);

        $contractModel = new \App\Models\ContractModel();
        $financialModel = new \App\Models\FinancialStatementModel();

        // Valida saldo: balance - compra >= 0 (Comentado para permitir compra mesmo com saldo abaixo de 0,00)
        $balance = $financialModel->getBalance($id);
        /*
        if (($balance - $brlAmount) < 0) {
            return redirect()->back()->with('error', 'Saldo insuficiente. Disponível para compra: R$ ' . number_format(max(0, $balance), 2, ',', '.') . '.');
        }
        */

        // Calcula a data de vencimento
        $days = 0;
        if ($deliveryType === 'D+1') { $days = 1; }
        elseif ($deliveryType === 'D+2') { $days = 2; }
        $dueDate = date('Y-m-d H:i:s', strtotime("+$days days"));

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Toda compra gera contrato (margin_lock reduz saldo via ledger)
        $contractId = null;
        {
            $contractId = $contractModel->insert([
                'user_id'           => $id,
                'total_amount'      => $usdtAmount,
                'total_brl'         => $brlAmount,
                'remaining_balance' => $brlAmount,
                'type'              => strtolower($deliveryType),
                'due_date'          => $dueDate,
                'status'            => 'pending',
                'fee_percent'       => $feePercent,
                'comercial_brl'     => $comercialBrl,
                'fee_brl'           => $feeBrl
            ]);
        }

        // 2. Cria a transação
        $transactionModel = new \App\Models\TransactionModel();
        $transactionId = $transactionModel->insert([
            'user_id'        => $id,
            'type'           => 'buy',
            'amount_brl'     => $brlAmount,
            'amount_usdt'    => $usdtAmount,
            'rate'           => $rate,
            'base_rate'      => $baseRate,
            'fee_percent'    => $feePercent,
            'comercial_brl'  => $comercialBrl,
            'fee_brl'        => $feeBrl,
            'status'         => 'pending',
            'delivery_type'  => $deliveryType,
            'wallet_address' => $user['usdt_wallet'] ?: 'Não informada'
        ]);

        if ($contractId) {
            $contractModel->update($contractId, ['transaction_id' => $transactionId]);

            // 3. Grava o bloqueio de margem no extrato financeiro (reduz saldo via ledger)
            $financialModel->insert([
                'user_id'          => $id,
                'admin_id'         => session()->get('user_id'),
                'contract_id'      => $contractId,
                'operation_type'   => 'margin_lock',
                'nature'           => 'D', // Débito (Consome o limite)
                'amount'           => $brlAmount,
                'description'      => $notes . ' (Contrato #' . $contractId . ')',
                'transaction_date' => date('Y-m-d H:i:s'),
                'fee_percent'      => $feePercent,
                'comercial_brl'    => $comercialBrl,
                'fee_brl'          => $feeBrl
            ]);

            // Auto-paga com saldo positivo que existia antes do margin_lock
            $autoPayAmount = min($brlAmount, max(0.0, $balance));
            if ($autoPayAmount > 0.01) {
                $contractModel->registerPayment($contractId, $autoPayAmount);
            }
        } else {
            // D+0: compra à vista, registra margin_lock direto (sem contrato)
            $financialModel->insert([
                'user_id'          => $id,
                'admin_id'         => session()->get('user_id'),
                'contract_id'      => null,
                'operation_type'   => 'margin_lock',
                'nature'           => 'D',
                'amount'           => $brlAmount,
                'description'      => $notes . ' (À vista D+0)',
                'transaction_date' => date('Y-m-d H:i:s'),
                'fee_percent'      => $feePercent,
                'comercial_brl'    => $comercialBrl,
                'fee_brl'          => $feeBrl
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Ocorreu um erro ao registrar a compra.');
        }

        return redirect()->back()->with('success', 'Compra de ' . number_format($usdtAmount, 2, '.', ',') . ' USDT registrada com sucesso para o cliente ' . $user['login'] . '!');
    }

    public function lockHeartbeat(int $id)
    {
        $db = \Config\Database::connect();
        $currentUserId = session()->get('user_id');

        // Check if lock exists for this contract
        $lock = $db->table('contract_locks')
                   ->where('contract_id', $id)
                   ->get()
                   ->getRow();

        if ($lock) {
            if ((int)$lock->user_id === (int)$currentUserId) {
                // Refresh lock timestamp
                $db->table('contract_locks')
                   ->where('contract_id', $id)
                   ->update(['updated_at' => date('Y-m-d H:i:s')]);
                return $this->response->setJSON(['status' => 'success']);
            } else {
                $lockingUser = $db->table('users')->where('id', $lock->user_id)->get()->getRow();
                $lockingUserName = $lockingUser ? $lockingUser->login : 'Outro operador';
                return $this->response->setJSON([
                    'status' => 'locked_by_other',
                    'message' => "Este contrato #{$id} foi assumido por {$lockingUserName}."
                ]);
            }
        } else {
            // Lock might have expired, re-create it
            $db->table('contract_locks')->insert([
                'contract_id' => $id,
                'user_id' => $currentUserId,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            return $this->response->setJSON(['status' => 'success']);
        }
    }

    public function getUserStatement(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            return $this->response->setJSON(['error' => 'Usuário não encontrado.'])->setStatusCode(404);
        }

        if (session()->get('user_role') === 'operator' && $user['role'] !== 'user') {
            return $this->response->setJSON(['error' => 'Acesso negado: operadores só podem gerenciar clientes.'])->setStatusCode(403);
        }

        $financialModel = new \App\Models\FinancialStatementModel();
        $history = $financialModel->select('financial_statements.*, admins.login as admin_name')
                                  ->join('users as admins', 'admins.id = financial_statements.admin_id', 'left')
                                  ->where('financial_statements.user_id', $id)
                                  ->orderBy('transaction_date', 'DESC')
                                  ->findAll();

        return $this->response->setJSON([
            'user' => $user,
            'history' => $history
        ]);
    }

    public function exportUserStatementJson(int $id)
    {
        if ($response = $this->checkPermission('usuarios')) return $response;

        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Usuário não encontrado.');
        }

        if (session()->get('user_role') === 'operator' && $user['role'] !== 'user') {
            return redirect()->back()->with('error', 'Acesso negado: operadores só podem gerenciar clientes.');
        }

        $financialModel = new \App\Models\FinancialStatementModel();
        $history = $financialModel->select('financial_statements.*, admins.login as admin_name')
                                  ->join('users as admins', 'admins.id = financial_statements.admin_id', 'left')
                                  ->where('financial_statements.user_id', $id)
                                  ->orderBy('transaction_date', 'DESC')
                                  ->findAll();

        $cleanName = preg_replace('/[^a-zA-Z0-9_]/', '_', $user['login']);
        $filename = 'extrato_' . strtolower($cleanName) . '_' . date('Ymd_His') . '.json';

        $data = [
            'user' => [
                'id' => $user['id'],
                'login' => $user['login'],
                'fee_percent' => $user['fee_percent'],
                'role' => $user['role'],
                'created_at' => $user['created_at']
            ],
            'statement' => $history
        ];

        return $this->response->setHeader('Content-Type', 'application/json')
                              ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                              ->setBody(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function exportUserStatementPdf(int $id)
    {
        if ($response = $this->checkPermission('usuarios')) return $response;

        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Usuário não encontrado.');
        }

        if (session()->get('user_role') === 'operator' && $user['role'] !== 'user') {
            return redirect()->back()->with('error', 'Acesso negado: operadores só podem gerenciar clientes.');
        }

        $financialModel = new \App\Models\FinancialStatementModel();
        $history = $financialModel->select('financial_statements.*, admins.login as admin_name')
                                  ->join('users as admins', 'admins.id = financial_statements.admin_id', 'left')
                                  ->where('financial_statements.user_id', $id)
                                  ->orderBy('transaction_date', 'DESC')
                                  ->findAll();

        $typeLabels = [
            'deposit'              => 'Depósito Aprovado',
            'withdrawal'           => 'Saída / Retirada',
            'margin_lock'          => 'Bloqueio de Margem',
            'limit_release'        => 'Liberação de Limite',
            'partial_amortization' => 'Amortização Parcial',
            'full_settlement'      => 'Liquidação Integral',
            'late_fee'             => 'Multa / Juros',
            'adjustment_add'       => 'Saldo Adicionado',
            'adjustment_subtract'  => 'Saldo Subtraído',
        ];

        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Extrato Financeiro - ' . esc($user['login']) . '</title>
    <style>
        body { font-family: "Segoe UI", Helvetica, Arial, sans-serif; color: #1e293b; background: #fff; margin: 30px; font-size: 13px; line-height: 1.5; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: 700; margin: 0 0 5px 0; color: #0f172a; }
        .header p { margin: 2px 0; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8fafc; border-bottom: 2px solid #cbd5e1; color: #475569; font-weight: 600; text-align: left; padding: 10px 12px; font-size: 11px; text-transform: uppercase; }
        td { border-bottom: 1px solid #e2e8f0; padding: 12px; vertical-align: top; }
        tr:nth-child(even) td { background: #fafafa; }
        .amount-c { color: #16a34a; font-weight: bold; }
        .amount-d { color: #dc2626; font-weight: bold; }
        @media print {
            body { margin: 15px; font-size: 12px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>GUARDIAN CORRETORA</h1>
            <p>Relatório de Extrato Financeiro - Área Administrativa</p>
        </div>
        <div style="text-align: right;">
            <p><span>Cliente:</span> ' . esc($user['login']) . '</p>
            <p>Gerado em: ' . date('d/m/Y H:i') . '</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Tipo</th>
                <th>Operação</th>
                <th>Descrição</th>
                <th style="text-align: right;">Valor</th>
            </tr>
        </thead>
        <tbody>';

        if (empty($history)) {
            echo '<tr><td colspan="5" style="text-align: center; color: #94a3b8; padding: 30px;">Nenhuma transação encontrada.</td></tr>';
        } else {
            foreach ($history as $row) {
                $isCredit = $row['nature'] === 'C';
                $class = $isCredit ? 'amount-c' : 'amount-d';
                $sign = $isCredit ? '+ ' : '− ';

                $label = $typeLabels[$row['operation_type']] ?? $row['operation_type'];
                $dateStr = date('d/m/Y H:i', strtotime($row['transaction_date']));
                
                $isUsdt = ($row['operation_type'] === 'withdrawal' || str_contains($row['description'] ?? '', 'Depósito de USDT'));
                $isBrl = !$isUsdt;
                $amount = $isBrl ? 'R$ ' . number_format($row['amount'], 2, ',', '.') : number_format($row['amount'], 2, '.', ',') . ' USDT';

                echo '<tr>
                    <td style="white-space: nowrap;">' . $dateStr . '</td>
                    <td>' . ($isCredit ? 'Entrada' : 'Saída') . '</td>
                    <td><strong>' . esc($label) . '</strong></td>
                    <td>
                        ' . esc($row['description']) . '
                    </td>
                    <td style="text-align: right;" class="' . $class . '">' . $sign . $amount . '</td>
                </tr>';
            }
        }

        echo '</tbody>
    </table>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>';
        exit;
    }

    public function exportUserStatementCsv(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Usuário não encontrado.');
        }

        if (session()->get('user_role') === 'operator' && $user['role'] !== 'user') {
            return redirect()->back()->with('error', 'Acesso negado: operadores só podem gerenciar clientes.');
        }

        $financialModel = new \App\Models\FinancialStatementModel();
        $history = $financialModel->select('financial_statements.*, admins.login as admin_name')
                                  ->join('users as admins', 'admins.id = financial_statements.admin_id', 'left')
                                  ->where('financial_statements.user_id', $id)
                                  ->orderBy('transaction_date', 'DESC')
                                  ->findAll();

        $cleanName = preg_replace('/[^a-zA-Z0-9_]/', '_', $user['login']);
        $filename = 'extrato_' . strtolower($cleanName) . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM
        fputs($output, "\xEF\xBB\xBF");

        // Headers
        fputcsv($output, [
            'ID',
            'Data',
            'Tipo Operacao',
            'Natureza',
            'Valor',
            'Taxa %',
            'Comercial BRL',
            'Taxa BRL',
            'Descricao',
            'Operador',
            'Metodo Pagamento',
            'Notas'
        ], ';');

        foreach ($history as $row) {
            $nature = $row['nature'] === 'C' ? 'Credito (Entrada)' : 'Debito (Saida)';
            
            $isUsdt = ($row['operation_type'] === 'withdrawal' || str_contains($row['description'] ?? '', 'Depósito de USDT'));
            $isBrl = !$isUsdt;
            $unit = $isBrl ? 'R$' : 'USDT';
            $formattedAmount = $isBrl 
                ? 'R$ ' . number_format($row['amount'], 2, ',', '.') 
                : 'USDT ' . number_format($row['amount'], 2, '.', ',');

            fputcsv($output, [
                $row['id'],
                date('d/m/Y H:i:s', strtotime($row['transaction_date'])),
                $row['operation_type'],
                $nature,
                $formattedAmount,
                number_format($row['fee_percent'] ?? 0, 4, ',', '.'),
                number_format($row['comercial_brl'] ?? 0, 2, ',', '.'),
                number_format($row['fee_brl'] ?? 0, 2, ',', '.'),
                $row['description'],
                $row['admin_name'] ?: 'Sistema',
                $row['payment_method'] ?: 'N/A',
                $row['notes'] ?: ''
            ], ';');
        }

        fclose($output);
        exit;
    }

    public function userActivity(int $id)
    {
        if ($response = $this->checkPermission('usuarios')) return $response;

        if (session()->get('user_role') === 'operator') {
            return redirect()->to('/admin/users')->with('error', 'Acesso negado.');
        }

        $userModel = new \App\Models\UserModel();
        $operator  = $userModel->find($id);
        if (!$operator) {
            return redirect()->to('/admin/users')->with('error', 'Usuário não encontrado.');
        }

        $startDate = $this->request->getGet('start_date') ?? '';
        $endDate   = $this->request->getGet('end_date')   ?? '';
        $perPage   = (int)($this->request->getGet('per_page') ?? 20);
        $perPage   = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;
        $page      = max(1, (int)($this->request->getGet('page') ?? 1));
        $offset    = ($page - 1) * $perPage;

        $db = \Config\Database::connect();

        $dateWhereLogs = '';
        $dateWhereFs   = '';
        $params        = [];

        if ($startDate) {
            $dateWhereLogs .= " AND al.created_at >= ?";
            $dateWhereFs   .= " AND fs.transaction_date >= ?";
            $params[]       = $startDate . ' 00:00:00';
            $params[]       = $startDate . ' 00:00:00';
        }
        if ($endDate) {
            $dateWhereLogs .= " AND al.created_at <= ?";
            $dateWhereFs   .= " AND fs.transaction_date <= ?";
            $params[]       = $endDate . ' 23:59:59';
            $params[]       = $endDate . ' 23:59:59';
        }

        $c = 'COLLATE utf8mb4_unicode_ci';
        $unionSql = "
            SELECT
                CONVERT('activity' USING utf8mb4)     $c AS source,
                al.created_at                            AS date,
                CONVERT(al.action USING utf8mb4)      $c AS label,
                CONVERT(al.entity_type USING utf8mb4) $c AS entity_type,
                al.entity_id,
                CAST(NULL AS DECIMAL(18,4))              AS amount,
                CAST(NULL AS CHAR)                    $c AS nature,
                CONVERT(al.payload USING utf8mb4)     $c AS payload,
                CONVERT(al.ip_address USING utf8mb4)  $c AS ip_address,
                CAST(NULL AS CHAR)                    $c AS client_name,
                CAST(NULL AS UNSIGNED)                   AS contract_id,
                CAST(NULL AS CHAR)                    $c AS payment_method,
                CAST(NULL AS CHAR)                    $c AS notes,
                CAST(NULL AS CHAR)                    $c AS description
            FROM activity_logs al
            WHERE al.user_id = ? {$dateWhereLogs}

            UNION ALL

            SELECT
                CONVERT('financial' USING utf8mb4)          $c AS source,
                fs.transaction_date                            AS date,
                CONVERT(fs.operation_type USING utf8mb4)    $c AS label,
                CAST(NULL AS CHAR)                          $c AS entity_type,
                CAST(NULL AS UNSIGNED)                         AS entity_id,
                fs.amount,
                CONVERT(fs.nature USING utf8mb4)            $c AS nature,
                CAST(NULL AS CHAR)                          $c AS payload,
                CAST(NULL AS CHAR)                          $c AS ip_address,
                CONVERT(COALESCE(u.login,'') USING utf8mb4) $c AS client_name,
                fs.contract_id,
                CONVERT(COALESCE(fs.payment_method,'') USING utf8mb4) $c AS payment_method,
                CONVERT(COALESCE(fs.notes,'') USING utf8mb4)          $c AS notes,
                CONVERT(COALESCE(fs.description,'') USING utf8mb4)    $c AS description
            FROM financial_statements fs
            LEFT JOIN users u ON u.id = fs.user_id
            WHERE fs.admin_id = ? {$dateWhereFs}
        ";

        $countParams = array_merge([$id], $params, [$id], $params);
        $totalResult = $db->query("SELECT COUNT(*) AS total FROM ({$unionSql}) AS t", $countParams)->getRow();
        $total       = (int)($totalResult->total ?? 0);
        $totalPages  = max(1, (int)ceil($total / $perPage));

        $dataParams = array_merge([$id], $params, [$id], $params);
        $timeline   = $db->query(
            "{$unionSql} ORDER BY date DESC LIMIT ? OFFSET ?",
            array_merge($dataParams, [$perPage, $offset])
        )->getResultArray();

        foreach ($timeline as &$row) {
            if ($row['source'] === 'activity' && $row['payload']) {
                $row['payload'] = json_decode($row['payload'], true) ?? [];
            }
        }
        unset($row);

        return view('admin/users/activity', [
            'operator'    => $operator,
            'timeline'    => $timeline,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'per_page'    => $perPage,
            'page'        => $page,
            'total'       => $total,
            'total_pages' => $totalPages,
            'active_menu' => 'users',
        ]);
    }
}
