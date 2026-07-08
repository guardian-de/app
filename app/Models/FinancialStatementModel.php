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

    public function getUserStatement(int $userId, int $page = 1, int $perPage = 20, string $nature = '', string $search = '', array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $like   = '%' . $search . '%';

        $startDate = $filters['start_date'] ?? '';
        $endDate   = $filters['end_date'] ?? '';
        $type      = $filters['type'] ?? '';
        $status    = $filters['status'] ?? '';

        $includeLedger = true;
        $includeDeposits = ($nature === '');

        if ($status === 'completed') {
            $includeDeposits = false;
        } elseif ($status === 'pending' || $status === 'rejected') {
            $includeLedger = false;
            $includeDeposits = true;
        }

        // --- Build Ledger SQL ---
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
                ROUND(c.comercial_brl / NULLIF(c.total_amount, 0), 4) AS spot_rate,
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
        if ($startDate !== '') {
            $ledgerSql .= " AND fs.transaction_date >= ?";
            $ledgerParams[] = $startDate . ' 00:00:00';
        }
        if ($endDate !== '') {
            $ledgerSql .= " AND fs.transaction_date <= ?";
            $ledgerParams[] = $endDate . ' 23:59:59';
        }
        if ($type !== '') {
            if ($type === 'adjustment') {
                $ledgerSql .= " AND fs.operation_type IN ('adjustment_add', 'adjustment_subtract')";
            } else {
                $ledgerSql .= " AND fs.operation_type = ?";
                $ledgerParams[] = $type;
            }
        }

        // --- Build Deposits SQL ---
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
                    NULL AS spot_rate,
                    CONVERT(NULL USING utf8mb4) AS purchase_hash,
                    CONVERT(NULL USING utf8mb4) AS notes
                FROM deposits d
                WHERE d.user_id = ?";
            $depositParams = [$userId];

            if ($status === 'pending') {
                $depositSql .= " AND d.status = 'pending'";
            } elseif ($status === 'rejected') {
                $depositSql .= " AND d.status = 'rejected'";
            } else {
                $depositSql .= " AND d.status IN ('pending','rejected')";
            }

            if ($search !== '') {
                $depositSql .= " AND (d.notes LIKE ? OR d.rejection_reason LIKE ?)";
                $depositParams[] = $like;
                $depositParams[] = $like;
            }
            if ($startDate !== '') {
                $depositSql .= " AND d.created_at >= ?";
                $depositParams[] = $startDate . ' 00:00:00';
            }
            if ($endDate !== '') {
                $depositSql .= " AND d.created_at <= ?";
                $depositParams[] = $endDate . ' 23:59:59';
            }
            if ($type !== '') {
                if ($type !== 'deposit') {
                    $depositSql .= " AND 1 = 0";
                }
            }
        }

        // --- Union SQL Assemble ---
        $sql = "";
        $params = [];

        if ($includeLedger && $includeDeposits) {
            $sql = "($ledgerSql) UNION ALL ($depositSql)";
            $params = array_merge($ledgerParams, $depositParams);
        } elseif ($includeLedger) {
            $sql = $ledgerSql;
            $params = $ledgerParams;
        } elseif ($includeDeposits) {
            $sql = $depositSql;
            $params = $depositParams;
        } else {
            return ['total' => 0, 'data' => [], 'has_more' => false];
        }

        $runQuery = function (string $innerSql, array $innerParams) use ($perPage, $offset): array {
            $total = (int) $this->db->query(
                "SELECT COUNT(*) AS total FROM ($innerSql) t",
                $innerParams
            )->getRowArray()['total'];

            if ($perPage === -1) {
                $rows = $this->db->query(
                    "SELECT * FROM ($innerSql) t ORDER BY t.transaction_date DESC, t.id DESC",
                    $innerParams
                )->getResultArray();
            } else {
                $rows = $this->db->query(
                    "SELECT * FROM ($innerSql) t ORDER BY t.transaction_date DESC, t.id DESC LIMIT ? OFFSET ?",
                    array_merge($innerParams, [$perPage + 1, $offset])
                )->getResultArray();
            }

            return [$total, $rows];
        };

        try {
            [$total, $rows] = $runQuery($sql, $params);
        } catch (\Throwable $e) {
            log_message('debug', 'getUserStatement deposits guard: ' . $e->getMessage());
            [$total, $rows] = $runQuery($ledgerSql, $ledgerParams);
        }

        if ($perPage === -1) {
            $hasMore = false;
        } else {
            $hasMore = count($rows) > $perPage;
            if ($hasMore) {
                array_pop($rows);
            }
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
