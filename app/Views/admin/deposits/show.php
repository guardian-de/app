<?php
/** @var array $deposit */
?>
<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="header">
    <div style="display: flex; align-items: center; gap: 15px;">
        <a href="<?= url_to('admin_deposits') ?>" class="btn" style="background: rgba(255,255,255,0.05); color: #94a3b8; padding: 8px 12px;">← Voltar</a>
        <h1 style="font-size: 24px; color: white;">Depósito #<?= $deposit['id'] ?></h1>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div style="background: rgba(34,197,94,0.1); color: #4ade80; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(34,197,94,0.2); font-size: 14px;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
    <div style="background: rgba(239,68,68,0.1); color: #f87171; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(239,68,68,0.2); font-size: 14px;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 30px;">

    <!-- Informações do Depósito -->
    <div class="card">
        <h3 style="margin-bottom: 24px; color: #6366f1;">Informações do Depósito</h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Cliente</p>
                <p style="font-size: 18px; font-weight: 700; color: white;"><?= esc($deposit['user_login']) ?></p>
            </div>
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Valor</p>
                <p style="font-size: 24px; font-weight: 800; color: #34d399;">R$ <?= number_format($deposit['amount'], 2, ',', '.') ?></p>
            </div>
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Status</p>
                <p style="margin-top: 4px;">
                    <?php if ($deposit['status'] === 'pending'): ?>
                        <span style="background: rgba(251,191,36,0.12); color: #fbbf24; border: 1px solid rgba(251,191,36,0.25); font-size: 13px; font-weight: 700; padding: 5px 14px; border-radius: 20px; text-transform: uppercase;">Pendente</span>
                    <?php elseif ($deposit['status'] === 'accepted'): ?>
                        <span style="background: rgba(16,185,129,0.12); color: #10b981; border: 1px solid rgba(16,185,129,0.25); font-size: 13px; font-weight: 700; padding: 5px 14px; border-radius: 20px; text-transform: uppercase;">Aceito</span>
                    <?php elseif ($deposit['status'] === 'rejected'): ?>
                        <span style="background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid rgba(239,68,68,0.25); font-size: 13px; font-weight: 700; padding: 5px 14px; border-radius: 20px; text-transform: uppercase;">Rejeitado</span>
                    <?php else: ?>
                        <span style="background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid rgba(239,68,68,0.25); font-size: 13px; font-weight: 700; padding: 5px 14px; border-radius: 20px; text-transform: uppercase;">Revertido</span>
                    <?php endif; ?>
                </p>
            </div>
            <div>
                <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase;">Solicitado em</p>
                <p style="font-size: 16px; font-weight: 700; color: white;"><?= date('d/m/Y H:i', strtotime($deposit['created_at'])) ?></p>
            </div>
        </div>

        <?php if ($deposit['notes']): ?>
        <div style="margin-bottom: 20px;">
            <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px;">Observações do cliente</p>
            <p style="color: #cbd5e1; font-size: 14px; background: rgba(15,23,42,0.4); padding: 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.06);"><?= esc($deposit['notes']) ?></p>
        </div>
        <?php endif; ?>

        <?php if ($deposit['status'] === 'accepted'): ?>
        <div style="background: rgba(16,185,129,0.06); border: 1px solid rgba(16,185,129,0.15); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
            <p style="font-size: 12px; color: #10b981; text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">Aceito por</p>
            <p style="font-size: 16px; font-weight: 700; color: white;"><?= esc($deposit['accepted_by_login']) ?></p>
            <p style="font-size: 13px; color: #94a3b8; margin-top: 4px;"><?= date('d/m/Y H:i', strtotime($deposit['accepted_at'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if ($deposit['status'] === 'reversed'): ?>
        <div style="background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.15); border-radius: 12px; padding: 16px;">
            <p style="font-size: 12px; color: #f87171; text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">Revertido por</p>
            <p style="font-size: 16px; font-weight: 700; color: white;"><?= esc($deposit['reversed_by_login']) ?></p>
            <p style="font-size: 13px; color: #94a3b8; margin-top: 4px;"><?= date('d/m/Y H:i', strtotime($deposit['reversed_at'])) ?></p>
            <?php if ($deposit['reversal_reason']): ?>
                <p style="font-size: 13px; color: #f87171; margin-top: 8px;">Motivo: <?= esc($deposit['reversal_reason']) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($deposit['status'] === 'rejected'): ?>
        <div style="background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.15); border-radius: 12px; padding: 16px;">
            <p style="font-size: 12px; color: #f87171; text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">Rejeitado por</p>
            <p style="font-size: 16px; font-weight: 700; color: white;"><?= esc($deposit['rejected_by_login']) ?></p>
            <p style="font-size: 13px; color: #94a3b8; margin-top: 4px;"><?= date('d/m/Y H:i', strtotime($deposit['rejected_at'])) ?></p>
            <p style="font-size: 13px; color: #f87171; margin-top: 8px;">Motivo: <?= esc($deposit['rejection_reason']) ?></p>
        </div>
        <?php endif; ?>

        <!-- Comprovante -->
        <div style="margin-top: 24px;">
            <p style="font-size: 12px; color: #94a3b8; text-transform: uppercase; margin-bottom: 12px;">Comprovante</p>
            <?php 
                $proofs = !empty($deposit['proof_file']) ? explode(',', $deposit['proof_file']) : [];
            ?>
            <?php if (empty($proofs)): ?>
                <p style="color: #64748b; font-size: 13px;">Depósito lançado manualmente pelo admin — sem comprovante.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach ($proofs as $idx => $proof): ?>
                        <?php 
                            $proof = trim($proof);
                            $ext = strtolower(pathinfo($proof, PATHINFO_EXTENSION)); 
                        ?>
                        <div style="background: rgba(15,23,42,0.3); padding: 12px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.04);">
                            <p style="font-size: 11px; color: #64748b; margin-bottom: 8px;">Arquivo #<?= $idx + 1 ?></p>
                            <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                                <a href="<?= base_url($proof) ?>" target="_blank">
                                    <img src="<?= base_url($proof) ?>" alt="Comprovante" style="max-width: 100%; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); cursor: zoom-in;">
                                </a>
                            <?php else: ?>
                                <a href="<?= base_url($proof) ?>" target="_blank" class="btn btn-primary" style="display: inline-flex; gap: 8px; align-items: center;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    Abrir PDF / Arquivo
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ações -->
    <div style="display: flex; flex-direction: column; gap: 20px;">

        <?php if ($deposit['status'] === 'pending'): ?>
        <div class="card">
            <h3 style="margin-bottom: 12px; color: white; font-size: 16px;">Aceitar Depósito</h3>
            <p style="color: #94a3b8; font-size: 13px; margin-bottom: 20px;">Ao aceitar, o valor de <strong style="color: #34d399;">R$ <?= number_format($deposit['amount'], 2, ',', '.') ?></strong> será lançado no extrato do cliente como crédito.</p>
            <button onclick="document.getElementById('accept-modal').style.display='flex'" class="btn btn-primary" style="width: 100%;">
                Aceitar Depósito
            </button>
        </div>
        <?php endif; ?>

        <?php if ($deposit['status'] === 'pending'): ?>
        <div class="card" style="border-color: rgba(239,68,68,0.2);">
            <h3 style="margin-bottom: 12px; color: #f87171; font-size: 16px;">Rejeitar Depósito</h3>
            <p style="color: #94a3b8; font-size: 13px; margin-bottom: 20px;">O cliente será notificado e nenhum crédito será lançado no extrato.</p>
            <button onclick="document.getElementById('reject-modal').style.display='flex'" class="btn" style="width: 100%; background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3);">
                Rejeitar Depósito
            </button>
        </div>
        <?php endif; ?>

        <?php if ($deposit['status'] === 'accepted' && session()->get('user_role') === 'admin'): ?>
        <div class="card" style="border-color: rgba(239,68,68,0.2);">
            <h3 style="margin-bottom: 12px; color: #f87171; font-size: 16px;">Reverter Depósito</h3>
            <p style="color: #94a3b8; font-size: 13px; margin-bottom: 20px;">Apenas administradores podem reverter. Um estorno de <strong style="color: #f87171;">R$ <?= number_format($deposit['amount'], 2, ',', '.') ?></strong> será lançado no extrato.</p>
            <button onclick="document.getElementById('reverse-modal').style.display='flex'" class="btn" style="width: 100%; background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3);">
                Reverter Depósito
            </button>
        </div>
        <?php endif; ?>

        <?php if ($deposit['status'] === 'rejected' && session()->get('user_role') === 'admin'): ?>
        <div class="card" style="border-color: rgba(239,68,68,0.2);">
            <h3 style="margin-bottom: 12px; color: #f87171; font-size: 16px;">Reverter Rejeição</h3>
            <p style="color: #94a3b8; font-size: 13px; margin-bottom: 20px;">Apenas administradores podem reverter. O depósito voltará para pendente e poderá ser aceito ou rejeitado novamente.</p>
            <button onclick="document.getElementById('reverse-rejection-modal').style.display='flex'" class="btn" style="width: 100%; background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3);">
                Reverter Rejeição
            </button>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php if (!empty($history)): ?>
<?php
$historyLabels = [
    'deposit.accepted'           => 'Aceito',
    'deposit.rejected'           => 'Rejeitado',
    'deposit.reversed'           => 'Revertido (estorno)',
    'deposit.rejection_reverted' => 'Rejeição revertida',
];
?>
<h3 style="font-size: 16px; font-weight: 700; color: white; margin: 30px 0 14px;">Histórico</h3>
<div class="card" style="padding: 0; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <th style="padding: 12px 18px; text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 600;">Ação</th>
                <th style="padding: 12px 18px; text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 600;">Responsável</th>
                <th style="padding: 12px 18px; text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 600;">Motivo</th>
                <th style="padding: 12px 18px; text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 600;">Data</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $entry): ?>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                    <td style="padding: 12px 18px; font-size: 13px; color: #e2e8f0; font-weight: 600;"><?= esc($historyLabels[$entry['action']] ?? $entry['action']) ?></td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #94a3b8;"><?= esc($entry['actor_login'] ?? '—') ?></td>
                    <td style="padding: 12px 18px; font-size: 13px; color: #94a3b8;"><?= esc($entry['payload']['reason'] ?? $entry['payload']['previous_reason'] ?? '—') ?></td>
                    <td style="padding: 12px 18px; font-size: 12px; color: #64748b; white-space: nowrap;"><?= date('d/m/y H:i', strtotime($entry['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php if (!empty($other_pending)): ?>
<div style="grid-column: 1 / -1; margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 30px;">
    <h3 style="font-size: 16px; font-weight: 700; color: white; margin-bottom: 16px;">Outros Depósitos Pendentes do Cliente (Máximo 2)</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
        <?php foreach ($other_pending as $idx => $op): ?>
            <?php 
                $opProofs = !empty($op['proof_file']) ? explode(',', $op['proof_file']) : [];
            ?>
            <div class="card" style="border: 1px solid rgba(99, 102, 241, 0.25); display: flex; flex-direction: column; justify-content: space-between; background: rgba(30, 41, 59, 0.5); padding: 20px; border-radius: 16px; margin-bottom: 0;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h4 style="color: white; font-size: 15px; font-weight: 700; margin: 0;">Depósito #<?= $op['id'] ?></h4>
                        <span style="font-size: 12px; color: #94a3b8;"><?= date('d/m/Y H:i', strtotime($op['created_at'])) ?></span>
                    </div>
                    <p style="font-size: 20px; font-weight: 800; color: #34d399; margin-bottom: 12px;">R$ <?= number_format($op['amount'], 2, ',', '.') ?></p>
                    
                    <?php if ($op['notes']): ?>
                        <p style="font-size: 13px; color: #cbd5e1; background: rgba(15,23,42,0.4); padding: 8px 12px; border-radius: 8px; margin-bottom: 12px; font-style: italic;">
                            "<?= esc($op['notes']) ?>"
                        </p>
                    <?php endif; ?>

                    <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                        <?php foreach ($opProofs as $opIdx => $opProof): ?>
                            <?php 
                                $opProof = trim($opProof);
                                $opExt = strtolower(pathinfo($opProof, PATHINFO_EXTENSION)); 
                            ?>
                            <div style="background: rgba(15,23,42,0.3); padding: 8px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.04);">
                                <p style="font-size: 10px; color: #64748b; margin-bottom: 4px;">Arquivo #<?= $opIdx + 1 ?></p>
                                <?php if (in_array($opExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                                    <a href="<?= base_url($opProof) ?>" target="_blank">
                                        <img src="<?= base_url($opProof) ?>" alt="Comprovante" style="max-height: 120px; border-radius: 8px; cursor: zoom-in;">
                                    </a>
                                <?php else: ?>
                                    <a href="<?= base_url($opProof) ?>" target="_blank" class="btn btn-primary" style="display: inline-flex; gap: 4px; align-items: center; padding: 4px 8px; font-size: 11px;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        Abrir PDF
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <div style="display: flex; gap: 10px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 12px;">
                        <form action="<?= url_to('admin_deposits_accept', $op['id']) ?>" method="POST" style="flex: 1; margin: 0;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 8px; font-size: 12px;">Confirmar</button>
                        </form>
                        
                        <button onclick="toggleInlineReject(<?= $op['id'] ?>)" class="btn" style="flex: 1; padding: 8px; font-size: 12px; background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3);">
                            Rejeitar
                        </button>
                    </div>

                    <div id="inline-reject-form-<?= $op['id'] ?>" style="display: none; margin-top: 12px; padding: 12px; border-radius: 12px; background: rgba(239,68,68,0.05); border: 1px solid rgba(239,68,68,0.15);">
                        <form action="<?= url_to('admin_deposits_reject', $op['id']) ?>" method="POST" style="margin: 0;">
                            <?= csrf_field() ?>
                            <label style="display: block; font-size: 11px; color: #f87171; margin-bottom: 6px; font-weight: 600;">Motivo da Rejeição</label>
                            <textarea name="rejection_reason" required style="width: 100%; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: white; padding: 8px; font-size: 12px; outline: none; resize: none; margin-bottom: 8px;" placeholder="Ex: Comprovante ilegível..."></textarea>
                            <div style="display: flex; gap: 6px;">
                                <button type="submit" class="btn" style="flex: 1; padding: 6px; font-size: 11px; background: #ef4444; color: white; border: none;">Confirmar</button>
                                <button type="button" onclick="toggleInlineReject(<?= $op['id'] ?>)" class="btn" style="padding: 6px 12px; font-size: 11px; background: rgba(255,255,255,0.05); color: #94a3b8; border: none;">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Modal de confirmação de aceite -->
<div id="accept-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(6px);">
    <div style="background: #1e293b; border-radius: 16px; padding: 32px; max-width: 420px; width: 100%; border: 1px solid rgba(99,102,241,0.2);">
        <h3 style="color: white; margin-bottom: 12px;">Confirmar aceite</h3>
        <p style="color: #94a3b8; font-size: 14px; margin-bottom: 24px;">
            Você está prestes a aceitar um depósito de <strong style="color: #34d399;">R$ <?= number_format($deposit['amount'], 2, ',', '.') ?></strong> do cliente <strong style="color: white;"><?= esc($deposit['user_login']) ?></strong>. Isso lançará um crédito no extrato financeiro.
        </p>
        <form action="<?= url_to('admin_deposits_accept', $deposit['id']) ?>" method="POST" style="display: flex; gap: 12px;">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary" style="flex: 1;">Confirmar Aceite</button>
            <button type="button" onclick="document.getElementById('accept-modal').style.display='none'" class="btn" style="flex: 1; background: rgba(255,255,255,0.05); color: #94a3b8;">Cancelar</button>
        </form>
    </div>
</div>

<!-- Modal de reversão -->
<div id="reverse-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(6px);">
    <div style="background: #1e293b; border-radius: 16px; padding: 32px; max-width: 420px; width: 100%; border: 1px solid rgba(239,68,68,0.2);">
        <h3 style="color: #f87171; margin-bottom: 12px;">Reverter depósito</h3>
        <p style="color: #94a3b8; font-size: 14px; margin-bottom: 20px;">
            Informe o motivo da reversão. Um débito de <strong style="color: #f87171;">R$ <?= number_format($deposit['amount'], 2, ',', '.') ?></strong> será lançado no extrato.
        </p>
        <form action="<?= url_to('admin_deposits_reverse', $deposit['id']) ?>" method="POST">
            <?= csrf_field() ?>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px;">Motivo (opcional)</label>
                <textarea name="reversal_reason" rows="3" style="width: 100%; background: #0f172a; border: 1px solid #334155; border-radius: 10px; color: white; padding: 12px; font-size: 14px; outline: none; resize: none;" placeholder="Ex: depósito não identificado..."></textarea>
            </div>
            <div style="display: flex; gap: 12px;">
                <button type="submit" class="btn" style="flex: 1; background: rgba(239,68,68,0.2); color: #f87171; border: 1px solid rgba(239,68,68,0.3);">Confirmar Reversão</button>
                <button type="button" onclick="document.getElementById('reverse-modal').style.display='none'" class="btn" style="flex: 1; background: rgba(255,255,255,0.05); color: #94a3b8;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de reversão de rejeição -->
<div id="reverse-rejection-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(6px);">
    <div style="background: #1e293b; border-radius: 16px; padding: 32px; max-width: 420px; width: 100%; border: 1px solid rgba(239,68,68,0.2);">
        <h3 style="color: #f87171; margin-bottom: 12px;">Reverter rejeição</h3>
        <p style="color: #94a3b8; font-size: 14px; margin-bottom: 24px;">
            O depósito voltará para <strong style="color: #fbbf24;">pendente</strong> e poderá ser aceito ou rejeitado novamente.
        </p>
        <form action="<?= url_to('admin_deposits_reverse_rejection', $deposit['id']) ?>" method="POST" style="display: flex; gap: 12px;">
            <?= csrf_field() ?>
            <button type="submit" class="btn" style="flex: 1; background: rgba(239,68,68,0.2); color: #f87171; border: 1px solid rgba(239,68,68,0.3);">Confirmar Reversão</button>
            <button type="button" onclick="document.getElementById('reverse-rejection-modal').style.display='none'" class="btn" style="flex: 1; background: rgba(255,255,255,0.05); color: #94a3b8;">Cancelar</button>
        </form>
    </div>
</div>

<!-- Modal de rejeição -->
<div id="reject-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(6px);">
    <div style="background: #1e293b; border-radius: 16px; padding: 32px; max-width: 420px; width: 100%; border: 1px solid rgba(239,68,68,0.2);">
        <h3 style="color: #f87171; margin-bottom: 12px;">Rejeitar depósito</h3>
        <p style="color: #94a3b8; font-size: 14px; margin-bottom: 20px;">
            Informe o motivo da rejeição. Este campo é obrigatório.
        </p>
        <form action="<?= url_to('admin_deposits_reject', $deposit['id']) ?>" method="POST" onsubmit="return validateRejectForm()">
            <?= csrf_field() ?>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px;">Motivo <span style="color: #f87171;">*</span></label>
                <textarea id="rejection_reason" name="rejection_reason" rows="3" required style="width: 100%; background: #0f172a; border: 1px solid #334155; border-radius: 10px; color: white; padding: 12px; font-size: 14px; outline: none; resize: none;" placeholder="Ex: comprovante ilegível, valor divergente..."></textarea>
                <p id="reject-error" style="color: #f87171; font-size: 12px; margin-top: 6px; display: none;">O motivo é obrigatório.</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button type="submit" class="btn" style="flex: 1; background: rgba(239,68,68,0.2); color: #f87171; border: 1px solid rgba(239,68,68,0.3);">Confirmar Rejeição</button>
                <button type="button" onclick="document.getElementById('reject-modal').style.display='none'" class="btn" style="flex: 1; background: rgba(255,255,255,0.05); color: #94a3b8;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function validateRejectForm() {
    var reason = document.getElementById('rejection_reason').value.trim();
    if (!reason) {
        document.getElementById('reject-error').style.display = 'block';
        return false;
    }
    return true;
}

function toggleInlineReject(id) {
    var el = document.getElementById('inline-reject-form-' + id);
    if (el) {
        el.style.display = el.style.display === 'none' ? 'block' : 'none';
    }
}
</script>

<?= $this->endSection() ?>
