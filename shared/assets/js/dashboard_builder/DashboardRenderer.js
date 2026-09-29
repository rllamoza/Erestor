class DashboardRenderer {
    /**
     * @param {string} containerId - ID del contenedor principal
     * @param {string} apiBaseUrl - URL base hasta el directorio api del módulo
     * @param {string} layoutName - Nombre del layout guardado (default 'Principal')
     */
    static async init(containerId, apiBaseUrl, layoutName = 'Principal') {
        const container = document.getElementById(containerId);
        if (!container) return console.error('Contenedor no encontrado: ' + containerId);

        container.style.display = 'grid';
        container.style.gridTemplateColumns = 'repeat(12, 1fr)';
        container.style.gap = '20px';
        container.innerHTML = '<div style="grid-column: span 12; text-align: center; color: var(--neon-cyan)">Cargando Dashboard...</div>';

        try {
            const res = await authFetch(`${apiBaseUrl}/load_layout.php?name=${layoutName}`);
            const data = await res.json();

            if (data.status === 'success' && data.data.layout.length > 0) {
                container.innerHTML = ''; // Limpiar loader
                for (const cfg of data.data.layout) {
                    this.renderWidget(container, cfg, apiBaseUrl);
                }
            } else {
                container.innerHTML = '<div style="grid-column: span 12; text-align: center; color: var(--text-muted)">El dashboard está vacío.</div>';
            }
        } catch (err) {
            container.innerHTML = '<div style="grid-column: span 12; color: #ff3333; text-align: center;">Error al cargar dashboard</div>';
        }
    }

    static async renderWidget(container, cfg, apiBaseUrl) {
        const widget = document.createElement('div');
        widget.className = 'panel';
        widget.style.gridColumn = `span ${cfg.width || 4}`;
        widget.style.display = 'flex';
        widget.style.flexDirection = 'column';
        widget.style.minHeight = '300px';
        widget.innerHTML = `<div style="flex:1; display:flex; flex-direction:column; justify-content:center;"><div style="text-align:center; color:var(--text-muted)">Cargando...</div></div>`;
        container.appendChild(widget);

        const contentDiv = widget.firstChild;

        try {
            const res = await authFetch(`${apiBaseUrl}/execute_query.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    table: cfg.table,
                    aggregation: cfg.agg,
                    target_column: cfg.col,
                    group_by: cfg.groupBy || null
                })
            });
            const data = await res.json();

            if (data.status === 'error') {
                contentDiv.innerHTML = `<div style="color:var(--neon-pink); font-size:12px;">Error: ${data.message}</div>`;
                return;
            }

            // Asegurarse de que ChartRenderer esté disponible (si no está, debe inyectarse o requerirse globalmente)
            if (typeof ChartRenderer === 'undefined') {
                contentDiv.innerHTML = '<div style="color:red">Falta incluir ChartRenderer.js</div>';
                return;
            }

            if (cfg.type === 'kpi') {
                ChartRenderer.renderKPI(contentDiv, data.data, cfg.title);
            } else {
                ChartRenderer.renderChart(contentDiv, cfg.type, data.data, cfg.title);
            }
        } catch (err) {
            contentDiv.innerHTML = `<div style="color:#ff3333; font-size:12px;">Error de red</div>`;
        }
    }
}
