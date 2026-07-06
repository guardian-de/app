<?php

namespace App\Models;

use CodeIgniter\Model;

class FinancialStatementModel extends Model
{
    protected $table            = 'financial_statements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 
        'admin_id',
        'contract_id', 
        'operation_type', 
        'nature', 
        'amount', 
        'description', 
        'attachment',
        'payment_method',
        'notes',
        'transaction_date',
        'fee_percent',
        'comercial_brl',
        'fee_brl'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $deletedField  = '';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function getUserStatement(int $userId, int $page = 1, int $perPage = 20, string $nature = '', string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $like   = '%' . $search . '%';

        // Linhas de USDT no ledger (entregas e recebimentos) ficam marcadas
        // com unit = USDT para o front não exibi-las como R$.
        // CONVERT(... USING utf8mb4) em todas as colunas de texto: as tabelas
        // ledger e deposits podem ter collations diferentes e a UNION falharia.
        $ledgerSql = "SELECT fs.id,
                CONVERT(fs.operation_type USING utf8mb4) AS operation_type,
                CONVERT(fs.nature USING utf8mb4) AS nature,
                fs.amount,
                CONVERT(fs.description USING utf8mb4) AS description,
                fs.contract_id, fs.transaction_date,
                CONVERT(CASE WHEN fs.operation_type = 'withdrawal' OR fs.description LIKE 'Depósito de USDT%'
                     THEN 'USDT' ELSE 'BRL' END USING utf8mb4) AS unit,
                CONVERT(NULL USING utf8mb4) AS rejection_reason,
                fs.fee_percent, fs.fee_brl, c.total_amount AS usdt_amount,
                CONVERT((
                    SELECT GROUP_CONCAT(DISTINCT ul.purchase_hash SEPARATOR ', ')
                    FROM lot_allocations la
                    JOIN usdt_lots ul ON ul.id = la.lot_id
                    WHERE la.contract_id = fs.contract_id AND la.status IN ('reserved','delivered')
                ) USING utf8mb4) AS purchase_hash,
                CONVERT(fs.notes USING utf8mb4) AS notes
            FROM financial_statements fs
            LEFT JOIN contracts c ON c.id = fs.contract_id
            WHERE fs.user_id = ?";
        $ledgerParams = [$userId];

        if ($nature !== '') {
            $ledgerSql .= " AND fs.nature = ?";
            $ledgerParams[] = $nature;
        }
        if ($search !== '') {
            $ledgerSql .= " AND fs.description LIKE ?";
            $ledgerParams[] = $like;
        }

        // Depósitos pendentes/rejeitados não são lançamentos contábeis (não afetam
        // o saldo), mas precisam aparecer no extrato. Aceitos já entram via ledger.
        // O filtro de natureza (C/D) exclui essas linhas informativas.
        $includeDeposits = ($nature === '');
        $sql    = $ledgerSql;
        $params = $ledgerParams;

        if ($includeDeposits) {
            $depositSql = "SELECT d.id,
                    CONVERT(CASE WHEN d.status = 'pending' THEN 'deposit_pending' ELSE 'deposit_rejected' END USING utf8mb4) AS operation_type,
                    CONVERT(NULL USING utf8mb4) AS nature,
                    d.amount,
                    CONVERT(d.notes USING utf8mb4) AS description,
                    NULL AS contract_id, d.created_at AS transaction_date,
                    CONVERT('BRL' USING utf8mb4) AS unit,
                    CONVERT(d.rejection_reason USING utf8mb4) AS rejection_reason,
                    NULL AS fee_percent, NULL AS fee_brl, NULL AS usdt_amount,
                    CONVERT(NULL USING utf8mb4) AS purchase_hash,
                    CONVERT(NULL USING utf8mb4) AS notes
                FROM deposits d
                WHERE d.user_id = ? AND d.status IN ('pending','rejected')";
            $depositParams = [$userId];

            if ($search !== '') {
                $depositSql .= " AND (d.notes LIKE ? OR d.rejection_reason LIKE ?)";
                $depositParams[] = $like;
                $depositParams[] = $like;
            }

            $sql    = "($ledgerSql) UNION ALL ($depositSql)";
            $params = array_merge($ledgerParams, $depositParams);
        }

        $runQuery = function (string $innerSql, array $innerParams) use ($perPage, $offset): array {
            $total = (int) $this->db->query(
                "SELECT COUNT(*) AS total FROM ($innerSql) t",
                $innerParams
            )->getRowArray()['total'];

            $rows = $this->db->query(
                "SELECT * FROM ($innerSql) t ORDER BY t.transaction_date DESC, t.id DESC LIMIT ? OFFSET ?",
                array_merge($innerParams, [$perPage + 1, $offset])
            )->getResultArray();

            return [$total, $rows];
        };

        try {
            [$total, $rows] = $runQuery($sql, $params);
        } catch (\Throwable $e) {
            // Tabela deposits pode não existir em deploys antigos — cai para o ledger puro
            log_message('debug', 'getUserStatement deposits guard: ' . $e->getMessage());
            [$total, $rows] = $runQuery($ledgerSql, $ledgerParams);
        }

        $hasMore = count($rows) > $perPage;
        if ($hasMore) {
            array_pop($rows);
        }

        return ['data' => $rows, 'has_more' => $hasMore, 'total' => $total];
    }

    public function getBalance(int $userId): float
    {
        $result = $this->db->query(
            "SELECT SUM(CASE WHEN nature = 'C' THEN amount ELSE -amount END) AS balance
             FROM financial_statements
             WHERE user_id = ?",
            [$userId]
        )->getRowArray();

        return (float)($result['balance'] ?? 0);
    }

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
