document.addEventListener('DOMContentLoaded', () => {
    // Auth Check for Dashboard
    // Auth Check for Dashboard
    const token = sessionStorage.getItem('token');
    const userStr = sessionStorage.getItem('user');

    if (!token || !userStr) {
        window.location.href = 'login.html';
    }

    const currentUser = JSON.parse(userStr);

    // Display User Info
    const userNameDisplay = document.getElementById('user-display-name');
    const userEmailDisplay = document.getElementById('user-display-email');
    if (userNameDisplay) userNameDisplay.innerText = currentUser.name;
    if (userEmailDisplay) userEmailDisplay.innerText = currentUser.email;

    // Role-based UI Restrictions
    // superadmin y admin tienen acceso completo al dashboard
    const isAdmin = currentUser.role === 'admin' || currentUser.role === 'superadmin';
    if (!isAdmin) {
        // Hide Admin Menu Items
        ['menu-usuarios', 'menu-redes', 'menu-sedes', 'menu-eventos', 'menu-premios'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
        // Show Attendance Menu
        const menuAsistencia = document.getElementById('menu-asistencia');
        if (menuAsistencia) menuAsistencia.style.display = 'block';

        // Hide Admin Stats in Header
        const adminStats = document.getElementById('admin-stats');
        if (adminStats) adminStats.style.display = 'none';

        // Optional: Hide entire dashboard content for non-admins if it's meant ONLY for admins
        // document.querySelector('.grid-content').innerHTML = '<h2>Bienvenido, ' + currentUser.name + '</h2><p>Utiliza el menú lateral para registrar tu asistencia.</p>';
    }

    // Theme Toggle Logic
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;

    // Removed legacy light-mode code here

    // Logout Functionality (Global)
    window.logout = function () {
        sessionStorage.clear();
        window.location.href = 'login.html';
    };

    // --- Chart.js Global Config ---
    Chart.defaults.color = '#8a8a9d';
    Chart.defaults.font.family = "'Rajdhani', sans-serif";

    const glowPlugin = {
        id: 'glow',
        beforeDatasetsDraw(chart, args, options) {
            const ctx = chart.ctx;
            chart.data.datasets.forEach((dataset, i) => {
                const meta = chart.getDatasetMeta(i);
                if (!meta.hidden) {
                    ctx.save();
                    ctx.shadowColor = dataset.borderColor || dataset.backgroundColor;
                    ctx.shadowBlur = options.blur || 15;
                    ctx.shadowOffsetX = 0;
                    ctx.shadowOffsetY = 0;
                }
            });
        },
        afterDatasetsDraw(chart) {
            chart.ctx.restore();
        }
    };
    Chart.register(glowPlugin);

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

    // Fetch data from API (Only if Admin or SuperAdmin)
    if (isAdmin) {
        authFetch('api/get_dashboard_data.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (document.getElementById('stat-users')) document.getElementById('stat-users').innerText = data.stats.users;
                    if (document.getElementById('stat-redes')) document.getElementById('stat-redes').innerText = data.stats.redes;
                    if (document.getElementById('stat-sedes')) document.getElementById('stat-sedes').innerText = data.stats.sedes;

                    initCharts(data.charts);
                } else {
                    console.error("Error fetching data:", data.message);
                    initCharts(null);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                initCharts(null);
            });
    } else {
        // Redirigir a asistencia si no es admin y está en el index?
        // O simplemente mostrar un mensaje de bienvenida.
        const gridContent = document.querySelector('.grid-content');
        if (gridContent) {
            gridContent.style.display = 'flex';
            gridContent.style.justifyContent = 'center';
            gridContent.style.alignItems = 'center';
            gridContent.style.minHeight = '60vh';
            gridContent.innerHTML = `
                <div class="panel" style="text-align:center; padding: 40px;">
                    <h1 style="color:var(--neon-cyan); margin-bottom:20px;">¡BIEVENIDO(A)!</h1>
                    <p style="font-size:18px; color:var(--text-main);">Hola, ${currentUser.name}.</p>
                    <p style="color:var(--text-muted); margin-top:10px;">Tu rol actual es <strong>${currentUser.role}</strong>.</p>
                    <a href="asistencia.html" class="btn-glow" style="display:inline-block; margin-top:30px; text-decoration:none;">IR A REGISTRAR ASISTENCIA</a>
                </div>
            `;
        }
    }

    function initCharts(chartsData) {
        const neonPink = '#ff007f';
        const neonPurple = '#8600ff';
        const neonCyan = '#00f0ff';
        const neonBlue = '#0055ff';

        // Helper to format dataset for line chart given historical data
        let datasets_main = [];
        let historical_labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'];

        if (chartsData && chartsData.historical_data && chartsData.historical_labels.length > 0) {
            historical_labels = chartsData.historical_labels;

            let i = 0;
            const colors = [neonPink, neonCyan, neonPurple, neonBlue];
            for (let red_nombre in chartsData.historical_data) {
                datasets_main.push({
                    label: red_nombre,
                    data: chartsData.historical_data[red_nombre],
                    borderColor: colors[i % colors.length],
                    backgroundColor: 'transparent',
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: colors[i % colors.length],
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4
                });
                i++;
            }
        }

        // 1. Main Line Chart (Crecimiento Histórico)
        const ctxMain = document.getElementById('mainLineChart').getContext('2d');
        const mainLineChart = new Chart(ctxMain, {
            type: 'line',
            data: {
                labels: historical_labels,
                datasets: datasets_main.length > 0 ? datasets_main : [
                    {
                        label: 'Sin Datos',
                        data: [0, 0, 0, 0],
                        borderColor: neonPink
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    glow: { blur: 15 }
                },
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.05)' } },
                    x: { grid: { color: 'rgba(255,255,255,0.05)' } }
                }
            }
        });

        // Bar chart labels (Ranking Absoluto)
        let bar_labels = [];
        let bar_data = [];
        let area_labels = [];
        let area_data1 = [];

        if (chartsData && chartsData.ranking_absoluto) {
            bar_labels = chartsData.ranking_absoluto.map(r => r.red_nombre);
            bar_data = chartsData.ranking_absoluto.map(r => r.total_puntos);

            area_labels = bar_labels;
            area_data1 = bar_data; // Usando los mismos para simplificar el área
        }

        // 2. Area Chart (Proyección Ranking)
        const ctxArea = document.getElementById('areaChart').getContext('2d');
        let gradientPink = ctxArea.createLinearGradient(0, 0, 0, 400);
        gradientPink.addColorStop(0, 'rgba(255, 0, 127, 0.5)');
        gradientPink.addColorStop(1, 'rgba(255, 0, 127, 0.0)');

        const areaChart = new Chart(ctxArea, {
            type: 'line',
            data: {
                labels: area_labels.length > 0 ? area_labels : ['A', 'B'],
                datasets: [
                    {
                        label: 'Puntos Actuales',
                        data: area_data1.length > 0 ? area_data1 : [0, 0],
                        borderColor: neonPink,
                        backgroundColor: gradientPink,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, glow: { blur: 10 } },
                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });

        // Doughnut logic (Tiempos de influencia / Miembros en Sede)
        let doug_labels = [];
        let doug_data = [];
        if (chartsData && chartsData.sedes_stats) {
            doug_labels = chartsData.sedes_stats.map(s => s.sede);
            doug_data = chartsData.sedes_stats.map(s => s.miembros);
        }

        // 3. Doughnut Charts
        const ctxD1 = document.getElementById('doughnut1').getContext('2d');
        const doug1 = new Chart(ctxD1, {
            type: 'doughnut',
            data: {
                labels: doug_labels.length > 0 ? doug_labels : ['N/A'],
                datasets: [{
                    data: doug_data.length > 0 ? doug_data : [1],
                    backgroundColor: [neonPink, neonPurple, neonCyan],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: { legend: { display: false }, glow: { blur: 10 } }
            }
        });

        // Segundo Doughnut: Dummy estático de momento (Usuarios activos vs inactivos, por ejemplo)
        const ctxD2 = document.getElementById('doughnut2').getContext('2d');
        const doug2 = new Chart(ctxD2, {
            type: 'doughnut',
            data: {
                labels: ['Activos', 'Inactivos'],
                datasets: [{
                    data: [doug_data.reduce((a, b) => a + b, 0), 1],
                    backgroundColor: [neonCyan, 'rgba(255,255,255,0.1)'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '80%',
                plugins: { legend: { display: false }, glow: { blur: 5 } }
            }
        });

        // 4. Bar Chart (Comparativa Ranking Absoluto)
        const ctxBar = document.getElementById('barChart').getContext('2d');
        const barChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: bar_labels.length > 0 ? bar_labels : ['Vacio'],
                datasets: [{
                    label: 'Puntos Base',
                    data: bar_data.length > 0 ? bar_data : [0],
                    backgroundColor: neonCyan,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, glow: { blur: 8 } },
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // 5. Activity Line Chart (Secondary - static demo as place was getting crowded, can hook into events API)
        const ctxAct = document.getElementById('activityChart').getContext('2d');
        const actChart = new Chart(ctxAct, {
            type: 'line',
            data: {
                labels: ['1', '2', '3', '4', '5', '6'],
                datasets: [{
                    data: [3, 2, 5, 2, 6, 4],
                    borderColor: neonPurple,
                    borderWidth: 2,
                    tension: 0.5,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, glow: { blur: 8 } },
                scales: { x: { display: false }, y: { display: false } }
            }
        });

        // 6. Trend Chart (Bottom Bar/Area mixed logic style - static demo here)
        const ctxTrend = document.getElementById('trendChart').getContext('2d');
        const trendChart = new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: ['A', 'B', 'C', 'D', 'E', 'F'],
                datasets: [{
                    data: [100, 150, 120, 200, 180, 250],
                    borderColor: neonPink,
                    backgroundColor: 'rgba(255,0,127,0.2)',
                    fill: true,
                    tension: 0.1,
                    pointRadius: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, glow: { blur: 12 } },
                scales: { x: { display: false }, y: { grid: { color: 'rgba(255,255,255,0.05)' } } }
            }
        });
    }
});
