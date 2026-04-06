/**
 * jQuery Enhanced API Client for POLRES Samosir
 * Version 1.0 - Always returns JSON, DOM-ready
 */

class jQueryApiClient {
    constructor(baseUrl = null) {
        // Use configured base URL or fallback
        this.baseUrl = baseUrl || (window.ApiConfig ? window.ApiConfig.baseUrl : '/api');
        this.token = localStorage.getItem('api_token') || null;
        
        // Always ensure JSON responses
        this.defaultHeaders = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest' // Important for jQuery AJAX
        };
        
        // Debug logging
        if (window.APP_CONFIG && window.APP_CONFIG.debugMode) {
            console.log('jQueryApiClient initialized with baseUrl:', this.baseUrl);
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
    
    // HTTP Methods with jQuery AJAX - Always return JSON
    async get(endpoint, params = {}) {
        return new Promise((resolve, reject) => {
            const url = this.buildUrl(endpoint, params);
            
            $.ajax({
                url: url,
                method: 'GET',
                headers: this.getHeaders(),
                dataType: 'json', // Always expect JSON
                cache: false,
                success: (data, textStatus, jqXHR) => {
                    // Ensure response is in correct format
                    const response = this.normalizeResponse(data);
                    resolve(response);
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    const error = this.handleError(jqXHR, textStatus, errorThrown);
                    reject(error);
                }
            });
        });
    }
    
    async post(endpoint, data = {}) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: this.baseUrl + endpoint,
                method: 'POST',
                headers: this.getHeaders(),
                data: JSON.stringify(data),
                dataType: 'json', // Always expect JSON
                contentType: 'application/json',
                success: (data, textStatus, jqXHR) => {
                    const response = this.normalizeResponse(data);
                    resolve(response);
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    const error = this.handleError(jqXHR, textStatus, errorThrown);
                    reject(error);
                }
            });
        });
    }
    
    async put(endpoint, data = {}) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: this.baseUrl + endpoint,
                method: 'PUT',
                headers: this.getHeaders(),
                data: JSON.stringify(data),
                dataType: 'json', // Always expect JSON
                contentType: 'application/json',
                success: (data, textStatus, jqXHR) => {
                    const response = this.normalizeResponse(data);
                    resolve(response);
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    const error = this.handleError(jqXHR, textStatus, errorThrown);
                    reject(error);
                }
            });
        });
    }
    
    async delete(endpoint) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: this.baseUrl + endpoint,
                method: 'DELETE',
                headers: this.getHeaders(),
                dataType: 'json', // Always expect JSON
                success: (data, textStatus, jqXHR) => {
                    const response = this.normalizeResponse(data);
                    resolve(response);
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    const error = this.handleError(jqXHR, textStatus, errorThrown);
                    reject(error);
                }
            });
        });
    }
    
    // Helper Methods
    buildUrl(endpoint, params = {}) {
        let url = this.baseUrl + endpoint;
        
        if (Object.keys(params).length > 0) {
            const queryString = $.param(params);
            url += '?' + queryString;
        }
        
        return url;
    }
    
    getHeaders() {
        const headers = { ...this.defaultHeaders };
        
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
    
    // Ensure response is always in correct JSON format
    normalizeResponse(data) {
        // If response is already in correct format, return as-is
        if (typeof data === 'object' && data !== null) {
            if (data.hasOwnProperty('success')) {
                return data;
            }
            
            // If response doesn't have success property, wrap it
            return {
                success: true,
                data: data,
                message: 'Success',
                timestamp: new Date().toISOString()
            };
        }
        
        // If response is not an object, create a proper response
        return {
            success: true,
            data: { value: data },
            message: 'Success',
            timestamp: new Date().toISOString()
        };
    }
    
    // Handle errors and ensure JSON format
    handleError(jqXHR, textStatus, errorThrown) {
        let errorData = {
            success: false,
            error: {
                message: 'Request failed',
                code: jqXHR.status || 500,
                textStatus: textStatus,
                errorThrown: errorThrown
            },
            timestamp: new Date().toISOString()
        };
        
        // Try to parse response as JSON
        try {
            if (jqXHR.responseText) {
                const parsedData = JSON.parse(jqXHR.responseText);
                
                // If parsed data has error information, use it
                if (parsedData.hasOwnProperty('error')) {
                    errorData.error = {
                        ...errorData.error,
                        ...parsedData.error
                    };
                } else if (parsedData.hasOwnProperty('success') && !parsedData.success) {
                    errorData.error = {
                        ...errorData.error,
                        message: parsedData.message || parsedData.error?.message || 'Request failed'
                    };
                }
            }
        } catch (e) {
            // If parsing fails, use default error
            console.warn('Could not parse error response as JSON:', e);
        }
        
        // Create error object
        const error = new Error(errorData.error.message);
        error.response = errorData;
        error.status = jqXHR.status;
        error.jqXHR = jqXHR;
        
        return error;
    }
    
    // Bagian API Methods
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
    
    // Personil API Methods
    async getPersonil(params = {}) {
        return this.get('/personil', params);
    }
    
    async getPersonilDetail(id) {
        return this.get(`/personil/${id}`);
    }
    
    // Statistics API Methods
    async getStatsBagian() {
        return this.get('/stats/bagian');
    }
    
    async getStatsPersonil() {
        return this.get('/stats/personil');
    }
    
    async getStatsPangkat() {
        return this.get('/stats/pangkat');
    }
    
    // Test API endpoints
    async testEndpoints() {
        const tests = [];
        
        // Test authentication
        try {
            const authResult = await this.login({ username: 'test', password: 'test' });
            tests.push({
                endpoint: 'POST /auth/login',
                status: 'success',
                message: 'Authentication successful',
                data: authResult
            });
        } catch (error) {
            tests.push({
                endpoint: 'POST /auth/login',
                status: 'error',
                message: error.message,
                data: error.response
            });
        }
        
        // Test get bagian
        try {
            const bagianResult = await this.getBagian();
            tests.push({
                endpoint: 'GET /bagian',
                status: 'success',
                message: `Retrieved ${bagianResult.data.length} bagian`,
                data: bagianResult
            });
        } catch (error) {
            tests.push({
                endpoint: 'GET /bagian',
                status: 'error',
                message: error.message,
                data: error.response
            });
        }
        
        // Test get personil
        try {
            const personilResult = await this.getPersonil({ limit: 5 });
            tests.push({
                endpoint: 'GET /personil',
                status: 'success',
                message: `Retrieved ${personilResult.data.data?.length || 0} personil`,
                data: personilResult
            });
        } catch (error) {
            tests.push({
                endpoint: 'GET /personil',
                status: 'error',
                message: error.message,
                data: error.response
            });
        }
        
        // Test statistics
        try {
            const [bagianStats, personilStats] = await Promise.all([
                this.getStatsBagian(),
                this.getStatsPersonil()
            ]);
            tests.push({
                endpoint: 'GET /stats/*',
                status: 'success',
                message: 'Statistics retrieved successfully',
                data: { bagianStats, personilStats }
            });
        } catch (error) {
            tests.push({
                endpoint: 'GET /stats/*',
                status: 'error',
                message: error.message,
                data: error.response
            });
        }
        
        return tests;
    }
    
    // Utility method for DOM manipulation
    updateElement(elementId, data) {
        const $element = $(`#${elementId}`);
        if ($element.length > 0) {
            if (typeof data === 'object') {
                $element.text(JSON.stringify(data));
            } else {
                $element.text(data);
            }
        }
    }
    
    // Method to update multiple DOM elements
    updateElements(updates) {
        Object.keys(updates).forEach(elementId => {
            this.updateElement(elementId, updates[elementId]);
        });
    }
    
    // Method to populate table with JSON data
    populateTable(tableId, data, columns) {
        const $table = $(`#${tableId}`);
        const $tbody = $table.find('tbody');
        
        if (!Array.isArray(data) || data.length === 0) {
            $tbody.html('<tr><td colspan="' + columns.length + '" class="text-center">No data available</td></tr>');
            return;
        }
        
        let html = '';
        data.forEach((row, index) => {
            html += '<tr>';
            columns.forEach(column => {
                const value = row[column] || '';
                html += `<td>${value}</td>`;
            });
            html += '</tr>';
        });
        
        $tbody.html(html);
    }
}

// jQuery DOM Helper Class
class jQueryDOMHelper {
    static showLoading(elementId, message = 'Loading...') {
        const $element = $(`#${elementId}`);
        $element.html(`<i class="fas fa-spinner fa-spin me-2"></i>${message}`);
    }
    
    static hideLoading(elementId, defaultContent = '') {
        const $element = $(`#${elementId}`);
        $element.html(defaultContent);
    }
    
    static showAlert(containerId, type, message, duration = 5000) {
        const alertId = 'alert-' + Date.now();
        const alertHtml = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $(`#${containerId}`).append(alertHtml);
        
        // Auto-remove after duration
        setTimeout(() => {
            $(`#${alertId}`).fadeOut('slow', function() {
                $(this).remove();
            });
        }, duration);
    }
    
    static updateStatistics(stats) {
        Object.keys(stats).forEach(key => {
            const $element = $(`#${key}`);
            if ($element.length > 0) {
                $element.text(stats[key]);
            }
        });
    }
    
    static bindTableActions(tableId, actions) {
        $(`#${tableId}`).on('click', '.btn-action', function(e) {
            e.preventDefault();
            const action = $(this).data('action');
            const id = $(this).data('id');
            
            if (actions[action]) {
                actions[action](id, $(this));
            }
        });
    }
}

// Initialize global instances
window.jQueryApiClient = jQueryApiClient;
window.jQueryDOMHelper = jQueryDOMHelper;

// Auto-initialize when DOM is ready
$(document).ready(function() {
    console.log('jQuery API Client and DOM Helper loaded');
    
    // Global error handler for AJAX requests
    $(document).ajaxError(function(event, jqXHR, settings, error) {
        console.error('Global AJAX error:', error);
        
        if (window.APP_CONFIG && window.APP_CONFIG.debugMode) {
            console.error('AJAX Error Details:', {
                status: jqXHR.status,
                responseText: jqXHR.responseText,
                settings: settings
            });
        }
    });
    
    // Global success handler for debugging
    $(document).ajaxSuccess(function(event, xhr, settings) {
        if (window.APP_CONFIG && window.APP_CONFIG.debugMode) {
            console.log('AJAX Success:', settings.url, xhr.responseJSON);
        }
    });
});
