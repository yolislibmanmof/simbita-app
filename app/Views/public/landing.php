<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMBITA - Sistem Informasi Bimbingan Tugas Akhir Digital</title>
    <meta name="description" content="Platform modern untuk mengelola bimbingan Skripsi, Tesis, dan Disertasi dengan teknologi terkini">
    <meta name="keywords" content="skripsi, tesis, disertasi, bimbingan, tugas akhir, akademik">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="SIMBITA - Sistem Informasi Bimbingan Tugas Akhir">
    <meta property="og:description" content="Platform modern untuk mengelola bimbingan Skripsi, Tesis, dan Disertasi">
    <meta property="og:type" content="website">
    
    <!-- Preconnect untuk Performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS -->
    <link href="<?= BASE_URL ?>/assets/css/public.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'></text></svg>">
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-content">
            <div class="loading-logo">
                <i class="bi bi-book-half"></i>
            </div>
            <div class="loading-spinner"></div>
            <p class="loading-text">Memuat SIMBITA...</p>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar" id="navbar">
        <div class="navbar-container">
            <a href="<?= BASE_URL ?>" class="navbar-brand">
                <i class="bi bi-book-half"></i>
                <span>SIMBITA</span>
            </a>
            
            <ul class="navbar-menu" id="navbarMenu">
                <li><a href="#home" class="nav-link active">Beranda</a></li>
                <li><a href="#features" class="nav-link">Fitur</a></li>
                <li><a href="#how-it-works" class="nav-link">Cara Kerja</a></li>
                <li><a href="#testimonials" class="nav-link">Testimoni</a></li>
                <li><a href="#faq" class="nav-link">FAQ</a></li>
                <li><a href="#contact" class="nav-link">Kontak</a></li>
                <li><a href="<?= BASE_URL ?>/login" class="btn btn-primary btn-sm">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a></li>
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
        <!-- Animated Background -->
        <div class="hero-bg">
            <div class="hero-particles" id="particles"></div>
            <div class="hero-gradient"></div>
        </div>
        
        <div class="hero-content">
            <div class="hero-badge animate-fade-in-down">
                <i class="bi bi-stars"></i>
                <span>Sistem Modern & Terintegrasi</span>
            </div>
            
            <h1 class="hero-title animate-fade-in-up">
                Sistem Informasi<br>
                <span class="text-gradient">Bimbingan Tugas Akhir</span><br>
                <span class="text-gradient-alt">Digital</span>
            </h1>
            
            <p class="hero-subtitle animate-fade-in-up delay-1">
                Platform lengkap untuk mengelola bimbingan Skripsi, Tesis, dan Disertasi 
                dengan teknologi modern, aman, dan mudah digunakan.
            </p>
            
            <div class="hero-buttons animate-fade-in-up delay-2">
                <a href="<?= BASE_URL ?>/login" class="btn btn-primary btn-large btn-glow">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Masuk ke Sistem</span>
                </a>
                <a href="#features" class="btn btn-secondary btn-large">
                    <i class="bi bi-play-circle"></i>
                    <span>Pelajari Lebih Lanjut</span>
                </a>
            </div>
            
            <div class="hero-stats animate-fade-in-up delay-3">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number" data-target="1000">0</span>
                        <span class="stat-label">Mahasiswa Aktif</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="bi bi-person-workspace"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number" data-target="150">0</span>
                        <span class="stat-label">Dosen Pembimbing</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="bi bi-file-earmark-check"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number" data-target="5000">0</span>
                        <span class="stat-label">Tugas Akhir Selesai</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="bi bi-heart"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number" data-target="98">0</span>
                        <span class="stat-label">% Kepuasan</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="scroll-indicator">
            <div class="mouse">
                <div class="wheel"></div>
            </div>
            <p>Scroll ke bawah</p>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section features" id="features">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">Fitur Unggulan</span>
                <h2 class="section-title">
                    Semua yang Anda Butuhkan untuk<br>
                    <span class="text-gradient">Kesuksesan Tugas Akhir</span>
                </h2>
                <p class="section-subtitle">
                    Platform komprehensif dengan fitur-fitur modern untuk memudahkan 
                    proses bimbingan dari awal hingga selesai
                </p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon">
                            <i class="bi bi-cloud-upload"></i>
                        </div>
                    </div>
                    <h3 class="feature-title">Upload Dokumen Cerdas</h3>
                    <p class="feature-description">
                        Upload dan kelola dokumen bimbingan dengan sistem versioning otomatis. 
                        Mendukung PDF dengan validasi file yang ketat.
                    </p>
                    <ul class="feature-list">
                        <li><i class="bi bi-check-circle"></i> Version control otomatis</li>
                        <li><i class="bi bi-check-circle"></i> Validasi file PDF</li>
                        <li><i class="bi bi-check-circle"></i> Storage cloud aman</li>
                    </ul>
                </div>
                
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                    </div>
                    <h3 class="feature-title">Review & Anotasi Interaktif</h3>
                    <p class="feature-description">
                        Dosen dapat memberikan komentar langsung pada dokumen dengan 
                        sistem anotasi yang presisi dan interaktif.
                    </p>
                    <ul class="feature-list">
                        <li><i class="bi bi-check-circle"></i> Komentar per halaman</li>
                        <li><i class="bi bi-check-circle"></i> Highlight & markup</li>
                        <li><i class="bi bi-check-circle"></i> Real-time sync</li>
                    </ul>
                </div>
                
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon">
                            <i class="bi bi-bell"></i>
                        </div>
                    </div>
                    <h3 class="feature-title">Notifikasi Real-time</h3>
                    <p class="feature-description">
                        Dapatkan notifikasi instan untuk setiap update, komentar baru, 
                        keputusan review, dan deadline penting.
                    </p>
                    <ul class="feature-list">
                        <li><i class="bi bi-check-circle"></i> Email notification</li>
                        <li><i class="bi bi-check-circle"></i> In-app alerts</li>
                        <li><i class="bi bi-check-circle"></i> Reminder otomatis</li>
                    </ul>
                </div>
                
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                    </div>
                    <h3 class="feature-title">Keamanan Enterprise-Grade</h3>
                    <p class="feature-description">
                        Sistem keamanan modern dengan enkripsi password, CSRF protection, 
                        dan validasi file berlapis untuk data Anda.
                    </p>
                    <ul class="feature-list">
                        <li><i class="bi bi-check-circle"></i> Password encryption</li>
                        <li><i class="bi bi-check-circle"></i> CSRF protection</li>
                        <li><i class="bi bi-check-circle"></i> Secure file storage</li>
                    </ul>
                </div>
                
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                    </div>
                    <h3 class="feature-title">Progress Tracking</h3>
                    <p class="feature-description">
                        Pantau progress bimbingan dengan dashboard yang informatif, 
                        milestone yang jelas, dan visualisasi yang mudah dipahami.
                    </p>
                    <ul class="feature-list">
                        <li><i class="bi bi-check-circle"></i> Visual progress bar</li>
                        <li><i class="bi bi-check-circle"></i> Milestone tracking</li>
                        <li><i class="bi bi-check-circle"></i> Timeline interaktif</li>
                    </ul>
                </div>
                
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                    <h3 class="feature-title">Multi-Role Access</h3>
                    <p class="feature-description">
                        Akses berbeda untuk Admin, Dosen, dan Mahasiswa dengan权限 
                        yang terstruktur dan terorganisir dengan baik.
                    </p>
                    <ul class="feature-list">
                        <li><i class="bi bi-check-circle"></i> Role-based access</li>
                        <li><i class="bi bi-check-circle"></i> Permission control</li>
                        <li><i class="bi bi-check-circle"></i> User management</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="section how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">Cara Kerja</span>
                <h2 class="section-title">
                    Proses Bimbingan yang<br>
                    <span class="text-gradient">Sederhana & Terstruktur</span>
                </h2>
                <p class="section-subtitle">
                    Empat langkah mudah untuk menyelesaikan tugas akhir Anda 
                    dengan bimbingan yang efektif dan efisien
                </p>
            </div>
            
            <div class="steps-container">
                <div class="step animate-on-scroll">
                    <div class="step-number">
                        <span>1</span>
                        <div class="step-number-glow"></div>
                    </div>
                    <div class="step-icon">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <h3 class="step-title">Registrasi & Login</h3>
                    <p class="step-description">
                        Mahasiswa dan dosen login ke sistem dengan akun yang telah 
                        diberikan administrator. Sistem autentikasi yang aman dan cepat.
                    </p>
                    <div class="step-features">
                        <span><i class="bi bi-check"></i> Single Sign-On</span>
                        <span><i class="bi bi-check"></i> Secure Login</span>
                    </div>
                </div>
                
                <div class="step animate-on-scroll">
                    <div class="step-number">
                        <span>2</span>
                        <div class="step-number-glow"></div>
                    </div>
                    <div class="step-icon">
                        <i class="bi bi-cloud-upload"></i>
                    </div>
                    <h3 class="step-title">Upload Dokumen</h3>
                    <p class="step-description">
                        Mahasiswa mengupload dokumen bimbingan (proposal, bab, revisi) 
                        sesuai jadwal dengan sistem versioning otomatis.
                    </p>
                    <div class="step-features">
                        <span><i class="bi bi-check"></i> Auto Versioning</span>
                        <span><i class="bi bi-check"></i> PDF Validation</span>
                    </div>
                </div>
                
                <div class="step animate-on-scroll">
                    <div class="step-number">
                        <span>3</span>
                        <div class="step-number-glow"></div>
                    </div>
                    <div class="step-icon">
                        <i class="bi bi-chat-left-text"></i>
                    </div>
                    <h3 class="step-title">Review Dosen</h3>
                    <p class="step-description">
                        Dosen pembimbing mereview dan memberikan komentar/koreksi 
                        pada dokumen dengan sistem anotasi yang presisi.
                    </p>
                    <div class="step-features">
                        <span><i class="bi bi-check"></i> Inline Comments</span>
                        <span><i class="bi bi-check"></i> Quick Review</span>
                    </div>
                </div>
                
                <div class="step animate-on-scroll">
                    <div class="step-number">
                        <span>4</span>
                        <div class="step-number-glow"></div>
                    </div>
                    <div class="step-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h3 class="step-title">Revisi & Approval</h3>
                    <p class="step-description">
                        Mahasiswa melakukan revisi berdasarkan masukan dosen hingga 
                        dokumen disetujui dan siap untuk tahap selanjutnya.
                    </p>
                    <div class="step-features">
                        <span><i class="bi bi-check"></i> Track Changes</span>
                        <span><i class="bi bi-check"></i> Final Approval</span>
                    </div>
                </div>
            </div>
            
            <!-- Process Timeline -->
            <div class="process-timeline animate-on-scroll">
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>Mulai Bimbingan</h4>
                        <p>Registrasi dan pengajuan judul</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>Proposal</h4>
                        <p>Penyusunan dan review proposal</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>Penulisan</h4>
                        <p>Penulisan bab demi bab</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>Sidang</h4>
                        <p>Ujian dan persetujuan akhir</p>
                    </div>
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
                    Apa Kata Mereka tentang<br>
                    <span class="text-gradient">SIMBITA</span>
                </h2>
                <p class="section-subtitle">
                    Pengalaman nyata dari mahasiswa dan dosen yang telah menggunakan 
                    platform kami
                </p>
            </div>
            
            <div class="testimonials-grid">
                <div class="testimonial-card animate-on-scroll">
                    <div class="testimonial-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testimonial-text">
                        "SIMBITA sangat membantu proses bimbingan skripsi saya. 
                        Sistemnya mudah digunakan dan dosen bisa memberikan feedback 
                        dengan cepat. Skripsi saya selesai tepat waktu!"
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="author-info">
                            <h4>Andi Pratama</h4>
                            <p>Mahasiswa S1 Teknik Informatika</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card animate-on-scroll">
                    <div class="testimonial-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testimonial-text">
                        "Sebagai dosen pembimbing, SIMBITA memudahkan saya untuk 
                        memantau progress mahasiswa dan memberikan review. 
                        Fitur anotasi PDF-nya sangat membantu."
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="author-info">
                            <h4>Dr. Ahmad Fauzi, M.Kom</h4>
                            <p>Dosen Pembimbing</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card animate-on-scroll">
                    <div class="testimonial-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="testimonial-text">
                        "Platform yang sangat profesional. Notifikasi real-time 
                        membuat saya tidak ketinggalan update dari dosen. 
                        Highly recommended untuk mahasiswa tingkat akhir!"
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="author-info">
                            <h4>Dewi Lestari</h4>
                            <p>Mahasiswa S2 Manajemen</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section faq" id="faq">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">FAQ</span>
                <h2 class="section-title">
                    Pertanyaan yang Sering<br>
                    <span class="text-gradient">Diajukan</span>
                </h2>
                <p class="section-subtitle">
                    Temukan jawaban atas pertanyaan umum tentang SIMBITA
                </p>
            </div>
            
            <div class="faq-container">
                <div class="faq-item animate-on-scroll">
                    <button class="faq-question">
                        <span>Apa itu SIMBITA?</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        <p>SIMBITA (Sistem Informasi Bimbingan Tugas Akhir) adalah platform digital 
                        untuk mengelola proses bimbingan Skripsi, Tesis, dan Disertasi secara online. 
                        Sistem ini memudahkan mahasiswa dan dosen dalam berkomunikasi, upload dokumen, 
                        dan tracking progress bimbingan.</p>
                    </div>
                </div>
                
                <div class="faq-item animate-on-scroll">
                    <button class="faq-question">
                        <span>Bagaimana cara mendapatkan akun?</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Akun SIMBITA diberikan oleh administrator kampus. Mahasiswa dan dosen 
                        dapat menghubungi bagian administrasi atau IT support untuk mendapatkan 
                        kredensial login.</p>
                    </div>
                </div>
                
                <div class="faq-item animate-on-scroll">
                    <button class="faq-question">
                        <span>Format file apa yang didukung?</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Saat ini SIMBITA mendukung upload file format PDF dengan ukuran maksimal 
                        25MB. Format PDF dipilih untuk memastikan konsistensi tampilan dokumen 
                        di semua perangkat.</p>
                    </div>
                </div>
                
                <div class="faq-item animate-on-scroll">
                    <button class="faq-question">
                        <span>Apakah data saya aman?</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Ya, keamanan data adalah prioritas kami. SIMBITA menggunakan enkripsi 
                        password, proteksi CSRF, secure file storage, dan backup data berkala 
                        untuk memastikan data Anda tetap aman.</p>
                    </div>
                </div>
                
                <div class="faq-item animate-on-scroll">
                    <button class="faq-question">
                        <span>Bisakah saya mengakses dari HP?</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Tentu! SIMBITA dirancang dengan responsive design yang optimal untuk 
                        berbagai perangkat, termasuk smartphone dan tablet. Anda dapat mengakses 
                        sistem kapan saja dan di mana saja.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta" id="contact">
        <div class="cta-bg">
            <div class="cta-particles"></div>
        </div>
        <div class="cta-content animate-on-scroll">
            <div class="cta-icon">
                <i class="bi bi-rocket-takeoff"></i>
            </div>
            <h2 class="cta-title">Siap Memulai Perjalanan Akademik Anda?</h2>
            <p class="cta-subtitle">
                Bergabunglah dengan ribuan mahasiswa dan dosen yang telah menggunakan SIMBITA 
                untuk mengelola bimbingan tugas akhir mereka. Wujudkan kesuksesan akademik Anda bersama kami.
            </p>
            <div class="cta-buttons">
                <a href="<?= BASE_URL ?>/login" class="btn btn-primary btn-large btn-glow">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Masuk ke Sistem</span>
                </a>
                <a href="mailto:admin@simbita.local" class="btn btn-outline btn-large">
                    <i class="bi bi-envelope"></i>
                    <span>Hubungi Kami</span>
                </a>
            </div>
            <div class="cta-stats">
                <div class="cta-stat">
                    <span class="cta-stat-number">98%</span>
                    <span class="cta-stat-label">Tingkat Kepuasan</span>
                </div>
                <div class="cta-stat">
                    <span class="cta-stat-number">24/7</span>
                    <span class="cta-stat-label">Akses Sistem</span>
                </div>
                <div class="cta-stat">
                    <span class="cta-stat-number">100%</span>
                    <span class="cta-stat-label">Aman & Terpercaya</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-bg"></div>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-brand">
                        <i class="bi bi-book-half"></i>
                        <h3>SIMBITA</h3>
                    </div>
                    <p>Sistem Informasi Bimbingan Tugas Akhir modern untuk mengelola skripsi, 
                    tesis, dan disertasi dengan teknologi terkini.</p>
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
                        <li><a href="#faq">FAQ</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Layanan</h4>
                    <ul>
                        <li><a href="<?= BASE_URL ?>/login">Login Mahasiswa</a></li>
                        <li><a href="<?= BASE_URL ?>/login">Login Dosen</a></li>
                        <li><a href="<?= BASE_URL ?>/login">Login Admin</a></li>
                        <li><a href="#">Panduan Pengguna</a></li>
                        <li><a href="#">Dokumentasi API</a></li>
                    </ul>
                </div>
                
                <div class="footer-section" id="contact">
                    <h4>Kontak Kami</h4>
                    <ul class="contact-info">
                        <li>
                            <i class="bi bi-geo-alt"></i>
                            <span>Yogyakarta, Indonesia</span>
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
                <div class="footer-bottom-content">
                    <p>&copy; 2026 SIMBITA. All rights reserved.</p>
                    <p>Made with <i class="bi bi-heart-fill"></i> in Indonesia</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button class="scroll-top" id="scrollTop" aria-label="Scroll to top">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- JavaScript -->
    <script src="<?= BASE_URL ?>/assets/js/public.js"></script>
</body>
</html>