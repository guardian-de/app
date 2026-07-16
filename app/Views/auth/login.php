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
                <input type="text" id="login" name="login" autocomplete="username" placeholder="seu login" required>
            </div>
            <div class="form-group">
                <label for="current-password">Senha</label>
                <div class="password-wrapper">
                    <input type="password" id="current-password" name="password" autocomplete="current-password" placeholder="••••••••" required>
                    <button type="button" class="password-toggle-btn" id="password-toggle" aria-label="Mostrar senha">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-primary">Entrar</button>
        </form>

        <div class="auth-footer" style="color: #94a3b8; font-size: 13px;">
            Acesso restrito a clientes autorizados.
        </div>
    </div>

    <script>
        document.getElementById('password-toggle').addEventListener('click', function() {
            const input = document.getElementById('current-password');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            const isText = type === 'text';
            this.innerHTML = isText ? `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                    <path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                    <line x1="2" y1="2" x2="22" y2="22"/>
                </svg>
            ` : `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
            `;
            this.setAttribute('aria-label', isText ? 'Ocultar senha' : 'Mostrar senha');
        });
    </script>
</body>
</html>
