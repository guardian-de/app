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

    <form action="<?= url_to('admin_users_update', $user['id']) ?>" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
        <?= csrf_field() ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="login" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Login</label>
                <input id="login" type="text" name="login" value="<?= old('login', $user['login']) ?>" required
                    style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
            </div>
            <div class="form-group">
                <label for="password" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Nova Senha</label>
                <input id="password" type="password" name="password" placeholder="Deixe em branco para não alterar"
                    style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="fee_percent" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Taxa (%)</label>
                <input id="fee_percent" type="number" name="fee_percent" value="<?= old('fee_percent', $user['fee_percent']) ?>" step="0.01" required
                    style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
            </div>
            <div class="form-group">
                <label for="score" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Score (Limite de Crédito)</label>
                <input id="score" type="number" name="score" value="<?= old('score', $user['score']) ?>" step="0.01" required
                    style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
            </div>
        </div>

        <div class="form-group">
            <label for="usdt_wallet" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Carteira USDT (TRC-20)</label>
            <input id="usdt_wallet" type="text" name="usdt_wallet" value="<?= old('usdt_wallet', $user['usdt_wallet']) ?>"
                style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
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

        <div class="form-group">
            <label for="role" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Permissão (Nível de Acesso)</label>
            <select id="role" name="role"
                style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none; cursor: pointer;">
                <option value="user"     <?= old('role', $user['role']) == 'user'     ? 'selected' : '' ?>>Cliente (Acesso ao Chat/App)</option>
                <option value="operator" <?= old('role', $user['role']) == 'operator' ? 'selected' : '' ?>>Operador (Suporte/Financeiro)</option>
                <option value="admin"    <?= old('role', $user['role']) == 'admin'    ? 'selected' : '' ?>>Administrador (Acesso Total)</option>
            </select>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 10px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">Salvar Alterações</button>
            <a href="<?= url_to('admin_users') ?>" class="btn" style="background: rgba(255,255,255,0.05); color: #94a3b8;">Cancelar</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
