/**
 * Test Alert Display for Unsur Delete
 * SPRIN Application Testing
 */

const puppeteer = require('puppeteer');
const config = require('../config');

class AlertDisplayTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.screenshotDir = config.output.screenshots;
    }

    async init() {
        console.log('🚨 Starting Alert Display Test...');
        this.browser = await puppeteer.launch(config.browser);
        this.page = await this.browser.newPage();
        await this.page.setViewport(config.browser.defaultViewport);
        
        // Handle alerts
        this.page.on('dialog', async dialog => {
            console.log('🚨 Alert detected:', dialog.message());
            this.testResults.push({ 
                test: 'Alert Detection', 
                status: 'PASS', 
                data: { message: dialog.message(), type: dialog.type() }
            });
            await dialog.accept();
        });
    }

    async takeScreenshot(name, description = '') {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filename = `${name}_${timestamp}.png`;
        const filepath = `${this.screenshotDir}/${filename}`;
        
        await this.page.screenshot({ 
            path: filepath, 
            fullPage: false 
        });
        
        console.log(`📸 Screenshot saved: ${filename} - ${description}`);
        return filepath;
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

    async navigateToUnsurPage() {
        console.log('🧭 Navigating to Unsur page...');
        try {
            await this.page.goto(config.baseUrl + '/pages/unsur.php', { 
                waitUntil: 'networkidle2',
                timeout: config.timeouts.navigation 
            });
            
            const currentUrl = this.page.url();
            if (currentUrl.includes('unsur.php')) {
                console.log('✅ Successfully navigated to Unsur page');
                await this.takeScreenshot('alert_initial_load', 'Initial unsur page for alert test');
                return true;
            }
            return false;
        } catch (error) {
            console.error('❌ Navigation error:', error.message);
            return false;
        }
    }

    async findAndClickDeleteButton() {
        console.log('🔍 Finding delete button for UNSUR LAINNYA...');
        
        try {
            // Find UNSUR LAINNYA delete button
            const deleteButtonFound = await this.page.evaluate(() => {
                const buttons = document.querySelectorAll('button[onclick*="deleteUnsur"]');
                let lainnyaButton = null;
                
                buttons.forEach(button => {
                    const onclick = button.getAttribute('onclick');
                    if (onclick.includes('UNSUR LAINNYA')) {
                        lainnyaButton = {
                            onclick: onclick,
                            visible: button.offsetParent !== null,
                            text: button.textContent.trim()
                        };
                    }
                });
                
                return lainnyaButton;
            });
            
            if (deleteButtonFound && deleteButtonFound.visible) {
                console.log('✅ Found UNSUR LAINNYA delete button');
                console.log('Button onclick:', deleteButtonFound.onclick);
                
                // Click the delete button
                await this.page.evaluate(() => {
                    const buttons = document.querySelectorAll('button[onclick*="deleteUnsur"]');
                    buttons.forEach(button => {
                        const onclick = button.getAttribute('onclick');
                        if (onclick.includes('UNSUR LAINNYA')) {
                            button.click();
                        }
                    });
                });
                
                await this.takeScreenshot('alert_after_delete_click', 'After clicking delete button');
                
                this.testResults.push({ 
                    test: 'Delete Button Click', 
                    status: 'PASS', 
                    data: deleteButtonFound 
                });
                
                return true;
            } else {
                console.log('❌ UNSUR LAINNYA delete button not found or not visible');
                this.testResults.push({ 
                    test: 'Delete Button Click', 
                    status: 'FAIL', 
                    error: 'Delete button not found or not visible' 
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Delete button click error:', error.message);
            this.testResults.push({ 
                test: 'Delete Button Click', 
                status: 'FAIL', 
                error: error.message 
            });
            return false;
        }
    }

    async waitForAlertAndCheckContent() {
        console.log('⏳ Waiting for alert and checking content...');
        
        try {
            // Wait for alert to appear (max 5 seconds)
            await this.page.waitForTimeout(5000);
            
            // Check if any alerts were captured
            const alerts = this.testResults.filter(r => r.test === 'Alert Detection');
            
            if (alerts.length > 0) {
                const alertMessage = alerts[0].data.message;
                console.log('✅ Alert captured:', alertMessage);
                
                // Check if alert contains detailed information
                const hasDetails = alertMessage.includes('Bagian terkait:') || 
                                 alertMessage.includes('BKO') || 
                                 alertMessage.includes('Pindahkan');
                
                if (hasDetails) {
                    console.log('✅ Alert contains detailed information');
                    this.testResults.push({ 
                        test: 'Alert Content Check', 
                        status: 'PASS', 
                        data: { 
                            message: alertMessage, 
                            hasDetails: true,
                            containsBagianList: alertMessage.includes('Bagian terkait:'),
                            containsBKO: alertMessage.includes('BKO'),
                            containsSuggestion: alertMessage.includes('Pindahkan')
                        }
                    });
                } else {
                    console.log('❌ Alert does not contain detailed information');
                    this.testResults.push({ 
                        test: 'Alert Content Check', 
                        status: 'FAIL', 
                        data: { 
                            message: alertMessage, 
                            hasDetails: false,
                            expectedDetails: ['Bagian terkait:', 'BKO', 'Pindahkan']
                        }
                    });
                }
                
                return true;
            } else {
                console.log('❌ No alert captured');
                this.testResults.push({ 
                    test: 'Alert Content Check', 
                    status: 'FAIL', 
                    error: 'No alert captured' 
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Alert wait error:', error.message);
            this.testResults.push({ 
                test: 'Alert Content Check', 
                status: 'FAIL', 
                error: error.message 
            });
            return false;
        }
    }

    async testDirectJavaScript() {
        console.log('🔧 Testing direct JavaScript call...');
        
        try {
            const jsTest = await this.page.evaluate(() => {
                return new Promise((resolve) => {
                    // Override alert to capture the message
                    const originalAlert = window.alert;
                    let alertMessage = '';
                    
                    window.alert = function(message) {
                        alertMessage = message;
                        console.log('Alert called with:', message);
                        resolve({ success: true, message: message });
                    };
                    
                    // Call deleteUnsur function directly
                    try {
                        deleteUnsur(6, 'UNSUR LAINNYA');
                        
                        // If no alert within 3 seconds, resolve with timeout
                        setTimeout(() => {
                            window.alert = originalAlert;
                            resolve({ success: false, message: 'No alert triggered within timeout' });
                        }, 3000);
                    } catch (error) {
                        window.alert = originalAlert;
                        resolve({ success: false, error: error.message });
                    }
                });
            });
            
            console.log('Direct JS test result:', JSON.stringify(jsTest, null, 2));
            
            if (jsTest.success) {
                console.log('✅ Direct JavaScript call successful');
                this.testResults.push({ 
                    test: 'Direct JavaScript Test', 
                    status: 'PASS', 
                    data: jsTest 
                });
                
                // Check if message contains details
                const hasDetails = jsTest.message.includes('Bagian terkait:') || 
                                 jsTest.message.includes('BKO') || 
                                 jsTest.message.includes('Pindahkan');
                
                if (hasDetails) {
                    console.log('✅ Direct JS alert contains details');
                } else {
                    console.log('❌ Direct JS alert missing details');
                }
                
                return true;
            } else {
                console.log('❌ Direct JavaScript call failed');
                this.testResults.push({ 
                    test: 'Direct JavaScript Test', 
                    status: 'FAIL', 
                    data: jsTest 
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Direct JavaScript test error:', error.message);
            this.testResults.push({ 
                test: 'Direct JavaScript Test', 
                status: 'FAIL', 
                error: error.message 
            });
            return false;
        }
    }

    async generateReport() {
        console.log('📋 Generating alert display test report...');
        
        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'Alert Display Test for Unsur Delete',
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };
        
        const reportPath = `${config.output.reports}/alert_display_test_${timestamp.replace(/[:.]/g, '-')}.json`;
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
            
            const navigationSuccess = await this.navigateToUnsurPage();
            if (!navigationSuccess) {
                throw new Error('Navigation to unsur page failed');
            }
            
            // Run tests
            await this.findAndClickDeleteButton();
            await this.waitForAlertAndCheckContent();
            await this.testDirectJavaScript();
            
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
    const test = new AlertDisplayTest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ Alert display test completed!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ Alert display test failed:', error.message);
            process.exit(1);
        });
}

module.exports = AlertDisplayTest;
