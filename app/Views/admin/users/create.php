<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="header">
    <h1 style="font-size: 24px; color: white;">Novo Usuário</h1>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <?php if(session()->getFlashdata('errors')): ?>
        <div style="background: rgba(248, 113, 113, 0.1); color: #f87171; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(248, 113, 113, 0.2); font-size: 14px;">
            <?php foreach(session()->getFlashdata('errors') as $error): ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= url_to('admin_users_store') ?>" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
        <?= csrf_field() ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Login</label>
                <input type="text" name="login" value="<?= old('login') ?>" required
                    style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
            </div>
            <div class="form-group">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Senha Temporária</label>
                <input type="password" name="password" required
                    style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
            </div>
        </div>

        <div class="form-group">
            <label for="role" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Permissão (Nível de Acesso)</label>
            <select id="role" name="role" onchange="toggleFields()"
                style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none; cursor: pointer;">
                <option value="user"     <?= old('role') == 'user'     ? 'selected' : '' ?>>Cliente (Acesso ao Chat/App)</option>
                <option value="operator" <?= old('role') == 'operator' ? 'selected' : '' ?>>Operador (Suporte/Financeiro)</option>
                <option value="admin"    <?= old('role') == 'admin'    ? 'selected' : '' ?>>Administrador (Acesso Total)</option>
            </select>
        </div>

        <!-- Client specific fields container -->
        <div id="client-fields" style="display: flex; flex-direction: column; gap: 20px;">
            <div class="form-group">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Taxa (%)</label>
                <input type="number" name="fee_percent" value="<?= old('fee_percent') ?: '10.00' ?>" step="0.01" required
                    style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
            </div>

            <div class="form-group">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Carteira USDT (TRC-20)</label>
                <input type="text" name="usdt_wallet" value="<?= old('usdt_wallet') ?>"
                    style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Tipo Operação</label>
                    <select name="default_contract_type"
                        style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
                        <option value="d+0" <?= old('default_contract_type') == 'd+0' ? 'selected' : '' ?>>D+0</option>
                        <option value="d+1" <?= old('default_contract_type') == 'd+1' ? 'selected' : '' ?>>D+1</option>
                        <option value="d+2" <?= old('default_contract_type') == 'd+2' ? 'selected' : '' ?>>D+2</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Juros Diários (%)</label>
                    <input type="number" name="daily_interest_rate" value="<?= old('daily_interest_rate') ?: '0.00' ?>" step="0.01" required
                        style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
                </div>
                <div class="form-group">
                    <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Tipos de Envio Permitidos</label>
                    <select name="allowed_delivery_types"
                        style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
                        <option value="all" <?= old('allowed_delivery_types') == 'all' ? 'selected' : '' ?>>Todos</option>
                        <option value="D+0" <?= old('allowed_delivery_types') == 'D+0' ? 'selected' : '' ?>>Apenas D+0</option>
                        <option value="D+1" <?= old('allowed_delivery_types') == 'D+1' ? 'selected' : '' ?>>Apenas D+1</option>
                        <option value="D+2" <?= old('allowed_delivery_types') == 'D+2' ? 'selected' : '' ?>>Apenas D+2</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Permissions container (for Admin / Operator) -->
        <div id="permission-fields" style="display: none; flex-direction: column; gap: 15px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px;">
            <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Permissões do Painel de Controle</label>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; background: rgba(15, 23, 42, 0.2); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 10px;">
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="usuarios" style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Gerenciar Usuários
                </label>
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="transacoes" style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Gerenciar Transações
                </label>
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="enviar_usdt" style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Enviar USDT (Fila/Contratos)
                </label>
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="lots" style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Gerenciar Lotes USDT
                </label>
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="suppliers" style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Gerenciar Fornecedores
                </label>
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="deposits" style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Confirmar Depósitos
                </label>
                <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; font-size: 14px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="permissions[]" value="settings" style="accent-color: #6366f1; width: 18px; height: 18px; cursor: pointer;">
                    Configurações Gerais
                </label>
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 10px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">Criar Usuário</button>
            <a href="<?= url_to('admin_users') ?>" class="btn" style="background: rgba(255,255,255,0.05); color: #94a3b8;">Cancelar</a>
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

document.addEventListener('DOMContentLoaded', () => {
    toggleFields();
});
</script>
<?= $this->endSection() ?>
