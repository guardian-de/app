<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
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
    $available = max(0, (float)$lot['usdt_amount'] - (float)$lot['usdt_reserved'] - (float)$lot['usdt_delivered']);
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:30px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="<?= url_to('admin_lots') ?>" style="color:#64748b;text-decoration:none;font-size:13px;">← Lotes</a>
        <h1 style="font-size:22px;font-weight:700;">Lote #<?= $lot['id'] ?> — <?= esc($lot['supplier']) ?></h1>
        <span style="font-size:12px;padding:4px 12px;border-radius:20px;font-weight:700;background:<?= $statusColor ?>22;color:<?= $statusColor ?>;"><?= $statusLabel ?></span>
    </div>
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

<!-- Resumo do lote -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px;margin-bottom:28px;">
    <div class="card" style="padding:18px;text-align:center;">
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:6px;">Comprado</p>
        <p style="font-size:20px;font-weight:700;color:white;"><?= number_format((float)$lot['usdt_amount'], 2, '.', ',') ?></p>
        <p style="font-size:11px;color:#64748b;">USDT</p>
    </div>
    <div class="card" style="padding:18px;text-align:center;">
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:6px;">Reservado</p>
        <p style="font-size:20px;font-weight:700;color:#fbbf24;"><?= number_format((float)$lot['usdt_reserved'], 2, '.', ',') ?></p>
        <p style="font-size:11px;color:#64748b;">USDT</p>
    </div>
    <div class="card" style="padding:18px;text-align:center;">
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:6px;">Entregue</p>
        <p style="font-size:20px;font-weight:700;color:#34d399;"><?= number_format((float)$lot['usdt_delivered'], 2, '.', ',') ?></p>
        <p style="font-size:11px;color:#64748b;">USDT</p>
    </div>
    <div class="card" style="padding:18px;text-align:center;">
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:6px;">Disponível</p>
        <p style="font-size:20px;font-weight:700;color:#60a5fa;"><?= number_format($available, 2, '.', ',') ?></p>
        <p style="font-size:11px;color:#64748b;">USDT</p>
    </div>
    <div class="card" style="padding:18px;text-align:center;">
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:6px;">Taxa</p>
        <p style="font-size:20px;font-weight:700;color:#a78bfa;">R$ <?= number_format((float)$lot['conversion_rate'], 4, ',', '.') ?></p>
        <p style="font-size:11px;color:#64748b;">/USDT</p>
    </div>
    <div class="card" style="padding:18px;text-align:center;">
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:6px;">Total BRL</p>
        <p style="font-size:20px;font-weight:700;color:white;">R$ <?= number_format((float)$lot['total_brl'], 2, ',', '.') ?></p>
    </div>
    <?php if (!empty($lot['profit_brl'])): ?>
    <div class="card" style="padding:18px;text-align:center;">
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:6px;">Lucro</p>
        <p style="font-size:20px;font-weight:700;color:#4ade80;">R$ <?= number_format((float)$lot['profit_brl'], 2, ',', '.') ?></p>
    </div>
    <?php endif; ?>
</div>

<!-- Detalhes -->
<div class="card" style="padding:20px;margin-bottom:28px;display:flex;flex-wrap:wrap;gap:24px;">
    <?php if (isset($lot['is_promotional']) && $lot['is_promotional']): ?>
    <div>
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:4px;">Tipo de Lote</p>
        <span style="font-size:13px;padding:4px 10px;border-radius:20px;background:rgba(239,68,68,0.12);color:#f87171;font-weight:700;">PROMOCIONAL</span>
    </div>
    <div>
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:4px;">Custo Cliente</p>
        <p style="font-size:13px;color:white;font-weight:600;">R$ <?= number_format((float)($lot['promo_rate'] ?? 0), 4, ',', '.') ?></p>
    </div>
    <div>
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:4px;">Destinatários</p>
        <p style="font-size:13px;color:#94a3b8;"><?= strtoupper($lot['target_type'] ?? '') ?> <?= $lot['target_group'] ? ' - ' . esc($lot['target_group']) : '' ?></p>
    </div>
    <?php endif; ?>
    <?php if ($lot['delivery_type']): ?>
    <div>
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:4px;">Fluxo</p>
        <span style="font-size:13px;padding:4px 10px;border-radius:20px;background:rgba(99,102,241,0.12);color:#818cf8;font-weight:700;"><?= strtoupper($lot['delivery_type']) ?></span>
    </div>
    <?php endif; ?>
    <?php if ($lot['purchase_hash']): ?>
    <div>
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:4px;">Hash da Compra</p>
        <p style="font-size:13px;font-family:monospace;color:#94a3b8;"><?= esc($lot['purchase_hash']) ?></p>
    </div>
    <?php endif; ?>
    <div>
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:4px;">Criado em</p>
        <p style="font-size:13px;color:#94a3b8;"><?= date('d/m/Y H:i', strtotime($lot['created_at'])) ?></p>
    </div>
    <div>
        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:4px;">Por</p>
        <p style="font-size:13px;color:#94a3b8;"><?= esc($lot['created_by_name'] ?? '—') ?></p>
    </div>
</div>

<!-- Alocações -->
<h3 style="font-size:16px;font-weight:700;color:white;margin-bottom:14px;">Alocações</h3>
<div class="card" style="padding:0;overflow:hidden;margin-bottom:28px;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                <th style="padding:12px 18px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">#</th>
                <th style="padding:12px 18px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Entidade</th>
                <th style="padding:12px 18px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Cliente</th>
                <th style="padding:12px 18px;text-align:right;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">USDT</th>
                <th style="padding:12px 18px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Status</th>
                <th style="padding:12px 18px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Alocado por</th>
                <th style="padding:12px 18px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Data</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($allocations)): ?>
                <tr>
                    <td colspan="7" style="padding:30px;text-align:center;color:#64748b;font-size:14px;">Nenhuma alocação registrada.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($allocations as $alloc): ?>
                    <?php
                        $aColor = match($alloc['status']) {
                            'reserved'  => '#fbbf24',
                            'delivered' => '#34d399',
                            'cancelled' => '#f87171',
                            default     => '#94a3b8',
                        };
                        $aLabel = match($alloc['status']) {
                            'reserved'  => 'Reservado',
                            'delivered' => 'Entregue',
                            'cancelled' => 'Cancelado',
                            default     => $alloc['status'],
                        };
                        $entityId = $alloc['contract_id'] ?? $alloc['transaction_id'];
                        $entityRoute = $alloc['contract_id']
                            ? url_to('admin_contracts_show', $alloc['contract_id'])
                            : url_to('admin_transactions_show', $alloc['transaction_id']);
                    ?>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                        <td style="padding:12px 18px;font-size:12px;color:#64748b;">#<?= $alloc['id'] ?></td>
                        <td style="padding:12px 18px;font-size:13px;">
                            <a href="<?= $entityRoute ?>" style="color:#6366f1;text-decoration:none;font-weight:600;">
                                <?= ucfirst($alloc['entity_label'] ?? '—') ?> #<?= $entityId ?>
                            </a>
                        </td>
                        <td style="padding:12px 18px;font-size:13px;color:#94a3b8;"><?= esc($alloc['client_name'] ?? '—') ?></td>
                        <td style="padding:12px 18px;text-align:right;font-size:13px;font-weight:600;color:white;"><?= number_format((float)$alloc['usdt_amount'], 2, '.', ',') ?></td>
                        <td style="padding:12px 18px;">
                            <span style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:700;background:<?= $aColor ?>22;color:<?= $aColor ?>;"><?= $aLabel ?></span>
                        </td>
                        <td style="padding:12px 18px;font-size:12px;color:#64748b;"><?= esc($alloc['allocated_by_name'] ?? '—') ?></td>
                        <td style="padding:12px 18px;font-size:12px;color:#64748b;white-space:nowrap;"><?= date('d/m/y H:i', strtotime($alloc['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Logs -->
<?php if (!empty($logs)): ?>
<h3 style="font-size:16px;font-weight:700;color:white;margin-bottom:14px;">Histórico de Atividade</h3>
<div class="card" style="padding:0;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                <th style="padding:12px 18px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Ação</th>
                <th style="padding:12px 18px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Data</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                    <td style="padding:12px 18px;font-size:13px;color:#94a3b8;"><?= esc($log['action']) ?></td>
                    <td style="padding:12px 18px;font-size:12px;color:#64748b;white-space:nowrap;"><?= date('d/m/y H:i', strtotime($log['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<style>
    .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 24px; backdrop-filter: blur(10px); }
</style>

<?= $this->endSection() ?>
