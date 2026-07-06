<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="header">
    <h1 style="font-size: 24px; color: white;">Configurações do Sistema</h1>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <p style="color: #94a3b8; font-size: 14px; margin-bottom: 30px;">Defina os parâmetros globais de funcionamento da plataforma.</p>

    <?php if(session()->getFlashdata('success')): ?>
        <div style="background: rgba(52, 211, 153, 0.1); color: #34d399; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(52, 211, 153, 0.2); font-size: 14px;">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <form action="<?= url_to('admin_settings_update') ?>" method="POST" style="display: flex; flex-direction: column; gap: 25px;">
        <?= csrf_field() ?>
        
        <div style="padding: 20px; background: rgba(15, 23, 42, 0.3); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
            <h3 style="font-size: 16px; color: white; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                🕒 Horários de Funcionamento por Prazo
            </h3>
            
            <!-- D+0 -->
            <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <h4 style="font-size: 14px; color: #60a5fa; margin-bottom: 12px; font-weight: 600;">Transações D+0</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Início D+0</label>
                        <input type="time" name="business_hours_start" value="<?= esc($start) ?>" required style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none; font-size: 16px;">
                    </div>
                    <div class="form-group">
                        <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Término D+0</label>
                        <input type="time" name="business_hours_end" value="<?= esc($end) ?>" required style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none; font-size: 16px;">
                    </div>
                </div>
            </div>

            <!-- D+1 -->
            <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <h4 style="font-size: 14px; color: #a78bfa; margin-bottom: 12px; font-weight: 600;">Transações D+1</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Início D+1</label>
                        <input type="time" name="business_hours_d1_start" value="<?= esc($start_d1) ?>" required style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none; font-size: 16px;">
                    </div>
                    <div class="form-group">
                        <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Término D+1</label>
                        <input type="time" name="business_hours_d1_end" value="<?= esc($end_d1) ?>" required style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none; font-size: 16px;">
                    </div>
                </div>
            </div>

            <!-- D+2 -->
            <div>
                <h4 style="font-size: 14px; color: #f472b6; margin-bottom: 12px; font-weight: 600;">Transações D+2</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Início D+2</label>
                        <input type="time" name="business_hours_d2_start" value="<?= esc($start_d2) ?>" required style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none; font-size: 16px;">
                    </div>
                    <div class="form-group">
                        <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Término D+2</label>
                        <input type="time" name="business_hours_d2_end" value="<?= esc($end_d2) ?>" required style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none; font-size: 16px;">
                    </div>
                </div>
            </div>
            
            <!-- Regras e Bloqueios Fora do Horário -->
            <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05); display: flex; flex-direction: column; gap: 20px;">
                <h4 style="font-size: 14px; color: #f87171; font-weight: 600; margin: 0;">🚫 Regras e Bloqueios Fora do Horário</h4>
                
                <!-- D+0 -->
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px; align-items: flex-end;">
                    <div class="form-group">
                        <label style="display: block; color: #94a3b8; font-size: 11px; font-weight: 600; margin-bottom: 8px;">Ação D+0</label>
                        <select name="business_hours_d0_allow_outside" style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 11px; border-radius: 10px; color: white; outline: none; font-size: 13px; cursor: pointer; height: 43px;">
                            <option value="0" <?= $d0_allow_outside === '0' ? 'selected' : '' ?>>🚫 Bloquear Compra</option>
                            <option value="1" <?= $d0_allow_outside === '1' ? 'selected' : '' ?>>🔓 Permitir Compra</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display: block; color: #94a3b8; font-size: 11px; font-weight: 600; margin-bottom: 8px;">Mensagem de Bloqueio D+0</label>
                        <input type="text" name="business_hours_d0_block_message" value="<?= esc($d0_block_message) ?>" required style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 11px; border-radius: 10px; color: white; outline: none; font-size: 13px; height: 43px;">
                    </div>
                </div>

                <!-- D+1 -->
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px; align-items: flex-end;">
                    <div class="form-group">
                        <label style="display: block; color: #94a3b8; font-size: 11px; font-weight: 600; margin-bottom: 8px;">Ação D+1</label>
                        <select name="business_hours_d1_allow_outside" style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 11px; border-radius: 10px; color: white; outline: none; font-size: 13px; cursor: pointer; height: 43px;">
                            <option value="0" <?= $d1_allow_outside === '0' ? 'selected' : '' ?>>🚫 Bloquear Compra</option>
                            <option value="1" <?= $d1_allow_outside === '1' ? 'selected' : '' ?>>🔓 Permitir Compra</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display: block; color: #94a3b8; font-size: 11px; font-weight: 600; margin-bottom: 8px;">Mensagem de Bloqueio D+1</label>
                        <input type="text" name="business_hours_d1_block_message" value="<?= esc($d1_block_message) ?>" required style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 11px; border-radius: 10px; color: white; outline: none; font-size: 13px; height: 43px;">
                    </div>
                </div>

                <!-- D+2 -->
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px; align-items: flex-end;">
                    <div class="form-group">
                        <label style="display: block; color: #94a3b8; font-size: 11px; font-weight: 600; margin-bottom: 8px;">Ação D+2</label>
                        <select name="business_hours_d2_allow_outside" style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 11px; border-radius: 10px; color: white; outline: none; font-size: 13px; cursor: pointer; height: 43px;">
                            <option value="0" <?= $d2_allow_outside === '0' ? 'selected' : '' ?>>🚫 Bloquear Compra</option>
                            <option value="1" <?= $d2_allow_outside === '1' ? 'selected' : '' ?>>🔓 Permitir Compra</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display: block; color: #94a3b8; font-size: 11px; font-weight: 600; margin-bottom: 8px;">Mensagem de Bloqueio D+2</label>
                        <input type="text" name="business_hours_d2_block_message" value="<?= esc($d2_block_message) ?>" required style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 11px; border-radius: 10px; color: white; outline: none; font-size: 13px; height: 43px;">
                    </div>
                </div>
            </div>
        </div>

        <div style="padding: 20px; background: rgba(15, 23, 42, 0.3); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
            <h3 style="font-size: 16px; color: white; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                ⚙️ Fluxo de Compra / Cotação
            </h3>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 12px;">Destino da Cotação ao Clicar em Comprar</label>
                
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <label style="display: flex; align-items: center; gap: 10px; color: white; cursor: pointer; font-size: 14px;">
                        <input type="radio" name="quotation_flow" value="direct" <?= $quotation_flow === 'direct' ? 'checked' : '' ?> style="accent-color: #3b82f6; width: 18px; height: 18px;">
                        <span>🛒 <strong>Compra Direta na Plataforma (Configuração Atual)</strong><br><small style="color: #64748b; display: block; margin-left: 28px; margin-top: 2px;">Cria transações automáticas pendentes e gera operações com juros diários direto no sistema.</small></span>
                    </label>
                    
                    <label style="display: flex; align-items: center; gap: 10px; color: white; cursor: pointer; font-size: 14px; margin-top: 8px;">
                        <input type="radio" name="quotation_flow" value="operator" <?= $quotation_flow === 'operator' ? 'checked' : '' ?> style="accent-color: #3b82f6; width: 18px; height: 18px;">
                        <span>💬 <strong>Enviar Cotação ao Operador (Manual via WhatsApp)</strong><br><small style="color: #64748b; display: block; margin-left: 28px; margin-top: 2px;">A cotação é salva e o usuário é direcionado ao WhatsApp do operador com mensagem pré-formatada.</small></span>
                    </label>
                </div>
            </div>

            <div class="form-group" style="margin-top: 25px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">WhatsApp do Operador (apenas números com DDD)</label>
                <input type="text" name="operator_whatsapp" value="<?= esc($operator_whatsapp) ?>" placeholder="Ex: 5511999999999" style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none; font-size: 16px;">
                <p style="margin-top: 8px; color: #64748b; font-size: 11px;">Insira o número completo incluindo o código do país (ex: 55 para o Brasil) e DDD, sem espaços, traços ou parênteses.</p>
            </div>
        </div>

        <div style="padding: 20px; background: rgba(15, 23, 42, 0.3); border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
            <h3 style="font-size: 16px; color: white; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #ffffff;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                Som de Alerta (Nova Compra)
            </h3>
            
            <div class="form-group" style="display: flex; gap: 15px; align-items: flex-end;">
                <div style="flex: 1;">
                    <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Selecione o Som de Notificação</label>
                    <select id="admin_alert_sound" name="admin_alert_sound" style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none; font-size: 16px; cursor: pointer; display: block; width: 100%;">
                        <option value="chime_premium" <?= ($admin_alert_sound ?? 'chime_premium') === 'chime_premium' ? 'selected' : '' ?>>Chime Premium (Padrão)</option>
                        <option value="retro_arcade" <?= ($admin_alert_sound ?? 'chime_premium') === 'retro_arcade' ? 'selected' : '' ?>>Retro Arcade (Gamer)</option>
                        <option value="digital_alert" <?= ($admin_alert_sound ?? 'chime_premium') === 'digital_alert' ? 'selected' : '' ?>>Alerta Digital (Discreto)</option>
                        <option value="soft_bell" <?= ($admin_alert_sound ?? 'chime_premium') === 'soft_bell' ? 'selected' : '' ?>>Sino de Cristal (Suave)</option>
                        <option value="triumph_chord" <?= ($admin_alert_sound ?? 'chime_premium') === 'triumph_chord' ? 'selected' : '' ?>>Acorde Triunfal (Celebrativo)</option>
                        <option value="siren_industrial" <?= ($admin_alert_sound ?? 'chime_premium') === 'siren_industrial' ? 'selected' : '' ?>>🚨 Sirene Industrial (Contínuo & Barulhento)</option>
                        <option value="police_sweep" <?= ($admin_alert_sound ?? 'chime_premium') === 'police_sweep' ? 'selected' : '' ?>>⚡ Giroflex Policial (Contínuo & Barulhento)</option>
                        <option value="emergency_bell" <?= ($admin_alert_sound ?? 'chime_premium') === 'emergency_bell' ? 'selected' : '' ?>>🔔 Campainha Emergência (Contínuo & Barulhento)</option>
                        <option value="factory_horn" <?= ($admin_alert_sound ?? 'chime_premium') === 'factory_horn' ? 'selected' : '' ?>>⚙️ Alarme de Fábrica (Contínuo & Barulhento)</option>
                        <option value="nuclear_danger" <?= ($admin_alert_sound ?? 'chime_premium') === 'nuclear_danger' ? 'selected' : '' ?>>☢️ Perigo Nuclear (Contínuo & Barulhento)</option>
                    </select>
                </div>
                <button type="button" id="btn-listen-sound" onclick="testSelectedSound()" style="padding: 13px 20px; font-size: 15px; height: 48px; flex-shrink: 0; background: #ffffff; color: #0f172a; border: 2px solid #ffffff; box-shadow: none; display: flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 6px; transition: all 0.2s; cursor: pointer;">
                    <svg id="svg-listen-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #0f172a; transition: stroke 0.2s;"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
                    <span id="listen-text">Ouvir Som</span>
                </button>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const listenBtn = document.getElementById('btn-listen-sound');
                        const listenIcon = document.getElementById('svg-listen-icon');
                        if (listenBtn && listenIcon) {
                            listenBtn.addEventListener('mouseenter', () => {
                                if (!window.testingContinuousSound) {
                                    listenBtn.style.background = 'transparent';
                                    listenBtn.style.color = '#ffffff';
                                    listenIcon.style.stroke = '#ffffff';
                                }
                            });
                            listenBtn.addEventListener('mouseleave', () => {
                                if (!window.testingContinuousSound) {
                                    listenBtn.style.background = '#ffffff';
                                    listenBtn.style.color = '#0f172a';
                                    listenIcon.style.stroke = '#0f172a';
                                }
                            });
                        }
                    });
                </script>
            </div>
            <p style="margin-top: 10px; color: #64748b; font-size: 12px;">Este som será reproduzido automaticamente sempre que um cliente realizar uma compra.</p>
        </div>

        <script>
            window.testingContinuousSound = false;

            function testSelectedSound() {
                const listenBtn = document.getElementById('btn-listen-sound');
                const listenText = document.getElementById('listen-text');
                const listenIcon = document.getElementById('svg-listen-icon');
                const sound = document.getElementById('admin_alert_sound').value;
                const isContinuous = ['siren_industrial', 'police_sweep', 'emergency_bell', 'factory_horn', 'nuclear_danger'].includes(sound);

                if (window.testingContinuousSound) {
                    if (typeof stopActiveAlertSound === 'function') {
                        stopActiveAlertSound();
                    }
                    window.testingContinuousSound = false;
                    listenText.innerText = 'Ouvir Som';
                    listenBtn.style.background = '#ffffff';
                    listenBtn.style.color = '#0f172a';
                    listenBtn.style.borderColor = '#ffffff';
                    listenIcon.innerHTML = `<polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>`;
                    listenIcon.style.stroke = '#0f172a';
                    return;
                }

                if (typeof playNotificationSound === 'function') {
                    playNotificationSound(sound);
                    
                    if (isContinuous) {
                        window.testingContinuousSound = true;
                        listenText.innerText = 'Parar Som';
                        listenBtn.style.background = 'rgba(239, 68, 68, 0.15)';
                        listenBtn.style.color = '#ef4444';
                        listenBtn.style.borderColor = '#ef4444';
                        listenIcon.innerHTML = `<rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect>`;
                        listenIcon.style.stroke = '#ef4444';
                    }
                } else {
                    console.error('Função playNotificationSound não encontrada no escopo global.');
                }
            }
        </script>

        <button type="submit" class="btn btn-primary" style="padding: 15px; justify-content: center; font-size: 15px;">Salvar Alterações</button>
    </form>
</div>
<?= $this->endSection() ?>
