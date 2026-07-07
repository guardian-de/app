<?php
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
    <title>Evo Mobile</title>
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
            font-family: 'Outfit', sans-serif;
            color: #f8fafc;
        }

        .mobile-wrapper {
            display: flex;
            flex-direction: column;
            height: 100%;
            width: 100vw;
            background: radial-gradient(circle at top right, #1e3a8a33, transparent);
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
        <header class="mobile-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="profile-badge"><?= strtoupper(substr($firstName, 0, 1)) ?></div>
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

        <!-- Saldo -->
        <div style="background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); padding: 10px 20px; border-bottom: 1px solid rgba(59, 130, 246, 0.2);">
            <p style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
                Saldo
            </p>
            <div id="balance-badge"
                style="padding: 6px 12px; border-radius: 6px; font-size: 20px; font-weight: 700; background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); color: #4ade80; display: inline-block;">
                R$ 0,00
            </div>
        </div>

        <!-- Botão Comprar + Depositar (Acima do Gráfico) -->
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

        <div class="chart-section" id="chart-section">
            <div class="chart-info">
                <span><?= lang('App.real_time_tendency') ?></span>
            </div>
            <canvas id="historyChart"></canvas>
            <button class="toggle-chart-btn" onclick="toggleChart()">
                <span id="toggle-text"><?= lang('App.min') ?></span>
            </button>
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

    <!-- Modals -->
    <!-- Buy Modal -->
    <div id="buy-modal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 5001; justify-content: center; align-items: center; padding: 20px;">
        <div
            style="width: 100%; max-width: 450px; position: relative; background: #1e293b; padding: 30px; border-radius: 16px; font-family: sans-serif;">
            <button onclick="closeModal()"
                style="position: absolute; right: 20px; top: 20px; background: none; border: none; color: #94a3b8; font-size: 24px; cursor: pointer;">&times;</button>

            <div style="text-align: center; margin-bottom: 25px;">
                <h1 style="font-size: 32px; font-weight: 700; margin-bottom: 8px; color: #a78bfa;">
                    <?= lang('App.buy') ?>
                </h1>
                <p style="color: #94a3b8; font-size: 14px; font-weight: 500;">Valor em BRL</p>
            </div>

            <div style="margin-bottom: 20px;">
                <label
                    style="display: block; color: #94a3b8; font-size: 13px; font-weight: 500; margin-bottom: 8px;">Endereço
                    da Carteira USDT (TRC-20)</label>
                <div
                    style="background: rgba(15, 23, 42, 0.5); padding: 12px; border-radius: 8px; border: 1px dashed #334155; font-family: monospace; font-size: 13px; color: #818cf8; word-break: break-all; line-height: 1.4;">
                    <?= session()->get('user_wallet') ?: ($isChinese ? '未注册' : 'Não cadastrada') ?>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="usdt-amount"
                    style="display: block; color: #94a3b8; font-size: 13px; font-weight: 500; margin-bottom: 8px;">Valor
                    em USDT (USDT)</label>
                <input type="number" id="usdt-amount" placeholder="Ex: 5000" step="0.01" min="5000"
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
                        if ($allowed == 'all' || $allowed == $opt):
                    ?>
                        <div class="delivery-option <?= $first ? 'active' : '' ?>" data-value="<?= $opt ?>"><?= $opt ?></div>
                        <?php if ($first) { $first = false; $active_val = $opt; } ?>
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
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: #94a3b8; font-size: 13px; font-weight: 500;"><?= lang('App.fee') ?> (<span
                                id="modal-fee-percent">0</span>%):</span>
                        <span id="modal-fee-value"
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
                        style="display: flex; justify-content: space-between; align-items: center; background: rgba(99, 102, 241, 0.1); padding: 12px; border-radius: 8px; border: 1px solid rgba(99, 102, 241, 0.2);">
                        <span
                            style="color: #94a3b8; font-size: 14px; font-weight: 700;"><?= lang('App.total_brl') ?>:</span>
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
                <p style="color: #94a3b8; font-size: 12px; margin-bottom: 20px;"><?= $isChinese ? '填写金额并上传付款凭证。' : 'Informe o valor e envie o comprovante de pagamento.' ?></p>
            </div>

            <!-- Scrollable list of items -->
            <div id="deposit-items-list" style="overflow-y: auto; flex: 1; min-height: 0; display: flex; flex-direction: column; gap: 16px; padding-right: 6px; margin-bottom: 16px;"></div>

            <div style="flex-shrink: 0;">
                <!-- Add Another Deposit Button -->
                <button type="button" onclick="addDepositItemField()"
                    style="width: 100%; padding: 12px; margin-bottom: 16px; background: rgba(255,255,255,0.03); border: 1px dashed rgba(16,185,129,0.4); border-radius: 12px; color: #34d399; font-weight: 600; font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                    + <?= $isChinese ? '添加另一个存款' : 'Adicionar outro depósito' ?>
                </button>

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
            <div style="position: relative; margin-bottom: 16px; flex-shrink: 0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round"
                    style="position:absolute;left:12px;top:50%;transform:translateY(-50%);">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" id="statement-search" placeholder="<?= lang('App.stmt_search_placeholder') ?>"
                    oninput="onStatementSearch(this.value)"
                    style="width:100%;background:rgba(15,23,42,0.6);border:1px solid #334155;border-radius:12px;color:white;padding:10px 12px 10px 36px;font-size:14px;outline:none;box-sizing:border-box;">
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
            let selectedDeliveryType = '<?= $active_val ?>';
            const quotationFlow = '<?= $quotation_flow ?>';
            const operatorWhatsapp = '<?= $operator_whatsapp ?>';

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
                    const response = await fetch('<?= url_to('chat_rate') ?>?delivery_type=' + encodeURIComponent(selectedDeliveryType));
                    const data = await response.json();
                    // Mostrar cotação SEM taxas no header
                    document.getElementById('live-rate-val').textContent = `R$ ${parseFloat(data.base_rate).toLocaleString('pt-BR', { minimumFractionDigits: 4, maximumFractionDigits: 4 })}`;
                    currentExchangeRate = parseFloat(data.rate);
                    currentBaseRate = parseFloat(data.base_rate);
                    currentFeePercent = parseFloat(data.fee_percent);

                    if (document.getElementById('buy-modal') && document.getElementById('buy-modal').style.display === 'flex') {
                        document.getElementById('modal-base-rate').textContent = `R$ ${currentBaseRate.toLocaleString('pt-BR', { minimumFractionDigits: 4 })}`;
                        document.getElementById('modal-fee-percent').textContent = currentFeePercent.toFixed(2);
                        document.getElementById('modal-fee-value').textContent = `R$ ${(currentExchangeRate - currentBaseRate).toLocaleString('pt-BR', { minimumFractionDigits: 4 })}`;
                        document.getElementById('modal-rate').textContent = `R$ ${currentExchangeRate.toLocaleString('pt-BR', { minimumFractionDigits: 4 })}`;
                        
                        const usdtInput = document.getElementById('usdt-amount');
                        const usdt = parseFloat(usdtInput.value) || 0;
                        const brl = usdt * currentExchangeRate;
                        document.getElementById('brl-result').textContent = `R$ ${brl.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
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
                        if (!badge) return;
                        const value = data.balance.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        const isNeg = data.balance < 0;
                        badge.textContent = `R$ ${value}`;
                        badge.style.background = isNeg ? 'rgba(239,68,68,0.1)' : 'rgba(34,197,94,0.1)';
                        badge.style.border = isNeg ? '1px solid rgba(239,68,68,0.2)' : '1px solid rgba(34,197,94,0.2)';
                        badge.style.color = isNeg ? '#f87171' : '#4ade80';
                    }
                } catch (e) { console.error("Erro ao atualizar saldo"); }
            }

            // Initialization calls moved to the bottom

            async function loadChatHistory() {
                try {
                    const response = await fetch('<?= url_to('chat_messages_history') ?>');
                    const data = await response.json();
                    data.forEach(msg => {
                        addMessage(msg.message, msg.sender, msg.show_buy == 1, parseFloat(msg.rate), parseFloat(msg.suggested_amount));
                    });
                } catch (e) { }
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
                div.className = `message ${side}`;
                div.textContent = text;
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

            function openBuyModal(rate, amount = 0) {
                // Se a taxa passada for significativamente diferente da global atual,
                // recalculamos o baseRate proporcionalmente para o detalhamento.
                let baseRate = currentBaseRate;
                let feePercent = currentFeePercent;

                if (currentExchangeRate > 0 && Math.abs(rate - currentExchangeRate) > 0.0001) {
                    // Estimativa para taxas vindas de mensagens antigas do bot
                    baseRate = rate / (1 + (currentFeePercent / 100));
                } else if (currentBaseRate === 0) {
                    // Caso inicial
                    baseRate = rate / (1 + (currentFeePercent / 100));
                }

                currentExchangeRate = rate;

                document.getElementById('modal-base-rate').textContent = `R$ ${baseRate.toLocaleString('pt-BR', { minimumFractionDigits: 4 })}`;
                document.getElementById('modal-fee-percent').textContent = feePercent.toFixed(2);
                document.getElementById('modal-fee-value').textContent = `R$ ${(rate - baseRate).toLocaleString('pt-BR', { minimumFractionDigits: 4 })}`;
                document.getElementById('modal-rate').textContent = `R$ ${rate.toLocaleString('pt-BR', { minimumFractionDigits: 4 })}`;

                const btn = document.getElementById('confirm-buy-btn');
                const resultLabel = document.getElementById('result-label');
                if (quotationFlow === 'operator') {
                    btn.textContent = isChinese ? '发送给操作员' : 'Enviar ao Operador';
                    btn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                } else {
                    btn.textContent = '<?= lang('App.confirm_buy') ?>';
                    btn.style.background = 'linear-gradient(135deg, #3b82f6, #1d4ed8)';
                }
                btn.style.opacity = '1';
                btn.disabled = false;

                const input = document.getElementById('usdt-amount');
                input.value = amount > 0 ? amount : '';
                input.dispatchEvent(new Event('input'));
                showModal('buy-modal');
            }

            function closeModal() { document.getElementById('buy-modal').style.display = 'none'; }
            function closeSuccessModal() { document.getElementById('success-modal').style.display = 'none'; }

            document.getElementById('usdt-amount').oninput = (e) => {
                const usdt = parseFloat(e.target.value) || 0;
                const brl = usdt * currentExchangeRate;
                document.getElementById('brl-result').textContent = `R$ ${brl.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            };

            document.querySelectorAll('.delivery-option').forEach(opt => {
                opt.onclick = () => {
                    document.querySelectorAll('.delivery-option').forEach(o => o.classList.remove('active'));
                    opt.classList.add('active');
                    selectedDeliveryType = opt.dataset.value;
                    document.getElementById('conversion-info').style.display = 'block';
                    document.getElementById('quote-info').style.display = selectedDeliveryType === 'D+0' ? 'none' : 'block';
                    updateLiveRate();
                };
            });

            document.getElementById('confirm-buy-btn').onclick = async function () {
                const amountUsdt = parseFloat(document.getElementById('usdt-amount').value) || 0;

                if (amountUsdt <= 0) {
                    alert(isChinese ? '请输入有效金额' : 'Por favor, insira um valor válido');
                    return;
                }

                if (amountUsdt < 5000) {
                    alert(isChinese ? '最低购买金额为 5000 USDT' : 'O valor mínimo de compra é 5.000 USDT');
                    return;
                }

                const btn = this;
                btn.disabled = true;
                btn.textContent = isChinese ? '处理中...' : 'Processando...';
                btn.style.opacity = '0.7';

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
                            delivery_type: selectedDeliveryType
                        })
                    });

                    const data = await response.json();

                    if (data.status === 'success') {
                        closeModal();
                        
                        if (quotationFlow === 'operator' && operatorWhatsapp) {
                            const amountBrl = amountUsdt * currentExchangeRate;
                            const messageText = `Olá! Acabei de gerar uma solicitação de compra de USDT na plataforma:\n\n` +
                                                `• *Valor:* ${amountUsdt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} USDT\n` +
                                                `• *Cotação:* R$ ${currentExchangeRate.toLocaleString('pt-BR', { minimumFractionDigits: 4, maximumFractionDigits: 4 })}\n` +
                                                `• *Total:* R$ ${amountBrl.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}\n` +
                                                `• *Prazo:* ${selectedDeliveryType}\n` +
                                                `• *Transação ID:* #${data.transaction_id}\n` +
                                                `• *Minha Carteira:* <?= esc(session()->get('user_wallet') ?: '') ?>\n\n` +
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

            chatForm.onsubmit = async (e) => {
                e.preventDefault();
                const message = userInput.value.trim();
                if (!message) return;
                addMessage(message, 'user');
                userInput.value = '';
                typingIndicator.style.display = 'block';
                chatMessages.scrollTop = chatMessages.scrollHeight;
                const response = await fetch('<?= url_to('chat_send') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCsrfToken() },
                    body: JSON.stringify({ message: message })
                });
                const data = await response.json();
                typingIndicator.style.display = 'none';
                if (data.reply) addMessage(data.reply, 'bot', data.showBuy, data.currentRate, data.suggestedAmount);
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
                
                const amount = parseFloat(item.amount).toLocaleString(isChinese ? 'en-US' : 'pt-BR', { minimumFractionDigits:2, maximumFractionDigits:2 });
                const amountStr = item.unit === 'USDT' ? `${amount} USDT` : `R$ ${amount}`;

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
                        ? `收到您的 ${amountStr} 充值申请，正在等待核对。`
                        : `Seu depósito de ${amountStr} foi recebido e está aguardando verificação.`;
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

                        const badge = document.getElementById('notifications-badge');
                        if (badge) {
                            if (unseenCount > 0) {
                                badge.textContent = unseenCount;
                                badge.style.display = 'flex';
                            } else {
                                badge.style.display = 'none';
                            }
                        }
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
                    
                    const amount = parseFloat(item.amount).toLocaleString(isChinese ? 'en-US' : 'pt-BR', { minimumFractionDigits:2, maximumFractionDigits:2 });
                    const amountStr = item.unit === 'USDT' ? `${amount} USDT` : `R$ ${amount}`;

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
                            ? `收到您的 ${amountStr} 充值申请，正在等待核对。`
                            : `Seu depósito de ${amountStr} foi recebido e está aguardando verificação.`;
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
                usdtLabel:            '<?= lang('App.stmt_usdt_label') ?>',
                spotLabel:            '<?= lang('App.stmt_spot_label') ?>',
                hashLabel:            '<?= lang('App.stmt_hash_label') ?>',
                pagePrev:             '<?= lang('App.stmt_page_prev') ?>',
                pageNext:             '<?= lang('App.stmt_page_next') ?>',
            };

            function openStatementModal() {
                showModal('statement-modal');
                document.getElementById('statement-search').value = '';
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
                    const params = new URLSearchParams({ page: stmtPage, nature: stmtNature, q: stmtSearch });
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
                    const amount   = parseFloat(item.amount).toLocaleString('pt-BR', { minimumFractionDigits:2, maximumFractionDigits:2 });
                    const amountStr = item.unit === 'USDT' ? `${amount} USDT` : `R$ ${amount}`;

                    let marginLockDetails = '';
                    if (item.operation_type === 'margin_lock') {
                        const parts = [];
                        if (item.usdt_amount != null) {
                            const usdtVal = parseFloat(item.usdt_amount).toLocaleString('pt-BR', { minimumFractionDigits:2, maximumFractionDigits:2 });
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


            // Initialization calls at the bottom
            updateLiveRate();
            updateDebtBalance();
            loadChatHistory();
            initChart();
            initContractsBadge();
            checkNotifications();

            setInterval(updateLiveRate, 1000); // 1s interval for fast real-time updates
            setInterval(updateDebtBalance, 30000);
            setInterval(checkNotifications, 30000);

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
                const badge = document.getElementById('contracts-badge');
                if (badge) {
                    const count = debtsData.geral?.count ?? 0;
                    if (count > 0) {
                        badge.textContent = count;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                }

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
                document.getElementById('stmt-pos-brl').textContent  = 'R$ ' + fmt(debtsData.todos.total_brl_owed);
                document.getElementById('stmt-pos-usdt').textContent = fmt(debtsData.todos.total_usdt_owed) + ' USDT';
            } catch (e) {}
        }

        async function initContractsBadge() {
            try {
                const response = await fetch('<?= url_to('chat_my_debts') ?>');
                const data = await response.json();
                const badge = document.getElementById('contracts-badge');
                const count = data.geral?.count ?? 0;
                if (badge && count > 0) {
                    badge.textContent = count;
                    badge.style.display = 'flex';
                }
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

        function addDepositItemField() {
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
                ${depositItemCounter > 1 ? removeBtn : ''}
                <div style="font-size: 12px; font-weight: 700; color: #10b981; text-transform: uppercase;">
                    ${isChinese ? '存款 #' : 'Depósito #'} ${depositItemCounter}
                </div>
                
                <div>
                    <label style="display: block; color: #94a3b8; font-size: 11px; text-transform: uppercase; font-weight: 600; margin-bottom: 6px;">${isChinese ? '金额 (BRL)' : 'Valor (BRL)'} *</label>
                    <input type="number" class="dep-item-amount" step="0.01" min="0.01" required
                        style="width: 100%; background: #0f172a; border: 1px solid #334155; border-radius: 10px; color: white; padding: 10px 12px; font-size: 14px; outline: none; box-sizing: border-box;"
                        placeholder="Ex: 5000.00">
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

        function openDepositModal() {
            document.getElementById('deposit-items-list').innerHTML = '';
            depositItemCounter = 0;
            addDepositItemField(); // start with one
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
                const amountInput = item.querySelector('.dep-item-amount');
                const fileInput = item.querySelector('.dep-item-proof');
                const notesInput = item.querySelector('.dep-item-notes');
                
                const amount = amountInput.value.trim();
                const notes = notesInput.value.trim();
                const file = fileInput.files[0];
                
                if (!amount || parseFloat(amount) <= 0) {
                    alert(isChinese ? '请输入有效金额。' : 'Informe um valor válido.');
                    amountInput.focus();
                    return;
                }
                if (!file) {
                    alert(isChinese ? '请上传所有付款凭证。' : 'O comprovante é obrigatório para todos os depósitos.');
                    return;
                }
                
                formData.append('amounts[]', amount);
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

    </script>
</body>

</html>