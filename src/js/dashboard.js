/**
 * Faydev Dashboard — Client-side Logic
 * Theme toggle, sidebar, toasts, CSRF, modals, confirm dialogs
 */

(() => {
    'use strict';

    /* ─── THEME ────────────────────────────────────────────────── */
    const htmlEl    = document.documentElement;
    const THEME_KEY = 'faydev-dashboard-theme';

    function applyTheme(theme) {
        htmlEl.setAttribute('data-theme', theme);
        localStorage.setItem(THEME_KEY, theme);

        const icon = document.querySelector('#themeToggle i');
        if (icon) {
            icon.className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
        }
    }

    function initTheme() {
        const saved = localStorage.getItem(THEME_KEY);
        if (saved) {
            applyTheme(saved);
        } else {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            applyTheme(prefersDark ? 'dark' : 'light');
        }
    }

    initTheme();

    document.getElementById('themeToggle')?.addEventListener('click', () => {
        const current = htmlEl.getAttribute('data-theme');
        applyTheme(current === 'dark' ? 'light' : 'dark');
    });


    /* ─── SIDEBAR ──────────────────────────────────────────────── */
    const sidebar        = document.getElementById('sidebar');
    const mainContent    = document.getElementById('mainContent');
    const sidebarToggle  = document.getElementById('sidebarToggle');
    const SIDEBAR_KEY    = 'faydev-sidebar-collapsed';

    function setSidebarState(collapsed) {
        if (!sidebar) return;

        if (collapsed) {
            sidebar.classList.add('collapsed');
            mainContent?.classList.add('sidebar-collapsed');
        } else {
            sidebar.classList.remove('collapsed');
            mainContent?.classList.remove('sidebar-collapsed');
        }

        localStorage.setItem(SIDEBAR_KEY, collapsed ? '1' : '0');
    }

    function initSidebar() {
        const isMobile = window.innerWidth <= 768;

        if (isMobile) {
            sidebar?.classList.remove('collapsed');
            sidebar?.classList.remove('open');
            return;
        }

        const saved = localStorage.getItem(SIDEBAR_KEY);
        if (saved === '1') {
            setSidebarState(true);
        }
    }

    initSidebar();

    sidebarToggle?.addEventListener('click', () => {
        const isMobile = window.innerWidth <= 768;

        if (isMobile) {
            sidebar.classList.toggle('open');
            const backdrop = document.querySelector('.sidebar-backdrop');

            if (sidebar.classList.contains('open')) {
                if (!backdrop) {
                    const el = document.createElement('div');
                    el.className = 'sidebar-backdrop';
                    el.addEventListener('click', () => {
                        sidebar.classList.remove('open');
                        el.remove();
                    });
                    document.body.appendChild(el);
                }
            } else {
                backdrop?.remove();
            }
        } else {
            const isCollapsed = sidebar.classList.contains('collapsed');
            setSidebarState(!isCollapsed);
        }
    });

    window.addEventListener('resize', () => {
        const isMobile = window.innerWidth <= 768;
        if (isMobile) {
            sidebar?.classList.remove('collapsed');
            mainContent?.classList.remove('sidebar-collapsed');
            document.querySelector('.sidebar-backdrop')?.remove();
        } else {
            sidebar?.classList.remove('open');
            document.querySelector('.sidebar-backdrop')?.remove();
        }
    });


    /* ─── CSRF ─────────────────────────────────────────────────── */
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }


    /* ─── FETCH HELPER ─────────────────────────────────────────── */
    /**
     * Wrapper around fetch that auto-injects CSRF for POST and handles JSON.
     * @param {string} url
     * @param {object} opts — same as fetch options, but `body` can be FormData or object
     * @returns {Promise<{success: boolean, data?: any, message?: string}>}
     */
    async function api(url, opts = {}) {
        const method = (opts.method || 'GET').toUpperCase();
        const headers = opts.headers || {};

        if (method === 'POST') {
            headers['X-CSRF-Token'] = getCsrfToken();
        }

        let body = opts.body;

        if (body && !(body instanceof FormData) && typeof body === 'object') {
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify(body);
        }

        const res = await fetch(url, {
            method,
            headers,
            body,
            credentials: 'same-origin',
        });

        if (res.status === 401) {
            window.location.href = '/login.php';
            return { success: false, message: 'Session expired' };
        }

        return res.json();
    }


    /* ─── TOAST NOTIFICATIONS ──────────────────────────────────── */
    const toastContainer = document.getElementById('toastContainer');

    /**
     * Show a toast notification.
     * @param {string} message
     * @param {'success'|'error'|'info'} type
     * @param {number} duration — ms
     */
    function showToast(message, type = 'info', duration = 3500) {
        if (!toastContainer) return;

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', info: 'fa-info-circle' };
        toast.innerHTML = `
            <i class="fas ${icons[type] || icons.info}"></i>
            <span>${escHtml(message)}</span>
            <button class="toast-close" aria-label="Close">&times;</button>
        `;

        toast.querySelector('.toast-close').addEventListener('click', () => removeToast(toast));
        toastContainer.appendChild(toast);

        // Trigger animation
        requestAnimationFrame(() => toast.classList.add('show'));

        setTimeout(() => removeToast(toast), duration);
    }

    function removeToast(toast) {
        toast.classList.remove('show');
        toast.addEventListener('transitionend', () => toast.remove(), { once: true });
        // Fallback removal
        setTimeout(() => { if (toast.parentNode) toast.remove(); }, 400);
    }


    /* ─── MODAL / CONFIRM DIALOG ───────────────────────────────── */
    const modalOverlay = document.getElementById('modalOverlay');

    /**
     * Show a confirmation modal.
     * @param {string} title
     * @param {string} message
     * @param {string} confirmLabel
     * @param {'danger'|'primary'} confirmType
     * @returns {Promise<boolean>}
     */
    function showConfirm(title, message, confirmLabel = 'Confirm', confirmType = 'danger') {
        return new Promise((resolve) => {
            if (!modalOverlay) {
                resolve(confirm(message));
                return;
            }

            modalOverlay.innerHTML = `
                <div class="modal">
                    <div class="modal-header">
                        <h3>${escHtml(title)}</h3>
                    </div>
                    <div class="modal-body">
                        <p>${escHtml(message)}</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-action="cancel">Cancel</button>
                        <button class="btn btn-${confirmType}" data-action="confirm">${escHtml(confirmLabel)}</button>
                    </div>
                </div>
            `;

            modalOverlay.classList.add('show');

            function cleanup(result) {
                modalOverlay.classList.remove('show');
                modalOverlay.innerHTML = '';
                resolve(result);
            }

            modalOverlay.querySelector('[data-action="cancel"]').addEventListener('click', () => cleanup(false));
            modalOverlay.querySelector('[data-action="confirm"]').addEventListener('click', () => cleanup(true));
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) cleanup(false);
            }, { once: true });
        });
    }


    /* ─── LOGOUT ───────────────────────────────────────────────── */
    document.getElementById('logoutBtn')?.addEventListener('click', async (e) => {
        e.preventDefault();

        const confirmed = await showConfirm('Logout', 'Are you sure you want to logout?', 'Logout', 'danger');
        if (!confirmed) return;

        const result = await api('api/auth.php?action=logout', { method: 'POST' });

        if (result.success) {
            window.location.href = 'login.php';
        } else {
            showToast('Logout failed', 'error');
        }
    });


    /* ─── UTILITY ──────────────────────────────────────────────── */
    function escHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Format a date string for display.
     */
    function formatDate(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    /**
     * Format a datetime string for display.
     */
    function formatDateTime(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit',
        });
    }


    /* ─── UNSAVED CHANGES WARNING ──────────────────────────────── */
    let hasUnsavedChanges = false;

    function trackUnsavedChanges(form) {
        if (!form) return;

        form.addEventListener('input', () => { hasUnsavedChanges = true; });
        form.addEventListener('change', () => { hasUnsavedChanges = true; });

        form.addEventListener('submit', () => { hasUnsavedChanges = false; });
    }

    window.addEventListener('beforeunload', (e) => {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });


    /* ─── IMAGE UPLOAD ZONE ────────────────────────────────────── */
    function initUploadZone(zoneEl, inputEl, previewEl) {
        if (!zoneEl || !inputEl) return;

        zoneEl.addEventListener('click', () => inputEl.click());

        zoneEl.addEventListener('dragover', (e) => {
            e.preventDefault();
            zoneEl.classList.add('drag-over');
        });

        zoneEl.addEventListener('dragleave', () => {
            zoneEl.classList.remove('drag-over');
        });

        zoneEl.addEventListener('drop', (e) => {
            e.preventDefault();
            zoneEl.classList.remove('drag-over');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                inputEl.files = files;
                inputEl.dispatchEvent(new Event('change'));
            }
        });

        inputEl.addEventListener('change', () => {
            const file = inputEl.files[0];
            if (!file || !previewEl) return;

            // Client-side validation
            const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                showToast('Invalid file type. Use JPEG, PNG, or WebP.', 'error');
                inputEl.value = '';
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                showToast('File too large. Maximum 5MB.', 'error');
                inputEl.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                previewEl.src = e.target.result;
                previewEl.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    }


    /* ─── PUBLIC API ───────────────────────────────────────────── */
    window.Dashboard = {
        api,
        showToast,
        showConfirm,
        escHtml,
        formatDate,
        formatDateTime,
        getCsrfToken,
        trackUnsavedChanges,
        initUploadZone,
    };

    window.showToast = showToast;
})();
