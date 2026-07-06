<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
    <h1 style="font-size:24px;font-weight:700;">Conciliação</h1>
</div>

<!-- Summary cards -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:28px;">
    <div class="card" style="padding:20px;text-align:center;">
        <p style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Registros</p>
        <p style="font-size:26px;font-weight:800;color:#60a5fa;"><?= number_format($total, 0, ',', '.') ?></p>
    </div>
    <div class="card" style="padding:20px;text-align:center;">
        <p style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">USDT Alocado</p>
        <p style="font-size:26px;font-weight:800;color:#34d399;"><?= number_format($summary->total_usdt, 2, '.', ',') ?></p>
        <p style="font-size:11px;color:#64748b;">USDT</p>
    </div>
    <div class="card" style="padding:20px;text-align:center;">
        <p style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Lucro (entregas)</p>
        <p style="font-size:26px;font-weight:800;color:#a78bfa;">R$ <?= number_format($summary->total_profit, 2, ',', '.') ?></p>
    </div>
</div>

<!-- Filters -->
<div class="card" style="padding:20px;margin-bottom:24px;">
    <form method="GET" action="<?= url_to('admin_conciliation') ?>" style="display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end;">

        <div style="display:flex;flex-direction:column;gap:6px;">
            <label style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Data início</label>
            <input type="date" name="start_date" value="<?= esc($filter_start) ?>"
                style="padding:8px 12px;background:#0f172a;border:1px solid #334155;border-radius:10px;color:white;font-size:13px;outline:none;">
        </div>

        <div style="display:flex;flex-direction:column;gap:6px;">
            <label style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Data fim</label>
            <input type="date" name="end_date" value="<?= esc($filter_end) ?>"
                style="padding:8px 12px;background:#0f172a;border:1px solid #334155;border-radius:10px;color:white;font-size:13px;outline:none;">
        </div>

        <div style="display:flex;flex-direction:column;gap:6px;">
            <label style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Fornecedor</label>
            <select name="supplier"
                style="padding:8px 12px;background:#0f172a;border:1px solid #334155;border-radius:10px;color:white;font-size:13px;outline:none;min-width:160px;">
                <option value="">Todos</option>
                <?php foreach ($suppliers as $s): ?>
                    <option value="<?= esc($s['name']) ?>" <?= $filter_supplier === $s['name'] ? 'selected' : '' ?>>
                        <?= esc($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display:flex;flex-direction:column;gap:6px;">
            <label style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Cliente</label>
            <input type="text" name="client" value="<?= esc($filter_client) ?>" placeholder="Buscar por nome..."
                style="padding:8px 12px;background:#0f172a;border:1px solid #334155;border-radius:10px;color:white;font-size:13px;outline:none;min-width:180px;">
        </div>

        <div style="display:flex;flex-direction:column;gap:6px;">
            <label style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Status</label>
            <select name="status"
                style="padding:8px 12px;background:#0f172a;border:1px solid #334155;border-radius:10px;color:white;font-size:13px;outline:none;">
                <option value="all"       <?= $filter_status === 'all'       ? 'selected' : '' ?>>Todos</option>
                <option value="reserved"  <?= $filter_status === 'reserved'  ? 'selected' : '' ?>>Reservado</option>
                <option value="delivered" <?= $filter_status === 'delivered' ? 'selected' : '' ?>>Entregue</option>
            </select>
        </div>

        <div style="display:flex;flex-direction:column;gap:6px;">
            <label style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Por página</label>
            <select name="per_page"
                style="padding:8px 12px;background:#0f172a;border:1px solid #334155;border-radius:10px;color:white;font-size:13px;outline:none;">
                <?php foreach ([20, 50, 100] as $n): ?>
                    <option value="<?= $n ?>" <?= $per_page == $n ? 'selected' : '' ?>><?= $n ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary" style="padding:8px 20px;font-size:13px;">Filtrar</button>

        <?php if ($filter_start || $filter_end || $filter_supplier || $filter_client || $filter_status !== 'all'): ?>
            <a href="<?= url_to('admin_conciliation') ?>" class="btn" style="padding:8px 16px;font-size:13px;background:rgba(255,255,255,0.05);color:#94a3b8;">Limpar</a>
        <?php endif; ?>

    </form>
</div>

<!-- Table -->
<div class="card" style="padding:0;overflow:hidden;">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="color:#94a3b8;text-transform:uppercase;font-size:10px;letter-spacing:0.05em;border-bottom:1px solid rgba(255,255,255,0.06);background:rgba(255,255,255,0.02);">
                    <th style="padding:12px 12px;text-align:left;white-space:nowrap;">Data</th>
                    <th style="padding:12px 12px;text-align:left;white-space:nowrap;">Cliente</th>
                    <th style="padding:12px 12px;text-align:left;white-space:nowrap;">Ref.</th>
                    <th style="padding:12px 16px;text-align:left;white-space:nowrap;min-width:460px;">Análise<br><span style="font-weight:400;color:#64748b;">Trava · Fluxo · BRL</span></th>
                    <th style="padding:12px 12px;text-align:right;white-space:nowrap;min-width:110px;">USDT</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="5" style="padding:50px;text-align:center;color:#64748b;">
                        Nenhum registro encontrado para os filtros aplicados.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $r):
                    $margin      = $r['margin_per_usdt'] !== null ? (float)$r['margin_per_usdt'] : null;
                    $marginColor = $margin === null ? '#64748b' : ($margin >= 0 ? '#4ade80' : '#f87171');
                    $colorMuted  = '#94a3b8';
                    $entityRoute = $r['contract_id']
                        ? url_to('admin_contracts_show', $r['contract_id'])
                        : url_to('admin_transactions_show', $r['transaction_id']);
                    $entityRef   = $r['contract_id']
                        ? 'Operação #' . $r['contract_id']
                        : 'Transação #' . $r['transaction_id'];
                    $deliveryColors = ['D+0' => '#34d399', 'D+1' => '#fbbf24', 'D+2' => '#f87171'];
                    $clientDtRaw   = $r['client_delivery_type']   ?? null;
                    $supplierDtRaw = $r['supplier_delivery_type'] ?? null;
                    $clientDt   = is_string($clientDtRaw)   ? $clientDtRaw   : null;
                    $supplierDt = is_string($supplierDtRaw) ? $supplierDtRaw : null;
                    $clientDtColor   = $deliveryColors[$clientDt]   ?? $colorMuted;
                    $supplierDtColor = $deliveryColors[$supplierDt] ?? $colorMuted;
                ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.04);"
                    onmouseover="this.style.background='rgba(255,255,255,0.025)'"
                    onmouseout="this.style.background=''">

                    <td style="padding:12px 12px;color:#64748b;white-space:nowrap;font-size:12px;">
                        <?= date('d/m/Y', strtotime($r['created_at'])) ?><br>
                        <span style="font-size:11px;color:#475569;"><?= date('H:i', strtotime($r['created_at'])) ?></span>
                    </td>

                    <td style="padding:12px 12px;color:white;font-weight:500;white-space:nowrap;"><?= esc($r['client_name'] ?? '—') ?></td>

                    <td style="padding:12px 12px;">
                        <a href="<?= $entityRoute ?>" aria-label="<?= esc($entityRef) ?>" style="color:#818cf8;text-decoration:none;font-size:12px;white-space:nowrap;"><?= $entityRef ?></a>
                    </td>

                    <!-- Bloco Análise -->
                    <td style="padding:10px 16px;">
                        <?php
                            $gridStyle = 'display:grid;grid-template-columns:52px 88px 40px 1fr 170px;align-items:center;gap:0 10px;';
                        ?>

                        <!-- Linha cliente -->
                        <div style="<?= $gridStyle ?>margin-bottom:5px;">
                            <span style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.04em;">Cliente</span>
                            <span style="text-align:right;color:#60a5fa;font-weight:700;font-variant-numeric:tabular-nums;font-size:13px;">
                                <?= $r['client_rate'] !== null ? number_format((float)$r['client_rate'], 4, ',', '.') : '—' ?>
                            </span>
                            <span>
                                <?php if ($clientDt): ?>
                                    <span style="font-size:10px;padding:2px 7px;border-radius:20px;font-weight:700;background:<?= $clientDtColor ?>22;color:<?= $clientDtColor ?>;"><?= $clientDt ?></span>
                                <?php endif; ?>
                            </span>
                            <span></span>
                            <span style="text-align:right;color:#93c5fd;font-variant-numeric:tabular-nums;font-size:12px;white-space:nowrap;">
                                <?= $r['valor_cliente_brl'] !== null ? 'R$ ' . number_format((float)$r['valor_cliente_brl'], 2, ',', '.') : '—' ?>
                            </span>
                        </div>

                        <!-- Linha fornecedor -->
                        <div style="<?= $gridStyle ?>margin-bottom:7px;">
                            <span style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.04em;">Forn.</span>
                            <span style="text-align:right;color:#f87171;font-weight:700;font-variant-numeric:tabular-nums;font-size:13px;">
                                <?= number_format((float)$r['supplier_rate'], 4, ',', '.') ?>
                            </span>
                            <span>
                                <?php if ($supplierDt): ?>
                                    <span style="font-size:10px;padding:2px 7px;border-radius:20px;font-weight:700;background:<?= $supplierDtColor ?>22;color:<?= $supplierDtColor ?>;"><?= $supplierDt ?></span>
                                <?php endif; ?>
                            </span>
                            <span style="font-size:11px;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= is_string($r['supplier']) ? $r['supplier'] : '' ?></span>
                            <span style="text-align:right;color:#fca5a5;font-variant-numeric:tabular-nums;font-size:12px;white-space:nowrap;">
                                R$ <?= number_format((float)$r['valor_fornecedor_brl'], 2, ',', '.') ?>
                            </span>
                        </div>

                        <!-- Linha margem + lucro -->
                        <div style="border-top:1px solid rgba(255,255,255,0.05);padding-top:6px;display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:11px;font-weight:700;color:<?= $marginColor ?>;font-variant-numeric:tabular-nums;">
                                Margem
                                <?php if ($margin !== null): ?>
                                    <?= ($margin >= 0 ? '+' : '') . number_format($margin, 4, ',', '.') ?> R$/USDT
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </span>
                            <span style="font-size:12px;font-variant-numeric:tabular-nums;white-space:nowrap;">
                                <?php if ($r['status'] === 'delivered' && $r['profit_brl'] !== null): ?>
                                    <span style="color:<?= (float)$r['profit_brl'] >= 0 ? '#4ade80' : '#f87171' ?>;">
                                        Lucro&nbsp;R$ <?= number_format((float)$r['profit_brl'], 2, ',', '.') ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color:#475569;font-size:11px;">lucro pendente</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </td>

                    <td style="padding:12px 12px;text-align:right;color:#e2e8f0;font-variant-numeric:tabular-nums;white-space:nowrap;font-weight:600;">
                        <?= number_format((float)$r['usdt_amount'], 2, '.', ',') ?>
                    </td>

                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <?php if ($total > 0): ?>
    <div style="padding:12px 20px;border-top:1px solid rgba(255,255,255,0.05);">
        <p style="font-size:12px;color:#64748b;">
            <?= number_format(($page - 1) * $per_page + 1, 0, ',', '.') ?>–<?= number_format(min($page * $per_page, $total), 0, ',', '.') ?>
            de <?= number_format($total, 0, ',', '.') ?> registros
        </p>
    </div>
    <?php endif; ?>
</div>

<?php
    $queryBase = array_filter([
        'start_date' => $filter_start,
        'end_date'   => $filter_end,
        'supplier'   => $filter_supplier,
        'client'     => $filter_client,
        'status'     => $filter_status !== 'all' ? $filter_status : '',
        'per_page'   => $per_page != 20 ? $per_page : '',
    ]);
    $buildUrl = function(int $p) use ($queryBase): string {
        $params = array_filter(array_merge($queryBase, ['page' => $p > 1 ? $p : '']));
        $qs = http_build_query($params);
        return url_to('admin_conciliation') . ($qs ? '?' . $qs : '');
    };
    $winStart = max(1, $page - 2);
    $winEnd   = min($total_pages, $page + 2);
?>
<div style="margin-top:20px;display:flex;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;">

    <?php if ($page > 1): ?>
        <a href="<?= $buildUrl($page - 1) ?>" class="page-btn">&#8592; Anterior</a>
    <?php else: ?>
        <span class="page-btn page-disabled">&#8592; Anterior</span>
    <?php endif; ?>

    <?php if ($winStart > 1): ?>
        <a href="<?= $buildUrl(1) ?>" class="page-btn">1</a>
        <?php if ($winStart > 2): ?><span class="page-ellipsis">&hellip;</span><?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $winStart; $i <= $winEnd; $i++): ?>
        <?php if ($i === $page): ?>
            <span class="page-btn page-active"><?= $i ?></span>
        <?php else: ?>
            <a href="<?= $buildUrl($i) ?>" class="page-btn" aria-label="Página <?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($winEnd < $total_pages): ?>
        <?php if ($winEnd < $total_pages - 1): ?><span class="page-ellipsis">&hellip;</span><?php endif; ?>
        <a href="<?= $buildUrl($total_pages) ?>" class="page-btn" aria-label="Página <?= $total_pages ?>"><?= $total_pages ?></a>
    <?php endif; ?>

    <?php if ($page < $total_pages): ?>
        <a href="<?= $buildUrl($page + 1) ?>" class="page-btn">Próximo &#8594;</a>
    <?php else: ?>
        <span class="page-btn page-disabled">Próximo &#8594;</span>
    <?php endif; ?>

</div>

<style>
    .page-btn {
        display: inline-block;
        padding: 8px 14px;
        background: rgba(255,255,255,0.05);
        color: #94a3b8;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        border: 1px solid rgba(255,255,255,0.1);
        transition: all 0.2s;
        user-select: none;
    }
    a.page-btn:hover { background: rgba(255,255,255,0.1); color: white; }
    .page-btn.page-active { background: #6366f1; color: white; border-color: #6366f1; font-weight: 600; }
    .page-btn.page-disabled { opacity: 0.35; cursor: default; }
    .page-ellipsis { color: #475569; padding: 0 4px; font-size: 14px; }
</style>

<?= $this->endSection() ?>
