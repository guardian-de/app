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
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
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
</body>
</html>
