<?php
/**
 * Main Dashboard Page
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Dashboard - SPRIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .sidebar { background: #343a40; min-height: 100vh; }
        .sidebar .nav-link { color: #fff; padding: 15px 20px; }
        .sidebar .nav-link:hover { background: #495057; }
        .sidebar .nav-link.active { background: #007bff; }
        .main-content { padding: 20px; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 sidebar p-0">
                <div class="p-3 text-center bg-dark text-white">
                    <h4>SPIN</h4>
                    <small>Sistem Manajemen Personil</small>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="#">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link" href="/pages/personil.php">
                        <i class="fas fa-users"></i> Personil
                    </a>
                    <a class="nav-link" href="/pages/bagian.php">
                        <i class="fas fa-building"></i> Bagian
                    </a>
                    <a class="nav-link" href="#">
                        <i class="fas fa-id-badge"></i> Jabatan
                    </a>
                    <a class="nav-link" href="#">
                        <i class="fas fa-cogs"></i> Unsur
                    </a>
                    <hr class="text-white">
                    <a class="nav-link" href="/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>
            <div class="col-md-9 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard</h2>
                    <div>
                        <span class="text-muted">Welcome, </span>
                        <strong><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></strong>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Personil</h5>
                                <h2>0</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Bagian</h5>
                                <h2>0</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Jabatan</h5>
                                <h2>0</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Unsur</h5>
                                <h2>0</h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="/pages/personil.php" class="btn btn-primary w-100">
                                    <i class="fas fa-plus"></i> Add Personil
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="/pages/bagian.php" class="btn btn-success w-100">
                                    <i class="fas fa-plus"></i> Add Bagian
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <button class="btn btn-info w-100">
                                    <i class="fas fa-file-export"></i> Export Data
                                </button>
                            </div>
                            <div class="col-md-6 mb-3">
                                <button class="btn btn-warning w-100">
                                    <i class="fas fa-cog"></i> Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">No recent activity to display.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>