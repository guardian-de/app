<?php if ($role === 'admin' || $role === 'operator'): ?>
    <?= $this->extend('layouts/admin_layout') ?>

    <?= $this->section('content') ?>
    <div class="header">
        <h1 style="font-size: 24px; color: white;">Alterar Minha Senha</h1>
    </div>

    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <?php if(session()->getFlashdata('error')): ?>
            <div style="background: rgba(248, 113, 113, 0.1); color: #f87171; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(248, 113, 113, 0.2); font-size: 14px;">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if(session()->getFlashdata('success')): ?>
            <div style="background: rgba(16, 185, 129, 0.1); color: #34d399; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(16, 185, 129, 0.2); font-size: 14px;">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <form action="<?= url_to('update_password') ?>" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            <?= csrf_field() ?>

            <div class="form-group">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Senha Atual</label>
                <input type="password" name="current_password" required
                    style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
            </div>

            <div class="form-group">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Nova Senha</label>
                <input type="password" name="new_password" required
                    style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
            </div>

            <div class="form-group">
                <label style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px;">Confirmar Nova Senha</label>
                <input type="password" name="confirm_password" required
                    style="width: 100%; background: rgba(15, 23, 42, 0.5); border: 1px solid #334155; padding: 12px; border-radius: 10px; color: white; outline: none;">
            </div>

            <div style="display: flex; gap: 15px; margin-top: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">Salvar Nova Senha</button>
            </div>
        </form>
    </div>
    <?= $this->endSection() ?>

<?php else: ?>
    <!-- Client Standalone View -->
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Alterar Senha | Guardian</title>
        <link rel="stylesheet" href="<?= base_url('css/auth.css') ?>">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                background: #0f172a;
                color: white;
                font-family: 'Inter', sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
                box-sizing: border-box;
            }
            .auth-container {
                width: 100%;
                max-width: 420px;
                background: rgba(30, 41, 59, 0.7);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.05);
                padding: 30px;
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            }
            .auth-header h1 {
                font-size: 24px;
                font-weight: 700;
                margin-bottom: 5px;
                color: white;
            }
            .auth-header p {
                font-size: 14px;
                color: #94a3b8;
                margin-top: 0;
                margin-bottom: 25px;
            }
            .form-group {
                margin-bottom: 20px;
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .form-group label {
                font-size: 12px;
                font-weight: 600;
                color: #94a3b8;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }
            .form-group input {
                width: 100%;
                background: rgba(15, 23, 42, 0.5);
                border: 1px solid #334155;
                padding: 12px;
                border-radius: 12px;
                color: white;
                outline: none;
                box-sizing: border-box;
                font-size: 15px;
                transition: border-color 0.2s;
            }
            .form-group input:focus {
                border-color: #3b82f6;
            }
            .btn-primary {
                width: 100%;
                background: #3b82f6;
                color: white;
                border: none;
                padding: 14px;
                border-radius: 12px;
                font-weight: 600;
                font-size: 15px;
                cursor: pointer;
                transition: background-color 0.2s;
            }
            .btn-primary:hover {
                background: #2563eb;
            }
            .btn-secondary {
                width: 100%;
                background: rgba(255, 255, 255, 0.05);
                color: #cbd5e1;
                border: 1px solid rgba(255, 255, 255, 0.1);
                padding: 14px;
                border-radius: 12px;
                font-weight: 600;
                font-size: 15px;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                box-sizing: border-box;
                margin-top: 10px;
                transition: background-color 0.2s, color 0.2s;
            }
            .btn-secondary:hover {
                background: rgba(255, 255, 255, 0.1);
                color: white;
            }
            .alert {
                padding: 12px 15px;
                border-radius: 12px;
                font-size: 14px;
                margin-bottom: 20px;
                border: 1px solid;
            }
            .alert-error {
                background: rgba(239, 68, 68, 0.1);
                color: #f87171;
                border-color: rgba(239, 68, 68, 0.2);
            }
            .alert-success {
                background: rgba(16, 185, 129, 0.1);
                color: #34d399;
                border-color: rgba(16, 185, 129, 0.2);
            }
        </style>
    </head>
    <body>
        <div class="auth-container">
            <div class="auth-header">
                <h1>Alterar Senha</h1>
                <p>Forneça a senha atual para confirmar</p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <form action="<?= url_to('update_password') ?>" method="POST">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label>Senha Atual</label>
                    <input type="password" name="current_password" placeholder="••••••••" required>
                </div>
                
                <div class="form-group">
                    <label>Nova Senha</label>
                    <input type="password" name="new_password" placeholder="••••••••" required>
                </div>
                
                <div class="form-group">
                    <label>Confirmar Nova Senha</label>
                    <input type="password" name="confirm_password" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn-primary">Salvar Nova Senha</button>
                <a href="<?= url_to('dashboard') ?>" class="btn-secondary">Voltar ao Painel</a>
            </form>
        </div>
    </body>
    </html>
<?php endif; ?>
