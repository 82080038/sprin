    </main>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Global Functions -->
    <script>
        // Toggle Fullscreen
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }

        // Show Help
        function showHelp() {
            alert('Bantuan:\n\n1. Personil-First Flow: Mulai dari manajemen personil\n2. Kepegawaian: Kenaikan pangkat & mutasi jabatan\n3. Penugasan: Definitif, PS, Plt, Pjs, Plh, Pj\n4. Compliance: Monitoring PS ≤ 15%\n\nUntuk bantuan lebih lanjut, hubungi admin.');
        }

        // Show Regulation Info
        function showRegulation(reg) {
            const regs = {
                'PERKAP23': 'PERKAP No. 23/2010 - Pembentukan dan Susunan Organisasi Kepolisian Republik Indonesia',
                'Perpol3': 'Perpol No. 3/2024 - Perubahan atas Perpol No. 7/2020 tentang Organisasi dan Tata Kerja Kepolisian',
                'PP100': 'PP No. 100/2000 - Jabatan Pejabat Pemerintah Sipil dan Gaji Pokoknya'
            };
            alert(regs[reg] || 'Regulasi tidak ditemukan');
        }

        // Show About
        function showAbout() {
            alert('SPRIN v2.0 - Sistem Manajemen Personil\nPersonil-First Flow Architecture\n\nPERKAP No. 23/2010 Compliant\nPerpol No. 3/2024 Compliant\nPP No. 100/2000 Compliant\n\n© 2026 POLRES Samosir');
        }

        // Initialize Bootstrap components
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all dropdowns
            var dropdowns = document.querySelectorAll('.dropdown-toggle');
            dropdowns.forEach(function(dropdown) {
                new bootstrap.Dropdown(dropdown);
            });

            // Initialize all tooltips
            var tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(function(tooltip) {
                new bootstrap.Tooltip(tooltip);
            });
        });
    </script>
</body>
</html>
