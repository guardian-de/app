<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<?php /** @var array $grouped */ ?>
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
    <h1 style="font-size:22px; color:white; font-weight:700;">Fila de Envio de USDT</h1>
    <span style="font-size:13px; color:#94a3b8;"><?= count($grouped) ?> cliente(s) com envio pendente</span>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div style="background:rgba(34,197,94,0.1); color:#4ade80; padding:12px 16px; border-radius:10px; margin-bottom:20px; border:1px solid rgba(34,197,94,0.2); font-size:13px;">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div style="background:rgba(248,113,113,0.1); color:#f87171; padding:12px 16px; border-radius:10px; margin-bottom:20px; border:1px solid rgba(248,113,113,0.2); font-size:13px;">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<?php if(empty($grouped)): ?>
    <div class="card" style="text-align:center; padding:60px 30px; color:#94a3b8;">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 14px; display:block; opacity:0.4;">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        <p style="font-size:15px; font-weight:600; color:#f8fafc; margin-bottom:6px;">Tudo em dia!</p>
        <p style="font-size:13px;">Não há operações com USDT pendente de envio.</p>
    </div>
<?php else: ?>

<div class="card" style="padding:0; overflow-x:auto;">
    <table style="width:100%; min-width:820px; border-collapse:collapse; font-size:13px;">
        <thead>
            <tr style="background:rgba(0,0,0,0.25); border-bottom:1px solid rgba(255,255,255,0.07);">
                <th style="padding:8px 12px; text-align:left; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em; white-space:nowrap;">Prioridade</th>
                <th style="padding:8px 12px; text-align:left; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em;">Cliente</th>
                <th style="padding:8px 12px; text-align:center; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em; white-space:nowrap;">Qtd</th>
                <th style="padding:8px 12px; text-align:right; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em; white-space:nowrap;">Pago (R$)</th>
                <th style="padding:8px 12px; text-align:right; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em; white-space:nowrap;">A enviar (USDT)</th>
                <th style="padding:8px 12px; text-align:right; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em; white-space:nowrap;">Enviado / Total</th>
                <th style="padding:8px 12px; text-align:left; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em;">Carteira</th>
                <th style="padding:8px 12px; text-align:center; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em;">Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($grouped as $g):
            $daysLate = (int) $g['max_days_late'];
            $brlTotal  = (float) $g['total_brl'];
            $brlPaid   = (float) $g['paid_amount'];
            $payPct    = $brlTotal > 0 ? min(100, ($brlPaid / $brlTotal) * 100) : 0;

            $walletFull  = $g['requested_wallet'] ?: ($g['usdt_wallet'] ?? '');
            $walletShort = $walletFull ? (substr($walletFull, 0, 8).'…'.substr($walletFull, -6)) : '';

            if ($daysLate >= 5) {
                $priorityLabel = 'D-'.$daysLate; $priorityColor = '#f87171';
                $priorityBg = 'rgba(248,113,113,0.15)'; $priorityBorder = 'rgba(248,113,113,0.4)';
                $rowBorder = '#f87171';
            } elseif ($daysLate >= 3) {
                $priorityLabel = 'D-'.$daysLate; $priorityColor = '#fb923c';
                $priorityBg = 'rgba(251,146,60,0.15)'; $priorityBorder = 'rgba(251,146,60,0.4)';
                $rowBorder = '#fb923c';
            } elseif ($daysLate >= 1) {
                $priorityLabel = 'D-'.$daysLate; $priorityColor = '#fbbf24';
                $priorityBg = 'rgba(251,191,36,0.15)'; $priorityBorder = 'rgba(251,191,36,0.4)';
                $rowBorder = '#fbbf24';
            } elseif ($daysLate === 0) {
                $priorityLabel = 'Hoje'; $priorityColor = '#38bdf8';
                $priorityBg = 'rgba(56,189,248,0.12)'; $priorityBorder = 'rgba(56,189,248,0.35)';
                $rowBorder = '#38bdf8';
            } else {
                $priorityLabel = 'Futuro'; $priorityColor = '#818cf8';
                $priorityBg = 'rgba(129,140,248,0.12)'; $priorityBorder = 'rgba(129,140,248,0.3)';
                $rowBorder = 'transparent';
            }

            $clientUrl = url_to('admin_delivery_client', $g['user_id']);
        ?>
        <tr style="border-bottom:1px solid rgba(255,255,255,0.05); border-left:3px solid <?= $rowBorder ?>; transition:background 0.15s; cursor:pointer;"
            onclick="window.location='<?= $clientUrl ?>'"
            onmouseover="this.style.background='rgba(255,255,255,0.04)'"
            onmouseout="this.style.background=''">

            <!-- Prioridade -->
            <td style="padding:8px 12px; white-space:nowrap;">
                <span style="display:inline-block; padding:3px 9px; border-radius:6px; font-size:12px; font-weight:800; letter-spacing:-0.02em; color:<?= $priorityColor ?>; background:<?= $priorityBg ?>; border:1px solid <?= $priorityBorder ?>;">
                    <?= $priorityLabel ?>
                </span>
            </td>

            <!-- Cliente -->
            <td style="padding:8px 12px;">
                <div style="font-weight:700; font-size:14px; color:#f8fafc; white-space:nowrap;"><?= esc($g['user_name']) ?></div>
                <div style="font-size:11px; color:#64748b; margin-top:1px; white-space:nowrap;">vence <?= date('d/m/Y', strtotime($g['earliest_due'])) ?></div>
            </td>

            <!-- Qtd contratos -->
            <td style="padding:8px 12px; text-align:center; white-space:nowrap;">
                <span style="display:inline-block; padding:2px 9px; border-radius:20px; font-size:12px; font-weight:700; color:#c084fc; background:rgba(192,132,252,0.12); border:1px solid rgba(192,132,252,0.25);">
                    <?= $g['contract_count'] ?>
                </span>
            </td>

            <!-- Pago (R$) -->
            <td style="padding:8px 12px; text-align:right; white-space:nowrap;">
                <div style="font-family:monospace; font-size:13px; font-weight:700; color:#4ade80;"><?= number_format($brlPaid, 2, ',', '.') ?></div>
                <div style="background:rgba(255,255,255,0.06); border-radius:3px; height:3px; overflow:hidden; margin-top:4px; min-width:60px;">
                    <div style="width:<?= round($payPct, 1) ?>%; height:100%; background:linear-gradient(90deg,#4ade80,#22d3ee); border-radius:3px;"></div>
                </div>
                <div style="font-size:10px; color:#475569; margin-top:2px;"><?= number_format($brlTotal, 2, ',', '.') ?> · <?= number_format($payPct, 0) ?>%</div>
            </td>

            <!-- A enviar (USDT) -->
            <td style="padding:8px 12px; text-align:right; white-space:nowrap;">
                <div style="font-family:monospace; font-size:14px; font-weight:800; color:#c084fc;"><?= number_format((float)$g['pending_usdt'], 2, '.', ',') ?></div>
                <?php if((float)$g['unlinked_usdt'] > 0): ?>
                    <div style="margin-top:3px; display:inline-flex; align-items:center; gap:3px; font-size:10px; font-weight:700; color:#fb923c; background:rgba(251,146,60,0.12); border:1px solid rgba(251,146,60,0.3); padding:1px 6px; border-radius:4px;" title="USDT sem lote de fornecedor associado">
                        ⚠ <?= number_format((float)$g['unlinked_usdt'], 2, '.', ',') ?> sem lote
                    </div>
                <?php endif; ?>
            </td>

            <!-- Enviado / Total USDT -->
            <td style="padding:8px 12px; text-align:right; white-space:nowrap;">
                <div style="font-family:monospace; font-size:12px; font-weight:600; color:#34d399;"><?= number_format((float)$g['delivered_usdt'], 2, '.', ',') ?></div>
                <div style="font-size:10px; color:#475569; margin-top:1px;"><?= number_format((float)$g['total_amount'], 2, '.', ',') ?></div>
            </td>

            <!-- Carteira -->
            <td style="padding:8px 12px;">
                <?php if($walletFull): ?>
                    <div style="display:flex; align-items:center; gap:5px;">
                        <span style="font-family:monospace; font-size:11px; color:#818cf8; white-space:nowrap;" title="<?= esc($walletFull) ?>"><?= esc($walletShort) ?></span>
                        <button onclick="event.stopPropagation(); navigator.clipboard.writeText('<?= esc($walletFull) ?>').then(()=>{ this.title='Copiado!'; setTimeout(()=>this.title='Copiar',1500) })"
                            title="Copiar"
                            style="background:none; border:none; color:#475569; cursor:pointer; padding:2px; line-height:0; flex-shrink:0;"
                            onmouseover="this.style.color='#818cf8'" onmouseout="this.style.color='#475569'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                            </svg>
                        </button>
                    </div>
                <?php else: ?>
                    <span style="font-size:11px; color:#f87171;">Sem carteira</span>
                <?php endif; ?>
            </td>

            <!-- Ações -->
            <td style="padding:8px 12px; text-align:center; white-space:nowrap;" onclick="event.stopPropagation()">
                <div style="display:flex; align-items:center; gap:6px;">
                    <a href="<?= $clientUrl ?>"
                       title="Ver operações"
                       style="display:inline-flex; align-items:center; justify-content:center; gap:4px; background:rgba(56,189,248,0.12); color:#38bdf8; padding:5px 10px; font-size:12px; border-radius:6px; text-decoration:none; font-weight:600; border:1px solid rgba(56,189,248,0.3); white-space:nowrap;"
                       onmouseover="this.style.background='rgba(56,189,248,0.22)'" onmouseout="this.style.background='rgba(56,189,248,0.12)'">
                        Operações
                    </a>
                    <?php
                        $sendable    = (float)($g['sendable_usdt'] ?? 0);
                        $absoluteMax = max(0, (float)$g['total_amount'] - (float)$g['delivered_usdt']);
                    ?>
                    <?php if($absoluteMax >= 0.01): ?>
                    <button type="button"
                       title="Enviar USDT — distribui automaticamente pelas operações de maior lucro"
                       onclick="openBulkSendModal(<?= (int)$g['user_id'] ?>, <?= number_format($absoluteMax, 2, '.', '') ?>, <?= number_format($sendable, 2, '.', '') ?>, '<?= esc(addslashes($g['user_name'])) ?>', '<?= esc(addslashes($walletShort)) ?>')"
                       style="display:inline-flex; align-items:center; justify-content:center; gap:4px; background:rgba(192,132,252,0.12); color:#c084fc; padding:5px 10px; font-size:12px; border-radius:6px; font-weight:600; border:1px solid rgba(192,132,252,0.28); cursor:pointer; white-space:nowrap;"
                       onmouseover="this.style.background='rgba(192,132,252,0.22)'" onmouseout="this.style.background='rgba(192,132,252,0.12)'">
                        Enviar
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                    <?php else: ?>
                    <span title="Nenhum USDT pendente de envio"
                       style="display:inline-flex; align-items:center; justify-content:center; gap:4px; background:rgba(100,116,139,0.1); color:#64748b; padding:5px 10px; font-size:12px; border-radius:6px; font-weight:600; border:1px solid rgba(100,116,139,0.2); white-space:nowrap; cursor:not-allowed;">
                        —
                    </span>
                    <?php endif; ?>
                </div>
            </td>

        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php endif; ?>

<!-- Bulk Send USDT Modal -->
<div id="bulk-send-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9000; align-items:center; justify-content:center;">
    <div style="background:#1e293b; border:1px solid rgba(255,255,255,0.1); border-radius:16px; padding:28px 32px; width:440px; max-width:92vw; box-shadow:0 24px 64px rgba(0,0,0,0.5);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
            <h3 id="bulk-modal-title" style="color:white; font-size:16px; font-weight:700; margin:0;">Enviar USDT</h3>
            <button onclick="closeBulkSendModal()" style="background:none; border:none; color:#64748b; cursor:pointer; font-size:20px; line-height:1; padding:0 2px;"
                onmouseover="this.style.color='#94a3b8'" onmouseout="this.style.color='#64748b'">&times;</button>
        </div>

        <p style="font-size:12px; color:#94a3b8; margin:0 0 16px; line-height:1.5;">
            O valor será distribuído automaticamente entre as operações do cliente,
            priorizando os de <strong style="color:#c084fc;">maior lucro</strong> (taxa do cliente − custo do lote).
            Valores fora do coberto por lote/pagamento pedem confirmação extra.
        </p>

        <div id="bulk-modal-wallet-row" style="display:none; align-items:center; gap:8px; margin-bottom:16px; padding:8px 12px; background:rgba(129,140,248,0.07); border:1px solid rgba(129,140,248,0.15); border-radius:8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
            <span id="bulk-modal-wallet-text" style="font-family:monospace; font-size:12px; color:#818cf8;"></span>
        </div>

        <form method="post" id="bulk-send-form">
            <?= csrf_field() ?>
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; font-weight:600; color:#94a3b8; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">Quantidade USDT</label>
                <input type="number" name="amount_usdt" id="bulk-modal-amount" step="0.01" min="0.01"
                    style="width:100%; background:#0f172a; border:1px solid rgba(255,255,255,0.12); border-radius:8px; padding:10px 14px; color:white; font-size:15px; font-family:monospace; font-weight:700; box-sizing:border-box; outline:none;"
                    onfocus="this.style.borderColor='#c084fc'" onblur="this.style.borderColor='rgba(255,255,255,0.12)'">
                <div id="bulk-modal-max-hint" style="font-size:11px; color:#64748b; margin-top:4px;"></div>
            </div>
            <input type="hidden" id="bulk-modal-sendable" value="0">
            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:12px; font-weight:600; color:#94a3b8; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">Hash da Transação *</label>
                <input type="text" name="notes" id="bulk-modal-notes" placeholder="Ex: Hash da rede TRC-20..." required
                    style="width:100%; background:#0f172a; border:1px solid rgba(255,255,255,0.12); border-radius:8px; padding:10px 14px; color:#cbd5e1; font-size:13px; box-sizing:border-box; outline:none;"
                    onfocus="this.style.borderColor='#818cf8'" onblur="this.style.borderColor='rgba(255,255,255,0.12)'">
            </div>
            <div style="display:flex; gap:10px;">
                <button type="button" onclick="closeBulkSendModal()"
                    style="flex:1; padding:10px; background:rgba(255,255,255,0.05); color:#94a3b8; border:1px solid rgba(255,255,255,0.1); border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;"
                    onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">
                    Cancelar
                </button>
                <button type="submit"
                    style="flex:2; padding:10px; background:rgba(192,132,252,0.2); color:#c084fc; border:1px solid rgba(192,132,252,0.4); border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;"
                    onmouseover="this.style.background='rgba(192,132,252,0.32)'" onmouseout="this.style.background='rgba(192,132,252,0.2)'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Confirmar Envio
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let bulkSendConfirmed = false;

function openBulkSendModal(userId, absoluteMax, sendable, userName, wallet) {
    document.getElementById('bulk-modal-title').textContent = 'Enviar USDT — ' + userName;
    var amountInput = document.getElementById('bulk-modal-amount');
    amountInput.value = sendable.toFixed(2);
    amountInput.max   = absoluteMax;
    document.getElementById('bulk-modal-max-hint').textContent = 'Máx entregável com lote: ' + sendable.toFixed(2).replace('.', ',') + ' USDT (total pendente: ' + absoluteMax.toFixed(2).replace('.', ',') + ' USDT)';
    document.getElementById('bulk-modal-notes').value = '';
    document.getElementById('bulk-modal-sendable').value = sendable;
    document.getElementById('bulk-send-form').action = '<?= site_url('admin/delivery/send/') ?>' + userId;
    bulkSendConfirmed = false;

    var walletRow = document.getElementById('bulk-modal-wallet-row');
    if (wallet) {
        document.getElementById('bulk-modal-wallet-text').textContent = wallet;
        walletRow.style.display = 'flex';
    } else {
        walletRow.style.display = 'none';
    }

    document.getElementById('bulk-send-modal').style.display = 'flex';
}

function closeBulkSendModal() {
    document.getElementById('bulk-send-modal').style.display = 'none';
}

document.getElementById('bulk-send-modal').addEventListener('click', function(e) {
    if (e.target === this) closeBulkSendModal();
});

document.getElementById('bulk-send-form').onsubmit = function (e) {
    if (bulkSendConfirmed) return true;

    const value    = parseFloat(document.getElementById('bulk-modal-amount').value);
    const sendable = parseFloat(document.getElementById('bulk-modal-sendable').value);

    if (value > sendable) {
        e.preventDefault();
        document.getElementById('bulk-usdt-send-confirm-text').textContent =
            'Este envio está fora do fluxo normal: parte do valor não está coberta por lotes reservados e/ou pagamento total efetivado nas operações deste cliente. Deseja continuar mesmo assim?';
        document.getElementById('bulk-usdt-send-confirm-modal').style.display = 'flex';
        return false;
    }
};

function closeBulkUsdtSendConfirmModal() {
    document.getElementById('bulk-usdt-send-confirm-modal').style.display = 'none';
}

function confirmBulkUsdtSend() {
    bulkSendConfirmed = true;
    closeBulkUsdtSendConfirmModal();
    document.getElementById('bulk-send-form').submit();
}
</script>

<!-- Modal de Confirmação de Envio Fora do Fluxo (bulk) -->
<div id="bulk-usdt-send-confirm-modal" style="display:none; position:fixed; inset:0; z-index:9500; background:rgba(0,0,0,0.75); backdrop-filter:blur(6px); justify-content:center; align-items:center; padding:20px;">
    <div style="background:#1e293b; border:1px solid rgba(251,191,36,0.3); border-radius:24px; width:100%; max-width:460px; padding:32px; box-shadow:0 25px 60px rgba(0,0,0,0.5);">
        <div style="display:flex; gap:14px; align-items:flex-start; margin-bottom:20px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div>
                <h2 style="font-size:17px; font-weight:700; color:white; margin-bottom:6px;">Envio fora do fluxo normal</h2>
                <p id="bulk-usdt-send-confirm-text" style="font-size:13px; color:#94a3b8; line-height:1.6;"></p>
            </div>
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="closeBulkUsdtSendConfirmModal()" style="flex:1; padding:12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:12px; color:#94a3b8; font-size:14px; font-weight:600; cursor:pointer;">Cancelar</button>
            <button onclick="confirmBulkUsdtSend()" style="flex:1; padding:12px; background:#059669; border:none; border-radius:12px; color:white; font-size:14px; font-weight:700; cursor:pointer;">Concordo</button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
