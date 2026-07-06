<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardian | Private Banking & Digital Assets</title>
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

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--dark);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Layout */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Navbar */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 24px 0;
            z-index: 1000;
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

        .nav-links {
            display: flex;
            gap: 32px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 14px;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: white;
        }

        .btn-login {
            background: white;
            color: black;
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            font-size: 14px;
            transition: transform 0.2s;
        }

        .btn-login:hover {
            transform: scale(1.05);
        }

        /* Hero Section */
        header {
            padding: 160px 0 100px;
            position: relative;
            overflow: hidden;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-image-container {
            position: relative;
            z-index: 2;
            border-radius: 40px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 0 1px var(--border);
            aspect-ratio: 1/1;
        }

        .hero-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .hero-image-container:hover img {
            transform: scale(1.05);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            padding: 6px 16px;
            border-radius: 50px;
            color: var(--primary-light);
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 24px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .hero-title {
            font-size: clamp(40px, 6vw, 72px);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.04em;
            margin-bottom: 24px;
        }

        .hero-title span {
            display: block;
            background: linear-gradient(135deg, #fff 30%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            font-size: 18px;
            color: var(--text-muted);
            margin-bottom: 40px;
            max-width: 480px;
        }

        .btn-cta {
            background: var(--primary);
            color: white;
            padding: 18px 36px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
            transition: all 0.3s;
        }

        .btn-cta:hover {
            transform: translateY(-5px);
            background: var(--primary-light);
        }

        /* Glow Effects */
        .glow-1 {
            position: absolute;
            top: -10%;
            right: -10%;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, rgba(0,0,0,0) 70%);
            z-index: 0;
            pointer-events: none;
        }

        /* Features Section */
        section {
            padding: 100px 0;
        }

        .section-tag {
            color: var(--primary-light);
            text-transform: uppercase;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 0.2em;
            display: block;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 40px;
            font-weight: 800;
            margin-bottom: 64px;
            letter-spacing: -0.02em;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
        }

        .feature-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            padding: 48px;
            border-radius: 32px;
            transition: all 0.3s;
        }

        .feature-card:hover {
            border-color: rgba(59, 130, 246, 0.3);
            background: rgba(15, 23, 42, 0.6);
            transform: translateY(-10px);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 32px;
            color: var(--primary-light);
        }

        .feature-card h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .feature-card p {
            color: var(--text-muted);
            font-size: 16px;
        }

        /* Security Section */
        .security-section {
            background: rgba(59, 130, 246, 0.02);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .security-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
        }

        .security-image {
            position: relative;
            height: 400px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(2, 6, 23, 0.5) 100%);
            border-radius: 40px;
            border: 1px solid var(--border);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .shield-large {
            width: 120px;
            height: 120px;
            border: 6px solid var(--primary-light);
            border-radius: 15px 15px 60px 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 60px;
            font-weight: 800;
            color: var(--primary-light);
            box-shadow: 0 0 50px rgba(59, 130, 246, 0.3);
        }

        /* Footer */
        footer {
            padding: 100px 0 64px;
            border-top: 1px solid var(--border);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 48px;
            margin-bottom: 80px;
        }

        .footer-logo {
            margin-bottom: 24px;
        }

        .footer-desc {
            color: var(--text-muted);
            max-width: 300px;
        }

        .footer-col h4 {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 24px;
            color: white;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .footer-links a {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 14px;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: white;
        }

        .copyright {
            padding-top: 48px;
            border-top: 1px solid var(--border);
            text-align: center;
            color: #475569;
            font-size: 14px;
        }

        @media (max-width: 968px) {
            .security-content { grid-template-columns: 1fr; gap: 48px; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
            .nav-links:not(.auth) { display: none; }
        }
    </style>
</head>
<body>

    <nav>
        <div class="container nav-content">
            <a href="#" class="logo">
                <div class="logo-box">G</div>
                Guardian
            </a>
            <div class="nav-links">
                <a href="#services">Serviços</a>
                <a href="#security">Segurança</a>
                <a href="#about">Sobre</a>
                <a href="<?= url_to('login') ?>" class="btn-login">Acessar Conta</a>
            </div>
        </div>
    </nav>

    <header>
        <div class="glow-1"></div>
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <div class="badge">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Segurança de Nível Institucional
                    </div>
                    <h1 class="hero-title">
                        <span>Proteja seu</span>
                        <span>patrimônio global.</span>
                    </h1>
                    <p class="hero-desc">
                        A Guardian oferece soluções de Private Banking para investidores que buscam segurança absoluta e liquidez internacional.
                    </p>
                    <div style="display: flex; gap: 16px;">
                        <a href="<?= url_to('login') ?>" class="btn-cta">
                            Começar Agora
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </a>
                    </div>
                </div>
                <div class="hero-image-container">
                    <img src="<?= base_url('guardian_hero_bg_1778851349522.png') ?>" alt="Guardian Interior">
                </div>
            </div>
        </div>
    </header>

    <style>
        @media (max-width: 968px) {
            .hero-grid { grid-template-columns: 1fr; gap: 40px; }
            .hero-image-container { display: none; }
        }
    </style>

    <section id="services">
        <div class="container">
            <span class="section-tag">Nossos Serviços</span>
            <h2 class="section-title">Construído para a elite financeira.</h2>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <h3>Private Banking</h3>
                    <p>Gestão personalizada de grandes fortunas com assessoria exclusiva e discricionária.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                    </div>
                    <h3>Liquidez Instantânea</h3>
                    <p>Acesse seu capital em qualquer lugar do mundo, 24 horas por dia, com taxas institucionais.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </div>
                    <h3>Ativos Digitais</h3>
                    <p>Integração total com o ecossistema de criptoativos e custódia segura garantida.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="security" class="security-section">
        <div class="container security-content">
            <div class="security-image">
                <div class="shield-large">G</div>
            </div>
            <div>
                <span class="section-tag">Segurança</span>
                <h2 class="section-title">Sua proteção é nossa maior prioridade.</h2>
                <p style="color: var(--text-muted); font-size: 18px; margin-bottom: 32px;">
                    Utilizamos criptografia de ponta a ponta e protocolos de custódia multi-assinatura para garantir que seus ativos estejam sempre protegidos contra qualquer ameaça.
                </p>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 16px;">
                    <li style="display: flex; align-items: center; gap: 12px; color: white; font-weight: 600;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Autenticação Biométrica e MFA
                    </li>
                    <li style="display: flex; align-items: center; gap: 12px; color: white; font-weight: 600;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Custódia em Cold Storage
                    </li>
                    <li style="display: flex; align-items: center; gap: 12px; color: white; font-weight: 600;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Auditorias em Tempo Real
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <a href="#" class="logo footer-logo">
                        <div class="logo-box">G</div>
                        Guardian
                    </a>
                    <p class="footer-desc">Redefinindo os limites da liberdade financeira global.</p>
                </div>
                <div class="footer-col">
                    <h4>Plataforma</h4>
                    <div class="footer-links">
                        <a href="#">Serviços</a>
                        <a href="#">Segurança</a>
                        <a href="#">Investimentos</a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Suporte</h4>
                    <div class="footer-links">
                        <a href="#">Central de Ajuda</a>
                        <a href="#">Contato</a>
                        <a href="<?= url_to('terms') ?>">Termos de Uso</a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Legal</h4>
                    <div class="footer-links">
                        <a href="<?= url_to('privacy') ?>">Privacidade</a>
                        <a href="#">Compliance</a>
                        <a href="#">Licenças</a>
                    </div>
                </div>
            </div>
            <div class="copyright">
                &copy; 2026 Guardian Private. Todos os direitos reservados.
            </div>
        </div>
    </footer>

</body>
</html>