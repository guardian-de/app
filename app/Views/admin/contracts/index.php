<?php
/** @var array $contracts */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var array $filters */
/** @var int $per_page */
?>
<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="header" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap: wrap; gap: 15px;">
    <div style="display:flex; align-items:center; gap:20px; flex-wrap: wrap;">
        <h1 style="font-size: 24px; color: white; margin: 0;"><?= ($is_completed ?? false) ? 'Operações Concluídas' : 'Gerenciar Operações' ?></h1>
        <?php if ($is_completed ?? false): ?>
            <a href="<?= url_to('admin_contracts') ?>" class="btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.05); color: #94a3b8; font-size: 13px; padding: 8px 16px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.1);">
                <span>Mostrar Ativas</span>
            </a>
        <?php else: ?>
            <a href="<?= url_to('admin_contracts_completed') ?>" class="btn btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; padding: 8px 16px; border-radius: 6px;">
                <span>Mostrar Concluídas</span>
            </a>
        <?php endif; ?>
    </div>
    <span id="sse-status" title="Atualizações em tempo real"
        style="display:inline-flex; align-items:center; gap:6px; font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.05em;">
        <span id="sse-dot" style="width:7px; height:7px; border-radius:50%; background:#475569; display:inline-block;"></span>
        <span id="sse-label">Conectando…</span>
    </span>
</div>

<!-- Banner: novos contratos fora da view atual -->
<div id="sse-new-banner" style="display:none; align-items:center; justify-content:space-between; gap:12px; background:rgba(99,102,241,0.1); border:1px solid rgba(99,102,241,0.3); border-radius:10px; padding:11px 18px; margin-bottom:16px; font-size:13px; color:#818cf8;">
    <div style="display:flex; align-items:center; gap:10px;">
        <span style="width:7px; height:7px; border-radius:50%; background:#6366f1; display:inline-block; animation:ssePulse 1s ease-in-out infinite;"></span>
        <span id="sse-banner-text"></span>
    </div>
    <div style="display:flex; gap:8px;">
        <button id="sse-banner-reload" style="background:#6366f1; color:white; border:none; border-radius:6px; padding:5px 14px; font-size:12px; font-weight:700; cursor:pointer;">Carregar</button>
        <button id="sse-banner-dismiss" style="background:rgba(255,255,255,0.06); color:#94a3b8; border:1px solid rgba(255,255,255,0.1); border-radius:6px; padding:5px 10px; font-size:12px; cursor:pointer;">✕</button>
    </div>
</div>
<style>
@keyframes ssePulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.4; transform: scale(0.8); }
}
</style>

<?php if(session()->getFlashdata('success')): ?>
    <div style="background: rgba(34, 197, 94, 0.1); color: #4ade80; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(34, 197, 94, 0.2); font-size: 14px;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div style="background: rgba(239, 68, 68, 0.1); color: #f87171; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(239, 68, 68, 0.2); font-size: 14px;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<!-- Filtros -->
<form method="get" action="" style="margin-bottom: 20px;">
    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 18px 20px;">
        <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
            <div style="display: flex; flex-direction: column; gap: 5px; min-width: 80px;">
                <label for="f-id" style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">ID</label>
                <input id="f-id" type="number" name="id" value="<?= esc($filters['id'] ?? '') ?>" placeholder="Ex: 40"
                    style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 8px 12px; color: white; font-size: 13px; width: 90px; outline: none;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 160px;">
                <label for="f-user" style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Usuário</label>
                <input id="f-user" type="text" name="user" value="<?= esc($filters['user'] ?? '') ?>" placeholder="Nome do usuário"
                    style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 8px 12px; color: white; font-size: 13px; width: 100%; outline: none;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 5px; min-width: 140px;">
                <label for="f-start" style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Vencimento de</label>
                <input id="f-start" type="date" name="start_date" value="<?= esc($filters['start_date'] ?? '') ?>"
                    style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 8px 12px; color: white; font-size: 13px; outline: none; color-scheme: dark;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 5px; min-width: 140px;">
                <label for="f-end" style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Vencimento até</label>
                <input id="f-end" type="date" name="end_date" value="<?= esc($filters['end_date'] ?? '') ?>"
                    style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 8px 12px; color: white; font-size: 13px; outline: none; color-scheme: dark;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 5px; min-width: 150px;">
                <label for="f-status" style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Status Pagamento</label>
                <select id="f-status" name="status"
                    style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 8px 12px; color: white; font-size: 13px; outline: none; cursor: pointer;">
                    <option value="">Todos</option>
                    <option value="pending"       <?= ($filters['status'] ?? '') === 'pending'        ? 'selected' : '' ?>>Pendente</option>
                    <option value="partially_paid"<?= ($filters['status'] ?? '') === 'partially_paid' ? 'selected' : '' ?>>Parcialmente Pago</option>
                    <option value="paid"          <?= ($filters['status'] ?? '') === 'paid'           ? 'selected' : '' ?>>Pago</option>
                    <option value="overdue"       <?= ($filters['status'] ?? '') === 'overdue'        ? 'selected' : '' ?>>Vencido</option>
                    <option value="sent"          <?= ($filters['status'] ?? '') === 'sent'           ? 'selected' : '' ?>>Enviado</option>
                </select>
            </div>
            <div style="display: flex; flex-direction: column; gap: 5px; min-width: 140px;">
                <label for="f-delivery" style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Fechamento</label>
                <select id="f-delivery" name="delivery_status"
                    style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 8px 12px; color: white; font-size: 13px; outline: none; cursor: pointer;">
                    <option value="">Todos</option>
                    <option value="em_aberto"  <?= ($filters['delivery_status'] ?? '') === 'em_aberto'  ? 'selected' : '' ?>>Em aberto</option>
                    <option value="concluido"  <?= ($filters['delivery_status'] ?? '') === 'concluido'  ? 'selected' : '' ?>>Concluído</option>
                </select>
            </div>
            <div style="display: flex; flex-direction: column; gap: 5px; min-width: 90px;">
                <label for="f-perpage" style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Por página</label>
                <select id="f-perpage" name="per_page"
                    style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 8px 12px; color: white; font-size: 13px; outline: none; cursor: pointer;">
                    <?php foreach([15, 25, 50, 100] as $opt): ?>
                        <option value="<?= $opt ?>" <?= $per_page === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display: flex; gap: 8px; align-items: flex-end; padding-bottom: 1px;">
                <button type="submit"
                    style="background: #6366f1; color: white; border: none; border-radius: 8px; padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap;">
                    Filtrar
                </button>
                <a href="<?= current_url() ?>"
                    style="background: rgba(255,255,255,0.06); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 8px 14px; font-size: 13px; text-decoration: none; white-space: nowrap; font-weight: 500;">
                    Limpar
                </a>
            </div>
        </div>
    </div>
</form>

<div class="card">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <th style="padding: 15px; color: #94a3b8; font-size: 12px; text-transform: uppercase;">ID</th>
                    <th style="padding: 15px; color: #94a3b8; font-size: 12px; text-transform: uppercase;">Usuário</th>
                    <th style="padding: 15px; color: #94a3b8; font-size: 12px; text-transform: uppercase;">Total</th>
                    <th style="padding: 15px; color: #94a3b8; font-size: 12px; text-transform: uppercase;">Taxa</th>
                    <th style="padding: 15px; color: #94a3b8; font-size: 12px; text-transform: uppercase;">Saldo Devedor</th>
                    <th style="padding: 15px; color: #94a3b8; font-size: 12px; text-transform: uppercase;">Vencimento</th>
                    <th style="padding: 15px; color: #94a3b8; font-size: 12px; text-transform: uppercase;">Status Pag.</th>
                    <th style="padding: 15px; color: #94a3b8; font-size: 12px; text-transform: uppercase;">Fechamento</th>
                    <th style="padding: 15px; color: #94a3b8; font-size: 12px; text-transform: uppercase;">Ações</th>
                </tr>
            </thead>
            <tbody id="contracts-tbody">
                <?php foreach($contracts as $contract): ?>
                    <?= view('admin/contracts/_row', ['contract' => $contract]) ?>
                <?php endforeach; ?>
                <?php if(empty($contracts)): ?>
                    <tr id="empty-row">
                        <td colspan="9" style="padding: 40px; text-align: center; color: #64748b; font-size: 14px;">
                            Nenhuma operação encontrada com os filtros aplicados.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Paginação -->
<?php
    $currentPage = $pager->getCurrentPage();
    $pageCount   = $pager->getPageCount();
    $queryBase   = array_filter([
        'id'              => $filters['id'] ?? '',
        'user'            => $filters['user'] ?? '',
        'start_date'      => $filters['start_date'] ?? '',
        'end_date'        => $filters['end_date'] ?? '',
        'status'          => $filters['status'] ?? '',
        'delivery_status' => $filters['delivery_status'] ?? '',
        'per_page'        => $per_page != 15 ? $per_page : '',
    ]);

    $buildUrl = function(int $page) use ($queryBase): string {
        $params = array_filter(array_merge($queryBase, ['page' => $page > 1 ? $page : '']));
        $qs = http_build_query($params);
        return current_url() . ($qs ? '?' . $qs : '');
    };

    $window = 2;
    $start  = max(1, $currentPage - $window);
    $end    = min($pageCount, $currentPage + $window);
?>
<div style="margin-top: 20px; display: flex; justify-content: center; align-items: center; gap: 6px; flex-wrap: wrap;">

    <!-- Anterior -->
    <?php if($currentPage > 1): ?>
        <a href="<?= $buildUrl($currentPage - 1) ?>" class="page-btn">&#8592; Anterior</a>
    <?php else: ?>
        <span class="page-btn page-disabled">&#8592; Anterior</span>
    <?php endif; ?>

    <!-- Primeira página se não está na janela -->
    <?php if($start > 1): ?>
        <a href="<?= $buildUrl(1) ?>" class="page-btn" aria-label="Página 1">1</a>
        <?php if($start > 2): ?><span class="page-ellipsis">&hellip;</span><?php endif; ?>
    <?php endif; ?>

    <!-- Páginas da janela -->
    <?php for($p = $start; $p <= $end; $p++): ?>
        <?php if($p === $currentPage): ?>
            <span class="page-btn page-active"><?= $p ?></span>
        <?php else: ?>
            <a href="<?= $buildUrl($p) ?>" class="page-btn" aria-label="Página <?= $p ?>"><?= $p ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <!-- Última página se não está na janela -->
    <?php if($end < $pageCount): ?>
        <?php if($end < $pageCount - 1): ?><span class="page-ellipsis">&hellip;</span><?php endif; ?>
        <a href="<?= $buildUrl($pageCount) ?>" class="page-btn" aria-label="Página <?= $pageCount ?>"><?= $pageCount ?></a>
    <?php endif; ?>

    <!-- Próximo -->
    <?php if($currentPage < $pageCount): ?>
        <a href="<?= $buildUrl($currentPage + 1) ?>" class="page-btn">Próximo &#8594;</a>
    <?php else: ?>
        <span class="page-btn page-disabled">Próximo &#8594;</span>
    <?php endif; ?>

</div>

<style>
    .page-btn {
        display: inline-block;
        padding: 8px 14px;
        background: rgba(255,255,255,0.05);
        color: #94a3b8;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        border: 1px solid rgba(255,255,255,0.1);
        transition: all 0.2s;
        user-select: none;
    }
    a.page-btn:hover {
        background: rgba(255,255,255,0.1);
        color: white;
    }
    .page-btn.page-active {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
        font-weight: 600;
    }
    .page-btn.page-disabled {
        opacity: 0.35;
        cursor: default;
        pointer-events: none;
    }
    .page-ellipsis {
        display: inline-block;
        padding: 8px 6px;
        color: #64748b;
        font-size: 14px;
    }
    select option { background: #1e2030; color: white; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const tbody    = document.getElementById('contracts-tbody');
    const banner   = document.getElementById('sse-new-banner');
    const dot      = document.getElementById('sse-dot');
    const label    = document.getElementById('sse-label');
    const rowBase  = '<?= site_url('admin/contracts/row/') ?>';
    const pollUrl  = '<?= url_to('admin_contracts_updates') ?>';

    let since    = '<?= date('Y-m-d H:i:s') ?>';
    let newCount = 0;
    let failures = 0;

    function setStatus(state) {
        if (state === 'live') {
            dot.style.background = '#4ade80';
            dot.style.animation  = 'ssePulse 2s ease-in-out infinite';
            label.textContent    = 'Ao vivo';
            label.style.color    = '#4ade80';
        } else if (state === 'error') {
            dot.style.background = '#f87171';
            dot.style.animation  = '';
            label.textContent    = 'Sem conexão';
            label.style.color    = '#f87171';
        }
    }

    function flashRow(row) {
        row.style.transition = 'background 0s';
        row.style.background = 'rgba(99,102,241,0.18)';
        setTimeout(function () {
            row.style.transition = 'background 0.8s ease';
            row.style.background = '';
        }, 50);
        setTimeout(function () { row.style.transition = ''; }, 900);
    }

    function handleUpdate(data) {
        const existing = tbody.querySelector('tr[data-contract-id="' + data.id + '"]');

        if (existing) {
            fetch(rowBase + data.id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.ok ? r.text() : null; })
                .then(function (html) {
                    if (!html) return;
                    const tmp = document.createElement('tbody');
                    tmp.innerHTML = html.trim();
                    const newRow = tmp.firstElementChild;
                    if (!newRow) return;
                    existing.replaceWith(newRow);
                    flashRow(newRow);
                });
        } else {
            newCount++;
            var plural = newCount > 1;
            document.getElementById('sse-banner-text').textContent =
                newCount + ' nova' + (plural ? 's' : '') +
                ' operaç' + (plural ? 'ões' : 'ão') + ' — clique para ver';
            banner.style.display = 'flex';
        }
    }

    function poll() {
        fetch(pollUrl + '?since=' + encodeURIComponent(since), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (rows) {
                failures = 0;
                setStatus('live');
                if (!rows || rows.length === 0) return;
                since = rows[rows.length - 1].updated_at;
                rows.forEach(handleUpdate);
            })
            .catch(function () {
                failures++;
                if (failures >= 3) setStatus('error');
            });
    }

    // Primeira chamada em 1s (captura compras recentes imediatamente)
    setTimeout(poll, 1000);
    setInterval(poll, 4000);

    document.getElementById('sse-banner-reload').addEventListener('click', function () {
        window.location.reload();
    });
    document.getElementById('sse-banner-dismiss').addEventListener('click', function () {
        banner.style.display = 'none';
        newCount = 0;
    });
})();
</script>
<?= $this->endSection() ?>
