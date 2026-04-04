<?php
declare(strict_types=1);
require_once '../core/config.php'; 
require_once '../core/auth_check.php'; 
$page_title = 'Laporan Personil - POLRES Samosir'; 
include '../includes/components/header.php'; 
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-chart-bar me-2"></i>Laporan Personil</h1>
        <p class="text-muted">Generate dan kelola laporan personil POLRES Samosir</p>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        Halaman ini dalam pengembangan
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Laporan Personil</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Fitur laporan akan segera tersedia.</p>
        </div>
    </div>
</div>

<?php include '../includes/components/footer.php'; ?>
