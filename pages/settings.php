<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/config.php'; 
require_once __DIR__ . '/../core/auth_check.php'; 
$page_title = 'Settings - POLRES Samosir'; 
include __DIR__ . '/../includes/components/header.php'; 
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-cog me-2"></i>Pengaturan</h1>
        <p class="text-muted">Konfigurasi sistem dan preferensi aplikasi</p>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        Halaman ini dalam pengembangan
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Pengaturan Sistem</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Fitur pengaturan akan segera tersedia.</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
?>
