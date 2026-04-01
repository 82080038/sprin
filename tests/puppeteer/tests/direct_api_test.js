/**
 * Direct API Test for move_bagian
 * SPRIN Application Testing
 */

const puppeteer = require('puppeteer');
const config = require('../config');

class DirectAPITest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
    }

    async init() {
        console.log('🔌 Starting Direct API Test...');
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

    async navigateToBagianPage() {
        console.log('🧭 Navigating to Bagian page...');
        try {
            await this.page.goto(config.baseUrl + '/pages/bagian.php', { 
                waitUntil: 'networkidle2',
                timeout: config.timeouts.navigation 
            });
            
            const currentUrl = this.page.url();
            if (currentUrl.includes('bagian.php')) {
                console.log('✅ Successfully navigated to Bagian page');
                return true;
            }
            return false;
        } catch (error) {
            console.error('❌ Navigation error:', error.message);
            return false;
        }
    }

    async testMoveBagianAPI() {
        console.log('🔌 Testing move_bagian API directly...');
        
        try {
            const apiResult = await this.page.evaluate(async () => {
                try {
                    const response = await fetch('./bagian.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'move_bagian',
                            bagian_id: '5',
                            new_unsur_id: '2',
                            new_urutan: '2'
                        })
                    });
                    
                    const responseText = await response.text();
                    
                    // Try to parse as JSON
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (e) {
                        data = { rawResponse: responseText, parseError: e.message };
                    }
                    
                    return {
                        success: response.ok,
                        status: response.status,
                        statusText: response.statusText,
                        data: data
                    };
                } catch (error) {
                    return {
                        success: false,
                        error: error.message
                    };
                }
            });
            
            console.log('API Test Result:', JSON.stringify(apiResult, null, 2));
            
            if (apiResult.success && apiResult.data.success) {
                console.log('✅ move_bagian API working correctly');
                this.testResults.push({ 
                    test: 'Direct API Test', 
                    status: 'PASS', 
                    data: apiResult 
                });
                return true;
            } else {
                console.log('❌ API test failed');
                this.testResults.push({ 
                    test: 'Direct API Test', 
                    status: 'FAIL', 
                    data: apiResult 
                });
                return false;
            }
        } catch (error) {
            console.error('❌ API test error:', error.message);
            this.testResults.push({ 
                test: 'Direct API Test', 
                status: 'FAIL', 
                error: error.message 
            });
            return false;
        }
    }

    async testRealChangeScenario() {
        console.log('🔄 Testing real change scenario...');
        
        try {
            const scenarioResult = await this.page.evaluate(async () => {
                // Step 1: Get current state
                const getResponse = await fetch('./bagian.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'get_bagian_list'
                    })
                });
                
                const getResponseText = await getResponse.text();
                
                // Step 2: Make a real change
                const moveResponse = await fetch('./bagian.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'move_bagian',
                        bagian_id: '5', // BAG LOG
                        new_unsur_id: '2', // Same unsur
                        new_urutan: '1' // Change urutan to 1
                    })
                });
                
                const moveResponseText = await moveResponse.text();
                
                // Step 3: Check result
                let moveData;
                try {
                    moveData = JSON.parse(moveResponseText);
                } catch (e) {
                    moveData = { rawResponse: moveResponseText, parseError: e.message };
                }
                
                return {
                    getResponseStatus: getResponse.ok,
                    getResponseText: getResponseText.substring(0, 200) + '...',
                    moveResponseStatus: moveResponse.ok,
                    moveResponseText: moveResponseText,
                    moveData: moveData
                };
            });
            
            console.log('Scenario Result:', JSON.stringify(scenarioResult, null, 2));
            
            if (scenarioResult.moveResponseStatus && scenarioResult.moveData.success) {
                console.log('✅ Real change scenario successful');
                this.testResults.push({ 
                    test: 'Real Change Scenario', 
                    status: 'PASS', 
                    data: scenarioResult 
                });
                return true;
            } else {
                console.log('❌ Real change scenario failed');
                this.testResults.push({ 
                    test: 'Real Change Scenario', 
                    status: 'FAIL', 
                    data: scenarioResult 
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Scenario test error:', error.message);
            this.testResults.push({ 
                test: 'Real Change Scenario', 
                status: 'FAIL', 
                error: error.message 
            });
            return false;
        }
    }

    async verifyDatabaseChange() {
        console.log('🔍 Verifying database change...');
        
        try {
            // Reload page to see if changes persist
            await this.page.reload({ waitUntil: 'networkidle2' });
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            const verification = await this.page.evaluate(() => {
                const bagianItems = document.querySelectorAll('.bagian-item');
                const results = [];
                
                bagianItems.forEach(item => {
                    const id = item.getAttribute('data-id');
                    const name = item.querySelector('.bagian-name')?.textContent.trim();
                    const urutan = item.getAttribute('data-urutan');
                    const unsurId = item.getAttribute('data-unsur-id');
                    
                    if (id === '5') { // BAG LOG
                        results.push({
                            id: id,
                            name: name,
                            urutan: urutan,
                            unsurId: unsurId
                        });
                    }
                });
                
                return results;
            });
            
            console.log('Database verification:', verification);
            
            if (verification.length > 0 && verification[0].urutan === '1') {
                console.log('✅ Database change verified - BAG LOG now has urutan 1');
                this.testResults.push({ 
                    test: 'Database Change Verification', 
                    status: 'PASS', 
                    data: verification 
                });
                return true;
            } else {
                console.log('❌ Database change not verified');
                this.testResults.push({ 
                    test: 'Database Change Verification', 
                    status: 'FAIL', 
                    data: verification 
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Verification error:', error.message);
            this.testResults.push({ 
                test: 'Database Change Verification', 
                status: 'FAIL', 
                error: error.message 
            });
            return false;
        }
    }

    async generateReport() {
        console.log('📋 Generating direct API test report...');
        
        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'Direct API Test',
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };
        
        const reportPath = `${config.output.reports}/direct_api_test_${timestamp.replace(/[:.]/g, '-')}.json`;
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
            
            const navigationSuccess = await this.navigateToBagianPage();
            if (!navigationSuccess) {
                throw new Error('Navigation to bagian page failed');
            }
            
            // Run API tests
            await this.testMoveBagianAPI();
            await this.testRealChangeScenario();
            await this.verifyDatabaseChange();
            
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
    const test = new DirectAPITest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ Direct API test completed!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ Direct API test failed:', error.message);
            process.exit(1);
        });
}

module.exports = DirectAPITest;
