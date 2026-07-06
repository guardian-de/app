<?php
$db = \Config\Database::connect();
$maxContract = $db->table('contracts')->selectMax('id')->get()->getRow();
$initialLastId = $maxContract ? (int) $maxContract->id : 0;

$initialLastDepositId = 0;
if ($db->tableExists('deposits')) {
    $maxDeposit = $db->table('deposits')->selectMax('id')->get()->getRow();
    $initialLastDepositId = $maxDeposit ? (int) $maxDeposit->id : 0;
}

$settingsModel = new \App\Models\SettingsModel();
$adminAlertSound = $settingsModel->getConfig('admin_alert_sound', 'chime_premium');
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Guardian | Admin' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #1d4ed8;
            --bg-dark: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --sidebar-bg: rgba(15, 23, 42, 0.9);
            --border: rgba(59, 130, 246, 0.2);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-dark);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
            letter-spacing: -0.01em;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 30px 20px;
            backdrop-filter: blur(20px);
            position: fixed;
            height: 100vh;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            color: white;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            letter-spacing: -0.02em;
        }

        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            text-decoration: none;
            color: var(--text-muted);
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.15s, color 0.15s;
            font-size: 14px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid #ffffff;
            color: white;
            box-shadow: none;
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 40px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .header h1 {
            font-weight: 700;
            letter-spacing: -0.03em;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 30px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        /* Utils */
        .btn {
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Estilos de Notificações de Compra Premium */
        #toast-notification-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 16px;
            max-width: 380px;
            width: calc(100% - 48px);
            pointer-events: none;
        }

        .premium-toast-card {
            background: #0f172a;
            border: 2px solid #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            pointer-events: auto;
            position: relative;
            transform: translateX(120%);
            animation: toastSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            transition: all 0.3s ease;
        }

        .premium-toast-card.fade-out {
            animation: toastSlideOut 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes toastSlideIn {
            to {
                transform: translateX(0);
            }
        }

        @keyframes toastSlideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(120%);
                opacity: 0;
            }
        }

        .toast-body {
            display: flex;
            padding: 16px 20px;
            align-items: flex-start;
            gap: 14px;
            position: relative;
        }

        .toast-icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.12);
            border: 2px solid #ffffff;
            color: #ffffff;
            flex-shrink: 0;
        }

        .toast-content {
            flex-grow: 1;
            padding-right: 15px;
        }

        .toast-title {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 4px;
            letter-spacing: -0.01em;
        }

        .toast-user {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 6px;
        }

        .toast-details {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .toast-usdt {
            color: #34d399;
        }

        .toast-arrow {
            color: #64748b;
            font-size: 11px;
        }

        .toast-brl {
            color: #60a5fa;
        }

        .toast-badge-delivery {
            display: inline-block;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 6px;
            text-transform: uppercase;
        }

        .toast-close-btn {
            background: none;
            border: none;
            color: #64748b;
            font-size: 20px;
            cursor: pointer;
            position: absolute;
            top: 12px;
            right: 12px;
            line-height: 1;
            transition: color 0.2s;
        }

        .toast-close-btn:hover {
            color: #f3f4f6;
        }

        .toast-actions {
            display: flex;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(0, 0, 0, 0.2);
            padding: 10px 16px;
        }

        .toast-action-btn {
            width: 100%;
            text-align: center;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.2s;
        }

        .toast-action-btn.primary {
            background: #ffffff;
            color: #0f172a;
            border: 2px solid #ffffff;
            box-shadow: none;
            border-radius: 6px;
        }

        .toast-action-btn.primary:hover {
            background: transparent;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .toast-progress-bar {
            height: 3px;
            background: #ffffff;
            width: 100%;
            animation: shrinkProgress 10s linear forwards;
            transform-origin: left;
        }

        @keyframes shrinkProgress {
            from {
                transform: scaleX(1);
            }

            to {
                transform: scaleX(0);
            }
        }

        /* Banner de permissão de notificação */
        .permission-banner {
            position: fixed;
            top: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            z-index: 10001;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(59, 130, 246, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            border-radius: 16px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            max-width: 600px;
            width: calc(100% - 48px);
            animation: bannerSlideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            font-size: 14px;
            color: #f8fafc;
        }

        @keyframes bannerSlideIn {
            to {
                transform: translateX(-50%) translateY(0);
            }
        }

        .permission-btn {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .permission-btn.allow {
            background: #3b82f6;
            color: white;
        }

        .permission-btn.allow:hover {
            background: #2563eb;
        }

        .permission-btn.dismiss {
            background: rgba(255, 255, 255, 0.08);
            color: #94a3b8;
        }

        .permission-btn.dismiss:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>

<body>
    <div class="sidebar">
        <a href="<?= url_to('dashboard') ?>" class="logo">
            <span style="color:var(--primary)">GUARDIAN</span> ADMIN
        </a>
        <div class="nav-links">
            <?php if (session()->get('user_role') === 'admin'): ?>
            <a href="<?= url_to('admin_users') ?>"
                class="nav-link <?= current_url() == url_to('admin_users') ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Usuários
            </a>
            <?php endif; ?>
            <a href="<?= url_to('admin_transactions') ?>"
                class="nav-link <?= current_url() == url_to('admin_transactions') ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="20" x2="12" y2="10"></line>
                    <line x1="18" y1="20" x2="18" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="16"></line>
                </svg>
                Transações
            </a>
            <a href="<?= url_to('admin_delivery') ?>"
                class="nav-link <?= (isset($active_menu) && $active_menu == 'delivery') ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
                Enviar USDT
            </a>
            <a href="<?= url_to('admin_lots') ?>"
                class="nav-link <?= (isset($active_menu) && $active_menu == 'lots') ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
                Lotes
            </a>
            <a href="<?= url_to('admin_contracts') ?>"
                class="nav-link <?= (isset($active_menu) && $active_menu == 'contracts') ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                Operações
            </a>
            <a href="<?= url_to('admin_deposits') ?>" id="deposits-nav-link"
                class="nav-link <?= (isset($active_menu) && $active_menu == 'deposits') ? 'active' : '' ?>"
                style="position: relative;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                Depósitos
                <span id="deposits-nav-dot" style="display:none; position:absolute; top:8px; right:12px; width:8px; height:8px; border-radius:50%; background:#f87171; box-shadow:0 0 0 2px rgba(15,23,42,0.9);"></span>
            </a>
            <a href="<?= url_to('admin_conciliation') ?>"
                class="nav-link <?= (isset($active_menu) && $active_menu == 'conciliation') ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/>
                </svg>
                Conciliação
            </a>
            <?php if (session()->get('user_role') === 'admin'): ?>
            <a href="<?= url_to('admin_suppliers') ?>"
                class="nav-link <?= (isset($active_menu) && $active_menu == 'suppliers') ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Fornecedores
            </a>
            <a href="<?= url_to('admin_settings') ?>"
                class="nav-link <?= current_url() == url_to('admin_settings') ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path
                        d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                    </path>
                </svg>
                Configurações
            </a>
            <?php endif; ?>
            <div style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
                <a href="<?= url_to('logout') ?>" class="nav-link" style="color:#f87171">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Sair
                </a>
            </div>
        </div>
    </div>

    <main class="main-content">
        <div class="container">
            <?= $this->renderSection('content') ?>
        </div>
    </main>

    <?= $this->renderSection('scripts') ?>

    <!-- Push Notification Script for Admin -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Recupera o último ID de contrato verificado — chave separada da antiga (que usava transactions)
            const STORAGE_KEY = 'admin_last_checked_contract_id';
            let lastCheckedId = localStorage.getItem(STORAGE_KEY)
                ? parseInt(localStorage.getItem(STORAGE_KEY))
                : <?= $initialLastId ?>;

            // Se for a primeira vez no navegador, inicia salvando o ID atual de referência
            if (!localStorage.getItem(STORAGE_KEY)) {
                localStorage.setItem(STORAGE_KEY, <?= $initialLastId ?>);
            }

            window.alertSoundPreference = '<?= $adminAlertSound ?>';

            // Solicita permissão para notificações nativas do navegador se aplicável
            if (typeof Notification !== 'undefined' && Notification.permission !== "granted" && Notification.permission !== "denied") {
                showNotificationPermissionRequest();
            }

            // Executa a primeira checagem imediata 1 segundo após carregar (captura compras offline imediatamente!)
            setTimeout(checkNewPurchases, 1000);

            // Inicia o loop de monitoramento em alta precisão a cada 8 segundos
            setInterval(checkNewPurchases, 8000);

            function checkNewPurchases() {
                fetch(`<?= site_url('admin/transactions/check-new') ?>?last_id=${lastCheckedId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            // Ordena cronologicamente por ID do menor para o maior para disparar alertas na ordem exata dos fatos
                            data.sort((a, b) => a.id - b.id);

                            data.forEach(tx => {
                                if (tx.id > lastCheckedId) {
                                    lastCheckedId = tx.id;
                                    localStorage.setItem(STORAGE_KEY, tx.id);

                                    // Dispara a notificação
                                    triggerNotification(tx);
                                }
                            });
                        }
                    })
                    .catch(err => console.error("Erro ao verificar novas compras:", err));
            }

            // --- Monitoramento de novos depósitos pendentes ---
            const DEPOSIT_STORAGE_KEY = 'admin_last_checked_deposit_id';
            const DEPOSIT_SEEN_KEY = 'admin_deposits_seen_id';
            let lastCheckedDepositId = localStorage.getItem(DEPOSIT_STORAGE_KEY)
                ? parseInt(localStorage.getItem(DEPOSIT_STORAGE_KEY))
                : <?= $initialLastDepositId ?>;
            if (!localStorage.getItem(DEPOSIT_STORAGE_KEY)) {
                localStorage.setItem(DEPOSIT_STORAGE_KEY, <?= $initialLastDepositId ?>);
            }
            let depositsSeenId = localStorage.getItem(DEPOSIT_SEEN_KEY)
                ? parseInt(localStorage.getItem(DEPOSIT_SEEN_KEY))
                : <?= $initialLastDepositId ?>;
            if (!localStorage.getItem(DEPOSIT_SEEN_KEY)) {
                localStorage.setItem(DEPOSIT_SEEN_KEY, <?= $initialLastDepositId ?>);
            }

            setTimeout(checkNewDeposits, 1000);
            setInterval(checkNewDeposits, 8000);

            const depositsNavLink = document.getElementById('deposits-nav-link');
            if (depositsNavLink) {
                depositsNavLink.addEventListener('click', function () {
                    const dot = document.getElementById('deposits-nav-dot');
                    if (dot) dot.style.display = 'none';
                    depositsSeenId = Math.max(depositsSeenId, lastCheckedDepositId);
                    localStorage.setItem(DEPOSIT_SEEN_KEY, depositsSeenId);
                });
            }

            function checkNewDeposits() {
                fetch(`<?= site_url('admin/deposits/check-new') ?>`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data) return;
                        data.sort((a, b) => a.id - b.id);

                        const dot = document.getElementById('deposits-nav-dot');
                        const hasUnseen = data.some(d => d.id > depositsSeenId);
                        if (dot) dot.style.display = hasUnseen ? 'block' : 'none';

                        data.forEach(dep => {
                            if (dep.id > lastCheckedDepositId) {
                                lastCheckedDepositId = dep.id;
                                localStorage.setItem(DEPOSIT_STORAGE_KEY, dep.id);
                                triggerNotification({
                                    id: dep.id,
                                    type: 'deposit',
                                    user_name: dep.user_name,
                                    amount_brl: dep.amount_brl,
                                    contract_id: null
                                });
                            }
                        });
                    })
                    .catch(err => console.error("Erro ao verificar novos depósitos:", err));
            }

            function triggerNotification(tx) {
                // 1. Som de notificação premium usando Web Audio API
                playNotificationSound();

                // Define a URL de destino (Depósito, Contrato se houver, senão Transação)
                const targetUrl = tx.type === 'deposit'
                    ? `<?= site_url('admin/deposits/show') ?>/${tx.id}`
                    : (tx.contract_id
                        ? `<?= site_url('admin/contracts/show') ?>/${tx.contract_id}`
                        : `<?= site_url('admin/transactions/show') ?>/${tx.id}`);

                // Parâmetros dinâmicos baseados no tipo do alerta
                let typeTitle = "Nova Transação!";
                let nativeTitle = "Nova Transação Recebida! 💼";
                let nativeBody = `${tx.user_name} realizou uma nova transação.`;
                let badgeText = tx.delivery_type || "Transação";
                let iconSVG = `<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>`;

                const amountUsdtFormatted = parseFloat(tx.amount_usdt).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                const amountBrlFormatted = parseFloat(tx.amount_brl).toLocaleString('pt-BR', { minimumFractionDigits: 2 });

                if (tx.type === 'buy') {
                    const isCredit = !!tx.contract_id;
                    const dType = (tx.delivery_type || 'd+0').toUpperCase();
                    if (isCredit) {
                        typeTitle = "Nova Compra no Crédito (" + dType + ")!";
                        nativeTitle = "Compra no Crédito! 💳 (" + dType + ")";
                        nativeBody = `${tx.user_name} comprou ${amountUsdtFormatted} USDT (R$ ${amountBrlFormatted}) no Limite de Crédito (${dType}).`;
                        badgeText = "Crédito " + dType;
                    } else {
                        typeTitle = "Nova Compra à Vista!";
                        nativeTitle = "Compra à Vista! 🛒";
                        nativeBody = `${tx.user_name} comprou ${amountUsdtFormatted} USDT (R$ ${amountBrlFormatted}) com Pagamento à Vista.`;
                        badgeText = "À Vista";
                    }
                    iconSVG = `<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>`;
                } else if (tx.type === 'sell') {
                    typeTitle = "Nova Venda / Retirada!";
                    nativeTitle = "Nova Retirada de Fundos! 💸";
                    nativeBody = `${tx.user_name} solicitou a venda de ${amountUsdtFormatted} USDT (R$ ${amountBrlFormatted})`;
                    badgeText = "Retirada/Venda";
                    iconSVG = `<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>`;
                } else if (tx.type === 'payment') {
                    typeTitle = "Novo Comprovante de Pagamento!";
                    nativeTitle = "Pagamento Efetuado! 📄";
                    nativeBody = `${tx.user_name} enviou o comprovante de R$ ${amountBrlFormatted} para pagar limite/operação.`;
                    badgeText = "Comprovante";
                    iconSVG = `<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>`;
                } else if (tx.type === 'delivery') {
                    typeTitle = "Nova Solicitação de Entrega!";
                    nativeTitle = "Entrega Solicitada! 🚚";
                    nativeBody = `${tx.user_name} solicitou a entrega de ${amountUsdtFormatted} USDT no limite de crédito.`;
                    badgeText = "Entrega";
                    iconSVG = `<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>`;
                } else if (tx.type === 'deposit') {
                    typeTitle = "Novo Depósito Pendente!";
                    nativeTitle = "Novo Depósito! 💰";
                    nativeBody = `${tx.user_name} enviou um depósito de R$ ${amountBrlFormatted} aguardando validação.`;
                    badgeText = "Depósito";
                    iconSVG = `<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>`;
                }

                // 2. Notificação nativa do navegador se permitida
                if (typeof Notification !== 'undefined' && Notification.permission === "granted") {
                    try {
                        const notification = new Notification(nativeTitle, {
                            body: nativeBody,
                            icon: '<?= base_url('favicon.ico') ?>'
                        });
                        notification.onclick = function () {
                            window.focus();
                            window.location.href = targetUrl;
                        };
                    } catch (e) {
                        console.error("Erro ao disparar notificação nativa:", e);
                    }
                }

                // 3. Notificação In-App (Toast)
                showInAppToast(tx, targetUrl, typeTitle, badgeText, iconSVG);
            }

            function showInAppToast(tx, targetUrl, typeTitle, badgeText, iconSVG) {
                const container = document.getElementById('toast-notification-container') || createToastContainer();

                const toast = document.createElement('div');
                toast.className = 'premium-toast-card';
                toast.innerHTML = `
                    <div class="toast-body">
                        <div class="toast-icon-wrapper">
                            ${iconSVG}
                        </div>
                        <div class="toast-content">
                            <h4 class="toast-title">${typeTitle}</h4>
                            <p class="toast-user"><strong>Cliente:</strong> ${tx.user_name}</p>
                            <p class="toast-details">
                                ${tx.type === 'deposit'
                                    ? `<span class="toast-brl">R$ ${parseFloat(tx.amount_brl).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</span>`
                                    : `<span class="toast-usdt">${parseFloat(tx.amount_usdt).toLocaleString('pt-BR', { minimumFractionDigits: 2 })} USDT</span>
                                       <span class="toast-arrow">➔</span>
                                       <span class="toast-brl">R$ ${parseFloat(tx.amount_brl).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</span>`}
                            </p>
                            <div class="toast-badge-delivery">${badgeText}</div>
                        </div>
                        <button class="toast-close-btn" onclick="this.closest('.premium-toast-card').remove(); if(typeof window.stopActiveAlertSound === 'function') window.stopActiveAlertSound();">&times;</button>
                    </div>
                    <div class="toast-actions" style="display: flex; gap: 8px;">
                        <a href="${targetUrl}" class="toast-action-btn primary" onclick="if(typeof window.stopActiveAlertSound === 'function') window.stopActiveAlertSound();">Ver e Aprovar</a>
                        <button class="toast-action-btn secondary" style="background: transparent; color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 11px; padding: 6px 12px; border-radius: 4px; font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='transparent'" onclick="if(typeof window.stopActiveAlertSound === 'function') window.stopActiveAlertSound(); this.style.display='none';">Silenciar</button>
                    </div>
                    <div class="toast-progress-bar"></div>
                `;

                container.appendChild(toast);

                // Auto-remover após 10 segundos
                let timeoutId = setTimeout(() => {
                    toast.classList.add('fade-out');
                    setTimeout(() => toast.remove(), 400);
                }, 10000);

                // Pausa animação no hover
                toast.addEventListener('mouseenter', () => {
                    clearTimeout(timeoutId);
                    toast.querySelector('.toast-progress-bar').style.animationPlayState = 'paused';
                });

                toast.addEventListener('mouseleave', () => {
                    // Calcula tempo restante baseado no width proporcional
                    const progressBar = toast.querySelector('.toast-progress-bar');
                    const computedStyle = window.getComputedStyle(progressBar);
                    const widthVal = parseFloat(computedStyle.width);
                    const parentWidth = parseFloat(window.getComputedStyle(toast).width);
                    const remainingRatio = parentWidth > 0 ? (widthVal / parentWidth) : 1;
                    const remainingTime = 10000 * remainingRatio;

                    timeoutId = setTimeout(() => {
                        toast.classList.add('fade-out');
                        setTimeout(() => toast.remove(), 400);
                    }, remainingTime);
                    progressBar.style.animationPlayState = 'running';
                });
            }

            function createToastContainer() {
                const container = document.createElement('div');
                container.id = 'toast-notification-container';
                document.body.appendChild(container);
                return container;
            }

            function showNotificationPermissionRequest() {
                // Banner de solicitação de permissão de notificação sutil na tela
                const permissionBanner = document.createElement('div');
                permissionBanner.className = 'permission-banner';
                permissionBanner.innerHTML = `
                    <div style="display:flex; align-items:center; gap: 12px; text-align: left;">
                        <span>🔔 Deseja receber notificações sonoras e em segundo plano de novas compras?</span>
                    </div>
                    <div style="display:flex; gap: 8px; flex-shrink: 0;">
                        <button class="permission-btn allow" onclick="requestBrowserNotifications(this)">Permitir</button>
                        <button class="permission-btn dismiss" onclick="this.closest('.permission-banner').remove()">Agora Não</button>
                    </div>
                `;
                document.body.appendChild(permissionBanner);
            }

            window.requestBrowserNotifications = function (btn) {
                if (typeof Notification !== 'undefined') {
                    Notification.requestPermission().then(permission => {
                        if (permission === 'granted') {
                            playNotificationSound();
                        }
                        btn.closest('.permission-banner').remove();
                    });
                } else {
                    btn.closest('.permission-banner').remove();
                }
            };

            window.activeAlertIntervalId = null;
            window.activeAlertAudioContexts = [];

            window.stopActiveAlertSound = function() {
                if (window.activeAlertIntervalId) {
                    clearInterval(window.activeAlertIntervalId);
                    window.activeAlertIntervalId = null;
                }
                if (window.activeAlertAudioContexts.length > 0) {
                    window.activeAlertAudioContexts.forEach(ctx => {
                        try {
                            ctx.close();
                        } catch (e) {}
                    });
                    window.activeAlertAudioContexts = [];
                }
            };

            window.playNotificationSound = function(soundName) {
                // Stop any previous active alarm loop
                window.stopActiveAlertSound();

                try {
                    const soundType = soundName || window.alertSoundPreference || 'chime_premium';

                    const isContinuous = [
                        'siren_industrial',
                        'police_sweep',
                        'emergency_bell',
                        'factory_horn',
                        'nuclear_danger'
                    ].includes(soundType);

                    function playSingleInstance() {
                        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        window.activeAlertAudioContexts.push(audioCtx);
                        const now = audioCtx.currentTime;

                        switch (soundType) {
                            case 'chime_premium':
                                playChimePremium(audioCtx, now);
                                break;
                            case 'retro_arcade':
                                playRetroArcade(audioCtx, now);
                                break;
                            case 'digital_alert':
                                playDigitalAlert(audioCtx, now);
                                break;
                            case 'soft_bell':
                                playSoftBell(audioCtx, now);
                                break;
                            case 'triumph_chord':
                                playTriumphChord(audioCtx, now);
                                break;
                            // 5 new continuous looping alarms
                            case 'siren_industrial':
                                playSirenIndustrial(audioCtx, now);
                                break;
                            case 'police_sweep':
                                playPoliceSweep(audioCtx, now);
                                break;
                            case 'emergency_bell':
                                playEmergencyBell(audioCtx, now);
                                break;
                            case 'factory_horn':
                                playFactoryHorn(audioCtx, now);
                                break;
                            case 'nuclear_danger':
                                playNuclearDanger(audioCtx, now);
                                break;
                            default:
                                playChimePremium(audioCtx, now);
                        }
                    }

                    playSingleInstance();

                    if (isContinuous) {
                        // Repeat loop every 1.8 seconds
                        window.activeAlertIntervalId = setInterval(() => {
                            window.activeAlertAudioContexts = window.activeAlertAudioContexts.filter(ctx => ctx.state !== 'closed');
                            playSingleInstance();
                        }, 1800);
                    }
                } catch (e) {
                    console.warn("Audio Context bloqueado ou indisponível:", e);
                }
            };

            function playChimePremium(audioCtx, now) {
                // Brighter, much louder chime
                const osc1 = audioCtx.createOscillator();
                const gain1 = audioCtx.createGain();
                osc1.type = 'triangle';
                osc1.frequency.setValueAtTime(587.33, now); // D5
                osc1.frequency.exponentialRampToValueAtTime(1174.66, now + 0.15); // D6
                gain1.gain.setValueAtTime(0.32, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
                osc1.connect(gain1);
                gain1.connect(audioCtx.destination);
                osc1.start(now);
                osc1.stop(now + 0.55);

                const osc2 = audioCtx.createOscillator();
                const gain2 = audioCtx.createGain();
                osc2.type = 'triangle';
                osc2.frequency.setValueAtTime(698.46, now + 0.08); // F5
                osc2.frequency.exponentialRampToValueAtTime(1396.91, now + 0.23); // F6
                gain2.gain.setValueAtTime(0.28, now + 0.08);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
                osc2.connect(gain2);
                gain2.connect(audioCtx.destination);
                osc2.start(now + 0.08);
                osc2.stop(now + 0.65);
            }

            function playRetroArcade(audioCtx, now) {
                // Laser-like punchy powerup alarm
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(180, now);
                osc.frequency.exponentialRampToValueAtTime(1900, now + 0.35);
                gain.gain.setValueAtTime(0.28, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(now);
                osc.stop(now + 0.45);

                const osc2 = audioCtx.createOscillator();
                const gain2 = audioCtx.createGain();
                osc2.type = 'square';
                osc2.frequency.setValueAtTime(185, now);
                osc2.frequency.exponentialRampToValueAtTime(1950, now + 0.35);
                gain2.gain.setValueAtTime(0.18, now);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
                osc2.connect(gain2);
                gain2.connect(audioCtx.destination);
                osc2.start(now);
                osc2.stop(now + 0.45);
            }

            function playDigitalAlert(audioCtx, now) {
                // High-intensity triple siren alarm beep (very noisy)
                [0, 0.12, 0.24].forEach((delay) => {
                    const t = now + delay;
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'square';
                    osc.frequency.setValueAtTime(987.77, t); // B5 (very sharp!)
                    gain.gain.setValueAtTime(0.38, t);
                    gain.gain.exponentialRampToValueAtTime(0.001, t + 0.08);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(t);
                    osc.stop(t + 0.09);
                });
            }

            function playSoftBell(audioCtx, now) {
                // Classic mechanical telephone tremolo ringer (highly notice-grabbing)
                const duration = 0.8;
                const osc1 = audioCtx.createOscillator();
                const osc2 = audioCtx.createOscillator();
                const gain1 = audioCtx.createGain();
                const gain2 = audioCtx.createGain();

                osc1.type = 'square';
                osc1.frequency.setValueAtTime(880, now); // A5
                gain1.gain.setValueAtTime(0.24, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + duration);

                osc2.type = 'square';
                osc2.frequency.setValueAtTime(885, now); // detuned by 5Hz
                gain2.gain.setValueAtTime(0.24, now);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + duration);

                osc1.connect(gain1);
                gain1.connect(audioCtx.destination);
                osc2.connect(gain2);
                gain2.connect(audioCtx.destination);

                osc1.start(now);
                osc2.start(now);
                osc1.stop(now + duration);
                osc2.stop(now + duration);
            }

            function playTriumphChord(audioCtx, now) {
                // Bright, epic fanfare chord sliding upwards
                const notes = [261.63, 329.63, 392.00, 523.25]; // C4, E4, G4, C5
                notes.forEach((freq) => {
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(freq, now);
                    osc.frequency.exponentialRampToValueAtTime(freq * 2, now + 0.5);
                    gain.gain.setValueAtTime(0.15, now);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(now);
                    osc.stop(now + 0.65);
                });
            }

            // Continuous Warning Synthesizers (Industrial-strength sweeps)
            function playSirenIndustrial(audioCtx, now) {
                const osc1 = audioCtx.createOscillator();
                const osc2 = audioCtx.createOscillator();
                const gain = audioCtx.createGain();

                osc1.type = 'triangle';
                osc2.type = 'square';

                osc1.frequency.setValueAtTime(400, now);
                osc1.frequency.linearRampToValueAtTime(900, now + 0.6);
                osc1.frequency.linearRampToValueAtTime(400, now + 1.2);

                osc2.frequency.setValueAtTime(402, now);
                osc2.frequency.linearRampToValueAtTime(902, now + 0.6);
                osc2.frequency.linearRampToValueAtTime(402, now + 1.2);

                gain.gain.setValueAtTime(0.35, now);
                gain.gain.linearRampToValueAtTime(0.35, now + 1.0);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 1.5);

                osc1.connect(gain);
                osc2.connect(gain);
                gain.connect(audioCtx.destination);

                osc1.start(now);
                osc2.start(now);
                osc1.stop(now + 1.6);
                osc2.stop(now + 1.6);
            }

            function playPoliceSweep(audioCtx, now) {
                const duration = 1.5;
                const gain = audioCtx.createGain();
                gain.gain.setValueAtTime(0.35, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + duration);

                for (let i = 0; i < 6; i++) {
                    const t = now + (i * 0.25);
                    const osc = audioCtx.createOscillator();
                    osc.type = 'square';
                    osc.frequency.setValueAtTime(800, t);
                    osc.frequency.linearRampToValueAtTime(1600, t + 0.12);
                    osc.frequency.linearRampToValueAtTime(800, t + 0.24);

                    osc.connect(gain);
                    osc.start(t);
                    osc.stop(t + 0.25);
                }

                gain.connect(audioCtx.destination);
            }

            function playEmergencyBell(audioCtx, now) {
                const duration = 1.4;
                const osc = audioCtx.createOscillator();
                const tremolo = audioCtx.createOscillator();
                const oscGain = audioCtx.createGain();
                const tremoloGain = audioCtx.createGain();

                osc.type = 'square';
                osc.frequency.setValueAtTime(1200, now);

                oscGain.gain.setValueAtTime(0.35, now);
                oscGain.gain.exponentialRampToValueAtTime(0.001, now + duration);

                tremolo.frequency.setValueAtTime(25, now);
                tremoloGain.gain.setValueAtTime(0.3, now);

                tremolo.connect(tremoloGain);
                tremoloGain.connect(oscGain.gain);

                osc.connect(oscGain);
                oscGain.connect(audioCtx.destination);

                osc.start(now);
                tremolo.start(now);
                osc.stop(now + duration + 0.1);
                tremolo.stop(now + duration + 0.1);
            }

            function playFactoryHorn(audioCtx, now) {
                [0, 0.6].forEach(delay => {
                    const t = now + delay;
                    const osc1 = audioCtx.createOscillator();
                    const osc2 = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();

                    osc1.type = 'sawtooth';
                    osc1.frequency.setValueAtTime(140, t);
                    osc2.type = 'sawtooth';
                    osc2.frequency.setValueAtTime(142, t);

                    gain.gain.setValueAtTime(0.4, t);
                    gain.gain.exponentialRampToValueAtTime(0.001, t + 0.45);

                    osc1.connect(gain);
                    osc2.connect(gain);
                    gain.connect(audioCtx.destination);

                    osc1.start(t);
                    osc2.start(t);
                    osc1.stop(t + 0.5);
                    osc2.stop(t + 0.5);
                });
            }

            function playNuclearDanger(audioCtx, now) {
                const osc1 = audioCtx.createOscillator();
                const osc2 = audioCtx.createOscillator();
                const gain = audioCtx.createGain();

                osc1.type = 'sawtooth';
                osc1.frequency.setValueAtTime(280, now);
                osc1.frequency.exponentialRampToValueAtTime(80, now + 1.2);

                osc2.type = 'square';
                osc2.frequency.setValueAtTime(282, now);
                osc2.frequency.exponentialRampToValueAtTime(82, now + 1.2);

                gain.gain.setValueAtTime(0.4, now);
                gain.gain.linearRampToValueAtTime(0.4, now + 0.8);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 1.5);

                osc1.connect(gain);
                osc2.connect(gain);
                gain.connect(audioCtx.destination);

                osc1.start(now);
                osc2.start(now);
                osc1.stop(now + 1.55);
                osc2.stop(now + 1.55);
            }
        });
    </script>
</body>

</html>