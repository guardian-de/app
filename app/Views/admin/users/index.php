<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="header">
    <h1 style="font-size: 24px; color: white;">Gerenciar Usuários</h1>
    <a href="<?= url_to('admin_users_create') ?>" class="btn btn-primary">+ Novo Usuário</a>
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

<div class="card" style="padding: 0; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: rgba(255,255,255,0.02);">
                <th style="padding: 18px 25px; text-align: left; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Login</th>
                <th style="padding: 18px 25px; text-align: left; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Permissão</th>
                <th style="padding: 18px 25px; text-align: left; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Spread (%)</th>
                <th style="padding: 18px 25px; text-align: left; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Limite (Saldo)</th>
                <th style="padding: 18px 25px; text-align: left; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Criado em</th>
                <th style="padding: 18px 25px; text-align: right; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $user): ?>
            <tr style="border-top: 1px solid rgba(255,255,255,0.05);">
                <td style="padding: 20px 25px; color: #cbd5e1; font-size: 14px; font-weight: 600;"><?= esc($user['login']) ?></td>
                <td style="padding: 20px 25px;">
                    <?php if (($user['role'] ?? 'user') === 'admin'): ?>
                        <span style="border: 2px solid #ffffff; color: #ffffff; padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 10px; text-transform: uppercase; background: rgba(255,255,255,0.12); display: inline-block;">Admin</span>
                    <?php elseif (($user['role'] ?? 'user') === 'operator'): ?>
                        <span style="border: 1px solid #ffffff; color: #ffffff; padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 10px; text-transform: uppercase; background: rgba(255,255,255,0.05); display: inline-block;">Operador</span>
                    <?php else: ?>
                        <span style="border: 1px solid rgba(255,255,255,0.25); color: rgba(255,255,255,0.55); padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 10px; text-transform: uppercase; background: transparent; display: inline-block;">Cliente</span>
                    <?php endif; ?>
                </td>
                <td style="padding: 20px 25px;">
                    <span style="background: rgba(59, 130, 246, 0.1); color: #60a5fa; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 13px;">
                        <?= number_format($user['fee_percent'], 2) ?>%
                    </span>
                </td>
                <td style="padding: 20px 25px; color: white; font-weight: 700; font-size: 14px;">
                    Saldo: R$ <?= number_format($user['balance'] ?? 0, 2, ',', '.') ?>
                </td>
                <td style="padding: 20px 25px; color: #64748b; font-size: 13px;"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                <td style="padding: 20px 25px; text-align: right;">
                    <div style="display: inline-flex; gap: 8px; justify-content: flex-end;">
                        <button class="btn btn-primary" onclick="openAdjustModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['login'], ENT_QUOTES) ?>', <?= $user['balance'] ?? 0 ?>)" style="background: var(--primary); color: white; padding: 8px 14px; font-size: 13px;">Ajustar Saldo</button>
                        <?php if (session()->get('user_role') === 'admin'): ?>
                            <button class="btn" onclick="openPurchaseModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['login'], ENT_QUOTES) ?>', <?= $user['fee_percent'] ?? 0 ?>, <?= $user['balance'] ?? 0 ?>)" style="background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2); padding: 8px 14px; font-size: 13px;">Comprar</button>
                        <?php endif; ?>
                        <button class="btn" onclick="openStatementModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['login'], ENT_QUOTES) ?>')" style="background: rgba(251, 191, 36, 0.1); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.2); padding: 8px 14px; font-size: 13px;">Extrato</button>
                        <?php if (session()->get('user_role') === 'admin'): ?>
                            <a href="<?= url_to('admin_users_activity', $user['id']) ?>" class="btn" style="background: rgba(129,140,248,0.08); color: #818cf8; border: 1px solid rgba(129,140,248,0.2); padding: 8px 14px; font-size: 13px;">Histórico</a>
                        <?php endif; ?>
                        <a href="<?= url_to('admin_users_edit', $user['id']) ?>" class="btn" style="background: rgba(255,255,255,0.05); color: #94a3b8; padding: 8px 14px; font-size: 13px;">Editar</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal de Ajuste de Saldo/Limite -->
<div id="adjust-modal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="max-width: 480px; width: 100%; border: 1px solid rgba(255,255,255,0.1); background: #0f172a; box-shadow: 0 20px 50px rgba(0,0,0,0.5); position: relative; animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
        <button onclick="closeAdjustModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: #64748b; font-size: 24px; cursor: pointer; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#64748b'">&times;</button>
        
        <h2 style="font-size: 20px; color: white; margin-bottom: 5px;">Ajustar Saldo</h2>
        <p id="adjust-user-name" style="font-size: 14px; color: #94a3b8; margin-bottom: 20px;"></p>
        
        <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <span style="color: #64748b; font-size: 13px;">Saldo Atual:</span>
            <span id="adjust-current-limit" style="color: white; font-weight: 700; font-size: 16px;">R$ 0,00</span>
        </div>
        
        <form id="adjust-form" action="" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
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
                <input type="number" id="adjust-amount" name="amount" step="0.01" min="0.01" required placeholder="0,00" style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; font-size: 18px; font-weight: 700; outline: none;">
            </div>
            
            <!-- Botões de Atalho Premium -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                <button type="button" onclick="setQuickAmount(1000)" class="btn" style="background: rgba(255,255,255,0.05); color: #cbd5e1; font-size: 12px; justify-content: center; border: 1px solid rgba(255,255,255,0.1);">+ R$ 1.000</button>
                <button type="button" onclick="setQuickAmount(5000)" class="btn" style="background: rgba(255,255,255,0.05); color: #cbd5e1; font-size: 12px; justify-content: center; border: 1px solid rgba(255,255,255,0.1);">+ R$ 5.000</button>
                <button type="button" onclick="setQuickAmount(10000)" class="btn" style="background: rgba(255,255,255,0.05); color: #cbd5e1; font-size: 12px; justify-content: center; border: 1px solid rgba(255,255,255,0.1);">+ R$ 10.000</button>
            </div>
            
            <div class="form-group">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Motivo / Descrição (Opcional)</label>
                <input type="text" name="notes" placeholder="Ex: Ajuste de saldo comercial" style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
            </div>
            
            <div style="display: flex; gap: 15px; margin-top: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">Confirmar Ajuste</button>
                <button type="button" onclick="closeAdjustModal()" class="btn" style="background: rgba(255,255,255,0.05); color: #94a3b8; flex: 1; justify-content: center;">Cancelar</button>
            </div>
        </form>
    </div>
</div>


<!-- Modal de Compra para Cliente -->
<div id="purchase-modal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="max-width: 480px; width: 100%; border: 1px solid rgba(255,255,255,0.1); background: #0f172a; box-shadow: 0 20px 50px rgba(0,0,0,0.5); position: relative; animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
        <button onclick="closePurchaseModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: #64748b; font-size: 24px; cursor: pointer; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#64748b'">&times;</button>
        
        <h2 style="font-size: 20px; color: white; margin-bottom: 5px;">Comprar para Cliente</h2>
        <p id="purchase-user-name" style="font-size: 14px; color: #94a3b8; margin-bottom: 20px;"></p>
        
        <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span style="color: #64748b; font-size: 12px; display: block;">Spread do Cliente:</span>
                <span id="purchase-user-fee" style="color: #3b82f6; font-weight: 700; font-size: 15px;">0,00%</span>
            </div>
            <div style="text-align: right;">
                <span id="purchase-user-limit-label" style="color: #64748b; font-size: 12px; display: block;">Saldo do Cliente:</span>
                <span id="purchase-user-limit" style="color: white; font-weight: 700; font-size: 15px;">R$ 0,00</span>
            </div>
        </div>
        
        <form id="purchase-form" action="" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
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
                <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center; background: #10b981;">Confirmar Compra</button>
                <button type="button" onclick="closePurchaseModal()" class="btn" style="background: rgba(255,255,255,0.05); color: #94a3b8; flex: 1; justify-content: center;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Extrato do Cliente -->
<div id="statement-modal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="max-width: 760px; width: 100%; border: 1px solid rgba(255,255,255,0.1); background: #0f172a; box-shadow: 0 20px 50px rgba(0,0,0,0.5); position: relative; animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; max-height: 85vh; display: flex; flex-direction: column; padding: 25px;">
        <button onclick="closeStatementModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: #64748b; font-size: 24px; cursor: pointer; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#64748b'">&times;</button>
        
        <h2 style="font-size: 20px; color: white; margin-bottom: 5px;">Extrato de Entradas e Saídas</h2>
        <p id="statement-user-name" style="font-size: 14px; color: #94a3b8; margin-bottom: 20px;"></p>
        
        <div style="display: flex; gap: 15px; margin-bottom: 20px;">
            <a id="statement-export-btn" href="#" class="btn" style="background: #10b981; color: white; border: none; padding: 10px 16px; font-size: 14px; text-decoration: none; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"></path>
                </svg>
                Baixar Planilha (CSV)
            </a>
        </div>
        
        <div style="flex: 1; overflow-y: auto; border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; background: rgba(0,0,0,0.2);">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.02);">
                        <th style="padding: 12px 15px; color: #94a3b8; font-weight: 600;">Data</th>
                        <th style="padding: 12px 15px; color: #94a3b8; font-weight: 600;">Tipo</th>
                        <th style="padding: 12px 15px; color: #94a3b8; font-weight: 600;">Operação</th>
                        <th style="padding: 12px 15px; color: #94a3b8; font-weight: 600;">Valor (USDT)</th>
                        <th style="padding: 12px 15px; color: #94a3b8; font-weight: 600;">Descrição</th>
                    </tr>
                </thead>
                <tbody id="statement-table-body">
                    <!-- Dynamic Rows -->
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
            <button onclick="closeStatementModal()" class="btn" style="background: rgba(255,255,255,0.05); color: #94a3b8; padding: 10px 20px;">Fechar</button>
        </div>
    </div>
</div>

<script>
function openAdjustModal(userId, name, currentBalance) {
    document.getElementById('adjust-user-name').innerHTML = `Ajustar saldo do cliente <strong>${name}</strong>`;
    document.getElementById('adjust-current-limit').textContent = `R$ ${currentBalance.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
    document.getElementById('adjust-form').action = `<?= site_url('admin/users/adjust-limit') ?>/${userId}`;
    document.getElementById('adjust-amount').value = '';
    selectOperation('add');
    document.getElementById('adjust-modal').style.display = 'flex';
}

function closeAdjustModal() {
    document.getElementById('adjust-modal').style.display = 'none';
}


let currentClientFee = 0;
let currentClientBalance = 0;

function openPurchaseModal(userId, name, feePercent, balance) {
    currentClientFee = feePercent;
    currentClientBalance = balance;
    
    document.getElementById('purchase-user-name').innerHTML = `Registrar compra para <strong>${name}</strong>`;
    document.getElementById('purchase-user-fee').textContent = `${feePercent.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}%`;
    document.getElementById('purchase-user-limit').textContent = `R$ ${balance.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
    document.getElementById('purchase-form').action = `<?= site_url('admin/users/register-purchase') ?>/${userId}`;
    
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
        
        // Se a compra exceder o saldo do cliente, valida saldo em tempo real
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

// Validação da compra antes de submeter
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
        const rate = baseRate * (1 + (currentClientFee / 100));
        const totalBrl = usdt * rate;
        // Validação de saldo removida para permitir compra mesmo com saldo abaixo de 0,00
        /*
        if (totalBrl > currentClientBalance) {
            e.preventDefault();
            alert('Erro: Saldo insuficiente para o cliente. Saldo disponível: R$ ' + currentClientBalance.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
            return false;
        }
        */
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

function openStatementModal(userId, name) {
    document.getElementById('statement-user-name').innerHTML = `Cliente: <strong>${name}</strong>`;
    
    // Set Export CSV link
    const exportUrl = '<?= site_url('admin/users/statement/export') ?>/' + userId;
    document.getElementById('statement-export-btn').href = exportUrl;
    
    // Clear existing table body rows
    const tbody = document.getElementById('statement-table-body');
    tbody.innerHTML = '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #94a3b8;">Carregando extrato...</td></tr>';
    
    document.getElementById('statement-modal').style.display = 'flex';
    
    // Fetch data via AJAX
    fetch('<?= site_url('admin/users/statement') ?>/' + userId)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao buscar extrato.');
            }
            return response.json();
        })
        .then(data => {
            tbody.innerHTML = '';
            if (data.history.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #94a3b8;">Nenhum lançamento financeiro encontrado para este cliente.</td></tr>';
                return;
            }
            
            data.history.forEach(row => {
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid rgba(255,255,255,0.03)';
                
                // Formata a data
                const date = new Date(row.transaction_date);
                const formattedDate = date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                
                // Formata o tipo de natureza
                const isCredit = row.nature === 'C';
                const natureBadge = isCredit 
                    ? '<span style="background: rgba(16, 185, 129, 0.1); color: #34d399; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 11px;">Entrada</span>'
                    : '<span style="background: rgba(239, 68, 68, 0.1); color: #f87171; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 11px;">Saída</span>';
                
                // Mapeia tipo de operação
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
                    <td style="padding: 12px 15px; color: #94a3b8;">${formattedDate}</td>
                    <td style="padding: 12px 15px;">${natureBadge}</td>
                    <td style="padding: 12px 15px; color: white; font-weight: 500;">${opType}</td>
                    <td style="padding: 12px 15px; color: ${isCredit ? '#34d399' : '#f87171'}; font-weight: 700;">${formattedAmount}</td>
                    <td style="padding: 12px 15px; color: #cbd5e1; max-width: 250px; word-break: break-word;">${row.description || ''}</td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => {
            tbody.innerHTML = `<tr><td colspan="5" style="padding: 20px; text-align: center; color: #ef4444;">${err.message}</td></tr>`;
        });
}

function closeStatementModal() {
    document.getElementById('statement-modal').style.display = 'none';
}

function setQuickAmount(amount) {
    const input = document.getElementById('adjust-amount');
    input.value = amount.toFixed(2);
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
