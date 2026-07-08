<?php
/** @var array $user @var array $business_hours @var string $quotation_flow @var string $operator_whatsapp */
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php $isChinese = session()->get('user_lang') === 'zh-CN'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardian Chat | Dashboard</title>
    <link rel="stylesheet" href="<?= base_url('css/auth.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            align-items: flex-start;
            padding-top: 40px;
            background: #0f172a;
        }

        .chat-layout {
            display: flex;
            width: 100%;
            max-width: 1200px;
            height: 85vh;
            margin: 0 auto;
            gap: 20px;
        }

        .sidebar {
            width: 260px;
            background: #1e293b;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #334155;
            display: flex;
            flex-direction: column;
        }

        .chat-main {
            flex: 1;
            background: #1e293b;
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            border: 1px solid #334155;
            overflow: hidden;
        }

        .chat-header {
            padding: 20px;
            border-bottom: 1px solid #334155;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .message {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 15px;
            line-height: 1.5;
            position: relative;
        }

        .message.user {
            align-self: flex-end;
            background: #6366f1;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.bot {
            align-self: flex-start;
            background: #334155;
            color: #f8fafc;
            border-bottom-left-radius: 4px;
        }

        .message .delete-msg-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #ef4444;
            font-size: 14px;
            cursor: pointer;
            padding: 4px;
            opacity: 0.15;
            transition: opacity 0.2s, transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .message.bot .delete-msg-btn {
            right: -32px;
        }
        .message.user .delete-msg-btn {
            left: -32px;
        }
        .message:hover .delete-msg-btn,
        .message:active .delete-msg-btn,
        .delete-msg-btn:hover {
            opacity: 0.95;
            transform: translateY(-50%) scale(1.15);
        }

        .chat-input-area {
            padding: 20px;
            border-top: 1px solid #334155;
            display: flex;
            gap: 12px;
        }

        .chat-input-area input {
            flex: 1;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 12px 16px;
            color: white;
            outline: none;
        }

        .chat-input-area input:focus {
            border-color: #6366f1;
        }

        .send-btn {
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0 20px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .send-btn:hover {
            opacity: 0.9;
        }

        .user-info {
            margin-bottom: 20px;
        }

        .user-info h3 {
            font-size: 16px;
            margin-bottom: 4px;
        }

        .user-info p {
            font-size: 12px;
            color: #94a3b8;
        }

        .logout-link {
            margin-top: auto;
            color: #ef4444;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .typing {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
            display: none;
        }

        .delivery-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .delivery-option {
            flex: 1;
            padding: 10px;
            text-align: center;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid #334155;
            border-radius: 8px;
            color: #94a3b8;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .delivery-option.active {
            background: rgba(99, 102, 241, 0.2);
            border-color: #6366f1;
            color: white;
            font-weight: 600;
        }

        .mobile-stats {
            display: none;
        }

        @media (max-width: 768px) {
            body {
                padding: 0;
            }

            .chat-layout {
                flex-direction: column;
                height: 100vh;
                gap: 0;
                border-radius: 0;
            }

            .sidebar {
                display: none;
                /* Oculta a sidebar grande no mobile */
            }

            .chat-main {
                flex: 1;
                border-radius: 0;
                border: none;
                height: 100vh;
            }

            .chat-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px;
            }

            .mobile-stats {
                display: flex;
                width: 100%;
                gap: 10px;
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px solid rgba(255, 255, 255, 0.05);
            }

            .mobile-stats>div {
                flex: 1;
            }
        }
    </style>
</head>

<body>
    <div class="chat-layout">
        <div class="sidebar">
            <div
                style="text-align: center; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #334155;">
                <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;">
                    <?= lang('App.live_rate') ?>
                </div>
                <div id="live-rate-badge" style="font-size: 20px; font-weight: 700; color: #22c55e;">R$ 0,0000</div>
            </div>

            <div class="user-profile">
                <div class="user-avatar"><?= substr(session()->get('user_name'), 0, 1) ?></div>
                <h3><?= explode(' ', session()->get('user_name'))[0] ?></h3>
            </div>
            <div style="flex: 1; overflow-y: auto;">
                <p
                    style="font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
                    <?= lang('App.wallet_address') ?>
                </p>
                <div
                    style="background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; color: #818cf8; padding: 10px; border-radius: 4px; margin-bottom: 24px; font-size: 11px; font-family: monospace; word-break: break-all;">
                    <?= session()->get('user_wallet') ?: (session()->get('user_lang') == 'zh-CN' ? '未提供' : 'Não informada') ?>
                </div>

                <div style="margin-bottom: 24px;">
                    <p style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                        Saldo
                    </p>
                    <div id="balance-badge"
                        style="padding: 8px; border-radius: 4px; font-size: 13px; font-weight: 600; background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); color: #4ade80;">
                        0,00
                    </div>
                </div>

                <p
                    style="font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
                    <?= lang('App.real_time_tendency') ?>
                </p>
                <div style="height: 150px; width: 100%; margin-bottom: 24px;">
                    <canvas id="historyChart"></canvas>
                </div>

                <p
                    style="font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
                    Idioma
                </p>
                <select id="language-selector"
                    style="width: 100%; background: #0f172a; border: 1px solid #334155; color: white; padding: 8px; border-radius: 4px; margin-bottom: 24px; outline: none;">
                    <option value="pt-BR" <?= session()->get('user_lang') == 'pt-BR' ? 'selected' : '' ?>>🇧🇷 Português
                        (Brasil)</option>
                    <option value="zh-CN" <?= session()->get('user_lang') == 'zh-CN' ? 'selected' : '' ?>>🇨🇳 中文 (Chinês)
                    </option>
                </select>

                <a href="javascript:void(0)" onclick="openDepositModal()"
                    style="display: block; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; padding: 10px; border-radius: 4px; font-size: 13px; text-decoration: none; text-align: center; transition: all 0.2s; margin-top: 8px; font-weight: 600;">
                    <?= $isChinese ? '存款' : 'Depositar' ?>
                </a>

            </div>
            <?php if (session()->get('user_role') === 'admin'): ?>
                <a href="<?= url_to('admin_users') ?>"
                    style="display: block; color: #6366f1; text-decoration: none; font-size: 14px; margin-top: 10px; font-weight: 600;">
                    <?= session()->get('user_lang') == 'zh-CN' ? '管理面板' : 'Painel Administrativo' ?>
                </a>
            <?php endif; ?>
            <a href="<?= url_to('logout') ?>"
                style="display: block; color: #ef4444; text-decoration: none; font-size: 14px; margin-top: 20px; font-weight: 500;">
                <?= lang('App.logout') ?>
            </a>
        </div>

        <div class="chat-main">
            <div class="chat-header">
                <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                    <div>
                        <h2 style="font-size: 18px; font-weight: 600;">GUARDIAN IA</h2>
                        <div style="font-size: 12px; color: #94a3b8;">GUARDIAN IA Assistente</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <button type="button" onclick="clearChatMessages()" style="background: none; border: none; color: #ef4444; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;" title="<?= $isChinese ? '清除聊天记录' : 'Limpar Histórico' ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                            <span><?= $isChinese ? '清除历史' : 'Limpar Histórico' ?></span>
                        </button>
                    </div>
                </div>

                <!-- Estatísticas Mobile -->
                <div class="mobile-stats">
                    <div>
                        <p style="font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
                            Saldo
                        </p>
                        <div id="mobile-balance-badge"
                            style="padding: 6px; border-radius: 4px; font-size: 12px; font-weight: 600; background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); color: #4ade80;">
                            0,00
                        </div>
                    </div>
                </div>
            </div>

            <div class="chat-messages" id="chat-messages">
                <div class="message bot">
                    <?= lang('App.welcome_msg', [
                        'name' => explode(' ', session()->get('user_name'))[0],
                        'start' => $business_hours['start'],
                        'end' => $business_hours['end']
                    ]) ?>
                </div>
            </div>

            <div id="typing" class="typing" style="padding-left: 20px; padding-bottom: 10px;">
                GUARDIAN IA...
            </div>

            <form class="chat-input-area" id="chat-form">
                <input type="text" id="user-input" placeholder="<?= lang('App.chat_placeholder') ?>" autocomplete="off">
                <button type="submit"
                    class="send-btn"><?= session()->get('user_lang') == 'zh-CN' ? '发送' : 'Enviar' ?></button>
            </form>
        </div>
    </div>

    <div id="buy-modal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center; padding: 20px;">
        <div class="auth-container"
            style="max-width: 450px; position: relative; background: #1e293b; padding: 30px; border-radius: 16px;">
            <button onclick="closeModal()"
                style="position: absolute; right: 20px; top: 20px; background: none; border: none; color: #94a3b8; font-size: 24px; cursor: pointer;">&times;</button>
            <?php
                $purchaseModel = $user['purchase_model'] ?? 'usdt';
                $initialMode = $purchaseModel === 'both' ? ($user['last_purchase_mode'] ?: 'usdt') : $purchaseModel;
            ?>
            <div class="auth-header">
                <h1><?= lang('App.buy') ?></h1>
                <?php if ($purchaseModel === 'both'): ?>
                <div id="mode-toggle" style="display: flex; gap: 10px; justify-content: center; margin-top: 10px;">
                    <button type="button" class="mode-toggle-btn" data-mode="usdt" style="flex:1; padding: 8px; border-radius: 8px; border: 1px solid #334155; background: rgba(99,102,241,0.15); color: #cbd5e1; cursor: pointer; font-size: 13px; font-weight: 600;"><?= lang('App.amount_usdt') ?></button>
                    <button type="button" class="mode-toggle-btn" data-mode="brl" style="flex:1; padding: 8px; border-radius: 8px; border: 1px solid #334155; background: rgba(99,102,241,0.15); color: #cbd5e1; cursor: pointer; font-size: 13px; font-weight: 600;"><?= lang('App.amount_brl') ?></button>
                </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label><?= lang('App.wallet_address') ?> (TRC-20)</label>
                <div
                    style="background: #0f172a; padding: 12px; border-radius: 8px; border: 1px dashed #334155; font-family: monospace; font-size: 13px; color: #818cf8; word-break: break-all;">
                    <?= session()->get('user_wallet') ?: (session()->get('user_lang') == 'zh-CN' ? '未注册' : 'Não cadastrada') ?>
                </div>
            </div>

            <div class="form-group" id="usdt-input-group" style="<?= $initialMode === 'brl' ? 'display:none;' : '' ?>">
                <label for="usdt-amount"><?= lang('App.amount_usdt') ?> (USDT)</label>
                <input type="number" id="usdt-amount" placeholder="Ex: 5000" step="0.01" min="5000">
            </div>

            <div class="form-group" id="brl-input-group" style="<?= $initialMode !== 'brl' ? 'display:none;' : '' ?>">
                <label for="brl-amount"><?= lang('App.amount_brl') ?> (R$)</label>
                <input type="number" id="brl-amount" placeholder="Ex: 30000" step="0.01">
            </div>

            <div class="form-group">
                <label><?= lang('App.delivery_type') ?></label>
                <div class="delivery-selector" style="margin-bottom: 20px;">
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
            </div>

            <div id="conversion-info" style="margin-bottom: 25px; background: rgba(15, 23, 42, 0.4); padding: 15px; border-radius: 12px; border: 1px solid #1e293b;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="color: #94a3b8; font-size: 13px; font-weight: 500;"><?= lang('App.live_rate') ?>:</span>
                    <span id="modal-base-rate" style="color: #cbd5e1; font-weight: 600;">R$ 0,0000</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-top: 10px; border-top: 1px solid #334155;">
                    <span style="color: #94a3b8; font-size: 14px; font-weight: 600;"><?= lang('App.final_rate') ?>:</span>
                    <span id="modal-rate" style="color: #a78bfa; font-weight: 700; font-size: 18px;">R$ 0,0000</span>
                </div>
                <div id="result-label" style="display: flex; justify-content: space-between; align-items: center; background: rgba(99, 102, 241, 0.1); padding: 12px; border-radius: 8px; border: 1px solid rgba(99, 102, 241, 0.2);"
                    data-label-usdt="<?= lang('App.total_brl') ?>" data-label-brl="<?= lang('App.receive_usdt') ?>">
                    <span id="result-label-text" style="color: #94a3b8; font-size: 14px; font-weight: 700;"><?= $initialMode === 'brl' ? lang('App.receive_usdt') : lang('App.total_brl') ?>:</span>
                    <span id="brl-result" style="color: #818cf8; font-weight: 800; font-size: 22px;">R$ 0,00</span>
                </div>
            </div>

            <div id="quote-info" style="display: none; margin-bottom: 20px; text-align: center;">
                <span style="background: rgba(251, 191, 36, 0.1); color: #fbbf24; font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(251, 191, 36, 0.2); text-transform: uppercase;">
                    <i class="fas fa-info-circle" style="margin-right: 4px;"></i> <?= lang('App.delivery_hint') ?>
                </span>
            </div>



            <button class="btn-primary" id="confirm-buy-btn">
                <?= lang('App.confirm_buy') ?>
            </button>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 1100; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(8px);">
        <div class="auth-container"
            style="max-width: 400px; text-align: center; background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(99, 102, 241, 0.3); padding: 40px; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
            <div
                style="width: 80px; height: 80px; background: rgba(34, 197, 94, 0.1); border: 2px solid #22c55e; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 24px; animation: scaleUp 0.5s ease-out;">
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
                <button onclick="closeSuccessModal()" class="btn-secondary" style="margin: 0; width: 100%; background: rgba(255,255,255,0.05); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 12px; cursor: pointer; font-weight: 600;">
                    <?= lang('App.understood') ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Proof Upload Modal -->
    <div id="proof-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 1200; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(10px);">
        <div class="auth-container" style="max-width: 400px; background: #1e293b; padding: 40px; border-radius: 24px; text-align: center; border: 1px solid rgba(59, 130, 246, 0.2);">
            <div style="width: 60px; height: 60px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px;">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            </div>
            <h2 style="color: white; font-size: 20px; font-weight: 700; margin-bottom: 10px;"><?= $isChinese ? '上传付款凭证' : 'Enviar Comprovante' ?></h2>
            <p style="color: #94a3b8; font-size: 14px; margin-bottom: 25px;"><?= $isChinese ? '请选择付款截图或PDF文件。' : 'Por favor, selecione o print ou PDF do pagamento.' ?></p>
            
            <input type="file" id="proof-file" accept="image/*,application/pdf" style="display: none;" onchange="handleFileSelect(this)">
            <input type="hidden" id="proof-transaction-id">
            
            <label for="proof-file" id="file-label" style="display: block; padding: 30px; border: 2px dashed rgba(59, 130, 246, 0.3); border-radius: 16px; cursor: pointer; transition: 0.3s; margin-bottom: 20px;">
                <span id="file-name" style="color: #60a5fa; font-weight: 500;"><?= $isChinese ? '点击此处选择文件' : 'Clique aqui para selecionar' ?></span>
            </label>

            <div style="margin-bottom: 20px; text-align: left;">
                <label style="display: block; color: #94a3b8; font-size: 13px; margin-bottom: 8px;"><?= $isChinese ? '备注 (可选)' : 'Observações (Opcional)' ?></label>
                <textarea id="proof-text" rows="3" style="width: 100%; background: #0f172a; border: 1px solid #334155; border-radius: 12px; color: white; padding: 12px; font-size: 14px; outline: none; resize: none;" placeholder="<?= $isChinese ? '输入任何额外信息...' : 'Digite qualquer informação extra...' ?>"></textarea>
            </div>

            <button id="upload-btn" onclick="uploadProof()" class="btn-primary" style="width: 100%; display: none;">
                <?= $isChinese ? '开始上传' : 'Iniciar Upload' ?>
            </button>
            
            <button onclick="closeProofModal()" style="margin-top: 15px; background: none; border: none; color: #64748b; font-size: 13px; cursor: pointer;"><?= $isChinese ? '稍后再说' : 'Fazer isso mais tarde' ?></button>
        </div>
    </div>

    <!-- Deposit Modal -->
    <div id="deposit-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 1200; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(10px);">
        <div class="auth-container" style="max-width: 440px; background: #1e293b; padding: 32px; border-radius: 24px; border: 1px solid rgba(16, 185, 129, 0.2); position: relative; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden;">
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
                <button id="deposit-submit-btn" onclick="submitDeposit()" class="btn-primary" style="width: 100%; background: #10b981;">
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
        const isChinese = <?= session()->get('user_lang') === 'zh-CN' ? 'true' : 'false' ?>;

        // Only one modal (any element whose id ends in "-modal") may be visible at a time.
        function showModal(id) {
            document.querySelectorAll('[id$="-modal"]').forEach(m => {
                if (m.id !== id) m.style.display = 'none';
            });
            document.getElementById(id).style.display = 'flex';
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
        const typingIndicator = document.getElementById('typing');
        const languageSelector = document.getElementById('language-selector');

        async function toggleLanguage() {
            const currentLang = '<?= session()->get('user_lang') ?>';
            const newLang = currentLang === 'zh-CN' ? 'pt-BR' : 'zh-CN';
            await fetch('<?= url_to('update_language') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                body: JSON.stringify({ language: newLang })
            });
            location.reload();
        }

        languageSelector.onchange = async () => {
            const lang = languageSelector.value;
            await fetch('<?= url_to('update_language') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                body: JSON.stringify({ language: lang })
            });
            location.reload();
        };

        let currentExchangeRate = 0;
        let currentBaseRate = 0;
        let currentFeePercent = 0;
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
                const brl = parseFloat(document.getElementById('brl-amount').value) || 0;
                const usdt = currentExchangeRate > 0 ? brl / currentExchangeRate : 0;
                resultText.textContent = resultLabelDiv.dataset.labelBrl + ':';
                resultValue.textContent = `${usdt.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} USDT`;
            } else {
                const usdt = parseFloat(document.getElementById('usdt-amount').value) || 0;
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

        async function updateLiveRate() {
            try {
                const response = await fetch('<?= url_to('chat_rate') ?>?delivery_type=' + encodeURIComponent(selectedDeliveryType));
                const data = await response.json();
                if (data.rate) {
                    const badge = document.getElementById('live-rate-badge');
                    const oldRate = badge.textContent;
                    // Mostrar cotação SEM taxas no badge lateral
                    const newRate = `R$ ${data.base_rate.toLocaleString('pt-BR', { minimumFractionDigits: 4, maximumFractionDigits: 4 })}`;

                    currentExchangeRate = parseFloat(data.rate);
                    currentBaseRate = parseFloat(data.base_rate);
                    currentFeePercent = parseFloat(data.fee_percent);

                    if (document.getElementById('buy-modal') && document.getElementById('buy-modal').style.display === 'flex') {
                        document.getElementById('modal-base-rate').textContent = `R$ ${currentBaseRate.toLocaleString('pt-BR', { minimumFractionDigits: 4 })}`;
                        document.getElementById('modal-rate').textContent = `R$ ${currentExchangeRate.toLocaleString('pt-BR', { minimumFractionDigits: 4 })}`;
                        updateResultDisplay();
                    }

                    if (oldRate !== newRate) {
                        badge.textContent = newRate;
                        // Efeito de pulso suave na atualização
                        badge.style.transition = 'none';
                        badge.style.transform = 'scale(1.1)';
                        badge.style.color = '#4ade80';
                        setTimeout(() => {
                            badge.style.transition = 'all 0.5s ease';
                            badge.style.transform = 'scale(1)';
                            badge.style.color = '#22c55e';
                        }, 100);

                        // Opcional: Atualizar gráfico se houver mudança significativa
                        if (window.myChart) {
                            const lastVal = window.myChart.data.datasets[0].data.slice(-1)[0];
                            if (lastVal !== currentExchangeRate) {
                                window.myChart.data.labels.push(new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
                                window.myChart.data.datasets[0].data.push(currentExchangeRate);
                                if (window.myChart.data.labels.length > 15) {
                                    window.myChart.data.labels.shift();
                                    window.myChart.data.datasets[0].data.shift();
                                }
                                window.myChart.update('none');
                            }
                        }
                    }
                }
            } catch (e) { console.error("Erro ao atualizar cotação live"); }
        }

        updateLiveRate();
        setInterval(updateLiveRate, 1000); // Atualiza a cada 1 segundo para dinâmica ultrarrápida

        async function updateDebtBalance() {
            try {
                const response = await fetch('<?= url_to('chat_debt') ?>');
                const data = await response.json();
                if (data.balance !== undefined) {
                    const badge = document.getElementById('balance-badge');
                    const mobileBadge = document.getElementById('mobile-balance-badge');
                    const value = data.balance.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    const isNegative = data.balance < 0;
                    const positiveStyle = 'background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.2);color:#4ade80;';
                    const negativeStyle = 'background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#f87171;';
                    const style = isNegative ? negativeStyle : positiveStyle;
                    if (badge) { badge.textContent = value; badge.style.cssText = 'padding:8px;border-radius:4px;font-size:13px;font-weight:600;' + style; }
                    if (mobileBadge) { mobileBadge.textContent = value; mobileBadge.style.cssText = 'padding:6px;border-radius:4px;font-size:12px;font-weight:600;' + style; }
                }
            } catch (e) { console.error("Erro ao atualizar saldo"); }
        }

        updateDebtBalance();
        setInterval(updateDebtBalance, 10000); // Atualiza a cada 10 segundos

        // Gráfico de Histórico
        async function initChart() {
            const response = await fetch('<?= url_to('chat_history') ?>');
            const data = await response.json();
            if (!data || data.length === 0) return;

            const ctx = document.getElementById('historyChart').getContext('2d');
            window.myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(i => i.time),
                    datasets: [{
                        label: 'USD/BRL',
                        data: data.map(i => i.value),
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { display: false },
                        y: {
                            grid: { color: '#334155' },
                            ticks: { color: '#94a3b8', font: { size: 10 } }
                        }
                    }
                }
            });
        }

        initChart();

        // Delivery Type Selection
        document.querySelectorAll('.delivery-option').forEach(opt => {
            opt.onclick = () => {
                document.querySelectorAll('.delivery-option').forEach(o => o.classList.remove('active'));
                opt.classList.add('active');
                selectedDeliveryType = opt.dataset.value;
                document.getElementById('conversion-info').style.display = selectedDeliveryType === 'D+0' ? 'block' : 'none';
                document.getElementById('quote-info').style.display = selectedDeliveryType === 'D+0' ? 'none' : 'block';
                updateLiveRate();
            };
        });

        function addMessage(text, side, showBuy = false, rate = 0, amount = 0, msgId = null) {
            const div = document.createElement('div');
            if (msgId) {
                div.dataset.id = msgId;
            }
            div.className = `message ${side}`;
            
            const textSpan = document.createElement('span');
            textSpan.textContent = text;
            div.appendChild(textSpan);

            if (msgId) {
                const deleteBtn = document.createElement('button');
                deleteBtn.className = 'delete-msg-btn';
                deleteBtn.innerHTML = '🗑️';
                deleteBtn.title = isChinese ? '删除消息' : 'Apagar mensagem';
                deleteBtn.onclick = (e) => deleteMessage(e, msgId, div);
                div.appendChild(deleteBtn);
            }

            if (showBuy && rate > 0) {
                const btn = document.createElement('button');
                btn.textContent = side === 'user' ? (isChinese ? '购买' : 'Comprar') : (isChinese ? '我想购买美元' : 'Quero Comprar Dólar');
                btn.className = 'send-btn';
                btn.style.marginTop = '10px';
                btn.style.width = 'auto';
                btn.style.padding = '8px 16px';
                btn.onclick = () => openBuyModal(rate, amount);
                div.appendChild(document.createElement('br'));
                div.appendChild(btn);
            }

            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            return div;
        }

        async function deleteMessage(e, id, element) {
            if (e) e.stopPropagation();
            if (!confirm(isChinese ? '确定要删除这条消息吗？' : 'Tem certeza que deseja apagar esta mensagem?')) {
                return;
            }
            try {
                const response = await fetch('<?= url_to('chat_delete_message') ?>', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-Requested-With': 'XMLHttpRequest', 
                        '<?= csrf_header() ?>': '<?= csrf_hash() ?>' 
                    },
                    body: JSON.stringify({ id: id })
                });
                const data = await response.json();
                if (data.status === 'success') {
                    element.remove();
                } else {
                    alert(data.message || 'Erro ao apagar');
                }
            } catch (err) {
                console.error(err);
            }
        }

        async function clearChatMessages() {
            if (!confirm(isChinese ? '确定要清空所有聊天记录吗？' : 'Tem certeza que deseja apagar TODO o histórico de mensagens?')) {
                return;
            }
            try {
                const response = await fetch('<?= url_to('chat_clear_messages') ?>', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-Requested-With': 'XMLHttpRequest', 
                        '<?= csrf_header() ?>': '<?= csrf_hash() ?>' 
                    }
                });
                const data = await response.json();
                if (data.status === 'success') {
                    chatMessages.innerHTML = '';
                    addMessage(`<?= lang('App.welcome_msg', [
                        'name' => explode(' ', session()->get('user_name'))[0],
                        'start' => $business_hours['start'],
                        'end' => $business_hours['end']
                    ]) ?>`, 'bot');
                } else {
                    alert('Erro ao limpar conversa');
                }
            } catch (err) {
                console.error(err);
            }
        }

        function openBuyModal(rate, amount = 0) {
            currentExchangeRate = rate;
            
            // Se a taxa passada for a mesma que a atual, usamos os detalhes globais
            // Se for diferente (ex: vindo de uma mensagem antiga), calculamos proporcionalmente
            let baseRate = currentBaseRate;
            let feePercent = currentFeePercent;
            
            if (Math.abs(rate - currentExchangeRate) > 0.0001) {
                // Tenta estimar se for diferente
                feePercent = currentFeePercent; 
                baseRate = rate / (1 + (feePercent / 100));
            }

            document.getElementById('modal-base-rate').textContent = `R$ ${baseRate.toLocaleString('pt-BR', { minimumFractionDigits: 4 })}`;
            document.getElementById('modal-rate').textContent = `R$ ${rate.toLocaleString('pt-BR', { minimumFractionDigits: 4 })}`;
            const input = currentInputMode === 'brl' ? document.getElementById('brl-amount') : document.getElementById('usdt-amount');
            if (amount > 0) {
                input.value = currentInputMode === 'brl' ? (amount * rate) : amount;
            } else {
                input.value = '';
            }
            // Disparar o cálculo
            input.dispatchEvent(new Event('input'));
            
            const btn = document.getElementById('confirm-buy-btn');
            if (quotationFlow === 'operator') {
                btn.textContent = isChinese ? '发送给操作员' : 'Enviar ao Operador';
                btn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
            } else {
                btn.textContent = '<?= lang('App.confirm_buy') ?>';
                btn.style.background = 'linear-gradient(135deg, #3b82f6, #1d4ed8)';
            }
            btn.disabled = false;
            btn.style.opacity = '1';

            showModal('buy-modal');
        }

        function closeModal() {
            document.getElementById('buy-modal').style.display = 'none';
        }

        document.getElementById('usdt-amount').oninput = () => updateResultDisplay();
        document.getElementById('brl-amount').oninput = () => updateResultDisplay();

        function closeSuccessModal() {
            document.getElementById('success-modal').style.display = 'none';
        }

        document.getElementById('confirm-buy-btn').onclick = async () => {
            let amountUsdt, amountBrl;
            if (currentInputMode === 'brl') {
                amountBrl = parseFloat(document.getElementById('brl-amount').value) || 0;
                amountUsdt = currentExchangeRate > 0 ? amountBrl / currentExchangeRate : 0;
            } else {
                amountUsdt = parseFloat(document.getElementById('usdt-amount').value) || 0;
                amountBrl = amountUsdt * currentExchangeRate;
            }

            if (amountUsdt <= 0 || amountBrl <= 0) {
                alert(isChinese ? '请输入有效金额' : 'Por favor, insira um valor válido');
                return;
            }

            if (amountUsdt < 5000) {
                alert(isChinese ? '最低购买金额为 5000 USDT' : 'O valor mínimo de compra é 5.000 USDT');
                return;
            }

            try {
                const response = await fetch('<?= url_to('chat_buy') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                    },
                    body: JSON.stringify({
                        amount_brl: amountBrl,
                        amount_usdt: amountUsdt,
                        delivery_type: selectedDeliveryType,
                        input_mode: currentInputMode
                    })
                });
                const data = await response.json();
                if (data.status === 'success') {
                    closeModal();
                    
                    if (quotationFlow === 'operator' && operatorWhatsapp) {
                        const messageText = `Olá! Acabei de gerar uma solicitação de compra de USDT na plataforma:\n\n` +
                                            `• *Valor:* ${parseFloat(amountUsdt).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} USDT\n` +
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
                    alert(data.error || data.message || 'Erro ao processar compra');
                }
            } catch (e) {
                if (!_sessionExpiredHandled) {
                    alert(isChinese ? '处理请求时出错' : 'Erro ao processar solicitação');
                }
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

            try {
                const response = await fetch('<?= url_to('chat_send') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                    },
                    body: JSON.stringify({ message: message })
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    console.error('Server error:', errorData);
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();
                typingIndicator.style.display = 'none';

                if (data.reply) {
                    addMessage(data.reply, 'bot', data.showBuy, data.currentRate, data.suggestedAmount);
                } else {
                    console.error('API error:', data);
                    addMessage(isChinese ? '抱歉，处理您的消息时出现错误。' : 'Desculpe, ocorreu um erro ao processar sua mensagem.', 'bot');
                }
            } catch (error) {
                console.error('Fetch error:', error);
                typingIndicator.style.display = 'none';
                addMessage(isChinese ? '连接或安全错误。请尝试刷新页面。' : 'Erro de conexão ou segurança. Tente atualizar a página.', 'bot');
            } finally {
                isSendingMessage = false;
            }
        };
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
                        '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                    },
                    body: formData
                });
                const data = await response.json();
                if (data.status === 'success') {
                    alert(data.message);
                    closeProofModal();
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
                const response = await fetch('<?= url_to('deposit_store') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
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