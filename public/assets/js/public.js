/**
 * =====================================================================
 * SIMBITA - PUBLIC WEBSITE JAVASCRIPT (FINAL MULTIMATE)
 * High Performance, Modern Interactions, Smooth Animations
 * =====================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // === 1. LOADING SCREEN ===
    const loadingScreen = document.getElementById('loadingScreen');
    if (loadingScreen) {
        window.addEventListener('load', () => {
            loadingScreen.classList.add('hidden');
            // Hapus dari DOM setelah animasi selesai untuk menghemat memori
            setTimeout(() => {
                loadingScreen.style.display = 'none';
            }, 500);
        });
    }

    // === 2. NAVBAR SCROLL EFFECT & ACTIVE LINK ===
    const navbar = document.getElementById('navbar');
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('section[id]');

    const handleNavbarScroll = () => {
        if (window.scrollY > 50) {
            navbar?.classList.add('scrolled');
        } else {
            navbar?.classList.remove('scrolled');
        }

        // Highlight menu aktif berdasarkan scroll position
        let currentSection = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100; // Offset untuk fixed navbar
            if (window.scrollY >= sectionTop) {
                currentSection = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${currentSection}`) {
                link.classList.add('active');
            }
        });
    };

    window.addEventListener('scroll', handleNavbarScroll, { passive: true });

    // === 3. MOBILE MENU TOGGLE ===
    const navbarToggle = document.getElementById('navbarToggle');
    const navbarMenu = document.getElementById('navbarMenu');

    if (navbarToggle && navbarMenu) {
        navbarToggle.addEventListener('click', () => {
            navbarToggle.classList.toggle('active');
            navbarMenu.classList.toggle('active');
            // Prevent body scroll saat menu terbuka
            document.body.style.overflow = navbarMenu.classList.contains('active') ? 'hidden' : '';
        });

        // Tutup menu saat link diklik
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navbarToggle.classList.remove('active');
                navbarMenu.classList.remove('active');
                document.body.style.overflow = '';
            });
        });

        // Tutup menu saat klik di luar area menu
        document.addEventListener('click', (e) => {
            if (!navbarMenu.contains(e.target) && !navbarToggle.contains(e.target) && navbarMenu.classList.contains('active')) {
                navbarToggle.classList.remove('active');
                navbarMenu.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }

    // === 4. SMOOTH SCROLLING (Dengan Offset Navbar) ===
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                const navbarHeight = navbar ? navbar.offsetHeight : 0;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // === 5. SCROLL ANIMATIONS (Intersection Observer) ===
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                // Unobserve setelah animasi terjadi agar hanya berjalan sekali (hemat resource)
                scrollObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        scrollObserver.observe(el);
    });

    // === 6. COUNTER ANIMATION (High Performance dengan requestAnimationFrame) ===
    const animateCounter = (element) => {
        const target = parseInt(element.getAttribute('data-target'), 10);
        const duration = 2000; // 2 detik
        const startTime = performance.now();

        const updateCounter = (currentTime) => {
            const elapsedTime = currentTime - startTime;
            const progress = Math.min(elapsedTime / duration, 1);
            
            // Easing function untuk animasi yang lebih natural (easeOutExpo)
            const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            
            const current = Math.floor(easeProgress * target);
            element.textContent = current;

            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = target; // Pastikan nilai akhir tepat
            }
        };

        requestAnimationFrame(updateCounter);
    };

    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.stat-number[data-target]').forEach(el => {
        counterObserver.observe(el);
    });

    // === 7. FAQ ACCORDION ===
    document.querySelectorAll('.faq-question').forEach(button => {
        button.addEventListener('click', () => {
            const faqItem = button.parentElement;
            const isActive = faqItem.classList.contains('active');

            // Tutup semua FAQ lainnya (opsional: hapus blok ini jika ingin multiple open)
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
            });

            // Toggle status FAQ yang diklik
            if (!isActive) {
                faqItem.classList.add('active');
            }
        });
    });

    // === 8. SCROLL TO TOP BUTTON ===
    const scrollTopBtn = document.getElementById('scrollTop');
    
    if (scrollTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 500) {
                scrollTopBtn.classList.add('visible');
            } else {
                scrollTopBtn.classList.remove('visible');
            }
        }, { passive: true });

        scrollTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // === 9. RIPPLE EFFECT PADA TOMBOL (Premium Feel) ===
    document.querySelectorAll('.btn').forEach(button => {
        button.addEventListener('click', function(e) {
            // Hindari membuat ripple jika sudah ada (mencegah memory leak)
            if (this.querySelector('.ripple-effect')) return;

            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            const ripple = document.createElement('span');
            ripple.classList.add('ripple-effect');
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            
            // Inject style dinamis jika belum ada di CSS
            if (!document.getElementById('ripple-style')) {
                const style = document.createElement('style');
                style.id = 'ripple-style';
                style.textContent = `
                    .btn { position: relative; overflow: hidden; }
                    .ripple-effect {
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.4);
                        transform: scale(0);
                        animation: ripple-animation 0.6s linear;
                        pointer-events: none;
                    }
                    @keyframes ripple-animation {
                        to { transform: scale(4); opacity: 0; }
                    }
                `;
                document.head.appendChild(style);
            }

            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });

    // === 10. FORM VALIDATION (Preventive) ===
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                // Tambahkan visual feedback untuk field yang invalid
                form.classList.add('was-validated');
            }
        }, false);
    });

    // === 11. CONSOLE BRANDING (Professional Touch) ===
    console.log('%c SIMBITA ', 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 16px; font-weight: bold; padding: 8px 12px; border-radius: 6px;');
    console.log('%c Sistem Informasi Bimbingan Tugas Akhir Digital v1.0 ', 'color: #6b7280; font-size: 12px; font-weight: 500;');
    console.log('%c Developed with ❤️ for Academic Excellence ', 'color: #3b82f6; font-size: 12px;');
});