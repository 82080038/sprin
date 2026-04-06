<?php
/**
 * API Configuration for Frontend
 */

// Include configuration
require_once __DIR__ . '/../../../core/config.php';

// Pass configuration to JavaScript
$config = [
    'baseUrl' => BASE_URL,
    'apiBaseUrl' => API_BASE_URL,
    'apiVersion' => API_VERSION,
    'appName' => APP_NAME,
    'appVersion' => APP_VERSION,
    'environment' => ENVIRONMENT,
    'debugMode' => DEBUG_MODE
];
?>

<script>
// Global Configuration
window.APP_CONFIG = <?php echo json_encode($config); ?>;

// URL Helper Functions
window.Urls = {
    baseUrl: window.APP_CONFIG.baseUrl,
    apiBaseUrl: window.APP_CONFIG.apiBaseUrl,
    apiVersion: window.APP_CONFIG.apiVersion,
    
    // Generate URLs
    url: function(path) {
        return this.baseUrl + '/' + path.replace(/^\/+/, '');
    },
    
    apiUrl: function(path, version) {
        version = version || this.apiVersion;
        return this.apiBaseUrl + '/' + version + '/' + path.replace(/^\/+/, '');
    },
    
    // Get base API URL (function for compatibility)
    getApiBaseUrl: function() {
        return this.apiBaseUrl;
    },
    
    assetUrl: function(path) {
        return this.baseUrl + '/assets/' + path.replace(/^\/+/, '');
    },
    
    currentUrl: function() {
        return window.location.href;
    }
};

// API Configuration
window.ApiConfig = {
    baseUrl: window.Urls.apiBaseUrl,
    version: window.APP_CONFIG.apiVersion,
    timeout: 30000, // 30 seconds
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Application': window.APP_CONFIG.appName,
        'X-Version': window.APP_CONFIG.appVersion
    }
};

// Debug logging
if (window.APP_CONFIG.debugMode) {
    console.log('Application Configuration:', window.APP_CONFIG);
    console.log('URL Helpers:', window.Urls);
    console.log('API Configuration:', window.ApiConfig);
}
</script>
