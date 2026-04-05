<?php

declare(strict_types=1);

/**
 * 404.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

// Load configuration and URL helpers
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/url_helper.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Start session using SessionManager
SessionManager::start();

// Check authentication using AuthHelper
if (!AuthHelper::validateSession()) {
    safe_redirect('login.php');
    exit;
}

$page_title = '404 - Sistem Manajemen POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2><?php echo htmlspecialchars($page_title); ?></h2>
            <p class="text-muted">Halaman 404</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p>Halaman 404 sedang dalam pengembangan.</p>
                    <p>Extracted content from corrupted file:</p>
                    <pre><code><?php echo htmlspecialchars(''); ?></code></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
