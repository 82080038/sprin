/**
 * Test Bagian Ordering Persistence with Puppeteer
 * SPRIN Application Testing
 */

const puppeteer = require('puppeteer');
const config = require('../config');

class BagianOrderingTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.screenshotDir = config.output.screenshots;
    }

    async init() {
        console.log('🔄 Starting Bagian Ordering Persistence Test...');
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
                await this.takeScreenshot('ordering_initial_load', 'Initial bagian page load');
                return true;
            }
            return false;
        } catch (error) {
            console.error('❌ Navigation error:', error.message);
            return false;
        }
    }

    async captureInitialOrder() {
        console.log('📊 Capturing initial bagian order...');
        
        try {
            const initialOrder = await this.page.evaluate(() => {
                const unsurCards = document.querySelectorAll('.unsur-card');
                const orderData = [];
                
                unsurCards.forEach((card, cardIndex) => {
                    const unsurName = card.querySelector('.unsur-header h6')?.textContent.trim();
                    const bagianItems = card.querySelectorAll('.bagian-item');
                    const bagians = [];
                    
                    bagianItems.forEach((item, itemIndex) => {
                        bagians.push({
                            index: itemIndex,
                            id: item.getAttribute('data-id'),
                            name: item.querySelector('.bagian-name')?.textContent.trim(),
                            urutan: item.getAttribute('data-urutan') || itemIndex + 1
                        });
                    });
                    
                    orderData.push({
                        unsurIndex: cardIndex,
                        unsurName: unsurName,
                        bagians: bagians
                    });
                });
                
                return orderData;
            });
            
            console.log('Initial Order:', JSON.stringify(initialOrder, null, 2));
            this.testResults.push({ 
                test: 'Initial Order Capture', 
                status: 'PASS', 
                data: initialOrder 
            });
            
            return initialOrder;
        } catch (error) {
            console.error('❌ Initial order capture error:', error.message);
            this.testResults.push({ 
                test: 'Initial Order Capture', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async checkUrutanColumn() {
        console.log('🔍 Checking if urutan column exists in database...');
        
        try {
            const columnCheck = await this.page.evaluate(async () => {
                try {
                    const response = await fetch('./bagian.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'check_urutan_column'
                        })
                    });
                    
                    if (response.ok) {
                        const data = await response.text();
                        return { success: true, data: data };
                    }
                    return { success: false, error: 'Request failed' };
                } catch (error) {
                    return { success: false, error: error.message };
                }
            });
            
            console.log('Column check result:', columnCheck);
            
            if (columnCheck.success) {
                this.testResults.push({ 
                    test: 'Urutan Column Check', 
                    status: 'PASS', 
                    data: columnCheck 
                });
            } else {
                this.testResults.push({ 
                    test: 'Urutan Column Check', 
                    status: 'FAIL', 
                    error: columnCheck.error 
                });
            }
            
        } catch (error) {
            console.error('❌ Column check error:', error.message);
            this.testResults.push({ 
                test: 'Urutan Column Check', 
                status: 'FAIL', 
                error: error.message 
            });
        }
    }

    async simulateDragAndDrop() {
        console.log('🖱️ Simulating drag and drop operation...');
        
        try {
            // Find first bagian item and try to move it
            const dragResult = await this.page.evaluate(() => {
                const firstContainer = document.querySelector('.sortable-bagian');
                const bagianItems = firstContainer?.querySelectorAll('.bagian-item');
                
                if (!firstContainer || bagianItems.length < 2) {
                    return { success: false, error: 'Not enough items to drag' };
                }
                
                const firstItem = bagianItems[0];
                const secondItem = bagianItems[1];
                
                // Get initial positions
                const firstId = firstItem.getAttribute('data-id');
                const secondId = secondItem.getAttribute('data-id');
                const firstUrutan = firstItem.getAttribute('data-urutan');
                const secondUrutan = secondItem.getAttribute('data-urutan');
                
                // Simulate drag and drop by swapping data attributes
                firstItem.setAttribute('data-urutan', secondUrutan);
                secondItem.setAttribute('data-urutan', firstUrutan);
                
                // Swap DOM positions
                const parent = firstItem.parentNode;
                const nextSibling = firstItem.nextSibling;
                parent.insertBefore(firstItem, secondItem.nextSibling);
                parent.insertBefore(secondItem, nextSibling);
                
                // Trigger change detection
                const event = new Event('change', { bubbles: true });
                firstContainer.dispatchEvent(event);
                
                return {
                    success: true,
                    movedItem: {
                        id: firstId,
                        oldUrutan: firstUrutan,
                        newUrutan: secondUrutan
                    },
                    targetItem: {
                        id: secondId,
                        oldUrutan: secondUrutan,
                        newUrutan: firstUrutan
                    }
                };
            });
            
            if (dragResult.success) {
                console.log('✅ Drag and drop simulated');
                await this.takeScreenshot('ordering_after_drag', 'After drag and drop simulation');
                
                this.testResults.push({ 
                    test: 'Drag Drop Simulation', 
                    status: 'PASS', 
                    data: dragResult 
                });
                
                return dragResult;
            } else {
                console.log('❌ Drag and drop simulation failed:', dragResult.error);
                this.testResults.push({ 
                    test: 'Drag Drop Simulation', 
                    status: 'FAIL', 
                    error: dragResult.error 
                });
                return null;
            }
        } catch (error) {
            console.error('❌ Drag drop simulation error:', error.message);
            this.testResults.push({ 
                test: 'Drag Drop Simulation', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async saveChanges() {
        console.log('💾 Saving changes...');
        
        try {
            // Look for save button and click it
            const saveResult = await this.page.evaluate(() => {
                const saveButton = document.querySelector('#saveChangesBtn');
                if (!saveButton) {
                    return { success: false, error: 'Save button not found' };
                }
                
                if (saveButton.style.display === 'none') {
                    return { success: false, error: 'Save button not visible' };
                }
                
                // Click save button
                saveButton.click();
                return { success: true, message: 'Save button clicked' };
            });
            
            if (saveResult.success) {
                console.log('✅ Save button clicked');
                
                // Wait for save operation to complete
                await this.page.waitForTimeout(2000);
                
                // Check for success notification
                const notificationCheck = await this.page.evaluate(() => {
                    const notifications = document.querySelectorAll('.alert, .toast');
                    const messages = [];
                    
                    notifications.forEach(notification => {
                        const text = notification.textContent.trim();
                        if (text.includes('berhasil') || text.includes('success')) {
                            messages.push(text);
                        }
                    });
                    
                    return messages;
                });
                
                console.log('Save notifications:', notificationCheck);
                await this.takeScreenshot('ordering_after_save', 'After save operation');
                
                this.testResults.push({ 
                    test: 'Save Changes', 
                    status: 'PASS', 
                    data: { saveResult, notifications: notificationCheck }
                });
                
                return true;
            } else {
                console.log('❌ Save operation failed:', saveResult.error);
                this.testResults.push({ 
                    test: 'Save Changes', 
                    status: 'FAIL', 
                    error: saveResult.error 
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Save changes error:', error.message);
            this.testResults.push({ 
                test: 'Save Changes', 
                status: 'FAIL', 
                error: error.message 
            });
            return false;
        }
    }

    async reloadAndCheckOrder() {
        console.log('🔄 Reloading page and checking order persistence...');
        
        try {
            // Reload the page
            await this.page.reload({ waitUntil: 'networkidle2' });
            await this.page.waitForTimeout(1000);
            
            await this.takeScreenshot('ordering_after_reload', 'After page reload');
            
            // Capture order after reload
            const reloadedOrder = await this.page.evaluate(() => {
                const unsurCards = document.querySelectorAll('.unsur-card');
                const orderData = [];
                
                unsurCards.forEach((card, cardIndex) => {
                    const unsurName = card.querySelector('.unsur-header h6')?.textContent.trim();
                    const bagianItems = card.querySelectorAll('.bagian-item');
                    const bagians = [];
                    
                    bagianItems.forEach((item, itemIndex) => {
                        bagians.push({
                            index: itemIndex,
                            id: item.getAttribute('data-id'),
                            name: item.querySelector('.bagian-name')?.textContent.trim(),
                            urutan: item.getAttribute('data-urutan') || itemIndex + 1
                        });
                    });
                    
                    orderData.push({
                        unsurIndex: cardIndex,
                        unsurName: unsurName,
                        bagians: bagians
                    });
                });
                
                return orderData;
            });
            
            console.log('Reloaded Order:', JSON.stringify(reloadedOrder, null, 2));
            
            this.testResults.push({ 
                test: 'Order After Reload', 
                status: 'PASS', 
                data: reloadedOrder 
            });
            
            return reloadedOrder;
        } catch (error) {
            console.error('❌ Reload check error:', error.message);
            this.testResults.push({ 
                test: 'Order After Reload', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async compareOrders(initialOrder, reloadedOrder) {
        console.log('🔍 Comparing initial and reloaded orders...');
        
        try {
            if (!initialOrder || !reloadedOrder) {
                console.log('❌ Cannot compare - missing order data');
                this.testResults.push({ 
                    test: 'Order Comparison', 
                    status: 'FAIL', 
                    error: 'Missing order data' 
                });
                return false;
            }
            
            let orderChanged = false;
            const differences = [];
            
            // Compare each unsur
            for (let i = 0; i < initialOrder.length; i++) {
                const initialUnsur = initialOrder[i];
                const reloadedUnsur = reloadedOrder[i];
                
                if (initialUnsur.unsurName !== reloadedUnsur.unsurName) {
                    differences.push(`Unsur ${i}: "${initialUnsur.unsurName}" -> "${reloadedUnsur.unsurName}"`);
                    continue;
                }
                
                // Compare bagian orders
                for (let j = 0; j < initialUnsur.bagians.length; j++) {
                    const initialBagian = initialUnsur.bagians[j];
                    const reloadedBagian = reloadedUnsur.bagians[j];
                    
                    if (initialBagian.id !== reloadedBagian.id) {
                        differences.push(`${initialUnsur.unsurName} position ${j}: "${initialBagian.name}" -> "${reloadedBagian.name}"`);
                        orderChanged = true;
                    }
                }
            }
            
            console.log('Order differences:', differences);
            
            if (orderChanged) {
                console.log('✅ Order changed - persistence issue detected');
                this.testResults.push({ 
                    test: 'Order Comparison', 
                    status: 'FAIL', 
                    data: { differences, orderChanged: true },
                    error: 'Order not persisted after reload'
                });
            } else {
                console.log('✅ Order persisted correctly');
                this.testResults.push({ 
                    test: 'Order Comparison', 
                    status: 'PASS', 
                    data: { differences: [], orderChanged: false }
                });
            }
            
            return !orderChanged;
        } catch (error) {
            console.error('❌ Order comparison error:', error.message);
            this.testResults.push({ 
                test: 'Order Comparison', 
                status: 'FAIL', 
                error: error.message 
            });
            return false;
        }
    }

    async generateReport() {
        console.log('📋 Generating ordering persistence test report...');
        
        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'Bagian Ordering Persistence Test',
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };
        
        const reportPath = `${config.output.reports}/ordering_persistence_test_${timestamp.replace(/[:.]/g, '-')}.json`;
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
            
            // Run ordering persistence tests
            const initialOrder = await this.captureInitialOrder();
            await this.checkUrutanColumn();
            const dragResult = await this.simulateDragAndDrop();
            
            if (dragResult) {
                const saveSuccess = await this.saveChanges();
                if (saveSuccess) {
                    const reloadedOrder = await this.reloadAndCheckOrder();
                    await this.compareOrders(initialOrder, reloadedOrder);
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
    const test = new BagianOrderingTest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ Ordering persistence test completed!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ Ordering persistence test failed:', error.message);
            process.exit(1);
        });
}

module.exports = BagianOrderingTest;
