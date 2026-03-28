/**
 * RESTful API Client for POLRES Samosir
 * Version 1.0 - Using BASE_URL Configuration
 */

class ApiClient {
    constructor(baseUrl = null) {
        // Use configured base URL or fallback
        this.baseUrl = baseUrl || (window.ApiConfig ? window.ApiConfig.baseUrl : '/api');
        this.token = localStorage.getItem('api_token') || null;
        
        // Debug logging
        if (window.APP_CONFIG && window.APP_CONFIG.debugMode) {
            console.log('ApiClient initialized with baseUrl:', this.baseUrl);
        }
    }
    
    // Authentication
    async login(credentials = {}) {
        try {
            const response = await this.post('/auth/login', credentials);
            if (response.success && response.data.token) {
                this.token = response.data.token;
                localStorage.setItem('api_token', this.token);
            }
            return response;
        } catch (error) {
            console.error('Login failed:', error);
            throw error;
        }
    }
    
    async logout() {
        try {
            const response = await this.post('/auth/logout');
            this.token = null;
            localStorage.removeItem('api_token');
            return response;
        } catch (error) {
            console.error('Logout failed:', error);
            throw error;
        }
    }
    
    // HTTP methods
    async get(endpoint, params = {}) {
        const url = new URL(this.baseUrl + endpoint, window.location.origin);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        
        const response = await fetch(url.toString(), {
            method: 'GET',
            headers: this.getHeaders()
        });
        
        return this.handleResponse(response);
    }
    
    async post(endpoint, data = {}) {
        const response = await fetch(this.baseUrl + endpoint, {
            method: 'POST',
            headers: this.getHeaders(),
            body: JSON.stringify(data)
        });
        
        return this.handleResponse(response);
    }
    
    async put(endpoint, data = {}) {
        const response = await fetch(this.baseUrl + endpoint, {
            method: 'PUT',
            headers: this.getHeaders(),
            body: JSON.stringify(data)
        });
        
        return this.handleResponse(response);
    }
    
    async delete(endpoint) {
        const response = await fetch(this.baseUrl + endpoint, {
            method: 'DELETE',
            headers: this.getHeaders()
        });
        
        return this.handleResponse(response);
    }
    
    // Helper methods
    getHeaders() {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
        
        // Add application headers if available
        if (window.ApiConfig && window.ApiConfig.headers) {
            Object.assign(headers, window.ApiConfig.headers);
        }
        
        // Add authorization if token exists
        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }
        
        return headers;
    }
    
    async handleResponse(response) {
        const text = await response.text();
        
        try {
            const data = JSON.parse(text);
            
            if (!response.ok) {
                throw new Error(data.error?.message || 'Request failed');
            }
            
            return data;
        } catch (error) {
            if (error instanceof SyntaxError) {
                throw new Error('Invalid JSON response');
            }
            throw error;
        }
    }
    
    // Bagian API methods
    async getBagian(params = {}) {
        // Use URL helper if available, otherwise fallback
        const endpoint = (window.Urls && window.Urls.url) ? 
            window.Urls.url('api/simple.php').replace(window.Urls.baseUrl, '') : 
            '/simple.php';
        
        return this.get(endpoint, params);
    }
    
    async getBagianDetail(id) {
        return this.get(`/bagian/${id}`);
    }
    
    async createBagian(data) {
        return this.post('/bagian', data);
    }
    
    async updateBagian(id, data) {
        return this.put(`/bagian/${id}`, data);
    }
    
    async deleteBagian(id) {
        return this.delete(`/bagian/${id}`);
    }
    
    // Personil API methods
    async getPersonil(params = {}) {
        return this.get('/personil', params);
    }
    
    async getPersonilDetail(id) {
        return this.get(`/personil/${id}`);
    }
    
    // Statistics API methods
    async getStatsBagian() {
        return this.get('/stats/bagian');
    }
    
    async getStatsPersonil() {
        return this.get('/stats/personil');
    }
    
    async getStatsPangkat() {
        return this.get('/stats/pangkat');
    }
}

// UI Helper Class
class UIHelper {
    static showAlert(type, message, duration = 5000) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container') || document.body;
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, duration);
    }
    
    static showLoading(element, show = true) {
        if (show) {
            element.disabled = true;
            element.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
        } else {
            element.disabled = false;
            element.innerHTML = element.dataset.originalText || element.textContent;
        }
    }
    
    static showModal(title, content, actions = []) {
        const modalHtml = `
            <div class="modal fade" id="dynamicModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${content}
                        </div>
                        <div class="modal-footer">
                            ${actions.map(action => `
                                <button type="button" class="btn ${action.class || 'btn-secondary'}" 
                                        onclick="${action.onclick}"
                                        ${action.dismiss ? 'data-bs-dismiss="modal"' : ''}>
                                    ${action.text}
                                </button>
                            `).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('dynamicModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        const modal = new bootstrap.Modal(document.getElementById('dynamicModal'));
        modal.show();
        
        return modal;
    }
    
    static confirm(message, onConfirm) {
        const modal = this.showModal('Konfirmasi', message, [
            {
                text: 'Batal',
                class: 'btn-secondary',
                dismiss: true
            },
            {
                text: 'Ya',
                class: 'btn-primary',
                onclick: onConfirm,
                dismiss: true
            }
        ]);
        
        return modal;
    }
}

// Bagian Management Class
class BagianManager {
    constructor(apiClient) {
        this.api = apiClient;
        this.bagianData = [];
        this.currentPage = 1;
        this.pageSize = 10;
    }
    
    async loadBagian() {
        try {
            UIHelper.showLoading(document.querySelector('[data-action="load-bagian"]'), true);
            
            const response = await this.api.getBagian();
            
            if (response.success) {
                this.bagianData = response.data;
                this.renderBagianTable();
                this.updateStatistics();
            } else {
                UIHelper.showAlert('danger', response.error?.message || 'Failed to load bagian');
            }
        } catch (error) {
            console.error('Load bagian error:', error);
            UIHelper.showAlert('danger', 'Failed to load bagian: ' + error.message);
        } finally {
            UIHelper.showLoading(document.querySelector('[data-action="load-bagian"]'), false);
        }
    }
    
    renderBagianTable() {
        const tbody = document.querySelector('#bagianTable tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        this.bagianData.forEach((bagian, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${bagian.nama_bagian}</td>
                <td>${bagian.type || 'BAG/SAT/SIE'}</td>
                <td>${bagian.personil_count || 0}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info btn-sm" onclick="bagianManager.viewBagian(${bagian.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-warning btn-sm" onclick="bagianManager.editBagian(${bagian.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="bagianManager.deleteBagian(${bagian.id}, '${bagian.nama_bagian}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    async createBagian(data) {
        try {
            const response = await this.api.createBagian(data);
            
            if (response.success) {
                UIHelper.showAlert('success', 'Bagian berhasil ditambahkan');
                await this.loadBagian();
                return true;
            } else {
                UIHelper.showAlert('danger', response.error?.message || 'Failed to create bagian');
                return false;
            }
        } catch (error) {
            console.error('Create bagian error:', error);
            UIHelper.showAlert('danger', 'Failed to create bagian: ' + error.message);
            return false;
        }
    }
    
    async updateBagian(id, data) {
        try {
            const response = await this.api.updateBagian(id, data);
            
            if (response.success) {
                UIHelper.showAlert('success', 'Bagian berhasil diperbarui');
                await this.loadBagian();
                return true;
            } else {
                UIHelper.showAlert('danger', response.error?.message || 'Failed to update bagian');
                return false;
            }
        } catch (error) {
            console.error('Update bagian error:', error);
            UIHelper.showAlert('danger', 'Failed to update bagian: ' + error.message);
            return false;
        }
    }
    
    async deleteBagian(id, nama) {
        UIHelper.confirm(
            `Apakah Anda yakin ingin menghapus bagian "${nama}"?`,
            async () => {
                try {
                    const response = await this.api.deleteBagian(id);
                    
                    if (response.success) {
                        UIHelper.showAlert('success', 'Bagian berhasil dihapus');
                        await this.loadBagian();
                    } else {
                        UIHelper.showAlert('danger', response.error?.message || 'Failed to delete bagian');
                    }
                } catch (error) {
                    console.error('Delete bagian error:', error);
                    UIHelper.showAlert('danger', 'Failed to delete bagian: ' + error.message);
                }
            }
        );
    }
    
    viewBagian(id) {
        // Implementation for viewing bagian details
        console.log('View bagian:', id);
    }
    
    editBagian(id) {
        // Implementation for editing bagian
        console.log('Edit bagian:', id);
    }
    
    async updateStatistics() {
        try {
            const [bagianStats, personilStats] = await Promise.all([
                this.api.getStatsBagian(),
                this.api.getStatsPersonil()
            ]);
            
            if (bagianStats.success) {
                document.querySelector('#totalBagian').textContent = bagianStats.data.total_bagian;
            }
            
            if (personilStats.success) {
                document.querySelector('#totalPersonil').textContent = personilStats.data.total_personil;
            }
        } catch (error) {
            console.error('Update statistics error:', error);
        }
    }
    
    showAddModal() {
        const content = `
            <form id="addBagianForm">
                <div class="mb-3">
                    <label for="nama_bagian" class="form-label">Nama Bagian</label>
                    <input type="text" class="form-control" id="nama_bagian" name="nama_bagian" required>
                </div>
            </form>
        `;
        
        const modal = UIHelper.showModal('Tambah Bagian', content, [
            {
                text: 'Batal',
                class: 'btn-secondary',
                dismiss: true
            },
            {
                text: 'Simpan',
                class: 'btn-primary',
                onclick: async () => {
                    const form = document.getElementById('addBagianForm');
                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData);
                    
                    if (await this.createBagian(data)) {
                        bootstrap.Modal.getInstance(document.getElementById('dynamicModal')).hide();
                    }
                }
            }
        ]);
        
        return modal;
    }
}

// Initialize API client and bagian manager
const apiClient = new ApiClient();
const bagianManager = new BagianManager(apiClient);

// Auto-load when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Load initial data
    bagianManager.loadBagian();
    
    // Setup event listeners
    document.querySelector('[data-action="add-bagian"]')?.addEventListener('click', () => {
        bagianManager.showAddModal();
    });
    
    document.querySelector('[data-action="load-bagian"]')?.addEventListener('click', () => {
        bagianManager.loadBagian();
    });
    
    console.log('API Client initialized');
});

// Export for global access
window.apiClient = apiClient;
window.bagianManager = bagianManager;
window.UIHelper = UIHelper;
