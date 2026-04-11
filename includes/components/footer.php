<div class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-shield-alt me-2"></i>POLRES SAMOSIR</h5>
                <p class="mb-0">Sistem Manajemen Personil & Schedule Management</p>
                <small>Bagian Operasional (BAGOPS)</small>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0">
                    <i class="fas fa-user me-1"></i>
                    User: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                </p>
                <p class="mb-0">
                    <i class="fas fa-clock me-1"></i>
                    Login: <?php echo date('d M Y H:i', strtotime($_SESSION['login_time'])); ?>
                </p>
                <small class="text-muted">
                    <i class="fas fa-code me-1"></i>
                    SPRIN v1.7.0-dev | 2026
                </small>
            </div>
        </div>
    </div>
</div>

<style>
.footer {
    background: var(--primary-color);
    color: white;
    padding: 30px 0;
    margin-top: 50px;
}
    
.footer h5 {
    color: var(--accent-color);
    font-weight: bold;
    margin-bottom: 15px;
}

.footer p {
    margin-bottom: 5px;
}

.footer a {
    color: var(--accent-color);
    text-decoration: none;
}

.footer a:hover {
    color: white;
    text-decoration: underline;
}
</style>

<!-- Dark Mode Toggle Button -->
<button id="darkModeToggle" class="btn btn-secondary" onclick="toggleDarkMode()">
    <i class="fas fa-moon"></i>
</button>

<script>
// Dark mode toggle with localStorage persistence
function toggleDarkMode() {
    const html = document.documentElement;
    const isDark = html.getAttribute('data-theme') === 'dark';
    const newTheme = isDark ? 'light' : 'dark';
    
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    const icon = document.querySelector('#darkModeToggle i');
    icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

// Load saved theme on page load
(function() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    const icon = document.querySelector('#darkModeToggle i');
    if (icon) {
        icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
})();
</script>

@media (max-width: 768px) {
    .footer {
        padding: 20px 0;
        margin-top: 30px;
    }
    
    .footer .col-md-6,
    .footer .col-md-6.text-md-end {
        text-align: center !important;
        margin-bottom: 20px;
    }
}
</style>

<!-- Toast Container -->
<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

<!-- Toast Helper Script -->
<script>
/**
 * Global Toast Notification System
 * Usage: showToast(type, message, duration, persistent)
 * type: 'success' | 'danger' | 'warning' | 'info'
 * duration: milliseconds (default: 5000, 0 = persistent)
 * persistent: true = no auto-hide, must click X
 */
function showToast(type, message, duration = 8000, persistent = false) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const toastId = 'toast-' + Date.now();
    const iconMap = {
        success: 'fa-check-circle',
        danger: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    // Persistent toast (no auto-hide) for errors
    const isPersistent = persistent || type === 'danger';
    const actualDuration = isPersistent ? 0 : Math.max(duration, 5000); // Minimum 5 seconds
    
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0 ${isPersistent ? 'persistent-toast' : ''}" 
             role="alert" aria-live="assertive" aria-atomic="true" 
             data-bs-autohide="${!isPersistent}" data-bs-delay="${actualDuration}">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas ${iconMap[type]} me-2"></i>
                    ${message}
                    ${isPersistent ? '<br><small class="text-white-50">(Klik X untuk menutup)</small>' : ''}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    
    // Use Bootstrap toast with proper config
    const toastConfig = isPersistent ? { autohide: false } : { delay: actualDuration, autohide: true };
    const toast = new bootstrap.Toast(toastElement, toastConfig);
    toast.show();
    
    // Auto-remove from DOM after hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

/**
 * Show persistent toast that doesn't auto-hide (for important messages)
 */
function showPersistentToast(type, message) {
    showToast(type, message, 0, true);
}

/**
 * Legacy showAlert - now uses toast with longer duration
 */
function showAlert(type, message) {
    // Alert-style messages stay longer
    const alertDuration = type === 'danger' ? 0 : 10000; // Errors persistent, others 10s
    showToast(type, message, alertDuration, type === 'danger');
}
</script>

<style>
/* Ensure persistent toasts stand out */
.persistent-toast {
    border: 2px solid rgba(255,255,255,0.5) !important;
    box-shadow: 0 0 20px rgba(0,0,0,0.3) !important;
}

/* Prevent toast from being too narrow */
.toast {
    min-width: 300px;
    max-width: 500px;
}

/* Animation for new toasts */
.toast.show {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>

<!-- Bootstrap JS already loaded in header.php - no duplicate loading needed -->
</body>
</html>
