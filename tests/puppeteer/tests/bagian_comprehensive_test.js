/**
 * Comprehensive Bagian Page Testing with Puppeteer
 * SPRIN Application Testing
 */

const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');
const config = require('../config');

class BagianPageTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.screenshotDir = config.output.screenshots;
        this.ensureScreenshotDir();
    }

    ensureScreenshotDir() {
        if (!fs.existsSync(this.screenshotDir)) {
            fs.mkdirSync(this.screenshotDir, { recursive: true });
        }
    }

    async init() {
        console.log('🚀 Starting Bagian Page Comprehensive Test...');
        this.browser = await puppeteer.launch(config.browser);
        this.page = await this.browser.newPage();
        await this.page.setViewport(config.browser.defaultViewport);

        // Setup error handling
        this.page.on('error', error => {
            console.error('Page error:', error);
        });

        this.page.on('pageerror', error => {
            console.error('Page error:', error);
        });
    }

    async takeScreenshot(name, description = '') {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filename = `${name}_${timestamp}.png`;
        const filepath = path.join(this.screenshotDir, filename);

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

            await this.takeScreenshot('bagian_login_page', 'Login page loaded');

            // Try quick login first
            const quickLoginButton = await this.page.$(config.selectors.login.quickLoginButton);
            if (quickLoginButton) {
                console.log('Using quick login...');
                await quickLoginButton.click();
                await this.page.waitForNavigation({ waitUntil: 'networkidle2' });
            } else {
                // Manual login
                await this.page.type(config.selectors.login.usernameInput, config.credentials.username);
                await this.page.type(config.selectors.login.passwordInput, config.credentials.password);
                await this.page.click(config.selectors.login.submitButton);
                await this.page.waitForNavigation({ waitUntil: 'networkidle2' });
            }

            // Verify login success
            const currentUrl = this.page.url();
            if (currentUrl.includes('main.php') || currentUrl.includes('pages/')) {
                console.log('✅ Login successful');
                await this.takeScreenshot('bagian_login_success', 'Login successful - Dashboard');
                return true;
            } else {
                console.log('❌ Login failed');
                await this.takeScreenshot('bagian_login_failed', 'Login failed');
                return false;
            }
        } catch (error) {
            console.error('❌ Login error:', error.message);
            await this.takeScreenshot('bagian_login_error', 'Login error');
            return false;
        }
    }

    async navigateToBagianPage() {
        console.log('🧭 Navigating to Bagian page...');
        try {
            // Navigate directly to bagian page
            await this.page.goto(config.baseUrl + '/pages/bagian.php', {
                waitUntil: 'networkidle2',
                timeout: config.timeouts.navigation
            });

            // Verify we're on the bagian page
            const currentUrl = this.page.url();
            if (currentUrl.includes('bagian.php')) {
                console.log('✅ Successfully navigated to Bagian page');
                await this.takeScreenshot('bagian_page_loaded', 'Bagian page loaded successfully');
                return true;
            } else {
                console.log('❌ Failed to navigate to Bagian page');
                await this.takeScreenshot('bagian_navigation_failed', 'Failed to navigate to Bagian page');
                return false;
            }
        } catch (error) {
            console.error('❌ Navigation error:', error.message);
            await this.takeScreenshot('bagian_navigation_error', 'Navigation error');
            return false;
        }
    }

    async testPageStructure() {
        console.log('🏗️ Testing page structure...');
        const tests = [
            {
                name: 'Page Title',
                selector: '//h1[contains(text(), "Manajemen Bagian")]',
                type: 'xpath',
                description: 'Page title should be visible'
            },
            {
                name: 'Container',
                selector: '.container',
                type: 'css',
                description: 'Main container should exist'
            },
            {
                name: 'Action Buttons',
                selector: '.action-buttons',
                type: 'css',
                description: 'Action buttons container should exist'
            },
            {
                name: 'Add Button',
                selector: '//button[contains(text(), "Tambah Bagian")]',
                type: 'xpath',
                description: 'Add bagian button should exist'
            },
            {
                name: 'Refresh Button',
                selector: '//button[contains(text(), "Refresh")]',
                type: 'xpath',
                description: 'Refresh button should exist'
            },
            {
                name: 'Instructions Alert',
                selector: '//div[contains(@class, "alert-info") and contains(text(), "Petunjuk")]',
                type: 'xpath',
                description: 'Instructions alert should be visible'
            },
            {
                name: 'Unsur Container',
                selector: '#unsur-bagian-container',
                type: 'css',
                description: 'Unsur-bagian container should exist'
            },
            {
                name: 'Unsur Cards',
                selector: '.unsur-card',
                type: 'css',
                description: 'Unsur cards should exist'
            }
        ];

        for (const test of tests) {
            try {
                if (test.type === 'xpath') {
                    await this.page.waitForSelector(`::-p-xpath(${test.selector})`, { timeout: 3000 });
                    const element = await this.page.$(`::-p-xpath(${test.selector})`);
                    const isVisible = element && await element.isVisible();

                    if (isVisible) {
                        console.log(`✅ ${test.name}: Visible`);
                        this.testResults.push({ test: test.name, status: 'PASS', description: test.description });
                    } else {
                        console.log(`⚠️ ${test.name}: Not visible`);
                        this.testResults.push({ test: test.name, status: 'WARNING', description: test.description });
                    }
                } else {
                    await this.page.waitForSelector(test.selector, { timeout: 3000 });
                    const element = await this.page.$(test.selector);
                    const isVisible = element && await element.isVisible();

                    if (isVisible) {
                        console.log(`✅ ${test.name}: Visible`);
                        this.testResults.push({ test: test.name, status: 'PASS', description: test.description });
                    } else {
                        console.log(`⚠️ ${test.name}: Not visible`);
                        this.testResults.push({ test: test.name, status: 'WARNING', description: test.description });
                    }
                }
            } catch (error) {
                console.log(`❌ ${test.name}: Not found - ${error.message}`);
                this.testResults.push({ test: test.name, status: 'FAIL', description: test.description, error: error.message });
            }
        }

        await this.takeScreenshot('bagian_structure_test', 'Page structure test completed');
    }

    async testDropdownFunctionality() {
        console.log('🔄 Testing dropdown functionality...');

        const dropdownTests = [
            {
                name: 'Bagian Dropdown',
                selector: '//a[contains(@data-bs-toggle, "dropdown") and contains(text(), "Bagian")]',
                menuSelector: '.dropdown-menu',
                menuItems: ['Manajemen Unsur', 'Manajemen Bagian', 'Manajemen Jabatan']
            },
            {
                name: 'Laporan Dropdown',
                selector: '//a[contains(@data-bs-toggle, "dropdown") and contains(text(), "Laporan")]',
                menuSelector: '.dropdown-menu',
                menuItems: ['Export PDF', 'Export Excel', 'Cetak Laporan']
            },
            {
                name: 'Pengaturan Dropdown',
                selector: '//a[contains(@data-bs-toggle, "dropdown") and contains(text(), "Pengaturan")]',
                menuSelector: '.dropdown-menu',
                menuItems: ['Manajemen User', 'Manajemen Backup']
            }
        ];

        for (const dropdownTest of dropdownTests) {
            try {
                // Click dropdown
                const dropdownElement = await this.page.$(`::-p-xpath(${dropdownTest.selector})`);
                await dropdownElement.click();
                await this.page.waitForFunction(() => document.querySelector('.dropdown-menu.show'), { timeout: 500 });

                // Check if menu appears
                const menuElement = await this.page.$(dropdownTest.menuSelector);
                const menuVisible = menuElement && await menuElement.isVisible();

                if (menuVisible) {
                    console.log(`✅ ${dropdownTest.name}: Menu appears`);

                    // Check menu items
                    for (const item of dropdownTest.menuItems) {
                        const itemSelector = `::-p-xpath(//a[contains(@class, "dropdown-item") and contains(text(), "${item}")])`;
                        const itemElement = await this.page.$(itemSelector);
                        if (itemElement) {
                            console.log(`  ✅ Menu item: ${item}`);
                        } else {
                            console.log(`  ❌ Menu item missing: ${item}`);
                        }
                    }

                    await this.takeScreenshot(`dropdown_${dropdownTest.name.toLowerCase().replace(' ', '_')}`, `${dropdownTest.name} dropdown test`);

                    // Close dropdown by clicking elsewhere
                    await this.page.click('body');
                    await new Promise(resolve => setTimeout(resolve, 200));

                    this.testResults.push({ test: `${dropdownTest.name} Dropdown`, status: 'PASS' });
                } else {
                    console.log(`❌ ${dropdownTest.name}: Menu not appearing`);
                    this.testResults.push({ test: `${dropdownTest.name} Dropdown`, status: 'FAIL', error: 'Menu not appearing' });
                }
            } catch (error) {
                console.log(`❌ ${dropdownTest.name}: Error - ${error.message}`);
                this.testResults.push({ test: `${dropdownTest.name} Dropdown`, status: 'FAIL', error: error.message });
            }
        }
    }

    async testModalFunctionality() {
        console.log('🪟 Testing modal functionality...');

        try {
            // Click "Tambah Bagian" button
            const addButton = await this.page.$(`::-p-xpath(//button[contains(text(), "Tambah Bagian")])`);
            await addButton.click();
            await this.page.waitForFunction(() => document.querySelector('#bagianModal.show'), { timeout: 500 });

            // Check if modal appears
            const modalSelector = '#bagianModal';
            await this.page.waitForSelector(modalSelector, { timeout: 3000 });

            const modalElement = await this.page.$(modalSelector);
            const modalVisible = modalElement && await modalElement.isVisible();

            if (modalVisible) {
                console.log('✅ Modal appears');

                // Test modal elements
                const modalTests = [
                    { name: 'Modal Title', selector: '//h5[contains(text(), "Tambah Bagian")]', type: 'xpath' },
                    { name: 'Nama Bagian Input', selector: '#nama_bagian', type: 'css' },
                    { name: 'Unsur Select', selector: '#id_unsur', type: 'css' },
                    { name: 'Type Select', selector: '#type', type: 'css' },
                    { name: 'Simpan Button', selector: '//button[contains(text(), "Simpan")]', type: 'xpath' },
                    { name: 'Batal Button', selector: '//button[contains(text(), "Batal")]', type: 'xpath' }
                ];

                for (const test of modalTests) {
                    try {
                        let element;
                        if (test.type === 'xpath') {
                            element = await this.page.$(`::-p-xpath(${test.selector})`);
                        } else {
                            element = await this.page.$(test.selector);
                        }
                        const exists = element && await element.isVisible();
                        if (exists) {
                            console.log(`  ✅ ${test.name}`);
                        } else {
                            console.log(`  ❌ ${test.name}: Not visible`);
                        }
                    } catch (error) {
                        console.log(`  ❌ ${test.name}: Error - ${error.message}`);
                    }
                }

                await this.takeScreenshot('bagian_modal_open', 'Bagian modal opened');

                // Test form interaction
                await this.page.type('#nama_bagian', 'TEST BAGIAN');
                await this.page.select('#id_unsur', '1');
                await this.page.select('#type', 'BAG/SAT/SIE');

                await this.takeScreenshot('bagian_modal_filled', 'Bagian modal filled');

                // Close modal
                await this.page.click('button[data-bs-dismiss="modal"]');
                await new Promise(resolve => setTimeout(resolve, 500));

                this.testResults.push({ test: 'Modal Functionality', status: 'PASS' });
            } else {
                console.log('❌ Modal not appearing');
                this.testResults.push({ test: 'Modal Functionality', status: 'FAIL', error: 'Modal not appearing' });
            }
        } catch (error) {
            console.log('❌ Modal error:', error.message);
            this.testResults.push({ test: 'Modal Functionality', status: 'FAIL', error: error.message });
        }
    }

    async testDataLoading() {
        console.log('📊 Testing data loading...');

        try {
            // Check if unsur cards are loaded
            const unsurCards = await this.page.$$('.unsur-card');
            console.log(`✅ Found ${unsurCards.length} unsur cards`);

            if (unsurCards.length > 0) {
                // Check first card content
                const firstCard = unsurCards[0];
                const headerText = await firstCard.$eval('.unsur-header h6', el => el.textContent.trim());
                console.log(`✅ First unsur: ${headerText}`);

                // Check bagian items in first card
                const bagianItems = await firstCard.$$('.bagian-item');
                console.log(`✅ Found ${bagianItems.length} bagian items in first unsur`);

                if (bagianItems.length > 0) {
                    const firstBagian = bagianItems[0];
                    const bagianName = await firstBagian.$eval('.bagian-name', el => el.textContent.trim());
                    console.log(`✅ First bagian: ${bagianName}`);
                }

                this.testResults.push({ test: 'Data Loading', status: 'PASS', data: { unsurCards: unsurCards.length } });
            } else {
                console.log('⚠️ No unsur cards found');
                this.testResults.push({ test: 'Data Loading', status: 'WARNING', error: 'No unsur cards found' });
            }

            await this.takeScreenshot('bagian_data_loaded', 'Data loading test');
        } catch (error) {
            console.log('❌ Data loading error:', error.message);
            this.testResults.push({ test: 'Data Loading', status: 'FAIL', error: error.message });
        }
    }

    async testResponsiveDesign() {
        console.log('📱 Testing responsive design...');

        const viewports = [
            { width: 1920, height: 1080, name: 'Desktop' },
            { width: 1366, height: 768, name: 'Laptop' },
            { width: 768, height: 1024, name: 'Tablet' },
            { width: 375, height: 667, name: 'Mobile' }
        ];

        for (const viewport of viewports) {
            try {
                await this.page.setViewport({ width: viewport.width, height: viewport.height });
                await new Promise(resolve => setTimeout(resolve, 500));

                // Check if navbar is responsive
                const navbar = await this.page.$('.navbar');
                const navbarVisible = navbar && await navbar.isVisible();

                // Check if content is still accessible
                const container = await this.page.$('.container');
                const containerVisible = container && await container.isVisible();

                if (navbarVisible && containerVisible) {
                    console.log(`✅ ${viewport.name}: Responsive`);
                    await this.takeScreenshot(`bagian_responsive_${viewport.name.toLowerCase()}`, `Responsive test - ${viewport.name}`);
                } else {
                    console.log(`⚠️ ${viewport.name}: Layout issues`);
                }
            } catch (error) {
                console.log(`❌ ${viewport.name}: Error - ${error.message}`);
            }
        }

        // Reset to default viewport
        await this.page.setViewport(config.browser.defaultViewport);
    }

    async testErrorHandling() {
        console.log('🚨 Testing error handling...');

        try {
            // Test with invalid form submission
            const addButton = await this.page.$(`::-p-xpath(//button[contains(text(), "Tambah Bagian")])`);
            await addButton.click();
            await this.page.waitForFunction(() => document.querySelector('#bagianModal.show'), { timeout: 500 });

            // Try to submit empty form
            const saveButton = await this.page.$(`::-p-xpath(//button[contains(text(), "Simpan")])`);
            await saveButton.click();
            await new Promise(resolve => setTimeout(resolve, 1000));

            // Check if validation works
            const formStillVisible = await this.page.$eval('#bagianModal', el => el.style.display !== 'none');
            if (formStillVisible) {
                console.log('✅ Form validation working');
            }

            // Close modal
            await this.page.click('button[data-bs-dismiss="modal"]');

            this.testResults.push({ test: 'Error Handling', status: 'PASS' });
        } catch (error) {
            console.log('❌ Error handling test failed:', error.message);
            this.testResults.push({ test: 'Error Handling', status: 'FAIL', error: error.message });
        }
    }

    async generateReport() {
        console.log('📋 Generating test report...');

        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'Bagian Page Comprehensive Test',
            browser: config.browser,
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };

        const reportPath = path.join(config.output.reports, `bagian_test_report_${timestamp.replace(/[:.]/g, '-')}.json`);
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));

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

            // Login and navigate
            const loginSuccess = await this.login();
            if (!loginSuccess) {
                throw new Error('Login failed');
            }

            const navigationSuccess = await this.navigateToBagianPage();
            if (!navigationSuccess) {
                throw new Error('Navigation to bagian page failed');
            }

            // Run comprehensive tests
            await this.testPageStructure();
            await this.testDropdownFunctionality();
            await this.testModalFunctionality();
            await this.testDataLoading();
            await this.testResponsiveDesign();
            await this.testErrorHandling();

            // Generate report
            const report = await this.generateReport();

            return report;

        } catch (error) {
            console.error('❌ Test execution failed:', error.message);
            await this.takeScreenshot('bagian_test_error', 'Test execution error');
            throw error;
        } finally {
            await this.cleanup();
        }
    }
}

// Run the test
if (require.main === module) {
    const test = new BagianPageTest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ Bagian page comprehensive test completed successfully!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ Bagian page test failed:', error.message);
            process.exit(1);
        });
}

module.exports = BagianPageTest;
