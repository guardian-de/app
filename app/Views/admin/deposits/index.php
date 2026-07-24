<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<?php
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var int $per_page */
/** @var array $deposits */
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
    .dep-table { width: 100%; border-collapse: collapse; }
    .dep-table th {
        font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 700;
        padding: 12px 16px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .dep-table td { padding: 14px 16px; border-bottom: 1px solid rgba(255,255,255,0.04); font-size: 13px; }
    .dep-table tr:hover td { background: rgba(255,255,255,0.02); }
    .badge {
        display: inline-block; font-size: 11px; font-weight: 700; padding: 3px 10px;
        border-radius: 20px; text-transform: uppercase;
    }
    .badge-pending  { background: rgba(251,191,36,0.12);  color: #fbbf24; border: 1px solid rgba(251,191,36,0.25); }
    .badge-accepted { background: rgba(16,185,129,0.12);  color: #10b981; border: 1px solid rgba(16,185,129,0.25); }
    .badge-reversed { background: rgba(239,68,68,0.12);   color: #f87171; border: 1px solid rgba(239,68,68,0.25); }
</style>

<div class="header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1 style="font-size: 24px; color: white;">Depósitos</h1>
    <?php if ($filters['status'] === 'pending'): ?>
        <a href="<?= url_to('admin_deposits') ?>?status=all" class="btn btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <span>Mostrar Todos</span>
        </a>
    <?php else: ?>
        <a href="<?= url_to('admin_deposits') ?>?status=pending" class="btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.05); color: #94a3b8;">
            <span>Mostrar Apenas Pendentes</span>
        </a>
    <?php endif; ?>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div style="background: rgba(34,197,94,0.1); color: #4ade80; padding: 14px; border-radius: 10px; margin-bottom: 20px; border: 1px solid rgba(34,197,94,0.2); font-size: 14px;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
    <div style="background: rgba(239,68,68,0.1); color: #f87171; padding: 14px; border-radius: 10px; margin-bottom: 20px; border: 1px solid rgba(239,68,68,0.2); font-size: 14px;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<form method="get" action="<?= url_to('admin_deposits') ?>">
    <div class="filters-row">
        <div class="filter-group">
            <label>Cliente</label>
            <input type="text" name="search" value="<?= esc($filters['search']) ?>" placeholder="Login ou nome...">
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="status">
                <option value="all"      <?= $filters['status'] === 'all' || $filters['status'] === '' ? 'selected' : '' ?>>Todos</option>
                <option value="pending"  <?= $filters['status'] === 'pending'  ? 'selected' : '' ?>>Pendente</option>
                <option value="accepted" <?= $filters['status'] === 'accepted' ? 'selected' : '' ?>>Aceito</option>
                <option value="reversed" <?= $filters['status'] === 'reversed' ? 'selected' : '' ?>>Revertido</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Data início</label>
            <input type="date" name="start_date" value="<?= esc($filters['startDate']) ?>">
        </div>
        <div class="filter-group">
            <label>Data fim</label>
            <input type="date" name="end_date" value="<?= esc($filters['endDate']) ?>">
        </div>
        <div class="filter-group">
            <label>Mostrar</label>
            <select name="per_page">
                <?php foreach ([25, 50, 100, 200] as $n): ?>
                    <option value="<?= $n ?>" <?= (int)$per_page === $n ? 'selected' : '' ?>><?= $n ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="padding: 9px 20px; height: fit-content; align-self: flex-end;">Filtrar</button>
        <a href="<?= url_to('admin_deposits') ?>" class="btn" style="padding: 9px 20px; height: fit-content; align-self: flex-end; background: rgba(255,255,255,0.05); color: #94a3b8;">Limpar</a>
    </div>
</form>

<div class="card" style="padding: 0; overflow: hidden;">
    <div style="overflow-x: auto;">
        <table class="dep-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Valor (BRL)</th>
                    <th>Status</th>
                    <th>Processado por</th>
                    <th>Data</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($deposits)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #94a3b8; padding: 40px;">Nenhum depósito encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($deposits as $d): ?>
                    <tr>
                        <td style="color: #94a3b8;">#<?= $d['id'] ?></td>
                        <td style="font-weight: 600; color: white;"><?= esc($d['user_login']) ?></td>
                        <td style="font-weight: 700; color: #34d399;">
                            <?php if ($d['amount'] === null): ?>
                                <?php if (($d['ocr_status'] ?? 'needs_review') === 'processing'): ?>
                                    <span style="color: #94a3b8;">Processando...</span>
                                  <?php else: ?>
                                    <span style="color: #fbbf24;">Não identificado</span>
                                <?php endif; ?>
                            <?php else: ?>
                                R$ <?= number_format($d['amount'], 2, ',', '.') ?>
                            <?php endif; ?>
                            <?php if ($d['status'] === 'pending' && ($d['ocr_status'] ?? 'needs_review') === 'needs_review'): ?>
                                <span title="IA não confirmou este comprovante — revisar manualmente" style="color:#fbbf24; margin-left:6px;">&#9888;</span>
                            <?php endif; ?>
                            <?php if (!empty($d['is_duplicate'])): ?>
                                <span title="Possível comprovante duplicado" style="background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; margin-left: 6px; text-transform: uppercase;">Duplicado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($d['status'] === 'pending'): ?>
                                <span class="badge badge-pending">Pendente</span>
                            <?php elseif ($d['status'] === 'accepted'): ?>
                                <span class="badge badge-accepted">Aceito</span>
                            <?php else: ?>
                                <span class="badge badge-reversed">Revertido</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: #94a3b8;">
                            <?php
                            $actor = '—';
                            if ($d['status'] === 'accepted' && !empty($d['accepted_by_login'])) {
                                $actor = esc($d['accepted_by_login']);
                            } elseif ($d['status'] === 'reversed' && !empty($d['reversed_by_login'])) {
                                $actor = esc($d['reversed_by_login']);
                            } elseif ($d['status'] === 'rejected' && !empty($d['rejected_by_login'])) {
                                $actor = esc($d['rejected_by_login']);
                            }
                            echo $actor;
                            ?>
                        </td>
                        <td style="color: #94a3b8;"><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                        <td>
                            <a href="<?= url_to('admin_deposits_show', $d['id']) ?>" class="btn btn-primary" style="padding: 6px 14px; font-size: 12px;">Ver</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
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
        'search'     => $filters['search'] ?? '',
        'status'     => $filters['status'] ?? '',
        'start_date' => $filters['startDate'] ?? '',
        'end_date'   => $filters['endDate'] ?? '',
        'per_page'   => $per_page != 50 ? $per_page : '',
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

    <?php if($currentPage > 1): ?>
        <a href="<?= $buildUrl($currentPage - 1) ?>" class="page-btn">&#8592; Anterior</a>
    <?php else: ?>
        <span class="page-btn page-disabled">&#8592; Anterior</span>
    <?php endif; ?>

    <?php if($start > 1): ?>
        <a href="<?= $buildUrl(1) ?>" class="page-btn">1</a>
        <?php if($start > 2): ?><span class="page-ellipsis">&hellip;</span><?php endif; ?>
    <?php endif; ?>

    <?php for($p = $start; $p <= $end; $p++): ?>
        <?php if($p === $currentPage): ?>
            <span class="page-btn page-active"><?= $p ?></span>
        <?php else: ?>
            <a href="<?= $buildUrl($p) ?>" class="page-btn" aria-label="Página <?= $p ?>"><?= $p ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if($end < $pageCount): ?>
        <?php if($end < $pageCount - 1): ?><span class="page-ellipsis">&hellip;</span><?php endif; ?>
        <a href="<?= $buildUrl($pageCount) ?>" class="page-btn" aria-label="Página <?= $pageCount ?>"><?= $pageCount ?></a>
    <?php endif; ?>

    <?php if($currentPage < $pageCount): ?>
        <a href="<?= $buildUrl($currentPage + 1) ?>" class="page-btn">Próximo &#8594;</a>
    <?php else: ?>
        <span class="page-btn page-disabled">Próximo &#8594;</span>
    <?php endif; ?>

</div>

<style>
    .page-btn {
        display: inline-block; padding: 8px 14px;
        background: rgba(255,255,255,0.05); color: #94a3b8;
        border-radius: 8px; text-decoration: none;
        font-size: 14px; border: 1px solid rgba(255,255,255,0.1);
        transition: all 0.2s; user-select: none;
    }
    a.page-btn:hover { background: rgba(255,255,255,0.1); color: white; }
    .page-btn.page-active { background: #6366f1; color: white; border-color: #6366f1; font-weight: 600; }
    .page-btn.page-disabled { opacity: 0.35; cursor: default; pointer-events: none; }
    .page-ellipsis { display: inline-block; padding: 8px 6px; color: #64748b; font-size: 14px; }
</style>

<?= $this->endSection() ?>
