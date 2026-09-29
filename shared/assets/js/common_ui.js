// ── THEME ENGINE v4.0: Apply theme immediately, before page renders ──────────────
// This script block is at the TOP on purpose to avoid FOUC (flash of unstyled content)
(function () {
    const THEMES = [
        'theme-erestor',        // ⭐ 1. Erestor Telemetry (Default - Stitch Design System)
        'theme-corporate',      //    2. Corporate Pro
        'theme-dark-advanced',  //    3. Modo Oscuro Avanzado
        'theme-clean-slate',    //    4. Clean Slate / Minimalismo
        'theme-glass',          //    5. Glassmorphism
        'theme-bento',          //    6. Bento Grid / Modular
        'theme-brutalist'       //    7. Brutalismo Moderno
    ];

    const savedTheme = localStorage.getItem('userTheme') || 'theme-erestor';

    // Apply the theme class to <body> even before the DOM is fully ready
    if (document.body) {
        document.body.classList.add(savedTheme);
    } else {
        document.addEventListener('DOMContentLoaded', function () {
            document.body.classList.add(savedTheme);
        });
    }

    window.applyTheme = function (themeName) {
        THEMES.forEach(t => document.body.classList.remove(t));
        document.body.classList.remove('light-mode'); // Remove legacy class
        document.body.classList.add(themeName);
        localStorage.setItem('userTheme', themeName);

        // Update background to match theme (CSS handles most of this, but set inline fallback)
        const gradients = {
            'theme-erestor':       'radial-gradient(ellipse 90% 60% at 50% 0%, rgba(6, 182, 212, 0.08) 0%, transparent 70%), linear-gradient(180deg, #090D15 0%, #0F131D 100%)',
            'theme-corporate':     'linear-gradient(160deg, #F8FAFC 0%, #EFF6FF 50%, #EEF2FF 100%)',
            'theme-dark-advanced': 'linear-gradient(180deg, #09090B 0%, #0C0C10 100%)',
            'theme-clean-slate':   '#FAFAFA',
            'theme-glass':         'linear-gradient(135deg, #1a1a2e 0%, #16213e 30%, #0f3460 60%, #533483 100%)',
            'theme-bento':         '#FAFAF9',
            'theme-brutalist':     '#F5F5F5'
        };
        document.body.style.background = gradients[themeName] || '';
    };
})();

window.alert = function (message, callback) {
    let alertModal = document.getElementById('neonAlertModal');

    // Function to show the modal once it's in the DOM
    const showModal = (modal) => {
        const msgPara = document.getElementById('neonAlertMessage');
        if (msgPara) msgPara.innerText = message;

        const btn = document.getElementById('neonAlertBtn');
        if (btn) {
            btn.onclick = function () {
                modal.classList.remove('open');
                // Small timeout to allow transition before potentially triggering navigation/blocking logic
                setTimeout(() => {
                    if (typeof callback === 'function') callback();
                }, 300);
            };
        }
        modal.classList.add('open');
    };

    if (!alertModal) {
        alertModal = document.createElement('div');
        alertModal.id = 'neonAlertModal';
        alertModal.className = 'modal-overlay';
        alertModal.style.zIndex = '999999';

        alertModal.innerHTML = `
            <div class="modal-content" style="max-width: 400px; text-align: center; border: 1px solid var(--neon-cyan); box-shadow: 0 0 15px rgba(0, 255, 255, 0.3);">
                <div class="modal-header" style="justify-content: center; border-bottom: none; margin-bottom: 10px;">
                    <h3 class="modal-title" style="color: var(--neon-cyan); text-shadow: 0 0 5px rgba(0, 255, 255, 0.5);">&#9888; Aviso del Sistema</h3>
                </div>
                <p id="neonAlertMessage" style="margin: 20px 0 30px; font-size: 1.1rem; line-height: 1.5; color: #fff;"></p>
                <div style="display: flex; justify-content: center;">
                    <button id="neonAlertBtn" class="btn-glow" style="border-color: var(--neon-cyan); color: var(--neon-cyan); min-width: 120px;">Aceptar</button>
                </div>
            </div>
        `;

        if (document.body) {
            document.body.appendChild(alertModal);
            // Small delay to ensure browser transition picks up the initial state
            setTimeout(() => showModal(alertModal), 20);
        } else {
            document.addEventListener('DOMContentLoaded', () => {
                document.body.appendChild(alertModal);
                setTimeout(() => showModal(alertModal), 20);
            });
        }
    } else {
        showModal(alertModal);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // Auth Check
    const token = sessionStorage.getItem('token');
    const userStr = sessionStorage.getItem('user');

    if (!token || !userStr) {
        if (!window.location.href.includes('login.html')) {
            window.location.href = '../auth/login.html';
        }
    } else {
        const currentUser = JSON.parse(userStr);

        // Display User Info
        const userNameDisplay = document.getElementById('user-display-name');
        const userEmailDisplay = document.getElementById('user-display-email');
        if (userNameDisplay) userNameDisplay.innerText = currentUser.name;
        if (userEmailDisplay) userEmailDisplay.innerText = currentUser.email;

        const isAdmin = ['superadmin', 'admin', 'coordinador', 'supervisor'].includes(currentUser.role);

        // Mostrar Mi Perfil a todos los usuarios siempre
        const menuPerfil = document.getElementById('menu-perfil');
        if (menuPerfil) menuPerfil.style.display = 'block';

        // Role-based Sidebar Content
        if (!isAdmin) {
            const adminItems = ['menu-dashboard', 'menu-usuarios', 'menu-redes', 'menu-sedes', 'menu-eventos', 'menu-premios', 'menu-configuracion', 'menu-areas', 'menu-asignaciones', 'menu-asistencia-admin', 'menu-reportes'];
            adminItems.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });
            const menuAsistencia = document.getElementById('menu-asistencia');
            if (menuAsistencia) menuAsistencia.style.display = 'block';

            // Redirect if trying to access admin pages
            const pathParts = window.location.pathname.split('/');
            const currentPage = pathParts.pop();
            const currentModule = pathParts.pop();

            const forbiddenModules = ['dashboard', 'users', 'networks', 'rewards', 'events', 'reports'];
            if (forbiddenModules.includes(currentModule) && currentPage !== 'perfil.html') {
                window.location.href = '../attendance/asistencia.html';
            }
        }
    }

    // ── Theme Engine: Apply saved theme on load ──────────────────
    const savedTheme = localStorage.getItem('userTheme') || 'theme-erestor';
    window.applyTheme(savedTheme);

    // Theme toggle button cycles through all themes
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        const THEMES = [
            'theme-erestor',
            'theme-corporate',
            'theme-dark-advanced',
            'theme-clean-slate',
            'theme-glass',
            'theme-bento',
            'theme-brutalist'
        ];
        const THEME_LABELS = {
            'theme-erestor':       '🛡️ Erestor Telemetry',
            'theme-corporate':     '🏢 Corporate Pro',
            'theme-dark-advanced': '🌑 Dark Advanced',
            'theme-clean-slate':   '⬜ Clean Slate',
            'theme-glass':         '🔮 Glassmorphism',
            'theme-bento':         '📦 Bento Grid',
            'theme-brutalist':     '⚡ Brutalista'
        };
        themeToggle.title = 'Cambiar Tema';
        themeToggle.addEventListener('click', () => {
            const current = localStorage.getItem('userTheme') || 'theme-erestor';
            const idx = THEMES.indexOf(current);
            const next = THEMES[(idx + 1) % THEMES.length];
            window.applyTheme(next);
            // Show a brief toast indicating which theme was applied
            if (typeof showRealtimeToast === 'function') {
                showRealtimeToast('Tema: ' + (THEME_LABELS[next] || next));
            }
        });
    }

    // Global Logout
    window.logout = function () {
        sessionStorage.clear();
        window.location.href = '../auth/login.html';
    };

    // Global Sidebar Logic (if present)
    const menuToggle = document.getElementById('menuToggle');
    const closeSidebar = document.getElementById('closeSidebar');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (menuToggle && sidebar) {
        function toggleSidebar() {
            sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('open');
        }
        menuToggle.addEventListener('click', toggleSidebar);
        if (closeSidebar) closeSidebar.addEventListener('click', toggleSidebar);
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', toggleSidebar);
    }
});

// AuthFetch Helper
window.authFetch = function (url, options = {}) {
    const token = sessionStorage.getItem('token');
    const headers = options.headers || {};
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }
    return fetch(url, { ...options, headers });
};

// Modal Logic
function openModal(id) {
    document.getElementById(id).classList.add('open');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

// Close modal with ESC
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        const modals = document.querySelectorAll('.modal-overlay.open');
        modals.forEach(m => m.classList.remove('open'));
    }
});

// Global Socket instance
let mainSocket = null;

function initRealtime() {
    if (typeof io === 'undefined') {
        console.warn('Socket.io not loaded. Realtime disabled.');
        return;
    }

    // Connect to Node.js server
    mainSocket = io('http://localhost:3000');
    // Note: For local XAMPP use 'http://localhost:3000'
    // For cPanel, it's usually proxied under /realtime or on a specific port

    mainSocket.on('connect', () => {
        console.log('📡 Connected to Realtime Service');
    });

    mainSocket.on('attendance_update', (data) => {
        showRealtimeToast(`Nueva asistencia: ${data.usuario} en ${data.evento}`);
        window.dispatchEvent(new CustomEvent('attendanceChanged', { detail: data }));
    });

    mainSocket.on('assignment_updated', (data) => {
        // Broadcast local frontend event to update the views
        // Specifically useful for asistencia.html to reload available events
        window.dispatchEvent(new CustomEvent('assignmentChanged', { detail: data }));
        console.log('🔄 Assignment realtime update received:', data);
    });
}

function showRealtimeToast(message) {
    const toast = document.createElement('div');
    toast.className = 'realtime-toast';
    toast.innerText = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}
