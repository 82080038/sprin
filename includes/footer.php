<?php
/**
 * Footer Component for SPRIN Application
?>
</div>
<!-- Main Content End -->

<!-- Footer -->
<footer class="bg-light text-center text-muted py-3 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">
                    <i class="fas fa-shield-alt me-2"></i>
                    <strong>SPRIN</strong> - Sistem Manajemen Personil POLRES Samosir
                </p>
            </div>
            <div class="col-md-6">
                <p class="mb-0">
                    &copy; <?php echo date('Y'); ?> POLRES Samosir. All rights reserved.
                </p>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <small>
                    Version 2.0.0 | 
                    <i class="fas fa-code me-1"></i> Developed by IT Team POLRES Samosir
                </small>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Custom JS -->
<script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>

<!-- Page-specific scripts can be added here -->
<?php if (isset($page_scripts)): ?>
    <?php echo $page_scripts; ?>
<?php endif; ?>

</body>
</html>
