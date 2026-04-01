/**
 * Test BKO Visibility in UNSUR LAINNYA
 * SPRIN Application Testing
 */

const puppeteer = require('puppeteer');
const config = require('../config');

class BKOVisibilityTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.screenshotDir = config.output.screenshots;
    }

    async init() {
        console.log('🔍 Starting BKO Visibility Test...');
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
                await this.takeScreenshot('bko_initial_load', 'Initial bagian page for BKO test');
                return true;
            }
            return false;
        } catch (error) {
            console.error('❌ Navigation error:', error.message);
            return false;
        }
    }

    async inspectUnsurLainnyaHTML() {
        console.log('🔍 Inspecting UNSUR LAINNYA HTML...');

        try {
            const htmlInspection = await this.page.evaluate(() => {
                const results = {
                    unsurCards: [],
                    bkoElements: [],
                    lainnyaCard: null,
                    lainnyaBagians: []
                };

                // Find all unsur cards
                const unsurCards = document.querySelectorAll('.unsur-card');
                unsurCards.forEach((card, index) => {
                    const unsurName = card.querySelector('h6')?.textContent.trim();
                    const unsurId = card.getAttribute('data-unsur-id');
                    const bagianList = card.querySelector('.bagian-list');
                    const bagians = bagianList ? bagianList.querySelectorAll('.bagian-item') : [];

                    results.unsurCards.push({
                        index: index,
                        unsurName: unsurName,
                        unsurId: unsurId,
                        totalBagians: bagians.length,
                        bagianNames: Array.from(bagians).map(b => b.querySelector('.bagian-name')?.textContent.trim())
                    });

                    if (unsurName === 'UNSUR LAINNYA') {
                        results.lainnyaCard = {
                            index: index,
                            unsurName: unsurName,
                            unsurId: unsurId,
                            totalBagians: bagians.length,
                            html: card.outerHTML.substring(0, 500) + '...'
                        };

                        // Get specific bagian details
                        bagians.forEach(bagian => {
                            const bagianName = bagian.querySelector('.bagian-name')?.textContent.trim();
                            const bagianId = bagian.getAttribute('data-id');
                            results.lainnyaBagians.push({
                                id: bagianId,
                                name: bagianName,
                                html: bagian.outerHTML
                            });
                        });
                    }
                });

                // Find all BKO elements
                const bkoElements = document.querySelectorAll('.bagian-item');
                bkoElements.forEach(element => {
                    const bagianName = element.querySelector('.bagian-name')?.textContent.trim();
                    if (bagianName === 'BKO') {
                        results.bkoElements.push({
                            tagName: element.tagName,
                            className: element.className,
                            textContent: bagianName,
                            id: element.getAttribute('data-id'),
                            html: element.outerHTML.substring(0, 200) + '...'
                        });
                    }
                });

                return results;
            });

            console.log('HTML Inspection Results:');
            console.log('Unsur Cards:', htmlInspection.unsurCards.length);
            htmlInspection.unsurCards.forEach(card => {
                console.log(`  ${card.index}. ${card.unsurName} (${card.unsurId}) - ${card.totalBagians} bagian`);
                if (card.unsurName === 'UNSUR LAINNYA') {
                    console.log(`     Bagians: ${card.bagianNames.join(', ')}`);
                }
            });

            console.log('BKO Elements:', htmlInspection.bkoElements.length);
            htmlInspection.bkoElements.forEach(element => {
                console.log(`  ${element.tagName} - ${element.textContent} (${element.id})`);
            });

            if (htmlInspection.lainnyaCard) {
                console.log('UNSUR LAINNYA Card Found:');
                console.log(`  Index: ${htmlInspection.lainnyaCard.index}`);
                console.log(`  Unsur ID: ${htmlInspection.lainnyaCard.unsurId}`);
                console.log(`  Total Bagians: ${htmlInspection.lainnyaCard.totalBagians}`);
                console.log(`  Bagians: ${htmlInspection.lainnyaBagians.map(b => b.name).join(', ')}`);

                if (htmlInspection.lainnyaCard.totalBagians > 0) {
                    console.log('✅ BKO visible in UNSUR LAINNYA card');
                    this.testResults.push({
                        test: 'BKO Visibility',
                        status: 'PASS',
                        data: htmlInspection.lainnyaCard
                    });
                } else {
                    console.log('❌ BKO NOT visible in UNSUR LAINNYA card');
                    this.testResults.push({
                        test: 'BKO Visibility',
                        status: 'FAIL',
                        data: htmlInspection.lainnyaCard
                    });
                }
            } else {
                console.log('❌ UNSUR LAINNYA card NOT found');
                this.testResults.push({
                    test: 'BKO Visibility',
                    status: 'FAIL',
                    error: 'UNSUR LAINNYA card not found'
                });
            }

            return htmlInspection;
        } catch (error) {
            console.error('❌ HTML inspection error:', error.message);
            this.testResults.push({
                test: 'BKO Visibility',
                status: 'FAIL',
                error: error.message
            });
            return null;
        }
    }

    async checkForHiddenElements() {
        console.log('🔍 Checking for hidden elements...');

        try {
            const hiddenCheck = await this.page.evaluate(() => {
                const results = {
                    hiddenElements: [],
                    bkoElement: null,
                    lainnyaCard: null
                };

                // Check BKO element specifically
                const bkoElements = document.querySelectorAll('.bagian-item');
                bkoElements.forEach(element => {
                    const bagianName = element.querySelector('.bagian-name')?.textContent.trim();
                    if (bagianName === 'BKO') {
                        results.bkoElement = {
                            textContent: bagianName,
                            display: window.getComputedStyle(element).display,
                            visibility: window.getComputedStyle(element).visibility,
                            opacity: window.getComputedStyle(element).opacity,
                            hidden: element.hidden,
                            offsetParent: element.offsetParent ? 'visible' : 'hidden',
                            className: element.className
                        };
                    }
                });

                // Check UNSUR LAINNYA card
                const unsurCards = document.querySelectorAll('.unsur-card');
                unsurCards.forEach(card => {
                    const unsurName = card.querySelector('h6')?.textContent.trim();
                    if (unsurName === 'UNSUR LAINNYA') {
                        results.lainnyaCard = {
                            display: window.getComputedStyle(card).display,
                            visibility: window.getComputedStyle(card).visibility,
                            opacity: window.getComputedStyle(card).opacity,
                            hidden: card.hidden,
                            offsetParent: card.offsetParent ? 'visible' : 'hidden'
                        };
                    }
                });

                return results;
            });

            console.log('Hidden Elements Check:');
            if (hiddenCheck.bkoElement) {
                console.log('BKO Element:');
                console.log(`  Display: ${hiddenCheck.bkoElement.display}`);
                console.log(`  Visibility: ${hiddenCheck.bkoElement.visibility}`);
                console.log(`  Hidden: ${hiddenCheck.bkoElement.hidden}`);
                console.log(`  OffsetParent: ${hiddenCheck.bkoElement.offsetParent}`);

                if (hiddenCheck.bkoElement.display === 'none' || hiddenCheck.bkoElement.hidden || hiddenCheck.bkoElement.offsetParent === 'hidden') {
                    console.log('❌ BKO element is hidden');
                    this.testResults.push({
                        test: 'BKO Hidden Check',
                        status: 'FAIL',
                        data: hiddenCheck.bkoElement
                    });
                } else {
                    console.log('✅ BKO element is visible');
                    this.testResults.push({
                        test: 'BKO Hidden Check',
                        status: 'PASS',
                        data: hiddenCheck.bkoElement
                    });
                }
            } else {
                console.log('❌ BKO element not found');
                this.testResults.push({
                    test: 'BKO Hidden Check',
                    status: 'FAIL',
                    error: 'BKO element not found'
                });
            }

            return hiddenCheck;
        } catch (error) {
            console.error('❌ Hidden check error:', error.message);
            this.testResults.push({
                test: 'BKO Hidden Check',
                status: 'FAIL',
                error: error.message
            });
            return null;
        }
    }

    async generateReport() {
        console.log('📋 Generating BKO visibility test report...');

        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'BKO Visibility in UNSUR LAINNYA Test',
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };

        const reportPath = `${config.output.reports}/bko_visibility_test_${timestamp.replace(/[:.]/g, '-')}.json`;
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

            // Run tests
            await this.inspectUnsurLainnyaHTML();
            await this.checkForHiddenElements();

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
    const test = new BKOVisibilityTest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ BKO visibility test completed!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ BKO visibility test failed:', error.message);
            process.exit(1);
        });
}

module.exports = BKOVisibilityTest;
