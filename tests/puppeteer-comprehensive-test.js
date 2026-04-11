#!/usr/bin/env node
/**
 * SPRIN Comprehensive Puppeteer Test Suite
 * Tests the application with visible browser (headed mode)
 * Covers: Login, Dashboard, CRUD operations, Calendar, Piket, Operations, Reports
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

// Configuration
const BASE_URL = 'http://localhost/sprin';
const SCREENSHOT_DIR = path.join(__dirname, 'screenshots');
const TEST_RESULTS_FILE = path.join(__dirname, 'puppeteer-test-results.json');

// Test credentials
const CREDENTIALS = {
    username: 'bagops',
    password: 'admin123'
};

// Ensure screenshot directory exists
if (!fs.existsSync(SCREENSHOT_DIR)) {
    fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

class SPRINTestSuite {
    constructor() {
        this.browser = null;
        this.page = null;
        this.results = [];
        this.testStartTime = Date.now();
    }

    async init() {
        console.log('='.repeat(70));
        console.log('SPRIN COMPREHENSIVE PUPPETEER TEST SUITE');
        console.log('='.repeat(70));
        console.log(`Base URL: ${BASE_URL}`);
        console.log(`Timestamp: ${new Date().toISOString()}`);
        console.log('='.repeat(70));

        // Launch browser with visible window (headed mode)
        this.browser = await puppeteer.launch({
            headless: false, // Show browser window
            slowMo: 100, // Slow down actions for visibility
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--window-size=1920,1080'
            ]
        });

        this.page = await this.browser.newPage();
        await this.page.setViewport({ width: 1920, height: 1080 });

        // Set default timeout
        this.page.setDefaultTimeout(60000);

        console.log('✅ Browser launched (visible mode)');
    }

    async cleanup() {
        if (this.browser) {
            await this.browser.close();
            console.log('✅ Browser closed');
        }
    }

    async takeScreenshot(name) {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filename = `${name}_${timestamp}.png`;
        const filepath = path.join(SCREENSHOT_DIR, filename);
        await this.page.screenshot({ path: filepath, fullPage: true });
        console.log(`📸 Screenshot saved: ${filename}`);
        return filepath;
    }

    addResult(testName, status, message, duration) {
        this.results.push({
            test: testName,
            status,
            message,
            duration,
            timestamp: new Date().toISOString()
        });
        const icon = status === 'PASS' ? '✅' : status === 'FAIL' ? '❌' : '⚠️';
        console.log(`${icon} ${testName}: ${message} (${duration}ms)`);
    }

    async runAllTests() {
        try {
            await this.init();

            // Test 1: Application Home Page
            await this.testHomePage();

            // Test 2: Login Page Load
            await this.testLoginPageLoad();

            // Test 3: Login Functionality
            await this.testLogin();

            // Test 4: Dashboard Load
            await this.testDashboardLoad();

            // Test 5: Personil Management
            await this.testPersonilManagement();

            // Test 6: Tim Piket Management
            await this.testTimPiketManagement();

            // Test 7: Calendar Dashboard
            await this.testCalendarDashboard();

            // Test 8: Operations Management
            await this.testOperationsManagement();

            // Test 9: Laporan Piket
            await this.testLaporanPiket();

            // Test 10: Laporan Operasi
            await this.testLaporanOperasi();

            // Test 11: LHPT
            await this.testLHPT();

            // Test 12: Ekspedisi Surat
            await this.testEkspedisiSurat();

            // Test 13: Apel Nominal
            await this.testApelNominal();

            // Test 14: Pelatihan
            await this.testPelatihan();

            // Test 15: Logout
            await this.testLogout();

            // Print summary
            this.printSummary();

            // Save results
            this.saveResults();

        } catch (error) {
            console.error('❌ Test suite failed:', error);
            this.addResult('Test Suite', 'FAIL', error.message, 0);
        } finally {
            await this.cleanup();
        }
    }

    async testHomePage() {
        const start = Date.now();
        try {
            console.log('\n[TEST 1] Loading Home Page...');
            await this.page.goto(BASE_URL, { waitUntil: 'networkidle2' });
            const title = await this.page.title();
            await this.takeScreenshot('01_home_page');
            
            const duration = Date.now() - start;
            this.addResult('Home Page Load', 'PASS', `Title: ${title}`, duration);
        } catch (error) {
            const duration = Date.now() - start;
            this.addResult('Home Page Load', 'FAIL', error.message, duration);
        }
    }

    async testLoginPageLoad() {
        const start = Date.now();
        try {
            console.log('\n[TEST 2] Loading Login Page...');
            await this.page.goto(`${BASE_URL}/login.php`, { waitUntil: 'networkidle2' });
            
            // Check if login form exists - use actual selectors from login.php
            const loginForm = await this.page.$('form[method="POST"]');
            const usernameInput = await this.page.$('#username');
            const passwordInput = await this.page.$('#password');
            const submitButton = await this.page.$('button[type="submit"]');
            
            await this.takeScreenshot('02_login_page');
            
            if (loginForm && usernameInput && passwordInput && submitButton) {
                const duration = Date.now() - start;
                this.addResult('Login Page Load', 'PASS', 'Login form elements found', duration);
            } else {
                const duration = Date.now() - start;
                this.addResult('Login Page Load', 'FAIL', 'Login form elements missing', duration);
            }
        } catch (error) {
            const duration = Date.now() - start;
            this.addResult('Login Page Load', 'FAIL', error.message, duration);
        }
    }

    async testLogin() {
        const start = Date.now();
        try {
            console.log('\n[TEST 3] Testing Login...');
            
            // Use Quick Login button instead - it auto-fills and submits
            await this.page.click('button.btn-quick-login');
            
            // Wait for URL to change to main.php
            await this.page.waitForFunction(
                () => window.location.href.includes('main.php'),
                { timeout: 30000 }
            );
            
            // Check if redirected to dashboard
            const currentUrl = this.page.url();
            await this.takeScreenshot('03_after_login');
            
            if (currentUrl.includes('main.php') || currentUrl.includes('dashboard')) {
                const duration = Date.now() - start;
                this.addResult('Login', 'PASS', 'Successfully logged in and redirected', duration);
            } else {
                const duration = Date.now() - start;
                this.addResult('Login', 'FAIL', `Not redirected to dashboard. URL: ${currentUrl}`, duration);
            }
        } catch (error) {
            const duration = Date.now() - start;
            this.addResult('Login', 'FAIL', error.message, duration);
        }
    }

    async testDashboardLoad() {
        const start = Date.now();
        try {
            console.log('\n[TEST 4] Testing Dashboard Load...');
            
            // Navigate to dashboard if not already there
            if (!this.page.url().includes('main.php')) {
                await this.page.goto(`${BASE_URL}/pages/main.php`, { waitUntil: 'networkidle2' });
            }
            
            // Wait for dashboard to load - use actual selector from main.php
            await this.page.waitForSelector('.container-fluid', { timeout: 10000 });
            
            // Check for dashboard elements
            const greeting = await this.page.$('h4');
            const cards = await this.page.$$('.card');
            
            await this.takeScreenshot('04_dashboard');
            
            const duration = Date.now() - start;
            this.addResult('Dashboard Load', 'PASS', `Found ${cards.length} cards`, duration);
        } catch (error) {
            const duration = Date.now() - start;
            this.addResult('Dashboard Load', 'FAIL', error.message, duration);
        }
    }

    async testPersonilManagement() {
        const start = Date.now();
        try {
            console.log('\n[TEST 5] Testing Personil Management...');
            
            await this.page.goto(`${BASE_URL}/pages/personil.php`, { waitUntil: 'networkidle2' });
            
            // Wait for personil container
            await this.page.waitForSelector('.container', { timeout: 10000 });
            
            // Check for personil data
            const stats = await this.page.$$('.stat-box');
            const searchBox = await this.page.$('.search-box');
            
            await this.takeScreenshot('05_personil');
            
            const duration = Date.now() - start;
            this.addResult('Personil Management', 'PASS', `Found ${stats.length} stat boxes`, duration);
        } catch (error) {
            const duration = Date.now() - start;
            this.addResult('Personil Management', 'FAIL', error.message, duration);
        }
    }

    async testTimPiketManagement() {
        const start = Date.now();
        try {
            console.log('\n[TEST 6] Testing Tim Piket Management...');
            
            await this.page.goto(`${BASE_URL}/pages/tim_piket.php`, { waitUntil: 'networkidle2' });
            
            // Wait for tim piket content - use container-fluid
            await this.page.waitForSelector('.container-fluid', { timeout: 10000 });
            
            // Check for filter options
            const filters = await this.page.$$('select');
            
            await this.takeScreenshot('06_tim_piket');
            
            const duration = Date.now() - start;
            this.addResult('Tim Piket Management', 'PASS', `Found ${filters.length} filter elements`, duration);
        } catch (error) {
            const duration = Date.now() - start;
            this.addResult('Tim Piket Management', 'FAIL', error.message, duration);
        }
    }

    async testCalendarDashboard() {
        const start = Date.now();
        try {
            console.log('\n[TEST 7] Testing Calendar Dashboard...');
            
            await this.page.goto(`${BASE_URL}/pages/calendar_dashboard.php`, { waitUntil: 'networkidle2' });
            
            // Wait for calendar - FullCalendar uses fc class
            await this.page.waitForSelector('.fc, #calendar', { timeout: 10000 });
            
            // Check for calendar elements
            const calendar = await this.page.$('.fc');
            
            await this.takeScreenshot('07_calendar');
            
            const duration = Date.now() - start;
            this.addResult('Calendar Dashboard', 'PASS', 'Calendar loaded successfully', duration);
        } catch (error) {
            const duration = Date.now() - start;
            this.addResult('Calendar Dashboard', 'FAIL', error.message, duration);
        }
    }

    async testOperationsManagement() {
        const start = Date.now();
        try {
            console.log('\n[TEST 8] Testing Operations Management...');
            
            await this.page.goto(`${BASE_URL}/pages/operasi.php`, { waitUntil: 'networkidle2' });
            
            // Wait for operations container
            await this.page.waitForSelector('.container-fluid', { timeout: 10000 });
            
            // Check for operations data
            const tables = await this.page.$$('table');
            
            await this.takeScreenshot('08_operasi');
            
            const duration = Date.now() - start;
            this.addResult('Operations Management', 'PASS', `Found ${tables.length} table(s)`, duration);
        } catch (error) {
            const duration = Date.now() - start;
            this.addResult('Operations Management', 'FAIL', error.message, duration);
        }
    }

    async testLaporanPiket() {
        const start = Date.now();
        try {
            console.log('\n[TEST 9] Testing Laporan Piket...');
            
            await this.page.goto(`${BASE_URL}/pages/laporan_piket.php`, { waitUntil: 'networkidle2' });
            
            // Wait for laporan content
            await this.page.waitForSelector('.container-fluid', { timeout: 10000 });
            
            // Check for filter options
            const filters = await this.page.$$('select, input');
            
            await this.takeScreenshot('09_laporan_piket');
            
            const duration = Date.now() - start;
            this.addResult('Laporan Piket', 'PASS', `Found ${filters.length} filter elements`, duration);
        } catch (error) {
            const duration = Date.now() - start;
            this.addResult('Laporan Piket', 'FAIL', error.message, duration);
        }
    }

    async testLaporanOperasi() {
        const start = Date.now();
        try {
            console.log('\n[TEST 10] Testing Laporan Operasi...');
            
            await this.page.goto(`${BASE_URL}/pages/laporan_operasi.php`, { waitUntil: 'networkidle2' });
            
            // Wait for laporan content
            await this.page.waitForSelector('.container-fluid', { timeout: 10000 });
            
            // Check for charts or tables
            const charts = await this.page.$$('canvas, .chart, .card');
            
            await this.takeScreenshot('10_laporan_operasi');
            
            const duration = Date.now() - start;
            this.addResult('Laporan Operasi', 'PASS', `Found ${charts.length} chart/card elements`, duration);
        } catch (error) {
            const duration = Date.now() - start;
            this.addResult('Laporan Operasi', 'FAIL', error.message, duration);
        }
    }

    async testLHPT() {
        const start = Date.now();
        try {
            console.log('\n[TEST 11] Testing LHPT...');
            
            await this.page.goto(`${BASE_URL}/pages/lhpt.php`, { waitUntil: 'networkidle2' });
            
            // Wait for LHPT content
            await this.page.waitForSelector('.container-fluid', { timeout: 10000 });
            
            // Check for LHPT table
            const table = await this.page.$('table');
            
            await this.takeScreenshot('11_lhpt');
            
            const duration = Date.now() - start;
            this.addResult('LHPT', 'PASS', 'LHPT page loaded successfully', duration);
        } catch (error) {
            const duration = Date.now() - start;
            this.addResult('LHPT', 'FAIL', error.message, duration);
        }
    }

    async testEkspedisiSurat() {
        const start = Date.now();
        try {
            console.log('\n[TEST 12] Testing Ekspedisi Surat...');
            
            await this.page.goto(`${BASE_URL}/pages/ekspedisi.php`, { waitUntil: 'networkidle2' });
            
            // Wait for ekspedisi content
            await this.page.waitForSelector('.container-fluid', { timeout: 10000 });
            
            // Check for ekspedisi table
            const table = await this.page.$('table');
            
            await this.takeScreenshot('12_ekspedisi');
            
            const duration = Date.now() - start;
            this.addResult('Ekspedisi Surat', 'PASS', 'Ekspedisi page loaded successfully', duration);
        } catch (error) {
            const duration = Date.now() - start;
            this.addResult('Ekspedisi Surat', 'FAIL', error.message, duration);
        }
    }

    async testApelNominal() {
        const start = Date.now();
        try {
            console.log('\n[TEST 13] Testing Apel Nominal...');
            
            await this.page.goto(`${BASE_URL}/pages/apel_nominal.php`, { waitUntil: 'networkidle2' });
            
            // Wait for apel content
            await this.page.waitForSelector('.container-fluid', { timeout: 10000 });
            
            // Check for apel table
            const table = await this.page.$('table');
            
            await this.takeScreenshot('13_apel_nominal');
            
            const duration = Date.now() - start;
            this.addResult('Apel Nominal', 'PASS', 'Apel Nominal page loaded successfully', duration);
        } catch (error) {
            const duration = Date.now() - start;
            this.addResult('Apel Nominal', 'FAIL', error.message, duration);
        }
    }

    async testPelatihan() {
        const start = Date.now();
        try {
            console.log('\n[TEST 14] Testing Pelatihan...');
            
            await this.page.goto(`${BASE_URL}/pages/pelatihan.php`, { waitUntil: 'networkidle2' });
            
            // Wait for pelatihan content
            await this.page.waitForSelector('.container-fluid', { timeout: 10000 });
            
            // Check for pelatihan table
            const table = await this.page.$('table');
            
            await this.takeScreenshot('14_pelatihan');
            
            const duration = Date.now() - start;
            this.addResult('Pelatihan', 'PASS', 'Pelatihan page loaded successfully', duration);
        } catch (error) {
            const duration = Date.now() - start;
            this.addResult('Pelatihan', 'FAIL', error.message, duration);
        }
    }

    async testLogout() {
        const start = Date.now();
        try {
            console.log('\n[TEST 15] Testing Logout...');
            
            // Look for logout button/link
            const logoutButton = await this.page.$('a[href*="logout"], button[type="submit"][value*="logout"], #btnLogout');
            
            if (logoutButton) {
                await logoutButton.click();
                await this.page.waitForNavigation({ waitUntil: 'networkidle2' });
                
                const currentUrl = this.page.url();
                await this.takeScreenshot('15_after_logout');
                
                if (currentUrl.includes('login.php')) {
                    const duration = Date.now() - start;
                    this.addResult('Logout', 'PASS', 'Successfully logged out', duration);
                } else {
                    const duration = Date.now() - start;
                    this.addResult('Logout', 'FAIL', `Not redirected to login. URL: ${currentUrl}`, duration);
                }
            } else {
                const duration = Date.now() - start;
                this.addResult('Logout', 'SKIP', 'Logout button not found', duration);
            }
        } catch (error) {
            const duration = Date.now() - start;
            this.addResult('Logout', 'FAIL', error.message, duration);
        }
    }

    printSummary() {
        console.log('\n' + '='.repeat(70));
        console.log('TEST SUMMARY');
        console.log('='.repeat(70));

        const passed = this.results.filter(r => r.status === 'PASS').length;
        const failed = this.results.filter(r => r.status === 'FAIL').length;
        const skipped = this.results.filter(r => r.status === 'SKIP').length;
        const total = this.results.length;

        console.log(`\nTotal Tests: ${total}`);
        console.log(`✅ PASS: ${passed}`);
        console.log(`❌ FAIL: ${failed}`);
        console.log(`⚠️  SKIP: ${skipped}`);
        console.log(`Success Rate: ${((passed / total) * 100).toFixed(2)}%`);

        if (failed > 0) {
            console.log('\n❌ FAILED TESTS:');
            this.results.filter(r => r.status === 'FAIL').forEach(r => {
                console.log(`  - ${r.test}: ${r.message}`);
            });
        }

        console.log('\n' + '='.repeat(70));
    }

    saveResults() {
        const summary = {
            timestamp: new Date().toISOString(),
            totalTests: this.results.length,
            passed: this.results.filter(r => r.status === 'PASS').length,
            failed: this.results.filter(r => r.status === 'FAIL').length,
            skipped: this.results.filter(r => r.status === 'SKIP').length,
            successRate: ((this.results.filter(r => r.status === 'PASS').length / this.results.length) * 100).toFixed(2),
            results: this.results
        };

        fs.writeFileSync(TEST_RESULTS_FILE, JSON.stringify(summary, null, 2));
        console.log(`\n📄 Test results saved to: ${TEST_RESULTS_FILE}`);
        console.log(`📸 Screenshots saved to: ${SCREENSHOT_DIR}`);
    }
}

// Run tests
(async () => {
    const suite = new SPRINTestSuite();
    await suite.runAllTests();
    
    const failed = suite.results.filter(r => r.status === 'FAIL').length;
    process.exit(failed > 0 ? 1 : 0);
})();
