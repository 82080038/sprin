<?php
/**
 * Sidebar Component for SPRIN Application
 */

// Check authentication
$authenticated = isset($_SESSION['user_id']);
$user_role = $_SESSION['user_role'] ?? 'user';
?>

<div class="sidebar" style="background: #f8f9fa; min-height: calc(100vh - 56px); border-right: 1px solid #dee2e6;">
    <div class="p-3">
        <h6 class="text-muted mb-3">
            <i class="fas fa-th-large me-2"></i>
            Menu Navigasi
        </h6>
        
        <nav class="nav flex-column">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'main.php' ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/pages/main.php">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard Utama
            </a>
            
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'personil.php' ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/pages/personil.php">
                <i class="fas fa-users me-2"></i>
                Manajemen Personil
            </a>
            
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'operasional_management.php' ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/pages/operasional_management.php">
                <i class="fas fa-cogs me-2"></i>
                Manajemen Operasional
            </a>
            
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'predictive_analytics_dashboard.php' ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/pages/predictive_analytics_dashboard.php">
                <i class="fas fa-chart-line me-2"></i>
                Predictive Analytics
            </a>
            
            <hr class="my-3">
            
            <h6 class="text-muted mb-3">
                <i class="fas fa-database me-2"></i>
                Master Data
            </h6>
            
            <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/unsur.php">
                <i class="fas fa-sitemap me-2"></i>
                Unsur
            </a>
            
            <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/bagian.php">
                <i class="fas fa-building me-2"></i>
                Bagian
            </a>
            
            <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/jabatan.php">
                <i class="fas fa-briefcase me-2"></i>
                Jabatan
            </a>
            
            <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/pangkat.php">
                <i class="fas fa-star me-2"></i>
                Pangkat
            </a>
            
            <hr class="my-3">
            
            <h6 class="text-muted mb-3">
                <i class="fas fa-tools me-2"></i>
                Utilitas
            </h6>
            
            <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/laporan.php">
                <i class="fas fa-file-alt me-2"></i>
                Laporan
            </a>
            
            <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/backup.php">
                <i class="fas fa-download me-2"></i>
                Backup
            </a>
            
            <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/settings.php">
                <i class="fas fa-cog me-2"></i>
                Pengaturan
            </a>
            
            <?php if ($user_role === 'admin'): ?>
                <hr class="my-3">
                
                <h6 class="text-muted mb-3">
                    <i class="fas fa-user-shield me-2"></i>
                    Admin
                </h6>
                
                <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/user_management.php">
                    <i class="fas fa-users-cog me-2"></i>
                    Manajemen User
                </a>
                
                <a class="nav-link" href="<?php echo BASE_URL; ?>/pages/system_logs.php">
                    <i class="fas fa-list-alt me-2"></i>
                    System Logs
                </a>
            <?php endif; ?>
        </nav>
    </div>
    
    <div class="p-3 mt-auto">
        <div class="text-muted small">
            <div class="mb-2">
                <i class="fas fa-info-circle me-1"></i>
                SPRIN Version 2.0.0
            </div>
            <div>
                <i class="fas fa-clock me-1"></i>
                <?php echo date('d M Y H:i'); ?>
            </div>
        </div>
    </div>
</div>

<style>
.sidebar .nav-link {
    color: #495057;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    margin-bottom: 0.25rem;
    transition: all 0.2s ease-in-out;
}

.sidebar .nav-link:hover {
    color: #007bff;
    background-color: #e9ecef;
}

.sidebar .nav-link.active {
    color: #fff;
    background-color: #007bff;
}

.sidebar .nav-link i {
    width: 20px;
    text-align: center;
}

.sidebar h6 {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>
