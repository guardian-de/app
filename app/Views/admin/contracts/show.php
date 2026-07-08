<?php
/** @var array $c @var array $history @var array|null $clientProof @var float $totalReservedUsdt @var float $usdtPending @var float $unlinkedDelivered @var array $availableLots @var float $currentBaseRate @var array $suppliers @var array $contractAllocations */
?>
<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="header">
    <div style="display: flex; align-items: center; gap: 15px;">
        <a href="<?= url_to('admin_contracts') ?>" class="btn"
            style="background: rgba(255,255,255,0.05); color: #94a3b8; padding: 8px 12px;">← Voltar</a>
        <h1 style="font-size: 24px; color: white;">Detalhes da Operação #<?= $c['id'] ?></h1>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div style="background: rgba(34, 197, 94, 0.1); color: #4ade80; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(34, 197, 94, 0.2); font-size: 14px;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div style="background: rgba(239, 68, 68, 0.1); color: #f87171; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(239, 68, 68, 0.2); font-size: 14px;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
    <!-- Detalhes da Operação -->
    <div class="card">
        <h3 style="margin-bottom: 20px; color: #6366f1;">Informações da Operação</h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Cliente</p>
                <p style="font-size: 18px; font-weight: 700; color: white;"><?= $c['user_name'] ?></p>
            </div>
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Tipo de Operação (Prazo)</p>
                <form action="<?= url_to('admin_contracts_change_delivery_type', $c['id']) ?>" method="POST" style="margin: 0;">
                    <?= csrf_field() ?>
                    <select name="delivery_type" onchange="this.form.submit()" style="background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; color: #60a5fa; font-size: 15px; font-weight: 700; padding: 6px 12px; outline: none; cursor: pointer; transition: all 0.2s; min-width: 100px;">
                        <option value="d+0" <?= strtolower($c['type']) === 'd+0' ? 'selected' : '' ?>>D+0</option>
                        <option value="d+1" <?= strtolower($c['type']) === 'd+1' ? 'selected' : '' ?>>D+1</option>
                        <option value="d+2" <?= strtolower($c['type']) === 'd+2' ? 'selected' : '' ?>>D+2</option>
                    </select>
                </form>
            </div>
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Valor em USDT</p>
                <p style="font-size: 18px; font-weight: 700; color: #34d399;">
                    <?= number_format($c['total_amount'], 2, '.', ',') ?> USDT</p>
            </div>
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Spot da Compra</p>
                <p style="font-size: 18px; font-weight: 700; color: white;">
                    <?= !empty($clientProof['base_rate']) ? 'R$ ' . number_format($clientProof['base_rate'], 4, ',', '.') : '—' ?></p>
            </div>
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">USDT Enviado ao Cliente</p>
                <p style="font-size: 18px; font-weight: 700; color: #60a5fa;">
                    <?= number_format($c['delivered_usdt'], 2, '.', ',') ?> USDT</p>
                <div style="font-size: 11px; color: #f87171;">Faltando:
                    <?= number_format($c['total_amount'] - $c['delivered_usdt'], 2, '.', ',') ?> USDT</div>
            </div>
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Data de Vencimento</p>
                <p style="font-size: 18px; font-weight: 700; color: white;">
                    <?= date('d/m/Y', strtotime($c['due_date'])) ?></p>
            </div>
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Saldo Devedor BRL</p>
                <p style="font-size: 24px; font-weight: 800; color: #f87171;">R$
                    <?= number_format($c['remaining_balance'], 2, ',', '.') ?></p>
                <?php if ($c['interest_accumulated'] > 0): ?>
                    <div style="font-size: 12px; color: #fbbf24;">(Incluso R$
                        <?= number_format($c['interest_accumulated'], 2, ',', '.') ?> de juros)</div>
                <?php endif; ?>
            </div>
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Total Pago BRL</p>
                <p style="font-size: 18px; font-weight: 700; color: #4ade80;">R$
                    <?= number_format($c['paid_amount'], 2, ',', '.') ?></p>
            </div>
            <div style="grid-column: span 2;">
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Carteira do Cliente (TRC-20)</p>
                <p
                    style="font-size: 14px; font-family: monospace; color: #818cf8; word-break: break-all; background: rgba(129, 140, 248, 0.05); padding: 10px; border-radius: 8px; border: 1px dashed rgba(129, 140, 248, 0.2);">
                    <?= $c['usdt_wallet'] ?: 'Não informada' ?>
                </p>
            </div>
        </div>

        <?php if ($c['remaining_balance'] <= 0): ?>
            <div
                style="margin-top: 30px; padding: 20px; border-radius: 16px; background: rgba(74, 222, 128, 0.05); border: 1px solid rgba(74, 222, 128, 0.2); text-align: center;">
                <p style="color: #4ade80; font-weight: 700; font-size: 14px;">✓ O saldo devedor desta operação já está quitado.</p>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.05);">
            <!-- Título + botões na mesma linha -->
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:20px;">
                <h3 style="color: #34d399; margin:0;">Registrar Envio de USDT</h3>
                <?php $canLinkLot = $c['delivered_usdt'] < $c['total_amount'] || $unlinkedDelivered > 0; ?>
                <?php if ($canLinkLot): ?>
                <div style="display:flex;gap:10px;">
                    <button onclick="openLotModal()"
                        style="padding:8px 16px;background:rgba(59,130,246,0.12);border:1px solid rgba(59,130,246,0.3);border-radius:10px;color:#60a5fa;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;">
                        🔗 Vincular Lote
                    </button>
                    <button onclick="openQuickBuyModal()"
                        style="padding:8px 16px;background:rgba(251,191,36,0.1);border:1px solid rgba(251,191,36,0.3);border-radius:10px;color:#fbbf24;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;">
                        ⚡ Compra Rápida
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($c['delivered_usdt'] < $c['total_amount']): ?>
                <?php if ($totalReservedUsdt <= 0): ?>
                    <div style="padding:14px 18px;border-radius:12px;background:rgba(251,191,36,0.07);border:1px solid rgba(251,191,36,0.25);margin-bottom:16px;font-size:13px;color:#fbbf24;display:flex;gap:10px;align-items:center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Nenhum lote reservado para esta operação. É possível registrar o envio mesmo assim, mas ele ficará sem lote associado.
                    </div>
                <?php elseif ($totalReservedUsdt < $usdtPending): ?>
                    <div style="padding:14px 18px;border-radius:12px;background:rgba(251,191,36,0.07);border:1px solid rgba(251,191,36,0.25);margin-bottom:16px;font-size:13px;color:#fbbf24;display:flex;gap:10px;align-items:center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Apenas <strong style="margin:0 4px;"><?= number_format($totalReservedUsdt, 2, '.', ',') ?> USDT</strong> reservados em lotes para esta operação. Enviar mais que isso deixará parte do envio sem lote associado.
                    </div>
                <?php endif; ?>
                <div style="display:flex;gap:20px;padding:12px 16px;border-radius:12px;background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.2);margin-bottom:16px;font-size:13px;">
                    <span style="color:#94a3b8;">Pago: <strong style="color:#4ade80;">R$ <?= number_format($c['paid_amount'], 2, ',', '.') ?></strong></span>
                    <span style="color:#94a3b8;">A enviar: <strong style="color:<?= $usdtPending > 0 ? '#f87171' : '#34d399' ?>;"><?= number_format($usdtPending, 2, '.', ',') ?> USDT</strong></span>
                </div>
                <form action="<?= url_to('admin_contracts_deliver_usdt', $c['id']) ?>" method="POST"
                    style="display: flex; flex-direction: column; gap: 15px;">
                    <?= csrf_field() ?>
                    <div style="display: grid; grid-template-columns: 1fr 150px; gap: 15px; align-items: flex-end;">
                        <div style="flex: 1;">
                            <label for="usdt-pay-input" style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 8px;">Valor USDT Enviado</label>
                            <input type="number" name="amount_usdt" step="0.01" id="usdt-pay-input"
                                placeholder="Ex: <?= number_format($usdtPending, 2, '.', '') ?>"
                                max="<?= max(0, (float)($c['total_amount'] - $c['delivered_usdt'])) ?>"
                                style="width: 100%; padding: 12px; background: #0f172a; border: 1px solid #334155; border-radius: 12px; color: white;"
                                required>
                        </div>
                        <button type="button"
                            onclick="document.getElementById('usdt-pay-input').value = '<?= $usdtPending ?>'"
                            class="btn"
                            style="background: rgba(52, 211, 153, 0.1); color: #34d399; border: 1px solid #34d399; height: 45px; width: 100%; justify-content: center;">Total</button>
                    </div>
                    <div>
                        <label for="usdt-deliver-notes" style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 8px;">Hash da Transação *</label>
                        <input type="text" name="notes" id="usdt-deliver-notes" placeholder="Ex: Hash da rede TRC-20..." required
                            style="width: 100%; padding: 12px; background: #0f172a; border: 1px solid #334155; border-radius: 12px; color: white;">
                    </div>
                    <button type="submit" class="btn"
                        style="background: #059669; color: white; padding: 12px 30px; height: 50px; font-size: 16px; width: 100%; border-radius: 12px; font-weight: 700; cursor: pointer;">Confirmar Envio USDT</button>
                </form>
            <?php else: ?>
                <div style="padding: 20px; border-radius: 16px; background: rgba(52, 211, 153, 0.05); border: 1px solid rgba(52, 211, 153, 0.2); text-align: center; margin-bottom: 16px;">
                    <p style="color: #34d399; font-weight: 700; font-size: 14px;">✓ Todo o USDT desta operação já foi enviado.</p>
                </div>
                <?php if ($unlinkedDelivered > 0): ?>
                    <div style="padding:16px 18px;border-radius:12px;background:rgba(248,113,113,0.1);border:1px solid rgba(248,113,113,0.4);margin-bottom:16px;font-size:13px;color:#f87171;display:flex;gap:12px;align-items:flex-start;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <div>
                            <p style="font-weight:700;margin-bottom:2px;">Atenção: custo de fornecedor pendente</p>
                            <p style="color:#fca5a5;"><strong style="color:#f87171;"><?= number_format($unlinkedDelivered, 2, '.', ',') ?> USDT</strong> foram enviados sem lote associado — o custo dessa compra ainda não está registrado, o que distorce o lucro real desta operação. Use <strong style="color:#60a5fa;">Vincular Lote</strong> ou <strong style="color:#fbbf24;">Compra Rápida</strong> acima para regularizar.</p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Lotes vinculados (sempre visível) -->
            <?php if (!empty($contractAllocations)): ?>
                <div style="margin-top:20px;display:flex;flex-direction:column;gap:8px;">
                    <?php foreach ($contractAllocations as $alloc): ?>
                        <?php
                            $statusColor = match($alloc['status']) {
                                'delivered' => '#34d399',
                                'cancelled' => '#64748b',
                                default     => '#60a5fa',
                            };
                            $statusLabel = match($alloc['status']) {
                                'delivered' => 'Entregue',
                                'cancelled' => 'Cancelado',
                                default     => 'Reservado',
                            };
                        ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:12px;">
                            <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
                                <span style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:700;background:<?= $statusColor ?>22;color:<?= $statusColor ?>;white-space:nowrap;"><?= $statusLabel ?></span>
                                <div>
                                    <p style="font-size:13px;font-weight:600;color:white;margin:0;"><?= esc($alloc['supplier']) ?> — Lote #<?= $alloc['lot_id'] ?></p>
                                    <p style="font-size:11px;color:#64748b;margin:2px 0 0;">
                                        <?= number_format((float)$alloc['usdt_amount'], 4, ',', '.') ?> USDT
                                        <?php if ($alloc['delivery_type']): ?> · <?= strtoupper($alloc['delivery_type']) ?><?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <?php if ($alloc['status'] === 'reserved'): ?>
                                <button onclick="cancelAllocation(<?= (int)$alloc['id'] ?>, this)"
                                    style="padding:6px 12px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);border-radius:8px;color:#f87171;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;">
                                    Remover
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Barra Lateral -->
    <div>
        <div class="card" style="padding: 20px;">
            <h3 style="margin-bottom: 15px; font-size: 16px; color: #94a3b8;">Status Atual</h3>
            <div style="padding: 10px; border-radius: 12px; text-align: center; font-weight: 800; text-transform: uppercase; margin-bottom: 20px; 
                <?php
                $status_color = '#94a3b8';
                $status_bg = 'rgba(148, 163, 184, 0.1)';
                if ($c['status'] == 'paid') {
                    $status_color = '#4ade80';
                    $status_bg = 'rgba(34, 197, 94, 0.1)';
                } elseif ($c['status'] == 'overdue') {
                    $status_color = '#f87171';
                    $status_bg = 'rgba(248, 113, 113, 0.1)';
                } elseif ($c['status'] == 'partially_paid') {
                    $status_color = '#60a5fa';
                    $status_bg = 'rgba(96, 165, 250, 0.1)';
                }
                ?>
                background: <?= $status_bg ?>; color: <?= $status_color ?>;">
                <?= $c['status'] ?>
            </div>

            <p style="font-size: 12px; color: #94a3b8; line-height: 1.5;">
                O pagamento é aplicado automaticamente ao aceitar um depósito do cliente.
            </p>
        </div>

        <?php if ($clientProof && !empty($clientProof['proof_path'])): ?>
            <div class="card" style="margin-top: 20px; padding: 20px; border: 1px solid rgba(34, 197, 94, 0.2);">
                <h3 style="margin-bottom: 15px; font-size: 16px; color: #34d399;"><i class="fas fa-file-invoice-dollar"
                        style="margin-right: 8px;"></i> Comprovante do Cliente</h3>

                <?php
                $ext = pathinfo($clientProof['proof_path'], PATHINFO_EXTENSION);
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])):
                    ?>
                    <a href="<?= base_url($clientProof['proof_path']) ?>" target="_blank">
                        <img src="<?= base_url($clientProof['proof_path']) ?>"
                            style="max-width: 100%; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                    </a>
                <?php else: ?>
                    <a href="<?= base_url($clientProof['proof_path']) ?>" target="_blank" class="btn"
                        style="background: #3b82f6; color: white; width: 100%; justify-content: center; font-size: 12px;">
                        Ver PDF Anexo
                    </a>
                <?php endif; ?>

                <?php if ($clientProof['proof_text']): ?>
                    <div
                        style="margin-top: 12px; padding: 10px; background: rgba(255,255,255,0.03); border-radius: 8px; border-left: 3px solid #6366f1;">
                        <p style="font-size: 10px; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;">Nota do
                            Cliente:</p>
                        <p style="color: #cbd5e1; font-size: 13px; line-height: 1.4;">
                            <?= nl2br(esc($clientProof['proof_text'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($clientProof['text_read']): ?>
                    <div style="margin-top: 12px;">
                        <details>
                            <summary style="font-size: 11px; color: #818cf8; cursor: pointer;">Ver Dados Extraídos (OCR)
                            </summary>
                            <div
                                style="margin-top: 8px; padding: 8px; background: rgba(0,0,0,0.2); border-radius: 6px; font-size: 11px; color: #94a3b8; font-family: monospace; white-space: pre-wrap;">
                                <?= esc($clientProof['text_read']) ?></div>
                        </details>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="card" style="margin-top: 20px; padding: 20px;">
            <h3 style="margin-bottom: 15px; font-size: 16px; color: #94a3b8;">Histórico da Operação</h3>

            <?php if (empty($history)): ?>
                <p style="color: #64748b; font-size: 13px;">Nenhuma movimentação registrada.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($history as $h): ?>
                        
                        <div
                            style="border-left: 2px solid <?= $h['nature'] == 'C' ? '#34d399' : '#f87171' ?>; padding-left: 12px; margin-bottom: 15px; background: rgba(255,255,255,0.02); padding: 10px; border-radius: 0 12px 12px 0;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div style="display: flex; flex-direction: column;">
                                    <span
                                        style="font-size: 11px; color: #64748b;"><?= date('d/m H:i', strtotime($h['transaction_date'])) ?></span>
                                    <?php if ($h['admin_name']): ?>
                                        <span style="font-size: 10px; color: #818cf8; font-weight: 600;">Op:
                                            <?= $h['admin_name'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <span
                                    style="font-size: 12px; font-weight: 700; color: <?= $h['nature'] == 'C' ? '#34d399' : '#f87171' ?>;">
                                    <?php if ($h['operation_type'] === 'withdrawal'): ?>
                                        <?= $h['nature'] == 'C' ? '+' : '-' ?> <?= number_format($h['amount'], 2, '.', ',') ?> USDT
                                    <?php else: ?>
                                        <?= $h['nature'] == 'C' ? '+' : '-' ?> R$ <?= number_format($h['amount'], 2, ',', '.') ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <p style="font-size: 12px; color: #cbd5e1; margin-top: 2px; font-weight: 600;">
                                <?= $h['description'] ?></p>

                            <?php if ($h['payment_method']): ?>
                                <div style="font-size: 10px; color: #94a3b8; margin-top: 5px;">
                                    <span
                                        style="background: rgba(99, 102, 241, 0.1); color: #818cf8; padding: 2px 6px; border-radius: 4px;"><?= $h['payment_method'] ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($h['notes']): ?>
                                <p
                                    style="font-size: 11px; color: #94a3b8; margin-top: 5px; font-style: italic; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 5px;">
                                    "<?= $h['notes'] ?>"
                                </p>
                            <?php endif; ?>

                            <?php if ($h['attachment']): ?>
                                <a href="<?= base_url('uploads/payments/' . $h['attachment']) ?>" target="_blank"
                                    style="display: inline-flex; align-items: center; gap: 4px; font-size: 10px; color: #60a5fa; margin-top: 8px; text-decoration: none; border-bottom: 1px dashed #60a5fa;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48">
                                        </path>
                                    </svg>
                                    Ver Comprovante
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 24px;
        padding: 30px;
        backdrop-filter: blur(10px);
    }
</style>

<script>
    // Validação Envio USDT
    const usdtForm = document.querySelector('form[action*="deliver-usdt"]');
    const usdtInput = document.getElementById('usdt-pay-input');
    const maxUsdt = <?= (float) ($c['total_amount'] - $c['delivered_usdt']) ?>;
    const usdtReservedLots = <?= (float) $totalReservedUsdt ?>;
    const usdtPaidPending = <?= (float) $usdtPending ?>;
    let usdtSendConfirmed = false;

    if (usdtForm) {
        usdtForm.onsubmit = function (e) {
            const value = parseFloat(usdtInput.value);
            if (value > maxUsdt) {
                e.preventDefault();
                alert('O valor do envio (' + value.toFixed(2) + ' USDT) não pode ser superior ao saldo restante ( ' + maxUsdt.toFixed(2) + ' USDT).');
                return false;
            }
            if (!usdtSendConfirmed && (value > usdtReservedLots || value > usdtPaidPending)) {
                e.preventDefault();
                const reasons = [];
                if (value > usdtReservedLots) reasons.push('não há lotes reservados suficientes para cobrir esse valor');
                if (value > usdtPaidPending) reasons.push('o pagamento total desta operação ainda não foi efetivado');
                document.getElementById('usdt-send-confirm-text').textContent =
                    'Este envio está fora do fluxo normal: ' + reasons.join(' e ') + '. Deseja continuar mesmo assim?';
                document.getElementById('usdt-send-confirm-modal').style.display = 'flex';
                return false;
            }
        };
    }

    function closeUsdtSendConfirmModal() {
        document.getElementById('usdt-send-confirm-modal').style.display = 'none';
    }

    function confirmUsdtSend() {
        usdtSendConfirmed = true;
        closeUsdtSendConfirmModal();
        usdtForm.submit();
    }

    // Alocação de lotes
    const allocCsrfName = '<?= csrf_token() ?>';
    let allocCsrfHash   = '<?= csrf_hash() ?>';

    // Dados para o modal — passados do PHP
    const contractRevenuePerUsdt = <?= ($c['total_amount'] > 0 && $c['comercial_brl'] > 0) ? round((float)$c['comercial_brl'] / (float)$c['total_amount'], 6) : 0 ?>;
    const contractRemainingToAllocate = <?= max(0, (float)$c['total_amount'] - (float)$c['delivered_usdt'] - ($totalReservedUsdt ?? 0)) ?>;
    const contractUnlinkedDelivered = <?= (float)($unlinkedDelivered ?? 0) ?>;
    const availableLots = <?= json_encode(array_map(fn($l) => [
        'id'            => (int)$l['id'],
        'supplier'      => $l['supplier'],
        'usdt_amount'   => (float)$l['usdt_amount'],
        'usdt_available'=> (float)$l['usdt_available'],
        'conversion_rate'=> (float)$l['conversion_rate'],
        'total_brl'     => (float)$l['total_brl'],
        'cost_per_usdt' => $l['usdt_amount'] > 0 ? round((float)$l['total_brl'] / (float)$l['usdt_amount'], 6) : 0,
    ], $availableLots ?? [])) ?>.filter(l => l.usdt_available > 0);

    let selectedLot = null;

    function openLotModal() {
        selectedLot = null;
        renderStep1();
        document.getElementById('lot-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeLotModal() {
        document.getElementById('lot-modal').style.display = 'none';
        document.body.style.overflow = '';
        selectedLot = null;
    }

    function renderStep1() {
        const body = document.getElementById('lot-modal-body');
        let cards = availableLots.map(lot => {
            const pctUsed = lot.usdt_amount > 0 ? Math.round((lot.usdt_amount - lot.usdt_available) / lot.usdt_amount * 100) : 0;
            const margin = contractRevenuePerUsdt > 0 ? contractRevenuePerUsdt - lot.cost_per_usdt : null;
            const marginHtml = margin !== null
                ? `<span style="font-size:12px;padding:3px 10px;border-radius:20px;background:${margin > 0 ? 'rgba(74,222,128,0.12)' : 'rgba(248,113,113,0.12)'};color:${margin > 0 ? '#4ade80' : '#f87171'};font-weight:700;">
                    ${margin > 0 ? '+' : ''}R$ ${margin.toFixed(4).replace('.', ',')} / USDT
                   </span>`
                : '';
            return `
            <div style="padding:20px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:16px;cursor:pointer;transition:all 0.15s;"
                 onmouseover="this.style.borderColor='rgba(59,130,246,0.4)';this.style.background='rgba(59,130,246,0.05)'"
                 onmouseout="this.style.borderColor='rgba(255,255,255,0.07)';this.style.background='rgba(255,255,255,0.03)'"
                 onclick="selectLot(${lot.id})">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;">
                    <div>
                        <p style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:3px;">Lote #${lot.id}</p>
                        <p style="font-size:17px;font-weight:700;color:white;">${lot.supplier}</p>
                    </div>
                    ${marginHtml}
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <p style="font-size:10px;color:#64748b;text-transform:uppercase;margin-bottom:3px;">Disponível</p>
                        <p style="font-size:15px;font-weight:700;color:#34d399;">${lot.usdt_available.toFixed(2)} USDT</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#64748b;text-transform:uppercase;margin-bottom:3px;">Taxa de Custo</p>
                        <p style="font-size:15px;font-weight:700;color:white;">R$ ${lot.cost_per_usdt.toFixed(4).replace('.', ',')}</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#64748b;text-transform:uppercase;margin-bottom:3px;">Total do Lote</p>
                        <p style="font-size:15px;font-weight:700;color:#94a3b8;">${lot.usdt_amount.toFixed(2)} USDT</p>
                    </div>
                </div>
                <div style="height:4px;background:rgba(255,255,255,0.06);border-radius:4px;overflow:hidden;">
                    <div style="height:100%;width:${pctUsed}%;background:linear-gradient(90deg,#3b82f6,#6366f1);border-radius:4px;transition:width 0.4s;"></div>
                </div>
                <p style="font-size:10px;color:#64748b;margin-top:4px;">${pctUsed}% utilizado</p>
            </div>`;
        }).join('');

        body.innerHTML = `
            <div style="margin-bottom:24px;">
                <h2 style="font-size:20px;font-weight:700;color:white;margin-bottom:4px;">Selecionar Lote</h2>
                <p style="font-size:13px;color:#64748b;">Escolha o lote para vincular a esta operação</p>
            </div>
            <div style="display:flex;flex-direction:column;gap:12px;max-height:420px;overflow-y:auto;padding-right:4px;">
                ${cards || '<p style="color:#64748b;text-align:center;padding:30px;">Nenhum lote disponível.</p>'}
            </div>`;
    }

    function selectLot(id) {
        selectedLot = availableLots.find(l => l.id === id);
        if (!selectedLot) return;
        renderStep2();
    }

    function isRetroactiveAllocation() {
        return contractRemainingToAllocate <= 0 && contractUnlinkedDelivered > 0;
    }

    function allocationCeiling() {
        return isRetroactiveAllocation() ? contractUnlinkedDelivered : contractRemainingToAllocate;
    }

    function renderStep2() {
        const lot = selectedLot;
        const ceiling = allocationCeiling();
        const retro = isRetroactiveAllocation();
        const maxAlloc = ceiling > 0
            ? Math.min(lot.usdt_available, ceiling)
            : lot.usdt_available;
        const body = document.getElementById('lot-modal-body');
        body.innerHTML = `
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
                <button onclick="renderStep1()" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:#94a3b8;border-radius:10px;padding:7px 14px;cursor:pointer;font-size:13px;">← Voltar</button>
                <div>
                    <h2 style="font-size:20px;font-weight:700;color:white;">Lote #${lot.id} — ${lot.supplier}</h2>
                    <p style="font-size:12px;color:#64748b;">Disponível: <span style="color:#34d399;font-weight:600;">${lot.usdt_available.toFixed(2)} USDT</span> · Máx. alocável: <span style="color:#fbbf24;font-weight:600;">${maxAlloc.toFixed(2)} USDT</span></p>
                </div>
            </div>
            ${retro ? `<div style="padding:12px 16px;border-radius:12px;background:rgba(251,191,36,0.07);border:1px solid rgba(251,191,36,0.25);margin-bottom:20px;font-size:13px;color:#fbbf24;">Vinculação retroativa: este lote será registrado como já entregue, cobrindo USDT enviado anteriormente sem lote associado.</div>` : ''}

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Quantidade USDT a alocar</label>
                <div style="display:flex;gap:10px;align-items:center;">
                    <input type="number" id="modal-usdt-amount" step="0.0001" min="0.0001"
                        max="${maxAlloc}"
                        placeholder="0.0000"
                        oninput="updateProfitPreview()"
                        style="flex:1;padding:14px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:16px;font-weight:600;outline:none;transition:border-color 0.2s;"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#334155'">
                    <button onclick="document.getElementById('modal-usdt-amount').value='${maxAlloc}';updateProfitPreview();"
                        style="padding:10px 16px;background:rgba(52,211,153,0.1);color:#34d399;border:1px solid rgba(52,211,153,0.3);border-radius:10px;cursor:pointer;font-size:12px;font-weight:600;white-space:nowrap;">
                        Tudo
                    </button>
                </div>
                <p id="modal-usdt-error" style="font-size:12px;color:#f87171;margin-top:6px;display:none;"></p>
            </div>

            <div id="profit-preview" style="padding:20px;background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.2);border-radius:16px;margin-bottom:20px;">
                <p style="font-size:12px;color:#94a3b8;margin-bottom:16px;text-transform:uppercase;letter-spacing:0.05em;">Preview de Lucro</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div>
                        <p style="font-size:11px;color:#64748b;margin-bottom:3px;">Custo por USDT</p>
                        <p style="font-size:16px;font-weight:700;color:#f87171;">R$ ${lot.cost_per_usdt.toFixed(4).replace('.', ',')}</p>
                    </div>
                    <div>
                        <p style="font-size:11px;color:#64748b;margin-bottom:3px;">Receita por USDT</p>
                        <p style="font-size:16px;font-weight:700;color:#4ade80;">${contractRevenuePerUsdt > 0 ? 'R$ ' + contractRevenuePerUsdt.toFixed(4).replace('.', ',') : '—'}</p>
                    </div>
                    <div>
                        <p style="font-size:11px;color:#64748b;margin-bottom:3px;">Margem por USDT</p>
                        <p id="preview-margin" style="font-size:16px;font-weight:700;color:#94a3b8;">—</p>
                    </div>
                    <div>
                        <p style="font-size:11px;color:#64748b;margin-bottom:3px;">Lucro Estimado</p>
                        <p id="preview-profit" style="font-size:22px;font-weight:800;color:#94a3b8;">—</p>
                    </div>
                </div>
                <div style="height:4px;background:rgba(255,255,255,0.06);border-radius:4px;overflow:hidden;">
                    <div id="preview-bar" style="height:100%;width:0%;background:linear-gradient(90deg,#6366f1,#3b82f6);border-radius:4px;transition:width 0.3s;"></div>
                </div>
                <p id="preview-bar-label" style="font-size:10px;color:#64748b;margin-top:4px;">0% do lote será utilizado</p>
            </div>

            <button onclick="confirmAllocation()"
                style="width:100%;padding:14px;background:linear-gradient(135deg,#3b82f6,#6366f1);color:white;border:none;border-radius:14px;font-size:15px;font-weight:700;cursor:pointer;letter-spacing:-0.01em;transition:opacity 0.2s;"
                onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                Confirmar Vinculação
            </button>`;

        setTimeout(() => {
            const input = document.getElementById('modal-usdt-amount');
            if (!input) return;
            input.value = maxAlloc.toFixed(4);
            input.focus();
            updateProfitPreview();
        }, 50);
    }

    function updateProfitPreview() {
        const lot = selectedLot;
        const amount = parseFloat(document.getElementById('modal-usdt-amount')?.value) || 0;
        if (!lot) return;

        const margin = contractRevenuePerUsdt > 0 ? contractRevenuePerUsdt - lot.cost_per_usdt : null;
        const profit = margin !== null && amount > 0 ? margin * amount : null;
        const pct = lot.usdt_amount > 0 ? Math.min(100, (amount / lot.usdt_amount) * 100) : 0;

        const fmtBrl = v => 'R$ ' + v.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        if (margin !== null) {
            const marginEl = document.getElementById('preview-margin');
            marginEl.textContent = (margin >= 0 ? '+' : '') + 'R$ ' + margin.toFixed(4).replace('.', ',');
            marginEl.style.color = margin >= 0 ? '#4ade80' : '#f87171';
        }

        if (profit !== null) {
            const profitEl = document.getElementById('preview-profit');
            profitEl.textContent = fmtBrl(profit);
            profitEl.style.color = profit >= 0 ? '#4ade80' : '#f87171';
        }

        document.getElementById('preview-bar').style.width = pct + '%';
        document.getElementById('preview-bar-label').textContent = pct.toFixed(1) + '% do lote será utilizado';
    }

    function confirmAllocation() {
        const lot = selectedLot;
        const amount = parseFloat(document.getElementById('modal-usdt-amount')?.value);
        const errEl = document.getElementById('modal-usdt-error');
        errEl.style.display = 'none';

        const retro   = isRetroactiveAllocation();
        const ceiling = allocationCeiling();

        if (!amount || amount <= 0) {
            errEl.textContent = 'Informe a quantidade de USDT.';
            errEl.style.display = 'block';
            return;
        }
        if (amount > lot.usdt_available) {
            errEl.textContent = `Disponível no lote: ${lot.usdt_available.toFixed(4)} USDT.`;
            errEl.style.display = 'block';
            return;
        }
        if (ceiling > 0 && amount > ceiling + 0.0001) {
            errEl.textContent = retro
                ? `A operação possui apenas ${ceiling.toFixed(4)} USDT entregue sem lote vinculado.`
                : `A operação precisa de apenas ${ceiling.toFixed(4)} USDT.`;
            errEl.style.display = 'block';
            return;
        }

        const confirmBtn = event.target;
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Vinculando...';

        const body = new FormData();
        body.append(allocCsrfName, allocCsrfHash);
        body.append('lot_id', lot.id);
        body.append('usdt_amount', amount);
        body.append('contract_id', '<?= $c['id'] ?>');
        body.append('retroactive', retro ? '1' : '0');

        fetch('<?= url_to('admin_lots_allocate') ?>', {method: 'POST', body})
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    closeLotModal();
                    location.reload();
                } else {
                    errEl.textContent = data.message;
                    errEl.style.display = 'block';
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Confirmar Vinculação';
                }
            });
    }

    function cancelAllocation(id, btn) {
        if (!confirm('Remover esta reserva de lote?')) return;
        btn.disabled = true;
        const body = new FormData();
        body.append(allocCsrfName, allocCsrfHash);

        fetch('<?= base_url('admin/lots/allocation/cancel/') ?>' + id, {method: 'POST', body})
            .then(r => r.json())
            .then(data => {
                if (data.success) location.reload();
                else { alert(data.message); btn.disabled = false; }
            });
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeLotModal(); closeQuickBuyModal(); } });
    document.getElementById('lot-modal')?.addEventListener('click', e => { if (e.target === e.currentTarget) closeLotModal(); });

    // Compra Rápida
    const quickBuyBaseRate  = <?= (float)($currentBaseRate ?? 0) ?>;
    const quickBuyUsdt      = <?= max(0, (float)$c['total_amount'] - (float)$c['delivered_usdt']) ?>;
    const quickBuyContractId = <?= (int)$c['id'] ?>;

    function openQuickBuyModal() {
        const modal = document.getElementById('quick-buy-modal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        const retro   = isRetroactiveAllocation();
        const prefill = retro ? contractUnlinkedDelivered : quickBuyUsdt;

        document.getElementById('qb-usdt').value    = prefill > 0 ? prefill.toFixed(4) : '';
        document.getElementById('qb-rate').value    = quickBuyBaseRate > 0 ? quickBuyBaseRate.toFixed(4) : '';
        document.getElementById('qb-supplier').value = '';
        document.getElementById('qb-error').style.display = 'none';
        document.getElementById('qb-retro-note').style.display = retro ? 'block' : 'none';
        qbRecalc();
    }

    function closeQuickBuyModal() {
        document.getElementById('quick-buy-modal').style.display = 'none';
        document.body.style.overflow = '';
    }

    document.getElementById('quick-buy-modal')?.addEventListener('click', e => { if (e.target === e.currentTarget) closeQuickBuyModal(); });

    function qbRecalc() {
        const usdt  = parseFloat(document.getElementById('qb-usdt').value)  || 0;
        const rate  = parseFloat(document.getElementById('qb-rate').value)  || 0;
        const total = Math.round(usdt * rate * 100) / 100;

        document.getElementById('qb-total').value = total > 0 ? total.toFixed(2) : '';

        const cost = usdt > 0 ? total / usdt : 0;
        document.getElementById('qb-cost-preview').textContent = usdt > 0
            ? 'R$ ' + cost.toLocaleString('pt-BR', {minimumFractionDigits: 4, maximumFractionDigits: 4})
            : 'R$ —';
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('qb-usdt').addEventListener('input', qbRecalc);
        document.getElementById('qb-rate').addEventListener('input', qbRecalc);
    });

    function submitQuickBuy() {
        const supplier  = document.getElementById('qb-supplier').value.trim();
        const usdt      = parseFloat(document.getElementById('qb-usdt').value);
        const rate      = parseFloat(document.getElementById('qb-rate').value);
        const total     = parseFloat(document.getElementById('qb-total').value) || 0;
        const delivery  = document.getElementById('qb-delivery').value;
        const errEl     = document.getElementById('qb-error');
        const btn       = document.getElementById('qb-submit');

        errEl.style.display = 'none';

        if (!supplier)          { errEl.textContent = 'Informe o fornecedor.';             errEl.style.display = 'block'; return; }
        if (!usdt || usdt <= 0) { errEl.textContent = 'Informe a quantidade USDT.';        errEl.style.display = 'block'; return; }
        if (!rate || rate <= 0) { errEl.textContent = 'Informe a taxa de conversão.';      errEl.style.display = 'block'; return; }
        if (!delivery)          { errEl.textContent = 'Informe o fluxo do fornecedor.';    errEl.style.display = 'block'; return; }

        btn.disabled = true;
        btn.textContent = 'Processando...';

        const body = new FormData();
        body.append(allocCsrfName, allocCsrfHash);
        body.append('contract_id',     quickBuyContractId);
        body.append('supplier',        supplier);
        body.append('usdt_amount',     usdt);
        body.append('conversion_rate', rate);
        body.append('total_brl',       total);
        body.append('delivery_type',   delivery);
        body.append('retroactive',     isRetroactiveAllocation() ? '1' : '0');

        fetch('<?= url_to('admin_lots_quick_buy') ?>', {method: 'POST', body})
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    closeQuickBuyModal();
                    location.reload();
                } else {
                    errEl.textContent = data.message;
                    errEl.style.display = 'block';
                    btn.disabled = false;
                    btn.textContent = 'Confirmar Compra e Reservar';
                }
            });
    }

    // Lock Heartbeat
    const contractId = <?= (int)$c['id'] ?>;
    const lockHeartbeatUrl = '<?= url_to('admin_contracts_lock_heartbeat', $c['id']) ?>';
    const csrfName = '<?= csrf_token() ?>';
    const csrfHash = '<?= csrf_hash() ?>';

    setInterval(function() {
        const formData = new FormData();
        formData.append(csrfName, csrfHash);

        fetch(lockHeartbeatUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'locked_by_other') {
                alert(data.message || 'Esta operação está sendo operada por outro operador. Você será redirecionado.');
                window.location.href = '<?= url_to('admin_contracts') ?>';
            }
        })
        .catch(err => console.error('Erro no batimento cardíaco de bloqueio:', err));
    }, 15000); // 15 seconds
</script>

<!-- Modal de Vinculação de Lote -->
<div id="lot-modal" style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(0,0,0,0.75);backdrop-filter:blur(6px);justify-content:center;align-items:center;padding:20px;">
    <div style="background:#1e293b;border:1px solid rgba(59,130,246,0.2);border-radius:24px;width:100%;max-width:560px;padding:32px;box-shadow:0 25px 60px rgba(0,0,0,0.5);animation:modalBounce 0.35s cubic-bezier(0.175,0.885,0.32,1.275);">
        <div style="display:flex;justify-content:flex-end;margin-bottom:-8px;">
            <button onclick="closeLotModal()" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);color:#94a3b8;border-radius:8px;width:32px;height:32px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;">✕</button>
        </div>
        <div id="lot-modal-body"></div>
    </div>
</div>

<!-- Modal de Compra Rápida -->
<div id="quick-buy-modal" style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(0,0,0,0.75);backdrop-filter:blur(6px);justify-content:center;align-items:center;padding:20px;">
    <div style="background:#1e293b;border:1px solid rgba(251,191,36,0.2);border-radius:24px;width:100%;max-width:520px;padding:32px;box-shadow:0 25px 60px rgba(0,0,0,0.5);animation:modalBounce 0.35s cubic-bezier(0.175,0.885,0.32,1.275);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;">
            <div>
                <h2 style="font-size:20px;font-weight:700;color:white;margin-bottom:4px;">Compra Rápida de USDT</h2>
                <p style="font-size:13px;color:#64748b;">Cria o lote e reserva para esta operação em uma etapa.</p>
            </div>
            <button onclick="closeQuickBuyModal()" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);color:#94a3b8;border-radius:8px;width:32px;height:32px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;">✕</button>
        </div>

        <div style="display:flex;flex-direction:column;gap:18px;">
            <div id="qb-retro-note" style="display:none;padding:12px 16px;border-radius:12px;background:rgba(251,191,36,0.07);border:1px solid rgba(251,191,36,0.25);font-size:13px;color:#fbbf24;">Vinculação retroativa: este lote será registrado como já entregue, cobrindo USDT enviado anteriormente sem lote associado.</div>
            <div>
                <label for="qb-supplier" style="display:block;font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Fornecedor *</label>
                <select id="qb-supplier"
                    style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:14px;outline:none;box-sizing:border-box;cursor:pointer;"
                    onfocus="this.style.borderColor='#fbbf24'" onblur="this.style.borderColor='#334155'">
                    <option value="">Selecione o fornecedor...</option>
                    <?php foreach ($suppliers ?? [] as $s): ?>
                        <option value="<?= esc($s['name']) ?>"><?= esc($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div>
                    <label for="qb-usdt" style="display:block;font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Quantidade USDT *</label>
                    <input type="number" id="qb-usdt" step="0.0001" min="0.0001"
                        style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:14px;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#fbbf24'" onblur="this.style.borderColor='#334155'">
                </div>
                <div>
                    <label for="qb-rate" style="display:block;font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Taxa R$/USDT *</label>
                    <input type="number" id="qb-rate" step="0.0001" min="0.0001"
                        style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:14px;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#fbbf24'" onblur="this.style.borderColor='#334155'">
                </div>
            </div>

            <div>
                <label for="qb-total" style="display:block;font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Total Pago BRL</label>
                <input type="number" id="qb-total" step="0.01" min="0.01" placeholder="—" readonly
                    style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:#94a3b8;font-size:14px;outline:none;box-sizing:border-box;cursor:default;">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div>
                    <label for="qb-delivery" style="display:block;font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Fluxo do Fornecedor *</label>
                    <select id="qb-delivery"
                        style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:14px;outline:none;box-sizing:border-box;cursor:pointer;"
                        onfocus="this.style.borderColor='#fbbf24'" onblur="this.style.borderColor='#334155'">
                        <option value="">— Selecione —</option>
                        <option value="d+0">D+0 (Spot)</option>
                        <option value="d+1">D+1</option>
                        <option value="d+2">D+2</option>
                    </select>
                </div>
                <div style="padding:14px 18px;background:rgba(251,191,36,0.05);border:1px solid rgba(251,191,36,0.15);border-radius:12px;display:flex;flex-direction:column;justify-content:center;">
                    <p style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Custo por USDT</p>
                    <p id="qb-cost-preview" style="font-size:18px;font-weight:700;color:#fbbf24;">R$ —</p>
                </div>
            </div>

            <p id="qb-error" style="font-size:13px;color:#f87171;display:none;padding:10px 14px;background:rgba(239,68,68,0.08);border-radius:10px;border:1px solid rgba(239,68,68,0.2);"></p>

            <button id="qb-submit" onclick="submitQuickBuy()"
                style="width:100%;padding:14px;background:linear-gradient(135deg,#d97706,#fbbf24);color:#0f172a;border:none;border-radius:14px;font-size:15px;font-weight:700;cursor:pointer;transition:opacity 0.2s;"
                onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                Confirmar Compra e Reservar
            </button>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Envio Fora do Fluxo -->
<div id="usdt-send-confirm-modal" style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(0,0,0,0.75);backdrop-filter:blur(6px);justify-content:center;align-items:center;padding:20px;">
    <div style="background:#1e293b;border:1px solid rgba(251,191,36,0.3);border-radius:24px;width:100%;max-width:460px;padding:32px;box-shadow:0 25px 60px rgba(0,0,0,0.5);animation:modalBounce 0.35s cubic-bezier(0.175,0.885,0.32,1.275);">
        <div style="display:flex;gap:14px;align-items:flex-start;margin-bottom:24px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div>
                <h2 style="font-size:17px;font-weight:700;color:white;margin-bottom:6px;">Envio fora do fluxo normal</h2>
                <p id="usdt-send-confirm-text" style="font-size:13px;color:#94a3b8;line-height:1.6;"></p>
            </div>
        </div>
        <div style="display:flex;gap:10px;">
            <button onclick="closeUsdtSendConfirmModal()" style="flex:1;padding:12px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:12px;color:#94a3b8;font-size:14px;font-weight:600;cursor:pointer;">Cancelar</button>
            <button onclick="confirmUsdtSend()" style="flex:1;padding:12px;background:#059669;border:none;border-radius:12px;color:white;font-size:14px;font-weight:700;cursor:pointer;">Concordo</button>
        </div>
    </div>
</div>

<!-- Modal de Sucesso -->
<?php if (session()->getFlashdata('success_modal')):
    $modal = session()->getFlashdata('success_modal');
    ?>
    <div id="admin-success-modal"
        style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 9999; display: flex; justify-content: center; align-items: center; backdrop-filter: blur(8px);">
        <div
            style="background: #1e293b; border: 1px solid rgba(59, 130, 246, 0.3); padding: 40px; border-radius: 32px; max-width: 400px; width: 90%; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); animation: modalBounce 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
            <div
                style="width: 80px; height: 80px; background: rgba(34, 197, 94, 0.1); border: 2px solid #22c55e; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 24px;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="3"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>

            <h2 style="color: white; font-size: 24px; font-weight: 700; margin-bottom: 8px;">Operação Confirmada!</h2>
            <p style="color: #94a3b8; font-size: 15px; margin-bottom: 24px;">
                O <?= $modal['type'] == 'payment' ? 'pagamento' : 'envio de USDT' ?> foi registrado com sucesso.
            </p>

            <div
                style="background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.1); padding: 20px; border-radius: 20px; margin-bottom: 30px;">
                <span
                    style="display: block; font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Valor
                    Registrado</span>
                <span style="font-size: 28px; font-weight: 800; color: #3b82f6;"><?= $modal['value'] ?></span>
            </div>

            <button onclick="document.getElementById('admin-success-modal').style.display='none'" class="btn btn-primary"
                style="width: 100%; height: 50px; justify-content: center; font-size: 16px;">Entendido</button>
        </div>
    </div>

    <style>
        @keyframes modalBounce {
            0% {
                transform: scale(0.5);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
<?php endif; ?>

<?= $this->endSection() ?>