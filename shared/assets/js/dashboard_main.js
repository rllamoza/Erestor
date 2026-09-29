document.addEventListener('DOMContentLoaded', () => {
    // Auth Check for Dashboard
    const token = sessionStorage.getItem('token');
    const userStr = sessionStorage.getItem('user');

    if (!token || !userStr) {
        window.location.href = '../auth/login.html';
        return;
    }

    const currentUser = JSON.parse(userStr);

    // Display User Info
    const userNameDisplay = document.getElementById('user-display-name');
    const userEmailDisplay = document.getElementById('user-display-email');
    if (userNameDisplay) userNameDisplay.innerText = currentUser.name;
    if (userEmailDisplay) userEmailDisplay.innerText = currentUser.email;

    // Role-based UI - superadmin y admin tienen acceso completo al dashboard
    const isAdmin = (currentUser.role === 'admin' || currentUser.role === 'superadmin');

    if (!isAdmin) {
        // Hide Admin Menu Items for non-admin roles
        ['menu-usuarios', 'menu-redes', 'menu-sedes', 'menu-eventos', 'menu-premios'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
        const menuAsistencia = document.getElementById('menu-asistencia');
        if (menuAsistencia) menuAsistencia.style.display = 'block';

        const adminStats = document.getElementById('admin-stats');
        if (adminStats) adminStats.style.display = 'none';
    }

    // Theme Toggle is handled in common_ui.js

    // Logout Functionality (Global)
    window.logout = function () {
        sessionStorage.clear();
        window.location.href = '../auth/login.html';
    };

    // --- Chart.js Global Config ---
    Chart.defaults.color = '#8a8a9d';
    Chart.defaults.font.family = "'Rajdhani', sans-serif";

    // (El plugin 'glow' ahora se inicializa dinámicamente desde ChartRenderer.js para soportar arrays de colores)

    // Sidebar Toggle Logic
    const menuToggle = document.getElementById('menuToggle');
    const closeSidebar = document.getElementById('closeSidebar');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        sidebar.classList.toggle('open');
        sidebarOverlay.classList.toggle('open');
    }

    if (menuToggle) menuToggle.addEventListener('click', toggleSidebar);
    if (closeSidebar) closeSidebar.addEventListener('click', toggleSidebar);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', toggleSidebar);

    // Fetch data for top stats headers (Admin or SuperAdmin only)
    if (isAdmin) {
        authFetch('api/get_dashboard_data.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (document.getElementById('stat-users')) document.getElementById('stat-users').innerText = data.stats.users;
                    if (document.getElementById('stat-redes')) document.getElementById('stat-redes').innerText = data.stats.redes;
                    if (document.getElementById('stat-sedes')) document.getElementById('stat-sedes').innerText = data.stats.sedes;
                }
            });

        // Telemetría KPI en tiempo real - Asistencia del día
        authFetch('../../api/telemetry/kpi.php')
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success' && data.kpi) {
                    const k = data.kpi;
                    // Inject into stat boxes if placeholders exist
                    const elHoy = document.getElementById('stat-marcaciones-hoy');
                    const elBio = document.getElementById('stat-biometric');
                    const elEvt = document.getElementById('stat-eventos-activos');
                    if (elHoy) elHoy.innerText = k.marcaciones_hoy;
                    if (elBio) elBio.innerText = k.precision_biometrica_pct + '%';
                    if (elEvt) elEvt.innerText = k.eventos_activos;
                }
            }).catch(() => {}); // Silenciar si no hay datos

        // ----- MAGIA DEL RENDERIZADOR UNIVERSAL ------
        DashboardRenderer.init('dynamicDashboardContainer', 'api', 'Principal');
        // ---------------------------------------------

    } else {
        // Non-admin users are redirected to asistencia directly
        window.location.href = '../attendance/asistencia.html';
        return;
    }
});
