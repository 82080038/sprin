/**
 * Test Direct HTML Output for BKO
 * SPRIN Application Testing
 */

const puppeteer = require('puppeteer');
const config = require('../config');

class BKOHTMLTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.screenshotDir = config.output.screenshots;
    }

    async init() {
        console.log('🔍 Starting BKO HTML Output Test...');
        this.browser = await puppeteer.launch(config.browser);
        this.page = await this.browser.newPage();
        await this.page.setViewport(config.browser.defaultViewport);
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

    async getBagianPageHTML() {
        console.log('🧭 Getting Bagian page HTML...');
        
        try {
            await this.page.goto(config.baseUrl + '/pages/bagian.php', { 
                waitUntil: 'networkidle2',
                timeout: config.timeouts.navigation 
            });
            
            // Get the full HTML content
            const htmlContent = await this.page.content();
            
            // Find UNSUR LAINNYA section
            const lainnyaMatch = htmlContent.match(/UNSUR LAINNYA[\s\S]*?(?=UNSUR[\s\S]*?<\/div>\s*<\/div>\s*<\/div>\s*<\/div>|$)/);
            
            if (lainnyaMatch) {
                console.log('✅ Found UNSUR LAINNYA section in HTML');
                console.log('HTML Section (first 1000 chars):');
                console.log(lainnyaMatch[0].substring(0, 1000));
                
                // Check if BKO is in the HTML
                const hasBKO = lainnyaMatch[0].includes('BKO');
                console.log(`\nBKO found in HTML: ${hasBKO ? 'YES' : 'NO'}`);
                
                if (hasBKO) {
                    // Extract BKO HTML
                    const bkoMatch = lainnyaMatch[0].match(/BKO[\s\S]*?<\/div>\s*<\/div>\s*<\/div>/);
                    if (bkoMatch) {
                        console.log('BKO HTML section:');
                        console.log(bkoMatch[0]);
                    }
                    
                    this.testResults.push({ 
                        test: 'BKO HTML Output', 
                        status: 'PASS', 
                        data: { found: true, html: bkoMatch ? bkoMatch[0] : 'Found but no specific match' }
                    });
                } else {
                    this.testResults.push({ 
                        test: 'BKO HTML Output', 
                        status: 'FAIL', 
                        data: { found: false, html: lainnyaMatch[0].substring(0, 500) }
                    });
                }
                
                return lainnyaMatch[0];
            } else {
                console.log('❌ UNSUR LAINNYA section not found in HTML');
                this.testResults.push({ 
                    test: 'BKO HTML Output', 
                    status: 'FAIL', 
                    error: 'UNSUR LAINNYA section not found' 
                });
                return null;
            }
        } catch (error) {
            console.error('❌ HTML extraction error:', error.message);
            this.testResults.push({ 
                test: 'BKO HTML Output', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async checkBadgeCount() {
        console.log('🔍 Checking badge count for UNSUR LAINNYA...');
        
        try {
            const badgeInfo = await this.page.evaluate(() => {
                const results = [];
                
                // Find all unsur cards
                const unsurCards = document.querySelectorAll('.unsur-card');
                unsurCards.forEach(card => {
                    const unsurName = card.querySelector('h6')?.textContent.trim();
                    const badge = card.querySelector('.badge');
                    const badgeCount = badge ? parseInt(badge.textContent.trim()) : 0;
                    
                    if (unsurName === 'UNSUR LAINNYA') {
                        results.push({
                            unsurName: unsurName,
                            badgeCount: badgeCount,
                            badgeText: badge ? badge.textContent.trim() : 'No badge'
                        });
                    }
                });
                
                return results;
            });
            
            console.log('Badge Info for UNSUR LAINNYA:');
            badgeInfo.forEach(info => {
                console.log(`  Badge Count: ${info.badgeCount}`);
                console.log(`  Badge Text: '${info.badgeText}'`);
            });
            
            if (badgeInfo.length > 0 && badgeInfo[0].badgeCount > 0) {
                console.log('✅ Badge shows bagian count > 0');
                this.testResults.push({ 
                    test: 'Badge Count Check', 
                    status: 'PASS', 
                    data: badgeInfo[0] 
                });
            } else {
                console.log('❌ Badge shows 0 bagian');
                this.testResults.push({ 
                    test: 'Badge Count Check', 
                    status: 'FAIL', 
                    data: badgeInfo[0] || { error: 'No badge info found' }
                });
            }
            
            return badgeInfo;
        } catch (error) {
            console.error('❌ Badge check error:', error.message);
            this.testResults.push({ 
                test: 'Badge Count Check', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async generateReport() {
        console.log('📋 Generating BKO HTML test report...');
        
        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'BKO HTML Output Test',
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };
        
        const reportPath = `${config.output.reports}/bko_html_test_${timestamp.replace(/[:.]/g, '-')}.json`;
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
            await this.getBagianPageHTML();
            await this.checkBadgeCount();
            
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
    const test = new BKOHTMLTest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ BKO HTML test completed!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ BKO HTML test failed:', error.message);
            process.exit(1);
        });
}

module.exports = BKOHTMLTest;
