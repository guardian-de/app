<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticação de 2 Fatores | Guardian</title>
    <link rel="stylesheet" href="<?= base_url('css/auth.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="bg-glow bg-glow-1"></div>
    <div class="bg-glow bg-glow-2"></div>
    <div class="bg-glow bg-glow-3"></div>

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
            <h1>Segurança Adicional</h1>
            <p>Digite o código de 6 dígitos gerado pelo seu aplicativo Google Authenticator para continuar.</p>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <form action="<?= url_to('verify_login_2fa') ?>" method="POST" autocomplete="off">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="two_factor_code">Código de Autenticação (2FA)</label>
                <input type="text" id="two_factor_code" name="two_factor_code" placeholder="000 000" inputmode="numeric" pattern="[0-9]*" maxlength="6" required autofocus style="text-align: center; font-size: 24px; font-weight: 700; letter-spacing: 0.3em; padding: 10px;">
            </div>
            <button type="submit" class="btn-primary">Confirmar e Entrar</button>
        </form>

        <div class="auth-footer">
            <a href="<?= url_to('logout') ?>" style="color: #6366f1; text-decoration: none; font-weight: 600;">Voltar para o Login</a>
        </div>
    </div>
</body>
</html>
