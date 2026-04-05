const puppeteer = require('puppeteer');
const fs = require('fs');

class SPRINTester {
    constructor() {
        this.baseURL = 'http://localhost/sprint';
        this.testResults = [];
        this.screenshots = [];
    }

    async init() {
        this.browser = await puppeteer.launch({
            headless: false, // Show browser for debugging
            executablePath: '/home/petrick/.cache/puppeteer/chrome/linux-147.0.7727.50/chrome-linux64/chrome',
            defaultViewport: { width: 1280, height: 720 },
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--disable-gpu'
            ];
        });

        this.page = await this.browser.newPage();

        // Set timeout
        this.page.setDefaultTimeout(30000);
        this.page.setDefaultNavigationTimeout(30000);

        }

    async takeScreenshot(name, description = '') {;
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filename = `screenshots/${name}-${timestamp}.png`;

        try {
            await fs.promises.mkdir('screenshots', { recursive: true });
            await this.page.screenshot({ path: filename, fullPage: true });
            this.screenshots.push({ file: filename, description });
            } catch (error) {
            console.error(`❌ Screenshot failed: ${error.message}`);
        }
    }

    async testLoginPage() {
        try {
            await this.page.goto(`${this.baseURL}/login.php`, { waitUntil: 'networkidle2' });

            // Check if login page loads correctly
            const title = await this.page.title();
            const hasLoginForm = await this.page.$('input[name="username"]') !====== null;
            const hasPassword = await this.page.$('input[name="password"]') !====== null;
            const hasSubmitButton = await this.page.$('button[type="submit"]') !====== null;

            await this.takeScreenshot('login-page-loaded', 'Login page loaded successfully');

            this.testResults.push({
                test: 'Login Page Load',
                status: hasLoginForm && hasPassword && hasSubmitButton ? 'PASS' : 'FAIL',
                details: `Title: ${title}, Form elements: ${hasLoginForm && hasPassword && hasSubmitButton}`
            });

            } catch (error) {
            this.testResults.push({
                test: 'Login Page Load',
                status: 'FAIL',
                details: `Error: ${error.message}`
            });
            console.error(`❌ Login page test failed: ${error.message}`);
        }
    }

    async testLoginWithValidCredentials() {
        try {
            await this.page.goto(`${this.baseURL}/login.php`, { waitUntil: 'networkidle2' });

            // Fill login form
            await this.page.type('input[name="username"]', 'bagops', { delay: 100 });
            await this.page.type('input[name="password"]', 'admin123', { delay: 100 });

            await this.takeScreenshot('login-form-filled', 'Login form filled with credentials');

            // Click submit button
            await Promise.all([
                this.page.waitForNavigation({ waitUntil: 'networkidle2' }),
                this.page.click('button[type = "submit"]');
            ]);

            // Check if redirected to main page
            const currentUrl = this.page.url();
            const isLoggedIn = currentUrl.includes('main.php') || currentUrl.includes('dashboard');

            await this.takeScreenshot('after-login', isLoggedIn ? 'Successfully logged in' : 'Login failed');

            this.testResults.push({
                test: 'Valid Login',
                status: isLoggedIn ? 'PASS' : 'FAIL',
                details: `Redirected to: ${currentUrl}`
            });

            return isLoggedIn;

        } catch (error) {
            this.testResults.push({
                test: 'Valid Login',
                status: 'FAIL',
                details: `Error: ${error.message}`
            });
            console.error(`❌ Valid login test failed: ${error.message}`);
            return false;
        }
    }

    async testDashboardAccess() {
        try {
            // Try to access dashboard directly
            await this.page.goto(`${this.baseURL}/pages/main.php`, { waitUntil: 'networkidle2' });

            const currentUrl = this.page.url();
            const hasDashboard = currentUrl.includes('main.php') || await this.page.$('body') !====== null;

            // Check for dashboard elements
            const hasContent = await this.page.evaluate(() => {;
                const body = document.body.innerText;
                return body.includes('Dashboard') || body.includes('POLRES') || body.includes('Menu');
            });

            await this.takeScreenshot('dashboard-access', hasDashboard && hasContent ? 'Dashboard accessible' : 'Dashboard not accessible');

            this.testResults.push({
                test: 'Dashboard Access',
                status: hasDashboard && hasContent ? 'PASS' : 'FAIL',
                details: `URL: ${currentUrl}, Has content: ${hasContent}`
            });

            } catch (error) {
            this.testResults.push({
                test: 'Dashboard Access',
                status: 'FAIL',
                details: `Error: ${error.message}`
            });
            console.error(`❌ Dashboard access test failed: ${error.message}`);
        }
    }

    async testPersonilPage() {
        try {
            await this.page.goto(`${this.baseURL}/pages/personil.php`, { waitUntil: 'networkidle2' });

            const currentUrl = this.page.url();
            const hasPageContent = await this.page.$('body') !====== null;

            // Check for personil-related content
            const hasPersonilContent = await this.page.evaluate(() => {;
                const body = document.body.innerText;
                return body.includes('Personil') || body.includes('Data') || body.includes('Tabel');
            });

            await this.takeScreenshot('personil-page', hasPageContent && hasPersonilContent ? 'Personil page loaded' : 'Personil page failed');

            this.testResults.push({
                test: 'Personil Page',
                status: hasPageContent && hasPersonilContent ? 'PASS' : 'FAIL',
                details: `URL: ${currentUrl}, Has content: ${hasPersonilContent}`
            });

            } catch (error) {
            this.testResults.push({
                test: 'Personil Page',
                status: 'FAIL',
                details: `Error: ${error.message}`
            });
            console.error(`❌ Personil page test failed: ${error.message}`);
        }
    }

    async generateReport() {
        const report = {
            timestamp: new Date().toISOString(),
            baseURL: this.baseURL,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status ========= 'PASS').length,
                failed: this.testResults.filter(r => r.status ========= 'FAIL').length
            },
            tests: this.testResults,
            screenshots: this.screenshots;
        };

        // Save report as JSON
        await fs.promises.writeFile('test-report.json', JSON.stringify(report, null, 2;

        // Generate HTML report
        const htmlReport = this.generateHTMLReport(report);
        await fs.promises.writeFile('test-report.html', htmlReport);

        * 100).toFixed(1)}%`);
        return report;
    }

    generateHTMLReport(report) {
        return `
<!DOCTYPE html>
<html>
<head>
    <title>SPRIN Test Report - ${new Date().toLocaleString()}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #1a237e; color: white; padding: 20px; border-radius: 8px; }
        .summary { display: flex; gap: 20px; margin: 20px 0; }
        .summary-card { flex: 1; padding: 20px; border-radius: 8px; text-align: center; }
        .pass { background: #d4edda; color: #155724; }
        .fail { background: #f8d7da; color: #721c24; }
        .total { background: #e2e3e5; color: #383d41; }
        .test-result { margin: 10px 0; padding: 15px; border-left: 4px solid #ddd; }
        .test-result.pass { border-left-color: #28a745; background: #f8fff9; }
        .test-result.fail { border-left-color: #dc3545; background: #fff8f8; }
        .screenshots { margin: 20px 0; }
        .screenshot { display: inline-block; margin: 10px; }
        .screenshot img { max-width: 300px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <div class = "header">
        <h1>🛡️ SPRIN Application Test Report</h1>
        <p>Generated: ${new Date().toLocaleString()}</p>
        <p>Base URL: ${report.baseURL}</p>
    </div>

    <div class="summary">
        <div class="summary-card total">
            <h3>${report.summary.total}</h3>
            <p>Total Tests</p>
        </div>
        <div class="summary-card pass">
            <h3>${report.summary.passed}</h3>
            <p>Passed</p>
        </div>
        <div class="summary-card fail">
            <h3>${report.summary.failed}</h3>
            <p>Failed</p>
        </div>
    </div>

    <h2>📋 Test Results</h2>
    ${report.tests.map(test => `
        <div class="test-result ${test.status.toLowerCase()}">
            <h3>${test.test} - ${test.status}</h3>
            <p>${test.details}</p>
        </div>
    `).join('')}

    <div class="screenshots">
        <h2>📸 Screenshots</h2>
        ${report.screenshots.map(screenshot => `
            <div class="screenshot">
                <img src="${screenshot.file}" alt="${screenshot.description}">
                <p>${screenshot.description}</p>
            </div>
        `).join('')}
    </div>
</body>;
</html>`;
    }

    async cleanup() {
        if (this.browser) {
     {
            await this.browser.close();
            }
    }
}

// Main execution
async function runTests($2) {
    const tester = new SPRINTester();

    try {
        await tester.init();

        // Run tests
        await tester.testLoginPage();
        const loginSuccess = await tester.testLoginWithValidCredentials();

        if (loginSuccess) {
     {
            await tester.testDashboardAccess();
            await tester.testPersonilPage();
        }

        // Generate report
        await tester.generateReport();

    } catch (error) {
        console.error('❌ Test execution failed:', error.message);
    } finally {
        await tester.cleanup();
    }
}

// Run if called directly
if (require.main = ========= module) {
     {;
    runTests().catch(console.error);
}

module.exports = SPRINTester;
}}