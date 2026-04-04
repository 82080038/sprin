/**
 * Font Awesome 6 Configuration - Centralized
 * Use only Font Awesome 6 Free for consistency
 */

// Font Awesome 6 CDN
const FA6_CSS_URL = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
const FA6_JS_URL = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js';

// Font Awesome 6 Icons Reference for common actions
const FA_ICONS = {
    // Navigation
    'dashboard' => 'fa-solid fa-gauge-high',
    'users' => 'fa-solid fa-users',
    'calendar' => 'fa-solid fa-calendar-days',
    'logout' => 'fa-solid fa-right-from-bracket',
    'login' => 'fa-solid fa-right-to-bracket',
    
    // Actions
    'add' => 'fa-solid fa-plus',
    'edit' => 'fa-solid fa-pen-to-square',
    'delete' => 'fa-solid fa-trash-can',
    'save' => 'fa-solid fa-floppy-disk',
    'cancel' => 'fa-solid fa-xmark',
    'search' => 'fa-solid fa-magnifying-glass',
    'filter' => 'fa-solid fa-filter',
    'export' => 'fa-solid fa-file-export',
    'import' => 'fa-solid fa-file-import',
    
    // Status
    'active' => 'fa-solid fa-check-circle',
    'inactive' => 'fa-solid fa-circle-xmark',
    'warning' => 'fa-solid fa-triangle-exclamation',
    'info' => 'fa-solid fa-circle-info',
    'success' => 'fa-solid fa-check',
    'error' => 'fa-solid fa-circle-exclamation',
    
    // UI
    'menu' => 'fa-solid fa-bars',
    'close' => 'fa-solid fa-xmark',
    'refresh' => 'fa-solid fa-rotate',
    'loading' => 'fa-solid fa-spinner fa-spin',
    'arrow-right' => 'fa-solid fa-arrow-right',
    'arrow-left' => 'fa-solid fa-arrow-left',
    'arrow-up' => 'fa-solid fa-arrow-up',
    'arrow-down' => 'fa-solid fa-arrow-down',
    
    // Data
    'file' => 'fa-solid fa-file',
    'excel' => 'fa-solid fa-file-excel',
    'pdf' => 'fa-solid fa-file-pdf',
    'print' => 'fa-solid fa-print',
    'download' => 'fa-solid fa-download',
    'upload' => 'fa-solid fa-upload',
    
    // Security
    'lock' => 'fa-solid fa-lock',
    'unlock' => 'fa-solid fa-lock-open',
    'shield' => 'fa-solid fa-shield-halved',
    'eye' => 'fa-solid fa-eye',
    'eye-slash' => 'fa-solid fa-eye-slash',
];

/**
 * Get Font Awesome icon class
 */
function getIcon($iconName) {
    return FA_ICONS[$iconName] ?? 'fa-solid fa-circle';
}

/**
 * Generate icon HTML
 */
function icon($iconName, $classes = '') {
    $iconClass = getIcon($iconName);
    $extraClasses = $classes ? ' ' . $classes : '';
    return "<i class="{$iconClass}{$extraClasses}"></i>";
}
