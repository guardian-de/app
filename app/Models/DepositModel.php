<?php

namespace App\Models;

use CodeIgniter\Model;

class DepositModel extends Model
{
    protected $table            = 'deposits';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'amount', 'proof_file', 'status', 'notes',
        'accepted_by', 'accepted_at',
        'reversed_by', 'reversed_at', 'reversal_reason',
        'rejected_by', 'rejected_at', 'rejection_reason',
        'ai_amount', 'ocr_status', 'ocr_raw_text',
        'amount_edited_reason', 'amount_edited_by', 'amount_edited_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Lança o crédito no extrato do cliente e distribui o valor entre os
     * contratos em aberto (FIFO). Usado tanto ao aceitar um depósito enviado
     * pelo cliente quanto ao lançar um depósito manual feito pelo admin.
     */
    public function applyAcceptedDeposit(array $deposit, int $actorId, string $description): void
    {
        $now = date('Y-m-d H:i:s');

        (new FinancialStatementModel())->insert([
            'user_id'          => $deposit['user_id'],
            'admin_id'         => $actorId,
            'operation_type'   => 'deposit',
            'nature'           => 'C',
            'amount'           => $deposit['amount'],
            'description'      => $description,
            'transaction_date' => $now,
        ]);

        $contractModel = new ContractModel();
        $openContracts = \Config\Database::connect()->table('contracts')
            ->where('user_id', $deposit['user_id'])
            ->whereIn('status', ['pending', 'partially_paid', 'overdue'])
            ->orderBy('created_at', 'ASC')
            ->get()->getResultArray();

        $toDistribute = (float) $deposit['amount'];
        foreach ($openContracts as $contract) {
            if ($toDistribute <= 0) { break; }

            $remaining = (float) $contract['remaining_balance'];
            if ($remaining <= 0) { continue; }

            $payment = min($toDistribute, $remaining);
            $contractModel->registerPayment($contract['id'], $payment);

            $toDistribute -= $payment;
        }
    }
}
