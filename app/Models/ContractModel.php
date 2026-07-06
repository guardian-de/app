<?php

namespace App\Models;

use CodeIgniter\Model;

class ContractModel extends Model
{
    protected $table            = 'contracts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'transaction_id', 'total_amount', 'delivered_usdt', 'total_brl', 'paid_amount', 'paid_client', 'remaining_balance',
        'type', 'due_date', 'status', 'interest_accumulated', 'locked_by', 'locked_at', 'fee_percent', 'comercial_brl', 'fee_brl',
        'delivery_blocked'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeUpdate   = ['recalculateBalance'];
    protected $beforeInsert   = ['recalculateBalance'];

    protected function recalculateBalance(array $data)
    {
        if (isset($data['data'])) {
            $row = $data['data'];
            
            // Se estivermos inserindo ou se os campos chave estiverem presentes no update
            // Precisamos dos valores atuais se eles não estiverem no array $row
            $totalBrl = $row['total_brl'] ?? null;
            $interest = $row['interest_accumulated'] ?? null;
            $paid = $row['paid_amount'] ?? null;

            // Se for update e faltar algum campo, buscamos o original
            if (isset($data['id'])) {
                $original = $this->find($data['id'][0]);
                if ($totalBrl === null) $totalBrl = $original['total_brl'] ?? 0;
                if ($interest === null) $interest = $original['interest_accumulated'] ?? 0;
                if ($paid === null) $paid = $original['paid_amount'] ?? 0;
            }

            if ($totalBrl !== null && $interest !== null && $paid !== null) {
                $remaining = (float)$totalBrl + (float)$interest - (float)$paid;
                $data['data']['remaining_balance'] = max(0, $remaining);
                
                // Atualiza status se necessário
                if ($data['data']['remaining_balance'] <= 0) {
                    $data['data']['status'] = 'paid';
                } elseif ((float)$paid > 0) {
                    // Só muda para partially_paid se não estiver overdue ou se preferirmos manter o estado
                    // Por segurança, se houver pagamento e ainda houver saldo, é partially_paid
                    // exceto se o admin quiser manter como overdue.
                    if (($row['status'] ?? null) !== 'overdue') {
                        $data['data']['status'] = 'partially_paid';
                    }
                }
            }
        }

        return $data;
    }
    /**
     * Calculate and apply interest to overdue contracts.
     */
    public function applyDailyInterest()
    {
        $today = date('Y-m-d H:i:s');
        
        // Find overdue contracts with remaining balance
        $overdueContracts = $this->db->table($this->table)
            ->select('contracts.*, users.daily_interest_rate')
            ->join('users', 'users.id = contracts.user_id')
            ->where('due_date <', $today)
            ->where('remaining_balance >', 0)
            ->get()
            ->getResultArray();

        foreach ($overdueContracts as $contract) {
            if ($contract['daily_interest_rate'] <= 0) continue;

            // Calculate interest for one day (or since last update)
            $dailyInterest = $contract['remaining_balance'] * ($contract['daily_interest_rate'] / 100);
            
            $newInterestAccumulated = $contract['interest_accumulated'] + $dailyInterest;
            $newRemainingBalance = $contract['remaining_balance'] + $dailyInterest;

            $this->update($contract['id'], [
                'interest_accumulated' => $newInterestAccumulated,
                'status'               => 'overdue'
            ]);

            // Grava no extrato antigo por compatibilidade
            $transactionModel = new \App\Models\TransactionModel();
            $transactionModel->save([
                'user_id'        => $contract['user_id'],
                'type'           => 'interest',
                'amount_brl'     => $dailyInterest,
                'amount_usdt'    => 0,
                'rate'           => 0,
                'status'         => 'completed',
                'delivery_type'  => 'D+0',
                'wallet_address' => 'Juros Diários - Contrato #' . $contract['id']
            ]);

            // Grava no novo extrato financeiro
            $financialModel = new \App\Models\FinancialStatementModel();
            $financialModel->insert([
                'user_id' => $contract['user_id'],
                'contract_id' => $contract['id'],
                'operation_type' => 'late_fee',
                'nature' => 'D', // Débito (aumenta a dívida)
                'amount' => $dailyInterest,
                'description' => 'Cobrança de Juros (Atraso) - Contrato #' . $contract['id'],
                'transaction_date' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Returns contracts with pending USDT to deliver, cross-referencing financial_statements.
     */
    public function getPendingDeliveries(?int $userId = null, string $orderBy = 'due_date'): array
    {
        $db = \Config\Database::connect();

        $sql = "
            SELECT
                c.*,
                u.login       AS user_name,
                u.usdt_wallet,
                COALESCE(fs_delivered.fs_total, 0)  AS fs_delivered_usdt,
                CASE
                    WHEN c.total_brl > 0
                    THEN LEAST(c.total_amount, (c.paid_amount / c.total_brl) * c.total_amount)
                    ELSE 0
                END AS usdt_entitled,
                GREATEST(0,
                    CASE
                        WHEN c.total_brl > 0
                        THEN (c.paid_amount / c.total_brl) * c.total_amount
                        ELSE 0
                    END - c.delivered_usdt
                ) AS pending_usdt,
                COALESCE(la_res.lot_reserved, 0) AS lot_reserved,
                GREATEST(0,
                    GREATEST(0,
                        CASE
                            WHEN c.total_brl > 0
                            THEN (c.paid_amount / c.total_brl) * c.total_amount
                            ELSE 0
                        END - c.delivered_usdt
                    ) - COALESCE(la_res.lot_reserved, 0)
                ) AS unlinked_usdt,
                LEAST(
                    GREATEST(0,
                        CASE
                            WHEN c.total_brl > 0
                            THEN (c.paid_amount / c.total_brl) * c.total_amount
                            ELSE 0
                        END - c.delivered_usdt
                    ),
                    COALESCE(la_res.lot_reserved, 0)
                ) AS sendable_usdt,
                ROUND(
                    ((c.comercial_brl / NULLIF(c.total_amount, 0)) - la_res.avg_lot_rate) *
                    LEAST(
                        GREATEST(0,
                            CASE
                                WHEN c.total_brl > 0
                                THEN (c.paid_amount / c.total_brl) * c.total_amount
                                ELSE 0
                            END - c.delivered_usdt
                        ),
                        la_res.lot_reserved
                    ),
                2) AS est_profit_brl
            FROM contracts c
            INNER JOIN users u ON u.id = c.user_id
            LEFT JOIN (
                SELECT contract_id, SUM(amount) AS fs_total
                FROM financial_statements
                WHERE operation_type = 'withdrawal' AND nature = 'D'
                GROUP BY contract_id
            ) fs_delivered ON fs_delivered.contract_id = c.id
            LEFT JOIN (
                SELECT la.contract_id,
                       SUM(la.usdt_amount) AS lot_reserved,
                       SUM(la.usdt_amount * ul.conversion_rate) / NULLIF(SUM(la.usdt_amount), 0) AS avg_lot_rate
                FROM lot_allocations la
                JOIN usdt_lots ul ON ul.id = la.lot_id
                WHERE la.status = 'reserved'
                GROUP BY la.contract_id
            ) la_res ON la_res.contract_id = c.id
            WHERE (c.delivery_blocked IS NULL OR c.delivery_blocked = 0)
              AND c.total_brl > 0
              AND c.paid_amount > 0
              AND c.delivered_usdt < (c.paid_amount / c.total_brl) * c.total_amount
        ";

        $bindings = [];
        if ($userId !== null) {
            $sql     .= ' AND c.user_id = ?';
            $bindings[] = $userId;
        }

        if ($orderBy === 'profit') {
            // Margem real (taxa cliente − custo médio dos lotes reservados);
            // NULLs (sem lote) ficam por último no DESC do MySQL
            $sql .= ' ORDER BY est_profit_brl DESC, COALESCE(c.fee_brl, 0) DESC, c.due_date ASC';
        } else {
            $sql .= ' ORDER BY c.due_date ASC';
        }

        return $db->query($sql, $bindings)->getResultArray();
    }

    public function getOpenDebtsByUser(int $userId): array
    {
        return $this->db->query("
            SELECT
                c.remaining_balance,
                c.total_brl,
                c.total_amount,
                c.delivered_usdt,
                c.status,
                UPPER(c.type) AS delivery_type,
                DATE(c.due_date) AS due_date_only,
                ROUND(GREATEST(0, c.total_amount - COALESCE(c.delivered_usdt, 0)), 2) AS usdt_owed
            FROM contracts c
            LEFT JOIN transactions t ON t.id = c.transaction_id
            WHERE c.user_id = ?
              AND (
                  c.status IN ('pending', 'partially_paid', 'overdue')
                  OR COALESCE(c.delivered_usdt, 0) < c.total_amount
              )
        ", [$userId])->getResultArray();
    }

    /**
     * Register a payment for a contract.
     */
    public function registerPayment($contractId, $amount)
    {
        $contract = $this->find($contractId);
        if (!$contract) return false;

        $newPaidAmount = $contract['paid_amount'] + $amount;
        
        return $this->update($contractId, [
            'paid_amount' => $newPaidAmount,
        ]);
    }
}
