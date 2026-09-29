document.addEventListener('DOMContentLoaded', () => {
    let schemaCache = null;
    let activeWidget = null;

    // Inicializar Drag and Drop (SortableJS)
    new Sortable(document.getElementById('widgetPalette'), {
        group: { name: 'shared', pull: 'clone', put: false },
        animation: 150,
        sort: false
    });

    new Sortable(document.getElementById('builderCanvas'), {
        group: 'shared',
        animation: 150,
        filter: '.delete, .edit', // Ignorar clics en botones de control
        onAdd: function (evt) {
            const el = evt.item;
            el.className = 'canvas-widget';
            // Set grid column width
            const width = el.dataset.width || 4;
            el.style.gridColumn = `span ${width}`;

            // Add unique ID
            const uuid = 'fw_' + Math.random().toString(36).substr(2, 9);
            el.id = uuid;

            // Reemplazar contenido base por estructura de canvas
            const title = el.dataset.type === 'kpi' ? 'Nuevo KPI' : 'Nuevo ' + el.dataset.type;
            el.innerHTML = `
                <div class="widget-controls">
                    <button class="btn-icon edit" onclick="editWidget('${uuid}')">⚙️</button>
                    <button class="btn-icon delete" onclick="deleteWidget('${uuid}')">✖</button>
                </div>
                <div class="widget-content" style="flex:1; display:flex; flex-direction:column; justify-content:center;">
                    <div style="text-align:center; color:var(--text-muted)">Haga click en ⚙️ para configurar</div>
                </div>
            `;

            // Set default config data
            el.dataset.config = JSON.stringify({
                title: title,
                type: el.dataset.type,
                table: '',
                groupBy: '',
                agg: 'COUNT',
                col: 'id',
                width: width,
                raw_sql: '',
                sql_visual: 'bar'
            });

            // Auto open edit
            editWidget(uuid);
        }
    });

    // Fetchear esquema de BD
    function loadSchema() {
        return authFetch('api/get_schema.php')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    schemaCache = data.data;
                    populateTableSelect();
                }
            });
    }

    function populateTableSelect() {
        const sel = document.getElementById('cfgTable');
        sel.innerHTML = '<option value="">Seleccione Tabla...</option>';
        for (let key in schemaCache) {
            sel.innerHTML += `<option value="${key}">${schemaCache[key].name}</option>`;
        }
    }

    document.getElementById('cfgTable').addEventListener('change', (e) => {
        const tableName = e.target.value;
        const selGroup = document.getElementById('cfgGroupBy');
        const selCol = document.getElementById('cfgCol');

        selGroup.innerHTML = '<option value="">(Ninguno)</option>';
        selCol.innerHTML = '';

        if (!tableName || !schemaCache[tableName]) return;

        const cols = schemaCache[tableName].columns;
        for (let col in cols) {
            // Se puede agrupar por categorías, booleanos o fechas
            if (cols[col] !== 'Numérico') {
                selGroup.innerHTML += `<option value="${col}">${col} (${cols[col]})</option>`;
            }
            // Para operar (COUNT, SUM) sirve numérico y booleano (para SUM de 1s), o id para COUNT
            selCol.innerHTML += `<option value="${col}">${col} (${cols[col]})</option>`;
        }
    });

    window.editWidget = function (id) {
        activeWidget = document.getElementById(id);
        const cfg = JSON.parse(activeWidget.dataset.config);

        document.getElementById('configPanel').classList.add('active');
        document.getElementById('cfgWidgetId').value = id;
        document.getElementById('cfgTitle').value = cfg.title;
        document.getElementById('cfgTable').value = cfg.table;
        document.getElementById('cfgAgg').value = cfg.agg;
        document.getElementById('cfgWidth').value = cfg.width;

        // Limpiar contenedores
        const stdCon = document.getElementById('standard-config-container');
        const sqlCon = document.getElementById('sql-config-container');

        if (cfg.type === 'sql') {
            stdCon.style.display = 'none';
            sqlCon.style.display = 'block';
            document.getElementById('cfgSql').value = cfg.raw_sql || '';
            document.getElementById('cfgSqlVisual').value = cfg.sql_visual || 'bar';
        } else {
            stdCon.style.display = 'block';
            sqlCon.style.display = 'none';
            // Mostrar u ocultar el campo de Agrupación dependiendo del tipo de gráfico
            const groupByContainer = document.getElementById('group-by-container');
            if (cfg.type === 'kpi') {
                groupByContainer.style.display = 'none';
            } else {
                groupByContainer.style.display = 'block';
            }
        }

        // Disparar change event manually to populate columns
        if (cfg.table) {
            const ev = new Event('change');
            document.getElementById('cfgTable').dispatchEvent(ev);
            setTimeout(() => {
                document.getElementById('cfgGroupBy').value = cfg.groupBy;
                document.getElementById('cfgCol').value = cfg.col;
            }, 100);
        } else {
            document.getElementById('cfgGroupBy').innerHTML = '<option value="">(Ninguno)</option>';
            document.getElementById('cfgCol').innerHTML = '';
        }
    };

    window.deleteWidget = function (id) {
        document.getElementById(id).remove();
        if (activeWidget && activeWidget.id === id) {
            document.getElementById('configPanel').classList.remove('active');
            activeWidget = null;
        }
    };

    document.getElementById('btnApplyConfig').addEventListener('click', () => {
        if (!activeWidget) return;

        const type = JSON.parse(activeWidget.dataset.config).type;
        let cfg = {};

        if (type === 'sql') {
            const rawSql = document.getElementById('cfgSql').value.trim();
            if (!rawSql) return alert('Por favor, ingresa una consulta SQL.');

            cfg = {
                title: document.getElementById('cfgTitle').value || 'SQL Personalizado',
                type: type,
                table: '', groupBy: '', agg: '', col: '',
                width: document.getElementById('cfgWidth').value,
                raw_sql: rawSql,
                sql_visual: document.getElementById('cfgSqlVisual').value
            };
        } else {
            const table = document.getElementById('cfgTable').value;
            if (!table) return alert('Por favor, selecciona una "Tabla origen" de datos.');

            const groupBy = document.getElementById('cfgGroupBy').value;
            const col = document.getElementById('cfgCol').value;

            if (type !== 'kpi' && !groupBy) {
                return alert('⚠️ Para este tipo de gráfico es OBLIGATORIO seleccionar una "Agrupación (Eje X)".');
            }

            if (!col) {
                return alert('⚠️ Debes seleccionar una "Columna a operar (Eje Y)".');
            }

            cfg = {
                title: document.getElementById('cfgTitle').value || 'Sin Título',
                type: type,
                table: table,
                groupBy: type === 'kpi' ? '' : groupBy,
                agg: document.getElementById('cfgAgg').value,
                col: col,
                width: document.getElementById('cfgWidth').value,
                raw_sql: '', sql_visual: 'bar'
            };
        }

        // Guardar config
        activeWidget.dataset.config = JSON.stringify(cfg);
        activeWidget.dataset.width = cfg.width;
        activeWidget.style.gridColumn = `span ${cfg.width}`;

        // Llamar API y renderizar
        renderWidgetData(activeWidget, cfg);
    });

    function renderWidgetData(widget, cfg) {
        const contentDiv = widget.querySelector('.widget-content');
        contentDiv.innerHTML = '<div style="text-align:center; color:var(--text-muted)">Cargando datos...</div>';

        const payload = cfg.type === 'sql'
            ? { is_sql: true, raw_sql: cfg.raw_sql }
            : {
                table: cfg.table,
                aggregation: cfg.agg,
                target_column: cfg.col,
                group_by: cfg.groupBy || null
            };

        authFetch('api/execute_query.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'error') {
                    contentDiv.innerHTML = `<div style="color:var(--neon-pink); font-size:12px; padding:10px;">Error SQL: <br> ${data.message}</div>`;
                    return;
                }

                if (cfg.type === 'kpi') {
                    ChartRenderer.renderKPI(contentDiv, data.data, cfg.title);
                } else if (cfg.type === 'sql') {
                    ChartRenderer.renderChart(contentDiv, cfg.sql_visual, data.data, cfg.title);
                } else {
                    ChartRenderer.renderChart(contentDiv, cfg.type, data.data, cfg.title);
                }
            })
            .catch(err => {
                contentDiv.innerHTML = `<div style="color:#ff3333; font-size:12px;">Error de red</div>`;
            });
    }

    document.getElementById('btnSaveDashboard').addEventListener('click', () => {
        const widgets = [];
        document.querySelectorAll('#builderCanvas .canvas-widget').forEach(w => {
            widgets.push(JSON.parse(w.dataset.config));
        });

        authFetch('api/save_layout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: 'Principal', // En una versión más avanzada se pediría el nombre del dashboard
                layout: widgets
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Layout guardado exitosamente');
                } else {
                    alert(data.message);
                }
            });
    });

    // Cargar esquema inicial
    loadSchema().then(() => {
        // Al final, intentar cargar layout guardado
        authFetch('api/load_layout.php?name=Principal')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.data.layout.length > 0) {
                    const canvas = document.getElementById('builderCanvas');
                    data.data.layout.forEach(cfg => {
                        const uuid = 'fw_' + Math.random().toString(36).substr(2, 9);
                        const el = document.createElement('div');
                        el.className = 'canvas-widget';
                        el.id = uuid;
                        el.dataset.width = cfg.width;
                        el.dataset.type = cfg.type;
                        el.dataset.config = JSON.stringify(cfg);
                        el.style.gridColumn = `span ${cfg.width}`;

                        el.innerHTML = `
                        <div class="widget-controls">
                            <button class="btn-icon edit" onclick="editWidget('${uuid}')">⚙️</button>
                            <button class="btn-icon delete" onclick="deleteWidget('${uuid}')">✖</button>
                        </div>
                        <div class="widget-content" style="flex:1; display:flex; flex-direction:column; justify-content:center;">
                        </div>
                    `;
                        canvas.appendChild(el);
                        renderWidgetData(el, cfg);
                    });
                }
            });
    });
});
