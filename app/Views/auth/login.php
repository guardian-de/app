<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Guardian</title>
    <link rel="stylesheet" href="<?= base_url('css/auth.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <?php
        $settingsModel = new \App\Models\SettingsModel();
        $logoPath = $settingsModel->getConfig('logo_path');
        ?>
        <?php if ($logoPath): ?>
            <div style="text-align: center; margin-bottom: 25px;">
                <img src="<?= base_url($logoPath) ?>" alt="Logo" style="max-height: 60px; max-width: 100%; object-fit: contain;">
            </div>
        <?php endif; ?>

        <div class="auth-header">
            <h1>Bem-vindo</h1>
            <p>Faça login para continuar</p>
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

        <form action="<?= url_to('authenticate') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="login">Login</label>
                <input type="text" id="login" name="login" placeholder="seu login" required>
            </div>
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-primary">Entrar</button>
        </form>

        <div class="auth-footer" style="color: #94a3b8; font-size: 13px;">
            Acesso restrito a clientes autorizados.
        </div>
    </div>
</body>
</html>
