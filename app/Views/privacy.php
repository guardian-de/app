<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade | Guardian</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-light: #60a5fa;
            --gold: #d4af37;
            --dark: #020617;
            --card-bg: rgba(15, 23, 42, 0.4);
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--dark);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 24px;
            width: 100%;
        }

        /* Navbar */
        nav {
            padding: 24px 0;
            background: rgba(2, 6, 23, 0.7);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
        }

        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: white;
            font-weight: 800;
            font-size: 22px;
            letter-spacing: -0.02em;
        }

        .logo-box {
            width: 36px;
            height: 36px;
            border: 2px solid white;
            border-radius: 6px 6px 15px 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
        }

        .btn-back {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 14px;
            transition: color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back:hover {
            color: white;
        }

        /* Main Content */
        main {
            flex: 1;
            padding: 80px 0;
        }

        .legal-header {
            margin-bottom: 48px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 32px;
        }

        .legal-title {
            font-size: 40px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #fff 30%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .legal-meta {
            color: var(--text-muted);
            font-size: 14px;
        }

        .legal-content h2 {
            font-size: 22px;
            font-weight: 700;
            margin: 32px 0 16px;
            color: var(--primary-light);
        }

        .legal-content p {
            color: var(--text-muted);
            margin-bottom: 16px;
            font-size: 16px;
            text-align: justify;
        }

        .legal-content ul {
            margin-bottom: 24px;
            padding-left: 20px;
            color: var(--text-muted);
        }

        .legal-content li {
            margin-bottom: 8px;
        }

        /* Footer */
        footer {
            padding: 48px 0;
            border-top: 1px solid var(--border);
            background: rgba(2, 6, 23, 0.4);
            text-align: center;
            color: #475569;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <nav>
        <div class="container nav-content">
            <a href="<?= base_url() ?>" class="logo">
                <div class="logo-box">G</div>
                Guardian
            </a>
            <a href="<?= base_url() ?>" class="btn-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Voltar ao início
            </a>
        </div>
    </nav>

    <main>
        <div class="container">
            <div class="legal-header">
                <h1 class="legal-title">Política de Privacidade</h1>
                <div class="legal-meta">Última atualização: Julho de 2026</div>
            </div>

            <div class="legal-content">
                <p>Na Guardian, a privacidade e a segurança dos dados dos nossos clientes são prioridades absolutas. Esta Política de Privacidade explica como coletamos, usamos, divulgamos e protegemos suas informações ao utilizar nossa plataforma de Private Banking e Ativos Digitais.</p>

                <h2>1. Informações que Coletamos</h2>
                <p>Coletamos informações necessárias para fornecer nossos serviços financeiros de forma segura e em total conformidade regulatória:</p>
                <ul>
                    <li><strong>Dados Cadastrais:</strong> Nome completo, e-mail, telefone, CPF/CNPJ, comprovante de residência e dados de faturamento.</li>
                    <li><strong>Dados de Transação:</strong> Histórico de depósitos, transferências, saldos, transações em criptoativos e endereços de carteiras digitais.</li>
                    <li><strong>Dados Técnicos:</strong> Endereço IP, tipo de dispositivo, sistema operacional e dados de navegação na plataforma.</li>
                </ul>

                <h2>2. Utilização dos Dados</h2>
                <p>As informações coletadas são utilizadas exclusivamente para:</p>
                <ul>
                    <li>Processar e validar transações financeiras e custódia de ativos.</li>
                    <li>Cumprir com obrigações legais, regulatórias (como regras de KYC/AML) e de compliance.</li>
                    <li>Prevenir fraudes e garantir a segurança cibernética da sua conta.</li>
                    <li>Melhorar os serviços e a experiência do usuário na plataforma.</li>
                </ul>

                <h2>3. Segurança das Informações</h2>
                <p>Implementamos medidas técnicas e organizacionais rigorosas para proteger seus dados, tais como:</p>
                <ul>
                    <li>Criptografia de ponta a ponta em todas as conexões e dados armazenados.</li>
                    <li>Autenticação de Múltiplos Fatores (MFA) para todas as ações sensíveis.</li>
                    <li>Armazenamento seguro de ativos e informações confidenciais em servidores de alta segurança e cold storage.</li>
                </ul>

                <h2>4. Compartilhamento de Informações</h2>
                <p>A Guardian não comercializa seus dados pessoais. Seus dados poderão ser compartilhados apenas com:</p>
                <ul>
                    <li>Instituições financeiras parceiras envolvidas na liquidação das suas transações.</li>
                    <li>Autoridades governamentais ou reguladoras quando exigido por lei ou decisão judicial.</li>
                </ul>

                <h2>5. Seus Direitos</h2>
                <p>De acordo com as leis de proteção de dados (incluindo a LGPD), você possui o direito de acessar, retificar, limitar ou solicitar a exclusão de seus dados pessoais da nossa base, ressalvadas as obrigações regulatórias de manutenção de histórico financeiro.</p>

                <h2>6. Contato</h2>
                <p>Para dúvidas sobre nossa Política de Privacidade ou solicitações de dados, entre em contato através do e-mail: <strong>dev@guardian.li</strong>.</p>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            &copy; 2026 Guardian Private. Todos os direitos reservados.
        </div>
    </footer>

</body>
</html>
