/**
 * =====================================================================
 * SIMBITA - ADMIN DASHBOARD JAVASCRIPT
 * FINAL MULTIMATE EDITION - Enterprise Grade
 * =====================================================================
 */

(function() {
    'use strict';

    // === 1. TOAST NOTIFICATION SYSTEM ===
    const Toast = {
        container: null,
        
        init() {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        },
        
        show(message, type = 'success', duration = 4000) {
            if (!this.container) this.init();
            
            const icons = {
                success: 'check-circle-fill',
                error: 'exclamation-circle-fill',
                warning: 'exclamation-triangle-fill',
                info: 'info-circle-fill'
            };
            
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <i class="bi bi-${icons[type] || 'info-circle-fill'} toast-icon"></i>
                <div class="toast-message">${message}</div>
                <button class="toast-close">&times;</button>
            `;
            
            this.container.appendChild(toast);
            
            // Trigger animation
            requestAnimationFrame(() => toast.classList.add('show'));
            
            // Close button
            toast.querySelector('.toast-close').addEventListener('click', () => this.close(toast));
            
            // Auto close
            if (duration > 0) {
                setTimeout(() => this.close(toast), duration);
            }
        },
        
        close(toast) {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }
    };

    // Export globally
    window.showToast = (message, type, duration) => Toast.show(message, type, duration);

    // === 2. DROPDOWN HANDLER ===
    const initDropdowns = () => {
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            const trigger = dropdown.querySelector('[data-toggle="dropdown"]');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            if (!trigger || !menu) return;
            
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = menu.classList.contains('show');
                
                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu.show').forEach(m => {
                    if (m !== menu) m.classList.remove('show');
                });
                
                menu.classList.toggle('show', !isOpen);
            });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(m => {
                    m.classList.remove('show');
                });
            }
        });
    };

    // === 3. ALERT AUTO-DISMISS ===
    const initAlerts = () => {
        document.querySelectorAll('.alert.alert-dismissible').forEach(alert => {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(30px)';
                    setTimeout(() => alert.remove(), 300);
                });
            }
            
            // Auto dismiss success alerts after 5 seconds
            if (alert.classList.contains('alert-success')) {
                setTimeout(() => {
                    if (alert.parentElement) {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateX(30px)';
                        setTimeout(() => alert.remove(), 300);
                    }
                }, 5000);
            }
        });
    };

    // === 4. RIPPLE EFFECT ON BUTTONS ===
    const initRippleEffect = () => {
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (this.disabled || this.querySelector('.ripple-effect')) return;
                
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                const ripple = document.createElement('span');
                ripple.className = 'ripple-effect';
                ripple.style.width = ripple.style.height = `${size}px`;
                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;
                
                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });
        });
    };

    // === 5. FILE UPLOAD PREVIEW ===
    const initFileUpload = () => {
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const preview = this.parentElement.querySelector('.file-preview');
                    if (preview) {
                        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                        preview.innerHTML = `
                            <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; background: var(--gray-50); border-radius: var(--radius-md); border: 1px solid var(--gray-200); margin-top: 0.5rem;">
                                <i class="bi bi-file-earmark-pdf" style="font-size: 1.5rem; color: var(--accent-red);"></i>
                                <div>
                                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--gray-800);">${file.name}</div>
                                    <small style="color: var(--gray-500);">${sizeMB} MB</small>
                                </div>
                            </div>
                        `;
                    }
                }
            });
        });
    };

    // === 6. FORM VALIDATION ===
    const initFormValidation = () => {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = this.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = 'var(--accent-red)';
                        field.addEventListener('input', () => {
                            field.style.borderColor = '';
                        }, { once: true });
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    Toast.show('Mohon lengkapi semua field yang wajib diisi', 'error');
                    return false;
                }
            });
        });
    };

    // === 7. CONFIRM DIALOG (Beautiful) ===
    const confirmDialog = (message, callback) => {
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.innerHTML = `
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title"><i class="bi bi-exclamation-triangle" style="color: var(--accent-yellow);"></i> Konfirmasi</h3>
                </div>
                <div class="modal-body">
                    <p style="color: var(--gray-700); line-height: 1.6;">${message}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-action="cancel">Batal</button>
                    <button class="btn btn-danger" data-action="confirm">Ya, Lanjutkan</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        requestAnimationFrame(() => overlay.classList.add('active'));
        
        const close = () => {
            overlay.classList.remove('active');
            setTimeout(() => overlay.remove(), 300);
        };
        
        overlay.querySelector('[data-action="cancel"]').addEventListener('click', close);
        overlay.querySelector('[data-action="confirm"]').addEventListener('click', () => {
            close();
            if (callback) callback();
        });
        
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) close();
        });
    };

    window.confirmDialog = confirmDialog;

    // === 8. AUTO REFRESH NOTIFICATIONS ===
    // CATATAN: Fitur polling notifikasi sudah ditangani secara native dan lebih akurat 
    // oleh script di dalam navbar.php. Kita nonaktifkan di sini untuk mencegah 
    // duplikasi request dan error 404.
    const initNotificationRefresh = () => {
    
    };

    // === 9. KEYBOARD SHORTCUTS ===
    const initKeyboardShortcuts = () => {
        document.addEventListener('keydown', (e) => {
            // Escape to close modals & dropdowns
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active').forEach(m => {
                    m.classList.remove('active');
                    setTimeout(() => m.remove(), 300);
                });
                document.querySelectorAll('.dropdown-menu.show').forEach(m => {
                    m.classList.remove('show');
                });
            }
            
            // Ctrl+K for search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.getElementById('global-search');
                if (searchInput) searchInput.focus();
            }
        });
    };

    // === 10. CHARACTER COUNTER ===
    const initCharacterCounter = () => {
        document.querySelectorAll('[data-maxlength]').forEach(input => {
            const maxLength = parseInt(input.getAttribute('data-maxlength'));
            const counter = input.parentElement.querySelector('.char-counter');
            
            if (counter) {
                const update = () => {
                    counter.textContent = `${input.value.length}/${maxLength}`;
                };
                input.addEventListener('input', update);
                update();
            }
        });
    };

    // === 11. TABLE ROW CLICK ===
    const initTableInteractions = () => {
        document.querySelectorAll('.table tbody tr[data-href]').forEach(row => {
            row.style.cursor = 'pointer';
            row.addEventListener('click', (e) => {
                // Prevent if clicking on action buttons
                if (e.target.closest('a, button, form')) return;
                window.location.href = row.dataset.href;
            });
        });
    };

    // === 12. LOADING STATE ===
    const Loading = {
        show() {
            const loader = document.createElement('div');
            loader.className = 'page-loader';
            loader.id = 'page-loader';
            loader.innerHTML = '<div class="spinner"></div>';
            document.body.appendChild(loader);
        },
        hide() {
            const loader = document.getElementById('page-loader');
            if (loader) {
                loader.classList.add('hide');
                setTimeout(() => loader.remove(), 300);
            }
        }
    };

    window.showLoading = Loading.show;
    window.hideLoading = Loading.hide;

    // === 13. AJAX FORM SUBMISSION ===
    const initAjaxForms = () => {
        document.querySelectorAll('form.ajax-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalHTML = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';
                
                try {
                    const response = await fetch(this.action, {
                        method: this.method || 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        Toast.show(data.message || 'Berhasil', 'success');
                        if (data.redirect) {
                            setTimeout(() => window.location.href = data.redirect, 1500);
                        } else if (data.reload !== false) {
                            setTimeout(() => window.location.reload(), 1500);
                        }
                    } else {
                        Toast.show(data.message || 'Terjadi kesalahan', 'error');
                    }
                } catch (error) {
                    Toast.show('Gagal terhubung ke server', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHTML;
                }
            });
        });
    };

    // === 14. SEARCH FUNCTIONALITY ===
    const initSearch = () => {
        const searchInput = document.getElementById('global-search');
        if (!searchInput) return;
        
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const query = e.target.value.toLowerCase().trim();
                document.querySelectorAll('.searchable-item').forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(query) ? '' : 'none';
                });
            }, 300);
        });
    };

    // === 15. ANIMATE ON SCROLL ===
    const initScrollAnimations = () => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('[data-animate]').forEach(el => {
            el.style.opacity = '0';
            observer.observe(el);
        });
    };

    // === 16. COPY TO CLIPBOARD ===
    const copyToClipboard = async (text) => {
        try {
            await navigator.clipboard.writeText(text);
            Toast.show('Disalin ke clipboard', 'success', 2000);
        } catch (err) {
            Toast.show('Gagal menyalin', 'error');
        }
    };

    window.copyToClipboard = copyToClipboard;

    // === 17. COUNTER ANIMATION (For Stats) ===
    const animateCounter = (element) => {
        const target = parseInt(element.getAttribute('data-count'));
        if (isNaN(target)) return;
        
        const duration = 1500;
        const startTime = performance.now();
        
        const update = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Ease out cubic
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(eased * target);
            
            element.textContent = current.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                element.textContent = target.toLocaleString();
            }
        };
        
        requestAnimationFrame(update);
    };

    const initCounters = () => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        document.querySelectorAll('[data-count]').forEach(el => {
            observer.observe(el);
        });
    };

    // === 18. TOOLTIP ===
    const initTooltips = () => {
        document.querySelectorAll('[data-tooltip]').forEach(el => {
            el.addEventListener('mouseenter', function(e) {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip-floating';
                tooltip.textContent = this.dataset.tooltip;
                tooltip.style.cssText = `
                    position: fixed;
                    background: var(--gray-900);
                    color: white;
                    padding: 0.5rem 0.75rem;
                    border-radius: var(--radius-md);
                    font-size: 0.8rem;
                    z-index: 9999;
                    pointer-events: none;
                    white-space: nowrap;
                    box-shadow: var(--shadow-lg);
                `;
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
                tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
                
                this._tooltip = tooltip;
            });
            
            el.addEventListener('mouseleave', function() {
                if (this._tooltip) {
                    this._tooltip.remove();
                    this._tooltip = null;
                }
            });
        });
    };

    // === 19. AUTO-SAVE DRAFT ===
    const initAutoSave = () => {
        const forms = document.querySelectorAll('form[data-autosave]');
        forms.forEach(form => {
            let timeout;
            const saveKey = `draft_${form.id || window.location.pathname}`;
            
            // Load saved draft
            const saved = localStorage.getItem(saveKey);
            if (saved) {
                const data = JSON.parse(saved);
                Object.entries(data).forEach(([name, value]) => {
                    const field = form.querySelector(`[name="${name}"]`);
                    if (field) field.value = value;
                });
            }
            
            // Auto save on change
            form.addEventListener('input', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    const data = {};
                    form.querySelectorAll('input, textarea, select').forEach(field => {
                        if (field.name && field.type !== 'password' && field.type !== 'file') {
                            data[field.name] = field.value;
                        }
                    });
                    localStorage.setItem(saveKey, JSON.stringify(data));
                }, 1000);
            });
            
            // Clear on submit
            form.addEventListener('submit', () => {
                localStorage.removeItem(saveKey);
            });
        });
    };

    // === 20. CONSOLE BRANDING ===
    const showConsoleBranding = () => {
        console.log(
            '%c SIMBITA Admin Dashboard ',
            'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 16px; font-weight: bold; padding: 8px 12px; border-radius: 6px;'
        );
        console.log(
            '%c Sistem Informasi Bimbingan Tugas Akhir - FINAL MULTIMATE EDITION ',
            'color: #6b7280; font-size: 11px; font-weight: 500;'
        );
        console.log(
            '%c ⚡ Loaded in ' + Math.round(performance.now()) + 'ms',
            'color: #3b82f6; font-size: 11px;'
        );
    };

    // === INIT ALL ===
    const init = () => {
        Toast.init();
        initDropdowns();
        initAlerts();
        initRippleEffect();
        initFileUpload();
        initFormValidation();
        initNotificationRefresh();
        initKeyboardShortcuts();
        initCharacterCounter();
        initTableInteractions();
        initAjaxForms();
        initSearch();
        initScrollAnimations();
        initCounters();
        initTooltips();
        initAutoSave();
        showConsoleBranding();
        
        // Show any flash messages as toasts
        const flashMessages = window.__FLASH_MESSAGES__;
        if (flashMessages) {
            if (flashMessages.error) Toast.show(flashMessages.error, 'error');
            if (flashMessages.success) Toast.show(flashMessages.success, 'success');
            if (flashMessages.warning) Toast.show(flashMessages.warning, 'warning');
            if (flashMessages.info) Toast.show(flashMessages.info, 'info');
        }
    };

    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();