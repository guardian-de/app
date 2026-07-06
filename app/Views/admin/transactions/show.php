<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="header">
    <div style="display: flex; align-items: center; gap: 15px;">
        <a href="<?= url_to('admin_transactions_unlock', $t['id']) ?>" class="btn" style="background: rgba(255,255,255,0.05); color: #94a3b8; padding: 8px 12px;">← Voltar</a>
        <h1 style="font-size: 24px; color: white;">Detalhes da Transação #<?= $t['id'] ?></h1>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
    <!-- Detalhes do Cliente e Transação -->
    <div class="card">
        <h3 style="margin-bottom: 20px; color: #6366f1;">Informações da Solicitação</h3>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Cliente</p>
                <p style="font-size: 18px; font-weight: 700; color: white;"><?= $t['user_name'] ?></p>
            </div>
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Operação</p>
                <p style="font-size: 18px; font-weight: 700; color: <?= $t['type'] == 'buy' ? '#60a5fa' : '#f87171' ?>;">
                    <?= $t['type'] == 'buy' ? 'COMPRA' : 'VENDA' ?>
                </p>
            </div>
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Valor Solicitado (BRL)</p>
                <?php 
                    $brlDisplay = $t['amount_brl'];
                    if ($brlDisplay <= 0 && $t['amount_usdt'] > 0 && $t['rate'] > 0) {
                        $brlDisplay = $t['amount_usdt'] * $t['rate'];
                    }
                ?>
                <p style="font-size: 18px; font-weight: 700; color: white;">
                    R$ <?= number_format($brlDisplay, 2, ',', '.') ?>
                    <?php if($t['amount_brl'] <= 0): ?>
                        <span style="font-size: 10px; color: #fbbf24; font-weight: normal; margin-left: 5px;">(Estimado)</span>
                    <?php endif; ?>
                </p>
            </div>
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Valor em USDT</p>
                <p style="font-size: 18px; font-weight: 700; color: #34d399;"><?= number_format($t['amount_usdt'], 2, '.', ',') ?> USDT</p>
            </div>
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Cotação Aplicada</p>
                <p style="font-size: 16px; font-weight: 600; color: white;">R$ <?= number_format($t['rate'], 4, ',', '.') ?></p>
                <div style="font-size: 11px; color: #94a3b8;">
                    <?php 
                        $fee = (float)$t['fee_percent'];
                        $baseRate = $t['type'] == 'buy' ? $t['rate'] / (1 + ($fee / 100)) : $t['rate'] / (1 - ($fee / 100));
                    ?>
                    Base: R$ <?= number_format($baseRate, 4, ',', '.') ?> (Taxa: <?= number_format($fee, 2, ',', '.') ?>%)
                </div>
            </div>
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Prazo de Entrega</p>
                <p style="font-size: 16px; font-weight: 600; color: white;"><?= $t['delivery_type'] ?></p>
            </div>
            <div style="grid-column: span 2;">
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Carteira do Cliente (TRC-20)</p>
                <p style="font-size: 14px; font-family: monospace; color: #818cf8; word-break: break-all; background: rgba(129, 140, 248, 0.05); padding: 10px; border-radius: 8px; border: 1px dashed rgba(129, 140, 248, 0.2);">
                    <?= $t['wallet_address'] ?: ($t['usdt_wallet'] ?: 'Não informada') ?>
                </p>
            </div>

            <?php if ($t['proof_path']): ?>
            <div style="grid-column: span 2; margin-top: 20px;">
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase; margin-bottom: 10px;">Comprovante Enviado</p>
                <?php 
                    $ext = pathinfo($t['proof_path'], PATHINFO_EXTENSION);
                    if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])):
                ?>
                    <a href="<?= base_url($t['proof_path']) ?>" target="_blank">
                        <img src="<?= base_url($t['proof_path']) ?>" style="max-width: 100%; border-radius: 12px; border: 1px solid var(--border); cursor: zoom-in;">
                    </a>
                <?php else: ?>
                    <a href="<?= base_url($t['proof_path']) ?>" target="_blank" class="btn" style="background: #3b82f6; color: white; width: 100%; justify-content: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        Visualizar Comprovante (PDF)
                    </a>
                <?php endif; ?>

                <?php if ($t['proof_text']): ?>
                <div style="margin-top: 15px; padding: 15px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                    <p style="font-size: 11px; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px;">Observações do Cliente:</p>
                    <p style="color: #cbd5e1; font-size: 14px; line-height: 1.5;"><?= nl2br(esc($t['proof_text'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if ($t['text_read']): ?>
                <div style="margin-top: 15px; padding: 15px; background: rgba(99, 102, 241, 0.05); border-radius: 12px; border: 1px solid rgba(99, 102, 241, 0.1);">
                    <p style="font-size: 11px; color: #818cf8; text-transform: uppercase; margin-bottom: 5px; font-weight: 700;">Dados Extraídos (OCR):</p>
                    <div style="color: #94a3b8; font-size: 12px; line-height: 1.4; max-height: 200px; overflow-y: auto; font-family: monospace; white-space: pre-wrap;"><?= esc($t['text_read']) ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div style="grid-column: span 2; margin-top: 20px;">
                <div style="background: rgba(248, 113, 113, 0.05); border: 1px solid rgba(248, 113, 113, 0.1); padding: 15px; border-radius: 12px; text-align: center;">
                    <p style="color: #f87171; font-size: 13px; font-weight: 600;">Nenhum comprovante anexado pelo cliente ainda.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.05);">
            <h3 style="margin-bottom: 20px; color: #6366f1;">Confirmar Pagamento BRL</h3>
            <form action="<?= url_to('admin_transactions_update', $t['id']) ?>" method="POST" style="display: flex; gap: 15px; align-items: flex-end;">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="completed">
                <div style="flex: 1;">
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 8px;">Valor BRL Recebido (Deixe em branco para total)</label>
                    <input type="number" name="amount_brl_fulfilled" step="0.01" placeholder="Ex: <?= number_format($brlDisplay, 2, '.', '') ?>" style="width: 100%; padding: 12px; background: #0f172a; border: 1px solid #334155; border-radius: 12px; color: white;">
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 12px 30px; height: 45px;">Confirmar Operação</button>
            </form>
        </div>
    </div>

    <!-- Amortização de Contratos -->
    <div>
        <div class="card" style="padding: 20px;">
            <h3 style="margin-bottom: 15px; font-size: 16px; color: #f87171;">Operações em Aberto</h3>

            <?php if(empty($contracts)): ?>
                <p style="color: #94a3b8; font-size: 13px;">Nenhuma operação aberta para este cliente.</p>
            <?php else: ?>
                <?php foreach($contracts as $c): ?>
                    <div style="background: rgba(15, 23, 42, 0.5); padding: 15px; border-radius: 16px; border: 1px solid #334155; margin-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="font-size: 11px; color: #94a3b8;">Operação #<?= $c['id'] ?> (<?= $c['type'] ?>) - <?= number_format($c['total_amount'], 2, '.', ',') ?> USDT</span>
                            <div style="text-align: right;">
                                <span style="font-size: 12px; font-weight: 700; color: #f87171; display: block;">R$ <?= number_format($c['remaining_balance'], 2, ',', '.') ?></span>
                                <span style="font-size: 10px; color: #34d399;">Pago pelo Cliente: R$ <?= number_format($c['paid_client'], 2, ',', '.') ?></span>
                            </div>
                        </div>
                        
                        <form action="<?= url_to('admin_contracts_update', $c['id']) ?>" method="POST" style="display: flex; flex-direction: column; gap: 8px;">
                            <?= csrf_field() ?>
                            <input type="number" name="amount_paid" step="0.01" id="pay-input-<?= $c['id'] ?>" placeholder="Valor BRL" style="background: #0f172a; border: 1px solid #334155; padding: 8px; border-radius: 8px; color: white; font-size: 13px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <button type="button" onclick="document.getElementById('pay-input-<?= $c['id'] ?>').value = '<?= $c['remaining_balance'] ?>'" style="background: rgba(99, 102, 241, 0.1); color: #818cf8; border: 1px solid #6366f1; border-radius: 8px; padding: 6px; font-size: 11px; cursor: pointer;">TOTAL</button>
                                <button type="submit" class="btn btn-primary" style="padding: 6px; font-size: 11px;">PAGAR</button>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="margin-top: 20px; text-align: center;">
            <form action="<?= url_to('admin_transactions_update', $t['id']) ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" style="background: none; border: none; color: #f87171; font-size: 13px; cursor: pointer; text-decoration: underline;">Cancelar Solicitação</button>
            </form>
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
<?= $this->endSection() ?>
