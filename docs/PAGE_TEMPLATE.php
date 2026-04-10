<?php
// Start output buffering
if (ob_get_level() === 0) {
    ob_start();
}

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

SessionManager::start();

if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

$page_title = 'Page Title - POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';

require_once __DIR__ . '/../core/Database.php';
$pdo = Database::getInstance()->getConnection();

// Handle AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    // Handle POST requests
    exit;
}
?>
<!-- Page content here -->

<script>
// Initialize CSRF
document.addEventListener('DOMContentLoaded', async function() {
    if (!window.APP_CONFIG?.csrfToken) {
        // Fetch token
    }
});

// Standardized API call
async function apiCall(url, data) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': window.APP_CONFIG?.csrfToken
        },
        credentials: 'same-origin',
        body: new URLSearchParams(data)
    });
    return response.json();
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
