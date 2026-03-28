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
                    Version 1.0.0 | © 2026
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
}

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

<!-- Bootstrap JS already loaded in header.php - no duplicate loading needed -->
</body>
</html>
