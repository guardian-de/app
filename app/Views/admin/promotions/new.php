<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="header" style="display:flex;align-items:center;gap:15px;margin-bottom:30px;">
    <a href="<?= url_to('admin_promotions') ?>" class="btn" style="background:rgba(255,255,255,0.05);color:#94a3b8;padding:8px 12px;">← Voltar</a>
    <h1 style="font-size:24px;font-weight:700;">Lançar Promoção USDT</h1>
</div>

<?php if(session()->getFlashdata('error')): ?>
    <div style="background:rgba(239,68,68,0.1);color:#f87171;padding:15px;border-radius:12px;margin-bottom:25px;border:1px solid rgba(239,68,68,0.2);font-size:14px;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div style="max-width:600px;">
    <div class="card">
        <form action="<?= url_to('admin_promotions_store') ?>" method="POST" style="display:flex;flex-direction:column;gap:24px;">
            <?= csrf_field() ?>

            <div>
                <label style="display:block;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Fornecedor (Opcional)</label>
                <select name="supplier"
                    style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:14px;outline:none;transition:border-color 0.2s;"
                    onfocus="this.style.borderColor='#f43f5e'" onblur="this.style.borderColor='#334155'">
                    <option value="">— Sem Vínculo (Avulso) —</option>
                    <?php if (!empty($suppliers)): ?>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= esc($s['name']) ?>" <?= old('supplier') === $s['name'] ? 'selected' : '' ?>>
                                <?= esc($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <label style="display:block;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Quantidade USDT *</label>
                    <input type="number" name="usdt_amount" id="usdt_amount" value="<?= old('usdt_amount') ?>" step="0.0001" min="0.0001" placeholder="Ex: 1000.00" required
                        style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:14px;outline:none;transition:border-color 0.2s;"
                        onfocus="this.style.borderColor='#f43f5e'" onblur="this.style.borderColor='#334155'">
                </div>
                <div>
                    <label style="display:block;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Taxa por USDT (Cotação da Promoção) *</label>
                    <input type="number" name="conversion_rate" id="conversion_rate" value="<?= old('conversion_rate') ?>" step="0.0001" min="0.0001" placeholder="Ex: 5.2000" required
                        style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:14px;outline:none;transition:border-color 0.2s;"
                        onfocus="this.style.borderColor='#f43f5e'" onblur="this.style.borderColor='#334155'">
                </div>
            </div>

            <div>
                <label style="display:block;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Total Pago BRL (Valor Pago)</label>
                <div style="padding:12px 16px;background:rgba(15,23,42,0.6);border:1px solid #1e293b;border-radius:12px;color:#60a5fa;font-size:16px;font-weight:700;" id="total_brl_display">R$ —</div>
            </div>

            <div>
                <label style="display:block;font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Prazo de Entrega (Fluxo)</label>
                <select name="delivery_type"
                    style="width:100%;padding:12px 16px;background:#0f172a;border:1px solid #334155;border-radius:12px;color:white;font-size:14px;outline:none;transition:border-color 0.2s;"
                    onfocus="this.style.borderColor='#f43f5e'" onblur="this.style.borderColor='#334155'">
                    <option value="">— Não informado —</option>
                    <option value="d+0" <?= old('delivery_type') === 'd+0' ? 'selected' : '' ?>>D+0 (Spot)</option>
                    <option value="d+1" <?= old('delivery_type') === 'd+1' ? 'selected' : '' ?>>D+1</option>
                    <option value="d+2" <?= old('delivery_type') === 'd+2' ? 'selected' : '' ?>>D+2</option>
                </select>
            </div>

            <button type="submit" class="btn" style="width:100%;justify-content:center;padding:14px;background:#f43f5e;color:white;font-weight:700;border:none;border-radius:12px;cursor:pointer;box-shadow: 0 4px 12px rgba(244, 63, 94, 0.2);">
                Lançar Promoção
            </button>
        </form>
    </div>
</div>

<script>
(function () {
    const usdtInput    = document.getElementById('usdt_amount');
    const rateInput    = document.getElementById('conversion_rate');
    const totalDisplay = document.getElementById('total_brl_display');

    function recalc() {
        const usdt = parseFloat(usdtInput.value) || 0;
        const rate = parseFloat(rateInput.value) || 0;
        const total = Math.round(usdt * rate * 100) / 100;

        totalDisplay.textContent = usdt > 0 && rate > 0
            ? 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})
            : 'R$ —';
    }

    usdtInput.addEventListener('input', recalc);
    rateInput.addEventListener('input', recalc);

    recalc();
})();
</script>

<style>
    .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 24px; padding: 30px; backdrop-filter: blur(10px); }
</style>

<?= $this->endSection() ?>
