/**
 * Puppeteer Test Configuration
 * SPRIN Application Testing
 */

module.exports = {
    // Base URL for testing
    baseUrl: 'http://localhost/sprint',
    
    // Test credentials
    credentials: {
        username: 'bagops',
        password: 'admin123'
    },
    
    // Browser configuration
    browser: {
        headless: false, // Show browser UI
        slowMo: 10,      // Faster execution (reduced from 50)
        defaultViewport: {
            width: 1366,
            height: 768
        },
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--disable-gpu',
            '--window-size=1366,768',
            '--start-maximized', // Start maximized
            '--disable-extensions', // Disable extensions for faster startup
            '--disable-infobars'
        ]
    },
    
    // Test timeouts (reduced for faster testing)
    timeouts: {
        navigation: 15000, // Reduced from 30000
        element: 5000,     // Reduced from 10000
        operation: 3000     // Reduced from 5000
    },
    
    // Test selectors
    selectors: {
        login: {
            usernameInput: '#username',
            passwordInput: '#password',
            submitButton: 'button[type="submit"], .btn-login',
            quickLoginButton: '.btn-quick-login'
        },
        navigation: {
            menuPersonil: 'a[href*="personil.php"]',
            menuBagian: 'a[href*="bagian.php"]',
            menuUnsur: 'a[href*="unsur.php"]',
            menuJabatan: 'a[href*="jabatan.php"]',
            menuCalendar: 'a[href*="calendar_dashboard.php"]',
            logoutButton: 'a[href*="logout.php"]'
        },
        dashboard: {
            totalPersonil: '#totalPersonil, .total-personil',
            polriCount: '#polriCount, .polri-count',
            asnCount: '#asnCount, .asn-count',
            statisticsContainer: '.stats, .statistics'
        },
        personil: {
            addButton: 'button:has-text("Tambah"), .btn-tambah, [data-action="add"], .btn-add',
            saveButton: 'button:has-text("Simpan"), .btn-save, [type="submit"]',
            table: '.table, table, .data-table, .personil-table',
            searchInput: 'input[type="search"], .search-input, #search, #searchInput',
            nrkInput: 'input[name="nrk"], #nrk',
            namaInput: 'input[name="nama_lengkap"], #nama_lengkap, #nama',
            nrpInput: 'input[name="nrp"], #nrp',
            deleteButton: '.btn-delete, button:has-text("Hapus")',
            personilContent: '#personilContent, .personil-content'
        }
    },
    
    // Output configuration
    output: {
        screenshots: '/opt/lampp/htdocs/sprint/tests/puppeteer/results/screenshots',
        reports: '/opt/lampp/htdocs/sprint/tests/puppeteer/results',
        videos: false
    }
};
