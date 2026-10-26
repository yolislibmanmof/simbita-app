<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMBITA - Revolusi Digital Bimbingan Tugas Akhir</title>
    <meta name="description" content="Platform modern untuk mengelola bimbingan Skripsi, Tesis, dan Disertasi dengan teknologi terkini">
    
    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- CSS Utama -->
    <link href="<?= BASE_URL ?>/assets/css/public.css" rel="stylesheet">

    <!-- =========================================================
         ULTRA PREMIUM ENHANCEMENTS - WORLD CLASS DESIGN
         ========================================================= -->
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --secondary: #8b5cf6;
            --accent: #06b6d4;
            --dark: #030712;
            --dark-light: #0f172a;
            --light: #f8fafc;
            --gray: #64748b;
            --gradient-main: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);
            --gradient-text: linear-gradient(135deg, #818cf8 0%, #c084fc 50%, #22d3ee 100%);
            --glass-bg: rgba(15, 23, 42, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; scroll-padding-top: 100px; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: var(--light);
            overflow-x: hidden;
            line-height: 1.6;
        }

        h1, h2, h3, h4 { font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; }
        a { text-decoration: none; color: inherit; transition: all 0.3s; }

        /* ===== ANIMATED BACKGROUND MESH ===== */
        .mesh-bg {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            z-index: -2;
            background: 
                radial-gradient(circle at 20% 30%, rgba(99, 102, 241, 0.2) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(139, 92, 246, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.1) 0%, transparent 50%);
            filter: blur(100px);
            animation: meshMove 30s ease-in-out infinite;
        }

        @keyframes meshMove {
            0%, 100% { transform: scale(1) rotate(0deg); }
            33% { transform: scale(1.1) rotate(5deg); }
            66% { transform: scale(0.95) rotate(-5deg); }
        }

        /* ===== NOISE OVERLAY ===== */
        .noise-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            z-index: -1;
            opacity: 0.03;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
        }

        /* ===== FLOATING PARTICLES ===== */
        .particles {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            pointer-events: none;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 4px; height: 4px;
            background: rgba(99, 102, 241, 0.5);
            border-radius: 50%;
            animation: floatParticle linear infinite;
        }

        @keyframes floatParticle {
            0% { transform: translateY(100vh) translateX(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) translateX(100px); opacity: 0; }
        }

        /* ===== HERO SECTION ENHANCED ===== */
        .hero {
            min-height: 100vh;
            padding: 12rem 2rem 6rem;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .hero-content {
            max-width: 1200px;
            text-align: center;
            position: relative;
            z-index: 10;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 2rem;
            background: var(--glass-bg);
            border: 1px solid rgba(129, 140, 248, 0.3);
            border-radius: 3rem;
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 2.5rem;
            backdrop-filter: blur(20px);
            animation: fadeInDown 1s ease;
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.2);
        }

        .hero-badge i {
            color: #fbbf24;
            animation: sparkle 2s ease-in-out infinite;
        }

        @keyframes sparkle {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.3) rotate(180deg); }
        }

        .hero-title {
            font-size: clamp(3rem, 8vw, 6rem);
            font-weight: 900;
            line-height: 1;
            margin-bottom: 2rem;
            letter-spacing: -0.03em;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .gradient-text {
            background: var(--gradient-text);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
            position: relative;
        }

        .gradient-text::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-main);
            border-radius: 2px;
            animation: slideIn 1s ease 0.5s both;
        }

        @keyframes slideIn {
            from { width: 0; }
            to { width: 100%; }
        }

        .hero-subtitle {
            font-size: 1.35rem;
            color: var(--gray);
            max-width: 750px;
            margin: 0 auto 3.5rem;
            line-height: 1.8;
            animation: fadeInUp 1s ease 0.4s both;
        }

        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            margin-bottom: 6rem;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease 0.6s both;
        }

        .btn-primary-glow {
            padding: 1.25rem 3rem;
            background: var(--gradient-main);
            color: white;
            border-radius: 1rem;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 0 40px rgba(99, 102, 241, 0.5);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn-primary-glow::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .btn-primary-glow:hover::before {
            left: 100%;
        }

        .btn-primary-glow:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 60px rgba(99, 102, 241, 0.7);
        }

        .btn-secondary-glass {
            padding: 1.25rem 3rem;
            background: rgba(255,255,255,0.05);
            color: white;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 1rem;
            font-weight: 600;
            font-size: 1.1rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
        }

        .btn-secondary-glass:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        /* ===== ANIMATED STATS SECTION ===== */
        .stats-showcase {
            position: relative;
            padding: 4rem 2rem;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 2rem;
            backdrop-filter: blur(20px);
            max-width: 1100px;
            margin: 0 auto;
            animation: fadeInUp 1s ease 0.8s both;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .stats-showcase::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), transparent);
            pointer-events: none;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            position: relative;
            z-index: 1;
        }

        .stat-card {
            text-align: center;
            padding: 2rem;
            position: relative;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-10px);
        }

        .stat-icon-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: var(--gradient-main);
            border-radius: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
            animation: floatIcon 4s ease-in-out infinite;
        }

        @keyframes floatIcon {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(5deg); }
        }

        .stat-number-wrapper {
            position: relative;
            margin-bottom: 0.5rem;
        }

        .stat-number {
            font-size: 3.5rem;
            font-weight: 900;
            background: var(--gradient-text);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }

        .stat-suffix {
            font-size: 2rem;
            font-weight: 800;
            color: var(--accent);
            margin-left: 0.25rem;
        }

        .stat-label {
            font-size: 1rem;
            color: var(--gray);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* ===== PLATFORM CAPABILITIES (REPLACES TRUSTED BY) ===== */
        .capabilities {
            padding: 6rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .capabilities-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .capabilities-title {
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .capabilities-grid {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 4rem;
            flex-wrap: wrap;
            max-width: 1000px;
            margin: 0 auto;
        }

        .capability-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem 2.5rem;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 1rem;
            transition: all 0.3s;
            animation: slideInLeft 0.8s ease backwards;
        }

        .capability-item:nth-child(1) { animation-delay: 0.1s; }
        .capability-item:nth-child(2) { animation-delay: 0.2s; }
        .capability-item:nth-child(3) { animation-delay: 0.3s; }
        .capability-item:nth-child(4) { animation-delay: 0.4s; }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .capability-item:hover {
            background: rgba(255,255,255,0.05);
            border-color: rgba(99, 102, 241, 0.3);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.2);
        }

        .capability-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-main);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .capability-text {
            font-weight: 600;
            color: var(--light);
            font-size: 1.05rem;
        }

        /* ===== SCROLL INDICATOR ===== */
        .scroll-indicator {
            position: absolute;
            bottom: 3rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            color: var(--gray);
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.1em;
            animation: bounce 2s ease-in-out infinite;
            cursor: pointer;
        }

        .mouse {
            width: 28px;
            height: 44px;
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 14px;
            position: relative;
        }

        .wheel {
            width: 4px;
            height: 12px;
            background: var(--primary-light);
            border-radius: 2px;
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            animation: scrollWheel 2s ease-in-out infinite;
        }

        @keyframes scrollWheel {
            0% { opacity: 1; top: 10px; }
            100% { opacity: 0; top: 25px; }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
            40% { transform: translateX(-50%) translateY(-15px); }
            60% { transform: translateX(-50%) translateY(-8px); }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== SECTIONS ===== */
        .section {
            padding: 8rem 2rem;
            position: relative;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 5rem;
        }

        .section-badge {
            display: inline-block;
            padding: 0.625rem 1.5rem;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            color: var(--primary-light);
            font-weight: 700;
            font-size: 0.875rem;
            border-radius: 2rem;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .section-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            margin-bottom: 1.5rem;
            color: white;
            line-height: 1.1;
        }

        .section-subtitle {
            color: var(--gray);
            font-size: 1.25rem;
            line-height: 1.8;
        }

        /* ===== FEATURES GRID ===== */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2.5rem;
        }

        .feature-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 1.5rem;
            padding: 3rem;
            position: relative;
            overflow: hidden;
            transition: all 0.4s;
            backdrop-filter: blur(20px);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(800px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(99, 102, 241, 0.1), transparent 40%);
            opacity: 0;
            transition: opacity 0.4s;
            pointer-events: none;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary-light);
            margin-bottom: 2rem;
            transition: all 0.4s;
            position: relative;
            z-index: 1;
        }

        .feature-card:hover .feature-icon {
            background: var(--gradient-main);
            color: white;
            transform: scale(1.15) rotate(-5deg);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }

        .feature-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: white;
            position: relative;
            z-index: 1;
        }

        .feature-description {
            color: var(--gray);
            font-size: 1.05rem;
            line-height: 1.8;
            position: relative;
            z-index: 1;
        }

        /* ===== HOW IT WORKS ===== */
        .how-it-works {
            background: rgba(255,255,255,0.02);
            border-top: 1px solid rgba(255,255,255,0.05);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .steps-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 3rem;
            position: relative;
        }

        .step {
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .step-number {
            width: 100px;
            height: 100px;
            background: var(--dark-light);
            border: 3px solid var(--primary);
            color: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 2.5rem;
            margin: 0 auto 2.5rem;
            box-shadow: 0 0 40px rgba(99, 102, 241, 0.3);
            transition: all 0.4s;
            position: relative;
            overflow: hidden;
        }

        .step-number::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-main);
            opacity: 0;
            transition: opacity 0.4s;
        }

        .step:hover .step-number {
            transform: scale(1.15) rotate(-10deg);
            box-shadow: 0 0 60px rgba(99, 102, 241, 0.5);
        }

        .step:hover .step-number::before {
            opacity: 1;
        }

        .step-number span {
            position: relative;
            z-index: 2;
        }

        .step-icon {
            font-size: 3.5rem;
            color: var(--primary-light);
            margin: 2rem 0 2rem;
            transition: all 0.4s;
        }

        .step:hover .step-icon {
            transform: scale(1.2);
            color: var(--accent);
        }

        .step-title {
            font-size: 1.5rem;
            margin-bottom: 1.25rem;
            color: white;
        }

        .step-description {
            color: var(--gray);
            font-size: 1.05rem;
            line-height: 1.8;
        }

        /* ===== TESTIMONIALS ===== */
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2.5rem;
        }

        .testimonial-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 1.5rem;
            padding: 3rem;
            position: relative;
            transition: all 0.4s;
            backdrop-filter: blur(20px);
        }

        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 2rem;
            right: 2.5rem;
            font-size: 8rem;
            color: rgba(99, 102, 241, 0.1);
            font-family: serif;
            line-height: 1;
        }

        .testimonial-card:hover {
            transform: translateY(-10px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
        }

        .testimonial-rating {
            color: #fbbf24;
            margin-bottom: 2rem;
            font-size: 1.25rem;
            display: flex;
            gap: 0.25rem;
        }

        .testimonial-text {
            color: var(--gray-light);
            font-size: 1.1rem;
            line-height: 1.9;
            margin-bottom: 2.5rem;
            font-style: italic;
            position: relative;
            z-index: 1;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .author-avatar {
            width: 64px;
            height: 64px;
            background: var(--gradient-main);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }

        .author-info h4 {
            color: white;
            font-size: 1.15rem;
            margin-bottom: 0.35rem;
        }

        .author-info p {
            color: var(--gray);
            font-size: 0.95rem;
        }

        /* ===== CTA SECTION ===== */
        .cta {
            padding: 10rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            background: var(--dark);
        }

        .cta-bg {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 50% 50%, rgba(99, 102, 241, 0.2) 0%, transparent 60%);
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
        }

        .cta-content {
            position: relative;
            z-index: 2;
            max-width: 900px;
            margin: 0 auto;
        }

        .cta-icon {
            font-size: 5rem;
            color: var(--primary-light);
            margin-bottom: 2.5rem;
            animation: float 5s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-25px); }
        }

        .cta-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            margin-bottom: 1.5rem;
            color: white;
        }

        .cta-subtitle {
            color: var(--gray-light);
            font-size: 1.35rem;
            margin-bottom: 4rem;
            line-height: 1.8;
        }

        /* ===== FOOTER ===== */
        .footer {
            background: #020617;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding: 6rem 2rem 3rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 5rem;
            margin-bottom: 5rem;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .footer-brand i {
            font-size: 2.5rem;
            color: var(--primary-light);
        }

        .footer-brand h3 {
            color: white;
            font-size: 2rem;
            margin: 0;
            font-weight: 900;
        }

        .footer-section h4 {
            color: white;
            margin-bottom: 2rem;
            font-size: 1.25rem;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section ul li {
            margin-bottom: 1.25rem;
        }

        .footer-section ul li a {
            color: var(--gray);
            transition: all 0.3s;
            display: inline-block;
        }

        .footer-section ul li a:hover {
            color: var(--primary-light);
            transform: translateX(8px);
        }

        .contact-info li {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            color: var(--gray);
        }

        .contact-info i {
            color: var(--primary-light);
            font-size: 1.25rem;
        }

        .footer-social {
            display: flex;
            gap: 1.25rem;
            margin-top: 2rem;
        }

        .footer-social a {
            width: 48px;
            height: 48px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
            transition: all 0.3s;
        }

        .footer-social a:hover {
            background: var(--gradient-main);
            color: white;
            transform: translateY(-5px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 3rem;
            text-align: center;
            color: var(--gray);
            font-size: 0.95rem;
        }

        .footer-bottom i {
            color: #f43f5e;
            animation: heartbeat 1.5s ease-in-out infinite;
        }

        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }

        /* ===== SCROLL TO TOP ===== */
        .scroll-top {
            position: fixed;
            bottom: 2.5rem;
            right: 2.5rem;
            width: 56px;
            height: 56px;
            background: var(--gradient-main);
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s;
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.4);
            z-index: 999;
        }

        .scroll-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .scroll-top:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 50px rgba(99, 102, 241, 0.6);
        }

        /* ===== ANIMATIONS ===== */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        [data-delay="100"] { transition-delay: 0.1s; }
        [data-delay="200"] { transition-delay: 0.2s; }
        [data-delay="300"] { transition-delay: 0.3s; }
        [data-delay="400"] { transition-delay: 0.4s; }
        [data-delay="500"] { transition-delay: 0.5s; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .features-grid,
            .testimonials-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
                gap: 3rem;
            }
        }

        @media (max-width: 768px) {
            .hero-title { font-size: 2.5rem !important; }
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 2rem; }
            .capabilities-grid { gap: 2rem; }
            .capability-item { padding: 1.25rem 2rem; }
            .section { padding: 5rem 1.5rem; }
        }

        /* ===== CUSTOM SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: var(--dark); }
        ::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary-light); }

        /* ===== SELECTION ===== */
        ::selection {
            background: var(--primary);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Premium Background Layers -->
    <div class="mesh-bg"></div>
    <div class="noise-overlay"></div>
    <div class="particles" id="particles"></div>

    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen" style="position: fixed; inset: 0; background: var(--dark); z-index: 9999; display: flex; align-items: center; justify-content: center; transition: opacity 0.6s, visibility 0.6s;">
        <div class="loading-content" style="text-align: center; color: white;">
            <div class="loading-logo" style="font-size: 5rem; color: var(--primary-light); margin-bottom: 2.5rem; animation: float 3s ease-in-out infinite; filter: drop-shadow(0 0 30px rgba(99, 102, 241, 0.6));">
                <i class="bi bi-book-half"></i>
            </div>
            <div class="loading-spinner" style="width: 60px; height: 60px; border: 4px solid rgba(99, 102, 241, 0.1); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 2rem; box-shadow: 0 0 30px rgba(99, 102, 241, 0.3);"></div>
            <p class="loading-text" style="color: var(--gray-light); font-size: 1rem; letter-spacing: 0.3em; animation: pulse 2s infinite; font-weight: 600;">MEMUAT SIMBITA</p>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar glass-panel" id="navbar" style="position: fixed; top: 0; width: 100%; z-index: 1000; padding: 1.5rem 0; transition: all 0.4s; border-bottom: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(20px);">
        <div class="navbar-container" style="max-width: 1200px; margin: 0 auto; padding: 0 2.5rem; display: flex; justify-content: space-between; align-items: center;">
            <a href="<?= BASE_URL ?>" class="navbar-brand" style="color: white; font-size: 2rem; font-weight: 900; display: flex; align-items: center; gap: 1rem;">
                <i class="bi bi-book-half" style="color: var(--primary-light); font-size: 2.5rem;"></i>
                <span>SIMBITA</span>
            </a>
            
            <ul class="navbar-menu" id="navbarMenu" style="display: flex; align-items: center; gap: 3rem; list-style: none;">
                <li><a href="#home" class="nav-link" style="color: var(--gray-light); font-weight: 600; font-size: 1rem;">Beranda</a></li>
                <li><a href="#features" class="nav-link" style="color: var(--gray-light); font-weight: 600; font-size: 1rem;">Fitur</a></li>
                <li><a href="#how-it-works" class="nav-link" style="color: var(--gray-light); font-weight: 600; font-size: 1rem;">Cara Kerja</a></li>
                <li><a href="#testimonials" class="nav-link" style="color: var(--gray-light); font-weight: 600; font-size: 1rem;">Testimoni</a></li>
                <li>
                    <a href="<?= BASE_URL ?>/login" class="btn" style="background: var(--gradient-main); color: white; padding: 0.875rem 2rem; font-size: 1rem; border-radius: 1rem; font-weight: 700; box-shadow: 0 0 30px rgba(99, 102, 241, 0.4);">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                </li>
            </ul>
            
            <div class="navbar-toggle" id="navbarToggle" style="display: none; flex-direction: column; gap: 6px; cursor: pointer; padding: 0.75rem; z-index: 1001;">
                <span style="width: 28px; height: 2.5px; background: white; transition: 0.3s; border-radius: 2px;"></span>
                <span style="width: 28px; height: 2.5px; background: white; transition: 0.3s; border-radius: 2px;"></span>
                <span style="width: 28px; height: 2.5px; background: white; transition: 0.3s; border-radius: 2px;"></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="bi bi-stars" style="font-size: 1.3rem;"></i>
                <span>Platform Akademik Generasi Baru</span>
            </div>
            
            <h1 class="hero-title">
                <span class="gradient-text">Revolusi Digital</span><br>
                <span style="color: white;">Bimbingan Tugas Akhir</span>
            </h1>
            
            <p class="hero-subtitle">
                Kelola Skripsi, Tesis, dan Disertasi dengan sistem terintegrasi. 
                Upload dokumen, anotasi interaktif, dan tracking progress real-time dalam satu ekosistem yang aman dan modern.
            </p>
            
            <div class="hero-buttons">
                <a href="<?= BASE_URL ?>/login" class="btn-primary-glow">
                    <span>Masuk ke Sistem</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
                <a href="#features" class="btn-secondary-glass">
                    <i class="bi bi-play-circle"></i>
                    <span>Pelajari Fitur</span>
                </a>
            </div>
            
            <!-- Animated Stats Showcase -->
            <div class="stats-showcase">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="stat-number-wrapper">
                            <span class="stat-number" data-target="2500">0</span><span class="stat-suffix">+</span>
                        </div>
                        <div class="stat-label">Mahasiswa Aktif</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <i class="bi bi-person-workspace"></i>
                        </div>
                        <div class="stat-number-wrapper">
                            <span class="stat-number" data-target="120">0</span><span class="stat-suffix">+</span>
                        </div>
                        <div class="stat-label">Dosen Pembimbing</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <i class="bi bi-file-earmark-check"></i>
                        </div>
                        <div class="stat-number-wrapper">
                            <span class="stat-number" data-target="8500">0</span><span class="stat-suffix">+</span>
                        </div>
                        <div class="stat-label">TA Selesai</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <i class="bi bi-heart-fill"></i>
                        </div>
                        <div class="stat-number-wrapper">
                            <span class="stat-number" data-target="99">0</span><span class="stat-suffix">%</span>
                        </div>
                        <div class="stat-label">Tingkat Kepuasan</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="scroll-indicator">
            <div class="mouse">
                <div class="wheel"></div>
            </div>
            <p>Scroll ke bawah</p>
        </div>
    </section>

    <!-- Platform Capabilities (REPLACES TRUSTED BY) -->
    <section class="capabilities">
        <div class="container">
            <div class="capabilities-header animate-on-scroll">
                <div class="capabilities-title">Platform Capabilities</div>
                <h2 style="font-size: 2.5rem; color: white; margin-bottom: 1rem;">Mengapa Memilih SIMBITA?</h2>
                <p style="color: var(--gray); font-size: 1.15rem;">Solusi lengkap untuk manajemen bimbingan akademik modern</p>
            </div>
            
            <div class="capabilities-grid">
                <div class="capability-item">
                    <div class="capability-icon">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div class="capability-text">Proses Cepat & Efisien</div>
                </div>
                <div class="capability-item">
                    <div class="capability-icon">
                        <i class="bi bi-shield-fill-check"></i>
                    </div>
                    <div class="capability-text">Keamanan Data Terjamin</div>
                </div>
                <div class="capability-item">
                    <div class="capability-icon">
                        <i class="bi bi-cloud-fill"></i>
                    </div>
                    <div class="capability-text">Akses Cloud 24/7</div>
                </div>
                <div class="capability-item">
                    <div class="capability-icon">
                        <i class="bi bi-phone-fill"></i>
                    </div>
                    <div class="capability-text">Mobile Responsive</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section features" id="features">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">Fitur Unggulan</span>
                <h2 class="section-title">
                    Semua yang Anda Butuhkan untuk<br>
                    <span class="gradient-text">Kesuksesan Akademik</span>
                </h2>
                <p class="section-subtitle">
                    Platform komprehensif dengan fitur-fitur modern untuk memudahkan proses bimbingan dari awal hingga selesai
                </p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card animate-on-scroll" data-delay="0">
                    <div class="feature-icon">
                        <i class="bi bi-cloud-upload"></i>
                    </div>
                    <h3 class="feature-title">Version Control Cerdas</h3>
                    <p class="feature-description">Upload dokumen dengan sistem versioning otomatis. Tidak ada lagi file "Revisi_Final_Banget.pdf" yang membingungkan. Setiap versi tersimpan dengan rapi dan dapat diakses kapan saja.</p>
                </div>
                
                <div class="feature-card animate-on-scroll" data-delay="100">
                    <div class="feature-icon">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <h3 class="feature-title">Anotasi PDF Interaktif</h3>
                    <p class="feature-description">Dosen dapat memberikan komentar langsung pada halaman spesifik dokumen dengan presisi koordinat X/Y. Feedback lebih jelas dan mudah dipahami.</p>
                </div>
                
                <div class="feature-card animate-on-scroll" data-delay="200">
                    <div class="feature-icon">
                        <i class="bi bi-bell"></i>
                    </div>
                    <h3 class="feature-title">Notifikasi Real-time</h3>
                    <p class="feature-description">Dapatkan pemberitahuan instan untuk review baru, deadline milestone, dan keputusan persetujuan. Tidak ada lagi informasi yang terlewat.</p>
                </div>
                
                <div class="feature-card animate-on-scroll" data-delay="300">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3 class="feature-title">Keamanan Enterprise</h3>
                    <p class="feature-description">Enkripsi password, proteksi CSRF, validasi MIME type ketat, dan penyimpanan file di luar root publik. Data Anda aman dan terlindungi.</p>
                </div>
                
                <div class="feature-card animate-on-scroll" data-delay="400">
                    <div class="feature-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h3 class="feature-title">Progress Tracking</h3>
                    <p class="feature-description">Pantau perjalanan bimbingan dari Proposal hingga Sidang dengan dashboard visual yang informatif. Lihat progress Anda secara real-time.</p>
                </div>
                
                <div class="feature-card animate-on-scroll" data-delay="500">
                    <div class="feature-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3 class="feature-title">Multi-Role Access</h3>
                    <p class="feature-description">Antarmuka yang disesuaikan secara dinamis untuk kebutuhan spesifik Admin, Dosen, dan Mahasiswa. Pengalaman pengguna yang optimal untuk setiap role.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="section how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">Alur Kerja</span>
                <h2 class="section-title">
                    Proses Bimbingan yang<br>
                    <span class="gradient-text">Sederhana & Terstruktur</span>
                </h2>
            </div>
            
            <div class="steps-container">
                <div class="step animate-on-scroll" data-delay="0">
                    <div class="step-number"><span>1</span></div>
                    <div class="step-icon"><i class="bi bi-person-check"></i></div>
                    <h3 class="step-title">Login & Validasi</h3>
                    <p class="step-description">Akses sistem menggunakan akun resmi yang diverifikasi oleh administrator kampus. Sistem autentikasi yang aman dan cepat.</p>
                </div>
                
                <div class="step animate-on-scroll" data-delay="100">
                    <div class="step-number"><span>2</span></div>
                    <div class="step-icon"><i class="bi bi-cloud-upload"></i></div>
                    <h3 class="step-title">Upload Dokumen</h3>
                    <p class="step-description">Mahasiswa mengunggah naskah sesuai jenis dokumen (Proposal, Bab 1-3, dll) dengan sistem versioning otomatis.</p>
                </div>
                
                <div class="step animate-on-scroll" data-delay="200">
                    <div class="step-number"><span>3</span></div>
                    <div class="step-icon"><i class="bi bi-chat-left-text"></i></div>
                    <h3 class="step-title">Review & Anotasi</h3>
                    <p class="step-description">Dosen memberikan koreksi langsung pada PDF dengan sistem anotasi presisi. Feedback lebih efektif dan efisien.</p>
                </div>
                
                <div class="step animate-on-scroll" data-delay="300">
                    <div class="step-number"><span>4</span></div>
                    <div class="step-icon"><i class="bi bi-check-circle"></i></div>
                    <h3 class="step-title">Revisi & Approval</h3>
                    <p class="step-description">Mahasiswa menindaklanjuti revisi hingga dosen memberikan persetujuan final. Proses yang transparan dan terstruktur.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="section testimonials" id="testimonials">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">Testimoni</span>
                <h2 class="section-title">
                    Dipercaya oleh<br>
                    <span class="gradient-text">Civitas Akademika</span>
                </h2>
            </div>
            
            <div class="testimonials-grid">
                <div class="testimonial-card animate-on-scroll" data-delay="0">
                    <div class="testimonial-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testimonial-text">"Fitur anotasi PDF-nya game changer. Saya tidak perlu lagi download-upload file berkali-kali untuk memberi catatan kecil. Sangat efisien dan menghemat waktu!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="author-info">
                            <h4>Dr. Ahmad Fauzi, M.Kom</h4>
                            <p>Dosen Pembimbing, Teknik Informatika</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card animate-on-scroll" data-delay="100">
                    <div class="testimonial-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testimonial-text">"Sistem version control-nya menyelamatkan skripsi saya. Saya bisa melihat dengan jelas perubahan dari revisi 1 ke revisi 3 tanpa bingung. Sangat membantu!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="author-info">
                            <h4>Andi Pratama</h4>
                            <p>Mahasiswa S1, Angkatan 2021</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card animate-on-scroll" data-delay="200">
                    <div class="testimonial-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testimonial-text">"Sebagai admin, dashboard monitoring-nya sangat membantu. Saya bisa melihat progress kelulusan mahasiswa secara real-time. Fitur yang sangat powerful!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="author-info">
                            <h4>Siti Aminah, S.T.</h4>
                            <p>Administrator Akademik</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta" id="contact">
        <div class="cta-bg"></div>
        <div class="cta-content animate-on-scroll">
            <div class="cta-icon">
                <i class="bi bi-rocket-takeoff"></i>
            </div>
            <h2 class="cta-title">Siap Memulai Perjalanan Akademik Anda?</h2>
            <p class="cta-subtitle">Bergabunglah dengan ekosistem bimbingan digital yang modern, aman, dan efisien. Wujudkan kesuksesan akademik Anda bersama SIMBITA.</p>
            <div class="cta-buttons" style="display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap;">
                <a href="<?= BASE_URL ?>/login" class="btn-primary-glow">
                    <span>Masuk ke Sistem</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
                <a href="mailto:admin@simbita.local" class="btn-secondary-glass">
                    <i class="bi bi-envelope"></i>
                    <span>Hubungi Kami</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-brand">
                        <i class="bi bi-book-half"></i>
                        <h3>SIMBITA</h3>
                    </div>
                    <p style="color: var(--gray); line-height: 1.8; max-width: 400px; margin-bottom: 2rem;">Sistem Informasi Bimbingan Tugas Akhir modern untuk mengelola skripsi, tesis, dan disertasi dengan teknologi terkini dan standar keamanan enterprise.</p>
                    <div class="footer-social">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Menu Cepat</h4>
                    <ul>
                        <li><a href="#home">Beranda</a></li>
                        <li><a href="#features">Fitur</a></li>
                        <li><a href="#how-it-works">Cara Kerja</a></li>
                        <li><a href="#testimonials">Testimoni</a></li>
                        <li><a href="<?= BASE_URL ?>/login">Login</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Kontak</h4>
                    <ul class="contact-info">
                        <li>
                            <i class="bi bi-geo-alt"></i>
                            <span>Maumere-Flores, Indonesia</span>
                        </li>
                        <li>
                            <i class="bi bi-envelope"></i>
                            <span>admin@simbita.local</span>
                        </li>
                        <li>
                            <i class="bi bi-telephone"></i>
                            <span>(0274) 123-4567</span>
                        </li>
                        <li>
                            <i class="bi bi-clock"></i>
                            <span>Sen - Jum: 08.00 - 16.00</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> SIMBITA. All rights reserved. Made with <i class="bi bi-heart-fill"></i> by Yolis Libman</p>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button class="scroll-top" id="scrollTop" aria-label="Scroll to top">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- JavaScript -->
    <script src="<?= BASE_URL ?>/assets/js/public.js"></script>
    
    <!-- Enhanced Interactions Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ===== 1. LOADING SCREEN =====
            const loader = document.getElementById('loadingScreen');
            if (loader) {
                window.addEventListener('load', () => {
                    setTimeout(() => {
                        loader.style.opacity = '0';
                        loader.style.visibility = 'hidden';
                        document.body.style.overflow = 'auto';
                    }, 1000);
                });
            }
            document.body.style.overflow = 'hidden';

            // ===== 2. PARTICLE SYSTEM =====
            const particlesContainer = document.getElementById('particles');
            if (particlesContainer) {
                for (let i = 0; i < 50; i++) {
                    createParticle();
                }
            }

            function createParticle() {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                const size = Math.random() * 6 + 2;
                const posX = Math.random() * 100;
                const duration = Math.random() * 20 + 15;
                const delay = Math.random() * 5;
                
                particle.style.cssText = `
                    width: ${size}px;
                    height: ${size}px;
                    left: ${posX}%;
                    animation-duration: ${duration}s;
                    animation-delay: ${delay}s;
                    opacity: ${Math.random() * 0.5 + 0.1};
                `;
                
                particlesContainer.appendChild(particle);
            }

            // ===== 3. NAVBAR SCROLL EFFECT =====
            const navbar = document.getElementById('navbar');
            let lastScrollY = window.scrollY;

            window.addEventListener('scroll', () => {
                const currentScrollY = window.scrollY;
                
                if (currentScrollY > 50) {
                    navbar.style.background = 'rgba(15, 23, 42, 0.9)';
                    navbar.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.2)';
                } else {
                    navbar.style.background = 'rgba(15, 23, 42, 0.7)';
                    navbar.style.boxShadow = 'none';
                }
                
                lastScrollY = currentScrollY;
            }, { passive: true });

            // ===== 4. SCROLL ANIMATIONS =====
            const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -80px 0px' };
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        
                        if (entry.target.querySelector('.stat-number')) {
                            animateCounters();
                        }
                        
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });

            // ===== 5. COUNTER ANIMATION =====
            let countersAnimated = false;
            
            function animateCounters() {
                if (countersAnimated) return;
                countersAnimated = true;
                
                document.querySelectorAll('.stat-number').forEach((counter, index) => {
                    const target = parseInt(counter.getAttribute('data-target'));
                    const duration = 2500;
                    const delay = index * 300;
                    const startTime = performance.now() + delay;
                    
                    const animate = (currentTime) => {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        const easeOut = 1 - Math.pow(1 - progress, 4);
                        const current = Math.floor(easeOut * target);
                        
                        counter.textContent = current.toLocaleString('id-ID');
                        
                        if (progress < 1) {
                            requestAnimationFrame(animate);
                        } else {
                            counter.textContent = target.toLocaleString('id-ID');
                        }
                    };
                    
                    requestAnimationFrame(animate);
                });
            }

            // ===== 6. SPOTLIGHT EFFECT =====
            document.querySelectorAll('.feature-card, .testimonial-card').forEach(card => {
                card.addEventListener('mousemove', (e) => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    card.style.setProperty('--mouse-x', `${x}px`);
                    card.style.setProperty('--mouse-y', `${y}px`);
                });
            });

            // ===== 7. SCROLL TO TOP =====
            const scrollTopBtn = document.getElementById('scrollTop');
            
            window.addEventListener('scroll', () => {
                if (window.scrollY > 500) {
                    scrollTopBtn.classList.add('visible');
                } else {
                    scrollTopBtn.classList.remove('visible');
                }
            }, { passive: true });

            scrollTopBtn.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });

            // ===== 8. SMOOTH SCROLL =====
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        const navbarHeight = navbar.offsetHeight;
                        const targetPosition = targetElement.offsetTop - navbarHeight;
                        
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // ===== 9. PARALLAX EFFECT =====
            const heroContent = document.querySelector('.hero-content');
            if (heroContent) {
                window.addEventListener('scroll', () => {
                    const scrolled = window.scrollY;
                    const heroHeight = document.querySelector('.hero').offsetHeight;
                    
                    if (scrolled < heroHeight) {
                        heroContent.style.transform = `translateY(${scrolled * 0.3}px)`;
                        heroContent.style.opacity = 1 - (scrolled / heroHeight);
                    }
                }, { passive: true });
            }

            // ===== 10. CONSOLE EASTER EGG =====
            console.log('%c🚀 SIMBITA', 'color: #6366f1; font-size: 3rem; font-weight: bold;');
            console.log('%cSistem Informasi Bimbingan Tugas Akhir', 'color: #94a3b8; font-size: 1.2rem;');
            console.log('%cMade with ❤️ by Yolis Libman', 'color: #f43f5e; font-size: 1rem;');
            console.log('%c World Class Landing Page', 'color: #fbbf24; font-size: 1rem; font-weight: bold;');
        });

        // Inject keyframes
        const styleSheet = document.createElement("style");
        styleSheet.innerText = `
            @keyframes spin { to { transform: rotate(360deg); } }
            @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        `;
        document.head.appendChild(styleSheet);
    </script>
</body>
</html>