<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<style>
    /* Premium Print and Screen Stylesheet */
    @media print {
        body {
            background: #ffffff !important;
            color: #000000 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif !important;
        }
        /* Hide sidebar, layout header, actions, filters, search container, print button, and paginator buttons */
        aside, .sidebar, nav, header, .header, #pagination-controls, .btn-print, td:last-child, th:last-child, .filters-container, [style*="display: flex; gap: 15px; margin-bottom: 25px;"], [style*="display: flex; gap: 15px; margin-bottom: 25px; align-items: center;"] {
            display: none !important;
        }
        /* Stretch card and containers to 100% width */
        .content-wrapper, main, .card {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        /* Custom print header card */
        .print-header {
            display: block !important;
            margin-bottom: 25px;
            border-bottom: 2px solid #000000;
            padding-bottom: 12px;
        }
        .print-header h1 {
            font-size: 20px;
            color: #000000;
            margin: 0 0 5px 0;
            font-weight: 800;
            text-transform: uppercase;
        }
        .print-header p {
            font-size: 11px;
            color: #475569;
            margin: 0;
        }
        /* Print stats layout */
        .print-stats-summary {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 15px !important;
            margin-bottom: 25px !important;
        }
        .print-stats-summary div {
            border: 1px solid #cbd5e1 !important;
            padding: 12px !important;
            border-radius: 6px !important;
            background: #f8fafc !important;
        }
        .print-stats-summary span {
            color: #000000 !important;
        }
        /* Force tables layout and clean borders */
        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        th {
            background: #f1f5f9 !important;
            color: #000000 !important;
            border-bottom: 2px solid #94a3b8 !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            padding: 10px 12px !important;
            text-transform: uppercase !important;
        }
        td {
            color: #000000 !important;
            border-bottom: 1px solid #cbd5e1 !important;
            font-size: 10px !important;
            padding: 10px 12px !important;
        }
        tr {
            page-break-inside: avoid !important;
        }
        /* Force all matching table rows to display in print mode, overriding pagination! */
        .tx-row {
            display: table-row !important;
        }
        /* Render high-contrast simple badges for print */
        span[style*="border"] {
            border: 1px solid #000000 !important;
            color: #000000 !important;
            background: transparent !important;
            padding: 2px 6px !important;
            font-size: 8px !important;
            font-weight: 700 !important;
            border-radius: 3px !important;
            text-transform: uppercase !important;
        }
        span[style*="color: #10b981"] {
            color: #000000 !important;
            font-weight: 700 !important;
        }
        span[style*="color: #ef4444"] {
            color: #000000 !important;
            font-weight: 700 !important;
        }
    }

    /* Screen display settings */
    .print-header, .print-stats-summary {
        display: none;
    }

    .btn-print:hover {
        background: transparent !important;
        color: #ffffff !important;
        border-color: #ffffff !important;
    }

    /* Responsive table */
    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .tx-table {
        width: 100%;
        min-width: 860px;
        border-collapse: collapse;
    }
    @media (max-width: 1200px) {
        .col-comercial, .col-fee { display: none; }
        .tx-table { min-width: 680px; }
    }
    @media (max-width: 900px) {
        .col-status { display: none; }
        .tx-table { min-width: 540px; }
        .tx-table th, .tx-table td { padding: 14px 14px; }
    }
    @media (max-width: 640px) {
        .col-desc { display: none; }
        .tx-table { min-width: 360px; }
    }
</style>

<!-- Print-Only Header -->
<div class="print-header">
    <h1>EVO CORRETORA - RELATÓRIO DO EXTRATO FINANCEIRO</h1>
    <p>Data de Emissão: <?= date('d/m/Y H:i:s') ?> | Documento de Razão Contábil Oficial</p>
</div>

<!-- Print-Only Stats Summary -->
<div class="print-stats-summary">
    <div>
        <span style="font-size: 10px; text-transform: uppercase; color: #475569; display: block; margin-bottom: 4px; font-weight: 600;">Total Entradas (Créditos)</span>
        <span style="font-size: 16px; font-weight: 700;" id="print-total-credits">R$ 0,00</span>
    </div>
    <div>
        <span style="font-size: 10px; text-transform: uppercase; color: #475569; display: block; margin-bottom: 4px; font-weight: 600;">Total Saídas (Débitos)</span>
        <span style="font-size: 16px; font-weight: 700;" id="print-total-debits">0.00 USDT</span>
    </div>
    <div>
        <span style="font-size: 10px; text-transform: uppercase; color: #475569; display: block; margin-bottom: 4px; font-weight: 600;">Fluxo Líquido</span>
        <span style="font-size: 16px; font-weight: 700;" id="print-net-flow">R$ 0,00</span>
    </div>
</div>

<div class="header" style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <h1 style="font-size: 24px; color: white; margin: 0;">Extrato Geral (Razão Financeiro)</h1>
    <button onclick="window.print()" class="btn-print" style="background: #ffffff; color: #0f172a; border: 1px solid #ffffff; font-weight: 700; padding: 8px 16px; border-radius: 6px; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Imprimir / PDF
    </button>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div style="background: rgba(52, 211, 153, 0.1); color: #34d399; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid rgba(52, 211, 153, 0.2); font-size: 14px;">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<!-- Dynamic Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px;">
    <div style="background: rgba(15, 23, 42, 0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 20px; display: flex; flex-direction: column; gap: 8px;">
        <span style="color: #10b981; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Total Entradas (Créditos)</span>
        <span style="color: #10b981; font-size: 22px; font-weight: 700; letter-spacing: -0.02em;" id="stat-total-credits">R$ 0,00</span>
    </div>
    <div style="background: rgba(15, 23, 42, 0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 20px; display: flex; flex-direction: column; gap: 8px;">
        <span style="color: #ef4444; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Total Saídas (Débitos)</span>
        <span style="color: #ef4444; font-size: 22px; font-weight: 700; letter-spacing: -0.02em;" id="stat-total-debits">0.00 USDT</span>
    </div>
    <div style="background: rgba(15, 23, 42, 0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 20px; display: flex; flex-direction: column; gap: 8px;">
        <span style="color: white; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Fluxo Líquido</span>
        <span style="color: white; font-size: 22px; font-weight: 700; letter-spacing: -0.02em;" id="stat-net-flow">R$ 0,00</span>
    </div>
</div>

<!-- Filters Bar -->
<div style="display: flex; gap: 15px; margin-bottom: 25px; align-items: center; background: rgba(15, 23, 42, 0.3); padding: 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08); flex-wrap: wrap;">
    <div style="display: flex; flex-direction: column; gap: 6px;">
        <label style="color: #94a3b8; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Filtrar Tipo</label>
        <select id="filter-type" onchange="applyFilters(true)" style="background: #0f172a; border: 1px solid #334155; color: white; padding: 8px 12px; border-radius: 6px; outline: none; font-size: 13px; cursor: pointer;">
            <option value="all">Todos os Tipos</option>
            <option value="deposit">Depósito</option>
            <option value="withdrawal">Saque / Retirada</option>
            <option value="margin_lock">Débito</option>
            <option value="limit_release">Liberação de Limite</option>
            <option value="partial_amortization">Amortização Parcial</option>
            <option value="full_settlement">Liquidação Integral</option>
            <option value="late_fee">Multa por Atraso</option>
            <option value="adjustment_add">Saldo Adicionado</option>
            <option value="adjustment_subtract">Saldo Subtraído</option>
        </select>
    </div>
    <div style="display: flex; flex-direction: column; gap: 6px;">
        <label style="color: #94a3b8; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Natureza</label>
        <select id="filter-nature" onchange="applyFilters(true)" style="background: #0f172a; border: 1px solid #334155; color: white; padding: 8px 12px; border-radius: 6px; outline: none; font-size: 13px; cursor: pointer;">
            <option value="all">Todas</option>
            <option value="C">Entradas (Créditos)</option>
            <option value="D">Saídas (Débitos)</option>
        </select>
    </div>
    <div style="display: flex; flex-direction: column; gap: 6px;">
        <label style="color: #94a3b8; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Data Inicial</label>
        <input type="date" id="filter-start-date" onchange="applyFilters(true)" style="background: #0f172a; border: 1px solid #334155; color: white; padding: 7px 12px; border-radius: 6px; outline: none; font-size: 13px; cursor: pointer; color-scheme: dark;">
    </div>
    <div style="display: flex; flex-direction: column; gap: 6px;">
        <label style="color: #94a3b8; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Data Final</label>
        <input type="date" id="filter-end-date" onchange="applyFilters(true)" style="background: #0f172a; border: 1px solid #334155; color: white; padding: 7px 12px; border-radius: 6px; outline: none; font-size: 13px; cursor: pointer; color-scheme: dark;">
    </div>
    <div style="display: flex; flex-direction: column; gap: 6px; flex-grow: 1; min-width: 240px;">
        <label style="color: #94a3b8; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Pesquisa Rápida</label>
        <div style="position: relative;">
            <input type="text" id="search-input" onkeyup="applyFilters(true)" placeholder="Buscar por cliente ou descrição..." style="width: 100%; background: #0f172a; border: 1px solid #334155; color: white; padding: 8px 12px 8px 36px; border-radius: 6px; outline: none; font-size: 13px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </div>
    </div>
</div>

<!-- Statement Log Table Card -->
<div class="card" style="padding: 0; overflow: hidden; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08);">
    <div class="table-scroll">
    <table class="tx-table">
        <thead>
            <tr style="background: rgba(255,255,255,0.02);">
                <th style="padding: 18px 25px; text-align: left; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Data / Cliente</th>
                <th style="padding: 18px 25px; text-align: left; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Operação</th>
                <th class="col-desc" style="padding: 18px 25px; text-align: left; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Descrição do Lançamento</th>
                <th class="col-comercial" style="padding: 18px 25px; text-align: left; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Vl. Comercial</th>
                <th class="col-fee" style="padding: 18px 25px; text-align: left; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Taxa (BRL)</th>
                <th style="padding: 18px 25px; text-align: left; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Valor</th>
                <th class="col-status" style="padding: 18px 25px; text-align: left; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Status</th>
                <th style="padding: 18px 25px; text-align: right; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($transactions as $t): ?>
            <tr class="tx-row" data-type="<?= esc($t['operation_type']) ?>" data-nature="<?= esc($t['nature']) ?>" data-amount="<?= esc($t['amount']) ?>" data-date="<?= esc(date('Y-m-d', strtotime($t['transaction_date'])), 'attr') ?>" style="border-top: 1px solid rgba(255,255,255,0.05);">
                <td style="padding: 20px 25px;">
                    <div style="font-weight: 600; color: white;" class="client-name"><?= esc($t['user_name']) ?></div>
                    <div style="font-size: 12px; color: #64748b;"><?= date('d/m/Y H:i', strtotime($t['transaction_date'])) ?></div>
                </td>
                <td style="padding: 20px 25px;">
                    <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 5px;">
                    <?php if ($t['operation_type'] === 'deposit'): ?>
                        <span style="color: #10b981; font-size: 11px; font-weight: 700; background: rgba(16, 185, 129, 0.15); padding: 4px 8px; border-radius: 4px; text-transform: uppercase; white-space: nowrap;">Depósito</span>
                    <?php elseif ($t['operation_type'] === 'withdrawal'): ?>
                        <span style="color: #ef4444; font-size: 11px; font-weight: 700; background: rgba(239, 68, 68, 0.15); padding: 4px 8px; border-radius: 4px; text-transform: uppercase; white-space: nowrap;">Retirada</span>
                    <?php elseif ($t['operation_type'] === 'margin_lock'): ?>
                        <span style="color: #f59e0b; font-size: 11px; font-weight: 700; background: rgba(245, 158, 11, 0.15); padding: 4px 8px; border-radius: 4px; text-transform: uppercase; white-space: nowrap;">Débito</span>
                    <?php elseif ($t['operation_type'] === 'limit_release'): ?>
                        <span style="color: #3b82f6; font-size: 11px; font-weight: 700; background: rgba(59, 130, 246, 0.15); padding: 4px 8px; border-radius: 4px; text-transform: uppercase; white-space: nowrap;">Liberação</span>
                    <?php elseif ($t['operation_type'] === 'partial_amortization'): ?>
                        <span style="color: #a855f7; font-size: 11px; font-weight: 700; background: rgba(168, 85, 247, 0.15); padding: 4px 8px; border-radius: 4px; text-transform: uppercase; white-space: nowrap;">Amortização</span>
                    <?php elseif ($t['operation_type'] === 'full_settlement'): ?>
                        <span style="color: #ec4899; font-size: 11px; font-weight: 700; background: rgba(236, 72, 153, 0.15); padding: 4px 8px; border-radius: 4px; text-transform: uppercase; white-space: nowrap;">Quitação</span>
                    <?php elseif ($t['operation_type'] === 'late_fee'): ?>
                        <span style="color: #f97316; font-size: 11px; font-weight: 700; background: rgba(249, 115, 22, 0.15); padding: 4px 8px; border-radius: 4px; text-transform: uppercase; white-space: nowrap;">Multa</span>
                    <?php elseif ($t['operation_type'] === 'adjustment_add'): ?>
                        <span style="color: #10b981; font-size: 11px; font-weight: 700; background: rgba(16, 185, 129, 0.15); padding: 4px 8px; border-radius: 4px; text-transform: uppercase; white-space: nowrap;">Saldo Adicionado</span>
                    <?php elseif ($t['operation_type'] === 'adjustment_subtract'): ?>
                        <span style="color: #ef4444; font-size: 11px; font-weight: 700; background: rgba(239, 68, 68, 0.15); padding: 4px 8px; border-radius: 4px; text-transform: uppercase; white-space: nowrap;">Saldo Subtraído</span>
                    <?php else: ?>
                        <span style="color: #94a3b8; font-size: 11px; font-weight: 700; background: rgba(148, 163, 184, 0.15); padding: 4px 8px; border-radius: 4px; text-transform: uppercase; white-space: nowrap;"><?= esc($t['operation_type']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($t['contract_type'])): ?>
                        <span style="color: #818cf8; font-size: 11px; font-weight: 700; background: rgba(129, 140, 248, 0.15); padding: 4px 8px; border-radius: 4px; text-transform: uppercase; white-space: nowrap;">
                            <?= strtoupper($t['contract_type']) ?>
                        </span>
                    <?php endif; ?>
                    </div>
                </td>
                <td class="col-desc tx-desc" style="padding: 20px 25px; color: #cbd5e1; font-size: 13px; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= esc($t['description']) ?>">
                    <?= esc($t['description']) ?>
                </td>
                <!-- Valor Comercial -->
                <td class="col-comercial" style="padding: 20px 25px; color: #94a3b8; font-size: 13px;">
                    <?php if (!empty($t['comercial_brl']) && $t['comercial_brl'] > 0): ?>
                        R$ <?= number_format($t['comercial_brl'], 2, ',', '.') ?>
                    <?php else: ?>
                        <span style="color: #475569; font-style: italic;">N/A</span>
                    <?php endif; ?>
                </td>
                <!-- Valor da Taxa (BRL) -->
                <td class="col-fee" style="padding: 20px 25px; color: #94a3b8; font-size: 13px;">
                    <?php if (!empty($t['fee_brl']) && $t['fee_brl'] > 0): ?>
                        <span style="color: #eab308; font-weight: 600;">R$ <?= number_format($t['fee_brl'], 2, ',', '.') ?></span>
                        <span style="font-size: 10px; color: #64748b; display: block; margin-top: 2px;">(<?= number_format($t['fee_percent'] ?? 0, 2, ',', '.') ?>%)</span>
                    <?php else: ?>
                        <span style="color: #475569; font-style: italic;">N/A</span>
                    <?php endif; ?>
                </td>
                <td style="padding: 20px 25px;">
                    <div style="font-weight: 700; display: flex; align-items: center; gap: 6px;">
                        <?php if ($t['nature'] === 'C'): ?>
                            <span style="color: #10b981;">+ R$ <?= number_format($t['amount'], 2, ',', '.') ?></span>
                        <?php elseif ($t['operation_type'] === 'withdrawal'): ?>
                            <span style="color: #ef4444;">- <?= number_format($t['amount'], 2, '.', ',') ?> USDT</span>
                        <?php else: ?>
                            <span style="color: #ef4444;">- R$ <?= number_format($t['amount'], 2, ',', '.') ?></span>
                        <?php endif; ?>
                    </div>
                </td>
                <td class="col-status" style="padding: 20px 25px;">
                    <span style="border: 1px solid rgba(255,255,255,0.2); color: rgba(255,255,255,0.7); font-size: 10px; font-weight: 700; background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 4px; text-transform: uppercase; display: inline-flex; align-items: center; gap: 4px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Liquidado
                    </span>
                </td>
                <td style="padding: 20px 25px; text-align: right;">
                    <?php if (!empty($t['contract_id'])): ?>
                        <a href="<?= url_to('admin_contracts_show', $t['contract_id']) ?>" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px; border-radius: 6px; background: #ffffff; color: #0f172a; border: 1px solid #ffffff; font-weight: 700; box-shadow: none; transition: all 0.2s; cursor: pointer; text-decoration: none;">
                            Ver Operação
                        </a>
                    <?php else: ?>
                        <span style="color: #475569; font-size: 12px; font-style: italic;">Lançamento Avulso</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div><!-- /.table-scroll -->

    <!-- Pagination Footer -->
    <div id="pagination-controls" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 25px; background: rgba(15, 23, 42, 0.2); border-top: 1px solid rgba(255,255,255,0.05); flex-wrap: wrap; gap: 15px;">
        <div style="font-size: 13px; color: #94a3b8;" id="pagination-info">
            Exibindo <span id="pag-start" style="font-weight: 600; color: white;">0</span> a <span id="pag-end" style="font-weight: 600; color: white;">0</span> de <span id="pag-total" style="font-weight: 600; color: white;">0</span> lançamentos
        </div>
        <div style="display: flex; gap: 6px;" id="pag-buttons">
            <!-- Dynamic Buttons will render here -->
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    const rowsPerPage = 5;

    function getPaginationRange(current, total) {
        const range = [];
        const delta = 2;
        range.push(1);
        if (total <= 1) return range;
        let l;
        for (let i = 2; i < total; i++) {
            if (Math.abs(i - current) <= delta || (current <= 4 && i <= 5) || (current >= total - 3 && i >= total - 4)) {
                range.push(i);
            }
        }
        range.push(total);
        const result = [];
        for (let i = 0; i < range.length; i++) {
            if (l !== undefined) {
                if (range[i] - l === 2) {
                    result.push(l + 1);
                } else if (range[i] - l > 2) {
                    result.push('...');
                }
            }
            result.push(range[i]);
            l = range[i];
        }
        return result;
    }

    function applyFilters(resetPage = false) {
        if (resetPage) {
            currentPage = 1;
        }

        const typeFilter = document.getElementById('filter-type').value;
        const natureFilter = document.getElementById('filter-nature').value;
        const startDate = document.getElementById('filter-start-date').value;
        const endDate = document.getElementById('filter-end-date').value;
        const searchQuery = document.getElementById('search-input').value.toLowerCase().trim();
        const rows = document.querySelectorAll('.tx-row');
        
        let totalCreditsBrl = 0;
        let totalDebitsBrl = 0;
        let totalDebitsUsdt = 0;
        let totalCreditsUsdt = 0;
        
        let filteredRows = [];
        
        rows.forEach(row => {
            const type = row.getAttribute('data-type');
            const nature = row.getAttribute('data-nature');
            const amount = parseFloat(row.getAttribute('data-amount')) || 0;
            const rowDate = row.getAttribute('data-date');

            // Client Name and Description matching
            const userName = row.querySelector('.client-name').innerText.toLowerCase();
            const description = row.querySelector('.tx-desc').innerText.toLowerCase();
            const matchesSearch = userName.includes(searchQuery) || description.includes(searchQuery);

            const matchesType = (typeFilter === 'all' || type === typeFilter);
            const matchesNature = (natureFilter === 'all' || nature === natureFilter);
            const matchesStart = (startDate === '' || rowDate >= startDate);
            const matchesEnd = (endDate === '' || rowDate <= endDate);

            if (matchesType && matchesNature && matchesSearch && matchesStart && matchesEnd) {
                filteredRows.push(row);
                
                if (nature === 'D') {
                    if (type === 'withdrawal') {
                        totalDebitsUsdt += amount;
                    } else {
                        // margin_lock (débito), adjustment_subtract, etc. — tudo em BRL
                        totalDebitsBrl += amount;
                    }
                } else {
                    // Natureza 'C' (Crédito/Entrada) — depósitos, ajustes, etc.
                    totalCreditsBrl += amount;
                }
            } else {
                row.style.display = 'none';
            }
        });
        
        // Credits Card
        let creditsHtml = 'R$ ' + totalCreditsBrl.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (totalCreditsUsdt > 0) {
            creditsHtml += ' <span style="font-size: 13px; color: #34d399; font-weight: 500; margin-left: 6px;">+ ' + totalCreditsUsdt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' USDT</span>';
        }
        document.getElementById('stat-total-credits').innerHTML = creditsHtml;
        document.getElementById('print-total-credits').innerHTML = creditsHtml;
        
        // Debits Card: USDT + BRL
        let debitsHtml = totalDebitsUsdt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' USDT';
        if (totalDebitsBrl > 0) {
            debitsHtml += ' <span style="font-size: 13px; color: #ef4444; font-weight: 500; margin-left: 6px;">+ R$ ' + totalDebitsBrl.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</span>';
        }
        document.getElementById('stat-total-debits').innerHTML = debitsHtml;
        document.getElementById('print-total-debits').innerHTML = debitsHtml;
        
        // Net Flow Card
        const netFlowBrl = totalCreditsBrl - totalDebitsBrl;
        let flowHtml = '';
        if (netFlowBrl >= 0) {
            flowHtml += '<span style="color: #10b981;">R$ ' + netFlowBrl.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</span>';
        } else {
            flowHtml += '<span style="color: #ef4444;">- R$ ' + Math.abs(netFlowBrl).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</span>';
        }
        
        const netFlowUsdt = totalCreditsUsdt - totalDebitsUsdt;
        if (netFlowUsdt >= 0) {
            flowHtml += ' <span style="font-size: 13px; color: #10b981; font-weight: 500; margin-left: 6px;">+ ' + netFlowUsdt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' USDT</span>';
        } else {
            flowHtml += ' <span style="font-size: 13px; color: #ef4444; font-weight: 500; margin-left: 6px;">- ' + Math.abs(netFlowUsdt).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' USDT</span>';
        }
        document.getElementById('stat-net-flow').innerHTML = flowHtml;
        document.getElementById('print-net-flow').innerHTML = flowHtml;

        // Pagination Calculations
        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage) || 1;
        
        if (currentPage > totalPages) {
            currentPage = totalPages;
        }
        if (currentPage < 1) {
            currentPage = 1;
        }
        
        const startIdx = (currentPage - 1) * rowsPerPage;
        const endIdx = Math.min(startIdx + rowsPerPage, totalRows);
        
        // Hide all rows initially, then display only current slice
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                row.style.display = 'none';
            }
        });
        
        for (let i = startIdx; i < endIdx; i++) {
            filteredRows[i].style.display = 'table-row';
        }
        
        // Update pagination info label
        document.getElementById('pag-start').innerText = totalRows > 0 ? (startIdx + 1) : 0;
        document.getElementById('pag-end').innerText = endIdx;
        document.getElementById('pag-total').innerText = totalRows;
        
        // Render pagination controls buttons
        const btnContainer = document.getElementById('pag-buttons');
        btnContainer.innerHTML = '';
        
        if (totalPages > 1) {
            // Previous button
            const prevBtn = document.createElement('button');
            prevBtn.innerText = 'Anterior';
            prevBtn.disabled = currentPage === 1;
            prevBtn.style.cssText = 'background: rgba(15,23,42,0.3); border: 1px solid #334155; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; opacity: ' + (currentPage === 1 ? '0.5' : '1') + '; transition: all 0.2s;';
            prevBtn.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    applyFilters();
                }
            };
            btnContainer.appendChild(prevBtn);
            
            // Page buttons with sliding range and ellipses
            const range = getPaginationRange(currentPage, totalPages);
            range.forEach(p => {
                if (p === '...') {
                    const span = document.createElement('span');
                    span.innerText = '...';
                    span.style.cssText = 'display: inline-block; padding: 6px 12px; color: #64748b; font-size: 12px;';
                    btnContainer.appendChild(span);
                } else {
                    const pageBtn = document.createElement('button');
                    pageBtn.innerText = p;
                    const isActive = p === currentPage;
                    pageBtn.style.cssText = 'background: ' + (isActive ? '#ffffff' : 'rgba(15,23,42,0.3)') + '; border: 1px solid ' + (isActive ? '#ffffff' : '#334155') + '; color: ' + (isActive ? '#0f172a' : 'white') + '; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; font-weight: ' + (isActive ? '700' : '500') + '; transition: all 0.2s;';
                    pageBtn.onclick = () => {
                        currentPage = p;
                        applyFilters();
                    };
                    btnContainer.appendChild(pageBtn);
                }
            });
            
            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.innerText = 'Próximo';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.style.cssText = 'background: rgba(15,23,42,0.3); border: 1px solid #334155; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; opacity: ' + (currentPage === totalPages ? '0.5' : '1') + '; transition: all 0.2s;';
            nextBtn.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    applyFilters();
                }
            };
            btnContainer.appendChild(nextBtn);
        }
    }

    // Run first calculation on load
    document.addEventListener('DOMContentLoaded', () => {
        applyFilters();
    });
</script>
<?= $this->endSection() ?>
