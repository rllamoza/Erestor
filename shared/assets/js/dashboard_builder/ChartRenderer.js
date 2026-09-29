class ChartRenderer {
    static initGlowPlugin() {
        if (Chart.registry.plugins.get('glow')) return;
        Chart.register({
            id: 'glow',
            beforeDatasetsDraw(chart, args, options) {
                const ctx = chart.ctx;
                chart.data.datasets.forEach(dataset => {
                    ctx.shadowColor = dataset.borderColor || dataset.backgroundColor[0] || '#00f0ff';
                    ctx.shadowBlur = options.blur || 10;
                });
            }
        });
    }

    static getColors(count) {
        const palette = ['#00f0ff', '#ff007f', '#8600ff', '#f1c40f', '#00ff88', '#ff5f5f', '#a29bfe'];
        let colors = [];
        for (let i = 0; i < count; i++) {
            colors.push(palette[i % palette.length]);
        }
        return colors;
    }

    static renderKPI(container, data, title) {
        container.innerHTML = `
            <div class="kpi-title">${title}</div>
            <div class="kpi-value">${data.value.toLocaleString()}</div>
        `;
    }

    static renderChart(container, type, data, title) {
        this.initGlowPlugin();

        // Agregamos un contenedor wrapper (position relative + flex 1 + min-height 0)
        // Esto es estrictamente requerido por Chart.js + flexbox para no desbordarse infinitamente
        container.innerHTML = `
            <div style="text-align:center; font-family:'Orbitron', sans-serif; color:var(--text-muted); font-size:12px; flex: 0 0 auto; margin-bottom:10px;">${title}</div>
            <div style="position:relative; flex:1 1 auto; width:100%; min-height:0;">
                <canvas></canvas>
            </div>
        `;
        const ctx = container.querySelector('canvas').getContext('2d');

        const colors = this.getColors(data.labels.length);
        const bgColors = colors.map(c => c + '33'); // 20% opacity

        let chartConfig = {
            type: type,
            data: {
                labels: data.labels,
                datasets: [{
                    label: title,
                    data: data.values,
                    backgroundColor: (type === 'line' || type === 'bar') ? bgColors[0] : bgColors,
                    borderColor: (type === 'line' || type === 'bar') ? colors[0] : colors,
                    borderWidth: 2,
                    fill: type === 'line' || type === 'polarArea'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: type !== 'bar' && type !== 'line', labels: { color: '#e0e0e0' } },
                    glow: { blur: 15 }
                }
            }
        };

        if (type === 'bar' || type === 'line') {
            chartConfig.options.scales = {
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#8a8a9d' } },
                x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#8a8a9d' } }
            };
        }

        new Chart(ctx, chartConfig);
    }
}
