<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<?php
/** @var array $users */
/** @var float|null $latest_rate */
/** @var array $filters */
?>
<style>
    .filters-row { display: flex; gap: 12px; margin-bottom: 25px; flex-wrap: wrap; align-items: flex-end; }
    .filter-group { display: flex; flex-direction: column; gap: 6px; }
    .filter-group label { font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 600; }
    .filter-group input, .filter-group select {
        background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.08);
        color: white; padding: 8px 12px; border-radius: 8px; font-size: 13px; outline: none;
    }
</style>

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

<form method="get" action="<?= url_to('admin_users') ?>">
    <div class="filters-row">
        <div class="filter-group">
            <label>Login / Nome</label>
            <input type="text" name="search" value="<?= esc($filters['search'] ?? '') ?>" placeholder="Pesquisar usuário...">
        </div>
        <?php if (session()->get('user_role') === 'admin'): ?>
        <div class="filter-group">
            <label>Tipo de Usuário</label>
            <select name="role">
                <option value="">Todos</option>
                <option value="user" <?= ($filters['role'] ?? '') === 'user' ? 'selected' : '' ?>>Cliente</option>
                <option value="operator" <?= ($filters['role'] ?? '') === 'operator' ? 'selected' : '' ?>>Operador</option>
                <option value="admin" <?= ($filters['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary" style="padding: 9px 20px; height: fit-content; align-self: flex-end;">Filtrar</button>
        <a href="<?= url_to('admin_users') ?>" class="btn" style="padding: 9px 20px; height: fit-content; align-self: flex-end; background: rgba(255,255,255,0.05); color: #94a3b8; text-decoration: none;">Limpar</a>
    </div>
</form>

<div class="card" style="padding: 0; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: rgba(255,255,255,0.02);">
                <th style="padding: 18px 25px; text-align: left; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Login</th>
                <th style="padding: 18px 25px; text-align: left; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Permissão</th>
                <th style="padding: 18px 25px; text-align: left; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Spread (%)</th>
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
                        <?= number_format($user['fee_percent'] ?? 0, 2) ?>%
                    </span>
                </td>
                <td style="padding: 20px 25px; color: #64748b; font-size: 13px;"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                <td style="padding: 20px 25px; text-align: right;">
                    <div style="display: inline-flex; gap: 8px; justify-content: flex-end;">
                        <a href="<?= url_to('admin_users_view', $user['id']) ?>" class="btn btn-primary" style="background: var(--primary); color: white; padding: 8px 14px; font-size: 13px; text-decoration: none; border-radius: 6px;">Ver</a>
                        <a href="<?= url_to('admin_users_edit', $user['id']) ?>" class="btn" style="background: rgba(255,255,255,0.05); color: #cbd5e1; border: 1px solid rgba(255,255,255,0.1); padding: 8px 14px; font-size: 13px; text-decoration: none; border-radius: 6px;">Editar</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
