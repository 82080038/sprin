/**
 * Test Modal Error Fix
 * SPRIN Application Testing
 */

const puppeteer = require('puppeteer');
const config = require('../config');

class ModalErrorTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.screenshotDir = config.output.screenshots;
    }

    async init() {
        console.log('🔧 Starting Modal Error Test...');
        this.browser = await puppeteer.launch(config.browser);
        this.page = await this.browser.newPage();
        await this.page.setViewport(config.browser.defaultViewport);

        // Capture console errors
        this.page.on('console', msg => {
            if (msg.type() === 'error') {
                console.log('🚨 Console Error:', msg.text());
                this.testResults.push({
                    test: 'Console Error',
                    status: 'FAIL',
                    data: { error: msg.text() }
                });
            }
        });

        // Capture page errors
        this.page.on('pageerror', error => {
            console.log('🚨 Page Error:', error.message);
            this.testResults.push({
                test: 'Page Error',
                status: 'FAIL',
                data: { error: error.message, stack: error.stack }
            });
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
                await this.takeScreenshot('modal_initial_load', 'Initial unsur page for modal test');
                return true;
            }
            return false;
        } catch (error) {
            console.error('❌ Navigation error:', error.message);
            return false;
        }
    }

    async testAddModal() {
        console.log('➕ Testing Add Modal...');

        try {
            // Wait for page to load completely
            await new Promise(resolve => setTimeout(resolve, 2000));

            // Find and click add button
            const addButton = await this.page.$('button[onclick="openAddModal()"]');
            if (!addButton) {
                console.log('❌ Add button not found');
                this.testResults.push({
                    test: 'Add Modal Button',
                    status: 'FAIL',
                    error: 'Add button not found'
                });
                return false;
            }

            console.log('✅ Add button found');

            // Click add button
            await addButton.click();

            // Wait for modal to appear
            await new Promise(resolve => setTimeout(resolve, 1000));

            // Check if modal is visible
            const modalVisible = await this.page.evaluate(() => {
                const modal = document.getElementById('unsurModal');
                return modal && modal.classList.contains('show');
            });

            if (modalVisible) {
                console.log('✅ Add modal opened successfully');
                await this.takeScreenshot('add_modal_open', 'Add modal opened successfully');

                // Test form filling
                await this.page.type('#nama_unsur', 'TEST UNSUR');
                await this.page.type('#urutan', '999');
                await this.page.type('#deskripsi', 'Test description');

                // Test modal close
                const closeButton = await this.page.$('button[data-bs-dismiss="modal"]');
                if (closeButton) {
                    await closeButton.click();
                    await new Promise(resolve => setTimeout(resolve, 500));

                    const modalHidden = await this.page.evaluate(() => {
                        const modal = document.getElementById('unsurModal');
                        return !modal || !modal.classList.contains('show');
                    });

                    if (modalHidden) {
                        console.log('✅ Modal closed successfully');
                        this.testResults.push({
                            test: 'Add Modal',
                            status: 'PASS',
                            data: { opened: true, closed: true }
                        });
                        return true;
                    } else {
                        console.log('❌ Modal failed to close');
                        this.testResults.push({
                            test: 'Add Modal',
                            status: 'FAIL',
                            error: 'Modal failed to close'
                        });
                        return false;
                    }
                } else {
                    console.log('❌ Close button not found');
                    this.testResults.push({
                        test: 'Add Modal',
                        status: 'FAIL',
                        error: 'Close button not found'
                    });
                    return false;
                }
            } else {
                console.log('❌ Add modal failed to open');
                this.testResults.push({
                    test: 'Add Modal',
                    status: 'FAIL',
                    error: 'Modal failed to open'
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Add modal test error:', error.message);
            this.testResults.push({
                test: 'Add Modal',
                status: 'FAIL',
                error: error.message
            });
            return false;
        }
    }

    async testEditModal() {
        console.log('✏️ Testing Edit Modal...');

        try {
            // Find first edit button
            const editButton = await this.page.$('button[onclick*="editUnsur"]');
            if (!editButton) {
                console.log('❌ Edit button not found');
                this.testResults.push({
                    test: 'Edit Modal Button',
                    status: 'FAIL',
                    error: 'Edit button not found'
                });
                return false;
            }

            console.log('✅ Edit button found');

            // Click edit button
            await editButton.click();

            // Wait for modal to appear
            await new Promise(resolve => setTimeout(resolve, 1000));

            // Check if modal is visible
            const modalVisible = await this.page.evaluate(() => {
                const modal = document.getElementById('unsurModal');
                return modal && modal.classList.contains('show');
            });

            if (modalVisible) {
                console.log('✅ Edit modal opened successfully');
                await this.takeScreenshot('edit_modal_open', 'Edit modal opened successfully');

                // Test modal close
                const closeButton = await this.page.$('button[data-bs-dismiss="modal"]');
                if (closeButton) {
                    await closeButton.click();
                    await new Promise(resolve => setTimeout(resolve, 500));

                    const modalHidden = await this.page.evaluate(() => {
                        const modal = document.getElementById('unsurModal');
                        return !modal || !modal.classList.contains('show');
                    });

                    if (modalHidden) {
                        console.log('✅ Edit modal closed successfully');
                        this.testResults.push({
                            test: 'Edit Modal',
                            status: 'PASS',
                            data: { opened: true, closed: true }
                        });
                        return true;
                    } else {
                        console.log('❌ Edit modal failed to close');
                        this.testResults.push({
                            test: 'Edit Modal',
                            status: 'FAIL',
                            error: 'Edit modal failed to close'
                        });
                        return false;
                    }
                } else {
                    console.log('❌ Close button not found');
                    this.testResults.push({
                        test: 'Edit Modal',
                        status: 'FAIL',
                        error: 'Close button not found'
                    });
                    return false;
                }
            } else {
                console.log('❌ Edit modal failed to open');
                this.testResults.push({
                    test: 'Edit Modal',
                    status: 'FAIL',
                    error: 'Edit modal failed to open'
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Edit modal test error:', error.message);
            this.testResults.push({
                test: 'Edit Modal',
                status: 'FAIL',
                error: error.message
            });
            return false;
        }
    }

    async checkForErrors() {
        console.log('🔍 Checking for JavaScript errors...');

        const errors = this.testResults.filter(r => r.test === 'Console Error' || r.test === 'Page Error');

        if (errors.length === 0) {
            console.log('✅ No JavaScript errors detected');
            this.testResults.push({
                test: 'JavaScript Errors',
                status: 'PASS',
                data: { errors: 0 }
            });
        } else {
            console.log(`❌ Found ${errors.length} JavaScript errors`);
            errors.forEach(error => {
                console.log('  -', error.data.error);
            });
            this.testResults.push({
                test: 'JavaScript Errors',
                status: 'FAIL',
                data: { errors: errors.length, details: errors }
            });
        }
    }

    async generateReport() {
        console.log('📋 Generating modal error test report...');

        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'Modal Error Fix Test',
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };

        const reportPath = `${config.output.reports}/modal_error_test_${timestamp.replace(/[:.]/g, '-')}.json`;
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
            await this.testAddModal();
            await this.testEditModal();
            await this.checkForErrors();

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
    const test = new ModalErrorTest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ Modal error test completed!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ Modal error test failed:', error.message);
            process.exit(1);
        });
}

module.exports = ModalErrorTest;
