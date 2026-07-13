<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<?php /** @var array $deliveries */ ?>
<?php /** @var array $client */ ?>

<!-- Breadcrumb -->
<div style="display:flex; align-items:center; gap:8px; margin-bottom:20px; font-size:13px; color:#64748b;">
    <a href="<?= url_to('admin_delivery') ?>" style="color:#64748b; text-decoration:none; display:inline-flex; align-items:center; gap:4px;"
       onmouseover="this.style.color='#94a3b8'" onmouseout="this.style.color='#64748b'">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Fila de Envio
    </a>
    <span style="color:#334155;">/</span>
    <span style="color:#94a3b8; font-weight:600;"><?= esc($client['login'] ?? '—') ?></span>
</div>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
    <div>
        <h1 style="font-size:22px; color:white; font-weight:700; margin:0 0 4px;"><?= esc($client['login'] ?? '—') ?></h1>
        <div style="font-size:12px; color:#64748b; display:flex; align-items:center; gap:8px;">
            <span>Operações ordenadas por maior lucro</span>
            <span style="display:inline-flex; align-items:center; gap:4px; color:#c084fc; background:rgba(192,132,252,0.1); border:1px solid rgba(192,132,252,0.2); padding:1px 8px; border-radius:20px; font-size:11px; font-weight:600;">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
                Lucro desc
            </span>
        </div>
    </div>
    <span style="font-size:13px; color:#94a3b8;"><?= count($deliveries) ?> operação(ões) pendente(s)</span>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div style="background:rgba(34,197,94,0.1); color:#4ade80; padding:12px 16px; border-radius:10px; margin-bottom:20px; border:1px solid rgba(34,197,94,0.2); font-size:13px;">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<?php $successModal = session()->getFlashdata('success_modal'); ?>
<?php if($successModal): ?>
    <div style="background:rgba(34,197,94,0.1); color:#4ade80; padding:12px 16px; border-radius:10px; margin-bottom:20px; border:1px solid rgba(34,197,94,0.2); font-size:13px;">
        Envio registrado com sucesso: <?= esc($successModal['value'] ?? '') ?>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div style="background:rgba(248,113,113,0.1); color:#f87171; padding:12px 16px; border-radius:10px; margin-bottom:20px; border:1px solid rgba(248,113,113,0.2); font-size:13px;">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<?php if(empty($deliveries)): ?>
    <div class="card" style="text-align:center; padding:60px 30px; color:#94a3b8;">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 14px; display:block; opacity:0.4;">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        <p style="font-size:15px; font-weight:600; color:#f8fafc; margin-bottom:6px;">Tudo em dia!</p>
        <p style="font-size:13px;">Não há operações pendentes para este cliente.</p>
    </div>
<?php else: ?>

<div class="card" style="padding:0; overflow-x:auto;">
    <table style="width:100%; min-width:900px; border-collapse:collapse; font-size:13px;">
        <thead>
            <tr style="background:rgba(0,0,0,0.25); border-bottom:1px solid rgba(255,255,255,0.07);">
                <th style="padding:8px 12px; text-align:left; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em; white-space:nowrap;">Prioridade</th>
                <th style="padding:8px 12px; text-align:left; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em;">Operação</th>
                <th style="padding:8px 12px; text-align:left; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em; white-space:nowrap;">Prazo</th>
                <th style="padding:8px 12px; text-align:right; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em; white-space:nowrap;">Lucro (R$)</th>
                <th style="padding:8px 12px; text-align:right; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em; white-space:nowrap;">Pago (R$)</th>
                <th style="padding:8px 12px; text-align:right; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em; white-space:nowrap;">A enviar (USDT)</th>
                <th style="padding:8px 12px; text-align:right; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em; white-space:nowrap;">Enviado / Total</th>
                <th style="padding:8px 12px; text-align:left; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em;">Carteira</th>
                <th style="padding:8px 12px; text-align:center; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.06em;">Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $rank = 0;
        foreach($deliveries as $d):
            $rank++;
            $today         = date('Y-m-d');
            $dueDate       = date('Y-m-d', strtotime($d['due_date']));
            $daysLate      = (int)round((strtotime($today) - strtotime($dueDate)) / 86400);

            $brlTotal      = (float)$d['total_brl'];
            $brlPaid       = (float)$d['paid_amount'];
            $usdtTotal     = (float)$d['total_amount'];
            $usdtDelivered = (float)$d['delivered_usdt'];
            $usdtToSend    = (float)$d['pending_usdt'];
            $feeBrl        = (float)($d['fee_brl'] ?? 0);
            $estProfit     = isset($d['est_profit_brl']) && $d['est_profit_brl'] !== null ? (float)$d['est_profit_brl'] : null;
            $unlinked      = (float)($d['unlinked_usdt'] ?? 0);
            $payPct        = $brlTotal > 0 ? min(100, ($brlPaid / $brlTotal) * 100) : 0;

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
                $priorityLabel = strtoupper($d['type']); $priorityColor = '#818cf8';
                $priorityBg = 'rgba(129,140,248,0.12)'; $priorityBorder = 'rgba(129,140,248,0.3)';
                $rowBorder = 'transparent';
            }

            $walletFull  = $d['requested_wallet'] ?: ($d['usdt_wallet'] ?? '');
            $walletShort = $walletFull ? (substr($walletFull, 0, 8).'…'.substr($walletFull, -6)) : '';
        ?>
        <tr style="border-bottom:1px solid rgba(255,255,255,0.05); border-left:3px solid <?= $rowBorder ?>; transition:background 0.15s;"
            onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background=''">

            <!-- Prioridade -->
            <td style="padding:8px 12px; white-space:nowrap;">
                <div style="display:flex; align-items:center; gap:5px;">
                    <span style="display:inline-block; padding:3px 9px; border-radius:6px; font-size:12px; font-weight:800; letter-spacing:-0.02em; color:<?= $priorityColor ?>; background:<?= $priorityBg ?>; border:1px solid <?= $priorityBorder ?>;">
                        <?= $priorityLabel ?>
                    </span>
                    <span style="font-size:10px; font-weight:700; color:#c084fc; background:rgba(192,132,252,0.1); border:1px solid rgba(192,132,252,0.2); padding:1px 5px; border-radius:10px;" title="Ordem de envio por lucro">#<?= $rank ?></span>
                </div>
            </td>

            <!-- Contrato -->
            <td style="padding:8px 12px; white-space:nowrap;">
                <span style="font-size:12px; color:#94a3b8; font-family:monospace; font-weight:600;">#<?= $d['id'] ?></span>
                <span style="font-size:10px; font-family:sans-serif; font-weight:700; color:#818cf8; background:rgba(129,140,248,0.1); padding:1px 5px; border-radius:3px; margin-left:4px;"><?= strtoupper($d['type']) ?></span>
            </td>

            <!-- Prazo -->
            <td style="padding:8px 12px; white-space:nowrap;">
                <div style="color:#94a3b8; font-size:12px;"><?= date('d/m/Y', strtotime($d['due_date'])) ?></div>
                <?php if($daysLate > 0): ?>
                    <div style="font-size:11px; color:<?= $priorityColor ?>; font-weight:600; margin-top:1px;"><?= $daysLate ?>d atraso</div>
                <?php elseif($daysLate === 0): ?>
                    <div style="font-size:11px; color:#38bdf8; font-weight:600; margin-top:1px;">Hoje</div>
                <?php else: ?>
                    <div style="font-size:11px; color:#475569; margin-top:1px;">em <?= abs($daysLate) ?>d</div>
                <?php endif; ?>
            </td>

            <!-- Lucro (R$) -->
            <td style="padding:8px 12px; text-align:right; white-space:nowrap;">
                <?php if($estProfit !== null): ?>
                    <div style="font-family:monospace; font-size:13px; font-weight:700; color:<?= $estProfit >= 0 ? '#fbbf24' : '#f87171' ?>;" title="Lucro estimado vs custo do lote reservado"><?= number_format($estProfit, 2, ',', '.') ?></div>
                    <div style="font-size:10px; color:#475569; margin-top:1px;">vs lote</div>
                <?php elseif($feeBrl > 0): ?>
                    <div style="font-family:monospace; font-size:13px; font-weight:600; color:#94a3b8;" title="Spread do cliente (sem lote reservado para comparar custo)"><?= number_format($feeBrl, 2, ',', '.') ?></div>
                    <div style="font-size:10px; color:#475569; margin-top:1px;">spread</div>
                <?php else: ?>
                    <div style="font-size:12px; color:#475569;">—</div>
                <?php endif; ?>
            </td>

            <!-- Pago (R$) -->
            <td style="padding:8px 12px; text-align:right; white-space:nowrap;">
                <div style="font-family:monospace; font-size:13px; font-weight:700; color:#4ade80;"><?= number_format($brlPaid, 2, ',', '.') ?></div>
                <div style="background:rgba(255,255,255,0.06); border-radius:3px; height:3px; overflow:hidden; margin-top:4px; min-width:55px;">
                    <div style="width:<?= round($payPct, 1) ?>%; height:100%; background:linear-gradient(90deg,#4ade80,#22d3ee); border-radius:3px;"></div>
                </div>
                <div style="font-size:10px; color:#475569; margin-top:2px;"><?= number_format($brlTotal, 2, ',', '.') ?> · <?= number_format($payPct, 0) ?>%</div>
            </td>

            <!-- A enviar (USDT) -->
            <td style="padding:8px 12px; text-align:right; white-space:nowrap;">
                <?php if($usdtToSend > 0): ?>
                    <div style="font-family:monospace; font-size:14px; font-weight:800; color:#c084fc;"><?= number_format($usdtToSend, 2, '.', ',') ?></div>
                    <?php if($unlinked > 0): ?>
                        <div style="margin-top:3px; display:inline-flex; align-items:center; gap:3px; font-size:10px; font-weight:700; color:#fb923c; background:rgba(251,146,60,0.12); border:1px solid rgba(251,146,60,0.3); padding:1px 6px; border-radius:4px;" title="USDT sem lote de fornecedor associado">
                            ⚠ <?= number_format($unlinked, 2, '.', ',') ?> sem lote
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div style="font-size:13px; color:#4ade80; font-weight:600;">—</div>
                <?php endif; ?>
            </td>

            <!-- Enviado / Total USDT -->
            <td style="padding:8px 12px; text-align:right; white-space:nowrap;">
                <div style="font-family:monospace; font-size:12px; font-weight:600; color:#34d399;"><?= number_format($usdtDelivered, 2, '.', ',') ?></div>
                <div style="font-size:10px; color:#475569; margin-top:1px;"><?= number_format($usdtTotal, 2, '.', ',') ?></div>
            </td>

            <!-- Carteira -->
            <td style="padding:8px 12px;">
                <?php if($walletFull): ?>
                    <div style="display:flex; align-items:center; gap:5px;">
                        <span style="font-family:monospace; font-size:11px; color:#818cf8; white-space:nowrap;" title="<?= esc($walletFull) ?>"><?= esc($walletShort) ?></span>
                        <button onclick="navigator.clipboard.writeText('<?= esc($walletFull) ?>').then(()=>{ this.title='Copiado!'; setTimeout(()=>this.title='Copiar',1500) })"
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
            <td style="padding:8px 12px; text-align:center; white-space:nowrap;">
                <!-- Enviar USDT — botão primário -->
                <button type="button"
                    onclick="openSendModal(<?= $d['id'] ?>, <?= max(0, $usdtTotal - $usdtDelivered) ?>, <?= $usdtToSend ?>, <?= (float)($d['lot_reserved'] ?? 0) ?>, '<?= $d['id'] ?>', '<?= esc(addslashes($walletShort)) ?>')"
                    style="display:inline-flex; align-items:center; gap:4px; background:rgba(192,132,252,0.15); color:#c084fc; padding:5px 11px; font-size:12px; border-radius:6px; font-weight:600; border:1px solid rgba(192,132,252,0.3); cursor:pointer; white-space:nowrap; margin-bottom:4px;"
                    onmouseover="this.style.background='rgba(192,132,252,0.25)'" onmouseout="this.style.background='rgba(192,132,252,0.15)'">
                    Enviar
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
                <!-- Ver contrato + Bloquear — linha secundária -->
                <div style="display:flex; align-items:center; gap:4px; justify-content:center;">
                    <a href="<?= url_to('admin_contracts_show', $d['id']) ?>" target="_blank" rel="noopener" title="Ver operação"
                       style="display:inline-flex; align-items:center; justify-content:center; background:rgba(99,102,241,0.1); color:#818cf8; padding:4px 8px; font-size:11px; border-radius:5px; text-decoration:none; font-weight:600; border:1px solid rgba(99,102,241,0.22); white-space:nowrap;"
                       onmouseover="this.style.background='rgba(99,102,241,0.2)'" onmouseout="this.style.background='rgba(99,102,241,0.1)'">
                        Ver ↗
                    </a>
                    <form method="post" action="<?= url_to('admin_delivery_block', $d['id']) ?>" style="margin:0;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="redirect" value="<?= esc(current_url()) ?>">
                        <button type="submit"
                            onclick="return confirm('Bloquear o envio da operação #<?= $d['id'] ?>?')"
                            style="background:rgba(248,113,113,0.08); color:#f87171; padding:4px 8px; font-size:11px; border-radius:5px; font-weight:600; border:1px solid rgba(248,113,113,0.18); cursor:pointer; white-space:nowrap;"
                            onmouseover="this.style.background='rgba(248,113,113,0.18)'" onmouseout="this.style.background='rgba(248,113,113,0.08)'">
                            Bloquear
                        </button>
                    </form>
                </div>
            </td>

        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php endif; ?>

<!-- Send USDT Modal -->
<div id="send-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9000; align-items:center; justify-content:center;">
    <div style="background:#1e293b; border:1px solid rgba(255,255,255,0.1); border-radius:16px; padding:28px 32px; width:420px; max-width:92vw; box-shadow:0 24px 64px rgba(0,0,0,0.5);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
            <h3 id="modal-title" style="color:white; font-size:16px; font-weight:700; margin:0;">Enviar USDT</h3>
            <button onclick="closeSendModal()" style="background:none; border:none; color:#64748b; cursor:pointer; font-size:20px; line-height:1; padding:0 2px;"
                onmouseover="this.style.color='#94a3b8'" onmouseout="this.style.color='#64748b'">&times;</button>
        </div>

        <div id="modal-wallet-row" style="display:none; align-items:center; gap:8px; margin-bottom:16px; padding:8px 12px; background:rgba(129,140,248,0.07); border:1px solid rgba(129,140,248,0.15); border-radius:8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
            <span id="modal-wallet-text" style="font-family:monospace; font-size:12px; color:#818cf8;"></span>
        </div>

        <form method="post" id="send-form">
            <?= csrf_field() ?>
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; font-weight:600; color:#94a3b8; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">Quantidade USDT</label>
                <input type="number" name="amount_usdt" id="modal-amount" step="0.01" min="0.01"
                    style="width:100%; background:#0f172a; border:1px solid rgba(255,255,255,0.12); border-radius:8px; padding:10px 14px; color:white; font-size:15px; font-family:monospace; font-weight:700; box-sizing:border-box; outline:none;"
                    onfocus="this.style.borderColor='#c084fc'" onblur="this.style.borderColor='rgba(255,255,255,0.12)'">
                <div id="modal-max-hint" style="font-size:11px; color:#64748b; margin-top:4px;"></div>
            </div>
            <input type="hidden" id="modal-pending" value="0">
            <input type="hidden" id="modal-lot-reserved" value="0">
            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:12px; font-weight:600; color:#94a3b8; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">Hash da Transação *</label>
                <input type="text" name="notes" id="modal-notes" placeholder="Ex: Hash da rede TRC-20..." required
                    style="width:100%; background:#0f172a; border:1px solid rgba(255,255,255,0.12); border-radius:8px; padding:10px 14px; color:#cbd5e1; font-size:13px; box-sizing:border-box; outline:none;"
                    onfocus="this.style.borderColor='#818cf8'" onblur="this.style.borderColor='rgba(255,255,255,0.12)'">
            </div>
            <div style="display:flex; gap:10px;">
                <button type="button" onclick="closeSendModal()"
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
let usdtSendConfirmed = false;

function openSendModal(contractId, absoluteMax, pending, lotReserved, contractLabel, wallet) {
    document.getElementById('modal-title').textContent = 'Enviar USDT — Operação #' + contractLabel;
    var amountInput = document.getElementById('modal-amount');
    amountInput.value = Math.min(pending, absoluteMax).toFixed(2);
    amountInput.max   = absoluteMax;
    document.getElementById('modal-max-hint').textContent = 'Máx: ' + absoluteMax.toFixed(2).replace('.', ',') + ' USDT';
    document.getElementById('modal-notes').value = '';
    document.getElementById('modal-pending').value = pending;
    document.getElementById('modal-lot-reserved').value = lotReserved;
    document.getElementById('send-form').action = '<?= site_url('admin/contracts/deliver-usdt/') ?>' + contractId;
    usdtSendConfirmed = false;

    var walletRow = document.getElementById('modal-wallet-row');
    if (wallet) {
        document.getElementById('modal-wallet-text').textContent = wallet;
        walletRow.style.display = 'flex';
    } else {
        walletRow.style.display = 'none';
    }

    var modal = document.getElementById('send-modal');
    modal.style.display = 'flex';
}

function closeSendModal() {
    document.getElementById('send-modal').style.display = 'none';
}

document.getElementById('send-modal').addEventListener('click', function(e) {
    if (e.target === this) closeSendModal();
});

document.getElementById('send-form').onsubmit = function (e) {
    if (usdtSendConfirmed) return true;

    const value        = parseFloat(document.getElementById('modal-amount').value);
    const pending       = parseFloat(document.getElementById('modal-pending').value);
    const lotReserved   = parseFloat(document.getElementById('modal-lot-reserved').value);

    if (value > lotReserved || value > pending) {
        e.preventDefault();
        const reasons = [];
        if (value > lotReserved) reasons.push('não há lotes reservados suficientes para cobrir esse valor');
        if (value > pending) reasons.push('o pagamento total desta operação ainda não foi efetivado');
        document.getElementById('usdt-send-confirm-text').textContent =
            'Este envio está fora do fluxo normal: ' + reasons.join(' e ') + '. Deseja continuar mesmo assim?';
        document.getElementById('usdt-send-confirm-modal').style.display = 'flex';
        return false;
    }
};

function closeUsdtSendConfirmModal() {
    document.getElementById('usdt-send-confirm-modal').style.display = 'none';
}

function confirmUsdtSend() {
    usdtSendConfirmed = true;
    closeUsdtSendConfirmModal();
    document.getElementById('send-form').submit();
}
</script>

<!-- Modal de Confirmação de Envio Fora do Fluxo -->
<div id="usdt-send-confirm-modal" style="display:none; position:fixed; inset:0; z-index:9500; background:rgba(0,0,0,0.75); backdrop-filter:blur(6px); justify-content:center; align-items:center; padding:20px;">
    <div style="background:#1e293b; border:1px solid rgba(251,191,36,0.3); border-radius:24px; width:100%; max-width:460px; padding:32px; box-shadow:0 25px 60px rgba(0,0,0,0.5);">
        <div style="display:flex; gap:14px; align-items:flex-start; margin-bottom:20px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div>
                <h2 style="font-size:17px; font-weight:700; color:white; margin-bottom:6px;">Envio fora do fluxo normal</h2>
                <p id="usdt-send-confirm-text" style="font-size:13px; color:#94a3b8; line-height:1.6;"></p>
            </div>
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="closeUsdtSendConfirmModal()" style="flex:1; padding:12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:12px; color:#94a3b8; font-size:14px; font-weight:600; cursor:pointer;">Cancelar</button>
            <button onclick="confirmUsdtSend()" style="flex:1; padding:12px; background:#059669; border:none; border-radius:12px; color:white; font-size:14px; font-weight:700; cursor:pointer;">Concordo</button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
