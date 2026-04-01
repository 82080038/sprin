const puppeteer = require('puppeteer');
const fs = require('fs');

class SprinTestSuite {
    constructor() {
        this.baseUrl = 'http://localhost/sprint';
        this.browser = null;
        this.page = null;
        this.testResults = [];
    }

    async setup() {
        console.log('🚀 Starting SPRIN Test Suite...');
        this.browser = await puppeteer.launch({
            headless: true,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--single-process',
                '--disable-gpu'
            ],
            defaultViewport: { width: 1366, height: 768 },
            timeout: 60000
        });
        this.page = await this.browser.newPage();

        // Enable request interception for debugging
        await this.page.setRequestInterception(true);
        this.page.on('request', request => {
            console.log(`🌐 Request: ${request.method()} ${request.url()}`);
            request.continue();
        });

        this.page.on('response', response => {
            console.log(`📡 Response: ${response.status()} ${response.url()}`);
        });
    }

    async takeScreenshot(name) {
        const screenshotPath = `tests/screenshots/${name}_${Date.now()}.png`;
        await this.page.screenshot({ path: screenshotPath, fullPage: true });
        console.log(`📸 Screenshot saved: ${screenshotPath}`);
        return screenshotPath;
    }

    async waitForElement(selector, timeout = 5000) {
        try {
            await this.page.waitForSelector(selector, { timeout });
            return true;
        } catch (error) {
            console.log(`❌ Element not found: ${selector}`);
            return false;
        }
    }

    async login(username = 'bagops', password = 'admin123') {
        console.log('🔐 Testing Login Functionality...');

        try {
            await this.page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
            await this.takeScreenshot('login_page');

            // Check if login page loaded
            const loginForm = await this.waitForElement('form');
            if (!loginForm) {
                throw new Error('Login form not found');
            }

            // Fill login form
            await this.page.type('input[name="username"]', username);
            await this.page.type('input[name="password"]', password);
            await this.takeScreenshot('login_filled');

            // Submit form
            await Promise.all([
                this.page.waitForNavigation({ waitUntil: 'networkidle2' }),
                this.page.click('button[type="submit"]')
            ]);

            await this.takeScreenshot('login_success');

            // Check if successfully logged in
            const currentUrl = this.page.url();
            if (currentUrl.includes('main.php')) {
                this.addTestResult('Login', true, 'Login successful');
                return true;
            } else {
                throw new Error('Login failed - redirect not working');
            }

        } catch (error) {
            this.addTestResult('Login', false, error.message);
            await this.takeScreenshot('login_error');
            return false;
        }
    }

    async testDashboard() {
        console.log('📊 Testing Dashboard Functionality...');

        try {
            // Wait for dashboard to load and content to be rendered
            await this.page.waitForSelector('.dashboard-content, .main-content, .container-fluid', { timeout: 10000 });
            // Wait for dynamic content to load
            await new Promise(resolve => setTimeout(resolve, 2000));
            await this.takeScreenshot('dashboard_loaded');

            // Test navigation menu
            const navItems = await this.page.$$('nav a, .navbar a, .sidebar a');
            console.log(`🧭 Found ${navItems.length} navigation items`);

            // Test statistics cards
            const statCards = await this.page.$$('.card, .stat-card, .info-box');
            console.log(`📈 Found ${statCards.length} statistics cards`);

            // Test personil count display
            const personilCount = await this.page.$eval('.personil-count, .badge-personil', el => el.textContent).catch(() => 'Not found');
            console.log(`👥 Personil count: ${personilCount}`);

            this.addTestResult('Dashboard', true, 'Dashboard loaded successfully');
            return true;

        } catch (error) {
            this.addTestResult('Dashboard', false, error.message);
            await this.takeScreenshot('dashboard_error');
            return false;
        }
    }

    async testPersonilManagement() {
        console.log('👮 Testing Personil Management...');

        try {
            // Navigate to personil page
            await this.page.goto(this.baseUrl + '/pages/personil.php', { waitUntil: 'networkidle2' });
            await this.takeScreenshot('personil_page');

            // Wait for content to be dynamically loaded
            await new Promise(resolve => setTimeout(resolve, 3000));

            // Wait for loading indicator to disappear (content loaded)
            await this.page.waitForFunction(() => {
                const loading = document.querySelector('#loadingIndicator');
                return loading && loading.style.display === 'none';
            }, { timeout: 10000 });

            // Wait for personil table to be dynamically loaded
            await this.page.waitForSelector('.personil-table, .table-responsive table', { timeout: 15000 });
            await this.takeScreenshot('personil_table');

            // Test search functionality
            const searchInput = await this.page.$('input[type="search"], #search, .search-input');
            if (searchInput) {
                await searchInput.type('test');
                await new Promise(resolve => setTimeout(resolve, 1000));
                await this.takeScreenshot('personil_search');
                this.addTestResult('Personil Search', true, 'Search functionality working');
            }

            // Test add personil button
            const addButton = await this.page.$('button[onclick*="add"], .btn-add, #addPersonil');
            if (addButton) {
                await addButton.click();
                await new Promise(resolve => setTimeout(resolve, 1000));
                await this.takeScreenshot('personil_add_modal');
                this.addTestResult('Personil Add', true, 'Add personil modal opened');
            }

            // Test pagination
            const pagination = await this.page.$$('.pagination a, .page-link');
            if (pagination.length > 0) {
                this.addTestResult('Personil Pagination', true, `Found ${pagination.length} pagination links`);
            }

            this.addTestResult('Personil Management', true, 'Personil management page working');
            return true;

        } catch (error) {
            this.addTestResult('Personil Management', false, error.message);
            await this.takeScreenshot('personil_error');
            return false;
        }
    }

    async testCalendarDashboard() {
        console.log('📅 Testing Calendar Dashboard...');

        try {
            // Navigate to calendar page
            await this.page.goto(this.baseUrl + '/pages/calendar_dashboard.php', { waitUntil: 'networkidle2' });
            await this.takeScreenshot('calendar_page');

            // Wait for content to be dynamically loaded
            await new Promise(resolve => setTimeout(resolve, 3000));

            // Wait for FullCalendar to initialize and render
            await this.page.waitForSelector('#calendar', { timeout: 15000 });
            // Wait for calendar to be fully rendered with actual content
            await this.page.waitForFunction(() => {
                const calendar = document.querySelector('#calendar');
                if (!calendar) return false;
                // Check for FullCalendar specific elements
                return calendar.querySelector('.fc-view-harness, .fc-daygrid, .fc-timegrid') ||
                    calendar.innerHTML.includes('fc-');
            }, { timeout: 10000 });
            await this.takeScreenshot('calendar_loaded');

            // Test calendar navigation
            const calendarNav = await this.page.$$('.fc-button, .calendar-nav button');
            console.log(`📅 Found ${calendarNav.length} calendar navigation buttons`);

            // Test view options
            const viewButtons = await this.page.$$('.fc-view-button, .view-toggle button');
            if (viewButtons.length > 0) {
                this.addTestResult('Calendar Views', true, `Found ${viewButtons.length} view options`);
            }

            // Test add event button
            const addEventBtn = await this.page.$('button[onclick*="add"], .btn-add-event, #addEvent');
            if (addEventBtn) {
                await addEventBtn.click();
                await new Promise(resolve => setTimeout(resolve, 1000));
                await this.takeScreenshot('calendar_add_event');
                this.addTestResult('Calendar Add Event', true, 'Add event modal opened');
            }

            this.addTestResult('Calendar Dashboard', true, 'Calendar dashboard working');
            return true;

        } catch (error) {
            this.addTestResult('Calendar Dashboard', false, error.message);
            await this.takeScreenshot('calendar_error');
            return false;
        }
    }

    async testAPIEndpoints() {
        console.log('🔌 Testing API Endpoints...');

        try {
            // Test personil API
            const personilResponse = await this.page.goto(this.baseUrl + '/api/personil_api.php?action=read', { waitUntil: 'networkidle2' });
            const personilStatus = personilResponse.status();
            this.addTestResult('Personil API', personilStatus === 200, `Status: ${personilStatus}`);

            // Test calendar API
            const calendarResponse = await this.page.goto(this.baseUrl + '/api/calendar_api.php?action=read', { waitUntil: 'networkidle2' });
            const calendarStatus = calendarResponse.status();
            this.addTestResult('Calendar API', calendarStatus === 200, `Status: ${calendarStatus}`);

            // Test stats API
            const statsResponse = await this.page.goto(this.baseUrl + '/api/unsur_stats.php', { waitUntil: 'networkidle2' });
            const statsStatus = statsResponse.status();
            this.addTestResult('Stats API', statsStatus === 200, `Status: ${statsStatus}`);

            // Test search API
            const searchResponse = await this.page.goto(this.baseUrl + '/api/search_personil.php?q=test', { waitUntil: 'networkidle2' });
            const searchStatus = searchResponse.status();
            this.addTestResult('Search API', searchStatus === 200, `Status: ${searchStatus}`);

            return true;

        } catch (error) {
            this.addTestResult('API Endpoints', false, error.message);
            return false;
        }
    }

    async testResponsiveDesign() {
        console.log('📱 Testing Responsive Design...');

        try {
            // Test mobile view
            await this.page.setViewport({ width: 375, height: 667 });
            await this.page.reload({ waitUntil: 'networkidle2' });
            await this.takeScreenshot('mobile_view');

            // Test tablet view
            await this.page.setViewport({ width: 768, height: 1024 });
            await this.page.reload({ waitUntil: 'networkidle2' });
            await this.takeScreenshot('tablet_view');

            // Test desktop view
            await this.page.setViewport({ width: 1366, height: 768 });
            await this.page.reload({ waitUntil: 'networkidle2' });
            await this.takeScreenshot('desktop_view');

            this.addTestResult('Responsive Design', true, 'Responsive design tested across devices');
            return true;

        } catch (error) {
            this.addTestResult('Responsive Design', false, error.message);
            return false;
        }
    }

    addTestResult(testName, passed, details) {
        const result = {
            test: testName,
            passed: passed,
            details: details,
            timestamp: new Date().toISOString()
        };
        this.testResults.push(result);

        const status = passed ? '✅' : '❌';
        console.log(`${status} ${testName}: ${details}`);
    }

    async generateReport() {
        console.log('📋 Generating Test Report...');

        const totalTests = this.testResults.length;
        const passedTests = this.testResults.filter(r => r.passed).length;
        const failedTests = totalTests - passedTests;
        const passRate = ((passedTests / totalTests) * 100).toFixed(2);

        const report = {
            summary: {
                total: totalTests,
                passed: passedTests,
                failed: failedTests,
                passRate: `${passRate}%`,
                timestamp: new Date().toISOString()
            },
            tests: this.testResults,
            environment: {
                baseUrl: this.baseUrl,
                browser: 'Puppeteer',
                viewport: '1366x768'
            }
        };

        // Save JSON report
        const reportPath = `tests/test-report-${Date.now()}.json`;
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));

        // Generate HTML report
        const htmlReport = this.generateHTMLReport(report);
        const htmlPath = `tests/test-report-${Date.now()}.html`;
        fs.writeFileSync(htmlPath, htmlReport);

        console.log(`📊 Test Summary:`);
        console.log(`   Total Tests: ${totalTests}`);
        console.log(`   Passed: ${passedTests}`);
        console.log(`   Failed: ${failedTests}`);
        console.log(`   Pass Rate: ${passRate}%`);
        console.log(`📁 Reports saved: ${reportPath}, ${htmlPath}`);

        return report;
    }

    generateHTMLReport(report) {
        return `
<!DOCTYPE html>
<html>
<head>
    <title>SPRIN Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #f4f4f4; padding: 20px; border-radius: 5px; }
        .summary { display: flex; gap: 20px; margin: 20px 0; }
        .stat { background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 5px; text-align: center; }
        .passed { color: green; }
        .failed { color: red; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f4f4f4; }
        .status { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SPRIN Application Test Report</h1>
        <p>Generated: ${report.summary.timestamp}</p>
    </div>
    
    <div class="summary">
        <div class="stat">
            <h3>Total Tests</h3>
            <h2>${report.summary.total}</h2>
        </div>
        <div class="stat">
            <h3 class="passed">Passed</h3>
            <h2 class="passed">${report.summary.passed}</h2>
        </div>
        <div class="stat">
            <h3 class="failed">Failed</h3>
            <h2 class="failed">${report.summary.failed}</h2>
        </div>
        <div class="stat">
            <h3>Pass Rate</h3>
            <h2>${report.summary.passRate}</h2>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Test Name</th>
                <th>Status</th>
                <th>Details</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            ${report.tests.map(test => `
                <tr>
                    <td>${test.test}</td>
                    <td class="status ${test.passed ? 'passed' : 'failed'}">
                        ${test.passed ? '✅ PASSED' : '❌ FAILED'}
                    </td>
                    <td>${test.details}</td>
                    <td>${test.timestamp}</td>
                </tr>
            `).join('')}
        </tbody>
    </table>
</body>
</html>`;
    }

    async cleanup() {
        console.log('🧹 Cleaning up...');
        if (this.browser) {
            await this.browser.close();
        }
    }
}

// Main test execution
async function runTests() {
    const testSuite = new SprinTestSuite();

    try {
        // Create screenshots directory
        if (!fs.existsSync('tests/screenshots')) {
            fs.mkdirSync('tests/screenshots', { recursive: true });
        }

        await testSuite.setup();

        // Run all tests
        const loginSuccess = await testSuite.login();
        if (loginSuccess) {
            await testSuite.testDashboard();
            await testSuite.testPersonilManagement();
            await testSuite.testCalendarDashboard();
            await testSuite.testResponsiveDesign();
        }

        await testSuite.testAPIEndpoints();

        // Generate report
        await testSuite.generateReport();

    } catch (error) {
        console.error('❌ Test execution failed:', error);
    } finally {
        await testSuite.cleanup();
    }
}

// Run tests if this file is executed directly
if (require.main === module) {
    runTests();
}

module.exports = SprinTestSuite;
