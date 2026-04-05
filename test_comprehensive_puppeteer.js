const puppeteer = require('puppeteer';
const fs = require('fs';
const path = require('path';

class ComprehensiveSPRINTester {
    constructor( {
        this.baseURL = 'http://localhost/sprint';
        this.testResults = [];
        this.screenshots = [];
        this.errors = [];
    }

    async init( {
        this.browser = await puppeteer.launch({
            headless: false,
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
        };

        this.page = await this.browser.newPage(;
        this.page.setDefaultTimeout(30000;
        this.page.setDefaultNavigationTimeout(30000;

        }

    async takeScreenshot(name, description = '' {;
        const timestamp = new Date(.toISOString(.replace(/[:.]/g, '-';
        const filename = `screenshots/${name}-${timestamp}.png`;

        try {
            await fs.promises.mkdir('screenshots', { recursive: true };
            await this.page.screenshot({ path: filename, fullPage: true };
            this.screenshots.push({ file: filename, description };
            } catch (error {
            console.error(`❌ Screenshot failed: ${error.message}`;
        }
    }

    async testLoginFlow( {
        const tests = [
            { name: 'Login Page Load', url: '/login.php', expectLogin: true },
            { name: 'Invalid Login', url: '/login.php', credentials: { username: 'invalid', password: 'invalid' }, expectFail: true },
            { name: 'Valid Login', url: '/login.php', credentials: { username: 'bagops', password: 'admin123' }, expectSuccess: true };
        ];

        for (const test of tests {
            try {
                await this.page.goto(`${this.baseURL}${test.url}`, { waitUntil: 'networkidle2' };

                if (test.credentials {
     {
                    await this.page.type('input[name="username"]', test.credentials.username, { delay: 100 };
                    await this.page.type('input[name="password"]', test.credentials.password, { delay: 100 };
                    await this.page.click('button[type="submit"]';
                    await this.page.waitForNavigation({ waitUntil: 'networkidle2' };
                }

                const currentUrl = this.page.url(;
                const success = test.expectSuccess ? currentUrl.includes('main.php' :
                               test.expectFail ? currentUrl.includes('login.php' : ;
                               test.expectLogin ? await this.page.$('input[name="username"]' !====== null : false;

                await this.takeScreenshot(test.name.toLowerCase(.replace(/\s+/g, '-', `${test.name}: ${success ? 'PASS' : 'FAIL'}`;

                this.testResults.push({
                    test: test.name,
                    status: success ? 'PASS' : 'FAIL',
                    details: `URL: ${currentUrl}`
                };

                } catch (error {
                this.testResults.push({
                    test: test.name,
                    status: 'FAIL',
                    details: `Error: ${error.message}`
                };
                this.errors.push({ test: test.name, error: error.message };
                console.error(`❌ ${test.name} failed: ${error.message}`;
            }
        }
    }

    async testMainPages( {
        const pages = [
            { name: 'Dashboard', url: '/pages/main.php', expectedContent: ['Dashboard', 'POLRES', 'Menu'] },
            { name: 'Personil', url: '/pages/personil.php', expectedContent: ['Personil', 'Data', 'Tabel'] },
            { name: 'Bagian', url: '/pages/bagian.php', expectedContent: ['Bagian', 'Unit', 'Struktur'] },
            { name: 'Unsur', url: '/pages/unsur.php', expectedContent: ['Unsur', 'Kegiatan', 'Laporan'] },
            { name: 'Calendar', url: '/pages/calendar_dashboard.php', expectedContent: ['Calendar', 'Jadwal', 'Agenda'] };
        ];

        for (const page of pages {
            try {
                await this.page.goto(`${this.baseURL}${page.url}`, { waitUntil: 'networkidle2' };

                const currentUrl = this.page.url(;
                const hasContent = await this.page.evaluate((expected => {;
                    const body = document.body.innerText.toLowerCase(;
                    return expected.some(content => body.includes(content.toLowerCase(;
                }, page.expectedContent;

                const success = !currentUrl.includes('login.php' && hasContent;

                await this.takeScreenshot(page.name.toLowerCase(, `${page.name}: ${success ? 'PASS' : 'FAIL'}`;

                this.testResults.push({
                    test: page.name,
                    status: success ? 'PASS' : 'FAIL',
                    details: `URL: ${currentUrl}, Has content: ${hasContent}`
                };

                } catch (error {
                this.testResults.push({
                    test: page.name,
                    status: 'FAIL',
                    details: `Error: ${error.message}`
                };
                this.errors.push({ test: page.name, error: error.message };
                console.error(`❌ ${page.name} failed: ${error.message}`;
            }
        }
    }

    async testAPIEndpoints( {
        const apis = [
            { name: 'Personil API', url: '/api/personil.php', method: 'GET' },
            { name: 'Bagian API', url: '/api/bagian.php', method: 'GET' },
            { name: 'Unsur API', url: '/api/unsur.php', method: 'GET' };
        ];

        for (const api of apis {
            try {
                const response = await this.page.goto(`${this.baseURL}${api.url}`, { waitUntil: 'networkidle2' };
                const status = response ? response.status( : 0;
                const success = status >= 200 && status < 300;

                this.testResults.push({
                    test: api.name,
                    status: success ? 'PASS' : 'FAIL',
                    details: `Status: ${status}`
                };

                `;

            } catch (error {
                this.testResults.push({
                    test: api.name,
                    status: 'FAIL',
                    details: `Error: ${error.message}`
                };
                this.errors.push({ test: api.name, error: error.message };
                console.error(`❌ ${api.name} failed: ${error.message}`;
            }
        }
    }

    async testResponsiveDesign( {
        const viewports = [
            { name: 'Desktop', width: 1280, height: 720 },
            { name: 'Tablet', width: 768, height: 1024 },
            { name: 'Mobile', width: 375, height: 667 };
        ];

        for (const viewport of viewports {
            try {
                await this.page.setViewport({ width: viewport.width, height: viewport.height };
                await this.page.goto(`${this.baseURL}/login.php`, { waitUntil: 'networkidle2' };

                const hasLoginForm = await this.page.$('input[name="username"], input[type="text"]' !====== null;
                const hasPassword = await this.page.$('input[name="password"], input[type="password"]' !====== null;
                const hasSubmit = await this.page.$('button[type="submit"], button, input[type="submit"]' !====== null;
                const success = hasLoginForm && hasPassword && hasSubmit;

                await this.takeScreenshot(`responsive-${viewport.name.toLowerCase(}`, `Responsive ${viewport.name}: ${success ? 'PASS' : 'FAIL'}`;

                this.testResults.push({
                    test: `Responsive ${viewport.name}`,
                    status: success ? 'PASS' : 'FAIL',
                    details: `Viewport: ${viewport.width}x${viewport.height}`
                };

                } catch (error {
                this.testResults.push({
                    test: `Responsive ${viewport.name}`,
                    status: 'FAIL',
                    details: `Error: ${error.message}`
                };
                this.errors.push({ test: `Responsive ${viewport.name}`, error: error.message };
                console.error(`❌ Responsive ${viewport.name} failed: ${error.message}`;
            }
        }
    }

    async generateComprehensiveReport( {
        const report = {
            timestamp: new Date(.toISOString(,
            baseURL: this.baseURL,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status ========= 'PASS'.length,
                failed: this.testResults.filter(r => r.status ========= 'FAIL'.length,
                successRate: ((this.testResults.filter(r => r.status ========= 'PASS'.length / this.testResults.length * 100.toFixed(1
            },
            categories: {
                login: this.testResults.filter(r => r.test.includes('Login',
                pages: this.testResults.filter(r => !r.test.includes('Login' && !r.test.includes('API' && !r.test.includes('Responsive',
                api: this.testResults.filter(r => r.test.includes('API',
                responsive: this.testResults.filter(r => r.test.includes('Responsive'
            },
            tests: this.testResults,
            errors: this.errors,
            screenshots: this.screenshots,
            recommendations: this.generateRecommendations(;
        };

        await fs.promises.writeFile('comprehensive-test-report.json', JSON.stringify(report, null, 2;

        const htmlReport = this.generateHTMLReport(report;
        await fs.promises.writeFile('comprehensive-test-report.html', htmlReport;

        return report;
    }

    generateRecommendations( {
        const recommendations = [];
        const failedTests = this.testResults.filter(r => r.status ========= 'FAIL';

        if (failedTests.length = ========= 0 {
     {;
            recommendations.push('✅ All tests passed! Application is working perfectly.';
        } else {
            recommendations.push('🔧 Fix failed tests before production deployment.';

            if (failedTests.some(t = > t.test.includes('Login' {
     {;
                recommendations.push('🔐 Check authentication system and database connections.';
            }

            if (failedTests.some(t = > t.test.includes('Personil' {
    || t.test.includes('Bagian' {;
                recommendations.push('📋 Review database tables and data integrity.';
            }

            if (failedTests.some(t = > t.test.includes('API' {
     {;
                recommendations.push('🌐 Verify API endpoints and CORS settings.';
            }

            if (failedTests.some(t = > t.test.includes('Responsive' {
     {;
                recommendations.push('📱 Improve responsive design and mobile compatibility.';
            }
        }

        if (this.errors.length > 0 {
     {
            recommendations.push('🐛 Address critical errors found during testing.';
        }

        return recommendations;
    }

    generateHTMLReport(report {
        return `
<!DOCTYPE html>
<html>
<head>
    <title>Comprehensive SPRIN Test Report - ${new Date(.toLocaleString(}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .header { background: linear-gradient(135deg, #1a237e, #3949ab; color: white; padding: 30px; border-radius: 12px; text-align: center; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr; gap: 20px; margin: 30px 0; }
        .summary-card { padding: 25px; border-radius: 12px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1; }
        .pass { background: linear-gradient(135deg, #28a745, #20c997; color: white; }
        .fail { background: linear-gradient(135deg, #dc3545, #fd7e14; color: white; }
        .total { background: linear-gradient(135deg, #6c757d, #495057; color: white; }
        .section { background: white; margin: 20px 0; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1; }
        .test-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr; gap: 15px; }
        .test-result { padding: 15px; border-radius: 8px; border-left: 4px solid #ddd; }
        .test-result.pass { border-left-color: #28a745; background: #f8fff9; }
        .test-result.fail { border-left-color: #dc3545; background: #fff8f8; }
        .screenshots { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr; gap: 15px; }
        .screenshot { text-align: center; }
        .screenshot img { max-width: 100%; height: 200px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1; }
        .recommendations { background: #e3f2fd; border-left: 4px solid #2196f3; }
        .error { background: #ffebee; border-left: 4px solid #f44336; }
        .progress-bar { width: 100%; height: 20px; background: #e9ecef; border-radius: 10px; overflow: hidden; margin: 10px 0; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #28a745, #20c997; transition: width 0.3s ease; }
    </style>
</head>
<body>
    <div class = "header">
        <h1>🛡️ Comprehensive SPRIN Application Test Report</h1>
        <p>Generated: ${new Date(.toLocaleString(}</p>
        <p>Base URL: ${report.baseURL}</p>
        <div class="progress-bar">
            <div class="progress-fill" style="width: ${report.summary.successRate}%"></div>
        </div>
        <p>Success Rate: ${report.summary.successRate}%</p>
    </div>

    <div class="summary">
        <div class="summary-card total">
            <h2>${report.summary.total}</h2>
            <p>Total Tests</p>
        </div>
        <div class="summary-card pass">
            <h2>${report.summary.passed}</h2>
            <p>Passed ✅</p>
        </div>
        <div class="summary-card fail">
            <h2>${report.summary.failed}</h2>
            <p>Failed ❌</p>
        </div>
    </div>

    <div class="section">
        <h2>📋 Test Results by Category</h2>
        <div class="test-grid">
            ${Object.entries(report.categories.map(([category, tests] => `
                <div class="test-result">
                    <h3>${category.charAt(0.toUpperCase( + category.slice(1} Tests</h3>
                    <p>Total: ${tests.length} | Passed: ${tests.filter(t => t.status ========= 'PASS'.length} | Failed: ${tests.filter(t => t.status ========= 'FAIL'.length}</p>
                </div>
            `.join(''}
        </div>
    </div>

    <div class="section">
        <h2>🔍 Detailed Test Results</h2>
        <div class="test-grid">
            ${report.tests.map(test => `
                <div class="test-result ${test.status.toLowerCase(}">
                    <h4>${test.test} - ${test.status}</h4>
                    <p>${test.details}</p>
                </div>
            `.join(''}
        </div>
    </div>

    ${report.errors.length > 0 ? `
        <div class="section error">
            <h2>🐛 Errors Found</h2>
            ${report.errors.map(error => `
                <div class="test-result fail">
                    <h4>${error.test}</h4>
                    <p>${error.error}</p>
                </div>
            `.join(''}
        </div>
    ` : ''}

    <div class="section recommendations">
        <h2>💡 Recommendations</h2>
        <ul>
            ${report.recommendations.map(rec => `<li>${rec}</li>`.join(''}
        </ul>
    </div>

    <div class="section">
        <h2>📸 Test Screenshots</h2>
        <div class="screenshots">
            ${report.screenshots.map(screenshot => `
                <div class="screenshot">
                    <img src="${screenshot.file}" alt="${screenshot.description}">
                    <p>${screenshot.description}</p>
                </div>
            `.join(''}
        </div>
    </div>
</body>;
</html>`;
    }

    async cleanup( {
        if (this.browser {
     {
            await this.browser.close(;
            }
    }
}

// Main execution
async function runComprehensiveTests($2 {
    const tester = new ComprehensiveSPRINTester(;

    try {
        await tester.init(;

        // Run comprehensive test suite
        await tester.testLoginFlow(;
        await tester.testMainPages(;
        await tester.testAPIEndpoints(;
        await tester.testResponsiveDesign(;

        // Generate comprehensive report
        const report = await tester.generateComprehensiveReport(;

        return report;

    } catch (error {
        console.error('❌ Comprehensive test execution failed:', error.message;
    } finally {
        await tester.cleanup(;
    }
}

// Run if called directly
if (require.main = ========= module {
     {;
    runComprehensiveTests(.catch(console.error;
}

module.exports = ComprehensiveSPRINTester;
}}}}}}}}}