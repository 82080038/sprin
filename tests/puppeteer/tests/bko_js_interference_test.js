/**
 * Test JavaScript Interference with BKO
 * SPRIN Application Testing
 */

const puppeteer = require('puppeteer');
const config = require('../config');

class BKOJSInterferenceTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.screenshotDir = config.output.screenshots;
    }

    async init() {
        console.log('🔍 Starting BKO JavaScript Interference Test...');
        this.browser = await puppeteer.launch(config.browser);
        this.page = await this.browser.newPage();
        await this.page.setViewport(config.browser.defaultViewport);
        
        // Enable request interception
        await this.page.setRequestInterception(true);
        
        // Log all console messages
        this.page.on('console', msg => {
            console.log(`Console ${msg.type()}: ${msg.text()}`);
        });
        
        // Log all page errors
        this.page.on('pageerror', error => {
            console.log(`Page Error: ${error.message}`);
        });
    }

    async login() {
        console.log('🔐 Logging in...');
        try {
            await this.page.goto(config.baseUrl + '/login.php', { 
                waitUntil: 'networkidle2',
                timeout: config.timeouts.navigation 
            });
            
            const quickLoginButton = await this.page.$(config.selectors.login.quickLoginButton);
            if (quickLoginButton) {
                await quickLoginButton.click();
                await this.page.waitForNavigation({ waitUntil: 'networkidle2' });
            }
            
            const currentUrl = this.page.url();
            if (currentUrl.includes('main.php') || currentUrl.includes('pages/')) {
                console.log('✅ Login successful');
                return true;
            }
            return false;
        } catch (error) {
            console.error('❌ Login error:', error.message);
            return false;
        }
    }

    async testInitialHTML() {
        console.log('🔍 Testing initial HTML before JavaScript...');
        
        try {
            // Navigate to bagian page
            await this.page.goto(config.baseUrl + '/pages/bagian.php', { 
                waitUntil: 'domcontentloaded',
                timeout: config.timeouts.navigation 
            });
            
            // Check HTML immediately after load (before JS execution)
            const initialCheck = await this.page.evaluate(() => {
                const results = {
                    unsurCards: [],
                    bkoFound: false
                };
                
                // Find UNSUR LAINNYA card
                const unsurCards = document.querySelectorAll('.unsur-card');
                unsurCards.forEach(card => {
                    const unsurName = card.querySelector('h6')?.textContent.trim();
                    const badge = card.querySelector('.badge');
                    const bagianList = card.querySelector('.bagian-list');
                    const bagians = bagianList ? bagianList.querySelectorAll('.bagian-item') : [];
                    
                    if (unsurName === 'UNSUR LAINNYA') {
                        results.unsurCards.push({
                            unsurName: unsurName,
                            badgeCount: badge ? parseInt(badge.textContent.trim()) : 0,
                            bagianCount: bagians.length,
                            bagianNames: Array.from(bagians).map(b => b.querySelector('.bagian-name')?.textContent.trim())
                        });
                        
                        // Check for BKO specifically
                        bagians.forEach(bagian => {
                            const bagianName = bagian.querySelector('.bagian-name')?.textContent.trim();
                            if (bagianName === 'BKO') {
                                results.bkoFound = true;
                            }
                        });
                    }
                });
                
                return results;
            });
            
            console.log('Initial HTML Check:');
            console.log(`  Badge Count: ${initialCheck.unsurCards[0]?.badgeCount || 'N/A'}`);
            console.log(`  Bagian Count: ${initialCheck.unsurCards[0]?.bagianCount || 'N/A'}`);
            console.log(`  BKO Found: ${initialCheck.bkoFound}`);
            console.log(`  Bagian Names: ${initialCheck.unsurCards[0]?.bagianNames.join(', ') || 'None'}`);
            
            if (initialCheck.bkoFound) {
                console.log('✅ BKO found in initial HTML');
                this.testResults.push({ 
                    test: 'Initial HTML Check', 
                    status: 'PASS', 
                    data: initialCheck 
                });
            } else {
                console.log('❌ BKO NOT found in initial HTML');
                this.testResults.push({ 
                    test: 'Initial HTML Check', 
                    status: 'FAIL', 
                    data: initialCheck 
                });
            }
            
            return initialCheck;
        } catch (error) {
            console.error('❌ Initial HTML check error:', error.message);
            this.testResults.push({ 
                test: 'Initial HTML Check', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async testAfterJavaScript() {
        console.log('🔍 Testing after JavaScript execution...');
        
        try {
            // Wait for JavaScript to execute
            await new Promise(resolve => setTimeout(resolve, 3000));
            
            // Check HTML after JS execution
            const afterJSCheck = await this.page.evaluate(() => {
                const results = {
                    unsurCards: [],
                    bkoFound: false
                };
                
                // Find UNSUR LAINNYA card
                const unsurCards = document.querySelectorAll('.unsur-card');
                unsurCards.forEach(card => {
                    const unsurName = card.querySelector('h6')?.textContent.trim();
                    const badge = card.querySelector('.badge');
                    const bagianList = card.querySelector('.bagian-list');
                    const bagians = bagianList ? bagianList.querySelectorAll('.bagian-item') : [];
                    
                    if (unsurName === 'UNSUR LAINNYA') {
                        results.unsurCards.push({
                            unsurName: unsurName,
                            badgeCount: badge ? parseInt(badge.textContent.trim()) : 0,
                            bagianCount: bagians.length,
                            bagianNames: Array.from(bagians).map(b => b.querySelector('.bagian-name')?.textContent.trim())
                        });
                        
                        // Check for BKO specifically
                        bagians.forEach(bagian => {
                            const bagianName = bagian.querySelector('.bagian-name')?.textContent.trim();
                            if (bagianName === 'BKO') {
                                results.bkoFound = true;
                            }
                        });
                    }
                });
                
                return results;
            });
            
            console.log('After JavaScript Check:');
            console.log(`  Badge Count: ${afterJSCheck.unsurCards[0]?.badgeCount || 'N/A'}`);
            console.log(`  Bagian Count: ${afterJSCheck.unsurCards[0]?.bagianCount || 'N/A'}`);
            console.log(`  BKO Found: ${afterJSCheck.bkoFound}`);
            console.log(`  Bagian Names: ${afterJSCheck.unsurCards[0]?.bagianNames.join(', ') || 'None'}`);
            
            if (afterJSCheck.bkoFound) {
                console.log('✅ BKO found after JavaScript');
                this.testResults.push({ 
                    test: 'After JavaScript Check', 
                    status: 'PASS', 
                    data: afterJSCheck 
                });
            } else {
                console.log('❌ BKO NOT found after JavaScript');
                this.testResults.push({ 
                    test: 'After JavaScript Check', 
                    status: 'FAIL', 
                    data: afterJSCheck 
                });
            }
            
            return afterJSCheck;
        } catch (error) {
            console.error('❌ After JavaScript check error:', error.message);
            this.testResults.push({ 
                test: 'After JavaScript Check', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async testWithJavaScriptDisabled() {
        console.log('🔍 Testing with JavaScript disabled...');
        
        try {
            // Create new page with JavaScript disabled
            const page = await this.browser.newPage();
            await page.setViewport(config.browser.defaultViewport);
            
            // Disable JavaScript
            await page.setJavaScriptEnabled(false);
            
            // Login
            await page.goto(config.baseUrl + '/login.php', { 
                waitUntil: 'networkidle2',
                timeout: config.timeouts.navigation 
            });
            
            const quickLoginButton = await page.$(config.selectors.login.quickLoginButton);
            if (quickLoginButton) {
                await quickLoginButton.click();
                await page.waitForNavigation({ waitUntil: 'networkidle2' });
            }
            
            // Navigate to bagian page
            await page.goto(config.baseUrl + '/pages/bagian.php', { 
                waitUntil: 'networkidle2',
                timeout: config.timeouts.navigation 
            });
            
            // Check HTML without JavaScript
            const noJSCheck = await page.evaluate(() => {
                const results = {
                    unsurCards: [],
                    bkoFound: false
                };
                
                // Find UNSUR LAINNYA card
                const unsurCards = document.querySelectorAll('.unsur-card');
                unsurCards.forEach(card => {
                    const unsurName = card.querySelector('h6')?.textContent.trim();
                    const badge = card.querySelector('.badge');
                    const bagianList = card.querySelector('.bagian-list');
                    const bagians = bagianList ? bagianList.querySelectorAll('.bagian-item') : [];
                    
                    if (unsurName === 'UNSUR LAINNYA') {
                        results.unsurCards.push({
                            unsurName: unsurName,
                            badgeCount: badge ? parseInt(badge.textContent.trim()) : 0,
                            bagianCount: bagians.length,
                            bagianNames: Array.from(bagians).map(b => b.querySelector('.bagian-name')?.textContent.trim())
                        });
                        
                        // Check for BKO specifically
                        bagians.forEach(bagian => {
                            const bagianName = bagian.querySelector('.bagian-name')?.textContent.trim();
                            if (bagianName === 'BKO') {
                                results.bkoFound = true;
                            }
                        });
                    }
                });
                
                return results;
            });
            
            console.log('No JavaScript Check:');
            console.log(`  Badge Count: ${noJSCheck.unsurCards[0]?.badgeCount || 'N/A'}`);
            console.log(`  Bagian Count: ${noJSCheck.unsurCards[0]?.bagianCount || 'N/A'}`);
            console.log(`  BKO Found: ${noJSCheck.bkoFound}`);
            console.log(`  Bagian Names: ${noJSCheck.unsurCards[0]?.bagianNames.join(', ') || 'None'}`);
            
            if (noJSCheck.bkoFound) {
                console.log('✅ BKO found without JavaScript');
                this.testResults.push({ 
                    test: 'No JavaScript Check', 
                    status: 'PASS', 
                    data: noJSCheck 
                });
            } else {
                console.log('❌ BKO NOT found without JavaScript');
                this.testResults.push({ 
                    test: 'No JavaScript Check', 
                    status: 'FAIL', 
                    data: noJSCheck 
                });
            }
            
            await page.close();
            return noJSCheck;
        } catch (error) {
            console.error('❌ No JavaScript check error:', error.message);
            this.testResults.push({ 
                test: 'No JavaScript Check', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async generateReport() {
        console.log('📋 Generating BKO JavaScript interference test report...');
        
        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'BKO JavaScript Interference Test',
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };
        
        const reportPath = `${config.output.reports}/bko_js_interference_test_${timestamp.replace(/[:.]/g, '-')}.json`;
        require('fs').writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        console.log(`📄 Report saved: ${reportPath}`);
        console.log(`\n📊 Test Summary:`);
        console.log(`   Total Tests: ${report.summary.total}`);
        console.log(`   ✅ Passed: ${report.summary.passed}`);
        console.log(`   ❌ Failed: ${report.summary.failed}`);
        console.log(`   ⚠️ Warnings: ${report.summary.warnings}`);
        
        return report;
    }

    async cleanup() {
        console.log('🧹 Cleaning up...');
        if (this.page) {
            await this.page.close();
        }
        if (this.browser) {
            await this.browser.close();
        }
    }

    async runFullTest() {
        try {
            await this.init();
            
            const loginSuccess = await this.login();
            if (!loginSuccess) {
                throw new Error('Login failed');
            }
            
            // Run tests
            await this.testInitialHTML();
            await this.testAfterJavaScript();
            await this.testWithJavaScriptDisabled();
            
            // Generate report
            const report = await this.generateReport();
            
            return report;
            
        } catch (error) {
            console.error('❌ Test execution failed:', error.message);
            throw error;
        } finally {
            await this.cleanup();
        }
    }
}

// Run the test
if (require.main === module) {
    const test = new BKOJSInterferenceTest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ BKO JavaScript interference test completed!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ BKO JavaScript interference test failed:', error.message);
            process.exit(1);
        });
}

module.exports = BKOJSInterferenceTest;
