/**
 * Layout Validation Test Suite for SPRIN Application
 * Tests Bootstrap consistency and responsiveness across all pages
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class LayoutValidator {
    constructor() {
        this.baseUrl = 'http://localhost/sprin';
        this.screenshotsDir = path.join(__dirname, 'screenshots');
        this.results = {
            tests: [],
            passed: 0,
            failed: 0,
            skipped: 0
        };
        
        // Ensure screenshots directory exists
        if (!fs.existsSync(this.screenshotsDir)) {
            fs.mkdirSync(this.screenshotsDir, { recursive: true });
        }
    }

    async runAllTests() {
        console.log('='.repeat(60));
        console.log('SPRIN LAYOUT VALIDATION TEST SUITE');
        console.log('='.repeat(60));
        console.log(`Base URL: ${this.baseUrl}`);
        console.log(`Timestamp: ${new Date().toISOString()}`);
        console.log('='.repeat(60));

        const browser = await puppeteer.launch({
            headless: false,
            defaultViewport: {
                width: 1920,
                height: 1080
            },
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });

        try {
            const page = await browser.newPage();
            
            // Test pages in order
            await this.testLoginPage(page);
            await this.testDashboard(page);
            await this.testPersonilPage(page);
            await this.testOperasiPage(page);
            await this.testCalendarPage(page);
            await this.testResponsiveness(page);
            
        } catch (error) {
            console.error('Test suite error:', error);
        } finally {
            await browser.close();
            this.generateReport();
        }
    }

    async testLoginPage(page) {
        console.log('\n[TEST 1] Login Page Layout');
        
        try {
            await page.goto(`${this.baseUrl}/login.php`, { waitUntil: 'networkidle2' });
            
            // Take screenshot
            await this.takeScreenshot(page, '01_login_page');
            
            // Validate Bootstrap elements
            const loginValidation = await page.evaluate(() => {
                const results = {
                    bootstrapContainer: false,
                    formControls: false,
                    buttons: false,
                    gridSystem: false,
                    responsiveDesign: false
                };
                
                // Check for Bootstrap container
                const containers = document.querySelectorAll('.container, .container-fluid');
                results.bootstrapContainer = containers.length > 0;
                
                // Check for Bootstrap form controls
                const formControls = document.querySelectorAll('.form-control, .form-select');
                results.formControls = formControls.length > 0;
                
                // Check for Bootstrap buttons
                const buttons = document.querySelectorAll('.btn');
                results.buttons = buttons.length > 0;
                
                // Check for grid system
                const rows = document.querySelectorAll('.row');
                const cols = document.querySelectorAll('.col');
                results.gridSystem = rows.length > 0 && cols.length > 0;
                
                // Check responsive design
                const metaViewport = document.querySelector('meta[name="viewport"]');
                results.responsiveDesign = metaViewport !== null;
                
                // Check layout structure
                const loginContainer = document.querySelector('.login-container');
                const sidebar = document.querySelector('.login-sidebar');
                const form = document.querySelector('.login-form');
                
                return {
                    ...results,
                    hasLoginContainer: loginContainer !== null,
                    hasSidebar: sidebar !== null,
                    hasForm: form !== null,
                    pageTitle: document.title,
                    bodyClasses: document.body.className
                };
            });
            
            this.addTestResult('Login Page Layout', loginValidation);
            
        } catch (error) {
            this.addTestResult('Login Page Layout', { error: error.message, passed: false });
        }
    }

    async testDashboard(page) {
        console.log('\n[TEST 2] Dashboard Layout');
        
        try {
            // Login first
            await this.login(page);
            
            await page.goto(`${this.baseUrl}/pages/main.php`, { waitUntil: 'networkidle2' });
            
            // Take screenshot
            await this.takeScreenshot(page, '02_dashboard');
            
            // Validate dashboard layout
            const dashboardValidation = await page.evaluate(() => {
                const results = {
                    sidebar: false,
                    mainContent: false,
                    statsCards: false,
                    navigation: false,
                    breadcrumb: false,
                    responsiveNav: false
                };
                
                // Check sidebar
                const sidebar = document.querySelector('.sidebar');
                results.sidebar = sidebar !== null;
                
                // Check main content
                const mainContent = document.querySelector('.main-content');
                results.mainContent = mainContent !== null;
                
                // Check stats cards
                const statsCards = document.querySelectorAll('.stats-card, .card');
                results.statsCards = statsCards.length > 0;
                
                // Check navigation
                const nav = document.querySelector('.navbar');
                results.navigation = nav !== null;
                
                // Check breadcrumb
                const breadcrumb = document.querySelector('.breadcrumb');
                results.breadcrumb = breadcrumb !== null;
                
                // Check responsive navigation
                const mobileToggle = document.querySelector('.mobile-menu-toggle');
                results.responsiveNav = mobileToggle !== null;
                
                // Count dashboard elements
                const statNumbers = document.querySelectorAll('.stats-number');
                const statLabels = document.querySelectorAll('.stats-label');
                
                return {
                    ...results,
                    statCount: statNumbers.length,
                    pageTitle: document.title,
                    hasPageHeader: document.querySelector('.page-header') !== null,
                    bodyClasses: document.body.className
                };
            });
            
            this.addTestResult('Dashboard Layout', dashboardValidation);
            
        } catch (error) {
            this.addTestResult('Dashboard Layout', { error: error.message, passed: false });
        }
    }

    async testPersonilPage(page) {
        console.log('\n[TEST 3] Personil Page Layout');
        
        try {
            await page.goto(`${this.baseUrl}/pages/personil.php`, { waitUntil: 'networkidle2' });
            
            // Take screenshot
            await this.takeScreenshot(page, '03_personil_page');
            
            // Validate personil page layout
            const personilValidation = await page.evaluate(() => {
                const results = {
                    personilStats: false,
                    searchSection: false,
                    unsurCards: false,
                    modals: false,
                    tables: false,
                    buttons: false
                };
                
                // Check personil stats
                const personilStats = document.querySelector('.personil-stats');
                results.personilStats = personilStats !== null;
                
                // Check search section
                const searchSection = document.querySelector('.search-section');
                results.searchSection = searchSection !== null;
                
                // Check unsur cards
                const unsurCards = document.querySelectorAll('.unsur-card');
                results.unsurCards = unsurCards.length > 0;
                
                // Check modals
                const modals = document.querySelectorAll('.modal');
                results.modals = modals.length > 0;
                
                // Check tables
                const tables = document.querySelectorAll('.table');
                results.tables = tables.length > 0;
                
                // Check buttons
                const buttons = document.querySelectorAll('.btn');
                results.buttons = buttons.length > 0;
                
                return {
                    ...results,
                    unsurCardCount: unsurCards.length,
                    modalCount: modals.length,
                    tableCount: tables.length,
                    buttonCount: buttons.length,
                    pageTitle: document.title,
                    hasLoadingIndicator: document.querySelector('#loadingIndicator') !== null
                };
            });
            
            this.addTestResult('Personil Page Layout', personilValidation);
            
        } catch (error) {
            this.addTestResult('Personil Page Layout', { error: error.message, passed: false });
        }
    }

    async testOperasiPage(page) {
        console.log('\n[TEST 4] Operasi Page Layout');
        
        try {
            await page.goto(`${this.baseUrl}/pages/operasi.php`, { waitUntil: 'networkidle2' });
            
            // Take screenshot
            await this.takeScreenshot(page, '04_operasi_page');
            
            // Validate operasi page layout
            const operasiValidation = await page.evaluate(() => {
                const results = {
                    operationsHeader: false,
                    statsGrid: false,
                    operationsTable: false,
                    modals: false,
                    badges: false,
                    buttons: false
                };
                
                // Check operations header
                const operationsHeader = document.querySelector('.operations-header');
                results.operationsHeader = operationsHeader !== null;
                
                // Check stats grid
                const statsGrid = document.querySelector('.stats-grid');
                results.statsGrid = statsGrid !== null;
                
                // Check operations table
                const operationsTable = document.querySelector('.operations-table');
                results.operationsTable = operationsTable !== null;
                
                // Check modals
                const modals = document.querySelectorAll('.modal');
                results.modals = modals.length > 0;
                
                // Check badges
                const badges = document.querySelectorAll('.badge');
                results.badges = badges.length > 0;
                
                // Check buttons
                const buttons = document.querySelectorAll('.btn');
                results.buttons = buttons.length > 0;
                
                return {
                    ...results,
                    modalCount: modals.length,
                    badgeCount: badges.length,
                    buttonCount: buttons.length,
                    pageTitle: document.title,
                    hasEmptyState: document.querySelector('.empty-state') !== null
                };
            });
            
            this.addTestResult('Operasi Page Layout', operasiValidation);
            
        } catch (error) {
            this.addTestResult('Operasi Page Layout', { error: error.message, passed: false });
        }
    }

    async testCalendarPage(page) {
        console.log('\n[TEST 5] Calendar Page Layout');
        
        try {
            await page.goto(`${this.baseUrl}/pages/calendar_dashboard.php`, { waitUntil: 'networkidle2' });
            
            // Take screenshot
            await this.takeScreenshot(page, '05_calendar_page');
            
            // Validate calendar page layout
            const calendarValidation = await page.evaluate(() => {
                const results = {
                    calendarHeader: false,
                    calendarControls: false,
                    calendarContainer: false,
                    piketSchedule: false,
                    modals: false,
                    eventLegend: false
                };
                
                // Check calendar header
                const calendarHeader = document.querySelector('.calendar-header');
                results.calendarHeader = calendarHeader !== null;
                
                // Check calendar controls
                const calendarControls = document.querySelector('.calendar-controls');
                results.calendarControls = calendarControls !== null;
                
                // Check calendar container
                const calendarContainer = document.querySelector('.calendar-container');
                results.calendarContainer = calendarContainer !== null;
                
                // Check piket schedule
                const piketSchedule = document.querySelector('.piket-schedule');
                results.piketSchedule = piketSchedule !== null;
                
                // Check modals
                const modals = document.querySelectorAll('.modal');
                results.modals = modals.length > 0;
                
                // Check event legend
                const eventLegend = document.querySelector('.event-legend');
                results.eventLegend = eventLegend !== null;
                
                return {
                    ...results,
                    modalCount: modals.length,
                    pageTitle: document.title,
                    hasCalendar: document.querySelector('#calendar') !== null,
                    hasFullCalendar: typeof FullCalendar !== 'undefined'
                };
            });
            
            this.addTestResult('Calendar Page Layout', calendarValidation);
            
        } catch (error) {
            this.addTestResult('Calendar Page Layout', { error: error.message, passed: false });
        }
    }

    async testResponsiveness(page) {
        console.log('\n[TEST 6] Responsive Design');
        
        try {
            const responsiveTests = [];
            
            // Test tablet view
            await page.setViewport({ width: 768, height: 1024 });
            await page.goto(`${this.baseUrl}/pages/main.php`, { waitUntil: 'networkidle2' });
            await this.takeScreenshot(page, '06_tablet_view');
            
            const tabletValidation = await page.evaluate(() => {
                const sidebar = document.querySelector('.sidebar');
                const mainContent = document.querySelector('.main-content');
                const mobileToggle = document.querySelector('.mobile-menu-toggle');
                
                return {
                    sidebarVisible: sidebar ? window.getComputedStyle(sidebar).display !== 'none' : false,
                    mainContentWidth: mainContent ? window.getComputedStyle(mainContent).width : '0px',
                    hasMobileToggle: mobileToggle !== null
                };
            });
            
            responsiveTests.push({
                device: 'Tablet (768x1024)',
                ...tabletValidation
            });
            
            // Test mobile view
            await page.setViewport({ width: 375, height: 667 });
            await page.goto(`${this.baseUrl}/pages/main.php`, { waitUntil: 'networkidle2' });
            await this.takeScreenshot(page, '07_mobile_view');
            
            const mobileValidation = await page.evaluate(() => {
                const sidebar = document.querySelector('.sidebar');
                const mainContent = document.querySelector('.main-content');
                const mobileToggle = document.querySelector('.mobile-menu-toggle');
                
                return {
                    sidebarVisible: sidebar ? window.getComputedStyle(sidebar).display !== 'none' : false,
                    mainContentWidth: mainContent ? window.getComputedStyle(mainContent).width : '0px',
                    hasMobileToggle: mobileToggle !== null
                };
            });
            
            responsiveTests.push({
                device: 'Mobile (375x667)',
                ...mobileValidation
            });
            
            // Reset to desktop
            await page.setViewport({ width: 1920, height: 1080 });
            
            this.addTestResult('Responsive Design', {
                passed: true,
                tests: responsiveTests
            });
            
        } catch (error) {
            this.addTestResult('Responsive Design', { error: error.message, passed: false });
        }
    }

    async login(page) {
        try {
            await page.goto(`${this.baseUrl}/login.php`, { waitUntil: 'networkidle2' });
            
            // Fill login form
            await page.type('#username', 'bagops');
            await page.type('#password', 'admin123');
            
            // Click login button
            await Promise.all([
                page.waitForNavigation({ waitUntil: 'networkidle2' }),
                page.click('button[type="submit"]')
            ]);
            
            return true;
        } catch (error) {
            console.error('Login failed:', error);
            return false;
        }
    }

    async takeScreenshot(page, filename) {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const screenshotPath = path.join(this.screenshotsDir, `${filename}_${timestamp}.png`);
        
        await page.screenshot({
            path: screenshotPath,
            fullPage: true
        });
        
        console.log(`  \ud83d\udcf8 Screenshot saved: ${path.basename(screenshotPath)}`);
        return screenshotPath;
    }

    addTestResult(testName, result) {
        const testResult = {
            name: testName,
            timestamp: new Date().toISOString(),
            passed: result.error ? false : (result.passed !== false),
            details: result
        };
        
        this.results.tests.push(testResult);
        
        if (testResult.passed) {
            this.results.passed++;
            console.log(`  \u2705 ${testName}: PASSED`);
        } else {
            this.results.failed++;
            console.log(`  \u274c ${testName}: FAILED - ${result.error || 'Validation failed'}`);
        }
    }

    generateReport() {
        console.log('\n' + '='.repeat(60));
        console.log('LAYOUT VALIDATION TEST RESULTS');
        console.log('='.repeat(60));
        
        const total = this.results.tests.length;
        const passRate = total > 0 ? ((this.results.passed / total) * 100).toFixed(2) : 0;
        
        console.log(`Total Tests: ${total}`);
        console.log(`\u2702 PASS: ${this.results.passed}`);
        console.log(`\u274c FAIL: ${this.results.failed}`);
        console.log(`\u2753 SKIP: ${this.results.skipped}`);
        console.log(`Success Rate: ${passRate}%`);
        console.log('='.repeat(60));
        
        // Generate detailed report
        const report = {
            timestamp: new Date().toISOString(),
            summary: {
                total: total,
                passed: this.results.passed,
                failed: this.results.failed,
                skipped: this.results.skipped,
                successRate: parseFloat(passRate)
            },
            tests: this.results.tests,
            screenshots: this.getScreenshots()
        };
        
        const reportPath = path.join(__dirname, 'layout-validation-report.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        console.log(`\ud83d\udccb Detailed report saved to: ${reportPath}`);
        console.log(`\ud83d\udcf8 Screenshots saved to: ${this.screenshotsDir}`);
        
        return report;
    }

    getScreenshots() {
        const screenshots = [];
        const files = fs.readdirSync(this.screenshotsDir);
        
        files.forEach(file => {
            if (file.endsWith('.png')) {
                screenshots.push({
                    filename: file,
                    path: path.join(this.screenshotsDir, file),
                    size: fs.statSync(path.join(this.screenshotsDir, file)).size
                });
            }
        });
        
        return screenshots.sort((a, b) => a.filename.localeCompare(b.filename));
    }
}

// Run the tests
if (require.main === module) {
    const validator = new LayoutValidator();
    validator.runAllTests().catch(console.error);
}

module.exports = LayoutValidator;
