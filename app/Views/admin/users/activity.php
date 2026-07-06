<?= $this->extend('layouts/admin_layout') ?>
<?= $this->section('content') ?>

<?php
$actionLabels = [
    'contract.usdt_delivered'      => 'Envio de USDT',
    'contract.payment_registered'  => 'Registro de Pagamento',
    'contract.delivery_blocked'    => 'Bloqueio de Entrega',
    'contract.delivery_unblocked'  => 'Desbloqueio de Entrega',
    'transaction.approved'         => 'Aprovação de Transação',
    'transaction.cancelled'        => 'Cancelamento de Transação',
    'lot.created'                  => 'Criação de Lote',
    'lot.allocated'                => 'Alocação de Lote',
    'lot.deallocated'              => 'Desalocação de Lote',
];

$entityTypeLabels = [
    'contract'    => 'Operação',
    'transaction' => 'Transação',
    'lot'         => 'Lote',
    'user'        => 'Usuário',
];
$actionColors = [
    'contract.usdt_delivered'      => '#34d399',
    'contract.payment_registered'  => '#60a5fa',
    'contract.delivery_blocked'    => '#f87171',
    'contract.delivery_unblocked'  => '#4ade80',
    'transaction.approved'         => '#818cf8',
    'transaction.cancelled'        => '#f87171',
    'lot.created'                  => '#fbbf24',
    'lot.allocated'                => '#fbbf24',
];
$operationLabels = [
    'partial_amortization' => 'Amortização Parcial',
    'full_settlement'      => 'Quitação Total',
    'withdrawal'           => 'Envio USDT',
    'margin_lock'          => 'Bloqueio de Margem',
    'deposit'              => 'Depósito',
    'adjustment_add'       => 'Ajuste (Crédito)',
    'adjustment_subtract'  => 'Ajuste (Débito)',
    'limit_release'        => 'Liberação de Limite',
    'late_fee'             => 'Juros de Atraso',
];
$payloadKeyLabels = [
    'supplier'        => 'fornecedor',
    'quick_buy'       => 'compra rápida',
    'total_brl'       => 'total BRL',
    'contract_id'     => 'operação ID',
    'usdt_amount'     => 'qtd. USDT',
    'conversion_rate' => 'taxa de conversão',
    'entity_id'       => 'entidade ID',
    'entity_type'     => 'tipo entidade',
    'allocation_id'   => 'alocação ID',
    'notes'           => 'observações',
    'amount_brl'      => 'valor BRL',
    'operation_type'  => 'tipo operação',
    'payment_method'  => 'método pagamento',
    'lot_id'          => 'lote ID',
    'transaction_id'  => 'transação ID',
    'amount'          => 'valor',
    'rate'            => 'taxa',
];
$payloadValueLabels = [
    'entity_type' => [
        'contract'    => 'operação',
        'transaction' => 'transação',
        'lot'         => 'lote',
        'user'        => 'usuário',
    ],
    'operation_type' => [
        'partial_amortization' => 'amortização parcial',
        'full_settlement'      => 'quitação total',
        'withdrawal'           => 'envio USDT',
        'margin_lock'          => 'bloqueio de margem',
        'deposit'              => 'depósito',
        'adjustment_add'       => 'ajuste (crédito)',
        'adjustment_subtract'  => 'ajuste (débito)',
        'limit_release'        => 'liberação de limite',
        'late_fee'             => 'juros de atraso',
    ],
];

?>

<!-- Cabeçalho -->
<div class="header" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
    <a href="<?= url_to('admin_users') ?>"
       style="display:inline-flex;align-items:center;gap:6px;color:#64748b;font-size:13px;text-decoration:none;padding:6px 12px;border:1px solid rgba(255,255,255,0.08);border-radius:8px;background:rgba(255,255,255,0.03);"
       onmouseover="this.style.color='white'" onmouseout="this.style.color='#64748b'">
        ← Voltar
    </a>
    <div>
        <h1 style="font-size:22px;color:white;margin:0;">Histórico do Operador</h1>
        <p style="font-size:13px;color:#64748b;margin:2px 0 0;">
            <strong style="color:#818cf8;"><?= esc($operator['login']) ?></strong>
            <span style="margin-left:6px;font-size:11px;padding:2px 8px;border-radius:4px;background:rgba(129,140,248,0.1);color:#818cf8;text-transform:uppercase;"><?= esc($operator['role']) ?></span>
        </p>
    </div>
</div>

<!-- Filtros + per_page -->
<form method="GET" action="<?= url_to('admin_users_activity', $operator['id']) ?>"
      style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;margin-bottom:20px;">
    <input type="hidden" name="page" value="1">

    <div>
        <label style="display:block;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Data início</label>
        <input type="date" name="start_date" value="<?= esc($start_date) ?>"
               style="background:#0f172a;border:1px solid #334155;border-radius:8px;color:white;padding:8px 12px;font-size:13px;outline:none;">
    </div>
    <div>
        <label style="display:block;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Data fim</label>
        <input type="date" name="end_date" value="<?= esc($end_date) ?>"
               style="background:#0f172a;border:1px solid #334155;border-radius:8px;color:white;padding:8px 12px;font-size:13px;outline:none;">
    </div>
    <div>
        <label style="display:block;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Registros por página</label>
        <select name="per_page"
                style="background:#0f172a;border:1px solid #334155;border-radius:8px;color:white;padding:8px 12px;font-size:13px;outline:none;cursor:pointer;">
            <?php foreach ([10, 20, 50, 100] as $opt): ?>
                <option value="<?= $opt ?>" <?= $per_page == $opt ? 'selected' : '' ?>><?= $opt ?> por página</option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit"
            style="padding:8px 20px;background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.3);border-radius:8px;color:#818cf8;font-size:13px;font-weight:600;cursor:pointer;">
        Filtrar
    </button>
    <?php if ($start_date || $end_date): ?>
        <a href="<?= url_to('admin_users_activity', $operator['id']) ?>?per_page=<?= $per_page ?>"
           style="padding:8px 16px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:8px;color:#64748b;font-size:13px;text-decoration:none;">
            Limpar datas
        </a>
    <?php endif; ?>
</form>

<!-- Resumo paginação -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
    <span style="font-size:13px;color:#475569;">
        <?php
        $from = $total > 0 ? (($page - 1) * $per_page + 1) : 0;
        $to   = min($page * $per_page, $total);
        ?>
        Exibindo <strong style="color:#94a3b8;"><?= $from ?>–<?= $to ?></strong> de <strong style="color:#94a3b8;"><?= $total ?></strong> registros
    </span>
    <span style="font-size:13px;color:#475569;">Página <?= $page ?> de <?= $total_pages ?></span>
</div>

<!-- Data-table -->
<?php if (empty($timeline)): ?>
    <div style="padding:48px;text-align:center;color:#475569;font-size:14px;background:rgba(255,255,255,0.02);border-radius:12px;border:1px solid rgba(255,255,255,0.05);">
        Nenhum registro encontrado para o período selecionado.
    </div>
<?php else: ?>
    <div style="overflow-x:auto;border-radius:12px;border:1px solid rgba(255,255,255,0.07);">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:rgba(255,255,255,0.04);border-bottom:1px solid rgba(255,255,255,0.07);">
                    <th style="padding:12px 16px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;white-space:nowrap;">Data / Hora</th>
                    <th style="padding:12px 16px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;white-space:nowrap;">Fonte</th>
                    <th style="padding:12px 16px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Ação</th>
                    <th style="padding:12px 16px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Descrição / Detalhes</th>
                    <th style="padding:12px 16px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;white-space:nowrap;">Referência</th>
                    <th style="padding:12px 16px;text-align:right;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;white-space:nowrap;">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($timeline as $item):
                    $isActivity = $item['source'] === 'activity';

                    if ($isActivity) {
                        $label = $actionLabels[$item['label']] ?? ucwords(str_replace(['.', '_'], ' ', $item['label']));
                        $color = $actionColors[$item['label']] ?? '#94a3b8';
                    } else {
                        $label = $operationLabels[$item['label']] ?? ucwords(str_replace('_', ' ', $item['label']));
                        $color = ($item['nature'] === 'C') ? '#34d399' : '#f87171';
                    }
                ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.04);transition:background 0.12s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.03)'"
                    onmouseout="this.style.background=''">

                    <!-- Data -->
                    <td style="padding:12px 16px;white-space:nowrap;color:#64748b;font-family:monospace;font-size:12px;">
                        <?= date('d/m/Y', strtotime($item['date'])) ?><br>
                        <span style="color:#334155;"><?= date('H:i:s', strtotime($item['date'])) ?></span>
                    </td>

                    <!-- Fonte -->
                    <td style="padding:12px 16px;white-space:nowrap;">
                        <?php if ($isActivity): ?>
                            <span style="font-size:11px;padding:2px 8px;border-radius:4px;background:rgba(129,140,248,0.1);color:#818cf8;font-weight:600;">Sistema</span>
                        <?php else: ?>
                            <span style="font-size:11px;padding:2px 8px;border-radius:4px;background:rgba(52,211,153,0.08);color:#34d399;font-weight:600;">Financeiro</span>
                        <?php endif; ?>
                    </td>

                    <!-- Ação -->
                    <td style="padding:12px 16px;white-space:nowrap;">
                        <span style="font-weight:700;color:<?= $color ?>;"><?= esc($label) ?></span>
                    </td>

                    <!-- Descrição / Detalhes -->
                    <td style="padding:12px 16px;max-width:320px;">
                        <?php if ($isActivity): ?>
                            <?php if (!empty($item['payload'])): ?>
                                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                    <?php foreach ((array)$item['payload'] as $k => $v): ?>
                                        <?php if ($v === null || $v === '' || $v === false) continue; ?>
                                        <?php
                                            $displayKey = $payloadKeyLabels[$k] ?? $k;
                                            if (is_bool($v)) {
                                                $displayVal = $v ? 'sim' : 'não';
                                            } else {
                                                $displayVal = esc((string)($payloadValueLabels[$k][$v] ?? $v));
                                            }
                                        ?>
                                        <span style="font-size:11px;color:#64748b;background:rgba(255,255,255,0.04);padding:2px 8px;border-radius:4px;white-space:nowrap;">
                                            <?= esc($displayKey) ?>: <strong style="color:#94a3b8;"><?= $displayVal ?></strong>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span style="color:#334155;font-size:12px;">—</span>
                            <?php endif; ?>
                            <?php if ($item['ip_address']): ?>
                                <div style="font-size:10px;color:#334155;margin-top:4px;">IP: <?= esc($item['ip_address']) ?></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($item['description']): ?>
                                <div style="color:#94a3b8;font-size:12px;"><?= esc($item['description']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($item['client_name'])): ?>
                                <div style="font-size:11px;color:#64748b;margin-top:3px;">Cliente: <strong style="color:#94a3b8;"><?= esc($item['client_name']) ?></strong></div>
                            <?php endif; ?>
                            <?php if ($item['payment_method']): ?>
                                <span style="font-size:10px;padding:1px 6px;border-radius:4px;background:rgba(99,102,241,0.1);color:#818cf8;margin-top:3px;display:inline-block;"><?= esc($item['payment_method']) ?></span>
                            <?php endif; ?>
                            <?php if ($item['notes']): ?>
                                <div style="font-size:11px;color:#475569;margin-top:3px;font-style:italic;">"<?= esc($item['notes']) ?>"</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>

                    <!-- Referência -->
                    <td style="padding:12px 16px;white-space:nowrap;">
                        <?php if ($isActivity && $item['entity_id']): ?>
                            <?php
                            $refUrl   = null;
                            $refLabel = '';
                            $entityName = $entityTypeLabels[$item['entity_type']] ?? ucfirst($item['entity_type']);
                            if ($item['entity_type'] === 'contract') {
                                $refUrl = url_to('admin_contracts_show', $item['entity_id']);
                            } elseif ($item['entity_type'] === 'transaction') {
                                $refUrl = url_to('admin_transactions_show', $item['entity_id']);
                            } elseif ($item['entity_type'] === 'lot') {
                                $refUrl = url_to('admin_lots_show', $item['entity_id']);
                            }
                            $refLabel = $entityName . ' #' . $item['entity_id'];
                            ?>
                            <?php if ($refUrl): ?>
                                <a href="<?= $refUrl ?>" target="_blank" rel="noopener"
                                   style="font-size:12px;color:#60a5fa;text-decoration:none;border-bottom:1px dashed #334155;">
                                    <?= esc($refLabel) ?>
                                </a>
                            <?php else: ?>
                                <span style="font-size:12px;color:#64748b;"><?= esc($refLabel) ?></span>
                            <?php endif; ?>
                        <?php elseif (!$isActivity && $item['contract_id']): ?>
                            <a href="<?= url_to('admin_contracts_show', $item['contract_id']) ?>" target="_blank" rel="noopener"
                               style="font-size:12px;color:#60a5fa;text-decoration:none;border-bottom:1px dashed #334155;">
                                Operação #<?= $item['contract_id'] ?>
                            </a>
                        <?php else: ?>
                            <span style="color:#334155;font-size:12px;">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Valor -->
                    <td style="padding:12px 16px;text-align:right;white-space:nowrap;font-family:monospace;">
                        <?php if (!$isActivity && $item['amount'] !== null): ?>
                            <span style="font-weight:700;color:<?= $color ?>;">
                                <?= $item['nature'] === 'C' ? '+' : '−' ?> <?= number_format((float)$item['amount'], 2, ',', '.') ?>
                            </span>
                        <?php else: ?>
                            <span style="color:#334155;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Controles de paginação -->
    <?php if ($total_pages > 1): ?>
        <?php
        $baseParams = array_filter([
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'per_page'   => $per_page != 20 ? $per_page : null,
        ]);
        ?>
        <div style="display:flex;justify-content:center;align-items:center;gap:6px;margin-top:20px;flex-wrap:wrap;">
            <!-- Anterior -->
            <?php if ($page > 1): ?>
                <a href="<?= url_to('admin_users_activity', $operator['id']) ?>?<?= http_build_query(array_merge($baseParams, ['page' => $page - 1])) ?>"
                   style="padding:7px 14px;border-radius:8px;border:1px solid rgba(255,255,255,0.1);color:#94a3b8;font-size:13px;text-decoration:none;background:rgba(255,255,255,0.03);"
                   onmouseover="this.style.background='rgba(255,255,255,0.07)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
                    ←
                </a>
            <?php endif; ?>

            <!-- Páginas -->
            <?php
            $window = 2;
            $start  = max(1, $page - $window);
            $end    = min($total_pages, $page + $window);
            if ($start > 1): ?>
                <a href="<?= url_to('admin_users_activity', $operator['id']) ?>?<?= http_build_query(array_merge($baseParams, ['page' => 1])) ?>"
                   style="padding:7px 12px;border-radius:8px;border:1px solid rgba(255,255,255,0.1);color:#94a3b8;font-size:13px;text-decoration:none;background:rgba(255,255,255,0.03);">1</a>
                <?php if ($start > 2): ?><span style="color:#334155;padding:0 4px;">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($p = $start; $p <= $end; $p++): ?>
                <?php if ($p === $page): ?>
                    <span style="padding:7px 12px;border-radius:8px;border:1px solid rgba(99,102,241,0.4);color:#818cf8;font-size:13px;font-weight:700;background:rgba(99,102,241,0.12);"><?= $p ?></span>
                <?php else: ?>
                    <a href="<?= url_to('admin_users_activity', $operator['id']) ?>?<?= http_build_query(array_merge($baseParams, ['page' => $p])) ?>"
                       style="padding:7px 12px;border-radius:8px;border:1px solid rgba(255,255,255,0.1);color:#94a3b8;font-size:13px;text-decoration:none;background:rgba(255,255,255,0.03);"
                       onmouseover="this.style.background='rgba(255,255,255,0.07)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
                        <?= $p ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $total_pages): ?>
                <?php if ($end < $total_pages - 1): ?><span style="color:#334155;padding:0 4px;">…</span><?php endif; ?>
                <a href="<?= url_to('admin_users_activity', $operator['id']) ?>?<?= http_build_query(array_merge($baseParams, ['page' => $total_pages])) ?>"
                   style="padding:7px 12px;border-radius:8px;border:1px solid rgba(255,255,255,0.1);color:#94a3b8;font-size:13px;text-decoration:none;background:rgba(255,255,255,0.03);"><?= $total_pages ?></a>
            <?php endif; ?>

            <!-- Próxima -->
            <?php if ($page < $total_pages): ?>
                <a href="<?= url_to('admin_users_activity', $operator['id']) ?>?<?= http_build_query(array_merge($baseParams, ['page' => $page + 1])) ?>"
                   style="padding:7px 14px;border-radius:8px;border:1px solid rgba(255,255,255,0.1);color:#94a3b8;font-size:13px;text-decoration:none;background:rgba(255,255,255,0.03);"
                   onmouseover="this.style.background='rgba(255,255,255,0.07)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
                    →
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?= $this->endSection() ?>
