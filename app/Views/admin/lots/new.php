<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="header" style="display:flex;align-items:center;gap:15px;margin-bottom:30px;">
    <a href="<?= url_to('admin_lots') ?>" class="btn" style="background:rgba(255,255,255,0.05);color:#94a3b8;padding:8px 12px;">← Voltar</a>
    <h1 style="font-size:24px;font-weight:700;">Registrar Compra de USDT</h1>
</div>

<?php if(session()->getFlashdata('error')): ?>
    <div style="background:rgba(239,68,68,0.1);color:#f87171;padding:15px;border-radius:12px;margin-bottom:25px;border:1px solid rgba(239,68,68,0.2);font-size:14px;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div style="max-width:600px;">
    <div class="card">
        <form action="<?= url_to('admin_lots_store') ?>" method="POST" style="display:flex;flex-direction:column;gap:24px;">
            <?= csrf_field() ?>

            <div>
                <label style="display:block;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Fornecedor *</label>
                <?php if (empty($suppliers)): ?>
                    <div style="padding:12px 16px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2);border-radius:12px;color:#f87171;font-size:13px;">
                        Nenhum fornecedor ativo. <a href="<?= url_to('admin_suppliers') ?>" style="color:#60a5fa;text-decoration:underline;">Cadastre um fornecedor</a> antes de registrar um lote.
                    </div>
                <?php else: ?>
                    <select name="supplier" required
                        style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:14px;outline:none;transition:border-color 0.2s;"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#334155'">
                        <option value="">— Selecione —</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= esc($s['name']) ?>" <?= old('supplier') === $s['name'] ? 'selected' : '' ?>>
                                <?= esc($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <label style="display:block;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Quantidade USDT *</label>
                    <input type="number" name="usdt_amount" id="usdt_amount" value="<?= old('usdt_amount') ?>" step="0.0001" min="0.0001" placeholder="Ex: 1000.00" required
                        style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:14px;outline:none;transition:border-color 0.2s;"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#334155'">
                </div>
                <div>
                    <label style="display:block;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Taxa de Conversão (R$/USDT) *</label>
                    <input type="number" name="conversion_rate" id="conversion_rate" value="<?= old('conversion_rate') ?>" step="0.0001" min="0.0001" placeholder="Ex: 5.2000" required
                        style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:14px;outline:none;transition:border-color 0.2s;"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#334155'">
                </div>
            </div>

            <div>
                <label style="display:block;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Total Pago BRL</label>
                <div style="padding:12px 16px;background:rgba(15,23,42,0.6);border:1px solid #1e293b;border-radius:12px;color:#60a5fa;font-size:16px;font-weight:700;" id="total_brl_display">R$ —</div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <label style="display:block;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Fluxo do Cliente</label>
                    <select name="delivery_type"
                        style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:14px;outline:none;transition:border-color 0.2s;"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#334155'">
                        <option value="">— Não informado —</option>
                        <option value="d+0" <?= old('delivery_type') === 'd+0' ? 'selected' : '' ?>>D+0 (Spot)</option>
                        <option value="d+1" <?= old('delivery_type') === 'd+1' ? 'selected' : '' ?>>D+1</option>
                        <option value="d+2" <?= old('delivery_type') === 'd+2' ? 'selected' : '' ?>>D+2</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Custo por USDT</label>
                    <div style="padding:12px 16px;background:rgba(15,23,42,0.6);border:1px solid #1e293b;border-radius:12px;color:#60a5fa;font-size:16px;font-weight:700;" id="cost-preview">R$ —</div>
                </div>
            </div>

            <div>
                <label style="display:block;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Data de Fechamento</label>
                <input type="date" name="closing_date" id="closing_date" value="<?= old('closing_date') ?>"
                    style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:14px;outline:none;transition:border-color 0.2s;color-scheme:dark;"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#334155'">
            </div>


            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;">
                Registrar Lote
            </button>
        </form>
    </div>
</div>

<script>
(function () {
    const usdtInput    = document.getElementById('usdt_amount');
    const rateInput    = document.getElementById('conversion_rate');
    const totalDisplay = document.getElementById('total_brl_display');
    const costDisplay  = document.getElementById('cost-preview');

    function recalc() {
        const usdt = parseFloat(usdtInput.value) || 0;
        const rate = parseFloat(rateInput.value) || 0;
        const total = Math.round(usdt * rate * 100) / 100;

        totalDisplay.textContent = usdt > 0 && rate > 0
            ? 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})
            : 'R$ —';

        costDisplay.textContent = usdt > 0 && rate > 0
            ? 'R$ ' + rate.toLocaleString('pt-BR', {minimumFractionDigits: 4, maximumFractionDigits: 4})
            : 'R$ —';
    }

    usdtInput.addEventListener('input', recalc);
    rateInput.addEventListener('input', recalc);

    recalc();


})();
</script>
<?= $this->endSection() ?>
