<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Serviço | Guardian</title>
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
                <h1 class="legal-title">Termos de Serviço</h1>
                <div class="legal-meta">Última atualização: Julho de 2026</div>
            </div>

            <div class="legal-content">
                <p>Seja bem-vindo à Guardian. Ao acessar e utilizar nossa plataforma de Private Banking e Ativos Digitais, você concorda em cumprir e em estar legalmente vinculado a estes Termos de Serviço.</p>

                <h2>1. Aceitação dos Termos</h2>
                <p>Ao se cadastrar na plataforma Guardian, você confirma ter capacidade legal para contratar os nossos serviços, estar ciente de todos os riscos associados ao mercado financeiro e de ativos digitais, e concordar integralmente com estes Termos de Serviço.</p>

                <h2>2. Elegibilidade e Cadastro</h2>
                <p>O acesso a nossos serviços de Private Banking é exclusivo para investidores qualificados e aprovados pelo nosso processo interno de due diligence. É sua obrigação fornecer informações cadastrais completas, exatas e verdadeiras durante todo o período de relacionamento conosco.</p>

                <h2>3. Custódia e Gestão de Ativos</h2>
                <p>A Guardian atua como custodiante qualificada de seus ativos digitais e fiduciários conforme as ordens enviadas por você na plataforma. O investidor reconhece que:</p>
                <ul>
                    <li>Ativos digitais sofrem alta volatilidade de preços de mercado.</li>
                    <li>Você é exclusivamente responsável pelas ordens de compra, venda, conversão e transferência enviadas.</li>
                    <li>As operações de custódia e depósito estão sujeitas aos limites operacionais estabelecidos.</li>
                </ul>

                <h2>4. Prevenção de Lavagem de Dinheiro (AML/KYC)</h2>
                <p>A Guardian implementa rigorosos processos de conformidade e detecção de atividades suspeitas. Reservamo-nos o direito de:</p>
                <ul>
                    <li>Solicitar documentação adicional para validar a origem lícita de qualquer depósito ou transação.</li>
                    <li>Bloquear temporariamente saques, transferências ou o acesso à conta em caso de suspeita fundamentada de fraude ou atividades ilícitas.</li>
                </ul>

                <h2>5. Limitação de Responsabilidade</h2>
                <p>A Guardian envida todos os esforços comerciais e tecnológicos razoáveis para garantir a disponibilidade contínua dos serviços. No entanto, não nos responsabilizamos por perdas decorrentes de instabilidades de redes descentralizadas de criptomoedas (blockchain), volatilidade extrema do mercado ou ações regulatórias de força maior.</p>

                <h2>6. Alterações nos Termos</h2>
                <p>Estes Termos podem ser atualizados periodicamente para refletir mudanças regulatórias ou aprimoramento dos serviços. Eventuais alterações entrarão em vigor imediatamente após a sua publicação nesta página.</p>

                <h2>7. Foro de Eleição</h2>
                <p>Fica eleito o foro da comarca da sede da Guardian para dirimir quaisquer dúvidas ou controvérsias decorrentes destes Termos de Serviço.</p>
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
