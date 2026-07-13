<?php
/** @var array $user @var array $business_hours @var string $quotation_flow @var string $operator_whatsapp */
$userName = session()->get('user_name');
$firstName = explode(' ', $userName)[0];
$isChinese = session()->get('user_lang') === 'zh-CN';
?>
<!DOCTYPE html>
<html lang="<?= session()->get('user_lang') ?? 'pt-BR' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title>Guardian Mobile</title>
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1"></script>
    <script>
        // Only one modal (any element whose id ends in "-modal") may be visible at a time.
        window.showModal = function (id) {
            document.querySelectorAll('[id$="-modal"]').forEach(m => {
                if (m.id !== id) m.style.display = 'none';
            });
            const modal = document.getElementById(id);
            if (modal) modal.style.display = 'flex';
        };

        window.openLanguageModal = function (e) {
            if (e) {
                if (typeof e.preventDefault === 'function') e.preventDefault();
                if (typeof e.stopPropagation === 'function') e.stopPropagation();
            }
            showModal('language-modal');
        };
        window.closeLanguageModal = function () {
            const modal = document.getElementById('language-modal');
            if (modal) modal.style.display = 'none';
        };

        window.openContractsModal = async function () {
            showModal('contracts-modal');
            if (typeof setContractsFilter === 'function') setContractsFilter('todos');
            if (typeof fetchContracts === 'function') {
                await fetchContracts();
            }
        };

        window.closeContractsModal = function () {
            const modal = document.getElementById('contracts-modal');
            if (modal) modal.style.display = 'none';
        };
    </script>
    <style>
        body,
        html {
            height: 100%;
            overflow: hidden;
            background: #020617;
            /* Deep Blue Night */
            color: #f8fafc;
        }

        body,
        html,
        input,
        select,
        textarea,
        button {
            font-family: 'Outfit', sans-serif !important;
        }

        .mobile-wrapper {
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            height: 100dvh;
            width: 100vw;
            background: radial-gradient(circle at top right, #1e3a8a11, transparent);
            overflow: hidden;
        }

        .desktop-sidebar-menu {
            display: none !important;
        }

        .mobile-only-views {
            display: block;
        }

        .desktop-chat-header {
            display: none !important;
        }

        .dashboard-panel-desktop {
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        .chat-panel-desktop {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            position: relative;
            overflow: hidden;
        }

        /* Responsive Desktop Refinements (screens >= 1024px) */
        @media (min-width: 1024px) {
            .desktop-sidebar-menu {
                display: flex !important;
                flex-direction: column;
                padding: 20px;
                height: 100%;
                background: rgba(15, 23, 42, 0.95);
                overflow-y: auto;
                box-sizing: border-box;
            }

            .mobile-only-views {
                display: none !important;
            }

            .desktop-chat-header {
                display: flex !important;
                justify-content: space-between;
                align-items: center;
                padding: 15px 30px;
                background: rgba(15, 23, 42, 0.8);
                border-bottom: 1px solid rgba(59, 130, 246, 0.15);
                flex-shrink: 0;
            }

            .mobile-wrapper {
                flex-direction: row;
            }

            .dashboard-panel-desktop {
                display: flex;
                flex-direction: column;
                width: 420px;
                border-right: 1px solid rgba(59, 130, 246, 0.2);
                height: 100%;
                background: rgba(15, 23, 42, 0.95);
                backdrop-filter: blur(15px);
                flex-shrink: 0;
                overflow-y: auto;
            }

            .chat-panel-desktop {
                display: flex;
                flex-direction: column;
                flex: 1;
                height: 100%;
                background: #090f21;
                position: relative;
                min-width: 0;
            }

            .chat-container-mobile {
                padding-bottom: 24px !important;
            }

            .mobile-input-area {
                position: relative !important;
                left: auto !important;
                right: auto !important;
                bottom: auto !important;
                background: #0f172a !important;
                padding: 20px 30px !important;
                border-top: 1px solid rgba(59, 130, 246, 0.15) !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .chart-section {
                height: 240px !important;
                opacity: 1 !important;
                margin: 20px auto !important;
                display: block !important;
            }

            .toggle-chart-btn {
                display: none !important;
            }
        }

        .mobile-header {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
            z-index: 5000;
        }

        .profile-badge {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .live-rate-mobile {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            padding: 6px 12px;
            border-radius: 99px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rate-dot {
            width: 8px;
            height: 8px;
            background: #3b82f6;
            border-radius: 50%;
            box-shadow: 0 0 10px #3b82f6;
            animation: pulse 2s infinite;
        }

        .typing-dot {
            width: 6px;
            height: 6px;
            background: #94a3b8;
            border-radius: 50%;
            display: inline-block;
            margin-right: 4px;
            animation: typingBounce 1s infinite;
        }

        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typingBounce {

            0%,
            60%,
            100% {
                transform: translateY(0);
            }

            30% {
                transform: translateY(-5px);
            }
        }

        .chat-container-mobile {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding-bottom: 100px;
            scroll-behavior: smooth;
        }

        .message {
            max-width: 80%;
            padding: 14px 18px;
            border-radius: 20px;
            font-size: 15px;
            line-height: 1.4;
            position: relative;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .message.bot {
            align-self: flex-start;
            background: #1e293b;
            color: #f1f5f9;
            border-bottom-left-radius: 4px;
        }

        .message.user {
            align-self: flex-end;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .lang-option {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid #334155;
            border-radius: 12px;
            margin-bottom: 12px;
            color: white;
            font-weight: 500;
            transition: all 0.2s;
        }

        .lang-option:active {
            background: rgba(59, 130, 246, 0.2);
            border-color: #3b82f6;
        }

        .lang-flag {
            font-size: 24px;
        }

        .chart-section {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(15px);
            height: 160px;
            width: 90%;
            margin: 15px auto;
            border-radius: 20px;
            border: 1px solid rgba(59, 130, 246, 0.2);
            padding: 15px;
            position: relative;
            transition: all 0.3s ease;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        }

        .chart-section.collapsed {
            height: 0;
            margin: 0 auto;
            padding: 0;
            border: none;
            opacity: 0;
        }

        .chart-info {
            position: absolute;
            top: 15px;
            left: 20px;
            z-index: 2;
            pointer-events: none;
        }

        .chart-info span {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .toggle-chart-btn {
            position: absolute;
            bottom: 5px;
            right: 15px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            padding: 2px 8px;
            border-radius: 6px;
            color: #3b82f6;
            font-size: 10px;
            font-weight: 600;
            z-index: 3;
        }

        .delivery-option {
            flex: 1;
            padding: 10px;
            text-align: center;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid #334155;
            border-radius: 8px;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .delivery-option.active {
            background: #4f46e5;
            border-color: #6366f1;
            color: white;
        }

        .delivery-option.disabled {
            opacity: 0.4;
            cursor: not-allowed;
            border-style: dashed;
        }

        .mobile-input-area {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(20px);
            padding: 16px 20px 24px;
            border-top: 1px solid rgba(59, 130, 246, 0.2);
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .mobile-input {
            flex: 1;
            background: #0f172a;
            border: 1px solid #334155;
            padding: 14px 20px;
            border-radius: 30px;
            color: white;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s;
        }

        .mobile-input:focus {
            border-color: #3b82f6;
        }

        .send-btn-mobile {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border: none;
            border-radius: 50%;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 22px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }

        /* Reuse modals from chat.php */
        @keyframes pulse {
            0% {
                transform: scale(0.95);
                opacity: 0.7;
            }

            50% {
                transform: scale(1.1);
                opacity: 1;
            }

            100% {
                transform: scale(0.95);
                opacity: 0.7;
            }
        }
    </style>
</head>

<body>
    <!-- Language Modal -->
    <div id="language-modal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 9999; justify-content: center; align-items: center; padding: 15px;">
        <div
            style="background: rgba(30, 41, 59, 0.98); width: 100%; max-width: 320px; padding: 30px; border-radius: 24px; position: relative; border: 1px solid rgba(59, 130, 246, 0.3); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.8);">
            <button onclick="closeLanguageModal()"
                style="position: absolute; right: 20px; top: 20px; background: rgba(255,255,255,0.05); border: none; color: #94a3b8; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; cursor: pointer;">&times;</button>
            <h2 style="color: white; font-size: 18px; font-weight: 700; margin-bottom: 25px; text-align: center;">Idioma / 语言</h2>
            <div class="lang-option" onclick="changeLanguage('pt-BR')"
                style="display: flex; align-items: center; gap: 15px; padding: 18px; background: rgba(15, 23, 42, 0.6); border: 1px solid #334155; border-radius: 16px; margin-bottom: 12px; color: white; cursor: pointer;">
                <span style="font-size: 24px;">🇧🇷</span>
                <span style="font-weight: 600;">Português</span>
            </div>
            <div class="lang-option" onclick="changeLanguage('zh-CN')"
                style="display: flex; align-items: center; gap: 15px; padding: 18px; background: rgba(15, 23, 42, 0.6); border: 1px solid #334155; border-radius: 16px; color: white; cursor: pointer;">
                <span style="font-size: 24px;">🇨🇳</span>
                <span style="font-weight: 600;">中文 (Chinês)</span>
            </div>
        </div>
    </div>

    <div class="mobile-wrapper">
        <div class="dashboard-panel-desktop">
            <!-- Menu Lateral Desktop (exibido apenas em telas >= 1024px) -->
            <div class="desktop-sidebar-menu">
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 25px; padding: 10px 0;">
                    <?php
                    $settingsModel = new \App\Models\SettingsModel();
                    $logoPath = $settingsModel->getConfig('logo_path');
                    ?>
                    <?php if ($logoPath): ?>
                        <img src="<?= base_url($logoPath) ?>" alt="Logo" style="max-height: 45px; max-width: 100%; object-fit: contain;">
                    <?php else: ?>
                        <span style="font-size: 22px; font-weight: 800; color: #3b82f6; letter-spacing: 1px;">GUARDIAN ADMIN</span>
                    <?php endif; ?>
                </div>

                <!-- Balanço/Saldo do Cliente -->
                <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 12px; margin-bottom: 20px;">
                    <p style="font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Meu Saldo</p>
                    <div id="desktop-balance-badge" style="font-size: 20px; font-weight: 700; color: #4ade80;">R$ 0,00</div>
                </div>

                <!-- Container de Lotes Promocionais Desktop -->
                <div id="promotional-lots-desktop" style="display: none; margin-bottom: 20px;"></div>

                <!-- Botões de Ação Rápida -->
                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 25px;">
                    <button onclick="openBuyModal(currentExchangeRate)"
                        style="width: 100%; padding: 12px; border-radius: 8px; background: linear-gradient(135deg, #3b82f6, #2563eb); border: none; color: white; font-weight: 700; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                        </svg>
                        <?= $isChinese ? '购买 USDT' : 'Comprar USDT' ?>
                    </button>
                    <button onclick="openDepositModal()"
                        style="width: 100%; padding: 12px; border-radius: 8px; background: linear-gradient(135deg, #10b981, #059669); border: none; color: white; font-weight: 700; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                        <?= $isChinese ? '存款' : 'Depositar' ?>
                    </button>
                </div>

                <!-- Lista de Itens do Menu -->
                <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 25px;">
                    <button onclick="openContractsModal()" style="display: flex; align-items: center; gap: 12px; width: 100%; background: none; border: 1px solid rgba(255,255,255,0.05); color: #cbd5e1; padding: 12px 16px; border-radius: 8px; font-size: 13px; cursor: pointer; text-align: left; transition: all 0.2s;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                        </svg>
                        <span style="flex: 1;"><?= $isChinese ? '我的交易' : 'Minhas Operações' ?></span>
                        <span id="contracts-badge-desktop" style="display: none; background: #ef4444; color: white; font-size: 9px; font-weight: 800; min-width: 16px; height: 16px; border-radius: 8px; align-items: center; justify-content: center; padding: 0 3px; line-height: 1;"></span>
                    </button>

                    <button onclick="openStatementModal()" style="display: flex; align-items: center; gap: 12px; width: 100%; background: none; border: 1px solid rgba(255,255,255,0.05); color: #cbd5e1; padding: 12px 16px; border-radius: 8px; font-size: 13px; cursor: pointer; text-align: left; transition: all 0.2s;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                        </svg>
                        <span><?= $isChinese ? '对账单' : 'Extrato' ?></span>
                    </button>

                    <button onclick="openNotificationsModal()" style="display: flex; align-items: center; gap: 12px; width: 100%; background: none; border: 1px solid rgba(255,255,255,0.05); color: #cbd5e1; padding: 12px 16px; border-radius: 8px; font-size: 13px; cursor: pointer; text-align: left; transition: all 0.2s;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span style="flex: 1;"><?= $isChinese ? '通知' : 'Notificações' ?></span>
                        <span id="notifications-badge-desktop" style="display: none; background: #ef4444; color: white; font-size: 9px; font-weight: 800; min-width: 16px; height: 16px; border-radius: 8px; align-items: center; justify-content: center; padding: 0 3px; line-height: 1;"></span>
                    </button>

                    <button onclick="openChangePasswordModal(event)" style="display: flex; align-items: center; gap: 12px; width: 100%; background: none; border: 1px solid rgba(255,255,255,0.05); color: #cbd5e1; padding: 12px 16px; border-radius: 8px; font-size: 13px; cursor: pointer; text-align: left; transition: all 0.2s;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        <span><?= $isChinese ? '修改密码' : 'Alterar Senha' ?></span>
                    </button>
                </div>
                
                <!-- Carteira USDT (TRC-20) -->
                <div style="margin-bottom: 20px;">
                    <p style="font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Endereço de Carteira</p>
                    <div style="background: rgba(15, 23, 42, 0.5); padding: 10px; border-radius: 8px; border: 1px dashed #334155; font-family: monospace; font-size: 11px; color: #818cf8; word-break: break-all; line-height: 1.4;">
                        <?= session()->get('user_wallet') ?: ($isChinese ? '未注册' : 'Não cadastrada') ?>
                    </div>
                </div>

                <!-- Idioma Dropdown -->
                <div style="margin-bottom: 20px;">
                    <p style="font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Idioma / 语言</p>
                    <select onchange="changeLanguage(this.value)" style="width: 100%; background: #0f172a; border: 1px solid #334155; color: white; padding: 8px; border-radius: 8px; outline: none; font-size: 12px; cursor: pointer;">
                        <option value="pt-BR" <?= session()->get('user_lang') == 'pt-BR' ? 'selected' : '' ?>>🇧🇷 Português</option>
                        <option value="zh-CN" <?= session()->get('user_lang') == 'zh-CN' ? 'selected' : '' ?>>🇨🇳 中文</option>
                    </select>
                </div>

                <!-- Botão Sair -->
                <a href="<?= url_to('logout') ?>" style="display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; border: 1px solid rgba(239, 68, 68, 0.2); background: rgba(239, 68, 68, 0.05); color: #f87171; padding: 12px; border-radius: 8px; font-size: 13px; text-decoration: none; font-weight: 700; cursor: pointer; text-align: center; transition: all 0.2s;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><polyline points="16 17 21 12 16 7" /><line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                    <span><?= $isChinese ? '退出' : 'Sair' ?></span>
                </a>
            </div>

            <!-- Views Exclusivas do Mobile (Ocultadas em telas >= 1024px) -->
            <div class="mobile-only-views">
                <header class="mobile-header">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="live-rate-mobile">
                        <div class="rate-dot"></div>
                        <span id="live-rate-val" style="color: #3b82f6; font-weight: 700; font-size: 14px;">R$ 0,0000</span>
                    </div>
                </div>
                <div style="display: flex; gap: 14px; align-items: center;">
                    <!-- Tradutor -->
                    <div id="translator-trigger" onclick="window.openLanguageModal()" ontouchstart="window.openLanguageModal()"
                        style="color: #3b82f6; padding: 12px; margin: -8px; display: flex; align-items: center; cursor: pointer; -webkit-tap-highlight-color: transparent; position: relative; z-index: 5001; pointer-events: auto;"
                        title="Traduzir">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="m5 8 6 6" />
                            <path d="m4 14 6-6 2-3" />
                            <path d="M2 5h12" />
                            <path d="M7 2h1" />
                            <path d="m22 22-5-10-5 10" />
                            <path d="M14 18h6" />
                        </svg>
                    </div>
                    <!-- Operações -->
                    <button onclick="openContractsModal()" id="contracts-btn"
                        style="background: none; border: none; color: #3b82f6; padding: 4px; display: flex; align-items: center; position: relative; cursor: pointer;"
                        title="Minhas Operações">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        <span id="contracts-badge" style="display: none; position: absolute; top: -2px; right: -4px; background: #ef4444; color: white; font-size: 9px; font-weight: 800; min-width: 16px; height: 16px; border-radius: 8px; align-items: center; justify-content: center; padding: 0 3px; line-height: 1;"></span>
                    </button>
                    <!-- Extrato -->
                    <button onclick="openStatementModal()"
                        style="background: none; border: none; color: #3b82f6; padding: 4px; display: flex; align-items: center; cursor: pointer;"
                        title="Extrato">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"/>
                            <line x1="12" y1="20" x2="12" y2="4"/>
                            <line x1="6" y1="20" x2="6" y2="14"/>
                            <line x1="2" y1="20" x2="22" y2="20"/>
                        </svg>
                    </button>
                    <!-- Notificações -->
                    <button onclick="openNotificationsModal()" id="notifications-btn"
                        style="background: none; border: none; color: #3b82f6; padding: 4px; display: flex; align-items: center; position: relative; cursor: pointer;"
                        title="Notificações">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span id="notifications-badge" style="display: none; position: absolute; top: -2px; right: -4px; background: #ef4444; color: white; font-size: 9px; font-weight: 800; min-width: 16px; height: 16px; border-radius: 8px; align-items: center; justify-content: center; padding: 0 3px; line-height: 1;"></span>
                    </button>
                    <!-- Alterar Senha -->
                    <button onclick="openChangePasswordModal(event)"
                        style="background: none; border: none; color: #60a5fa; padding: 4px; display: flex; align-items: center; cursor: pointer;"
                        title="Alterar Senha">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </button>
                    <!-- Sair -->
                    <a href="<?= url_to('logout') ?>"
                        style="text-decoration: none; color: #f87171; padding: 4px; display: flex; align-items: center; cursor: pointer;"
                        title="Sair">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                            <polyline points="16 17 21 12 16 7" />
                            <line x1="21" y1="12" x2="9" y2="12" />
                        </svg>
                    </a>
                </div>
                </header>

                <!-- Saldo Mobile -->
                <div style="background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); padding: 10px 20px; border-bottom: 1px solid rgba(59, 130, 246, 0.2);">
                    <p style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
                        Saldo
                    </p>
                    <div id="balance-badge"
                        style="padding: 6px 12px; border-radius: 6px; font-size: 20px; font-weight: 700; background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); color: #4ade80; display: inline-block;">
                        R$ 0,00
                    </div>
                </div>

                <!-- Container de Lotes Promocionais Mobile -->
                <div id="promotional-lots-mobile" style="display: none; padding: 10px 20px; border-bottom: 1px solid rgba(239, 68, 68, 0.2); background: rgba(239, 68, 68, 0.02);"></div>

                <!-- Botão Comprar + Depositar Mobile -->
                <div style="padding: 8px 15px; background: rgba(15, 23, 42, 0.8); border-bottom: 1px solid rgba(59, 130, 246, 0.1); display: flex; gap: 8px;">
                    <button onclick="openBuyModal(currentExchangeRate)"
                        style="flex: 1; padding: 12px; border-radius: 12px; background: linear-gradient(135deg, #3b82f6, #2563eb); border: none; color: white; font-weight: 700; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                        </svg>
                        <?= $isChinese ? '购买 USDT' : 'Comprar USDT' ?>
                    </button>
                    <button onclick="openDepositModal()"
                        style="flex: 1; padding: 12px; border-radius: 12px; background: linear-gradient(135deg, #10b981, #059669); border: none; color: white; font-weight: 700; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                        <?= $isChinese ? '存款' : 'Depositar' ?>
                    </button>
                </div>
            </div>

            <!-- Gráfico (Exibido no rodapé do menu no desktop, e no fluxo no mobile) -->
            <div class="chart-section" id="chart-section">
                <div class="chart-info">
                    <span><?= lang('App.real_time_tendency') ?></span>
                </div>
                <canvas id="historyChart"></canvas>
                <button class="toggle-chart-btn" onclick="toggleChart()">
                    <span id="toggle-text"><?= lang('App.min') ?></span>
                </button>
            </div>
        </div> <!-- End of dashboard-panel-desktop -->

        <div class="chat-panel-desktop">
            <!-- Cabeçalho do Chat Exclusivo do Desktop -->
            <div class="desktop-chat-header">
                <div></div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <!-- Live Rate -->
                    <div class="live-rate-desktop" style="background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.15); padding: 6px 12px; border-radius: 99px; display: flex; align-items: center; gap: 8px;">
                        <span class="rate-dot" style="width: 8px; height: 8px; background: #3b82f6; border-radius: 50%; display: inline-block;"></span>
                        <span id="live-rate-val-desktop" style="color: #3b82f6; font-weight: 700; font-size: 13px;">R$ 0,0000</span>
                    </div>
                    <!-- Tradutor -->
                    <div onclick="window.openLanguageModal()" style="color: #3b82f6; cursor: pointer; display: flex; align-items: center;" title="Traduzir">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m5 8 6 6" /><path d="m4 14 6-6 2-3" /><path d="M2 5h12" /><path d="M7 2h1" /><path d="m22 22-5-10-5 10" /><path d="M14 18h6" />
                        </svg>
                    </div>
                </div>
            </div>

            <main class="chat-container-mobile" id="chat-messages" style="position: relative;">
            <div class="message bot">
                <?= lang('App.welcome_msg', [
                    'name' => $firstName,
                    'start' => $business_hours['start'],
                    'end' => $business_hours['end']
                ]) ?>
            </div>
            <div id="typing-indicator" class="message bot" style="display: none; padding: 10px 15px;">
                <span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>
            </div>
        </main>



        <form class="mobile-input-area" id="chat-form">
            <input type="text" class="mobile-input" id="user-input" placeholder="<?= lang('App.chat_placeholder') ?>"
                autocomplete="off">
            <button type="submit" class="send-btn-mobile">➤</button>
        </form>
        </div>
    </div>

    <!-- Modals -->
    <!-- Buy Modal -->
    <div id="buy-modal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 5001; justify-content: center; align-items: center; padding: 20px;">
        <div
            style="width: 100%; max-width: 450px; position: relative; background: #1e293b; padding: 30px; border-radius: 16px; font-family: sans-serif;">
            <button onclick="closeModal()"
                style="position: absolute; right: 20px; top: 20px; background: none; border: none; color: #94a3b8; font-size: 24px; cursor: pointer;">&times;</button>

            <?php
                $purchaseModel = $user['purchase_model'] ?? 'usdt';
                $initialMode = $purchaseModel === 'both' ? ($user['last_purchase_mode'] ?: 'usdt') : $purchaseModel;
            ?>
            <div style="text-align: center; margin-bottom: 25px;">
                <h1 style="font-size: 32px; font-weight: 700; margin-bottom: 8px; color: #a78bfa;">
                    <?= lang('App.buy') ?>
                </h1>
                <?php if ($purchaseModel === 'both'): ?>
                <div id="mode-toggle" style="display: flex; gap: 10px; justify-content: center; margin-top: 10px;">
                    <button type="button" class="mode-toggle-btn" data-mode="usdt" style="flex:1; padding: 8px; border-radius: 8px; border: 1px solid #334155; background: rgba(99,102,241,0.15); color: #cbd5e1; cursor: pointer; font-size: 13px; font-weight: 600;"><?= lang('App.amount_usdt') ?></button>
                    <button type="button" class="mode-toggle-btn" data-mode="brl" style="flex:1; padding: 8px; border-radius: 8px; border: 1px solid #334155; background: rgba(99,102,241,0.15); color: #cbd5e1; cursor: pointer; font-size: 13px; font-weight: 600;"><?= lang('App.amount_brl') ?></button>
                </div>
                <?php endif; ?>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="wallet-selector"
                    style="display: block; color: #94a3b8; font-size: 13px; font-weight: 500; margin-bottom: 8px;">
                    <?= session()->get('user_lang') == 'zh-CN' ? 'USDT 接收钱包 (TRC-20)' : 'Carteira de Destino USDT (TRC-20)' ?>
                </label>
                <?php if (!empty($wallets)): ?>
                    <select id="wallet-selector"
                        style="width: 100%; background: #0f172a; border: 1px solid #334155; padding: 12px; border-radius: 8px; color: white; font-size: 14px; font-family: monospace; outline: none; box-sizing: border-box; cursor: pointer;"
                        onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#334155'">
                        <?php foreach ($wallets as $w): ?>
                            <option value="<?= esc($w['address']) ?>" <?= $w['is_default'] ? 'selected' : '' ?>>
                                <?= esc($w['address']) ?> <?= $w['is_default'] ? ($isChinese ? '(默认)' : ' (Padrão)') : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <div
                        style="background: rgba(15, 23, 42, 0.5); padding: 12px; border-radius: 8px; border: 1px dashed #334155; font-family: monospace; font-size: 13px; color: #818cf8; word-break: break-all; line-height: 1.4;">
                        <?= session()->get('user_wallet') ?: ($isChinese ? '未注册' : 'Não cadastrada') ?>
                    </div>
                    <select id="wallet-selector" style="display: none;">
                        <option value="<?= esc(session()->get('user_wallet') ?: '') ?>" selected></option>
                    </select>
                <?php endif; ?>
            </div>


            <div id="usdt-input-group" style="margin-bottom: 20px; <?= $initialMode === 'brl' ? 'display:none;' : '' ?>">
                <label for="usdt-amount"
                    style="display: block; color: #94a3b8; font-size: 13px; font-weight: 500; margin-bottom: 8px;">Valor
                    em USDT (USDT)</label>
                <input type="text" inputmode="numeric" id="usdt-amount" placeholder="Ex: 5,000.00"
                    style="width: 100%; background: #0f172a; border: 1px solid #334155; padding: 12px; border-radius: 8px; color: white; font-size: 16px; outline: none; transition: border-color 0.2s;"
                    onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#334155'">
            </div>

            <div id="brl-input-group" style="margin-bottom: 20px; <?= $initialMode !== 'brl' ? 'display:none;' : '' ?>">
                <label for="brl-amount"
                    style="display: block; color: #94a3b8; font-size: 13px; font-weight: 500; margin-bottom: 8px;">Valor
                    em BRL (R$)</label>
                <input type="text" inputmode="numeric" id="brl-amount" placeholder="Ex: 30.000,00"
                    style="width: 100%; background: #0f172a; border: 1px solid #334155; padding: 12px; border-radius: 8px; color: white; font-size: 16px; outline: none; transition: border-color 0.2s;"
                    onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#334155'">
            </div>

            <div style="margin-bottom: 20px;">
                <label
                    style="display: block; color: #94a3b8; font-size: 13px; font-weight: 500; margin-bottom: 8px;">Prazo
                    de Entrega</label>
                <div class="delivery-selector" style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <?php
                    $allowed = $user['allowed_delivery_types'];
                    $options = ['D+0', 'D+1', 'D+2'];
                    $first = true;
                    $active_val = 'D+0';
                    foreach ($options as $opt):
                        $is_disabled = ($opt === 'D+1' && isset($disable_d1) && $disable_d1) || ($opt === 'D+2' && isset($disable_d2) && $disable_d2);
                        if ($allowed == 'all' || $allowed == $opt):
                    ?>
                        <div class="delivery-option <?= $is_disabled ? 'disabled' : ($first ? 'active' : '') ?>" data-value="<?= $opt ?>"><?= $opt ?></div>
                        <?php if ($first && !$is_disabled) { $first = false; $active_val = $opt; } ?>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
                <div id="conversion-info"
                    style="margin-bottom: 25px; background: rgba(15, 23, 42, 0.4); padding: 15px; border-radius: 12px; border: 1px solid #1e293b;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span
                            style="color: #94a3b8; font-size: 13px; font-weight: 500;"><?= lang('App.live_rate') ?>:</span>
                        <span id="modal-base-rate"
                            style="color: #cbd5e1; font-weight: 600; font-family: 'Outfit', sans-serif;">R$
                            0,0000</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-top: 10px; border-top: 1px solid #334155;">
                        <span
                            style="color: #94a3b8; font-size: 14px; font-weight: 600;"><?= lang('App.final_rate') ?>:</span>
                        <span id="modal-rate"
                            style="color: #a78bfa; font-weight: 700; font-size: 18px; font-family: 'Outfit', sans-serif;">R$
                            0,0000</span>
                    </div>
                    <div id="result-label"
                        style="display: flex; justify-content: space-between; align-items: center; background: rgba(99, 102, 241, 0.1); padding: 12px; border-radius: 8px; border: 1px solid rgba(99, 102, 241, 0.2);"
                        data-label-usdt="<?= lang('App.total_brl') ?>" data-label-brl="<?= lang('App.receive_usdt') ?>">
                        <span id="result-label-text"
                            style="color: #94a3b8; font-size: 14px; font-weight: 700;"><?= $initialMode === 'brl' ? lang('App.receive_usdt') : lang('App.total_brl') ?>:</span>
                        <span id="brl-result"
                            style="color: #818cf8; font-weight: 800; font-size: 22px; font-family: 'Outfit', sans-serif;">R$
                            0,00</span>
                    </div>
                </div>

                <div id="quote-info" style="display: none; margin-bottom: 20px; text-align: center;">
                    <span
                        style="background: rgba(251, 191, 36, 0.1); color: #fbbf24; font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(251, 191, 36, 0.2); text-transform: uppercase;">
                        <i class="fas fa-info-circle" style="margin-right: 4px;"></i> <?= lang('App.delivery_hint') ?>
                    </span>
                </div>

                <button id="confirm-buy-btn"
                    style="width: 100%; padding: 15px; border-radius: 10px; background: #6366f1; border: none; color: white; font-weight: 700; font-size: 16px; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);"
                    onmouseover="this.style.background='#4f46e5'" onmouseout="this.style.background='#6366f1'">
                    Confirmar Compra
                </button>
            </div>
        </div>
        </div>


    <!-- Buy Promo Modal -->
    <div id="buy-promo-modal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 5001; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(8px);">
        <div
            style="width: 100%; max-width: 450px; position: relative; background: #1e293b; padding: 30px; border-radius: 24px; border: 1px solid rgba(244, 63, 94, 0.3); font-family: sans-serif; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
            <button onclick="closePromoModal()"
                style="position: absolute; right: 20px; top: 20px; background: none; border: none; color: #94a3b8; font-size: 24px; cursor: pointer;">&times;</button>

            <div style="text-align: center; margin-bottom: 25px;">
                <h1 style="font-size: 32px; font-weight: 700; margin-bottom: 8px; color: #f43f5e;">
                    Comprar Promoção
                </h1>
                <div style="background: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.2); padding: 6px 12px; border-radius: 20px; display: inline-block; font-size: 11px; font-weight: 700; color: #f43f5e; text-transform: uppercase; letter-spacing: 0.05em;">
                    Lote Promocional Ativo
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="promo-wallet-selector"
                    style="display: block; color: #94a3b8; font-size: 13px; font-weight: 500; margin-bottom: 8px;">
                    <?= session()->get('user_lang') == 'zh-CN' ? 'USDT 接收钱包 (TRC-20)' : 'Carteira de Destino USDT (TRC-20)' ?>
                </label>
                <?php if (!empty($wallets)): ?>
                    <select id="promo-wallet-selector"
                        style="width: 100%; background: #0f172a; border: 1px solid #334155; padding: 12px; border-radius: 8px; color: white; font-size: 14px; font-family: monospace; outline: none; box-sizing: border-box; cursor: pointer;"
                        onfocus="this.style.borderColor='#f43f5e'" onblur="this.style.borderColor='#334155'">
                        <?php foreach ($wallets as $w): ?>
                            <option value="<?= esc($w['address']) ?>" <?= $w['is_default'] ? 'selected' : '' ?>>
                                <?= esc($w['address']) ?> <?= $w['is_default'] ? ($isChinese ? '(默认)' : ' (Padrão)') : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <div
                        style="background: rgba(15, 23, 42, 0.5); padding: 12px; border-radius: 8px; border: 1px dashed #334155; font-family: monospace; font-size: 13px; color: #818cf8; word-break: break-all; line-height: 1.4;">
                        <?= session()->get('user_wallet') ?: ($isChinese ? '未注册' : 'Não cadastrada') ?>
                    </div>
                    <select id="promo-wallet-selector" style="display: none;">
                        <option value="<?= esc(session()->get('user_wallet') ?: '') ?>" selected></option>
                    </select>
                <?php endif; ?>
            </div>

            <div id="promo-usdt-input-group" style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <label for="promo-usdt-amount"
                        style="color: #94a3b8; font-size: 13px; font-weight: 500;">Valor em USDT (USDT)</label>
                    <span id="promo-stock-display" style="font-size: 12px; font-weight: 600; color: #fca5a5;">
                        Estoque: 0,00 USDT
                    </span>
                </div>
                <input type="text" inputmode="numeric" id="promo-usdt-amount" placeholder="Ex: 1,000.00"
                    style="width: 100%; background: #0f172a; border: 1px solid #334155; padding: 12px; border-radius: 8px; color: white; font-size: 16px; outline: none; transition: border-color 0.2s;"
                    onfocus="this.style.borderColor='#f43f5e'" onblur="this.style.borderColor='#334155'">
            </div>

            <div style="margin-bottom: 20px;">
                <label
                    style="display: block; color: #94a3b8; font-size: 13px; font-weight: 500; margin-bottom: 8px;">Prazo de Entrega</label>
                <div id="promo-delivery-display"
                    style="background: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.2); padding: 12px; border-radius: 8px; font-weight: 700; color: white; font-size: 15px; text-align: center; text-transform: uppercase;">
                    D+0
                </div>
            </div>

            <div id="promo-conversion-info"
                style="margin-bottom: 25px; background: rgba(15, 23, 42, 0.4); padding: 15px; border-radius: 12px; border: 1px solid #1e293b;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span
                        style="color: #94a3b8; font-size: 13px; font-weight: 500;">Cotação Final:</span>
                    <span id="promo-modal-rate"
                        style="color: #fca5a5; font-weight: 700; font-size: 18px; font-family: 'Outfit', sans-serif;">R$ 0,0000</span>
                </div>
                <div id="promo-result-label"
                    style="display: flex; justify-content: space-between; align-items: center; background: rgba(244, 63, 94, 0.1); padding: 12px; border-radius: 8px; border: 1px solid rgba(244, 63, 94, 0.2);">
                    <span
                        style="color: #94a3b8; font-size: 14px; font-weight: 700;">Total em BRL:</span>
                    <span id="promo-brl-result"
                        style="color: #f43f5e; font-weight: 800; font-size: 22px; font-family: 'Outfit', sans-serif;">R$ 0,00</span>
                </div>
            </div>

            <button id="confirm-promo-buy-btn"
                style="width: 100%; padding: 15px; border-radius: 10px; background: #f43f5e; border: none; color: white; font-weight: 700; font-size: 16px; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(244, 63, 94, 0.3);"
                onmouseover="this.style.background='#e11d48'" onmouseout="this.style.background='#f43f5e'">
                Confirmar Compra Promocional
            </button>
        </div>
    </div>


        <!-- Success Modal -->
    <div id="success-modal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 5001; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(8px);">
        <div class="auth-container"
            style="max-width: 400px; text-align: center; background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(99, 102, 241, 0.3); padding: 40px; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
            <div
                style="width: 80px; height: 80px; background: rgba(34, 197, 94, 0.1); border: 2px solid #22c55e; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 24px;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="3"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <h2 style="color: white; font-size: 24px; font-weight: 700; margin-bottom: 12px;">
                <?= lang('App.transaction_success_title') ?></h2>
            <p id="success-msg-text" style="color: #94a3b8; font-size: 15px; line-height: 1.6; margin-bottom: 30px;">
                <?= lang('App.transaction_success_msg') ?>
            </p>
            <div id="success-actions">
                <button onclick="closeSuccessModal()" style="width: 100%; background: rgba(255,255,255,0.05); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); padding: 15px; border-radius: 12px; cursor: pointer; font-weight: 600;">
                    <?= lang('App.understood') ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Deposit Modal -->
    <div id="deposit-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 5001; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(10px);">
        <div style="max-width: 440px; width: 100%; background: #1e293b; padding: 32px; border-radius: 24px; border: 1px solid rgba(16,185,129,0.2); position: relative; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden;">
            <button onclick="closeDepositModal()" style="position: absolute; right: 16px; top: 16px; background: none; border: none; color: #94a3b8; font-size: 24px; cursor: pointer; z-index: 10;">&times;</button>

            <div style="flex-shrink: 0; text-align: center;">
                <div style="width: 52px; height: 52px; background: rgba(16,185,129,0.1); border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 12px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <h2 style="color: white; font-size: 18px; font-weight: 700; margin-bottom: 4px;"><?= $isChinese ? '存款' : 'Realizar Depósito' ?></h2>
                <p style="color: #94a3b8; font-size: 12px; margin-bottom: 16px;"><?= $isChinese ? '上传付款凭证，金额将自动识别。' : 'Envie o(s) comprovante(s) de pagamento. O valor será identificado automaticamente.' ?></p>
            </div>

            <input type="file" id="deposit-bulk-input" multiple accept="image/*,application/pdf" style="display:none" onchange="handleBulkFileSelect(this)">
            <div id="deposit-dropzone" onclick="document.getElementById('deposit-bulk-input').click()"
                ondragover="event.preventDefault(); this.style.borderColor='#34d399'; this.style.background='rgba(16,185,129,0.1)';"
                ondragleave="this.style.borderColor='rgba(16,185,129,0.3)'; this.style.background='transparent';"
                ondrop="handleDepositDrop(event, this)"
                style="flex-shrink: 0; border: 2px dashed rgba(16,185,129,0.3); border-radius: 14px; padding: 20px 16px; text-align: center; cursor: pointer; margin-bottom: 16px; transition: 0.2s;">
                <div style="font-size: 26px; margin-bottom: 6px;">📎</div>
                <div style="color: #34d399; font-weight: 600; font-size: 13px; margin-bottom: 2px;"><?= $isChinese ? '点击或拖拽多个凭证到此处' : 'Clique ou arraste vários comprovantes aqui' ?></div>
                <div style="color: #64748b; font-size: 11px;"><?= $isChinese ? '可一次选择多个文件' : 'Você pode selecionar quantos arquivos quiser de uma vez' ?></div>
            </div>

            <!-- Scrollable list of items -->
            <div id="deposit-items-list" style="overflow-y: auto; flex: 1; min-height: 0; display: flex; flex-direction: column; gap: 16px; padding-right: 6px; margin-bottom: 16px;"></div>

            <div style="flex-shrink: 0;">
                <button id="deposit-submit-btn" onclick="submitDeposit()"
                    style="width: 100%; padding: 14px; border-radius: 12px; background: #10b981; border: none; color: white; font-weight: 700; font-size: 15px; cursor: pointer;">
                    <?= $isChinese ? '提交存款' : 'Enviar Depósito' ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Session Expired Modal -->
    <div id="session-expired-modal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 9999; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(8px);">
        <div style="max-width: 360px; width: 100%; text-align: center; background: rgba(30, 41, 59, 0.95); border: 1px solid rgba(239, 68, 68, 0.3); padding: 40px 30px; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.6);">
            <div style="width: 64px; height: 64px; background: rgba(239, 68, 68, 0.1); border: 2px solid #ef4444; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <h2 style="color: white; font-size: 20px; font-weight: 700; margin-bottom: 10px;">
                <?= $isChinese ? '会话已过期' : 'Sessão expirada' ?>
            </h2>
            <p style="color: #94a3b8; font-size: 14px; line-height: 1.6; margin-bottom: 24px;">
                <?= $isChinese ? '您的会话已过期，请重新登录。' : 'Sua sessão expirou. Você será redirecionado para o login.' ?>
            </p>
            <button onclick="window.location.href='<?= site_url('login') ?>'"
                style="width: 100%; padding: 14px; border-radius: 10px; background: #ef4444; border: none; color: white; font-weight: 700; font-size: 15px; cursor: pointer;">
                <?= $isChinese ? '重新登录' : 'Fazer login' ?>
            </button>
        </div>
    </div>

    <!-- Contracts Modal -->
    <div id="contracts-modal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 5001; justify-content: center; align-items: center; padding: 15px; backdrop-filter: blur(10px);">
        <div
            style="background: rgba(30, 41, 59, 0.98); width: 100%; max-height: 88vh; padding: 25px; border-radius: 24px; position: relative; overflow-y: auto; border: 1px solid rgba(99, 102, 241, 0.25); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.6);">
            <button onclick="closeContractsModal()"
                style="position: absolute; right: 20px; top: 20px; background: rgba(255,255,255,0.05); border: none; color: #94a3b8; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">&times;</button>

            <h1 style="font-size: 20px; color: white; font-weight: 700; margin-bottom: 4px; padding-right: 40px;">
                Posição Financeira
            </h1>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 20px;">
                Seu saldo devedor e o USDT pendente de envio
            </p>

            <!-- Filter Tabs -->
            <div style="display:flex;gap:6px;margin-bottom:20px;overflow-x:auto;padding-bottom:2px;">
                <button id="cf-btn-todos" onclick="setContractsFilter('todos')"
                    style="flex-shrink:0;padding:9px 14px;border-radius:10px;background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.35);color:#a78bfa;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">
                    Todos
                </button>
                <button id="cf-btn-d0" onclick="setContractsFilter('d0')"
                    style="flex-shrink:0;padding:9px 14px;border-radius:10px;background:rgba(15,23,42,0.5);border:1px solid #334155;color:#94a3b8;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">
                    D+0
                </button>
                <button id="cf-btn-d1" onclick="setContractsFilter('d1')"
                    style="flex-shrink:0;padding:9px 14px;border-radius:10px;background:rgba(15,23,42,0.5);border:1px solid #334155;color:#94a3b8;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">
                    D+1
                </button>
                <button id="cf-btn-d2" onclick="setContractsFilter('d2')"
                    style="flex-shrink:0;padding:9px 14px;border-radius:10px;background:rgba(15,23,42,0.5);border:1px solid #334155;color:#94a3b8;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">
                    D+2
                </button>
                <button id="cf-btn-atrasado" onclick="setContractsFilter('atrasado')"
                    style="flex-shrink:0;padding:9px 14px;border-radius:10px;background:rgba(15,23,42,0.5);border:1px solid #334155;color:#94a3b8;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">
                    Atrasados
                </button>
            </div>

            <!-- Loading state -->
            <div id="debts-loading" style="text-align:center;padding:30px;color:#64748b;font-size:14px;">Carregando...</div>

            <!-- Main summary card -->
            <div id="debts-summary" style="display:none;">
                <!-- A Pagar (BRL) -->
                <div style="background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.2);border-radius:16px;padding:20px;margin-bottom:12px;">
                    <p style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Total a Pagar</p>
                    <p id="debts-brl" style="font-size:28px;font-weight:900;color:#f87171;line-height:1;">R$ 0,00</p>
                    <p id="debts-count" style="font-size:12px;color:#64748b;margin-top:6px;">0 operações em aberto</p>
                </div>

                <!-- USDT card -->
                <div style="background:rgba(99,102,241,0.07);border:1px solid rgba(99,102,241,0.2);border-radius:14px;padding:16px 18px;">
                    <p style="font-size:9px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:5px;">USDT a receber</p>
                    <p id="debts-usdt" style="font-size:24px;font-weight:800;color:#a78bfa;line-height:1.1;">0,00</p>
                    <p style="font-size:9px;color:#64748b;margin-top:3px;">USDT</p>
                </div>
            </div>

            <!-- Empty State -->
            <div id="contracts-empty" style="display:none;text-align:center;padding:40px 20px;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#334155" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 16px;display:block;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <p style="color:#64748b;font-size:14px;">Nenhuma operação nesta categoria</p>
            </div>
        </div>
    </div>

    <!-- Statement Modal -->
    <div id="statement-modal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 5001; justify-content: center; align-items: center; padding: 15px; backdrop-filter: blur(10px);">
        <div
            style="background: rgba(30, 41, 59, 0.98); width: 100%; max-width: 420px; max-height: 88vh; padding: 25px; border-radius: 24px; position: relative; display: flex; flex-direction: column; border: 1px solid rgba(59, 130, 246, 0.2); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
            <button onclick="closeStatementModal()"
                style="position: absolute; right: 20px; top: 20px; background: rgba(255,255,255,0.05); border: none; color: #94a3b8; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; cursor: pointer;">&times;</button>

            <h1 style="font-size: 20px; color: white; font-weight: 700; margin-bottom: 4px; padding-right: 40px;"><?= lang('App.stmt_title') ?></h1>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 16px;"><?= lang('App.stmt_subtitle') ?></p>

            <!-- Posição Financeira (resumo) -->
            <button onclick="openContractsModal()"
                style="display:flex; align-items:center; justify-content:space-between; gap:10px; width:100%; background:rgba(15,23,42,0.6); border:1px solid #334155; border-radius:14px; padding:12px 14px; margin-bottom:16px; cursor:pointer; text-align:left; flex-shrink:0;">
                <div style="display:flex; gap:20px; flex:1; min-width:0;">
                    <div style="min-width:0;">
                        <p style="font-size:9px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:3px;">A Pagar</p>
                        <p id="stmt-pos-brl" style="font-size:15px;font-weight:800;color:#f87171;line-height:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">R$ 0,00</p>
                    </div>
                    <div style="min-width:0;">
                        <p style="font-size:9px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:3px;">A Receber</p>
                        <p id="stmt-pos-usdt" style="font-size:15px;font-weight:800;color:#a78bfa;line-height:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">0,00 USDT</p>
                    </div>
                </div>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="m9 18 6-6-6-6"/></svg>
            </button>

            <!-- Filters -->
            <div style="display: flex; gap: 8px; margin-bottom: 12px; overflow-x: auto; padding-bottom: 2px; flex-shrink: 0;">
                <button class="stmt-filter-btn" data-nature="" onclick="setStatementFilter('')"
                    style="flex-shrink:0;padding:8px 14px;border-radius:10px;background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.2);color:#3b82f6;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;"><?= lang('App.stmt_filter_all') ?></button>
                <button class="stmt-filter-btn" data-nature="C" onclick="setStatementFilter('C')"
                    style="flex-shrink:0;padding:8px 14px;border-radius:10px;background:rgba(15,23,42,0.5);border:1px solid #334155;color:#94a3b8;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;"><?= lang('App.stmt_filter_credit') ?></button>
                <button class="stmt-filter-btn" data-nature="D" onclick="setStatementFilter('D')"
                    style="flex-shrink:0;padding:8px 14px;border-radius:10px;background:rgba(15,23,42,0.5);border:1px solid #334155;color:#94a3b8;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;"><?= lang('App.stmt_filter_debit') ?></button>
            </div>

            <!-- Search -->
            <div style="position: relative; margin-bottom: 12px; flex-shrink: 0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round"
                    style="position:absolute;left:12px;top:50%;transform:translateY(-50%);">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" id="statement-search" placeholder="<?= lang('App.stmt_search_placeholder') ?>"
                    oninput="onStatementSearch(this.value)"
                    style="width:100%;background:rgba(15,23,42,0.6);border:1px solid #334155;border-radius:12px;color:white;padding:10px 12px 10px 36px;font-size:14px;outline:none;box-sizing:border-box;">
            </div>

            <!-- Toggle Advanced Filters Button -->
            <button onclick="toggleAdvancedFilters()"
                style="display:flex; align-items:center; justify-content:center; gap:6px; width:100%; background:none; border:none; color:#3b82f6; font-size:12px; font-weight:600; cursor:pointer; padding:6px 0; margin-bottom:12px; flex-shrink:0;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                <span>Filtros Avançados</span>
                <svg id="stmt-filters-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="transition: transform 0.2s;"><polyline points="6 9 12 15 18 9"/></svg>
            </button>

            <!-- Advanced Filters & Exports -->
            <div id="statement-advanced-filters" style="display:none; flex-direction:column; gap:10px; margin-bottom:16px; background:rgba(15,23,42,0.3); padding:12px; border-radius:16px; border:1px solid rgba(51,65,85,0.4); flex-shrink:0;">
                <!-- Dates Grid -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; font-size:9px; color:#64748b; text-transform:uppercase; font-weight:700; margin-bottom:4px;">De</label>
                        <input type="date" id="statement-start-date" onchange="resetStatement()"
                            style="width:100%; background:rgba(15,23,42,0.6); border:1px solid #334155; border-radius:10px; color:white; padding:8px; font-size:12px; outline:none; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block; font-size:9px; color:#64748b; text-transform:uppercase; font-weight:700; margin-bottom:4px;">Até</label>
                        <input type="date" id="statement-end-date" onchange="resetStatement()"
                            style="width:100%; background:rgba(15,23,42,0.6); border:1px solid #334155; border-radius:10px; color:white; padding:8px; font-size:12px; outline:none; box-sizing:border-box;">
                    </div>
                </div>

                <!-- Selectors Grid -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; font-size:9px; color:#64748b; text-transform:uppercase; font-weight:700; margin-bottom:4px;">Tipo</label>
                        <select id="statement-type" onchange="resetStatement()"
                            style="width:100%; background:rgba(15,23,42,0.6); border:1px solid #334155; border-radius:10px; color:white; padding:8px; font-size:12px; outline:none; box-sizing:border-box; cursor:pointer;">
                            <option value="">Todos</option>
                            <option value="deposit">Depósito</option>
                            <option value="withdrawal">Saída / Retirada</option>
                            <option value="margin_lock">Compra de USDT</option>
                            <option value="limit_release">Liberação Limite</option>
                            <option value="partial_amortization">Amortização Parcial</option>
                            <option value="full_settlement">Liquidação Integral</option>
                            <option value="late_fee">Multa por Atraso</option>
                            <option value="adjustment">Ajustes</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:9px; color:#64748b; text-transform:uppercase; font-weight:700; margin-bottom:4px;">Status</label>
                        <select id="statement-status" onchange="resetStatement()"
                            style="width:100%; background:rgba(15,23,42,0.6); border:1px solid #334155; border-radius:10px; color:white; padding:8px; font-size:12px; outline:none; box-sizing:border-box; cursor:pointer;">
                            <option value="">Todos</option>
                            <option value="completed">Lançado</option>
                            <option value="pending">Pendente</option>
                            <option value="rejected">Rejeitado</option>
                        </select>
                    </div>
                </div>

                <!-- Exports Row -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:4px;">
                    <button onclick="downloadStatement('pdf')"
                        style="display:flex; align-items:center; justify-content:center; gap:6px; padding:10px; background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.2); border-radius:12px; color:#f87171; font-size:12px; font-weight:700; cursor:pointer; transition:all 0.2s;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        PDF
                    </button>
                    <button onclick="downloadStatement('xlsx')"
                        style="display:flex; align-items:center; justify-content:center; gap:6px; padding:10px; background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.2); border-radius:12px; color:#4ade80; font-size:12px; font-weight:700; cursor:pointer; transition:all 0.2s;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Excel
                    </button>
                </div>
            </div>

            <!-- List (scrollable) -->
            <div id="statement-list" style="overflow-y: auto; flex: 1; min-height: 0;"></div>
            <div id="statement-loader" style="display:none;text-align:center;padding:12px 0;color:#475569;font-size:13px;flex-shrink:0;">
                <?= lang('App.loading') ?>
            </div>
            <div id="statement-pagination" style="display:flex;flex-wrap:wrap;gap:6px;justify-content:center;align-items:center;padding-top:12px;flex-shrink:0;"></div>
        </div>
    </div>

    <!-- Notifications Modal -->
    <div id="notifications-modal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 5001; justify-content: center; align-items: center; padding: 15px; backdrop-filter: blur(10px);">
        <div
            style="background: rgba(30, 41, 59, 0.98); width: 100%; max-width: 420px; max-height: 88vh; padding: 25px; border-radius: 24px; position: relative; display: flex; flex-direction: column; border: 1px solid rgba(59, 130, 246, 0.2); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
            <button onclick="closeNotificationsModal()"
                style="position: absolute; right: 20px; top: 20px; background: rgba(255,255,255,0.05); border: none; color: #94a3b8; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; cursor: pointer;">&times;</button>

            <h1 style="font-size: 20px; color: white; font-weight: 700; margin-bottom: 4px; padding-right: 40px;"><?= $isChinese ? '系统通知' : 'Notificações' ?></h1>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 16px;"><?= $isChinese ? '查看您的最新财务和操单更新。' : 'Acompanhe as atualizações de saldo e operações.' ?></p>

            <!-- List (scrollable) -->
            <div id="notifications-list" style="overflow-y: auto; flex: 1; min-height: 0; display: flex; flex-direction: column; gap: 12px;"></div>
            <div id="notifications-loader" style="display:none;text-align:center;padding:12px 0;color:#475569;font-size:13px;flex-shrink:0;">
                <?= lang('App.loading') ?>
            </div>
            <div id="notifications-empty" style="display:none;text-align:center;padding:40px 20px;color:#64748b;font-size:14px;">
                <?= $isChinese ? '暂无新通知。' : 'Nenhuma notificação por enquanto.' ?>
            </div>
        </div>
    </div>

    <!-- Alert Notification Modal -->
    <div id="alert-notification-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 6000; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(8px);">
        <div style="background: rgba(30, 41, 59, 0.98); width: 100%; max-width: 380px; padding: 30px; border-radius: 24px; text-align: center; border: 2px solid #3b82f6; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.6); position: relative; animation: scaleUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
            <!-- Icon -->
            <div id="alert-notif-icon-container" style="width: 56px; height: 56px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px; color: #3b82f6;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </div>
            <h2 id="alert-notif-title" style="color: white; font-size: 18px; font-weight: 700; margin-bottom: 12px;"></h2>
            <p id="alert-notif-message" style="color: #94a3b8; font-size: 14px; line-height: 1.5; margin-bottom: 25px;"></p>
            
            <button onclick="closeAlertNotifModal()" style="width: 100%; background: #3b82f6; color: white; border: none; padding: 12px; border-radius: 12px; font-weight: 600; font-size: 14px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#3b82f6'">
                <?= lang('App.understood') ?>
            </button>
        </div>
    </div>

    <!-- Proof Upload Modal -->
    <div id="proof-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 5001; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(10px);">
        <div style="width: 100%; max-width: 400px; background: #1e293b; padding: 30px; border-radius: 24px; text-align: center; border: 1px solid rgba(59, 130, 246, 0.2);">
            <div style="width: 60px; height: 60px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px;">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            </div>
            <h2 style="color: white; font-size: 20px; font-weight: 700; margin-bottom: 10px;"><?= $isChinese ? '上传付款凭证' : 'Enviar Comprovante' ?></h2>
            <p style="color: #94a3b8; font-size: 14px; margin-bottom: 25px;"><?= $isChinese ? '请选择付款截图或PDF文件。' : 'Por favor, selecione o print ou PDF do pagamento.' ?></p>
            
            <input type="file" id="proof-file" accept="image/*,application/pdf" style="display: none;" onchange="handleFileSelect(this)">
            <input type="hidden" id="proof-transaction-id">
            
            <label for="proof-file" id="file-label" style="display: block; padding: 25px; border: 2px dashed rgba(59, 130, 246, 0.3); border-radius: 16px; cursor: pointer; transition: 0.3s; margin-bottom: 20px;">
                <span id="file-name" style="color: #60a5fa; font-weight: 500; font-size: 14px;"><?= $isChinese ? '点击此处选择文件' : 'Clique aqui para selecionar' ?></span>
            </label>

            <div style="margin-bottom: 20px; text-align: left;">
                <label style="display: block; color: #94a3b8; font-size: 13px; margin-bottom: 8px;"><?= $isChinese ? '备注 (可选)' : 'Observações (Opcional)' ?></label>
                <textarea id="proof-text" rows="3" style="width: 100%; background: #0f172a; border: 1px solid #334155; border-radius: 12px; color: white; padding: 12px; font-size: 14px; outline: none; resize: none;" placeholder="<?= $isChinese ? '输入任何额外信息...' : 'Digite qualquer informação extra...' ?>"></textarea>
            </div>

            <button id="upload-btn" onclick="uploadProof()" style="width: 100%; display: none; background: #6366f1; border: none; padding: 15px; border-radius: 12px; color: white; font-weight: 700;">
                <?= $isChinese ? '开始上传' : 'Iniciar Upload' ?>
            </button>
            
            <button onclick="closeProofModal()" style="margin-top: 15px; background: none; border: none; color: #64748b; font-size: 13px; cursor: pointer;"><?= $isChinese ? '稍后再说' : 'Fazer isso mais tarde' ?></button>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="change-password-modal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 5001; justify-content: center; align-items: center; padding: 15px; backdrop-filter: blur(10px);">
        <div
            style="background: rgba(30, 41, 59, 0.98); width: 100%; max-width: 400px; padding: 25px; border-radius: 24px; position: relative; border: 1px solid rgba(59, 130, 246, 0.25); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.6); box-sizing: border-box;">
            <button onclick="closeChangePasswordModal()"
                style="position: absolute; right: 20px; top: 20px; background: rgba(255,255,255,0.05); border: none; color: #94a3b8; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; cursor: pointer;">&times;</button>

            <h1 style="font-size: 20px; color: white; font-weight: 700; margin-bottom: 4px; padding-right: 40px; text-align: left;">
                <?= $isChinese ? '修改密码' : 'Alterar Senha' ?>
            </h1>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 20px; text-align: left;">
                <?= $isChinese ? '请输入当前密码进行确认' : 'Forneça a senha atual para confirmar a alteração' ?>
            </p>

            <div id="pwd-alert" style="display: none; padding: 12px 15px; border-radius: 12px; font-size: 14px; margin-bottom: 20px; border: 1px solid; text-align: left;"></div>

            <form id="change-password-form" onsubmit="submitChangePassword(event)" style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; flex-direction: column; gap: 6px; text-align: left;">
                    <label style="font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase;"><?= $isChinese ? '当前密码' : 'Senha Atual' ?></label>
                    <input type="password" id="pwd-current" placeholder="••••••••" required
                        style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 12px; color: white; outline: none; box-sizing: border-box; font-size: 15px;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px; text-align: left;">
                    <label style="font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase;"><?= $isChinese ? '新密码' : 'Nova Senha' ?></label>
                    <input type="password" id="pwd-new" placeholder="••••••••" required
                        style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 12px; color: white; outline: none; box-sizing: border-box; font-size: 15px;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px; text-align: left;">
                    <label style="font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase;"><?= $isChinese ? '确认新密码' : 'Confirmar Nova Senha' ?></label>
                    <input type="password" id="pwd-confirm" placeholder="••••••••" required
                        style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 12px; color: white; outline: none; box-sizing: border-box; font-size: 15px;">
                </div>
                <button type="submit" style="width: 100%; background: #3b82f6; color: white; border: none; padding: 14px; border-radius: 12px; font-weight: 600; font-size: 15px; cursor: pointer; transition: background-color 0.2s; margin-top: 10px;">
                    <?= $isChinese ? '保存新密码' : 'Salvar Nova Senha' ?>
                </button>
            </form>
        </div>
    </div>

        <style>
            @keyframes scaleUp {
                from {
                    transform: scale(0);
                    opacity: 0;
                }

                to {
                    transform: scale(1);
                    opacity: 1;
                }
            }
        </style>

        <script>
            function getCsrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            }

            function formatUSDTMask(value) {
                let clean = value.replace(/\D/g, '');
                if (!clean || clean === '00' || clean === '0') return '';
                
                clean = clean.replace(/^0+/, '');
                if (clean.length < 3) {
                    clean = clean.padStart(3, '0');
                }
                
                let cents = parseInt(clean, 10);
                if (isNaN(cents)) return '';
                let val = (cents / 100).toFixed(2);
                let parts = val.split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                return parts.join('.');
            }

            function getCleanUSDT(value) {
                let clean = value.replace(/,/g, '');
                return parseFloat(clean) || 0;
            }

            function formatBRLMask(value) {
                let clean = value.replace(/\D/g, '');
                if (!clean || clean === '00' || clean === '0') return '';
                
                clean = clean.replace(/^0+/, '');
                if (clean.length < 3) {
                    clean = clean.padStart(3, '0');
                }
                
                let cents = parseInt(clean, 10);
                if (isNaN(cents)) return '';
                let val = (cents / 100).toFixed(2);
                let parts = val.split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                return parts.join(',');
            }

            function getCleanBRL(value) {
                let clean = value.replace(/\./g, '').replace(',', '.');
                return parseFloat(clean) || 0;
            }

            let _sessionExpiredHandled = false;
            function handleUnauthorized() {
                if (_sessionExpiredHandled) return;
                _sessionExpiredHandled = true;
                showModal('session-expired-modal');
                setTimeout(() => { window.location.href = '<?= site_url('login') ?>'; }, 3000);
            }

            (function () {
                const _orig = window.fetch;
                window.fetch = async function (input, init) {
                    init = init ?? {};
                    const headers = new Headers(init.headers ?? {});
                    if (!headers.has('X-Requested-With')) headers.set('X-Requested-With', 'XMLHttpRequest');
                    const response = await _orig(input, { ...init, headers });
                    if (response.status === 401) handleUnauthorized();
                    return response;
                };
            })();

            const chatMessages = document.getElementById('chat-messages');
            const chatForm = document.getElementById('chat-form');
            const userInput = document.getElementById('user-input');
            const typingIndicator = document.getElementById('typing-indicator');
            const isChinese = <?= (session()->get('user_lang') === 'zh-CN') ? 'true' : 'false' ?>;
            const opsLang = {
                loading:        '<?= lang('App.loading') ?>',
                error:          '<?= lang('App.ops_error') ?>',
                overdueAlert:   '<?= lang('App.ops_overdue_alert') ?>',
                expiresToday:   '<?= lang('App.ops_expires_today') ?>',
                label:          '<?= lang('App.ops_label') ?>',
                expires:        '<?= addslashes(lang('App.ops_expires')) ?>',
                usdtContracted: '<?= lang('App.ops_usdt_contracted') ?>',
                value:          '<?= lang('App.ops_value') ?>',
                statusPending:  '<?= lang('App.status_pending') ?>',
                statusPartial:  '<?= lang('App.ops_status_partial') ?>',
                statusOverdue:  '<?= lang('App.ops_status_overdue') ?>',
                paidLabel:      '<?= lang('App.ops_paid') ?>',
                totalLabel:     '<?= lang('App.ops_total_contract') ?>',
                usdtPurchased:  '<?= lang('App.ops_usdt_purchased') ?>',
                usdtSent:       '<?= lang('App.ops_usdt_sent') ?>',
            };
            let currentExchangeRate = 0;
            let currentBaseRate = 0;
            let currentFeePercent = 0;
            let standardExchangeRate = 0;
            let activePromotionalLots = [];
            let selectedDeliveryType = '<?= $active_val ?>';
            const quotationFlow = '<?= $quotation_flow ?>';
            const operatorWhatsapp = '<?= $operator_whatsapp ?>';
            const purchaseModel = '<?= $purchaseModel ?>';
            let currentInputMode = '<?= $initialMode ?>';

            function updateResultDisplay() {
                const resultLabelDiv = document.getElementById('result-label');
                const resultText = document.getElementById('result-label-text');
                const resultValue = document.getElementById('brl-result');
                if (currentInputMode === 'brl') {
                    const brl = getCleanBRL(document.getElementById('brl-amount').value) || 0;
                    const usdt = currentExchangeRate > 0 ? brl / currentExchangeRate : 0;
                    resultText.textContent = resultLabelDiv.dataset.labelBrl + ':';
                    resultValue.textContent = `${usdt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} USDT`;
                } else {
                    const usdt = getCleanUSDT(document.getElementById('usdt-amount').value) || 0;
                    const brl = usdt * currentExchangeRate;
                    resultText.textContent = resultLabelDiv.dataset.labelUsdt + ':';
                    resultValue.textContent = `R$ ${brl.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                }
            }

            document.querySelectorAll('.mode-toggle-btn').forEach(btn => {
                btn.onclick = () => {
                    currentInputMode = btn.dataset.mode;
                    document.getElementById('usdt-input-group').style.display = currentInputMode === 'usdt' ? '' : 'none';
                    document.getElementById('brl-input-group').style.display = currentInputMode === 'brl' ? '' : 'none';
                    updateResultDisplay();
                };
            });

            async function changeLanguage(lang) {
                await fetch('<?= url_to('update_language') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({ language: lang })
                });
                location.reload();
            }

            function toggleChart() {
                const section = document.getElementById('chart-section');
                const text = document.getElementById('toggle-text');
                section.classList.toggle('collapsed');
                text.textContent = section.classList.contains('collapsed') ? '<?= lang('App.show') ?>' : '<?= lang('App.min') ?>';
            }



            async function updateLiveRate() {
                try {
                    if (activePromoLotId) {
                        return;
                    }
                    const response = await fetch('<?= url_to('chat_rate') ?>?delivery_type=' + encodeURIComponent(selectedDeliveryType));
                    const data = await response.json();
                    if (activePromoLotId) {
                        return;
                    }
                    // Mostrar cotação SEM taxas no header
                    const formattedRate = `R$ ${parseFloat(data.base_rate).toLocaleString('pt-BR', { minimumFractionDigits: 4, maximumFractionDigits: 4 })}`;
                    const liveRateVal = document.getElementById('live-rate-val');
                    if (liveRateVal) liveRateVal.textContent = formattedRate;
                    const liveRateValDesktop = document.getElementById('live-rate-val-desktop');
                    if (liveRateValDesktop) liveRateValDesktop.textContent = formattedRate;

                    currentExchangeRate = parseFloat(data.rate);
                    currentBaseRate = parseFloat(data.base_rate);
                    currentFeePercent = parseFloat(data.fee_percent);
                    standardExchangeRate = currentExchangeRate;

                    if (document.getElementById('buy-modal') && document.getElementById('buy-modal').style.display === 'flex') {
                        document.getElementById('modal-base-rate').textContent = `R$ ${currentBaseRate.toLocaleString('pt-BR', { minimumFractionDigits: 4 })}`;
                        document.getElementById('modal-rate').textContent = `R$ ${currentExchangeRate.toLocaleString('pt-BR', { minimumFractionDigits: 4 })}`;
                        updateResultDisplay();
                    }

                    if (window.mobileChart) {
                        const lastVal = window.mobileChart.data.datasets[0].data.slice(-1)[0];
                        if (lastVal !== currentExchangeRate) {
                            window.mobileChart.data.labels.push(new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
                            window.mobileChart.data.datasets[0].data.push(currentExchangeRate);
                            if (window.mobileChart.data.labels.length > 15) {
                                window.mobileChart.data.labels.shift();
                                window.mobileChart.data.datasets[0].data.shift();
                            }
                            const values = window.mobileChart.data.datasets[0].data;
                            window.mobileChart.options.scales.y.min = Math.min(...values) * 0.9995;
                            window.mobileChart.options.scales.y.max = Math.max(...values) * 1.0005;
                            window.mobileChart.update('none');
                        }
                    }
                } catch (e) { }
            }

            async function updateDebtBalance() {
                try {
                    const response = await fetch('<?= url_to('chat_debt') ?>');
                    const data = await response.json();
                    if (data.balance !== undefined) {
                        const badge = document.getElementById('balance-badge');
                        const value = data.balance.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        const isNeg = data.balance < 0;

                        if (badge) {
                            badge.textContent = `R$ ${value}`;
                            badge.style.background = isNeg ? 'rgba(239,68,68,0.1)' : 'rgba(34,197,94,0.1)';
                            badge.style.border = isNeg ? '1px solid rgba(239,68,68,0.2)' : '1px solid rgba(34,197,94,0.2)';
                            badge.style.color = isNeg ? '#f87171' : '#4ade80';
                        }

                        const desktopBadge = document.getElementById('desktop-balance-badge');
                        if (desktopBadge) {
                            desktopBadge.textContent = `R$ ${value}`;
                            desktopBadge.style.color = isNeg ? '#f87171' : '#4ade80';
                        }
                    }
                } catch (e) { console.error("Erro ao atualizar saldo"); }
            }

            // Initialization calls moved to the bottom

            let lastMessageId = 0;
            let isLoadingHistory = false;
            async function loadChatHistory() {
                if (isLoadingHistory) return;
                isLoadingHistory = true;
                try {
                    const response = await fetch('<?= url_to('chat_messages_history') ?>?last_id=' + lastMessageId);
                    const data = await response.json();
                    
                    const isInitialLoad = (lastMessageId === 0);
                    
                    data.forEach(msg => {
                        const msgId = parseInt(msg.id);
                        if (msgId > lastMessageId) {
                            lastMessageId = msgId;
                        }
                        
                        // Durante o polling (isInitialLoad === false), não adicionamos mensagens do próprio 'user' para evitar duplicação local
                        if (!isInitialLoad && msg.sender === 'user') {
                            return;
                        }
                        
                        addMessage(msg.message, msg.sender, msg.show_buy == 1, parseFloat(msg.rate), parseFloat(msg.suggested_amount));
                    });
                } catch (e) { } finally {
                    isLoadingHistory = false;
                }
            }

            async function initChart() {
                const response = await fetch('<?= url_to('chat_history') ?>');
                const data = await response.json();
                if (!data || data.length === 0) return;

                const ctx = document.getElementById('historyChart').getContext('2d');

                const gradient = ctx.createLinearGradient(0, 0, 0, 150);
                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

                window.mobileChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.map(i => i.time),
                        datasets: [{
                            data: data.map(i => i.value),
                            borderColor: '#3b82f6',
                            borderWidth: 3,
                            fill: true,
                            backgroundColor: gradient,
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                enabled: true,
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                titleColor: '#94a3b8',
                                bodyColor: '#fff',
                                bodyFont: { weight: 'bold' },
                                displayColors: false,
                                callbacks: {
                                    label: function (context) {
                                        return 'R$ ' + context.parsed.y.toLocaleString('pt-BR', { minimumFractionDigits: 4 });
                                    }
                                }
                            },
                            zoom: {
                                pan: {
                                    enabled: true,
                                    mode: 'x',
                                },
                                zoom: {
                                    wheel: { enabled: true },
                                    pinch: { enabled: true },
                                    mode: 'x',
                                }
                            }
                        },
                        scales: {
                            x: { display: false },
                            y: {
                                display: true,
                                position: 'right',
                                grid: { display: false },
                                ticks: {
                                    color: '#94a3b8',
                                    font: { size: 10 },
                                    callback: function (value) {
                                        return 'R$ ' + value.toFixed(4);
                                    }
                                },
                                min: Math.min(...data.map(i => i.value)) * 0.9995,
                                max: Math.max(...data.map(i => i.value)) * 1.0005
                            }
                        }
                    }
                });
            }
            initChart();

            function addMessage(text, side, showBuy = false, rate = 0, amount = 0) {
                const div = document.createElement('div');
                
                let renderedSide = side;
                let prefix = '';
                if (side === 'operator') {
                    renderedSide = 'bot';
                    prefix = isChinese ? '👤 客服人员: ' : '👤 Operador: ';
                } else if (side === 'admin') {
                    renderedSide = 'bot';
                    prefix = isChinese ? '🛡️ 管理员: ' : '🛡️ Admin: ';
                } else if (side === 'bot') {
                    prefix = '🤖 ';
                }
                
                div.className = `message ${renderedSide}`;
                div.textContent = prefix + text;
                
                if (showBuy && rate > 0) {
                    const btn = document.createElement('button');
                    btn.textContent = isChinese ? '购买 USDT' : 'Comprar USDT';
                    btn.style.cssText = "display:block; margin-top:12px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border:none; color:white; padding:12px 15px; border-radius:12px; font-weight:700; width:100%; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);";
                    btn.onclick = () => openBuyModal(rate, amount);
                    div.appendChild(document.createElement('br'));
                    div.appendChild(btn);
                }
                chatMessages.appendChild(div);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            let activePromoLotId = null;
            let activePromoRate = 0;
            let activePromoDelivery = 'D+0';
            let activePromoStock = 0;

            function openPromoBuyModal(rate, deliveryType, promoLotId, usdtAvailable) {
                activePromoLotId = promoLotId;
                activePromoRate = parseFloat(rate);
                activePromoDelivery = deliveryType || 'D+0';
                activePromoStock = parseFloat(usdtAvailable) || 0;

                const isChinese = <?= (session()->get('user_lang') === 'zh-CN') ? 'true' : 'false' ?>;

                document.getElementById('promo-modal-rate').textContent = `R$ ${activePromoRate.toLocaleString('pt-BR', { minimumFractionDigits: 4 })}`;
                document.getElementById('promo-delivery-display').textContent = activePromoDelivery.toUpperCase();
                document.getElementById('promo-stock-display').textContent = (isChinese ? '库存: ' : 'Estoque: ') + activePromoStock.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' USDT';
                document.getElementById('promo-usdt-amount').value = '';
                document.getElementById('promo-brl-result').textContent = 'R$ 0,00';
                document.getElementById('promo-usdt-amount').style.borderColor = '#334155';
                document.getElementById('promo-stock-display').style.color = '#fca5a5';
                
                showModal('buy-promo-modal');
            }

            function closePromoModal() {
                document.getElementById('buy-promo-modal').style.display = 'none';
                activePromoLotId = null;
            }

            // Update calculations on input
            document.getElementById('promo-usdt-amount').oninput = function() {
                const masked = formatUSDTMask(this.value);
                this.value = masked;

                const usdt = getCleanUSDT(masked);
                const brl = usdt * activePromoRate;
                document.getElementById('promo-brl-result').textContent = `R$ ${brl.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

                const inputEl = this;
                if (usdt > activePromoStock) {
                    inputEl.style.borderColor = '#ef4444';
                    document.getElementById('promo-stock-display').style.color = '#ef4444';
                } else {
                    inputEl.style.borderColor = '#f43f5e';
                    document.getElementById('promo-stock-display').style.color = '#fca5a5';
                }
            };

            function openBuyModal(rate, amount = 0) {
                // Se a taxa passada for significativamente diferente da global atual,
                // recalculamos o baseRate proporcionalmente para o detalhamento.
                let baseRate = currentBaseRate;

                if (currentExchangeRate > 0 && Math.abs(rate - currentExchangeRate) > 0.0001) {
                    // Estimativa para taxas vindas de mensagens antigas do bot
                    baseRate = rate / (1 + (currentFeePercent / 100));
                } else if (currentBaseRate === 0) {
                    // Caso inicial
                    baseRate = rate / (1 + (currentFeePercent / 100));
                }

                currentExchangeRate = rate;

                document.getElementById('modal-base-rate').textContent = `R$ ${baseRate.toLocaleString('pt-BR', { minimumFractionDigits: 4 })}`;
                document.getElementById('modal-rate').textContent = `R$ ${rate.toLocaleString('pt-BR', { minimumFractionDigits: 4 })}`;

                const btn = document.getElementById('confirm-buy-btn');
                if (quotationFlow === 'operator') {
                    btn.textContent = isChinese ? '发送给操作员' : 'Enviar ao Operador';
                    btn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                } else {
                    btn.textContent = '<?= lang('App.confirm_buy') ?>';
                    btn.style.background = 'linear-gradient(135deg, #3b82f6, #1d4ed8)';
                }
                btn.style.opacity = '1';
                btn.disabled = false;

                const input = currentInputMode === 'brl' ? document.getElementById('brl-amount') : document.getElementById('usdt-amount');
                if (amount > 0) {
                    let rawVal = currentInputMode === 'brl' ? (amount * rate) : amount;
                    let cleanDigits = parseFloat(rawVal).toFixed(2).replace('.', '');
                    input.value = currentInputMode === 'brl' ? formatBRLMask(cleanDigits) : formatUSDTMask(cleanDigits);
                } else {
                    input.value = '';
                }
                input.dispatchEvent(new Event('input'));
                showModal('buy-modal');
            }

            function closeModal() { 
                document.getElementById('buy-modal').style.display = 'none'; 
            }
            function closeSuccessModal() { document.getElementById('success-modal').style.display = 'none'; }

            document.getElementById('usdt-amount').oninput = function() {
                const masked = formatUSDTMask(this.value);
                this.value = masked;
                updateResultDisplay();
            };
            document.getElementById('brl-amount').oninput = function() {
                const masked = formatBRLMask(this.value);
                this.value = masked;
                updateResultDisplay();
            };

            document.querySelectorAll('.delivery-option').forEach(opt => {
                opt.onclick = () => {
                    if (opt.classList.contains('disabled')) return;
                    document.querySelectorAll('.delivery-option').forEach(o => o.classList.remove('active'));
                    opt.classList.add('active');
                    selectedDeliveryType = opt.dataset.value;
                    document.getElementById('conversion-info').style.display = 'block';
                    document.getElementById('quote-info').style.display = selectedDeliveryType === 'D+0' ? 'none' : 'block';
                    updateLiveRate();
                };
            });

            document.getElementById('confirm-buy-btn').onclick = async function () {
                let amountUsdt, amountBrl;
                if (currentInputMode === 'brl') {
                    amountBrl = getCleanBRL(document.getElementById('brl-amount').value) || 0;
                    amountUsdt = currentExchangeRate > 0 ? amountBrl / currentExchangeRate : 0;
                } else {
                    amountUsdt = getCleanUSDT(document.getElementById('usdt-amount').value) || 0;
                    amountBrl = amountUsdt * currentExchangeRate;
                }

                if (amountUsdt <= 0 || amountBrl <= 0) {
                    alert(isChinese ? '请输入有效金额' : 'Por favor, insira um valor válido');
                    return;
                }

                if (!activePromoLotId && amountUsdt < 5000) {
                    alert(isChinese ? '最低购买金额为 5000 USDT' : 'O valor mínimo de compra é 5.000 USDT');
                    return;
                }

                const btn = this;
                btn.disabled = true;
                btn.textContent = isChinese ? '处理中...' : 'Processando...';
                btn.style.opacity = '0.7';

                const selectedWallet = document.getElementById('wallet-selector')?.value || '';
                try {
                    const response = await fetch('<?= url_to('chat_buy') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({
                            amount_usdt: amountUsdt,
                            amount_brl: amountBrl,
                            delivery_type: selectedDeliveryType,
                            input_mode: currentInputMode,
                            promo_lot_id: activePromoLotId,
                            wallet_address: selectedWallet
                        })
                    });

                    const data = await response.json();

                    if (data.status === 'success') {
                        closeModal();

                        if (quotationFlow === 'operator' && operatorWhatsapp) {
                            const messageText = `Olá! Acabei de gerar uma solicitação de compra de USDT na plataforma:\n\n` +
                                                `• *Valor:* ${amountUsdt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} USDT\n` +
                                                `• *Cotação:* R$ ${currentExchangeRate.toLocaleString('pt-BR', { minimumFractionDigits: 4, maximumFractionDigits: 4 })}\n` +
                                                `• *Total:* R$ ${amountBrl.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}\n` +
                                                `• *Prazo:* ${selectedDeliveryType}\n` +
                                                `• *Transação ID:* #${data.transaction_id}\n` +
                                                `• *Minha Carteira:* ${selectedWallet}\n\n` +
                                                `Pode prosseguir com a minha operação?`;
                            const encodedText = encodeURIComponent(messageText);
                            window.open(`https://wa.me/${operatorWhatsapp}?text=${encodedText}`, '_blank');
                        }
                        
                        document.getElementById('proof-transaction-id').value = data.transaction_id;
                        showModal('success-modal');
                    } else if (response.status !== 401) {
                        alert(data.error || data.message || (isChinese ? '发生错误' : 'Erro ao processar'));
                    }
                } catch (e) {
                    if (!_sessionExpiredHandled) {
                        alert(isChinese ? '网络错误' : 'Erro de conexão');
                    }
                } finally {
                    btn.disabled = false;
                    btn.textContent = quotationFlow === 'operator' ? (isChinese ? '发送给操作员' : 'Enviar ao Operador') : '<?= lang('App.confirm_buy') ?>';
                    btn.style.opacity = '1';
                }
            };

            document.getElementById('confirm-promo-buy-btn').onclick = async function() {
                const usdtAmount = getCleanUSDT(document.getElementById('promo-usdt-amount').value) || 0;
                const brlAmount = usdtAmount * activePromoRate;

                if (usdtAmount <= 0) {
                    alert(isChinese ? '请输入有效金额' : 'Por favor, insira um valor válido');
                    return;
                }

                if (usdtAmount > activePromoStock) {
                    alert(isChinese ? '输入金额超出可用促销库存' : 'O valor inserido é superior ao estoque disponível da promoção');
                    return;
                }

                const btn = this;
                btn.disabled = true;
                btn.textContent = isChinese ? '处理中...' : 'Processando...';
                btn.style.opacity = '0.7';

                const selectedWallet = document.getElementById('promo-wallet-selector')?.value || '';
                try {
                    const response = await fetch('<?= url_to('chat_buy') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({
                            amount_usdt: usdtAmount,
                            amount_brl: brlAmount,
                            delivery_type: activePromoDelivery,
                            input_mode: 'usdt',
                            promo_lot_id: activePromoLotId,
                            wallet_address: selectedWallet
                        })
                    });

                    const data = await response.json();

                    if (data.status === 'success') {
                        closePromoModal();

                        if (quotationFlow === 'operator' && operatorWhatsapp) {
                            const messageText = `Olá! Acabei de gerar uma solicitação de compra de USDT na plataforma (Lote Promocional):\n\n` +
                                                `• *Valor:* ${usdtAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} USDT\n` +
                                                `• *Cotação:* R$ ${activePromoRate.toLocaleString('pt-BR', { minimumFractionDigits: 4, maximumFractionDigits: 4 })}\n` +
                                                `• *Total:* R$ ${brlAmount.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}\n` +
                                                `• *Prazo:* ${activePromoDelivery}\n` +
                                                `• *Transação ID:* #${data.transaction_id}\n` +
                                                `• *Minha Carteira:* ${selectedWallet}\n\n` +
                                                `Pode prosseguir com a minha operação?`;
                            const encodedText = encodeURIComponent(messageText);
                            window.open(`https://wa.me/${operatorWhatsapp}?text=${encodedText}`, '_blank');
                        }
                        
                        document.getElementById('proof-transaction-id').value = data.transaction_id;
                        showModal('success-modal');
                    } else if (response.status !== 401) {
                        alert(data.error || data.message || (isChinese ? '发生错误' : 'Erro ao processar'));
                    }
                } catch(e) {
                    if (!_sessionExpiredHandled) {
                        alert(isChinese ? '网络错误' : 'Erro de conexão');
                    }
                } finally {
                    btn.disabled = false;
                    btn.textContent = 'Confirmar Compra Promocional';
                    btn.style.opacity = '1';
                }
            };

            let isSendingMessage = false;
            chatForm.onsubmit = async (e) => {
                e.preventDefault();
                const message = userInput.value.trim();
                if (!message || isSendingMessage) return;
                
                isSendingMessage = true;
                addMessage(message, 'user');
                userInput.value = '';
                typingIndicator.style.display = 'block';
                chatMessages.scrollTop = chatMessages.scrollHeight;
                
                try {
                    const response = await fetch('<?= url_to('chat_send') ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCsrfToken() },
                        body: JSON.stringify({ message: message })
                    });
                    
                    await loadChatHistory();
                } catch (err) {
                    console.error(err);
                } finally {
                    isSendingMessage = false;
                    typingIndicator.style.display = 'none';
                }
            };

            // ── Notificações (Móbile) ──────────────────────────────────────────
            let notifLoading = false;
            let lastSeenNotifKey = localStorage.getItem('last_read_notification') || '';
            let lastAlertedNotifKey = '';

            window.openNotificationsModal = function() {
                showModal('notifications-modal');
                fetchNotifications(true);
            };

            window.closeNotificationsModal = function() {
                const modal = document.getElementById('notifications-modal');
                if (modal) modal.style.display = 'none';
            };

            window.closeAlertNotifModal = function() {
                const modal = document.getElementById('alert-notification-modal');
                if (modal) modal.style.display = 'none';
            };

            function triggerAlertModal(item) {
                const isChinese = <?= $isChinese ? 'true' : 'false' ?>;
                let title = '';
                let description = '';
                let color = '#3b82f6';
                let iconBg = 'rgba(59, 130, 246, 0.1)';
                
                const hasAmount = item.amount !== null && item.amount !== undefined && !isNaN(parseFloat(item.amount));
                const amount = hasAmount ? parseFloat(item.amount).toLocaleString(item.unit === 'USDT' ? 'en-US' : 'pt-BR', { minimumFractionDigits:2, maximumFractionDigits:2 }) : '';
                const amountStr = hasAmount ? (item.unit === 'USDT' ? `${amount} USDT` : `R$ ${amount}`) : '';

                if (item.operation_type === 'adjustment_add' || (item.operation_type === 'deposit' && item.description.includes('Ajuste'))) {
                    title = isChinese ? '账户信用额度增加' : 'Ajuste de Crédito';
                    description = isChinese 
                        ? `管理员已向您的账户添加了 ${amountStr}。备注: ${item.description || ''}`
                        : `O administrador adicionou ${amountStr} ao seu saldo. Obs: ${item.description || ''}`;
                    color = '#22c55e';
                    iconBg = 'rgba(34, 197, 94, 0.1)';
                } else if (item.operation_type === 'adjustment_subtract') {
                    title = isChinese ? '账户扣款调整' : 'Ajuste de Débito';
                    description = isChinese 
                        ? `管理员已从您的账户中扣除了 ${amountStr}。备注: ${item.description || ''}`
                        : `O administrador removeu ${amountStr} do seu saldo. Obs: ${item.description || ''}`;
                    color = '#ef4444';
                    iconBg = 'rgba(239, 68, 68, 0.1)';
                } else if (item.operation_type === 'withdrawal') {
                    title = isChinese ? 'USDT 已发送交割' : 'USDT Enviado';
                    description = isChinese 
                        ? `已成功向您的钱包发送交割 ${amountStr}。操单 #${item.contract_id || ''}`
                        : `Foi enviado ${amountStr} para sua carteira. Operação #${item.contract_id || ''}`;
                    color = '#a78bfa';
                    iconBg = 'rgba(167, 139, 250, 0.1)';
                } else if (item.operation_type === 'deposit_pending') {
                    title = isChinese ? '充值申请审核中' : 'Depósito em Análise';
                    description = isChinese 
                        ? (hasAmount ? `收到您的 ${amountStr} 充值申请，正在等待核对。` : '您的充值申请已收到，正在等待核对。')
                        : (hasAmount ? `Seu depósito de ${amountStr} foi recebido e está aguardando verificação.` : 'Seu depósito foi recebido e está aguardando verificação.');
                    color = '#fbbf24';
                    iconBg = 'rgba(251, 191, 36, 0.1)';
                } else if (item.operation_type === 'deposit_rejected') {
                    title = isChinese ? '充值已被拒绝' : 'Depósito Rejeitado';
                    description = isChinese 
                        ? `您的 ${amountStr} 充值已被拒绝。原因: ${item.rejection_reason || ''}`
                        : `Seu depósito de ${amountStr} foi rejeitado. Motivo: ${item.rejection_reason || ''}`;
                    color = '#ef4444';
                    iconBg = 'rgba(239, 68, 68, 0.1)';
                } else if (item.operation_type === 'deposit') {
                    title = isChinese ? '充值已被批准' : 'Depósito Confirmado';
                    description = isChinese 
                        ? `您的 ${amountStr} 充值已被批准，已记入您的余额。`
                        : `Seu depósito de ${amountStr} foi verificado e aprovado com sucesso!`;
                    color = '#22c55e';
                    iconBg = 'rgba(34, 197, 94, 0.1)';
                } else if (item.operation_type === 'buy') {
                    title = isChinese ? '新操单已启动' : 'Operação Iniciada';
                    description = isChinese 
                        ? `您的操单已批准，价值 ${amountStr}。`
                        : `Sua operação no valor de ${amountStr} foi iniciada com sucesso.`;
                    color = '#3b82f6';
                    iconBg = 'rgba(59, 130, 246, 0.1)';
                } else if (item.operation_type === 'interest') {
                    title = isChinese ? '产生逾期利息' : 'Juros Aplicados';
                    description = isChinese 
                        ? `操单 #${item.contract_id || ''} 产生了 ${amountStr} 的逾期费。`
                        : `Foram aplicados juros de ${amountStr} na Operação #${item.contract_id || ''}.`;
                    color = '#f97316';
                    iconBg = 'rgba(249, 115, 22, 0.1)';
                } else {
                    title = isChinese ? '账户活动更新' : 'Movimentação';
                    description = isChinese 
                        ? `${item.description || ''} (${amountStr})`
                        : `${item.description || ''} (${amountStr})`;
                    color = '#94a3b8';
                    iconBg = 'rgba(148, 163, 184, 0.1)';
                }

                document.getElementById('alert-notif-title').textContent = title;
                document.getElementById('alert-notif-message').textContent = description;
                
                const iconContainer = document.getElementById('alert-notif-icon-container');
                if (iconContainer) {
                    iconContainer.style.background = iconBg;
                    iconContainer.style.color = color;
                }
                
                showModal('alert-notification-modal');
            }

            async function checkNotifications() {
                try {
                    const res = await fetch(`<?= url_to('chat_notifications') ?>`);
                    const data = await res.json();
                    const items = data.data || [];
                    if (items.length > 0) {
                        const newest = items[0];
                        const newestKey = `${newest.operation_type}_${newest.id}_${newest.transaction_date}`;
                        
                        if (lastAlertedNotifKey && lastAlertedNotifKey !== newestKey) {
                            let newItems = [];
                            for (let i = 0; i < items.length; i++) {
                                const key = `${items[i].operation_type}_${items[i].id}_${items[i].transaction_date}`;
                                if (key === lastAlertedNotifKey) {
                                    break;
                                }
                                newItems.push(items[i]);
                            }
                            if (newItems.length > 0) {
                                triggerAlertModal(newItems[0]);
                                if (typeof updateDebtBalance === 'function') {
                                    updateDebtBalance();
                                }
                            }
                        }
                        
                        lastAlertedNotifKey = newestKey;

                        let unseenCount = 0;
                        if (lastSeenNotifKey) {
                            for (let i = 0; i < items.length; i++) {
                                const key = `${items[i].operation_type}_${items[i].id}_${items[i].transaction_date}`;
                                if (key === lastSeenNotifKey) {
                                    break;
                                }
                                unseenCount++;
                            }
                        } else {
                            unseenCount = items.length;
                        }

                        const updateBadge = (id) => {
                            const badge = document.getElementById(id);
                            if (badge) {
                                if (unseenCount > 0) {
                                    badge.textContent = unseenCount;
                                    badge.style.display = 'flex';
                                } else {
                                    badge.style.display = 'none';
                                }
                            }
                        };
                        updateBadge('notifications-badge');
                        updateBadge('notifications-badge-desktop');
                    }
                } catch(e) {
                    console.error('Error checking notifications', e);
                }
            }

            async function fetchNotifications(markAsRead = false) {
                if (notifLoading) return;
                notifLoading = true;
                const loader = document.getElementById('notifications-loader');
                const empty = document.getElementById('notifications-empty');
                if (loader) loader.style.display = 'block';
                if (empty) empty.style.display = 'none';
                try {
                    const res = await fetch(`<?= url_to('chat_notifications') ?>`);
                    const data = await res.json();
                    const items = data.data || [];
                    renderNotificationItems(items);
                    
                    if (items.length === 0 && empty) {
                        empty.style.display = 'block';
                    }

                    if (markAsRead && items.length > 0) {
                        const newest = items[0];
                        const newestKey = `${newest.operation_type}_${newest.id}_${newest.transaction_date}`;
                        localStorage.setItem('last_read_notification', newestKey);
                        lastSeenNotifKey = newestKey;
                        const badge = document.getElementById('notifications-badge');
                        if (badge) badge.style.display = 'none';
                        const badgeDesktop = document.getElementById('notifications-badge-desktop');
                        if (badgeDesktop) badgeDesktop.style.display = 'none';
                    }
                } catch(e) {
                    console.error('Notifications fetch error', e);
                } finally {
                    notifLoading = false;
                    if (loader) loader.style.display = 'none';
                }
            }

            function renderNotificationItems(items) {
                const list = document.getElementById('notifications-list');
                if (!list) return;
                list.innerHTML = '';
                const isChinese = <?= $isChinese ? 'true' : 'false' ?>;
                
                items.forEach(item => {
                    let title = '';
                    let description = '';
                    let color = '#3b82f6';
                    let iconBg = 'rgba(59, 130, 246, 0.1)';
                    
                    const hasAmount = item.amount !== null && item.amount !== undefined && !isNaN(parseFloat(item.amount));
                    const amount = hasAmount ? parseFloat(item.amount).toLocaleString(item.unit === 'USDT' ? 'en-US' : 'pt-BR', { minimumFractionDigits:2, maximumFractionDigits:2 }) : '';
                    const amountStr = hasAmount ? (item.unit === 'USDT' ? `${amount} USDT` : `R$ ${amount}`) : '';

                    if (item.operation_type === 'adjustment_add' || (item.operation_type === 'deposit' && item.description.includes('Ajuste'))) {
                        title = isChinese ? '账户信用额度增加' : 'Ajuste de Crédito';
                        description = isChinese 
                            ? `管理员已向您的账户添加了 ${amountStr}。备注: ${item.description || ''}`
                            : `O administrador adicionou ${amountStr} ao seu saldo. Obs: ${item.description || ''}`;
                        color = '#22c55e';
                        iconBg = 'rgba(34, 197, 94, 0.1)';
                    } else if (item.operation_type === 'adjustment_subtract') {
                        title = isChinese ? '账户扣款调整' : 'Ajuste de Débito';
                        description = isChinese 
                            ? `管理员已从您的账户中扣除了 ${amountStr}。备注: ${item.description || ''}`
                            : `O administrador removeu ${amountStr} do seu saldo. Obs: ${item.description || ''}`;
                        color = '#ef4444';
                        iconBg = 'rgba(239, 68, 68, 0.1)';
                    } else if (item.operation_type === 'withdrawal') {
                        title = isChinese ? 'USDT 已发送交割' : 'USDT Enviado';
                        description = isChinese 
                            ? `已成功向您的钱包发送交割 ${amountStr}。操单 #${item.contract_id || ''}`
                            : `Foi enviado ${amountStr} para sua carteira. Operação #${item.contract_id || ''}`;
                        color = '#a78bfa';
                        iconBg = 'rgba(167, 139, 250, 0.1)';
                    } else if (item.operation_type === 'deposit_pending') {
                        title = isChinese ? '充值申请审核中' : 'Depósito em Análise';
                        description = isChinese 
                            ? (hasAmount ? `收到您的 ${amountStr} 充值申请，正在等待核对。` : '您的充值申请已收到，正在等待核对。')
                            : (hasAmount ? `Seu depósito de ${amountStr} foi recebido e está aguardando verificação.` : 'Seu depósito foi recebido e está aguardando verificação.');
                        color = '#fbbf24';
                        iconBg = 'rgba(251, 191, 36, 0.1)';
                    } else if (item.operation_type === 'deposit_rejected') {
                        title = isChinese ? '充值已被拒绝' : 'Depósito Rejeitado';
                        description = isChinese 
                            ? `您的 ${amountStr} 充值已被拒绝。原因: ${item.rejection_reason || ''}`
                            : `Seu depósito de ${amountStr} foi rejeitado. Motivo: ${item.rejection_reason || ''}`;
                        color = '#ef4444';
                        iconBg = 'rgba(239, 68, 68, 0.1)';
                    } else if (item.operation_type === 'deposit') {
                        title = isChinese ? '充值已被批准' : 'Depósito Confirmado';
                        description = isChinese 
                            ? `您的 ${amountStr} 充值已被批准，已记入您的余额。`
                            : `Seu depósito de ${amountStr} foi verificado e aprovado com sucesso!`;
                        color = '#22c55e';
                        iconBg = 'rgba(34, 197, 94, 0.1)';
                    } else if (item.operation_type === 'buy') {
                        title = isChinese ? '新操单已启动' : 'Operação Iniciada';
                        description = isChinese 
                            ? `您的操单已批准，价值 ${amountStr}。`
                            : `Sua operação no valor de ${amountStr} foi iniciada com sucesso.`;
                        color = '#3b82f6';
                        iconBg = 'rgba(59, 130, 246, 0.1)';
                    } else if (item.operation_type === 'interest') {
                        title = isChinese ? '产生逾期利息' : 'Juros Aplicados';
                        description = isChinese 
                            ? `操单 #${item.contract_id || ''} 产生了 ${amountStr} 的逾期费。`
                            : `Foram aplicados juros de ${amountStr} na Operação #${item.contract_id || ''}.`;
                        color = '#f97316';
                        iconBg = 'rgba(249, 115, 22, 0.1)';
                    } else {
                        title = isChinese ? '账户活动更新' : 'Movimentação';
                        description = isChinese 
                            ? `${item.description || ''} (${amountStr})`
                            : `${item.description || ''} (${amountStr})`;
                        color = '#94a3b8';
                        iconBg = 'rgba(148, 163, 184, 0.1)';
                    }

                    const d = new Date(item.transaction_date.replace(' ', 'T'));
                    const dateStr = d.toLocaleDateString(isChinese ? 'zh-CN' : 'pt-BR', { day:'2-digit', month:'short' })
                                   + ' ' + d.toLocaleTimeString(isChinese ? 'zh-CN' : 'pt-BR', { hour:'2-digit', minute:'2-digit' });

                    const el = document.createElement('div');
                    el.style.cssText = 'display:flex;align-items:flex-start;gap:12px;background:rgba(15,23,42,0.4);border:1px solid rgba(255,255,255,0.03);border-radius:16px;padding:12px 14px;';
                    el.innerHTML = `
                        <div style="display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:${iconBg};color:${color};flex-shrink:0;margin-top:2px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                            </svg>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:3px;">
                                <span style="font-size:13px;font-weight:700;color:white;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1;">${title}</span>
                                <span style="font-size:10px;color:#475569;white-space:nowrap;">${dateStr}</span>
                            </div>
                            <p style="font-size:11px;color:#94a3b8;line-height:1.4;">${description}</p>
                        </div>
                    `;
                    list.appendChild(el);
                });
            }

            // ── Statement (Extrato) ──────────────────────────────────────────
            let stmtPage = 1, stmtTotal = 0, stmtLoading = false;
            const stmtPerPage = 20;
            let stmtNature = '', stmtSearch = '', stmtSearchTimer = null;

            const stmtLang = {
                empty:                '<?= lang('App.stmt_empty') ?>',
                loading:              '<?= lang('App.loading') ?>',
                operationRef:         '<?= lang('App.stmt_operation_ref') ?>',
                deposit:              '<?= lang('App.stmt_op_deposit') ?>',
                withdrawal:           '<?= lang('App.stmt_op_withdrawal') ?>',
                margin_lock:          '<?= lang('App.stmt_op_margin_lock') ?>',
                limit_release:        '<?= lang('App.stmt_op_limit_release') ?>',
                partial_amortization: '<?= lang('App.stmt_op_partial_amortization') ?>',
                full_settlement:      '<?= lang('App.stmt_op_full_settlement') ?>',
                late_fee:             '<?= lang('App.stmt_op_late_fee') ?>',
                adjustment_add:       '<?= lang('App.stmt_op_adjustment_add') ?>',
                adjustment_subtract:  '<?= lang('App.stmt_op_adjustment_subtract') ?>',
                deposit_pending:      '<?= lang('App.stmt_op_deposit_pending') ?>',
                deposit_rejected:     '<?= lang('App.stmt_op_deposit_rejected') ?>',
                rejectionReason:      '<?= lang('App.stmt_rejection_reason') ?>',
                amountUnidentified:   '<?= lang('App.stmt_amount_unidentified') ?>',
                amountProcessing:     '<?= lang('App.stmt_amount_processing') ?>',
                amountEditedReason:   '<?= lang('App.stmt_amount_edited_reason') ?>',
                usdtLabel:            '<?= lang('App.stmt_usdt_label') ?>',
                spotLabel:            '<?= lang('App.stmt_spot_label') ?>',
                hashLabel:            '<?= lang('App.stmt_hash_label') ?>',
                pagePrev:             '<?= lang('App.stmt_page_prev') ?>',
                pageNext:             '<?= lang('App.stmt_page_next') ?>',
            };

            function openStatementModal() {
                showModal('statement-modal');
                document.getElementById('statement-search').value = '';
                document.getElementById('statement-start-date').value = '';
                document.getElementById('statement-end-date').value = '';
                document.getElementById('statement-type').value = '';
                document.getElementById('statement-status').value = '';
                stmtNature = '';
                stmtSearch = '';
                document.querySelectorAll('.stmt-filter-btn').forEach(b => applyStmtFilterStyle(b, b.dataset.nature === ''));
                resetStatement();
                loadStatementPosition();
            }

            function closeStatementModal() {
                document.getElementById('statement-modal').style.display = 'none';
            }

            function resetStatement() {
                stmtPage = 1;
                stmtTotal = 0;
                stmtLoading = false;
                document.getElementById('statement-list').innerHTML = '';
                document.getElementById('statement-pagination').innerHTML = '';
                fetchStatement();
            }

            async function fetchStatement() {
                if (stmtLoading) return;
                stmtLoading = true;
                document.getElementById('statement-loader').style.display = 'block';
                try {
                    const params = new URLSearchParams({
                        page: stmtPage,
                        nature: stmtNature,
                        q: stmtSearch,
                        start_date: document.getElementById('statement-start-date').value,
                        end_date: document.getElementById('statement-end-date').value,
                        type: document.getElementById('statement-type').value,
                        status: document.getElementById('statement-status').value
                    });
                    const res  = await fetch(`<?= url_to('chat_statement') ?>?${params}`);
                    const data = await res.json();
                    stmtTotal = data.total;
                    renderStmtItems(data.data);
                    renderStmtPagination();
                    document.getElementById('statement-list').scrollTop = 0;
                } catch(e) {
                    console.error('Statement fetch error', e);
                } finally {
                    stmtLoading = false;
                    document.getElementById('statement-loader').style.display = 'none';
                }
            }

            function downloadStatement(format) {
                const params = new URLSearchParams({
                    nature: stmtNature,
                    q: stmtSearch,
                    start_date: document.getElementById('statement-start-date').value,
                    end_date: document.getElementById('statement-end-date').value,
                    type: document.getElementById('statement-type').value,
                    status: document.getElementById('statement-status').value
                });
                
                let baseUrl = format === 'pdf' ? "<?= url_to('chat_statement_pdf') ?>" : "<?= url_to('chat_statement_xlsx') ?>";
                window.open(`${baseUrl}?${params.toString()}`, '_blank');
            }

            function toggleAdvancedFilters() {
                const container = document.getElementById('statement-advanced-filters');
                const chevron = document.getElementById('stmt-filters-chevron');
                if (container.style.display === 'none' || container.style.display === '') {
                    container.style.display = 'flex';
                    chevron.style.transform = 'rotate(180deg)';
                } else {
                    container.style.display = 'none';
                    chevron.style.transform = 'rotate(0deg)';
                }
            }

            function renderStmtItems(items) {
                const list = document.getElementById('statement-list');
                list.innerHTML = '';
                if (items.length === 0) {
                    list.innerHTML = `<div style="text-align:center;color:#64748b;padding:40px 0;font-size:14px;">${stmtLang.empty}</div>`;
                    return;
                }
                items.forEach(item => {
                    const isPendingDeposit  = item.operation_type === 'deposit_pending';
                    const isRejectedDeposit = item.operation_type === 'deposit_rejected';
                    // Entrega de USDT é um débito no ledger da empresa, mas para o
                    // cliente é um recebimento — exibir como crédito (verde).
                    const isCredit = item.nature === 'C' || item.operation_type === 'withdrawal';

                    // Depósitos pendentes/rejeitados são informativos: sem sinal (não afetam o saldo)
                    let color, sign;
                    if (isPendingDeposit)       { color = '#fbbf24'; sign = ''; }
                    else if (isRejectedDeposit) { color = '#f87171'; sign = ''; }
                    else                        { color = isCredit ? '#22c55e' : '#f87171'; sign = isCredit ? '+ ' : '− '; }

                    const label    = stmtLang[item.operation_type] || item.operation_type;
                    const d        = new Date(item.transaction_date.replace(' ', 'T'));
                    const dateStr  = d.toLocaleDateString('pt-BR', { day:'2-digit', month:'short', year:'numeric' })
                                   + ' · ' + d.toLocaleTimeString('pt-BR', { hour:'2-digit', minute:'2-digit' });
                    let amountStr;
                    if (item.amount === null) {
                        amountStr = item.ocr_status === 'processing' ? stmtLang.amountProcessing : stmtLang.amountUnidentified;
                    } else {
                        const amount = parseFloat(item.amount).toLocaleString(item.unit === 'USDT' ? 'en-US' : 'pt-BR', { minimumFractionDigits:2, maximumFractionDigits:2 });
                        amountStr = item.unit === 'USDT' ? `${amount} USDT` : `R$ ${amount}`;
                    }

                    let marginLockDetails = '';
                    if (item.operation_type === 'margin_lock') {
                        const parts = [];
                        if (item.usdt_amount != null) {
                            const usdtVal = parseFloat(item.usdt_amount).toLocaleString('en-US', { minimumFractionDigits:2, maximumFractionDigits:2 });
                            parts.push(`<div>${stmtLang.usdtLabel}: ${usdtVal} USDT</div>`);
                        }
                        if (item.spot_rate != null) {
                            const spotVal = parseFloat(item.spot_rate).toLocaleString('pt-BR', { minimumFractionDigits:4, maximumFractionDigits:4 });
                            parts.push(`<div>${stmtLang.spotLabel}: R$ ${spotVal}</div>`);
                        }
                        if (item.purchase_hash) {
                            parts.push(`<div style="font-family:monospace;word-break:break-all;">${stmtLang.hashLabel}: ${item.purchase_hash}</div>`);
                        }
                        if (parts.length) {
                            marginLockDetails = `<div style="font-size:11px;color:#94a3b8;background:rgba(15,23,42,0.4);border-radius:8px;padding:8px 10px;margin-top:6px;display:flex;flex-direction:column;gap:3px;">${parts.join('')}</div>`;
                        }
                    } else if (item.operation_type === 'withdrawal' && item.notes) {
                        marginLockDetails = `<div style="font-size:11px;color:#94a3b8;background:rgba(15,23,42,0.4);border-radius:8px;padding:8px 10px;margin-top:6px;">`
                            + `<div style="font-family:monospace;word-break:break-all;">${stmtLang.hashLabel}: ${item.notes}</div></div>`;
                    }

                    const el = document.createElement('div');
                    el.style.cssText = 'display:flex;flex-direction:column;padding:14px 0;border-bottom:1px solid rgba(51,65,85,0.4);';
                    el.innerHTML = `
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:14px;font-weight:600;color:${isRejectedDeposit ? '#f87171' : (isPendingDeposit ? '#fbbf24' : 'white')};margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${label}</div>
                                <div style="font-size:11px;color:#64748b;">${dateStr}</div>
                                ${item.description ? `<div style="font-size:11px;color:#475569;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.description}</div>` : ''}
                                ${item.rejection_reason ? `<div style="font-size:11px;color:#f87171;margin-top:2px;">${stmtLang.rejectionReason}: ${item.rejection_reason}</div>` : ''}
                                ${item.amount_edited_reason ? `<div style="font-size:11px;color:#fbbf24;margin-top:2px;">${stmtLang.amountEditedReason}: ${item.amount_edited_reason}</div>` : ''}
                            </div>
                            <div style="text-align:right;flex-shrink:0;">
                                <div style="font-size:15px;font-weight:700;color:${color};white-space:nowrap;${isRejectedDeposit ? 'text-decoration:line-through;opacity:0.7;' : ''}">${sign}${amountStr}</div>
                                ${item.contract_id ? `<div style="font-size:10px;color:#475569;margin-top:2px;">${stmtLang.operationRef} #${item.contract_id}</div>` : ''}
                            </div>
                        </div>
                        ${marginLockDetails}`;
                    list.appendChild(el);
                });
            }

            function renderStmtPagination() {
                const container = document.getElementById('statement-pagination');
                container.innerHTML = '';
                const totalPages = Math.ceil(stmtTotal / stmtPerPage) || 1;
                if (totalPages <= 1) return;

                const btnStyle = (active, disabled) =>
                    `background:${active ? '#ffffff' : 'rgba(15,23,42,0.5)'};border:1px solid ${active ? '#ffffff' : '#334155'};` +
                    `color:${active ? '#0f172a' : '#e2e8f0'};padding:5px 10px;border-radius:8px;font-size:12px;` +
                    `font-weight:${active ? '700' : '500'};cursor:${disabled ? 'default' : 'pointer'};opacity:${disabled ? '0.4' : '1'};`;

                const makeBtn = (text, page, active, disabled) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.innerText = text;
                    btn.style.cssText = btnStyle(active, disabled);
                    btn.disabled = disabled;
                    if (!disabled) btn.onclick = () => { stmtPage = page; fetchStatement(); };
                    return btn;
                };

                container.appendChild(makeBtn(stmtLang.pagePrev, stmtPage - 1, false, stmtPage === 1));

                // Janela de até 5 páginas em volta da atual, com reticências nas pontas
                const windowSize = 5;
                let start = Math.max(1, stmtPage - Math.floor(windowSize / 2));
                let end   = Math.min(totalPages, start + windowSize - 1);
                start     = Math.max(1, end - windowSize + 1);

                if (start > 1) {
                    container.appendChild(makeBtn('1', 1, stmtPage === 1, false));
                    if (start > 2) {
                        const dots = document.createElement('span');
                        dots.innerText = '…';
                        dots.style.cssText = 'color:#64748b;font-size:12px;padding:0 2px;';
                        container.appendChild(dots);
                    }
                }
                for (let p = start; p <= end; p++) {
                    container.appendChild(makeBtn(String(p), p, p === stmtPage, false));
                }
                if (end < totalPages) {
                    if (end < totalPages - 1) {
                        const dots = document.createElement('span');
                        dots.innerText = '…';
                        dots.style.cssText = 'color:#64748b;font-size:12px;padding:0 2px;';
                        container.appendChild(dots);
                    }
                    container.appendChild(makeBtn(String(totalPages), totalPages, stmtPage === totalPages, false));
                }

                container.appendChild(makeBtn(stmtLang.pageNext, stmtPage + 1, false, stmtPage === totalPages));
            }

            function setStatementFilter(nature) {
                stmtNature = nature;
                document.querySelectorAll('.stmt-filter-btn').forEach(b => applyStmtFilterStyle(b, b.dataset.nature === nature));
                resetStatement();
            }

            function applyStmtFilterStyle(btn, active) {
                btn.style.background   = active ? 'rgba(59,130,246,0.1)' : 'rgba(15,23,42,0.5)';
                btn.style.borderColor  = active ? 'rgba(59,130,246,0.2)' : '#334155';
                btn.style.color        = active ? '#3b82f6' : '#94a3b8';
            }

            function onStatementSearch(value) {
                clearTimeout(stmtSearchTimer);
                stmtSearchTimer = setTimeout(() => { stmtSearch = value; resetStatement(); }, 350);
            }


            async function checkPromotionalLots() {
                try {
                    const response = await fetch('<?= url_to('chat_promotional_lots') ?>');
                    const lots = await response.json();
                    activePromotionalLots = lots || [];
                    
                    const containerDesktop = document.getElementById('promotional-lots-desktop');
                    const containerMobile = document.getElementById('promotional-lots-mobile');
                    
                    if (!lots || lots.length === 0) {
                        if (containerDesktop) containerDesktop.style.display = 'none';
                        if (containerMobile) containerMobile.style.display = 'none';
                        return;
                    }
                    
                    let html = '';
                    const isChinese = <?= $isChinese ? 'true' : 'false' ?>;
                    lots.forEach(lot => {
                        const rate = parseFloat(lot.conversion_rate).toLocaleString('pt-BR', { minimumFractionDigits: 4, maximumFractionDigits: 4 });
                        const available = parseFloat(lot.usdt_available).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        const deliveryStr = lot.delivery_type ? ` [${lot.delivery_type.toUpperCase()}]` : '';
                        
                        html += `
                            <div onclick="openPromoBuyModal(${lot.conversion_rate}, '${lot.delivery_type || ''}', ${lot.id}, ${lot.usdt_available})"
                                 onmouseover="this.style.background='rgba(239, 68, 68, 0.12)'; this.style.transform='scale(1.01)';"
                                 onmouseout="this.style.background='rgba(239, 68, 68, 0.08)'; this.style.transform='scale(1.00)';"
                                 style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); padding: 12px 16px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; cursor: pointer; transition: background 0.2s, transform 0.2s;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="color: #ef4444; display: flex; align-items: center; justify-content: center; background: rgba(239, 68, 68, 0.1); width: 32px; height: 32px; border-radius: 50%;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p style="font-size: 13px; font-weight: 700; color: white; margin: 0;">Lote Promocional!</p>
                                        <p style="font-size: 11px; color: #fca5a5; margin: 2px 0 0 0;">Taxa: R$ ${rate}${deliveryStr}</p>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <p style="font-size: 11px; font-weight: 700; color: #fca5a5; margin: 0;">Estoque</p>
                                    <p style="font-size: 13px; font-weight: 800; color: white; margin: 2px 0 0 0;">${available} USDT</p>
                                </div>
                            </div>
                        `;
                    });
                    
                    if (containerDesktop) {
                        containerDesktop.innerHTML = html;
                        containerDesktop.style.display = 'block';
                    }
                    if (containerMobile) {
                        containerMobile.innerHTML = html;
                        containerMobile.style.display = 'block';
                    }
                    
                    let alerted = JSON.parse(localStorage.getItem('alerted_promo_lots') || '[]');
                    let newLotToAlert = null;
                    
                    lots.forEach(lot => {
                        if (!alerted.includes(lot.id)) {
                            newLotToAlert = lot;
                        }
                    });
                    
                    if (newLotToAlert) {
                        alerted.push(newLotToAlert.id);
                        localStorage.setItem('alerted_promo_lots', JSON.stringify(alerted));
                        
                        const rate = parseFloat(newLotToAlert.conversion_rate).toLocaleString('pt-BR', { minimumFractionDigits: 4, maximumFractionDigits: 4 });
                        const available = parseFloat(newLotToAlert.usdt_available).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        const deliveryStr = newLotToAlert.delivery_type ? ` [${newLotToAlert.delivery_type.toUpperCase()}]` : '';

                        document.getElementById('alert-notif-title').textContent = isChinese ? '新促销活动可用！' : 'Novo Lote Promocional!';
                        document.getElementById('alert-notif-message').textContent = isChinese
                            ? `管理员已发布特价 USDT。价格: R$ ${rate}，库存: ${available} USDT。`
                            : `O administrador liberou uma taxa especial de USDT. Taxa: R$ ${rate}${deliveryStr}, Estoque: ${available} USDT.`;
                        
                        const iconContainer = document.getElementById('alert-notif-icon-container');
                        if (iconContainer) {
                            iconContainer.style.background = 'rgba(239, 68, 68, 0.1)';
                            iconContainer.style.color = '#ef4444';
                        }
                        
                        showModal('alert-notification-modal');
                    }
                } catch(e) {
                    console.error('Error checking promotional lots', e);
                }
            }

            // Initialization calls at the bottom
            updateLiveRate();
            updateDebtBalance();
            loadChatHistory();
            initChart();
            initContractsBadge();
            checkNotifications();
            checkPromotionalLots();

            setInterval(updateLiveRate, 1000); // 1s interval for fast real-time updates
            setInterval(updateDebtBalance, 30000);
            setInterval(checkNotifications, 30000);
            setInterval(checkPromotionalLots, 5000);
            setInterval(loadChatHistory, 3000); // Poll chat messages every 3 seconds

        let debtsData = null;
        let activeDebtsFilter = 'todos';

        const activeTabStyle   = 'flex-shrink:0;padding:9px 14px;border-radius:10px;background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.35);color:#a78bfa;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;';
        const inactiveTabStyle = 'flex-shrink:0;padding:9px 14px;border-radius:10px;background:rgba(15,23,42,0.5);border:1px solid #334155;color:#94a3b8;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;';

        const filterButtons = ['todos', 'd0', 'd1', 'd2', 'atrasado'];

        function setContractsFilter(filter) {
            activeDebtsFilter = filter;
            filterButtons.forEach(f => {
                const btn = document.getElementById('cf-btn-' + f);
                if (btn) btn.style.cssText = f === filter ? activeTabStyle : inactiveTabStyle;
            });
            if (debtsData) renderDebts(debtsData[filter]);
        }

        function renderDebts(summary) {
            const loading = document.getElementById('debts-loading');
            const section = document.getElementById('debts-summary');
            const empty   = document.getElementById('contracts-empty');
            const fmt = v => v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            loading.style.display = 'none';

            if (!summary || summary.count === 0) {
                section.style.display = 'none';
                empty.style.display = 'block';
                return;
            }

            empty.style.display = 'none';
            section.style.display = 'block';

            document.getElementById('debts-brl').textContent   = 'R$ ' + fmt(summary.total_brl_owed);
            document.getElementById('debts-usdt').textContent  = fmt(summary.total_usdt_owed);
            document.getElementById('debts-count').textContent = summary.count + (summary.count === 1 ? ' operação em aberto' : ' operações em aberto');
        }

        async function fetchContracts() {
            const loading = document.getElementById('debts-loading');
            const section = document.getElementById('debts-summary');
            const empty   = document.getElementById('contracts-empty');

            loading.style.display = 'block';
            section.style.display = 'none';
            empty.style.display   = 'none';

            try {
                const response = await fetch('<?= url_to('chat_my_debts') ?>');
                debtsData = await response.json();

                // Update header badge using geral count
                const count = debtsData.geral?.count ?? 0;
                const updateBadge = (id) => {
                    const badge = document.getElementById(id);
                    if (badge) {
                        if (count > 0) {
                            badge.textContent = count;
                            badge.style.display = 'flex';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                };
                updateBadge('contracts-badge');
                updateBadge('contracts-badge-desktop');

                renderDebts(debtsData[activeDebtsFilter]);
            } catch (e) {
                loading.textContent = 'Erro ao carregar dados.';
            }
        }

        async function loadStatementPosition() {
            try {
                if (!debtsData) {
                    const response = await fetch('<?= url_to('chat_my_debts') ?>');
                    debtsData = await response.json();
                }
                const fmt = v => v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const fmtUsdt = v => v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('stmt-pos-brl').textContent  = 'R$ ' + fmt(debtsData.todos.total_brl_owed);
                document.getElementById('stmt-pos-usdt').textContent = fmtUsdt(debtsData.todos.total_usdt_owed) + ' USDT';
            } catch (e) {}
        }

        async function initContractsBadge() {
            try {
                const response = await fetch('<?= url_to('chat_my_debts') ?>');
                const data = await response.json();
                const count = data.geral?.count ?? 0;
                const updateBadge = (id) => {
                    const badge = document.getElementById(id);
                    if (badge && count > 0) {
                        badge.textContent = count;
                        badge.style.display = 'flex';
                    }
                };
                updateBadge('contracts-badge');
                updateBadge('contracts-badge-desktop');
            } catch (e) {}
        }

        function openProofModal() {
            showModal('proof-modal');
        }

        function closeProofModal() {
            document.getElementById('proof-modal').style.display = 'none';
            document.getElementById('proof-file').value = '';
            document.getElementById('proof-text').value = '';
            document.getElementById('file-name').textContent = isChinese ? '点击此处选择文件' : 'Clique aqui para selecionar';
            document.getElementById('upload-btn').style.display = 'none';
        }

        function handleFileSelect(input) {
            if (input.files && input.files[0]) {
                document.getElementById('file-name').textContent = input.files[0].name;
                document.getElementById('upload-btn').style.display = 'block';
            }
        }

        async function uploadProof() {
            const file = document.getElementById('proof-file').files[0];
            const transactionId = document.getElementById('proof-transaction-id').value;
            const proofText = document.getElementById('proof-text').value;
            
            if (!file) return;

            const formData = new FormData();
            formData.append('proof', file);
            formData.append('transaction_id', transactionId);
            formData.append('proof_text', proofText);

            const uploadBtn = document.getElementById('upload-btn');
            uploadBtn.disabled = true;
            uploadBtn.textContent = isChinese ? '正在上传...' : 'Enviando...';

            try {
                const response = await fetch('<?= url_to('upload_proof') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: formData
                });
                const data = await response.json();
                if (data.status === 'success') {
                    alert(data.message);
                    closeProofModal();
                    loadTransactions(true); // Recarregar lista
                } else {
                    alert(data.error || 'Erro no upload');
                }
            } catch (e) {
                alert('Erro de conexão');
            } finally {
                uploadBtn.disabled = false;
                uploadBtn.textContent = isChinese ? '开始上传' : 'Iniciar Upload';
            }
        }

        let depositItemCounter = 0;

        function addDepositItemField(file) {
            depositItemCounter++;
            const container = document.getElementById('deposit-items-list');
            if (!container) return;

            const itemDiv = document.createElement('div');
            itemDiv.id = 'deposit-item-' + depositItemCounter;
            itemDiv.className = 'deposit-item-card';
            itemDiv.style.cssText = 'background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255,255,255,0.04); border-radius: 16px; padding: 16px; position: relative; display: flex; flex-direction: column; gap: 12px; margin-bottom: 4px; text-align: left;';

            const isChinese = <?= $isChinese ? 'true' : 'false' ?>;
            const removeBtn = `<button type="button" onclick="removeDepositItemField(${depositItemCounter})" style="position: absolute; right: 12px; top: 12px; background: rgba(239, 68, 68, 0.15); border: none; color: #ef4444; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; cursor: pointer; line-height: 1;">&times;</button>`;

            itemDiv.innerHTML = `
                ${removeBtn}
                <div style="font-size: 12px; font-weight: 700; color: #10b981; text-transform: uppercase;">
                    ${isChinese ? '存款 #' : 'Depósito #'} ${depositItemCounter}
                </div>

                <div>
                    <label style="display: block; color: #94a3b8; font-size: 11px; text-transform: uppercase; font-weight: 600; margin-bottom: 6px;">${isChinese ? '付款凭证' : 'Comprovante'} *</label>
                    <input type="file" id="dep-item-proof-${depositItemCounter}" class="dep-item-proof" accept="image/*,application/pdf" style="display: none;" onchange="handleDepositItemFile(this, ${depositItemCounter})">
                    <label for="dep-item-proof-${depositItemCounter}" style="display: block; padding: 12px; border: 2px dashed rgba(16,185,129,0.3); border-radius: 10px; cursor: pointer; text-align: center; box-sizing: border-box;">
                        <span id="dep-item-file-name-${depositItemCounter}" style="color: #34d399; font-weight: 500; font-size: 12px; word-break: break-all;">${isChinese ? '点击选择文件' : 'Clique para selecionar arquivo'}</span>
                    </label>
                </div>

                <div>
                    <label style="display: block; color: #94a3b8; font-size: 11px; text-transform: uppercase; font-weight: 600; margin-bottom: 6px;">${isChinese ? '备注 (可选)' : 'Observações (Opcional)'}</label>
                    <textarea class="dep-item-notes" rows="2" style="width: 100%; background: #0f172a; border: 1px solid #334155; border-radius: 10px; color: white; padding: 10px; font-size: 13px; outline: none; resize: none; box-sizing: border-box;" placeholder="${isChinese ? '输入备注...' : 'Informações adicionais...' }"></textarea>
                </div>
            `;

            container.appendChild(itemDiv);
            container.scrollTop = container.scrollHeight;

            if (file) {
                const fileInputEl = itemDiv.querySelector('.dep-item-proof');
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInputEl.files = dt.files;
                const span = itemDiv.querySelector('span[id^="dep-item-file-name-"]');
                if (span) span.textContent = file.name;
            }
        }

        function removeDepositItemField(id) {
            const el = document.getElementById('deposit-item-' + id);
            if (el) el.remove();
        }

        function handleDepositItemFile(input, id) {
            const files = input.files;
            const span = document.getElementById('dep-item-file-name-' + id);
            if (span) {
                span.textContent = files[0] ? files[0].name : (isChinese ? '点击选择文件' : 'Clique para selecionar arquivo');
            }
        }

        function addFilesAsDepositItems(fileList) {
            Array.from(fileList).forEach(file => addDepositItemField(file));
        }

        function handleBulkFileSelect(input) {
            addFilesAsDepositItems(input.files);
            input.value = '';
        }

        function handleDepositDrop(e, zone) {
            e.preventDefault();
            zone.style.borderColor = 'rgba(16,185,129,0.3)';
            zone.style.background = 'transparent';
            addFilesAsDepositItems(e.dataTransfer.files);
        }

        function openDepositModal() {
            document.getElementById('deposit-items-list').innerHTML = '';
            depositItemCounter = 0;
            showModal('deposit-modal');
        }

        function closeDepositModal() {
            document.getElementById('deposit-modal').style.display = 'none';
            document.getElementById('deposit-items-list').innerHTML = '';
            depositItemCounter = 0;
        }

        async function submitDeposit() {
            const listContainer = document.getElementById('deposit-items-list');
            const items = listContainer.getElementsByClassName('deposit-item-card');
            
            if (items.length === 0) {
                alert(isChinese ? '请添加至少一个存款项。' : 'Adicione pelo menos um item de depósito.');
                return;
            }

            const formData = new FormData();
            
            for (let i = 0; i < items.length; i++) {
                const item = items[i];
                const fileInput = item.querySelector('.dep-item-proof');
                const notesInput = item.querySelector('.dep-item-notes');

                const notes = notesInput.value.trim();
                const file = fileInput.files[0];

                if (!file) {
                    alert(isChinese ? '请上传所有付款凭证。' : 'O comprovante é obrigatório para todos os depósitos.');
                    return;
                }

                formData.append('notes[]', notes);
                formData.append('proofs[]', file);
            }

            const btn = document.getElementById('deposit-submit-btn');
            btn.disabled = true;
            btn.textContent = isChinese ? '提交中...' : 'Enviando...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch('<?= url_to('deposit_store') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        '<?= csrf_header() ?>': csrfToken
                    },
                    body: formData
                });
                const data = await response.json();
                if (data.status === 'success') {
                    closeDepositModal();
                    alert(isChinese ? '存款申请已提交，等待审核。' : 'Depósito enviado! Aguardando validação do operador.');
                } else {
                    alert(data.error || (isChinese ? '发生错误。' : 'Ocorreu um erro.'));
                }
            } catch (e) {
                alert(isChinese ? '连接错误。' : 'Erro de conexão.');
            } finally {
                btn.disabled = false;
                btn.textContent = isChinese ? '提交存款' : 'Enviar Depósito';
            }
        }

        window.openChangePasswordModal = function(e) {
            if (e) {
                if (typeof e.preventDefault === 'function') e.preventDefault();
                if (typeof e.stopPropagation === 'function') e.stopPropagation();
            }
            const alertBox = document.getElementById('pwd-alert');
            alertBox.style.display = 'none';
            alertBox.textContent = '';
            alertBox.className = '';
            document.getElementById('pwd-current').value = '';
            document.getElementById('pwd-new').value = '';
            document.getElementById('pwd-confirm').value = '';
            showModal('change-password-modal');
        };

        window.closeChangePasswordModal = function() {
            document.getElementById('change-password-modal').style.display = 'none';
        };

        window.submitChangePassword = async function(e) {
            if (e) e.preventDefault();
            const alertBox = document.getElementById('pwd-alert');
            alertBox.style.display = 'none';
            alertBox.textContent = '';
            alertBox.className = '';

            const currentPassword = document.getElementById('pwd-current').value;
            const newPassword     = document.getElementById('pwd-new').value;
            const confirmPassword = document.getElementById('pwd-confirm').value;

            if (newPassword.length < 6) {
                alertBox.textContent = isChinese ? '新密码必须至少包含 6 个字符。' : 'A nova senha deve ter pelo menos 6 caracteres.';
                alertBox.className = 'alert alert-error';
                alertBox.style.display = 'block';
                return;
            }

            if (newPassword !== confirmPassword) {
                alertBox.textContent = isChinese ? '新密码和确认密码不匹配。' : 'A nova senha e a confirmação não coincidem.';
                alertBox.className = 'alert alert-error';
                alertBox.style.display = 'block';
                return;
            }

            const formData = new FormData();
            formData.append('current_password', currentPassword);
            formData.append('new_password', newPassword);
            formData.append('confirm_password', confirmPassword);
            
            // Get CSRF Token
            const csrfToken = getCsrfToken();

            try {
                const response = await fetch('<?= url_to('update_password') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        '<?= csrf_header() ?>': csrfToken
                    },
                    body: formData
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    alertBox.textContent = isChinese ? '密码修改成功！' : 'Senha alterada com sucesso!';
                    alertBox.className = 'alert alert-success';
                    alertBox.style.display = 'block';
                    document.getElementById('pwd-current').value = '';
                    document.getElementById('pwd-new').value = '';
                    document.getElementById('pwd-confirm').value = '';
                } else {
                    alertBox.textContent = data.error || (isChinese ? '密码更新失败。' : 'Erro ao atualizar a senha.');
                    alertBox.className = 'alert alert-error';
                    alertBox.style.display = 'block';
                }
            } catch (err) {
                alertBox.textContent = isChinese ? '发生错误。' : 'Ocorreu um erro ao processar a requisição.';
                alertBox.className = 'alert alert-error';
                alertBox.style.display = 'block';
            }
        };

    </script>
</body>

</html>