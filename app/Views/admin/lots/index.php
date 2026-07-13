<?php
/** @var array $lots */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var array $filters */
/** @var int $per_page */
/** @var array $suppliers */
?>
<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:30px;">
    <h1 style="font-size:24px;font-weight:700;">Lotes de USDT</h1>
    <a href="<?= url_to('admin_lots_create') ?>" class="btn"
        style="background:#6366f1;color:white;padding:10px 20px;border-radius:12px;font-weight:600;font-size:14px;">
        + Registrar Compra
    </a>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div style="background:rgba(34,197,94,0.1);color:#4ade80;padding:15px;border-radius:12px;margin-bottom:20px;border:1px solid rgba(34,197,94,0.2);font-size:14px;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
    <div style="background:rgba(239,68,68,0.1);color:#f87171;padding:15px;border-radius:12px;margin-bottom:20px;border:1px solid rgba(239,68,68,0.2);font-size:14px;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<!-- Resumo -->
<?php if (!empty($summary)): ?>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:30px;">
    <div class="card" style="padding:18px;text-align:center;">
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:6px;">Total Lotes</p>
        <p style="font-size:22px;font-weight:700;color:white;"><?= number_format((int)($summary['total_lots'] ?? 0)) ?></p>
    </div>
    <div class="card" style="padding:18px;text-align:center;">
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:6px;">Total Comprado</p>
        <p style="font-size:22px;font-weight:700;color:#60a5fa;"><?= number_format((float)($summary['total_usdt'] ?? 0), 2, '.', ',') ?> USDT</p>
    </div>
    <div class="card" style="padding:18px;text-align:center;">
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:6px;">Total Entregue</p>
        <p style="font-size:22px;font-weight:700;color:#34d399;"><?= number_format((float)($summary['total_delivered'] ?? 0), 2, '.', ',') ?> USDT</p>
    </div>
    <div class="card" style="padding:18px;text-align:center;">
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:6px;">Reservado</p>
        <p style="font-size:22px;font-weight:700;color:#fbbf24;"><?= number_format((float)($summary['total_reserved'] ?? 0), 2, '.', ',') ?> USDT</p>
    </div>
    <div class="card" style="padding:18px;text-align:center;">
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:6px;">Lucro Total</p>
        <p style="font-size:22px;font-weight:700;color:#4ade80;">R$ <?= number_format((float)($summary['total_profit'] ?? 0), 2, ',', '.') ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Filtros -->
<form method="get" action="" style="margin-bottom:20px;">
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:18px 20px;">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">

            <div style="display:flex;flex-direction:column;gap:5px;min-width:140px;">
                <label style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Data de</label>
                <input type="date" name="start_date" value="<?= esc($filters['start_date'] ?? '') ?>"
                    style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:8px 12px;color:white;font-size:13px;outline:none;color-scheme:dark;">
            </div>

            <div style="display:flex;flex-direction:column;gap:5px;min-width:140px;">
                <label style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Data até</label>
                <input type="date" name="end_date" value="<?= esc($filters['end_date'] ?? '') ?>"
                    style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:8px 12px;color:white;font-size:13px;outline:none;color-scheme:dark;">
            </div>

            <div style="display:flex;flex-direction:column;gap:5px;min-width:160px;">
                <label style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Fornecedor</label>
                <select name="supplier"
                    style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:8px 12px;color:white;font-size:13px;outline:none;cursor:pointer;">
                    <option value="">Todos</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= esc($s['name']) ?>" <?= ($filters['supplier'] ?? '') === $s['name'] ? 'selected' : '' ?>>
                            <?= esc($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:flex;flex-direction:column;gap:5px;min-width:120px;">
                <label style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Fluxo</label>
                <select name="delivery_type"
                    style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:8px 12px;color:white;font-size:13px;outline:none;cursor:pointer;">
                    <option value="">Todos</option>
                    <option value="d+0" <?= ($filters['delivery_type'] ?? '') === 'd+0' ? 'selected' : '' ?>>D+0</option>
                    <option value="d+1" <?= ($filters['delivery_type'] ?? '') === 'd+1' ? 'selected' : '' ?>>D+1</option>
                    <option value="d+2" <?= ($filters['delivery_type'] ?? '') === 'd+2' ? 'selected' : '' ?>>D+2</option>
                </select>
            </div>

            <div style="display:flex;flex-direction:column;gap:5px;min-width:130px;">
                <label style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Status</label>
                <select name="status"
                    style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:8px 12px;color:white;font-size:13px;outline:none;cursor:pointer;">
                    <option value="">Todos</option>
                    <option value="active"    <?= ($filters['status'] ?? '') === 'active'    ? 'selected' : '' ?>>Ativo</option>
                    <option value="depleted"  <?= ($filters['status'] ?? '') === 'depleted'  ? 'selected' : '' ?>>Esgotado</option>
                    <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>

            <div style="display:flex;flex-direction:column;gap:5px;min-width:90px;">
                <label style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Por página</label>
                <select name="per_page"
                    style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:8px 12px;color:white;font-size:13px;outline:none;cursor:pointer;">
                    <?php foreach([15, 25, 50, 100] as $opt): ?>
                        <option value="<?= $opt ?>" <?= $per_page === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:flex;gap:8px;align-items:flex-end;padding-bottom:1px;">
                <button type="submit"
                    style="background:#6366f1;color:white;border:none;border-radius:8px;padding:8px 18px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;">
                    Filtrar
                </button>
                <a href="<?= current_url() ?>"
                    style="background:rgba(255,255,255,0.06);color:#94a3b8;border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:8px 14px;font-size:13px;text-decoration:none;white-space:nowrap;font-weight:500;">
                    Limpar
                </a>
            </div>

        </div>
    </div>
</form>

<!-- Tabela -->
<div class="card" style="padding:0;overflow:hidden;">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                    <th style="padding:14px 20px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">#</th>
                    <th style="padding:14px 20px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Fornecedor</th>
                    <th style="padding:14px 20px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Fluxo</th>
                    <th style="padding:14px 20px;text-align:right;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Comprado</th>
                    <th style="padding:14px 20px;text-align:right;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Reservado</th>
                    <th style="padding:14px 20px;text-align:right;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Entregue</th>
                    <th style="padding:14px 20px;text-align:right;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Taxa R$/USDT</th>
                    <th style="padding:14px 20px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Status</th>
                    <th style="padding:14px 20px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Data</th>
                    <th style="padding:14px 20px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Por</th>
                    <th style="padding:14px 20px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lots)): ?>
                    <tr>
                        <td colspan="11" style="padding:40px;text-align:center;color:#64748b;font-size:14px;">Nenhum lote encontrado com os filtros aplicados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($lots as $lot): ?>
                        <?php
                            $statusColor = match($lot['status']) {
                                'active'    => '#34d399',
                                'depleted'  => '#64748b',
                                'cancelled' => '#f87171',
                                default     => '#94a3b8',
                            };
                            $statusLabel = match($lot['status']) {
                                'active'    => 'Ativo',
                                'depleted'  => 'Esgotado',
                                'cancelled' => 'Cancelado',
                                default     => $lot['status'],
                            };
                        ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.04);transition:background 0.15s;"
                            onmouseover="this.style.background='rgba(255,255,255,0.02)'"
                            onmouseout="this.style.background=''">
                            <td style="padding:14px 20px;font-size:13px;color:#64748b;">#<?= $lot['id'] ?></td>
                            <td style="padding:14px 20px;font-size:13px;font-weight:600;color:white;">
                                <?= esc($lot['supplier']) ?>
                                <?php if (isset($lot['is_promotional']) && $lot['is_promotional']): ?>
                                    <span style="font-size:9px;padding:2px 6px;border-radius:4px;background:rgba(239,68,68,0.15);color:#f87171;font-weight:700;margin-left:6px;vertical-align:middle;text-transform:uppercase;">PROMO</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:14px 20px;">
                                <?php if ($lot['delivery_type']): ?>
                                    <span style="font-size:11px;padding:3px 8px;border-radius:20px;background:rgba(99,102,241,0.12);color:#818cf8;font-weight:700;"><?= strtoupper($lot['delivery_type']) ?></span>
                                <?php else: ?>
                                    <span style="color:#475569;font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:14px 20px;text-align:right;font-size:13px;color:white;"><?= number_format((float)$lot['usdt_amount'], 2, '.', ',') ?></td>
                            <td style="padding:14px 20px;text-align:right;font-size:13px;color:#fbbf24;"><?= number_format((float)$lot['usdt_reserved'], 2, '.', ',') ?></td>
                            <td style="padding:14px 20px;text-align:right;font-size:13px;color:#34d399;"><?= number_format((float)$lot['usdt_delivered'], 2, '.', ',') ?></td>
                            <td style="padding:14px 20px;text-align:right;font-size:13px;color:#94a3b8;">R$ <?= number_format((float)$lot['conversion_rate'], 4, ',', '.') ?></td>
                            <td style="padding:14px 20px;">
                                <span style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:700;background:<?= $statusColor ?>22;color:<?= $statusColor ?>;"><?= $statusLabel ?></span>
                            </td>
                            <td style="padding:14px 20px;font-size:12px;color:#64748b;white-space:nowrap;"><?= date('d/m/y H:i', strtotime($lot['created_at'])) ?></td>
                            <td style="padding:14px 20px;font-size:12px;color:#64748b;"><?= esc($lot['created_by_name'] ?? '—') ?></td>
                            <td style="padding:14px 20px;text-align:right;">
                                <a href="<?= url_to('admin_lots_show', $lot['id']) ?>"
                                    style="font-size:12px;color:#6366f1;text-decoration:none;font-weight:600;white-space:nowrap;">Ver →</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Paginação -->
<?php
    $currentPage = $pager->getCurrentPage();
    $pageCount   = $pager->getPageCount();
    $queryBase   = array_filter([
        'start_date'    => $filters['start_date']    ?? '',
        'end_date'      => $filters['end_date']      ?? '',
        'supplier'      => $filters['supplier']      ?? '',
        'delivery_type' => $filters['delivery_type'] ?? '',
        'status'        => $filters['status']        ?? '',
        'per_page'      => $per_page !== 15 ? $per_page : '',
    ]);

    $buildUrl = function(int $page) use ($queryBase): string {
        $params = array_filter(array_merge($queryBase, ['page' => $page > 1 ? $page : '']));
        $qs = http_build_query($params);
        return current_url() . ($qs ? '?' . $qs : '');
    };

    $window = 2;
    $start  = max(1, $currentPage - $window);
    $end    = min($pageCount, $currentPage + $window);
?>
<div style="margin-top:20px;display:flex;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;">

    <?php if ($currentPage > 1): ?>
        <a href="<?= $buildUrl($currentPage - 1) ?>" class="page-btn">&#8592; Anterior</a>
    <?php else: ?>
        <span class="page-btn page-disabled">&#8592; Anterior</span>
    <?php endif; ?>

    <?php if ($start > 1): ?>
        <a href="<?= $buildUrl(1) ?>" class="page-btn">1</a>
        <?php if ($start > 2): ?><span class="page-ellipsis">&hellip;</span><?php endif; ?>
    <?php endif; ?>

    <?php for ($p = $start; $p <= $end; $p++): ?>
        <?php if ($p === $currentPage): ?>
            <span class="page-btn page-active"><?= $p ?></span>
        <?php else: ?>
            <a href="<?= $buildUrl($p) ?>" class="page-btn"><?= $p ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($end < $pageCount): ?>
        <?php if ($end < $pageCount - 1): ?><span class="page-ellipsis">&hellip;</span><?php endif; ?>
        <a href="<?= $buildUrl($pageCount) ?>" class="page-btn"><?= $pageCount ?></a>
    <?php endif; ?>

    <?php if ($currentPage < $pageCount): ?>
        <a href="<?= $buildUrl($currentPage + 1) ?>" class="page-btn">Próximo &#8594;</a>
    <?php else: ?>
        <span class="page-btn page-disabled">Próximo &#8594;</span>
    <?php endif; ?>

</div>

<style>
    .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 24px; backdrop-filter: blur(10px); }
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
