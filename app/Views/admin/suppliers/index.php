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
    <div class="card" style="padding:0;overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                    <th style="padding:14px 20px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Fornecedor</th>
                    <th style="padding:14px 20px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Carteira</th>
                    <th style="padding:14px 20px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Status</th>
                    <th style="padding:14px 20px;text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Cadastrado em</th>
                    <th style="padding:14px 20px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($suppliers)): ?>
                    <tr>
                        <td colspan="5" style="padding:40px;text-align:center;color:#64748b;font-size:14px;">Nenhum fornecedor cadastrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($suppliers as $s): ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.04);transition:background 0.15s;"
                            onmouseover="this.style.background='rgba(255,255,255,0.02)'"
                            onmouseout="this.style.background=''">
                            <td style="padding:14px 20px;font-size:14px;font-weight:600;color:white;"><?= esc($s['name']) ?></td>
                            <td style="padding:14px 20px;font-size:12px;color:#94a3b8;font-family:monospace;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <?php if (!empty($s['wallet'])): ?>
                                    <span title="<?= esc($s['wallet']) ?>" style="cursor:help;">
                                        <?= esc(substr($s['wallet'], 0, 8) . '...' . substr($s['wallet'], -6)) ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td style="padding:14px 20px;">
                                <?php if ($s['enabled']): ?>
                                    <span style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:700;background:rgba(52,211,153,0.1);color:#34d399;">Ativo</span>
                                <?php else: ?>
                                    <span style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:700;background:rgba(100,116,139,0.1);color:#64748b;">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:14px 20px;font-size:12px;color:#64748b;"><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
                            <td style="padding:14px 20px;text-align:right;">
                                <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;flex-wrap:nowrap;">
                                    <button type="button" onclick="editSupplier(<?= $s['id'] ?>, '<?= esc($s['name'], 'js') ?>', '<?= esc($s['wallet'] ?? '', 'js') ?>')"
                                        style="font-size:12px;font-weight:600;cursor:pointer;border:none;background:rgba(99,102,241,0.08);color:#818cf8;padding:6px 12px;border-radius:8px;transition:all 0.2s;">
                                        Editar
                                    </button>
                                    <form action="<?= url_to('admin_suppliers_toggle', $s['id']) ?>" method="POST" style="display:inline;margin:0;">
                                        <?= csrf_field() ?>
                                        <button type="submit"
                                            style="font-size:12px;font-weight:600;cursor:pointer;border:none;padding:6px 12px;border-radius:8px;<?= $s['enabled'] ? 'color:#f87171;background:rgba(239,68,68,0.08);' : 'color:#4ade80;background:rgba(34,197,94,0.08);' ?> transition:all 0.2s;">
                                            <?= $s['enabled'] ? 'Desativar' : 'Ativar' ?>
                                        </button>
                                    </form>
                                    <form action="<?= url_to('admin_suppliers_delete', $s['id']) ?>" method="POST" style="display:inline;margin:0;" onsubmit="return confirm('Deseja realmente excluir o fornecedor <?= esc($s['name'], 'js') ?>?')">
                                        <?= csrf_field() ?>
                                        <button type="submit"
                                            style="font-size:12px;font-weight:600;cursor:pointer;border:none;background:rgba(239,68,68,0.08);color:#f87171;padding:6px 12px;border-radius:8px;transition:all 0.2s;">
                                            Excluir
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Formulário -->
    <div class="card" style="padding:24px;">
        <h3 id="form-title" style="font-size:16px;font-weight:700;color:white;margin-bottom:20px;">Novo Fornecedor</h3>
        <form id="supplier-form" action="<?= url_to('admin_suppliers_store') ?>" method="POST" style="display:flex;flex-direction:column;gap:16px;">
            <?= csrf_field() ?>
            <div>
                <label style="display:block;font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Nome *</label>
                <input type="text" name="name" id="supplier-name" placeholder="Ex: OKX, Bybit..."
                    style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:14px;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#334155'"
                    required>
            </div>
            <div>
                <label style="display:block;font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Carteira do Fornecedor *</label>
                <input type="text" name="wallet" id="supplier-wallet" placeholder="Endereço de carteira..."
                    style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:14px;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#334155'"
                    required>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <button type="submit" id="submit-btn"
                    style="padding:12px;background:#6366f1;color:white;border:none;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;transition:all 0.2s;">
                    Cadastrar
                </button>
                <button type="button" id="cancel-btn" onclick="resetForm()"
                    style="display:none;padding:12px;background:rgba(255,255,255,0.08);color:#94a3b8;border:none;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;transition:all 0.2s;">
                    Cancelar
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    const storeUrl = "<?= url_to('admin_suppliers_store') ?>";
    const updateUrlBase = "<?= site_url('admin/suppliers/update') ?>";

    function editSupplier(id, name, wallet) {
        document.getElementById('form-title').textContent = 'Editar Fornecedor';
        document.getElementById('supplier-name').value = name;
        document.getElementById('supplier-wallet').value = wallet;
        document.getElementById('supplier-form').action = `${updateUrlBase}/${id}`;
        document.getElementById('submit-btn').textContent = 'Salvar Alterações';
        document.getElementById('cancel-btn').style.display = 'block';
        
        // Rola suavemente até o formulário
        document.getElementById('supplier-form').scrollIntoView({ behavior: 'smooth' });
    }

    function resetForm() {
        document.getElementById('form-title').textContent = 'Novo Fornecedor';
        document.getElementById('supplier-name').value = '';
        document.getElementById('supplier-wallet').value = '';
        document.getElementById('supplier-form').action = storeUrl;
        document.getElementById('submit-btn').textContent = 'Cadastrar';
        document.getElementById('cancel-btn').style.display = 'none';
    }
</script>

<style>
    .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 24px; backdrop-filter: blur(10px); }
</style>

<?= $this->endSection() ?>
