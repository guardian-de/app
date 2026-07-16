<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | Guardian</title>
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
        <div class="auth-header">
            <h1>Criar Conta</h1>
            <p>Junte-se a nós hoje mesmo</p>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-error">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?= url_to('store_user') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="name">Nome Completo</label>
                <input type="text" id="name" name="name" value="<?= old('name') ?>" placeholder="Seu nome" required>
            </div>
            <div class="form-group">
                <label for="login">Login</label>
                <input type="text" id="login" name="login" value="<?= old('login') ?>" placeholder="seu login" required>
            </div>
            <div class="form-group">
                <label for="new-password">Senha</label>
                <div class="password-wrapper">
                    <input type="password" id="new-password" name="password" autocomplete="new-password" placeholder="••••••••" required>
                    <button type="button" class="password-toggle-btn" id="password-toggle" aria-label="Mostrar senha">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label for="company_name">Empresa</label>
                <input type="text" id="company_name" name="company_name" value="<?= old('company_name') ?>" placeholder="Nome da sua empresa">
            </div>
            <div class="form-group">
                <label for="phone">Telefone</label>
                <input type="text" id="phone" name="phone" value="<?= old('phone') ?>" placeholder="(00) 00000-0000">
            </div>
            <div class="form-group">
                <label for="cnpj">CNPJ</label>
                <input type="text" id="cnpj" name="cnpj" value="<?= old('cnpj') ?>" placeholder="00.000.000/0000-00">
            </div>
            <div class="form-group">
                <label for="usdt_wallet">Carteira USDT (TRC-20)</label>
                <input type="text" id="usdt_wallet" name="usdt_wallet" value="<?= old('usdt_wallet') ?>" placeholder="Endereço da sua carteira" required>
            </div>
            <button type="submit" class="btn-primary">Criar Conta</button>
        </form>

        <div class="auth-footer">
            Já tem uma conta? <a href="<?= url_to('login') ?>">Faça login</a>
        </div>
    </div>

    <script>
        document.getElementById('password-toggle').addEventListener('click', function() {
            const input = document.getElementById('new-password');
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
