<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<style>
    .header-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .btn-back { color: #94a3b8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; font-weight: 500; }
    .btn-back:hover { color: white; }
    .grid-container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .info-item { display: flex; flex-direction: column; gap: 4px; }
    .info-item span { color: #64748b; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    .info-item strong { color: #f8fafc; font-size: 15px; }
    .badge-role { border-radius: 4px; font-weight: 700; font-size: 10px; text-transform: uppercase; padding: 2px 6px; display: inline-block; width: fit-content; }
    .badge-admin { border: 1px solid #ffffff; color: #ffffff; background: rgba(255,255,255,0.12); }
    .badge-operator { border: 1px solid #ffffff; color: #ffffff; background: rgba(255,255,255,0.05); }
    .badge-user { border: 1px solid rgba(255,255,255,0.25); color: rgba(255,255,255,0.55); }
    .wallet-card { display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 10px 14px; border-radius: 8px; }
</style>

<div class="header-nav">
    <div>
        <a href="<?= url_to('admin_users') ?>" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Voltar para Usuários
        </a>
        <h1 style="font-size: 24px; color: white; margin: 0;">Detalhes do Usuário</h1>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?= url_to('admin_users_edit', $user['id']) ?>" class="btn" style="background: rgba(255,255,255,0.05); color: #cbd5e1; border: 1px solid rgba(255,255,255,0.1); padding: 10px 18px; font-size: 14px; text-decoration: none; border-radius: 8px; display: inline-flex; align-items: center;">Editar Cadastro</a>
        <?php if (session()->get('user_role') === 'admin'): ?>
            <a href="<?= url_to('admin_users_activity', $user['id']) ?>" class="btn" style="background: rgba(129,140,248,0.08); color: #818cf8; border: 1px solid rgba(129,140,248,0.2); padding: 10px 18px; font-size: 14px; text-decoration: none; border-radius: 8px; display: inline-flex; align-items: center;">Histórico de Atividades</a>
        <?php endif; ?>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div style="background: rgba(52, 211, 153, 0.1); color: #34d399; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(52, 211, 153, 0.2); font-size: 14px;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div style="background: rgba(239, 68, 68, 0.1); color: #f87171; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(239, 68, 68, 0.2); font-size: 14px;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="grid-container">
    <!-- Info Card -->
    <div class="card" style="padding: 25px; display: flex; flex-direction: column; gap: 18px;">
        <h2 style="font-size: 18px; color: white; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 10px; margin: 0;">Informações Gerais</h2>
        <div class="info-grid">
            <div class="info-item">
                <span>Login</span>
                <strong><?= esc($user['login']) ?></strong>
            </div>
            <div class="info-item">
                <span>Nível de Acesso</span>
                <div>
                    <?php if (($user['role'] ?? 'user') === 'admin'): ?>
                        <span class="badge-role badge-admin">Admin</span>
                    <?php elseif (($user['role'] ?? 'user') === 'operator'): ?>
                        <span class="badge-role badge-operator">Operador</span>
                    <?php else: ?>
                        <span class="badge-role badge-user">Cliente</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="info-item">
                <span>Taxa / Spread</span>
                <div>
                    <span style="background: rgba(59, 130, 246, 0.1); color: #60a5fa; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 13px; display: inline-block;">
                        <?= number_format($user['fee_percent'] ?? 0, 2) ?>%
                    </span>
                </div>
            </div>
            <div class="info-item">
                <span>Criado em</span>
                <strong style="font-weight: 500; color: #cbd5e1;"><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></strong>
            </div>
        </div>
        
        <?php if (!empty($wallets)): ?>
            <div style="margin-top: 5px;">
                <span style="color: #64748b; font-size: 12px; display: block; margin-bottom: 8px; font-weight: 600; text-transform: uppercase;">Carteiras USDT (TRC-20)</span>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <?php foreach ($wallets as $w): ?>
                        <div class="wallet-card">
                            <span style="font-family: monospace; font-size: 13px; color: #cbd5e1; flex: 1; word-break: break-all;"><?= esc($w['address']) ?></span>
                            <?php if ($w['is_default']): ?>
                                <span style="font-size: 10px; background: rgba(16, 185, 129, 0.15); color: #34d399; font-weight: 700; padding: 2px 6px; border-radius: 4px; border: 1px solid rgba(16, 185, 129, 0.3);">PADRÃO</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Financial Card -->
    <div class="card" style="padding: 25px; display: flex; flex-direction: column; justify-content: space-between; gap: 20px;">
        <div>
            <h2 style="font-size: 18px; color: white; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 10px; margin-bottom: 15px;">Gestão Financeira</h2>
            <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 20px; border-radius: 12px; text-align: center; margin-bottom: 5px;">
                <span style="color: #94a3b8; font-size: 13px; display: block; margin-bottom: 5px;">Saldo Atual do Cliente:</span>
                <strong style="color: #38bdf8; font-size: 28px; font-weight: 800;">R$ <?= number_format($user['balance'] ?? 0, 2, ',', '.') ?></strong>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <button class="btn btn-primary" onclick="openAdjustModal()" style="background: var(--primary); color: white; padding: 14px; font-size: 14px; justify-content: center; font-weight: 700; border-radius: 8px;">Ajustar Saldo</button>
            <?php if (session()->get('user_role') === 'admin'): ?>
                <button class="btn" onclick="openPurchaseModal()" style="background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2); padding: 14px; font-size: 14px; justify-content: center; font-weight: 700; border-radius: 8px;">Registrar Compra</button>
            <?php else: ?>
                <button class="btn" disabled style="background: rgba(255,255,255,0.02); color: #64748b; border: 1px solid rgba(255,255,255,0.05); padding: 14px; font-size: 14px; justify-content: center; font-weight: 700; cursor: not-allowed; border-radius: 8px;">Registrar Compra</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Statement (Extrato) Card -->
<div class="card" style="padding: 25px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-size: 18px; color: white; margin: 0;">Extrato de Entradas e Saídas</h2>
        <a id="statement-export-btn" href="<?= site_url('admin/users/statement/export/' . $user['id']) ?>" class="btn" style="background: #10b981; color: white; border: none; padding: 10px 16px; font-size: 13px; text-decoration: none; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"></path>
            </svg>
            Exportar CSV
        </a>
    </div>
    
    <div style="overflow-x: auto; border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; background: rgba(0,0,0,0.2);">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.02);">
                    <th style="padding: 14px 18px; color: #94a3b8; font-weight: 600;">Data</th>
                    <th style="padding: 14px 18px; color: #94a3b8; font-weight: 600;">Tipo</th>
                    <th style="padding: 14px 18px; color: #94a3b8; font-weight: 600;">Operação</th>
                    <th style="padding: 14px 18px; color: #94a3b8; font-weight: 600;">Valor</th>
                    <th style="padding: 14px 18px; color: #94a3b8; font-weight: 600;">Descrição</th>
                </tr>
            </thead>
            <tbody id="statement-table-body">
                <tr>
                    <td colspan="5" style="padding: 20px; text-align: center; color: #94a3b8;">Carregando extrato...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Ajuste de Saldo/Limite -->
<div id="adjust-modal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="max-width: 480px; width: 100%; border: 1px solid rgba(255,255,255,0.1); background: #0f172a; box-shadow: 0 20px 50px rgba(0,0,0,0.5); position: relative; animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
        <button onclick="closeAdjustModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: #64748b; font-size: 24px; cursor: pointer; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#64748b'">&times;</button>
        
        <h2 style="font-size: 20px; color: white; margin-bottom: 5px;">Ajustar Saldo</h2>
        <p style="font-size: 14px; color: #94a3b8; margin-bottom: 20px;">Ajustar saldo do cliente <strong><?= esc($user['login']) ?></strong></p>
        
        <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <span style="color: #64748b; font-size: 13px;">Saldo Atual:</span>
            <span style="color: white; font-weight: 700; font-size: 16px;">R$ <?= number_format($user['balance'] ?? 0, 2, ',', '.') ?></span>
        </div>
        
        <form id="adjust-form" action="<?= site_url('admin/users/adjust-limit/' . $user['id']) ?>" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Operação</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <label style="display: flex; align-items: center; justify-content: center; gap: 8px; border: 2px solid #10b981; background: rgba(16, 185, 129, 0.05); color: #10b981; padding: 12px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.2s;" id="label-op-add">
                        <input type="radio" name="operation" value="add" checked onclick="selectOperation('add')" style="display: none;">
                        Adicionar (+)
                    </label>
                    <label style="display: flex; align-items: center; justify-content: center; gap: 8px; border: 2px solid rgba(255,255,255,0.05); background: transparent; color: #94a3b8; padding: 12px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.2s;" id="label-op-subtract">
                        <input type="radio" name="operation" value="subtract" onclick="selectOperation('subtract')" style="display: none;">
                        Subtrair (-)
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Valor do Ajuste (R$)</label>
                <input type="text" id="adjust-amount" name="amount" required placeholder="R$ 0,00" style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; font-size: 18px; font-weight: 700; outline: none;">
            </div>
            
            <!-- Botões de Atalho Premium -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                <button type="button" onclick="setQuickAmount(1000)" class="btn" style="background: rgba(255,255,255,0.05); color: #cbd5e1; font-size: 12px; justify-content: center; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">+ R$ 1.000</button>
                <button type="button" onclick="setQuickAmount(5000)" class="btn" style="background: rgba(255,255,255,0.05); color: #cbd5e1; font-size: 12px; justify-content: center; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">+ R$ 5.000</button>
                <button type="button" onclick="setQuickAmount(10000)" class="btn" style="background: rgba(255,255,255,0.05); color: #cbd5e1; font-size: 12px; justify-content: center; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">+ R$ 10.000</button>
            </div>
            
            <div class="form-group">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Motivo / Descrição (Opcional)</label>
                <input type="text" name="notes" placeholder="Ex: Ajuste de saldo comercial" style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
            </div>
            
            <div style="display: flex; gap: 15px; margin-top: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center; border-radius: 8px; font-weight: 700;">Confirmar Ajuste</button>
                <button type="button" onclick="closeAdjustModal()" class="btn" style="background: rgba(255,255,255,0.05); color: #94a3b8; flex: 1; justify-content: center; border-radius: 8px;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Compra para Cliente -->
<div id="purchase-modal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="max-width: 480px; width: 100%; border: 1px solid rgba(255,255,255,0.1); background: #0f172a; box-shadow: 0 20px 50px rgba(0,0,0,0.5); position: relative; animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
        <button onclick="closePurchaseModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: #64748b; font-size: 24px; cursor: pointer; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#64748b'">&times;</button>
        
        <h2 style="font-size: 20px; color: white; margin-bottom: 5px;">Comprar para Cliente</h2>
        <p style="font-size: 14px; color: #94a3b8; margin-bottom: 20px;">Registrar compra para <strong><?= esc($user['login']) ?></strong></p>
        
        <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span style="color: #64748b; font-size: 12px; display: block;">Spread do Cliente:</span>
                <span style="color: #3b82f6; font-weight: 700; font-size: 15px;"><?= number_format($user['fee_percent'] ?? 0, 2, ',', '.') ?>%</span>
            </div>
            <div style="text-align: right;">
                <span style="color: #64748b; font-size: 12px; display: block;">Saldo do Cliente:</span>
                <span style="color: white; font-weight: 700; font-size: 15px;">R$ <?= number_format($user['balance'] ?? 0, 2, ',', '.') ?></span>
            </div>
        </div>
        
        <form id="purchase-form" action="<?= site_url('admin/users/register-purchase/' . $user['id']) ?>" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Valor da Compra (USDT)</label>
                <input type="number" id="purchase-amount" name="usdt_amount" step="0.01" min="0.01" required placeholder="0,00" style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 10px; border-radius: 8px; color: white; font-size: 16px; font-weight: 700; outline: none;" oninput="calculateBrlEstimation()">
            </div>
            
            <div class="form-group">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Prazo de Entrega</label>
                <select name="delivery_type" required style="width: 100%; background: #0f172a; border: 1px solid #334155; padding: 10px; border-radius: 8px; color: white; font-size: 14px; outline: none; cursor: pointer;">
                    <option value="D+0">D+0</option>
                    <option value="D+1">D+1</option>
                    <option value="D+2">D+2</option>
                </select>
            </div>
            
            <div class="form-group">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Cotação Base (R$)</label>
                <input type="number" id="purchase-base-rate" name="base_rate" step="0.0001" min="0.0001" required placeholder="0,0000" value="<?= number_format($latest_rate ?? 5.0000, 4, '.', '') ?>" style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 10px; border-radius: 8px; color: white; font-size: 16px; font-weight: 700; outline: none;" oninput="calculateBrlEstimation()">
            </div>
            
            <div class="form-group">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Observações / Nota (Opcional)</label>
                <input type="text" name="notes" placeholder="Ex: Registro manual de compra" style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 10px; border-radius: 8px; color: white; outline: none;">
            </div>
            
            <div id="brl-estimation-box" style="background: rgba(16, 185, 129, 0.03); border: 1px dashed rgba(16, 185, 129, 0.2); padding: 12px; border-radius: 8px; text-align: center;">
                <span style="color: #64748b; font-size: 12px; display: block; margin-bottom: 2px;">Total BRL Estimado (com taxa):</span>
                <span id="purchase-total-brl" style="color: #10b981; font-weight: 800; font-size: 18px;">R$ 0,00</span>
            </div>
            
            <div style="display: flex; gap: 15px; margin-top: 5px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center; background: #10b981; border-radius: 8px; font-weight: 700;">Confirmar Compra</button>
                <button type="button" onclick="closePurchaseModal()" class="btn" style="background: rgba(255,255,255,0.05); color: #94a3b8; flex: 1; justify-content: center; border-radius: 8px;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
    const currentClientFee = <?= (float)($user['fee_percent'] ?? 0) ?>;
    const currentClientBalance = <?= (float)($user['balance'] ?? 0) ?>;

    document.addEventListener('DOMContentLoaded', function() {
        loadStatement(<?= $user['id'] ?>);
    });

    function loadStatement(userId) {
        const tbody = document.getElementById('statement-table-body');
        
        fetch('<?= site_url('admin/users/statement') ?>/' + userId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao buscar extrato.');
                }
                return response.json();
            })
            .then(data => {
                tbody.innerHTML = '';
                if (!data.history || data.history.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #94a3b8;">Nenhum lançamento financeiro encontrado para este cliente.</td></tr>';
                    return;
                }
                
                data.history.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.style.borderBottom = '1px solid rgba(255,255,255,0.03)';
                    
                    const date = new Date(row.transaction_date);
                    const formattedDate = date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                    
                    const isCredit = row.nature === 'C';
                    const natureBadge = isCredit 
                        ? '<span style="background: rgba(16, 185, 129, 0.1); color: #34d399; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 11px;">Entrada</span>'
                        : '<span style="background: rgba(239, 68, 68, 0.1); color: #f87171; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 11px;">Saída</span>';
                    
                    let opType = row.operation_type;
                    if (opType === 'margin_lock') opType = 'Bloqueio de Margem';
                    else if (opType === 'limit_release') opType = 'Liberação de Limite';
                    else if (opType === 'partial_amortization') opType = 'Amortização Parcial';
                    else if (opType === 'full_settlement') opType = 'Liquidação Total';
                    else if (opType === 'adjustment_add') opType = 'Saldo Adicionado';
                    else if (opType === 'adjustment_subtract') opType = 'Saldo Subtraído';
                    else if (opType === 'deposit') opType = 'Depósito';
                    else if (opType === 'withdrawal') opType = 'Saque';
                    else if (opType === 'late_fee') opType = 'Multa / Juros';
                    
                    const isBrl = ['adjustment_add', 'adjustment_subtract', 'partial_amortization', 'full_settlement', 'late_fee'].includes(row.operation_type);
                    const formattedAmount = isBrl 
                        ? 'R$ ' + (parseFloat(row.amount) || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                        : (parseFloat(row.amount) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' USDT';
                    
                    tr.innerHTML = `
                        <td style="padding: 14px 18px; color: #94a3b8;">${formattedDate}</td>
                        <td style="padding: 14px 18px;">${natureBadge}</td>
                        <td style="padding: 14px 18px; color: white; font-weight: 500;">${opType}</td>
                        <td style="padding: 14px 18px; color: ${isCredit ? '#34d399' : '#f87171'}; font-weight: 700;">${formattedAmount}</td>
                        <td style="padding: 14px 18px; color: #cbd5e1; max-width: 250px; word-break: break-word;">${row.description || ''}</td>
                    `;
                    tbody.appendChild(tr);
                });
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="5" style="padding: 20px; text-align: center; color: #ef4444;">${err.message}</td></tr>`;
            });
    }

    function openAdjustModal() {
        document.getElementById('adjust-amount').value = '';
        selectOperation('add');
        document.getElementById('adjust-modal').style.display = 'flex';
    }

    function closeAdjustModal() {
        document.getElementById('adjust-modal').style.display = 'none';
    }

    function openPurchaseModal() {
        document.getElementById('purchase-amount').value = '';
        document.getElementById('purchase-total-brl').textContent = 'R$ 0,00';
        document.getElementById('purchase-modal').style.display = 'flex';
    }

    function closePurchaseModal() {
        document.getElementById('purchase-modal').style.display = 'none';
    }

    function calculateBrlEstimation() {
        const usdt = parseFloat(document.getElementById('purchase-amount').value) || 0;
        const baseRate = parseFloat(document.getElementById('purchase-base-rate').value) || 0;
        
        if (usdt > 0 && baseRate > 0) {
            const rate = baseRate * (1 + (currentClientFee / 100));
            const totalBrl = usdt * rate;
            document.getElementById('purchase-total-brl').textContent = `R$ ${totalBrl.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
            
            if (totalBrl > currentClientBalance) {
                document.getElementById('purchase-total-brl').style.color = '#ef4444';
            } else {
                document.getElementById('purchase-total-brl').style.color = '#10b981';
            }
        } else {
            document.getElementById('purchase-total-brl').textContent = 'R$ 0,00';
            document.getElementById('purchase-total-brl').style.color = '#10b981';
        }
    }

    const purchaseForm = document.getElementById('purchase-form');
    if (purchaseForm) {
        purchaseForm.onsubmit = function(e) {
            const usdt = parseFloat(document.getElementById('purchase-amount').value);
            const baseRate = parseFloat(document.getElementById('purchase-base-rate').value);
            if (usdt <= 0 || isNaN(usdt)) {
                e.preventDefault();
                alert('Erro: O valor da compra em USDT deve ser superior a zero.');
                return false;
            }
            if (baseRate <= 0 || isNaN(baseRate)) {
                e.preventDefault();
                alert('Erro: A cotação base deve ser superior a zero.');
                return false;
            }
        };
    }

    function selectOperation(op) {
        const addLabel = document.getElementById('label-op-add');
        const subtractLabel = document.getElementById('label-op-subtract');
        
        if (op === 'add') {
            addLabel.style.border = '2px solid #10b981';
            addLabel.style.background = 'rgba(16, 185, 129, 0.05)';
            addLabel.style.color = '#10b981';
            
            subtractLabel.style.border = '2px solid rgba(255,255,255,0.05)';
            subtractLabel.style.background = 'transparent';
            subtractLabel.style.color = '#94a3b8';
            
            addLabel.querySelector('input').checked = true;
        } else {
            subtractLabel.style.border = '2px solid #ef4444';
            subtractLabel.style.background = 'rgba(239, 68, 68, 0.05)';
            subtractLabel.style.color = '#ef4444';
            
            addLabel.style.border = '2px solid rgba(255,255,255,0.05)';
            addLabel.style.background = 'transparent';
            addLabel.style.color = '#94a3b8';
            
            subtractLabel.querySelector('input').checked = true;
        }
    }

    function setQuickAmount(amount) {
        const input = document.getElementById('adjust-amount');
        const options = { minimumFractionDigits: 2, maximumFractionDigits: 2 };
        const result = amount.toLocaleString('pt-BR', options);
        input.value = 'R$ ' + result;
    }

    const adjustInput = document.getElementById('adjust-amount');
    if (adjustInput) {
        adjustInput.addEventListener('input', function(e) {
            let value = e.target.value;
            value = value.replace(/\D/g, '');
            if (value === '') {
                e.target.value = '';
                return;
            }
            const options = { minimumFractionDigits: 2, maximumFractionDigits: 2 };
            const result = (parseFloat(value) / 100).toLocaleString('pt-BR', options);
            e.target.value = 'R$ ' + result;
        });
    }

    const adjustForm = document.getElementById('adjust-form');
    if (adjustForm) {
        adjustForm.addEventListener('submit', function(e) {
            const input = document.getElementById('adjust-amount');
            let value = input.value.replace('R$', '').trim();
            value = value.replace(/\./g, '').replace(',', '.');
            
            const numericValue = parseFloat(value);
            if (isNaN(numericValue) || numericValue <= 0) {
                e.preventDefault();
                alert('Erro: O valor do ajuste deve ser superior a zero.');
                return false;
            }
            
            input.value = numericValue;
        });
    }
</script>

<style>
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>
<?= $this->endSection() ?>
