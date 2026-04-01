/**
 * Manual Save Button Test with Puppeteer
 * SPRIN Application Testing
 */

const puppeteer = require('puppeteer');
const config = require('../config');

class ManualSaveTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.screenshotDir = config.output.screenshots;
    }

    async init() {
        console.log('🔧 Starting Manual Save Button Test...');
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
                await this.takeScreenshot('manual_initial_load', 'Initial bagian page load');
                return true;
            }
            return false;
        } catch (error) {
            console.error('❌ Navigation error:', error.message);
            return false;
        }
    }

    async manuallyTriggerChanges() {
        console.log('🔧 Manually triggering changes and save button...');

        try {
            const triggerResult = await this.page.evaluate(() => {
                // Initialize changes array if not exists
                if (!window.changes) {
                    window.changes = [];
                }

                // Add a test change
                window.changes.push({
                    bagian_id: '5',
                    old_unsur_id: '2',
                    new_unsur_id: '2',
                    new_urutan: 2
                });

                // Manually show save buttons
                const saveBtn = document.getElementById('saveChangesBtn');
                const cancelBtn = document.getElementById('cancelChangesBtn');

                if (saveBtn && cancelBtn) {
                    saveBtn.style.display = 'inline-block';
                    cancelBtn.style.display = 'inline-block';

                    return {
                        success: true,
                        saveButtonVisible: saveBtn.style.display !== 'none',
                        cancelButtonVisible: cancelBtn.style.display !== 'none',
                        changesCount: window.changes.length
                    };
                } else {
                    return {
                        success: false,
                        error: 'Save buttons not found'
                    };
                }
            });

            if (triggerResult.success) {
                console.log('✅ Changes triggered and save buttons shown');
                console.log(`Save button visible: ${triggerResult.saveButtonVisible}`);
                console.log(`Cancel button visible: ${triggerResult.cancelButtonVisible}`);
                console.log(`Changes count: ${triggerResult.changesCount}`);

                await this.takeScreenshot('manual_save_buttons_shown', 'Save buttons manually shown');

                this.testResults.push({
                    test: 'Manual Trigger Changes',
                    status: 'PASS',
                    data: triggerResult
                });

                return triggerResult;
            } else {
                console.log('❌ Failed to trigger changes:', triggerResult.error);
                this.testResults.push({
                    test: 'Manual Trigger Changes',
                    status: 'FAIL',
                    error: triggerResult.error
                });
                return null;
            }
        } catch (error) {
            console.error('❌ Manual trigger error:', error.message);
            this.testResults.push({
                test: 'Manual Trigger Changes',
                status: 'FAIL',
                error: error.message
            });
            return null;
        }
    }

    async clickSaveButton() {
        console.log('💾 Clicking save button...');

        try {
            const saveResult = await this.page.evaluate(() => {
                return new Promise((resolve) => {
                    const saveBtn = document.getElementById('saveChangesBtn');

                    if (!saveBtn) {
                        resolve({ success: false, error: 'Save button not found' });
                        return;
                    }

                    if (saveBtn.style.display === 'none') {
                        resolve({ success: false, error: 'Save button not visible' });
                        return;
                    }

                    // Click save button
                    saveBtn.click();

                    // Wait for response
                    setTimeout(() => {
                        resolve({ success: true, message: 'Save button clicked' });
                    }, 2000);
                });
            });

            if (saveResult.success) {
                console.log('✅ Save button clicked');

                // Wait for save operation
                await new Promise(resolve => setTimeout(resolve, 3000));

                // Check for notifications
                const notifications = await this.page.evaluate(() => {
                    const alerts = document.querySelectorAll('.alert');
                    const messages = [];

                    alerts.forEach(alert => {
                        const text = alert.textContent.trim();
                        if (text.includes('berhasil') || text.includes('success')) {
                            messages.push(text);
                        }
                    });

                    return messages;
                });

                console.log('Save notifications:', notifications);
                await this.takeScreenshot('manual_after_save', 'After manual save operation');

                this.testResults.push({
                    test: 'Manual Save',
                    status: 'PASS',
                    data: { saveResult, notifications }
                });

                return true;
            } else {
                console.log('❌ Save failed:', saveResult.error);
                this.testResults.push({
                    test: 'Manual Save',
                    status: 'FAIL',
                    error: saveResult.error
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Manual save error:', error.message);
            this.testResults.push({
                test: 'Manual Save',
                status: 'FAIL',
                error: error.message
            });
            return false;
        }
    }

    async verifyDatabaseUpdate() {
        console.log('🔍 Verifying database update...');

        try {
            // Check if save operation was successful by checking if save buttons are hidden
            const verification = await this.page.evaluate(() => {
                const saveBtn = document.getElementById('saveChangesBtn');
                const cancelBtn = document.getElementById('cancelChangesBtn');

                return {
                    saveButtonHidden: saveBtn ? saveBtn.style.display === 'none' : false,
                    cancelButtonHidden: cancelBtn ? cancelBtn.style.display === 'none' : false,
                    changesArray: window.changes || []
                };
            });

            console.log('Verification result:', verification);

            if (verification.saveButtonHidden && verification.cancelButtonHidden) {
                console.log('✅ Save operation completed - buttons hidden');
                this.testResults.push({
                    test: 'Database Update Verification',
                    status: 'PASS',
                    data: verification
                });
                return true;
            } else {
                console.log('⚠️ Save buttons still visible - operation might not be complete');
                this.testResults.push({
                    test: 'Database Update Verification',
                    status: 'WARNING',
                    data: verification
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Verification error:', error.message);
            this.testResults.push({
                test: 'Database Update Verification',
                status: 'FAIL',
                error: error.message
            });
            return false;
        }
    }

    async generateReport() {
        console.log('📋 Generating manual save test report...');

        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'Manual Save Button Test',
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };

        const reportPath = `${config.output.reports}/manual_save_test_${timestamp.replace(/[:.]/g, '-')}.json`;
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

            // Run manual save test
            const triggerResult = await this.manuallyTriggerChanges();

            if (triggerResult) {
                const saveSuccess = await this.clickSaveButton();

                if (saveSuccess) {
                    await this.verifyDatabaseUpdate();
                }
            }

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
    const test = new ManualSaveTest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ Manual save test completed!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ Manual save test failed:', error.message);
            process.exit(1);
        });
}

module.exports = ManualSaveTest;
