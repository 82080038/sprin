/**
 * SPRIN Core JavaScript Framework
 * Centralized state management, API client, and UI components
 * Version 2.0 - Unified Integration Layer
 */

class SPRINCore {
    constructor() {
        this.state = {
            currentPage: 'dashboard',
            isLoading: false,
            user: null,
            config: {
                baseUrl: '/sprin',
                apiBaseUrl: '/sprin/api',
                debugMode: true
            },
            modules: {}
        };
        
        this.apiClient = new SPRINApiClient(this);
        this.uiHelper = new SPRINUIHelper(this);
        this.eventBus = new SPRINEventBus(this);
        this.stateManager = new SPRINStateManager(this);
        
        this.init();
    }
    
    init() {
        console.log('🚀 SPRIN Core initializing...');
        
        // Setup global references
        window.SPRINT = this;
        window.SPRINT_CORE = this;
        
        // Initialize modules
        this.setupEventHandlers();
        this.setupNavigation();
        this.setupErrorHandling();
        
        // Load initial page
        this.loadPage(this.getInitialPage());
        
        console.log('✅ SPRIN Core initialized');
    }
    
    // Navigation System
    setupNavigation() {
        // Handle navigation clicks
        document.addEventListener('click', (e) => {
            const link = e.target.closest('[data-page]');
            if (link) {
                e.preventDefault();
                const page = link.getAttribute('data-page');
                this.loadPage(page);
            }
        });
        
        // Handle hash changes
        window.addEventListener('hashchange', () => {
            const hash = window.location.hash.substring(1) || 'dashboard';
            this.loadPage(hash, false);
        });
    }
    
    async loadPage(page, addToHistory = true) {
        if (this.state.isLoading || page === this.state.currentPage) {
            return;
        }
        
        this.state.isLoading = true;
        this.showLoading();
        
        try {
            // Update active navigation
            this.updateActiveNavigation(page);
            
            // Load page content
            const response = await fetch(`${this.state.config.baseUrl}/pages/${page}.php`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const html = await response.text();
            
            // Update content
            document.getElementById('main-content').innerHTML = html;
            
            // Update state
            this.state.currentPage = page;
            
            // Update URL
            if (addToHistory) {
                history.pushState({page: page}, '', `#${page}`);
            }
            
            // Initialize page
            this.initializePage(page);
            
            // Update title
            this.updatePageTitle(page);
            
            // Emit page loaded event
            this.eventBus.emit('page:loaded', {page, html});
            
        } catch (error) {
            console.error('Error loading page:', error);
            this.showError(`Gagal memuat halaman: ${error.message}`);
        } finally {
            this.state.isLoading = false;
            this.hideLoading();
        }
    }
    
    initializePage(page) {
        // Clear previous page modules
        this.clearPageModules();
        
        // Initialize page-specific modules
        switch (page) {
            case 'unsur':
                this.modules.unsur = new SPRINUnsurModule(this);
                break;
            case 'bagian':
                this.modules.bagian = new SPRINBagianModule(this);
                break;
            case 'jabatan':
                this.modules.jabatan = new SPRINJabatanModule(this);
                break;
            case 'personil':
                this.modules.personil = new SPRINPersonilModule(this);
                break;
            case 'calendar_dashboard':
                this.modules.calendar = new SPRINCalendarModule(this);
                break;
            case 'backup_management':
                this.modules.backup = new SPRINBackupModule(this);
                break;
            case 'user_management':
                this.modules.users = new SPRINUserModule(this);
                break;
        }
        
        // Setup common handlers
        this.setupCommonHandlers();
        
        // Emit page initialized event
        this.eventBus.emit('page:initialized', {page});
    }
    
    clearPageModules() {
        Object.keys(this.modules).forEach(key => {
            if (this.modules[key] && typeof this.modules[key].destroy === 'function') {
                this.modules[key].destroy();
            }
        });
        this.modules = {};
    }
    
    setupCommonHandlers() {
        // Handle form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.hasAttribute('data-ajax')) {
                e.preventDefault();
                this.handleAjaxForm(e.target);
            }
        });
        
        // Handle modal events
        document.addEventListener('shown.bs.modal', (e) => {
            const firstInput = e.target.querySelector('input:not([type="hidden"])');
            if (firstInput) {
                firstInput.focus();
            }
        });
    }
    
    async handleAjaxForm(form) {
        const formData = new FormData(form);
        const action = formData.get('action');
        const method = form.method || 'POST';
        const url = form.action || window.location.href;
        
        try {
            this.showLoading();
            
            const response = await fetch(url, {
                method: method,
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(data.message || 'Operasi berhasil');
                
                // Handle success actions
                if (data.reload) {
                    setTimeout(() => window.location.reload(), 1500);
                } else if (data.redirect) {
                    window.location.href = data.redirect;
                } else if (data.callback) {
                    // Execute callback function
                    if (typeof window[data.callback] === 'function') {
                        window[data.callback](data);
                    }
                }
            } else {
                this.showError(data.message || 'Operasi gagal');
            }
            
        } catch (error) {
            console.error('Form submission error:', error);
            this.showError('Terjadi kesalahan saat mengirim form');
        } finally {
            this.hideLoading();
        }
    }
    
    updateActiveNavigation(page) {
        // Update navigation links
        document.querySelectorAll('[data-page]').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('data-page') === page) {
                link.classList.add('active');
            }
        });
    }
    
    updatePageTitle(page) {
        const titles = {
            dashboard: 'Dashboard',
            unsur: 'Manajemen Unsur',
            bagian: 'Manajemen Bagian',
            jabatan: 'Manajemen Jabatan',
            personil: 'Manajemen Personil',
            calendar_dashboard: 'Kalender',
            backup_management: 'Backup Management',
            user_management: 'Manajemen Pengguna',
            reporting: 'Laporan'
        };
        
        document.title = titles[page] ? `${titles[page]} - POLRES Samosir` : 'POLRES Samosir';
    }
    
    getInitialPage() {
        return window.location.hash.substring(1) || 'dashboard';
    }
    
    // Loading states
    showLoading(message = 'Loading...') {
        this.state.isLoading = true;
        this.uiHelper.showLoading(message);
    }
    
    hideLoading() {
        this.state.isLoading = false;
        this.uiHelper.hideLoading();
    }
    
    // Notification system
    showSuccess(message) {
        this.uiHelper.showSuccess(message);
    }
    
    showError(message) {
        this.uiHelper.showError(message);
    }
    
    showWarning(message) {
        this.uiHelper.showWarning(message);
    }
    
    showInfo(message) {
        this.uiHelper.showInfo(message);
    }
    
    // Event handling
    setupEventHandlers() {
        // Handle global errors
        window.addEventListener('error', (e) => {
            console.error('Global error:', e.error);
            this.showError('Terjadi kesalahan yang tidak terduga');
        });
        
        // Handle unhandled promise rejections
        window.addEventListener('unhandledrejection', (e) => {
            console.error('Unhandled promise rejection:', e.reason);
            this.showError('Terjadi kesalahan pada operasi asynchronous');
        });
    }
    
    setupErrorHandling() {
        // Override console.error to show errors in production
        if (!this.state.config.debugMode) {
            const originalError = console.error;
            console.error = (...args) => {
                originalError(...args);
                this.showError('Terjadi kesalahan sistem');
            };
        }
    }
    
    // Utility methods
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
}

// API Client Class
class SPRINApiClient {
    constructor(core) {
        this.core = core;
        this.baseUrl = core.state.config.apiBaseUrl;
        this.token = localStorage.getItem('api_token') || null;
        this.unifiedEndpoint = `${this.baseUrl}/unified-api.php`;
    }
    
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            ...options
        };
        
        // Add authentication if available
        if (this.token) {
            config.headers['Authorization'] = `Bearer ${this.token}`;
        }
        
        try {
            const response = await fetch(url, config);
            return await this.handleResponse(response);
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    }
    
    async get(endpoint, params = {}) {
        const url = new URL(endpoint, window.location.origin);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        
        return this.request(url.pathname + url.search, {method: 'GET'});
    }
    
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }
    
    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }
    
    async delete(endpoint) {
        return this.request(endpoint, {
            method: 'DELETE'
        });
    }
    
    async formRequest(endpoint, formData) {
        return this.request(endpoint, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
    }
    
    // Unified API methods
    async getUnsur(action = 'get_all', params = {}) {
        const url = new URL(this.unifiedEndpoint);
        url.searchParams.append('resource', 'unsur');
        url.searchParams.append('action', action);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        
        return this.request(url.pathname + url.search, {method: 'GET'});
    }
    
    async getBagian(action = 'get_all', params = {}) {
        const url = new URL(this.unifiedEndpoint);
        url.searchParams.append('resource', 'bagian');
        url.searchParams.append('action', action);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        
        return this.request(url.pathname + url.search, {method: 'GET'});
    }
    
    async getJabatan(action = 'get_all', params = {}) {
        const url = new URL(this.unifiedEndpoint);
        url.searchParams.append('resource', 'jabatan');
        url.searchParams.append('action', action);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        
        return this.request(url.pathname + url.search, {method: 'GET'});
    }
    
    async getPersonil(action = 'get_all', params = {}) {
        const url = new URL(this.unifiedEndpoint);
        url.searchParams.append('resource', 'personil');
        url.searchParams.append('action', action);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        
        return this.request(url.pathname + url.search, {method: 'GET'});
    }
    
    async getStats(action = 'dashboard') {
        const url = new URL(this.unifiedEndpoint);
        url.searchParams.append('resource', 'stats');
        url.searchParams.append('action', action);
        
        return this.request(url.pathname + url.search, {method: 'GET'});
    }
    
    async createUnsur(data) {
        const formData = new FormData();
        formData.append('action', 'create');
        Object.keys(data).forEach(key => formData.append(key, data[key]));
        
        const url = new URL(this.unifiedEndpoint);
        url.searchParams.append('resource', 'unsur');
        
        return this.formRequest(url.pathname + url.search, formData);
    }
    
    async updateUnsur(id, data) {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('id', id);
        Object.keys(data).forEach(key => formData.append(key, data[key]));
        
        const url = new URL(this.unifiedEndpoint);
        url.searchParams.append('resource', 'unsur');
        
        return this.formRequest(url.pathname + url.search, formData);
    }
    
    async deleteUnsur(id) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        
        const url = new URL(this.unifiedEndpoint);
        url.searchParams.append('resource', 'unsur');
        
        return this.formRequest(url.pathname + url.search, formData);
    }
    
    async handleResponse(response) {
        const text = await response.text();
        
        // Check if response is HTML (error page)
        if (text.trim().startsWith('<!DOCTYPE') || text.includes('<html')) {
            throw new Error('Server returned HTML error page instead of JSON');
        }
        
        try {
            const data = JSON.parse(text);
            
            if (!response.ok) {
                throw new Error(data.message || data.error?.message || 'Request failed');
            }
            
            return data;
        } catch (error) {
            if (error instanceof SyntaxError) {
                console.error('Invalid JSON response:', text.substring(0, 200));
                throw new Error('Invalid JSON response from server');
            }
            throw error;
        }
    }
}

// UI Helper Class
class SPRINUIHelper {
    constructor(core) {
        this.core = core;
    }
    
    showSuccess(message) {
        this.showToast(message, 'success');
    }
    
    showError(message) {
        this.showToast(message, 'error');
    }
    
    showWarning(message) {
        this.showToast(message, 'warning');
    }
    
    showInfo(message) {
        this.showToast(message, 'info');
    }
    
    showToast(message, type = 'info') {
        if (window.toastr) {
            toastr[type](message);
        } else {
            // Fallback to alert
            console.log(`${type.toUpperCase()}: ${message}`);
            alert(message);
        }
    }
    
    showLoading(message = 'Loading...') {
        const loader = document.getElementById('global-loader');
        if (loader) {
            loader.style.display = 'block';
            loader.querySelector('.loading-text').textContent = message;
        }
    }
    
    hideLoading() {
        const loader = document.getElementById('global-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }
    
    showModal(title, content, options = {}) {
        const modalId = 'sprin-modal-' + Date.now();
        const modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1">
                <div class="modal-dialog ${options.size || ''}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${content}
                        </div>
                        <div class="modal-footer">
                            ${options.footer || ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();
        
        // Clean up after modal is hidden
        document.getElementById(modalId).addEventListener('hidden.bs.modal', () => {
            document.getElementById(modalId).remove();
        });
        
        return modal;
    }
    
    confirm(message, onConfirm, onCancel = null) {
        const modal = this.showModal('Konfirmasi', message, {
            footer: `
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirm-btn">Ya</button>
            `
        });
        
        document.getElementById('confirm-btn').addEventListener('click', () => {
            if (onConfirm) onConfirm();
            modal.hide();
        });
        
        return modal;
    }
}

// Event Bus Class
class SPRINEventBus {
    constructor(core) {
        this.core = core;
        this.events = {};
    }
    
    on(event, callback) {
        if (!this.events[event]) {
            this.events[event] = [];
        }
        this.events[event].push(callback);
    }
    
    off(event, callback) {
        if (this.events[event]) {
            this.events[event] = this.events[event].filter(cb => cb !== callback);
        }
    }
    
    emit(event, data) {
        if (this.events[event]) {
            this.events[event].forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error(`Event handler error for ${event}:`, error);
                }
            });
        }
    }
}

// State Manager Class
class SPRINStateManager {
    constructor(core) {
        this.core = core;
        this.state = {};
        this.subscribers = {};
    }
    
    setState(key, value) {
        const oldValue = this.state[key];
        this.state[key] = value;
        
        // Notify subscribers
        if (this.subscribers[key]) {
            this.subscribers[key].forEach(callback => {
                callback(value, oldValue);
            });
        }
        
        // Emit state change event
        this.core.eventBus.emit('state:changed', {key, value, oldValue});
    }
    
    getState(key) {
        return this.state[key];
    }
    
    subscribe(key, callback) {
        if (!this.subscribers[key]) {
            this.subscribers[key] = [];
        }
        this.subscribers[key].push(callback);
        
        // Return unsubscribe function
        return () => {
            this.subscribers[key] = this.subscribers[key].filter(cb => cb !== callback);
        };
    }
}

// Base Module Class
class SPRINBaseModule {
    constructor(core) {
        this.core = core;
        this.apiClient = core.apiClient;
        this.uiHelper = core.uiHelper;
        this.eventBus = core.eventBus;
        this.stateManager = core.stateManager;
    }
    
    init() {
        // Override in child classes
    }
    
    destroy() {
        // Override in child classes
    }
    
    async loadData() {
        // Override in child classes
    }
    
    bindEvents() {
        // Override in child classes
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.SPRINT_CORE = new SPRINCore();
});

// Export for global access
window.SPRINCore = SPRINCore;
window.SPRINApiClient = SPRINApiClient;
window.SPRINUIHelper = SPRINUIHelper;
window.SPRINEventBus = SPRINEventBus;
window.SPRINStateManager = SPRINStateManager;
window.SPRINBaseModule = SPRINBaseModule;
