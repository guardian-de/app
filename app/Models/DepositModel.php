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
        'ai_amount', 'ocr_status', 'ocr_raw_text', 'ocr_code', 'is_duplicate',
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
            'attachment'       => $deposit['proof_file'] ?? null,
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

    public static function extractAuthCodeFromText(?string $ocrText): ?string
    {
        if (empty($ocrText)) {
            return null;
        }

        // 1. Procura padrão de ID de transação PIX (EndToEndId): começa com 'E' seguido de 31 caracteres alfanuméricos
        if (preg_match('/\b(E[A-Za-z0-9]{31})\b/', $ocrText, $matches)) {
            return $matches[1];
        }

        // 2. Procura padrão de autenticação com hífen/pontos comum em bancos (ex: A1B2.C3D4... ou UUIDs)
        if (preg_match('/\b([A-Za-z0-9]{8}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{12})\b/', $ocrText, $matches)) {
            return $matches[1];
        }

        // 3. Procura por termos como "Autenticação", "Protocolo", "Transação", "Controle", "Código" seguido de caracteres
        $keywords = ['autenticacao', 'autenticação', 'protocolo', 'transacao', 'transação', 'controle', 'codigo', 'código', 'id'];
        foreach ($keywords as $kw) {
            if (preg_match('/' . preg_quote($kw, '/') . '\s*[:=-]?\s*([A-Za-z0-9.\/-]{8,40})/i', $ocrText, $matches)) {
                $code = trim($matches[1], " \t\n\r\0\x0B.:-");
                if (strlen($code) >= 8) {
                    return $code;
                }
            }
        }

        // 4. Qualquer string alfanumérica contínua longa que pareça um hash/id (comprimento entre 16 e 40)
        if (preg_match_all('/\b([A-Za-z0-9]{16,40})\b/', $ocrText, $matches)) {
            foreach ($matches[1] as $candidate) {
                // Se contiver letras e números (para evitar pegar um monte de zeros ou números de conta normais)
                if (preg_match('/[A-Za-z]/', $candidate) && preg_match('/\d/', $candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }
}
