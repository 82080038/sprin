/**
 * Test Runner and Utilities
 * SPRIN Puppeteer Testing Suite
 */

const puppeteer = require('puppeteer');
const config = require('./config');
const fs = require('fs');
const path = require('path');

class TestRunner {
    constructor() {
        this.browser = null;
        this.page = null;
        this.results = [];
        this.screenshotCounter = 0;
        
        // Ensure output directories exist
        if (!fs.existsSync(config.output.screenshots)) {
            fs.mkdirSync(config.output.screenshots, { recursive: true });
        }
    }
    
    async initialize() {
        console.log('🚀 Initializing browser...');
        this.browser = await puppeteer.launch(config.browser);
        this.page = await this.browser.newPage();
        
        // Set default timeout
        this.page.setDefaultTimeout(config.timeouts.element);
        this.page.setDefaultNavigationTimeout(config.timeouts.navigation);
        
        // Enable console logging
        this.page.on('console', msg => {
            if (msg.type() === 'error') {
                console.log('📋 Browser Console Error:', msg.text());
            }
        });
        
        // Enable error logging
        this.page.on('pageerror', error => {
            console.log('📋 Page Error:', error.message);
        });
        
        console.log('✅ Browser initialized');
    }
    
    async close() {
        if (this.browser) {
            await this.browser.close();
            console.log('🔒 Browser closed');
        }
    }
    
    async screenshot(name) {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filename = `${this.screenshotCounter++}_${name}_${timestamp}.png`;
        const filepath = path.join(config.output.screenshots, filename);
        
        await this.page.screenshot({ 
            path: filepath,
            fullPage: true 
        });
        
        console.log(`📸 Screenshot saved: ${filename}`);
        return filepath;
    }
    
    async test(name, testFn) {
        console.log(`\n🧪 Testing: ${name}`);
        const startTime = Date.now();
        
        try {
            await testFn(this.page);
            const duration = Date.now() - startTime;
            
            this.results.push({
                name,
                status: 'PASSED',
                duration,
                timestamp: new Date().toISOString()
            });
            
            console.log(`✅ PASSED (${duration}ms)`);
            return true;
        } catch (error) {
            const duration = Date.now() - startTime;
            
            // Take screenshot on failure
            await this.screenshot(`FAILED_${name}`).catch(() => {});
            
            this.results.push({
                name,
                status: 'FAILED',
                duration,
                error: error.message,
                timestamp: new Date().toISOString()
            });
            
            console.log(`❌ FAILED: ${error.message}`);
            return false;
        }
    }
    
    async waitForSelector(selector, timeout = config.timeouts.element) {
        try {
            await this.page.waitForSelector(selector, { timeout });
            return true;
        } catch (error) {
            throw new Error(`Selector not found: ${selector}`);
        }
    }
    
    async click(selector) {
        await this.waitForSelector(selector);
        await this.page.click(selector);
    }
    
    async type(selector, text) {
        await this.waitForSelector(selector);
        await this.page.click(selector, { clickCount: 3 }); // Select all
        await this.page.type(selector, text);
    }
    
    async getText(selector) {
        await this.waitForSelector(selector);
        return await this.page.evaluate(sel => {
            const el = document.querySelector(sel);
            return el ? el.textContent.trim() : null;
        }, selector);
    }
    
    async isVisible(selector) {
        try {
            await this.page.waitForSelector(selector, { 
                visible: true, 
                timeout: 2000 
            });
            return true;
        } catch {
            return false;
        }
    }
    
    async waitForTimeout(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    async log(message) {
        console.log(`   ${message}`);
    }
    
    async login() {
        console.log('🔐 Logging in...');
        await this.page.goto(`${config.baseUrl}/login.php`);
        await this.type(config.selectors.login.usernameInput, config.credentials.username);
        await this.type(config.selectors.login.passwordInput, config.credentials.password);
        await this.click(config.selectors.login.submitButton);
        
        // Wait for redirect to main page
        try {
            await this.page.waitForNavigation({ 
                waitUntil: 'networkidle0',
                timeout: config.timeouts.navigation 
            });
        } catch (e) {
            // Check if we're already on main page
            const url = this.page.url();
            if (!url.includes('main.php')) {
                throw e;
            }
        }
        
        // Verify we're logged in
        const url = this.page.url();
        if (!url.includes('main.php')) {
            throw new Error('Login failed - not redirected to main page');
        }
        
        console.log('✅ Logged in successfully');
        await this.screenshot('login_success');
    }
    
    async logout() {
        console.log('🚪 Logging out...');
        // Try to find logout link
        const logoutSelectors = [
            'a[href*="logout.php"]',
            '.logout',
            '.logout-btn',
            'button:has-text("Logout")',
            'a:has-text("Logout")'
        ];
        
        for (const selector of logoutSelectors) {
            if (await this.isVisible(selector)) {
                await this.click(selector);
                // Wait for navigation with timeout
                try {
                    await this.page.waitForNavigation({ 
                        waitUntil: 'networkidle0',
                        timeout: 10000 
                    });
                } catch (e) {
                    // Check if we're on login page already
                    const url = this.page.url();
                    if (url.includes('login.php')) {
                        console.log('✅ Logged out successfully');
                        return;
                    }
                    throw e;
                }
                console.log('✅ Logged out successfully');
                return;
            }
        }
        
        console.log('⚠️ Logout button not found, navigating to login page directly');
        await this.page.goto(`${config.baseUrl}/login.php`);
    }
    
    generateReport() {
        const total = this.results.length;
        const passed = this.results.filter(r => r.status === 'PASSED').length;
        const failed = this.results.filter(r => r.status === 'FAILED').length;
        const totalDuration = this.results.reduce((sum, r) => sum + r.duration, 0);
        
        const report = {
            summary: {
                total,
                passed,
                failed,
                passRate: ((passed / total) * 100).toFixed(2) + '%',
                totalDuration: totalDuration + 'ms',
                timestamp: new Date().toISOString()
            },
            details: this.results
        };
        
        const reportPath = path.join(config.output.reports, 'test-report.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        // Generate HTML report
        this.generateHtmlReport(report);
        
        return report;
    }
    
    generateHtmlReport(report) {
        const html = `
<!DOCTYPE html>
<html>
<head>
    <title>SPRIN Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-box { padding: 15px; border-radius: 8px; text-align: center; }
        .stat-box.passed { background: #d4edda; color: #155724; }
        .stat-box.failed { background: #f8d7da; color: #721c24; }
        .stat-box.total { background: #e2e3e5; color: #383d41; }
        .stat-value { font-size: 2em; font-weight: bold; }
        .stat-label { font-size: 0.9em; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #1a237e; color: white; }
        tr:hover { background: #f5f5f5; }
        .status-passed { color: #28a745; font-weight: bold; }
        .status-failed { color: #dc3545; font-weight: bold; }
        .error-message { color: #dc3545; font-size: 0.9em; }
        .timestamp { color: #666; font-size: 0.85em; }
    </style>
</head>
<body>
    <div class="container">
        <h1>SPRIN Test Report</h1>
        <p class="timestamp">Generated: ${report.summary.timestamp}</p>
        
        <div class="summary">
            <div class="stat-box total">
                <div class="stat-value">${report.summary.total}</div>
                <div class="stat-label">Total Tests</div>
            </div>
            <div class="stat-box passed">
                <div class="stat-value">${report.summary.passed}</div>
                <div class="stat-label">Passed</div>
            </div>
            <div class="stat-box failed">
                <div class="stat-value">${report.summary.failed}</div>
                <div class="stat-label">Failed</div>
            </div>
            <div class="stat-box total">
                <div class="stat-value">${report.summary.passRate}</div>
                <div class="stat-label">Pass Rate</div>
            </div>
        </div>
        
        <h2>Test Details</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Test Name</th>
                    <th>Status</th>
                    <th>Duration</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                ${report.details.map((r, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${r.name}</td>
                        <td class="status-${r.status.toLowerCase()}">${r.status}</td>
                        <td>${r.duration}ms</td>
                        <td>${r.error ? `<span class="error-message">${r.error}</span>` : '-'}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    </div>
</body>
</html>`;
        
        const htmlPath = path.join(config.output.reports, 'test-report.html');
        fs.writeFileSync(htmlPath, html);
        
        console.log(`\n📊 Report saved:`);
        console.log(`   JSON: ${path.join(config.output.reports, 'test-report.json')}`);
        console.log(`   HTML: ${htmlPath}`);
    }
}

module.exports = TestRunner;
