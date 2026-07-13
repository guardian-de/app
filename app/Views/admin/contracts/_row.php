<?php
/** @var array $contract */
$totalLotAllocated = (float)($contract['total_lot_allocated'] ?? 0);
$totalAmount       = (float)$contract['total_amount'];
$deliveredUsdt     = (float)$contract['delivered_usdt'];

$isPaid   = $contract['status'] === 'paid';
$isSent   = $totalAmount > 0 && $deliveredUsdt >= $totalAmount;
$isClosed = $totalAmount > 0 && $totalLotAllocated >= $totalAmount;
?>
<tr data-contract-id="<?= $contract['id'] ?>" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
    <td style="padding: 15px; font-family: monospace; color: #94a3b8; font-size: 13px;">
        #<?= $contract['id'] ?>
    </td>
    <td style="padding: 15px;">
        <div style="font-weight: 600;"><?= esc($contract['user_name']) ?></div>
        <div style="font-size: 10px; color: #818cf8; font-family: monospace;"><?= esc($contract['requested_wallet'] ?: ($contract['usdt_wallet'] ?: 'N/A')) ?></div>
        <div style="font-size: 11px; color: #94a3b8;"><?= esc($contract['type']) ?></div>
    </td>
    <td style="padding: 15px; font-weight: 600;">
        <div><?= number_format($contract['total_amount'], 2, '.', ',') ?> USDT</div>
        <div style="font-size: 11px; color: #60a5fa; font-weight: normal;">Enviado: <?= number_format($contract['delivered_usdt'], 2, '.', ',') ?></div>
        <div style="font-size: 10px; color: #f87171; font-weight: normal;">Faltando: <?= number_format($contract['total_amount'] - $contract['delivered_usdt'], 2, '.', ',') ?></div>
    </td>
    <td style="padding: 15px; font-size: 13px;">
        <?= isset($contract['spot_rate']) && $contract['spot_rate'] !== null ? 'R$ ' . number_format($contract['spot_rate'], 4, ',', '.') : '—' ?>
    </td>
    <td style="padding: 15px;">
        <span style="color: #f87171; font-weight: 700;">
            R$ <?= number_format($contract['remaining_balance'], 2, ',', '.') ?>
        </span>
        <?php if ($contract['interest_accumulated'] > 0): ?>
            <div style="font-size: 10px; color: #fbbf24;">+ R$ <?= number_format($contract['interest_accumulated'], 2, ',', '.') ?> juros</div>
        <?php endif; ?>
    </td>
    <td style="padding: 15px; font-size: 13px;">
        <?= date('d/m/Y', strtotime($contract['due_date'])) ?>
    </td>
    <td style="padding: 15px;">
        <?php if ($isPaid): ?>
            <div><span class="status-badge" style="color: #4ade80; background: rgba(34, 197, 94, 0.1);">Pago</span></div>
        <?php endif; ?>
        <?php if ($isSent): ?>
            <div<?= $isPaid ? ' style="margin-top: 4px;"' : '' ?>><span class="status-badge" style="color: #60a5fa; background: rgba(96, 165, 250, 0.1);">Enviado</span></div>
        <?php endif; ?>
    </td>
    <td style="padding: 15px;">
        <?php if ($isClosed): ?>
            <span class="status-badge" style="color: #34d399; background: rgba(52, 211, 153, 0.1);">Concluído</span>
        <?php else: ?>
            <span class="status-badge" style="color: #f87171; background: rgba(248, 113, 113, 0.1);">Em aberto</span>
        <?php endif; ?>
    </td>
    <td style="padding: 15px;">
        <a href="<?= url_to('admin_contracts_show', $contract['id']) ?>" class="btn"
            style="background: rgba(59, 130, 246, 0.1); color: #60a5fa; padding: 8px 15px; font-size: 12px; border-radius: 8px; text-decoration: none; font-weight: 600; border: 1px solid rgba(59, 130, 246, 0.2);">Abrir</a>
    </td>
</tr>
