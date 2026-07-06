<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:30px;">
    <h1 style="font-size:24px;font-weight:700;">Fornecedores de USDT</h1>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div style="background:rgba(34,197,94,0.1);color:#4ade80;padding:15px;border-radius:12px;margin-bottom:20px;border:1px solid rgba(34,197,94,0.2);font-size:14px;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
    <div style="background:rgba(239,68,68,0.1);color:#f87171;padding:15px;border-radius:12px;margin-bottom:20px;border:1px solid rgba(239,68,68,0.2);font-size:14px;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">

    <!-- Lista -->
    <div class="card" style="padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                    <th style="padding:14px 20px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Fornecedor</th>
                    <th style="padding:14px 20px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Status</th>
                    <th style="padding:14px 20px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Cadastrado em</th>
                    <th style="padding:14px 20px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($suppliers)): ?>
                    <tr>
                        <td colspan="4" style="padding:40px;text-align:center;color:#64748b;font-size:14px;">Nenhum fornecedor cadastrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($suppliers as $s): ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.04);transition:background 0.15s;"
                            onmouseover="this.style.background='rgba(255,255,255,0.02)'"
                            onmouseout="this.style.background=''">
                            <td style="padding:14px 20px;font-size:14px;font-weight:600;color:white;"><?= esc($s['name']) ?></td>
                            <td style="padding:14px 20px;">
                                <?php if ($s['enabled']): ?>
                                    <span style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:700;background:rgba(52,211,153,0.1);color:#34d399;">Ativo</span>
                                <?php else: ?>
                                    <span style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:700;background:rgba(100,116,139,0.1);color:#64748b;">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:14px 20px;font-size:12px;color:#64748b;"><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
                            <td style="padding:14px 20px;text-align:right;">
                                <form action="<?= url_to('admin_suppliers_toggle', $s['id']) ?>" method="POST" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit"
                                        style="font-size:12px;font-weight:600;cursor:pointer;border:none;background:none;padding:6px 12px;border-radius:8px;<?= $s['enabled'] ? 'color:#f87171;background:rgba(239,68,68,0.08);' : 'color:#4ade80;background:rgba(34,197,94,0.08);' ?>">
                                        <?= $s['enabled'] ? 'Desativar' : 'Ativar' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Formulário -->
    <div class="card" style="padding:24px;">
        <h3 style="font-size:16px;font-weight:700;color:white;margin-bottom:20px;">Novo Fornecedor</h3>
        <form action="<?= url_to('admin_suppliers_store') ?>" method="POST" style="display:flex;flex-direction:column;gap:16px;">
            <?= csrf_field() ?>
            <div>
                <label style="display:block;font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Nome *</label>
                <input type="text" name="name" placeholder="Ex: OKX, Bybit..."
                    style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:14px;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#334155'"
                    required>
            </div>
            <button type="submit"
                style="padding:12px;background:#6366f1;color:white;border:none;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;">
                Cadastrar
            </button>
        </form>
    </div>

</div>

<style>
    .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 24px; backdrop-filter: blur(10px); }
</style>

<?= $this->endSection() ?>
