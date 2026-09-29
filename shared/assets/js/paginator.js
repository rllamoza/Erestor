/**
 * Helper to handle server-side pagination across the application
 */
class Paginator {
    constructor({
        containerId,      // ID of the tbody or container where rows are rendered
        paginationId,     // ID of the container where pagination controls will be placed
        apiUrl,           // Base API URL
        renderRow,        // Function to render a single row/item
        token,            // Auth token
        limit = 10,       // Records per page
        onDataLoaded      // Optional callback
    }) {
        this.container = document.getElementById(containerId);
        this.paginationContainer = document.getElementById(paginationId);
        this.apiUrl = apiUrl;
        this.renderRow = renderRow;
        this.token = token;
        this.limit = limit;
        this.currentPage = 1;
        this.totalRecords = 0;
        this.totalPages = 0;
        this.onDataLoaded = onDataLoaded;
        this.currentFilters = {};
    }

    async loadPage(page = 1, filters = {}) {
        this.currentPage = page;
        this.currentFilters = filters;

        // Build URL with pagination and filters
        const url = new URL(this.apiUrl, window.location.href);
        url.searchParams.append('page', this.currentPage);
        url.searchParams.append('limit', this.limit);

        if (this.searchTerm) {
            url.searchParams.append('search', this.searchTerm);
        }

        Object.keys(filters).forEach(key => {
            if (filters[key]) url.searchParams.append(key, filters[key]);
        });

        try {
            const response = await fetch(url, {
                headers: { 'Authorization': `Bearer ${this.token}` }
            });
            const data = await response.json();

            if (data.status === 'success') {
                this.totalRecords = data.total_records || 0;
                this.totalPages = Math.ceil(this.totalRecords / this.limit);
                this.render(data.data);
                this.renderControls();
                if (this.onDataLoaded) this.onDataLoaded(data);
            } else {
                console.error('Error loading data:', data.error);
                this.container.innerHTML = `<tr><td colspan="100%" style="text-align:center; padding: 20px; color: var(--neon-pink);">Error: ${data.error}</td></tr>`;
            }
        } catch (error) {
            console.error('Fetch error:', error);
            this.container.innerHTML = `<tr><td colspan="100%" style="text-align:center; padding: 20px; color: var(--neon-pink);">Error de conexión</td></tr>`;
        }
    }

    render(records) {
        this.container.innerHTML = '';
        if (!records || records.length === 0) {
            this.container.innerHTML = `<tr><td colspan="100%" style="text-align:center; padding: 20px; color: var(--text-muted);">No se encontraron registros</td></tr>`;
            return;
        }

        records.forEach(record => {
            const row = this.renderRow(record);
            if (typeof row === 'string') {
                this.container.insertAdjacentHTML('beforeend', row);
            } else {
                this.container.appendChild(row);
            }
        });
    }

    renderControls() {
        if (!this.paginationContainer) return;

        let html = `
            <div class="pagination-wrapper" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; font-family: var(--font-heading); font-size: 0.85rem;">
                <div class="pagination-info" style="color: var(--text-muted);">
                    Mostrando ${Math.min((this.currentPage - 1) * this.limit + 1, this.totalRecords)}-${Math.min(this.currentPage * this.limit, this.totalRecords)} de ${this.totalRecords}
                </div>
                <div class="pagination-buttons" style="display: flex; gap: 10px;">
                    <button class="btn-glow-outline" ${this.currentPage <= 1 ? 'disabled' : ''} onclick="window.paginators['${this.container.id}'].loadPage(${this.currentPage - 1}, window.paginators['${this.container.id}'].currentFilters)" style="padding: 5px 15px; cursor: pointer; border: 1px solid var(--neon-cyan); background: transparent; color: var(--neon-cyan); border-radius: 4px; ${this.currentPage <= 1 ? 'opacity: 0.5; cursor: not-allowed;' : ''}">
                        Anterior
                    </button>
                    <span style="color: var(--text-primary); display: flex; align-items: center; padding: 0 10px;">
                        Página ${this.currentPage} de ${this.totalPages || 1}
                    </span>
                    <button class="btn-glow-outline" ${this.currentPage >= this.totalPages ? 'disabled' : ''} onclick="window.paginators['${this.container.id}'].loadPage(${this.currentPage + 1}, window.paginators['${this.container.id}'].currentFilters)" style="padding: 5px 15px; cursor: pointer; border: 1px solid var(--neon-cyan); background: transparent; color: var(--neon-cyan); border-radius: 4px; ${this.currentPage >= this.totalPages ? 'opacity: 0.5; cursor: not-allowed;' : ''}">
                        Siguiente
                    </button>
                </div>
            </div>
        `;
        this.paginationContainer.innerHTML = html;
    }

    search(term) {
        this.searchTerm = term;
        this.loadPage(1, this.currentFilters);
    }
}

// Global registry for paginators to allow onclick handlers to find them
window.paginators = {};

function initPaginator(config) {
    const paginator = new Paginator(config);
    window.paginators[config.containerId] = paginator;
    return paginator;
}
