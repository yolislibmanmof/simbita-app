<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMBITA - Revolusi Digital Bimbingan Tugas Akhir</title>
    <meta name="description" content="Platform modern untuk mengelola bimbingan Skripsi, Tesis, dan Disertasi dengan teknologi terkini">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/public.css" rel="stylesheet">

    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --accent: #06b6d4;
            --accent-light: #22d3ee;
            --dark: #030712;
            --dark-light: #0f172a;
            --dark-lighter: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --gray-light: #94a3b8;
            --gradient-main: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);
            --gradient-text: linear-gradient(135deg, #818cf8 0%, #c084fc 50%, #22d3ee 100%);
            --gradient-glow: linear-gradient(135deg, rgba(99, 102, 241, 0.5), rgba(139, 92, 246, 0.5), rgba(6, 182, 212, 0.5));
            --glass-bg: rgba(15, 23, 42, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --transition-smooth: cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);
            --transition-spring: cubic-bezier(0.16, 1, 0.3, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; scroll-padding-top: 100px; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: var(--light);
            overflow-x: hidden;
            line-height: 1.6;
            cursor: none;
        }

        @media (max-width: 768px) { body { cursor: auto; } }

        h1, h2, h3, h4 { font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; }
        a { text-decoration: none; color: inherit; transition: all 0.3s var(--transition-smooth); cursor: none; }
        button { cursor: none; }

        /* ===== CUSTOM CURSOR (GPU-FRIENDLY, TRANSFORM-BASED) ===== */
        .cursor-dot,
        .cursor-outline {
            position: fixed;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 99999;
            border-radius: 50%;
            will-change: transform;
            transform: translate3d(-200px, -200px, 0);
        }

        .cursor-dot {
            width: 8px;
            height: 8px;
            margin: -4px 0 0 -4px;
            background: #ffffff;
            box-shadow: 0 0 12px rgba(255, 255, 255, 0.8);
            transition: opacity 0.25s ease, background 0.25s ease;
        }

        .cursor-outline {
            width: 40px;
            height: 40px;
            margin: -20px 0 0 -20px;
            border: 1.5px solid rgba(139, 92, 246, 0.85);
            background: rgba(99, 102, 241, 0.06);
            box-shadow: 0 0 25px rgba(99, 102, 241, 0.25), inset 0 0 12px rgba(99, 102, 241, 0.15);
            transition: width 0.25s var(--transition-spring), height 0.25s var(--transition-spring), margin 0.25s var(--transition-spring), border-color 0.25s ease, background 0.25s ease, opacity 0.25s ease;
        }

        .cursor-dot.hovered { opacity: 0; }

        .cursor-outline.hovered {
            width: 68px;
            height: 68px;
            margin: -34px 0 0 -34px;
            background: rgba(99, 102, 241, 0.12);
            border-color: var(--primary-light);
        }

        /* ===== ULTIMATE LOADING SCREEN ===== */
        .loading-screen {
            position: fixed;
            inset: 0;
            background: var(--dark);
            z-index: 100000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: opacity 1s var(--transition-smooth), visibility 1s;
            overflow: hidden;
        }

        .loading-screen::before {
            content: '';
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(circle at 20% 30%, rgba(99, 102, 241, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(139, 92, 246, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.15) 0%, transparent 60%);
            filter: blur(80px);
            animation: meshPulse 4s ease-in-out infinite;
        }

        .loading-screen::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.04'/%3E%3C/svg%3E");
            opacity: 0.5;
            pointer-events: none;
        }

        @keyframes meshPulse {
            0%, 100% { opacity: 0.6; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.1); }
        }

        .loading-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            width: 90%;
            max-width: 500px;
        }

        .loading-logo-container {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 3rem;
        }

        .loading-logo-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid transparent;
            border-top-color: var(--primary-light);
            border-right-color: var(--accent);
            animation: spinRing 2s linear infinite;
        }

        .loading-logo-ring:nth-child(2) {
            inset: 15px;
            border-top-color: var(--secondary);
            border-left-color: var(--primary-light);
            animation: spinRingReverse 2.5s linear infinite;
        }

        .loading-logo-ring:nth-child(3) {
            inset: 30px;
            border-bottom-color: var(--accent);
            border-right-color: var(--secondary);
            animation: spinRing 3s linear infinite;
        }

        @keyframes spinRing { to { transform: rotate(360deg); } }
        @keyframes spinRingReverse { to { transform: rotate(-360deg); } }

        .loading-logo-icon {
            position: absolute;
            inset: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gradient-main);
            border-radius: 50%;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 0 60px rgba(99, 102, 241, 0.6), inset 0 0 30px rgba(255, 255, 255, 0.2);
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-10px) scale(1.05); }
        }

        .loading-brand {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: 0.3em;
            margin-bottom: 2.5rem;
            background: var(--gradient-text);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
        }

        .loading-brand::after {
            content: 'SIMBITA';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            background: var(--gradient-text);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            opacity: 0;
            animation: glitch 3s infinite;
        }

        @keyframes glitch {
            0%, 90%, 100% { opacity: 0; transform: translate(0); }
            92% { opacity: 0.8; transform: translate(-2px, 1px); }
            94% { opacity: 0.8; transform: translate(2px, -1px); }
            96% { opacity: 0.8; transform: translate(-1px, -2px); }
            98% { opacity: 0.8; transform: translate(1px, 2px); }
        }

        .loading-progress-wrapper {
            position: relative;
            width: 100%;
            margin-bottom: 2rem;
        }

        .loading-progress-bar {
            width: 100%;
            height: 4px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 2px;
            overflow: hidden;
            position: relative;
        }

        .loading-progress-fill {
            height: 100%;
            width: 0%;
            background: var(--gradient-main);
            border-radius: 2px;
            transition: width 0.3s var(--transition-smooth);
            position: relative;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.8);
        }

        .loading-progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 30px;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(200%); }
        }

        .loading-percentage {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.9rem;
            color: var(--gray-light);
            letter-spacing: 0.1em;
        }

        .loading-percent-number {
            font-size: 1.25rem;
            font-weight: 600;
            background: var(--gradient-text);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .loading-status {
            font-size: 0.85rem;
            color: var(--gray-light);
            letter-spacing: 0.2em;
            text-transform: uppercase;
            margin-top: 1rem;
            min-height: 1.5rem;
            font-family: 'JetBrains Mono', monospace;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .loading-status-dot {
            width: 8px;
            height: 8px;
            background: var(--primary-light);
            border-radius: 50%;
            animation: statusPulse 1s ease-in-out infinite;
            box-shadow: 0 0 10px var(--primary-light);
        }

        @keyframes statusPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.3; transform: scale(0.8); }
        }

        .loading-steps {
            display: flex;
            justify-content: space-between;
            margin-top: 3rem;
            gap: 0.5rem;
        }

        .loading-step {
            flex: 1;
            text-align: center;
            opacity: 0.3;
            transition: all 0.5s var(--transition-smooth);
        }

        .loading-step.active {
            opacity: 1;
        }

        .loading-step.completed {
            opacity: 1;
        }

        .loading-step-icon {
            width: 40px;
            height: 40px;
            margin: 0 auto 0.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
            font-size: 1.1rem;
            transition: all 0.5s var(--transition-smooth);
        }

        .loading-step.active .loading-step-icon {
            background: var(--gradient-main);
            border-color: transparent;
            color: white;
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.6);
            transform: scale(1.1);
        }

        .loading-step.completed .loading-step-icon {
            background: var(--accent);
            border-color: transparent;
            color: white;
        }

        .loading-step-label {
            font-size: 0.7rem;
            color: var(--gray);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            transition: color 0.5s;
        }

        .loading-step.active .loading-step-label,
        .loading-step.completed .loading-step-label {
            color: var(--light);
        }

        .loading-particles {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 1;
        }

        .loading-particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: var(--primary-light);
            border-radius: 50%;
            opacity: 0.6;
            animation: loadParticle 10s linear infinite;
        }

        @keyframes loadParticle {
            0% { transform: translate(0, 0) scale(0); opacity: 0; }
            10% { opacity: 0.6; transform: scale(1); }
            90% { opacity: 0.6; }
            100% { transform: translate(var(--tx), var(--ty)) scale(0); opacity: 0; }
        }

        .loading-screen.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .loading-burst {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 3;
        }

        .burst-particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: var(--gradient-main);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: burst 1.2s var(--transition-spring) forwards;
        }

        @keyframes burst {
            0% { transform: translate(-50%, -50%) scale(0); opacity: 1; }
            100% { transform: translate(var(--bx), var(--by)) scale(1); opacity: 0; }
        }

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
            will-change: transform;
        }

        @keyframes meshMove {
            0%, 100% { transform: scale(1) rotate(0deg); }
            33% { transform: scale(1.1) rotate(5deg); }
            66% { transform: scale(0.95) rotate(-5deg); }
        }

        .noise-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            z-index: -1;
            opacity: 0.03;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
        }

        .particles {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            pointer-events: none;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            background: rgba(99, 102, 241, 0.5);
            border-radius: 50%;
            animation: floatParticle linear infinite;
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
        }

        @keyframes floatParticle {
            0% { transform: translateY(100vh) translateX(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) translateX(100px); opacity: 0; }
        }

        /* ===== NAVBAR PREMIUM ===== */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            padding: 1.5rem 0;
            transition: all 0.5s var(--transition-smooth);
            background: rgba(3, 7, 18, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .navbar.scrolled {
            padding: 1rem 0;
            background: rgba(3, 7, 18, 0.9);
            border-bottom: 1px solid var(--glass-border);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            color: white;
            font-size: 1.75rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .navbar-brand-icon {
            position: relative;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gradient-main);
            border-radius: 12px;
            font-size: 1.5rem;
            color: white;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
            transition: transform 0.4s var(--transition-bounce);
        }

        .navbar-brand:hover .navbar-brand-icon {
            transform: rotate(-10deg) scale(1.1);
        }

        .navbar-brand-icon::before {
            content: '';
            position: absolute;
            inset: -3px;
            background: var(--gradient-main);
            border-radius: 14px;
            z-index: -1;
            opacity: 0.5;
            filter: blur(10px);
        }

        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 2.5rem;
            list-style: none;
        }

        .nav-link {
            color: var(--gray-light);
            font-weight: 600;
            font-size: 0.95rem;
            position: relative;
            padding: 0.5rem 0;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--gradient-main);
            transition: all 0.4s var(--transition-smooth);
            transform: translateX(-50%);
        }

        .nav-link:hover, .nav-link.active {
            color: white;
        }

        .nav-link:hover::after, .nav-link.active::after {
            width: 100%;
        }

        .btn-login {
            background: var(--gradient-main);
            color: white;
            padding: 0.75rem 1.75rem;
            font-size: 0.95rem;
            border-radius: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
            position: relative;
            overflow: hidden;
            transition: all 0.3s var(--transition-smooth);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s;
        }

        .btn-login:hover::before { left: 100%; }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.6);
        }

        .navbar-toggle {
            display: none;
            flex-direction: column;
            gap: 6px;
            cursor: pointer;
            padding: 0.75rem;
            z-index: 1001;
        }

        .navbar-toggle span {
            width: 28px;
            height: 2.5px;
            background: white;
            transition: 0.3s;
            border-radius: 2px;
        }

        /* ===== HERO SECTION ENHANCED ===== */
        .hero {
            min-height: 100vh;
            padding: 10rem 2rem 6rem;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-content {
            max-width: 1200px;
            text-align: center;
            position: relative;
            z-index: 10;
        }

        .hero-text {
            position: relative;
            z-index: 2;
            will-change: transform, opacity;
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
            animation: fadeInDown 1s var(--transition-spring) both;
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.2);
            position: relative;
            overflow: hidden;
        }

        .hero-badge::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shimmer 3s infinite;
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
            animation: fadeInUp 1s var(--transition-spring) 0.2s both;
        }

        .gradient-text {
            background: var(--gradient-text);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
            position: relative;
            background-size: 200% auto;
            animation: gradientShift 5s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .text-reveal {
            display: inline-block;
            overflow: hidden;
            vertical-align: top;
        }

        .text-reveal span {
            display: inline-block;
            animation: revealUp 1s var(--transition-spring) both;
        }

        @keyframes revealUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .gradient-text::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 0;
            height: 4px;
            background: var(--gradient-main);
            border-radius: 2px;
            animation: slideIn 1s var(--transition-spring) 0.8s both;
        }

        @keyframes slideIn {
            from { width: 0; }
            to { width: 100%; }
        }

        .hero-subtitle {
            font-size: 1.35rem;
            color: var(--gray-light);
            max-width: 750px;
            margin: 0 auto 3.5rem;
            line-height: 1.8;
            animation: fadeInUp 1s var(--transition-spring) 0.4s both;
        }

        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            margin-bottom: 6rem;
            flex-wrap: wrap;
            animation: fadeInUp 1s var(--transition-spring) 0.6s both;
        }

        .btn-primary-glow {
            padding: 1.25rem 3rem;
            background: var(--gradient-main);
            color: white;
            border-radius: 1rem;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 0 40px rgba(99, 102, 241, 0.5);
            transition: all 0.4s var(--transition-smooth);
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn-primary-glow::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .btn-primary-glow:hover::before { left: 100%; }

        .btn-primary-glow:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(99, 102, 241, 0.8);
        }

        .btn-primary-glow i {
            transition: transform 0.3s;
        }

        .btn-primary-glow:hover i {
            transform: translateX(5px);
        }

        .btn-secondary-glass {
            padding: 1.25rem 3rem;
            background: rgba(255,255,255,0.05);
            color: white;
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 1rem;
            font-weight: 600;
            font-size: 1.1rem;
            backdrop-filter: blur(10px);
            transition: all 0.4s var(--transition-smooth);
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            overflow: hidden;
        }

        .btn-secondary-glass::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-main);
            opacity: 0;
            transition: opacity 0.4s;
            z-index: -1;
        }

        .btn-secondary-glass:hover::before { opacity: 0.15; }

        .btn-secondary-glass:hover {
            border-color: var(--primary-light);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }

        /* ===== PREVIEW SECTION (STANDALONE) ===== */
        .preview-section {
            padding: 2rem 2rem 8rem;
            position: relative;
        }

        /* ===== LIVE APP PREVIEW (REPLACES STATS) ===== */
        .app-preview {
            position: relative;
            max-width: 1000px;
            margin: 0 auto;
            perspective: 1500px;
        }

        .preview-glow {
            position: absolute;
            inset: 10% -5% -10% -5%;
            background: var(--gradient-glow);
            filter: blur(80px);
            z-index: -1;
            opacity: 0.35;
            animation: pulse 5s ease-in-out infinite;
        }

        .app-window {
            position: relative;
            background: rgba(10, 15, 30, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.25rem;
            overflow: hidden;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.6), 0 0 60px rgba(99, 102, 241, 0.15);
            transform-style: preserve-3d;
            transition: transform 0.4s var(--transition-smooth);
        }

        .app-window::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary-light), var(--accent), transparent);
            z-index: 2;
        }

        .app-titlebar {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.9rem 1.25rem;
            background: rgba(255, 255, 255, 0.03);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .window-dots { display: flex; gap: 0.5rem; }
        .dot { width: 12px; height: 12px; border-radius: 50%; }
        .dot-red { background: #f43f5e; box-shadow: 0 0 10px rgba(244, 63, 94, 0.6); }
        .dot-yellow { background: #fbbf24; box-shadow: 0 0 10px rgba(251, 191, 36, 0.5); }
        .dot-green { background: #22c55e; box-shadow: 0 0 10px rgba(34, 197, 94, 0.5); }

        .window-url {
            flex: 1;
            max-width: 420px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 2rem;
            padding: 0.4rem 1rem;
            font-size: 0.8rem;
            color: var(--gray-light);
            font-family: 'JetBrains Mono', monospace;
        }

        .window-url i { color: #22c55e; font-size: 0.75rem; }

        .window-actions { display: flex; gap: 0.9rem; color: var(--gray); font-size: 0.8rem; }

        .app-body {
            display: grid;
            grid-template-columns: 210px 1fr;
            min-height: 420px;
        }

        .app-sidebar {
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            padding: 1.25rem 0.9rem;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            background: rgba(255, 255, 255, 0.015);
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.7rem 0.9rem;
            border-radius: 0.75rem;
            color: var(--gray-light);
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s var(--transition-smooth);
            position: relative;
        }

        .sidebar-item i { font-size: 1.05rem; }
        .sidebar-item:hover { background: rgba(255, 255, 255, 0.05); color: white; }

        .sidebar-item.active {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.25), rgba(139, 92, 246, 0.15));
            color: white;
            border: 1px solid rgba(99, 102, 241, 0.3);
        }

        .sidebar-item.active::before {
            content: '';
            position: absolute;
            left: -0.9rem;
            top: 20%;
            bottom: 20%;
            width: 3px;
            background: var(--gradient-main);
            border-radius: 2px;
        }

        .app-main {
            padding: 1.5rem 1.75rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .app-main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .app-main-header h4 { color: white; font-size: 1.15rem; margin-bottom: 0.2rem; }
        .app-main-header p { color: var(--gray); font-size: 0.85rem; }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .pill-progress {
            background: rgba(99, 102, 241, 0.15);
            color: var(--primary-light);
            border: 1px solid rgba(99, 102, 241, 0.3);
        }

        .pill-progress i { animation: spinRing 2s linear infinite; }

        .doc-rows { display: flex; flex-direction: column; gap: 0.75rem; }

        .doc-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.9rem 1rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 0.9rem;
            transition: all 0.3s var(--transition-smooth);
        }

        .doc-row:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(99, 102, 241, 0.35);
            transform: translateX(6px);
        }

        .doc-icon {
            width: 44px;
            height: 44px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .icon-done { background: rgba(34, 197, 94, 0.15); color: #4ade80; }
        .icon-review { background: rgba(251, 191, 36, 0.15); color: #fbbf24; }
        .icon-revision { background: rgba(244, 63, 94, 0.15); color: #fb7185; }

        .doc-info { flex: 1; display: flex; flex-direction: column; gap: 0.15rem; min-width: 0; }
        .doc-name { color: white; font-weight: 600; font-size: 0.95rem; }
        .doc-meta { color: var(--gray); font-size: 0.8rem; }

        .doc-status {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.85rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-approved { background: rgba(34, 197, 94, 0.12); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
        .status-review { background: rgba(251, 191, 36, 0.12); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3); }
        .status-revision { background: rgba(244, 63, 94, 0.12); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); }
        .status-review i { animation: statusPulse 1.6s ease-in-out infinite; }

        .app-progress { margin-top: auto; }

        .progress-head {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: var(--gray-light);
            margin-bottom: 0.6rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .progress-value { color: var(--primary-light); font-weight: 600; }

        .progress-track {
            height: 8px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar-animated {
            height: 100%;
            width: 0%;
            background: var(--gradient-main);
            border-radius: 4px;
            transition: width 1.8s var(--transition-spring) 0.4s;
            position: relative;
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.6);
        }

        .app-preview.visible .progress-bar-animated { width: 60%; }

        .progress-bar-animated::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: shimmer 2s infinite;
        }

        .milestone-row {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            position: relative;
        }

        .milestone-row::before {
            content: '';
            position: absolute;
            top: 9px;
            left: 8%;
            right: 8%;
            height: 2px;
            background: rgba(255, 255, 255, 0.08);
        }

        .milestone {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.45rem;
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .milestone-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--dark-lighter);
            border: 2px solid rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            color: transparent;
            transition: all 0.4s;
        }

        .milestone.done .milestone-dot {
            background: var(--gradient-main);
            border-color: transparent;
            color: white;
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.5);
        }

        .milestone.active .milestone-dot {
            border-color: var(--primary-light);
            animation: milestonePulse 2s ease-in-out infinite;
        }

        @keyframes milestonePulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.5); }
            50% { box-shadow: 0 0 0 8px rgba(99, 102, 241, 0); }
        }

        .milestone-label {
            font-size: 0.72rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .milestone.done .milestone-label,
        .milestone.active .milestone-label { color: var(--gray-light); }

        .preview-toast {
            position: absolute;
            z-index: 5;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.9rem 1.25rem;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 1rem;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            animation: toastFloat 6s ease-in-out infinite;
        }

        .toast-1 { top: -28px; right: -20px; }
        .toast-2 { bottom: -26px; left: -20px; animation-delay: 3s; }

        @keyframes toastFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        .toast-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .toast-icon-1 { background: var(--gradient-main); color: white; }
        .toast-icon-2 { background: rgba(251, 191, 36, 0.15); color: #fbbf24; }

        .toast-body { display: flex; flex-direction: column; gap: 0.1rem; text-align: left; }
        .toast-title { color: white; font-size: 0.85rem; font-weight: 600; }
        .toast-desc { color: var(--gray); font-size: 0.75rem; }

        @media (max-width: 900px) {
            .app-body { grid-template-columns: 1fr; }
            .app-sidebar { display: none; }
            .toast-1 { right: 0; top: -20px; }
            .toast-2 { left: 0; bottom: -20px; }
        }

        @media (max-width: 560px) {
            .doc-row { flex-wrap: wrap; }
            .milestone-label { display: none; }
            .app-main { padding: 1.25rem 1rem; }
        }

        /* ===== MARQUEE / INFINITE SCROLL ===== */
        .marquee-section {
            padding: 3rem 0;
            border-top: 1px solid rgba(255,255,255,0.05);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            background: rgba(255,255,255,0.01);
            overflow: hidden;
        }

        .marquee-wrapper {
            display: flex;
            gap: 3rem;
            animation: marqueeScroll 30s linear infinite;
            width: max-content;
        }

        .marquee-wrapper:hover { animation-play-state: paused; }

        @keyframes marqueeScroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .marquee-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 2rem;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 3rem;
            white-space: nowrap;
            transition: all 0.3s;
        }

        .marquee-item:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: rgba(99, 102, 241, 0.3);
            transform: scale(1.05);
        }

        .marquee-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-main);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
        }

        .marquee-text {
            font-weight: 600;
            color: var(--light);
            font-size: 1rem;
        }

        /* ===== PLATFORM CAPABILITIES ===== */
        .capabilities {
            padding: 6rem 2rem;
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
            color: var(--primary-light);
            margin-bottom: 1rem;
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 2rem;
        }

        .capabilities-heading {
            font-size: clamp(2rem, 5vw, 3rem);
            color: white;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .capabilities-subheading {
            color: var(--gray-light);
            font-size: 1.15rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .capabilities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            max-width: 1100px;
            margin: 0 auto;
        }

        .capability-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.75rem 2rem;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 1rem;
            transition: all 0.4s var(--transition-smooth);
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
            transform-style: preserve-3d;
        }

        .capability-item::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(400px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(99, 102, 241, 0.15), transparent 40%);
            opacity: 0;
            transition: opacity 0.4s;
        }

        .capability-item:hover::before { opacity: 1; }

        .capability-item:hover {
            background: rgba(255,255,255,0.05);
            border-color: rgba(99, 102, 241, 0.4);
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.2);
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
            flex-shrink: 0;
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
            transition: transform 0.4s var(--transition-bounce);
        }

        .capability-item:hover .capability-icon {
            transform: rotate(-10deg) scale(1.1);
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
            color: var(--gray-light);
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.1em;
            animation: bounce 2s ease-in-out infinite;
            cursor: pointer;
        }

        .mouse {
            width: 28px;
            height: 44px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 14px;
            position: relative;
            background: rgba(255,255,255,0.02);
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
            box-shadow: 0 0 10px var(--primary-light);
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
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-40px); }
            to { opacity: 1; transform: translateY(0); }
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
            position: relative;
            overflow: hidden;
        }

        .section-badge::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shimmer 3s infinite;
        }

        .section-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            margin-bottom: 1.5rem;
            color: white;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }

        .section-subtitle {
            color: var(--gray-light);
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
            transition: all 0.4s var(--transition-smooth);
            backdrop-filter: blur(20px);
            transform-style: preserve-3d;
            will-change: transform;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(800px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(99, 102, 241, 0.15), transparent 40%);
            opacity: 0;
            transition: opacity 0.4s;
            pointer-events: none;
        }

        .feature-card::after {
            content: '';
            position: absolute;
            top: -2px; left: -2px; right: -2px; bottom: -2px;
            background: linear-gradient(135deg, transparent, rgba(99, 102, 241, 0.3), transparent);
            border-radius: 1.5rem;
            opacity: 0;
            transition: opacity 0.4s;
            z-index: -1;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.4), 0 0 40px rgba(99, 102, 241, 0.2);
        }

        .feature-card:hover::before { opacity: 1; }
        .feature-card:hover::after { opacity: 1; }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary-light);
            margin-bottom: 2rem;
            transition: all 0.4s var(--transition-bounce);
            position: relative;
            z-index: 1;
        }

        .feature-card:hover .feature-icon {
            background: var(--gradient-main);
            color: white;
            transform: scale(1.15) rotate(-8deg);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.5);
            border-color: transparent;
        }

        .feature-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: white;
            position: relative;
            z-index: 1;
        }

        .feature-description {
            color: var(--gray-light);
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
            position: relative;
        }

        .steps-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 3rem;
            position: relative;
        }

        .steps-container::before {
            content: '';
            position: absolute;
            top: 60px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--primary), var(--secondary), var(--accent), transparent);
            opacity: 0.3;
            z-index: 1;
        }

        @media (max-width: 768px) { .steps-container::before { display: none; } }

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
            box-shadow: 0 0 40px rgba(99, 102, 241, 0.3), inset 0 0 20px rgba(99, 102, 241, 0.1);
            transition: all 0.5s var(--transition-bounce);
            position: relative;
            overflow: hidden;
            font-family: 'Plus Jakarta Sans', sans-serif;
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
            box-shadow: 0 0 60px rgba(99, 102, 241, 0.7);
        }

        .step:hover .step-number::before { opacity: 1; }

        .step-number span { position: relative; z-index: 2; }

        .step-icon {
            font-size: 3.5rem;
            color: var(--primary-light);
            margin: 2rem 0 2rem;
            transition: all 0.4s var(--transition-bounce);
        }

        .step:hover .step-icon {
            transform: scale(1.25);
            color: var(--accent);
            filter: drop-shadow(0 0 20px var(--accent));
        }

        .step-title {
            font-size: 1.5rem;
            margin-bottom: 1.25rem;
            color: white;
        }

        .step-description {
            color: var(--gray-light);
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
            transition: all 0.4s var(--transition-smooth);
            backdrop-filter: blur(20px);
            transform-style: preserve-3d;
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
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.4), 0 0 40px rgba(99, 102, 241, 0.2);
        }

        .testimonial-rating {
            color: #fbbf24;
            margin-bottom: 2rem;
            font-size: 1.25rem;
            display: flex;
            gap: 0.25rem;
        }

        .testimonial-rating i {
            animation: starPulse 2s ease-in-out infinite;
        }

        .testimonial-rating i:nth-child(1) { animation-delay: 0s; }
        .testimonial-rating i:nth-child(2) { animation-delay: 0.2s; }
        .testimonial-rating i:nth-child(3) { animation-delay: 0.4s; }
        .testimonial-rating i:nth-child(4) { animation-delay: 0.6s; }
        .testimonial-rating i:nth-child(5) { animation-delay: 0.8s; }

        @keyframes starPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
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
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
            position: relative;
            transition: transform 0.3s;
        }

        .testimonial-card:hover .author-avatar {
            transform: scale(1.1) rotate(-5deg);
        }

        .author-avatar::before {
            content: '';
            position: absolute;
            inset: -4px;
            background: var(--gradient-main);
            border-radius: 50%;
            z-index: -1;
            opacity: 0.4;
            filter: blur(10px);
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
            background: radial-gradient(circle at 50% 50%, rgba(99, 102, 241, 0.3) 0%, transparent 60%);
            animation: pulse 4s ease-in-out infinite;
        }

        .cta-bg::before, .cta-bg::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.5;
        }

        .cta-bg::before {
            background: var(--primary);
            top: 20%;
            left: 10%;
            animation: floatBlob1 15s ease-in-out infinite;
        }

        .cta-bg::after {
            background: var(--accent);
            bottom: 20%;
            right: 10%;
            animation: floatBlob2 18s ease-in-out infinite;
        }

        @keyframes floatBlob1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(100px, -50px) scale(1.2); }
        }

        @keyframes floatBlob2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-100px, 50px) scale(1.3); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.8; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.05); }
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
            filter: drop-shadow(0 0 30px rgba(99, 102, 241, 0.6));
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-25px) rotate(5deg); }
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

        .cta-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* ===== FOOTER ===== */
        .footer {
            background: #020617;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding: 6rem 2rem 3rem;
            position: relative;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
        }

        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 5rem;
            margin-bottom: 5rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .footer-brand-icon {
            width: 56px;
            height: 56px;
            background: var(--gradient-main);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }

        .footer-brand h3 {
            color: white;
            font-size: 2rem;
            margin: 0;
            font-weight: 900;
        }

        .footer-section-description {
            color: var(--gray-light);
            line-height: 1.8;
            max-width: 400px;
            margin-bottom: 2rem;
        }

        .footer-section h4 {
            color: white;
            margin-bottom: 2rem;
            font-size: 1.25rem;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .footer-section h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--gradient-main);
            border-radius: 2px;
        }

        .footer-section ul { list-style: none; padding: 0; }
        .footer-section ul li { margin-bottom: 1.25rem; }

        .footer-section ul li a {
            color: var(--gray-light);
            transition: all 0.3s var(--transition-smooth);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-section ul li a::before {
            content: '';
            width: 0;
            height: 2px;
            background: var(--primary-light);
            transition: width 0.3s;
        }

        .footer-section ul li a:hover {
            color: var(--primary-light);
            transform: translateX(8px);
        }

        .footer-section ul li a:hover::before { width: 15px; }

        .contact-info li {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            color: var(--gray-light);
        }

        .contact-info i {
            color: var(--primary-light);
            font-size: 1.25rem;
            width: 24px;
        }

        .footer-social {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .footer-social a {
            width: 48px;
            height: 48px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-light);
            font-size: 1.15rem;
            transition: all 0.3s var(--transition-smooth);
            position: relative;
            overflow: hidden;
        }

        .footer-social a::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-main);
            opacity: 0;
            transition: opacity 0.3s;
            z-index: -1;
        }

        .footer-social a:hover {
            color: white;
            border-color: transparent;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);
        }

        .footer-social a:hover::before { opacity: 1; }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 3rem;
            text-align: center;
            color: var(--gray-light);
            font-size: 0.95rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-bottom i {
            color: #f43f5e;
            animation: heartbeat 1.5s ease-in-out infinite;
        }

        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
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
            transition: all 0.4s var(--transition-smooth);
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.4);
            z-index: 999;
        }

        .scroll-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .scroll-top:hover {
            transform: translateY(-8px) rotate(-10deg);
            box-shadow: 0 15px 50px rgba(99, 102, 241, 0.7);
        }

        /* ===== SCROLL ANIMATIONS ===== */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 1s var(--transition-spring), transform 1s var(--transition-spring);
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        [data-delay="0"] { transition-delay: 0s; }
        [data-delay="100"] { transition-delay: 0.1s; }
        [data-delay="200"] { transition-delay: 0.2s; }
        [data-delay="300"] { transition-delay: 0.3s; }
        [data-delay="400"] { transition-delay: 0.4s; }
        [data-delay="500"] { transition-delay: 0.5s; }
        [data-delay="600"] { transition-delay: 0.6s; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .features-grid, .testimonials-grid { grid-template-columns: 1fr; }
            .footer-content { grid-template-columns: 1fr; gap: 3rem; }
        }

        @media (max-width: 768px) {
            .navbar-menu {
                position: fixed;
                top: 0;
                right: -100%;
                width: 85%;
                max-width: 400px;
                height: 100vh;
                background: rgba(3, 7, 18, 0.98);
                backdrop-filter: blur(20px);
                flex-direction: column;
                padding: 6rem 2rem 2rem;
                transition: right 0.4s var(--transition-smooth);
                box-shadow: -10px 0 40px rgba(0, 0, 0, 0.5);
                gap: 1.5rem;
                align-items: flex-start;
            }
            .navbar-menu.active { right: 0; }
            .nav-link { font-size: 1.125rem; width: 100%; padding: 1rem; border-radius: 0.75rem; }
            .nav-link:hover { background: rgba(99, 102, 241, 0.1); }
            .navbar-toggle { display: flex; }
            .hero-title { font-size: 2.5rem !important; }
            .capabilities-grid { grid-template-columns: 1fr; }
            .section { padding: 5rem 1.5rem; }
            .loading-brand { font-size: 1.75rem; }
            .loading-steps { flex-direction: column; gap: 1rem; }
            .steps-container::before { display: none; }
        }

        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: var(--dark); }
        ::-webkit-scrollbar-thumb { background: var(--gradient-main); border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary-dark); }
        ::selection { background: var(--primary); color: white; }
    </style>
</head>
<body>
    <!-- Custom Cursor -->
    <div class="cursor-dot" id="cursorDot"></div>
    <div class="cursor-outline" id="cursorOutline"></div>

    <!-- Premium Background Layers -->
    <div class="mesh-bg"></div>
    <div class="noise-overlay"></div>
    <div class="particles" id="particles"></div>

    <!-- Ultimate Loading Screen (5 Seconds) -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-particles" id="loadingParticles"></div>
        <div class="loading-content">
            <div class="loading-logo-container">
                <div class="loading-logo-ring"></div>
                <div class="loading-logo-ring"></div>
                <div class="loading-logo-ring"></div>
                <div class="loading-logo-icon">
                    <i class="bi bi-book-half"></i>
                </div>
            </div>
            <div class="loading-brand">SIMBITA</div>
            <div class="loading-progress-wrapper">
                <div class="loading-progress-bar">
                    <div class="loading-progress-fill" id="progressFill"></div>
                </div>
                <div class="loading-percentage">
                    <span>PROGRESS</span>
                    <span class="loading-percent-number" id="percentNumber">0%</span>
                </div>
            </div>
            <div class="loading-status">
                <span class="loading-status-dot"></span>
                <span id="loadingStatus">INITIALIZING SYSTEM</span>
            </div>
            <div class="loading-steps">
                <div class="loading-step active" data-step="1">
                    <div class="loading-step-icon"><i class="bi bi-cpu"></i></div>
                    <div class="loading-step-label">System</div>
                </div>
                <div class="loading-step" data-step="2">
                    <div class="loading-step-icon"><i class="bi bi-box"></i></div>
                    <div class="loading-step-label">Modules</div>
                </div>
                <div class="loading-step" data-step="3">
                    <div class="loading-step-icon"><i class="bi bi-database"></i></div>
                    <div class="loading-step-label">Database</div>
                </div>
                <div class="loading-step" data-step="4">
                    <div class="loading-step-icon"><i class="bi bi-palette"></i></div>
                    <div class="loading-step-label">Interface</div>
                </div>
                <div class="loading-step" data-step="5">
                    <div class="loading-step-icon"><i class="bi bi-check-circle"></i></div>
                    <div class="loading-step-label">Ready</div>
                </div>
            </div>
        </div>
        <div class="loading-burst" id="loadingBurst"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar" id="navbar">
        <div class="navbar-container">
            <a href="<?= BASE_URL ?>" class="navbar-brand">
                <span class="navbar-brand-icon"><i class="bi bi-book-half"></i></span>
                <span>SIMBITA</span>
            </a>
            
            <ul class="navbar-menu" id="navbarMenu">
                <li><a href="#home" class="nav-link">Beranda</a></li>
                <li><a href="#features" class="nav-link">Fitur</a></li>
                <li><a href="#how-it-works" class="nav-link">Cara Kerja</a></li>
                <li><a href="#testimonials" class="nav-link">Testimoni</a></li>
                <li>
                    <a href="<?= BASE_URL ?>/login" class="btn-login">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Login</span>
                    </a>
                </li>
            </ul>
            
            <div class="navbar-toggle" id="navbarToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <div class="hero-text" id="heroText">
                <div class="hero-badge">
                    <i class="bi bi-stars"></i>
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
                    <a href="<?= BASE_URL ?>/login" class="btn-primary-glow magnetic-btn">
                        <span>Masuk ke Sistem</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="#preview" class="btn-secondary-glass magnetic-btn">
                        <i class="bi bi-play-circle"></i>
                        <span>Pelajari Fitur</span>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="scroll-indicator" onclick="document.getElementById('preview').scrollIntoView({behavior:'smooth'})">
            <div class="mouse">
                <div class="wheel"></div>
            </div>
            <p>Scroll ke bawah</p>
        </div>
    </section>

    <!-- Live App Preview Section (Standalone, Mustahil Ter-clip) -->
    <section class="preview-section" id="preview">
        <div class="app-preview animate-on-scroll" id="appPreview">
            <div class="preview-glow"></div>

            <div class="preview-toast toast-1">
                <div class="toast-icon toast-icon-1"><i class="bi bi-chat-left-text-fill"></i></div>
                <div class="toast-body">
                    <span class="toast-title">4 anotasi baru diterima</span>
                    <span class="toast-desc">Dr. Ahmad Fauzi • Bab_1-3.pdf</span>
                </div>
            </div>

            <div class="preview-toast toast-2">
                <div class="toast-icon toast-icon-2"><i class="bi bi-calendar-event-fill"></i></div>
                <div class="toast-body">
                    <span class="toast-title">Pengingat: Seminar Kemajuan</span>
                    <span class="toast-desc">Kamis, 09.00 • Ruang B-204</span>
                </div>
            </div>

            <div class="app-window">
                <div class="app-titlebar">
                    <div class="window-dots">
                        <span class="dot dot-red"></span>
                        <span class="dot dot-yellow"></span>
                        <span class="dot dot-green"></span>
                    </div>
                    <div class="window-url"><i class="bi bi-lock-fill"></i> simbita.ac.id/dashboard</div>
                    <div class="window-actions">
                        <i class="bi bi-dash"></i>
                        <i class="bi bi-square"></i>
                        <i class="bi bi-x"></i>
                    </div>
                </div>

                <div class="app-body">
                    <aside class="app-sidebar">
                        <div class="sidebar-item active"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></div>
                        <div class="sidebar-item"><i class="bi bi-file-earmark-text"></i><span>Dokumen Saya</span></div>
                        <div class="sidebar-item"><i class="bi bi-chat-left-text"></i><span>Anotasi</span></div>
                        <div class="sidebar-item"><i class="bi bi-calendar-event"></i><span>Jadwal Bimbingan</span></div>
                        <div class="sidebar-item"><i class="bi bi-gear"></i><span>Pengaturan</span></div>
                    </aside>

                    <div class="app-main">
                        <div class="app-main-header">
                            <div>
                                <h4>Dashboard Bimbingan</h4>
                                <p>Tugas Akhir — Andi Pratama • NIM 2101010001</p>
                            </div>
                            <span class="status-pill pill-progress"><i class="bi bi-arrow-repeat"></i> Bimbingan Berjalan</span>
                        </div>

                        <div class="doc-rows">
                            <div class="doc-row">
                                <div class="doc-icon icon-done"><i class="bi bi-file-earmark-check-fill"></i></div>
                                <div class="doc-info">
                                    <span class="doc-name">Proposal.pdf</span>
                                    <span class="doc-meta">Versi 3 • Disetujui dosen pembimbing</span>
                                </div>
                                <span class="doc-status status-approved"><i class="bi bi-check-circle-fill"></i> Disetujui</span>
                            </div>
                            <div class="doc-row">
                                <div class="doc-icon icon-review"><i class="bi bi-file-earmark-text-fill"></i></div>
                                <div class="doc-info">
                                    <span class="doc-name">Bab_1-3.pdf</span>
                                    <span class="doc-meta">Versi 2 • Menunggu review dosen pembimbing</span>
                                </div>
                                <span class="doc-status status-review"><i class="bi bi-hourglass-split"></i> Direview</span>
                            </div>
                            <div class="doc-row">
                                <div class="doc-icon icon-revision"><i class="bi bi-file-earmark-diff-fill"></i></div>
                                <div class="doc-info">
                                    <span class="doc-name">Bab_4-5.pdf</span>
                                    <span class="doc-meta">Versi 1 • Catatan revisi tersedia</span>
                                </div>
                                <span class="doc-status status-revision"><i class="bi bi-pencil-square"></i> Revisi</span>
                            </div>
                        </div>

                        <div class="app-progress">
                            <div class="progress-head">
                                <span>Progress Bimbingan Keseluruhan</span>
                                <span class="progress-value">Tahap: Hasil & Pembahasan</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-bar-animated"></div>
                            </div>
                            <div class="milestone-row">
                                <div class="milestone done">
                                    <div class="milestone-dot"><i class="bi bi-check2"></i></div>
                                    <span class="milestone-label">Proposal</span>
                                </div>
                                <div class="milestone done">
                                    <div class="milestone-dot"><i class="bi bi-check2"></i></div>
                                    <span class="milestone-label">Seminar</span>
                                </div>
                                <div class="milestone active">
                                    <div class="milestone-dot"><i class="bi bi-check2"></i></div>
                                    <span class="milestone-label">Hasil</span>
                                </div>
                                <div class="milestone">
                                    <div class="milestone-dot"><i class="bi bi-check2"></i></div>
                                    <span class="milestone-label">Penutup</span>
                                </div>
                                <div class="milestone">
                                    <div class="milestone-dot"><i class="bi bi-check2"></i></div>
                                    <span class="milestone-label">Sidang</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Marquee Section -->
    <section class="marquee-section" id="marquee">
        <div class="marquee-wrapper" id="marqueeWrapper">
            <div class="marquee-item"><div class="marquee-icon"><i class="bi bi-lightning-charge-fill"></i></div><span class="marquee-text">Proses Cepat</span></div>
            <div class="marquee-item"><div class="marquee-icon"><i class="bi bi-shield-fill-check"></i></div><span class="marquee-text">Keamanan Terjamin</span></div>
            <div class="marquee-item"><div class="marquee-icon"><i class="bi bi-cloud-fill"></i></div><span class="marquee-text">Cloud 24/7</span></div>
            <div class="marquee-item"><div class="marquee-icon"><i class="bi bi-phone-fill"></i></div><span class="marquee-text">Mobile Responsive</span></div>
            <div class="marquee-item"><div class="marquee-icon"><i class="bi bi-gear-fill"></i></div><span class="marquee-text">Fitur Modern</span></div>
            <div class="marquee-item"><div class="marquee-icon"><i class="bi bi-graph-up-arrow"></i></div><span class="marquee-text">Scalable</span></div>
            <div class="marquee-item"><div class="marquee-icon"><i class="bi bi-people-fill"></i></div><span class="marquee-text">Multi-Role</span></div>
            <div class="marquee-item"><div class="marquee-icon"><i class="bi bi-bell-fill"></i></div><span class="marquee-text">Real-time Notification</span></div>
            <!-- Duplicate for seamless loop -->
            <div class="marquee-item"><div class="marquee-icon"><i class="bi bi-lightning-charge-fill"></i></div><span class="marquee-text">Proses Cepat</span></div>
            <div class="marquee-item"><div class="marquee-icon"><i class="bi bi-shield-fill-check"></i></div><span class="marquee-text">Keamanan Terjamin</span></div>
            <div class="marquee-item"><div class="marquee-icon"><i class="bi bi-cloud-fill"></i></div><span class="marquee-text">Cloud 24/7</span></div>
            <div class="marquee-item"><div class="marquee-icon"><i class="bi bi-phone-fill"></i></div><span class="marquee-text">Mobile Responsive</span></div>
            <div class="marquee-item"><div class="marquee-icon"><i class="bi bi-gear-fill"></i></div><span class="marquee-text">Fitur Modern</span></div>
            <div class="marquee-item"><div class="marquee-icon"><i class="bi bi-graph-up-arrow"></i></div><span class="marquee-text">Scalable</span></div>
            <div class="marquee-item"><div class="marquee-icon"><i class="bi bi-people-fill"></i></div><span class="marquee-text">Multi-Role</span></div>
            <div class="marquee-item"><div class="marquee-icon"><i class="bi bi-bell-fill"></i></div><span class="marquee-text">Real-time Notification</span></div>
        </div>
    </section>

    <!-- Platform Capabilities -->
    <section class="capabilities">
        <div class="container">
            <div class="capabilities-header animate-on-scroll">
                <div class="capabilities-title">Platform Capabilities</div>
                <h2 class="capabilities-heading">Mengapa Memilih <span class="gradient-text">SIMBITA</span>?</h2>
                <p class="capabilities-subheading">Solusi lengkap untuk manajemen bimbingan akademik modern</p>
            </div>
            
            <div class="capabilities-grid">
                <div class="capability-item animate-on-scroll tilt-card" data-delay="0">
                    <div class="capability-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                    <div class="capability-text">Proses Cepat & Efisien</div>
                </div>
                <div class="capability-item animate-on-scroll tilt-card" data-delay="100">
                    <div class="capability-icon"><i class="bi bi-shield-fill-check"></i></div>
                    <div class="capability-text">Keamanan Data Terjamin</div>
                </div>
                <div class="capability-item animate-on-scroll tilt-card" data-delay="200">
                    <div class="capability-icon"><i class="bi bi-cloud-fill"></i></div>
                    <div class="capability-text">Akses Cloud 24/7</div>
                </div>
                <div class="capability-item animate-on-scroll tilt-card" data-delay="300">
                    <div class="capability-icon"><i class="bi bi-phone-fill"></i></div>
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
                <div class="feature-card animate-on-scroll tilt-card" data-delay="0">
                    <div class="feature-icon"><i class="bi bi-cloud-upload"></i></div>
                    <h3 class="feature-title">Version Control Cerdas</h3>
                    <p class="feature-description">Upload dokumen dengan sistem versioning otomatis. Tidak ada lagi file "Revisi_Final_Banget.pdf" yang membingungkan. Setiap versi tersimpan dengan rapi dan dapat diakses kapan saja.</p>
                </div>
                
                <div class="feature-card animate-on-scroll tilt-card" data-delay="100">
                    <div class="feature-icon"><i class="bi bi-chat-dots"></i></div>
                    <h3 class="feature-title">Anotasi PDF Interaktif</h3>
                    <p class="feature-description">Dosen dapat memberikan komentar langsung pada halaman spesifik dokumen dengan presisi koordinat X/Y. Feedback lebih jelas dan mudah dipahami.</p>
                </div>
                
                <div class="feature-card animate-on-scroll tilt-card" data-delay="200">
                    <div class="feature-icon"><i class="bi bi-bell"></i></div>
                    <h3 class="feature-title">Notifikasi Real-time</h3>
                    <p class="feature-description">Dapatkan pemberitahuan instan untuk review baru, deadline milestone, dan keputusan persetujuan. Tidak ada lagi informasi yang terlewat.</p>
                </div>
                
                <div class="feature-card animate-on-scroll tilt-card" data-delay="300">
                    <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                    <h3 class="feature-title">Keamanan Enterprise</h3>
                    <p class="feature-description">Enkripsi password, proteksi CSRF, validasi MIME type ketat, dan penyimpanan file di luar root publik. Data Anda aman dan terlindungi.</p>
                </div>
                
                <div class="feature-card animate-on-scroll tilt-card" data-delay="400">
                    <div class="feature-icon"><i class="bi bi-graph-up"></i></div>
                    <h3 class="feature-title">Progress Tracking</h3>
                    <p class="feature-description">Pantau perjalanan bimbingan dari Proposal hingga Sidang dengan dashboard visual yang informatif. Lihat progress Anda secara real-time.</p>
                </div>
                
                <div class="feature-card animate-on-scroll tilt-card" data-delay="500">
                    <div class="feature-icon"><i class="bi bi-people"></i></div>
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
                <div class="testimonial-card animate-on-scroll tilt-card" data-delay="0">
                    <div class="testimonial-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testimonial-text">"Fitur anotasi PDF-nya game changer. Saya tidak perlu lagi download-upload file berkali-kali untuk memberi catatan kecil. Sangat efisien dan menghemat waktu!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar"><i class="bi bi-person"></i></div>
                        <div class="author-info">
                            <h4>Dr. Ahmad Fauzi, M.Kom</h4>
                            <p>Dosen Pembimbing, Teknik Informatika</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card animate-on-scroll tilt-card" data-delay="100">
                    <div class="testimonial-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testimonial-text">"Sistem version control-nya menyelamatkan skripsi saya. Saya bisa melihat dengan jelas perubahan dari revisi 1 ke revisi 3 tanpa bingung. Sangat membantu!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar"><i class="bi bi-person"></i></div>
                        <div class="author-info">
                            <h4>Andi Pratama</h4>
                            <p>Mahasiswa S1, Angkatan 2021</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card animate-on-scroll tilt-card" data-delay="200">
                    <div class="testimonial-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testimonial-text">"Sebagai admin, dashboard monitoring-nya sangat membantu. Saya bisa melihat progress kelulusan mahasiswa secara real-time. Fitur yang sangat powerful!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar"><i class="bi bi-person"></i></div>
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
            <div class="cta-icon"><i class="bi bi-rocket-takeoff"></i></div>
            <h2 class="cta-title">Siap Memulai Perjalanan <span class="gradient-text">Akademik Anda</span>?</h2>
            <p class="cta-subtitle">Bergabunglah dengan ekosistem bimbingan digital yang modern, aman, dan efisien. Wujudkan kesuksesan akademik Anda bersama SIMBITA.</p>
            <div class="cta-buttons">
                <a href="<?= BASE_URL ?>/login" class="btn-primary-glow magnetic-btn">
                    <span>Masuk ke Sistem</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
                <a href="mailto:admin@simbita.local" class="btn-secondary-glass magnetic-btn">
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
                        <div class="footer-brand-icon"><i class="bi bi-book-half"></i></div>
                        <h3>SIMBITA</h3>
                    </div>
                    <p class="footer-section-description">Sistem Informasi Bimbingan Tugas Akhir modern untuk mengelola skripsi, tesis, dan disertasi dengan teknologi terkini dan standar keamanan enterprise.</p>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
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
    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // ============================================
        // ULTIMATE LOADING SCREEN (5 SECONDS)
        // ============================================
        const loader = document.getElementById('loadingScreen');
        const progressFill = document.getElementById('progressFill');
        const percentNumber = document.getElementById('percentNumber');
        const loadingStatus = document.getElementById('loadingStatus');
        const loadingSteps = document.querySelectorAll('.loading-step');
        const loadingBurst = document.getElementById('loadingBurst');
        const loadingParticles = document.getElementById('loadingParticles');
        
        // Create loading background particles
        for (let i = 0; i < 30; i++) {
            const particle = document.createElement('div');
            particle.className = 'loading-particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = Math.random() * 100 + '%';
            particle.style.setProperty('--tx', (Math.random() - 0.5) * 200 + 'px');
            particle.style.setProperty('--ty', (Math.random() - 0.5) * 200 + 'px');
            particle.style.animationDelay = Math.random() * 5 + 's';
            loadingParticles.appendChild(particle);
        }
        
        document.body.style.overflow = 'hidden';
        
        const statusMessages = [
            'INITIALIZING SYSTEM',
            'LOADING MODULES',
            'CONNECTING DATABASE',
            'PREPARING INTERFACE',
            'READY'
        ];
        
        const totalDuration = 5000; // 5 seconds
        const startTime = Date.now();
        
        function updateLoader() {
            const elapsed = Date.now() - startTime;
            const progress = Math.min((elapsed / totalDuration) * 100, 100);
            
            progressFill.style.width = progress + '%';
            percentNumber.textContent = Math.floor(progress) + '%';
            
            // Update status messages and steps
            const stepIndex = Math.min(Math.floor(progress / 20), 4);
            loadingStatus.textContent = statusMessages[stepIndex];
            
            loadingSteps.forEach((step, idx) => {
                if (idx < stepIndex) {
                    step.classList.add('completed');
                    step.classList.remove('active');
                } else if (idx === stepIndex) {
                    step.classList.add('active');
                    step.classList.remove('completed');
                } else {
                    step.classList.remove('active');
                    step.classList.remove('completed');
                }
            });
            
            if (progress < 100) {
                requestAnimationFrame(updateLoader);
            } else {
                // Create burst particles
                for (let i = 0; i < 30; i++) {
                    const burst = document.createElement('div');
                    burst.className = 'burst-particle';
                    const angle = (Math.PI * 2 * i) / 30;
                    const distance = 200 + Math.random() * 100;
                    burst.style.setProperty('--bx', Math.cos(angle) * distance + 'px');
                    burst.style.setProperty('--by', Math.sin(angle) * distance + 'px');
                    burst.style.animationDelay = Math.random() * 0.2 + 's';
                    loadingBurst.appendChild(burst);
                }
                
                setTimeout(() => {
                    loader.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                    
                    // Trigger initial animations after loader
                    document.querySelectorAll('.hero-text > *').forEach((el, idx) => {
                        el.style.animationDelay = (idx * 0.12) + 's';
                    });
                }, 600);
            }
        }
        
        requestAnimationFrame(updateLoader);

        // ============================================
        // CUSTOM CURSOR (HIGH-PERFORMANCE, TRANSFORM-BASED)
        // ============================================
        const cursorDot = document.getElementById('cursorDot');
        const cursorOutline = document.getElementById('cursorOutline');
        const finePointer = window.matchMedia('(pointer: fine)').matches;
        
        if (finePointer && cursorDot && cursorOutline) {
            let targetX = window.innerWidth / 2;
            let targetY = window.innerHeight / 2;
            let outlineX = targetX;
            let outlineY = targetY;
            
            const renderCursor = () => {
                outlineX += (targetX - outlineX) * 0.28;
                outlineY += (targetY - outlineY) * 0.28;
                cursorOutline.style.transform = `translate3d(${outlineX}px, ${outlineY}px, 0)`;
                requestAnimationFrame(renderCursor);
            };
            
            window.addEventListener('mousemove', (e) => {
                targetX = e.clientX;
                targetY = e.clientY;
                cursorDot.style.transform = `translate3d(${targetX}px, ${targetY}px, 0)`;
            }, { passive: true });
            
            document.addEventListener('mouseleave', () => {
                cursorDot.style.opacity = '0';
                cursorOutline.style.opacity = '0';
            });
            
            document.addEventListener('mouseenter', () => {
                cursorDot.style.opacity = '1';
                cursorOutline.style.opacity = '1';
            });
            
            const hoverTargets = 'a, button, .nav-link, .feature-card, .testimonial-card, .capability-item, .tilt-card, .doc-row, .sidebar-item, .marquee-item';
            document.querySelectorAll(hoverTargets).forEach((el) => {
                el.addEventListener('mouseenter', () => {
                    cursorDot.classList.add('hovered');
                    cursorOutline.classList.add('hovered');
                });
                el.addEventListener('mouseleave', () => {
                    cursorDot.classList.remove('hovered');
                    cursorOutline.classList.remove('hovered');
                });
            });
        } else if (cursorDot && cursorOutline) {
            cursorDot.style.display = 'none';
            cursorOutline.style.display = 'none';
        }

        // ============================================
        // PARTICLE SYSTEM (BACKGROUND)
        // ============================================
        const particlesContainer = document.getElementById('particles');
        const particleCount = window.innerWidth < 768 ? 20 : 60;
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            
            const size = Math.random() * 6 + 2;
            const posX = Math.random() * 100;
            const duration = Math.random() * 25 + 15;
            const delay = Math.random() * 10;
            const colors = [
                'rgba(99, 102, 241, 0.6)',
                'rgba(139, 92, 246, 0.5)',
                'rgba(6, 182, 212, 0.5)',
                'rgba(129, 140, 248, 0.6)'
            ];
            
            particle.style.cssText = `
                width: ${size}px;
                height: ${size}px;
                left: ${posX}%;
                animation-duration: ${duration}s;
                animation-delay: ${delay}s;
                background: ${colors[Math.floor(Math.random() * colors.length)]};
            `;
            particlesContainer.appendChild(particle);
        }

        // ============================================
        // NAVBAR SCROLL EFFECT
        // ============================================
        const navbar = document.getElementById('navbar');
        let lastScrollY = window.scrollY;
        
        window.addEventListener('scroll', () => {
            const currentScrollY = window.scrollY;
            if (currentScrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            lastScrollY = currentScrollY;
        }, { passive: true });

        // ============================================
        // MOBILE MENU
        // ============================================
        const navToggle = document.getElementById('navbarToggle');
        const navMenu = document.getElementById('navbarMenu');
        
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
        
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
            });
        });

        // ============================================
        // SCROLL ANIMATIONS (INTERSECTION OBSERVER)
        // ============================================
        const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -100px 0px' };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                                   
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });

        // ============================================
        // SPOTLIGHT & TILT EFFECT
        // ============================================
            document.querySelectorAll('.feature-card, .testimonial-card, .capability-item, .tilt-card').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = ((y - centerY) / centerY) * -8;
                const rotateY = ((x - centerX) / centerX) * 8;
                
                card.style.setProperty('--mouse-x', `${x}px`);
                card.style.setProperty('--mouse-y', `${y}px`);
                
                if (card.classList.contains('tilt-card')) {
                    card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
                }
            });
            
            card.addEventListener('mouseleave', () => {
                if (card.classList.contains('tilt-card')) {
                    card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
                }
            });
        });

        // ============================================
        // MAGNETIC BUTTONS
        // ============================================
        document.querySelectorAll('.magnetic-btn').forEach(btn => {
            btn.addEventListener('mousemove', (e) => {
                const rect = btn.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                btn.style.transform = `translate(${x * 0.3}px, ${y * 0.3}px)`;
            });
            
            btn.addEventListener('mouseleave', () => {
                btn.style.transform = 'translate(0, 0)';
            });
        });

        // ============================================
        // SCROLL TO TOP
        // ============================================
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

        // ============================================
        // SMOOTH SCROLL FOR ANCHORS
        // ============================================
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    const navbarHeight = navbar.offsetHeight;
                    const targetPosition = targetElement.offsetTop - navbarHeight;
                    window.scrollTo({ top: targetPosition, behavior: 'smooth' });
                }
            });
        });

        // ============================================
        // PARALLAX MESH BACKGROUND ON MOUSE MOVE
        // ============================================
        const meshBg = document.querySelector('.mesh-bg');
        window.addEventListener('mousemove', (e) => {
            const x = (e.clientX / window.innerWidth - 0.5) * 30;
            const y = (e.clientY / window.innerHeight - 0.5) * 30;
            if (meshBg) meshBg.style.transform = `translate(${x}px, ${y}px) scale(1.1)`;
        });

        // ============================================
        // GENTLE 3D TILT FOR APP PREVIEW WINDOW
        // ============================================
        const appPreview = document.getElementById('appPreview');
        const appWindow = document.querySelector('.app-window');
        
        if (appPreview && appWindow && window.innerWidth > 900) {
            appPreview.addEventListener('mousemove', (e) => {
                const rect = appPreview.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width - 0.5;
                const y = (e.clientY - rect.top) / rect.height - 0.5;
                appWindow.style.transform = `rotateY(${x * 6}deg) rotateX(${y * -6}deg)`;
            });
            
            appPreview.addEventListener('mouseleave', () => {
                appWindow.style.transform = 'rotateY(0deg) rotateX(0deg)';
            });
        }

        // ============================================
        // CONSOLE EASTER EGG
        // ============================================
        console.log('%c SIMBITA ', 'background: linear-gradient(135deg, #6366f1, #8b5cf6, #06b6d4); color: white; font-size: 2.5rem; font-weight: bold; padding: 1rem 2rem; border-radius: 12px;');
        console.log('%cSistem Informasi Bimbingan Tugas Akhir', 'color: #94a3b8; font-size: 1.1rem;');
        console.log('%cMade with ❤ by Yolis Libman', 'color: #f43f5e; font-size: 0.95rem;');
        console.log('%c World Class Landing Page - Ultra Premium ', 'background: #0f172a; color: #fbbf24; padding: 0.5rem 1rem; border-radius: 6px; font-weight: bold;');
    });
    </script>
</body>
</html>