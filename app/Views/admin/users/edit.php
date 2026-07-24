<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="header">
    <h1 style="font-size: 24px; color: white;">Editar Usuário</h1>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <?php if(session()->getFlashdata('errors')): ?>
        <div style="background: rgba(248, 113, 113, 0.1); color: #f87171; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(248, 113, 113, 0.2); font-size: 14px;">
            <?php foreach(session()->getFlashdata('errors') as $error): ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
        $savedPermissions = !empty($user['permissions']) ? json_decode($user['permissions'], true) : [];
        if (!is_array($savedPermissions)) {
            $savedPermissions = [];
        }
        $canSetPurchaseModel = session()->get('user_role') === 'admin' || in_array('purchase_model', session()->get('user_permissions') ?? []);
    ?>

    <form action="<?= url_to('admin_users_update', $user['id']) ?>" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
        <?= csrf_field() ?>

        <div style="display: grid; grid-template-columns: <?= session()->get('user_role') === 'admin' ? '1fr 1fr' : '1fr' ?>; gap: 20px;">
            <div class="form-group">
                <label for="login" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Login</label>
                <input id="login" type="text" name="login" value="<?= old('login', $user['login']) ?>" required
                    style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
            </div>
            <?php if (session()->get('user_role') === 'admin'): ?>
            <div class="form-group">
                <label for="password" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Nova Senha</label>
                <input id="password" type="password" name="password" placeholder="Deixe em branco para não alterar"
                    style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
            </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="role" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Permissão (Nível de Acesso)</label>
            <select id="role" name="role" onchange="toggleFields()"
                style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none; cursor: pointer;">
                <option value="user"     <?= old('role', $user['role']) == 'user'     ? 'selected' : '' ?>>Cliente (Acesso ao Chat/App)</option>
                <?php if (session()->get('user_role') === 'admin'): ?>
                    <option value="operator" <?= old('role', $user['role']) == 'operator' ? 'selected' : '' ?>>Operador (Suporte/Financeiro)</option>
                    <option value="admin"    <?= old('role', $user['role']) == 'admin'    ? 'selected' : '' ?>>Administrador (Acesso Total)</option>
                <?php endif; ?>
            </select>
        </div>

        <!-- Client specific fields container -->
        <div id="client-fields" style="display: flex; flex-direction: column; gap: 20px;">
            <?php if (session()->get('user_role') === 'admin'): ?>
            <div class="form-group">
                <label for="fee_percent" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Taxa (%)</label>
                <input id="fee_percent" type="number" name="fee_percent" value="<?= old('fee_percent', $user['fee_percent']) ?>" step="0.01" required
                    style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
            </div>
            <?php elseif (session()->get('user_role') === 'operator'): ?>
            <div class="form-group">
                <label for="fee_percent" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Spread / Taxa (%)</label>
                <input id="fee_percent" type="number" value="<?= old('fee_percent', $user['fee_percent']) ?>" step="0.01" disabled
                    style="width: 100%; background: rgba(15, 23, 42, 0.3); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: #64748b; outline: none; cursor: not-allowed;">
            </div>
            <?php endif; ?>

            <div class="form-group" style="border: 1px solid rgba(255,255,255,0.05); padding: 20px; border-radius: 12px; background: rgba(255,255,255,0.01);">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Carteiras USDT (TRC-20)</label>
                
                <div id="wallets-container" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 16px;">
                    <?php if (!empty($wallets)): ?>
                        <?php foreach ($wallets as $index => $w): ?>
                            <div class="wallet-row" style="display: flex; align-items: center; gap: 12px;">
                                <input type="radio" name="default_wallet" value="<?= esc($w['address']) ?>" <?= $w['is_default'] ? 'checked' : '' ?> <?= ($w['status'] ?? 'active') === 'inactive' ? 'disabled' : '' ?> title="Definir como padrão" style="width: 18px; height: 18px; cursor: pointer; accent-color: #3b82f6;">
                                <input type="text" name="wallets[]" value="<?= esc($w['address']) ?>" placeholder="Endereço da carteira USDT" required
                                    style="flex: 1; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none; font-family: monospace; opacity: <?= ($w['status'] ?? 'active') === 'active' ? '1' : '0.5' ?>;"
                                    oninput="updateRadioValue(this)">
                                <input type="hidden" name="wallet_statuses[]" value="<?= esc($w['status'] ?? 'active') ?>" class="wallet-status-input">
                                <button type="button" onclick="toggleWalletStatus(this)" class="status-toggle-btn"
                                    style="background: <?= ($w['status'] ?? 'active') === 'active' ? 'rgba(239, 68, 68, 0.1)' : 'rgba(16, 185, 129, 0.1)' ?>; border: 1px solid <?= ($w['status'] ?? 'active') === 'active' ? 'rgba(239, 68, 68, 0.2)' : 'rgba(16, 185, 129, 0.2)' ?>; color: <?= ($w['status'] ?? 'active') === 'active' ? '#f87171' : '#34d399' ?>; border-radius: 10px; padding: 12px 16px; cursor: pointer; font-weight: 600; transition: all 0.2s; min-width: 90px; text-align: center;">
                                    <?= ($w['status'] ?? 'active') === 'active' ? 'Desativar' : 'Ativar' ?>
                                </button>
                                <button type="button" onclick="removeWalletRow(this)"
                                    style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171; border-radius: 10px; padding: 12px 16px; cursor: pointer; font-weight: 600; transition: all 0.2s;">
                                    Remover
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="wallet-row" style="display: flex; align-items: center; gap: 12px;">
                            <input type="radio" name="default_wallet" value="" checked title="Definir como padrão" style="width: 18px; height: 18px; cursor: pointer; accent-color: #3b82f6;">
                            <input type="text" name="wallets[]" value="" placeholder="Endereço da carteira USDT" required
                                style="flex: 1; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none; font-family: monospace;"
                                oninput="updateRadioValue(this)">
                            <input type="hidden" name="wallet_statuses[]" value="active" class="wallet-status-input">
                            <button type="button" onclick="toggleWalletStatus(this)" class="status-toggle-btn"
                                style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171; border-radius: 10px; padding: 12px 16px; cursor: pointer; font-weight: 600; transition: all 0.2s; min-width: 90px; text-align: center;">
                                Desativar
                            </button>
                            <button type="button" onclick="removeWalletRow(this)"
                                style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171; border-radius: 10px; padding: 12px 16px; cursor: pointer; font-weight: 600; transition: all 0.2s;">
                                Remover
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="button" onclick="addWalletRow()" class="btn"
                    style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); font-size: 13px; font-weight: 600; padding: 10px 20px; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Adicionar Carteira
                </button>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="default_contract_type" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Tipo Operação</label>
                    <select id="default_contract_type" name="default_contract_type"
                        style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
                        <option value="d+0" <?= old('default_contract_type', $user['default_contract_type']) == 'd+0' ? 'selected' : '' ?>>D+0</option>
                        <option value="d+1" <?= old('default_contract_type', $user['default_contract_type']) == 'd+1' ? 'selected' : '' ?>>D+1</option>
                        <option value="d+2" <?= old('default_contract_type', $user['default_contract_type']) == 'd+2' ? 'selected' : '' ?>>D+2</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="daily_interest_rate" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Juros Diários (%)</label>
                    <input id="daily_interest_rate" type="number" name="daily_interest_rate" value="<?= old('daily_interest_rate', $user['daily_interest_rate']) ?>" step="0.01" required
                        style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
                </div>
                <div class="form-group">
                    <label for="allowed_delivery_types" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Tipos de Envio Permitidos</label>
                    <select id="allowed_delivery_types" name="allowed_delivery_types"
                        style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
                        <option value="all" <?= old('allowed_delivery_types', $user['allowed_delivery_types']) == 'all' ? 'selected' : '' ?>>Todos</option>
                        <option value="D+0" <?= old('allowed_delivery_types', $user['allowed_delivery_types']) == 'D+0' ? 'selected' : '' ?>>Apenas D+0</option>
                        <option value="D+1" <?= old('allowed_delivery_types', $user['allowed_delivery_types']) == 'D+1' ? 'selected' : '' ?>>Apenas D+1</option>
                        <option value="D+2" <?= old('allowed_delivery_types', $user['allowed_delivery_types']) == 'D+2' ? 'selected' : '' ?>>Apenas D+2</option>
                    </select>
                </div>
            </div>
            <?php if ($canSetPurchaseModel): ?>
            <div class="form-group">
                <label for="purchase_model" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Modelo de Compra</label>
                <select id="purchase_model" name="purchase_model"
                    style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
                    <option value="usdt" <?= old('purchase_model', $user['purchase_model']) == 'usdt' ? 'selected' : '' ?>>Compra informando USDT</option>
                    <option value="brl" <?= old('purchase_model', $user['purchase_model']) == 'brl' ? 'selected' : '' ?>>Compra informando BRL</option>
                    <option value="both" <?= old('purchase_model', $user['purchase_model']) == 'both' ? 'selected' : '' ?>>Ambos (cliente escolhe)</option>
                </select>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="lock_only_with_balance" value="1" <?= old('lock_only_with_balance', $user['lock_only_with_balance'] ?? 0) == 1 ? 'checked' : '' ?> style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Travar apenas com saldo em conta
                </label>
            </div>
            <?php endif; ?>
        </div>

        <!-- Permissions container (for Admin / Operator) -->
        <div id="permission-fields" style="display: none; flex-direction: column; gap: 15px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px;">
            <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Permissões do Painel de Controle</label>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; background: rgba(15, 23, 42, 0.2); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 10px;">
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="usuarios" <?= in_array('usuarios', $savedPermissions) ? 'checked' : '' ?> style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Gerenciar Usuários
                </label>
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="transacoes" <?= in_array('transacoes', $savedPermissions) ? 'checked' : '' ?> style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Gerenciar Transações
                </label>
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="enviar_usdt" <?= in_array('enviar_usdt', $savedPermissions) ? 'checked' : '' ?> style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Enviar USDT (Fila/Contratos)
                </label>
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="lots" <?= in_array('lots', $savedPermissions) ? 'checked' : '' ?> style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Gerenciar Lotes USDT
                </label>
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="suppliers" <?= in_array('suppliers', $savedPermissions) ? 'checked' : '' ?> style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Gerenciar Fornecedores
                </label>
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="deposits" <?= in_array('deposits', $savedPermissions) ? 'checked' : '' ?> style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Confirmar Depósitos
                </label>
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="settings" <?= in_array('settings', $savedPermissions) ? 'checked' : '' ?> style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Configurações Gerais
                </label>
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="purchase_model" <?= in_array('purchase_model', $savedPermissions) ? 'checked' : '' ?> style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Gerenciar Modelo de Compra de Clientes
                </label>
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="edit_deposit_amount" <?= in_array('edit_deposit_amount', $savedPermissions) ? 'checked' : '' ?> style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Corrigir Valor de Depósitos (IA)
                </label>
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="conciliation" <?= in_array('conciliation', $savedPermissions) ? 'checked' : '' ?> style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Conciliação
                </label>
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="chat" <?= in_array('chat', $savedPermissions) ? 'checked' : '' ?> style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Atendimento Chat
                </label>
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 10px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">Salvar Alterações</button>
            <a href="<?= url_to('admin_users_view', $user['id']) ?>" class="btn" style="background: rgba(255,255,255,0.05); color: #94a3b8;">Cancelar</a>
        </div>
    </form>
</div>

<script>
function toggleFields() {
    const role = document.getElementById('role').value;
    const clientFields = document.getElementById('client-fields');
    const permissionFields = document.getElementById('permission-fields');
    const requiredInputs = clientFields.querySelectorAll('input[required]');

    if (role === 'admin' || role === 'operator') {
        clientFields.style.display = 'none';
        permissionFields.style.display = 'flex';
        
        requiredInputs.forEach(input => {
            input.dataset.wasRequired = 'true';
            input.removeAttribute('required');
        });
    } else {
        clientFields.style.display = 'flex';
        permissionFields.style.display = 'none';
        
        requiredInputs.forEach(input => {
            if (input.dataset.wasRequired === 'true') {
                input.setAttribute('required', 'required');
            }
        });
    }
}

function updateRadioValue(input) {
    const row = input.closest('.wallet-row');
    const radio = row.querySelector('input[type="radio"]');
    radio.value = input.value;
}

function removeWalletRow(button) {
    const container = document.getElementById('wallets-container');
    if (container.querySelectorAll('.wallet-row').length > 1) {
        button.closest('.wallet-row').remove();
    } else {
        alert('O usuário deve possuir pelo menos uma carteira cadastrada.');
    }
}

function toggleWalletStatus(button) {
    const row = button.closest('.wallet-row');
    const statusInput = row.querySelector('.wallet-status-input');
    const textInput = row.querySelector('input[type="text"]');
    const radio = row.querySelector('input[type="radio"]');
    
    if (statusInput.value === 'active') {
        statusInput.value = 'inactive';
        button.textContent = 'Ativar';
        button.style.background = 'rgba(16, 185, 129, 0.1)';
        button.style.borderColor = 'rgba(16, 185, 129, 0.2)';
        button.style.color = '#34d399';
        textInput.style.opacity = '0.5';
        
        radio.disabled = true;
        if (radio.checked) {
            radio.checked = false;
            
            const container = document.getElementById('wallets-container');
            const allRadios = container.querySelectorAll('input[type="radio"]');
            const activeRadio = Array.from(allRadios).find(r => !r.disabled);
            if (activeRadio) {
                activeRadio.checked = true;
            }
        }
    } else {
        statusInput.value = 'active';
        button.textContent = 'Desativar';
        button.style.background = 'rgba(239, 68, 68, 0.1)';
        button.style.borderColor = 'rgba(239, 68, 68, 0.2)';
        button.style.color = '#f87171';
        textInput.style.opacity = '1';
        
        radio.disabled = false;
    }
}

function addWalletRow() {
    const container = document.getElementById('wallets-container');
    const div = document.createElement('div');
    div.className = 'wallet-row';
    div.style.display = 'flex';
    div.style.alignItems = 'center';
    div.style.gap = '12px';
    div.innerHTML = `
        <input type="radio" name="default_wallet" value="" title="Definir como padrão" style="width: 18px; height: 18px; cursor: pointer; accent-color: #3b82f6;">
        <input type="text" name="wallets[]" value="" placeholder="Endereço da carteira USDT" required
            style="flex: 1; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none; font-family: monospace;"
            oninput="updateRadioValue(this)">
        <input type="hidden" name="wallet_statuses[]" value="active" class="wallet-status-input">
        <button type="button" onclick="toggleWalletStatus(this)" class="status-toggle-btn"
            style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171; border-radius: 10px; padding: 12px 16px; cursor: pointer; font-weight: 600; transition: all 0.2s; min-width: 90px; text-align: center;">
            Desativar
        </button>
        <button type="button" onclick="removeWalletRow(this)"
            style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171; border-radius: 10px; padding: 12px 16px; cursor: pointer; font-weight: 600; transition: all 0.2s;">
            Remover
        </button>
    `;
    container.appendChild(div);
    
    const radios = container.querySelectorAll('input[type="radio"]');
    if (radios.length === 1) {
        radios[0].checked = true;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    toggleFields();
});
</script>
<?= $this->endSection() ?>
