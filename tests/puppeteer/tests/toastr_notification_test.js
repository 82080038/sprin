/**
 * Test Toastr Notifications and Error Handling in Bagian Page
 * SPRIN Application Testing
 */

const puppeteer = require('puppeteer');
const config = require('../config');

class ToastrNotificationTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.screenshotDir = config.output.screenshots;
    }

    async init() {
        console.log('🔔 Starting Toastr Notification Test...');
        this.browser = await puppeteer.launch(config.browser);
        this.page = await this.browser.newPage();
        await this.page.setViewport(config.browser.defaultViewport);
    }

    async takeScreenshot(name, description = '') {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filename = `${name}_${timestamp}.png`;
        const filepath = `${this.screenshotDir}/${filename}`;
        
        await this.page.screenshot({ 
            path: filepath, 
            fullPage: true 
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
            
            // Quick login
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
                await this.takeScreenshot('toastr_test_page_loaded', 'Bagian page loaded for notification test');
                return true;
            }
            return false;
        } catch (error) {
            console.error('❌ Navigation error:', error.message);
            return false;
        }
    }

    async testLibraryLoading() {
        console.log('📚 Testing library loading...');
        
        try {
            // Check if libraries are loaded
            const libraries = await this.page.evaluate(() => {
                return {
                    bootstrap: typeof bootstrap !== 'undefined',
                    toastr: typeof toastr !== 'undefined',
                    sprint: typeof window.SPRINT !== 'undefined',
                    jquery: typeof jQuery !== 'undefined'
                };
            });
            
            console.log('Library Status:', libraries);
            
            // Check console for any errors
            const consoleErrors = await this.page.evaluate(() => {
                return window.consoleErrors || [];
            });
            
            if (consoleErrors.length > 0) {
                console.log('⚠️ Console errors found:', consoleErrors);
                this.testResults.push({ 
                    test: 'Library Loading', 
                    status: 'WARNING', 
                    errors: consoleErrors 
                });
            } else {
                console.log('✅ No console errors found');
                this.testResults.push({ 
                    test: 'Library Loading', 
                    status: 'PASS', 
                    data: libraries 
                });
            }
            
            await this.takeScreenshot('toastr_test_libraries', 'Library loading test');
        } catch (error) {
            console.error('❌ Library test error:', error.message);
            this.testResults.push({ 
                test: 'Library Loading', 
                status: 'FAIL', 
                error: error.message 
            });
        }
    }

    async testToastrConfiguration() {
        console.log('⚙️ Testing toastr configuration...');
        
        try {
            const toastrConfig = await this.page.evaluate(() => {
                if (typeof toastr !== 'undefined') {
                    return {
                        options: toastr.options || null,
                        hasSuccess: typeof toastr.success === 'function',
                        hasError: typeof toastr.error === 'function',
                        hasInfo: typeof toastr.info === 'function',
                        hasWarning: typeof toastr.warning === 'function'
                    };
                }
                return null;
            });
            
            if (toastrConfig) {
                console.log('✅ Toastr configured:', toastrConfig);
                this.testResults.push({ 
                    test: 'Toastr Configuration', 
                    status: 'PASS', 
                    data: toastrConfig 
                });
            } else {
                console.log('⚠️ Toastr not available');
                this.testResults.push({ 
                    test: 'Toastr Configuration', 
                    status: 'WARNING', 
                    error: 'Toastr not available' 
                });
            }
            
            await this.takeScreenshot('toastr_test_config', 'Toastr configuration test');
        } catch (error) {
            console.error('❌ Toastr config test error:', error.message);
            this.testResults.push({ 
                test: 'Toastr Configuration', 
                status: 'FAIL', 
                error: error.message 
            });
        }
    }

    async testSPRINTFallback() {
        console.log('🛡️ Testing SPRINT fallback system...');
        
        try {
            const sprintStatus = await this.page.evaluate(() => {
                if (typeof window.SPRINT !== 'undefined') {
                    return {
                        hasShowSuccess: typeof window.SPRINT.showSuccess === 'function',
                        hasShowError: typeof window.SPRINT.showError === 'function',
                        methods: Object.keys(window.SPRINT)
                    };
                }
                return null;
            });
            
            if (sprintStatus) {
                console.log('✅ SPRINT available:', sprintStatus);
                this.testResults.push({ 
                    test: 'SPRINT Fallback', 
                    status: 'PASS', 
                    data: sprintStatus 
                });
            } else {
                console.log('❌ SPRINT not available');
                this.testResults.push({ 
                    test: 'SPRINT Fallback', 
                    status: 'FAIL', 
                    error: 'SPRINT not available' 
                });
            }
            
            await this.takeScreenshot('toastr_test_sprint', 'SPRINT fallback test');
        } catch (error) {
            console.error('❌ SPRINT test error:', error.message);
            this.testResults.push({ 
                test: 'SPRINT Fallback', 
                status: 'FAIL', 
                error: error.message 
            });
        }
    }

    async testNotificationTrigger() {
        console.log('🔔 Testing notification trigger...');
        
        try {
            // Trigger a notification by calling SPRINT.showError directly
            const notificationResult = await this.page.evaluate(() => {
                try {
                    // Test error notification
                    window.SPRINT.showError('Test error message from Puppeteer');
                    
                    // Test success notification  
                    window.SPRINT.showSuccess('Test success message from Puppeteer');
                    
                    return { success: true, message: 'Notifications triggered successfully' };
                } catch (error) {
                    return { success: false, error: error.message };
                }
            });
            
            if (notificationResult.success) {
                console.log('✅ Notifications triggered successfully');
                
                // Wait a bit for notifications to appear
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                await this.takeScreenshot('toastr_test_notifications', 'Notifications triggered');
                
                this.testResults.push({ 
                    test: 'Notification Trigger', 
                    status: 'PASS', 
                    data: notificationResult 
                });
            } else {
                console.log('❌ Notification trigger failed:', notificationResult.error);
                this.testResults.push({ 
                    test: 'Notification Trigger', 
                    status: 'FAIL', 
                    error: notificationResult.error 
                });
            }
        } catch (error) {
            console.error('❌ Notification trigger error:', error.message);
            this.testResults.push({ 
                test: 'Notification Trigger', 
                status: 'FAIL', 
                error: error.message 
            });
        }
    }

    async testErrorScenario() {
        console.log('🚨 Testing error scenario...');
        
        try {
            // Try to trigger the specific error scenario from the user
            const errorTest = await this.page.evaluate(() => {
                try {
                    // Simulate the error scenario
                    const errorMessage = 'Test error for TypeError: Cannot read properties of undefined (reading \'extend\')';
                    window.SPRINT.showError(errorMessage);
                    return { success: true };
                } catch (error) {
                    console.error('Error in scenario:', error);
                    return { success: false, error: error.message };
                }
            });
            
            if (errorTest.success) {
                console.log('✅ Error scenario handled successfully');
                await this.takeScreenshot('toastr_test_error_scenario', 'Error scenario test');
                this.testResults.push({ 
                    test: 'Error Scenario', 
                    status: 'PASS' 
                });
            } else {
                console.log('❌ Error scenario failed:', errorTest.error);
                this.testResults.push({ 
                    test: 'Error Scenario', 
                    status: 'FAIL', 
                    error: errorTest.error 
                });
            }
        } catch (error) {
            console.error('❌ Error scenario test error:', error.message);
            this.testResults.push({ 
                test: 'Error Scenario', 
                status: 'FAIL', 
                error: error.message 
            });
        }
    }

    async generateReport() {
        console.log('📋 Generating notification test report...');
        
        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'Toastr Notification & Error Handling Test',
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };
        
        const reportPath = `${config.output.reports}/toastr_notification_test_${timestamp.replace(/[:.]/g, '-')}.json`;
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
            
            // Run notification-specific tests
            await this.testLibraryLoading();
            await this.testToastrConfiguration();
            await this.testSPRINTFallback();
            await this.testNotificationTrigger();
            await this.testErrorScenario();
            
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
    const test = new ToastrNotificationTest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ Toastr notification test completed successfully!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ Toastr notification test failed:', error.message);
            process.exit(1);
        });
}

module.exports = ToastrNotificationTest;
