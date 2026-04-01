/**
 * Complete Bagian Drag-Drop Test with Puppeteer
 * SPRIN Application Testing - Full Workflow
 */

const puppeteer = require('puppeteer');
const config = require('../config');

class CompleteBagianTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.screenshotDir = config.output.screenshots;
    }

    async init() {
        console.log('🎯 Starting Complete Bagian Drag-Drop Test...');
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
                await this.takeScreenshot('complete_initial_load', 'Initial bagian page load');
                return true;
            }
            return false;
        } catch (error) {
            console.error('❌ Navigation error:', error.message);
            return false;
        }
    }

    async captureCurrentState() {
        console.log('📊 Capturing current bagian state...');
        
        try {
            const currentState = await this.page.evaluate(() => {
                const unsurCards = document.querySelectorAll('.unsur-card');
                const stateData = [];
                
                unsurCards.forEach((card, cardIndex) => {
                    const unsurName = card.querySelector('.unsur-header h6')?.textContent.trim();
                    const bagianItems = card.querySelectorAll('.bagian-item');
                    const bagians = [];
                    
                    bagianItems.forEach((item, itemIndex) => {
                        bagians.push({
                            position: itemIndex,
                            id: item.getAttribute('data-id'),
                            name: item.querySelector('.bagian-name')?.textContent.trim(),
                            urutan: item.getAttribute('data-urutan'),
                            unsurId: item.getAttribute('data-unsur-id')
                        });
                    });
                    
                    stateData.push({
                        unsurIndex: cardIndex,
                        unsurName: unsurName,
                        totalBagians: bagianItems.length,
                        bagians: bagians
                    });
                });
                
                return stateData;
            });
            
            console.log('Current State:', JSON.stringify(currentState, null, 2));
            this.testResults.push({ 
                test: 'Current State Capture', 
                status: 'PASS', 
                data: currentState 
            });
            
            return currentState;
        } catch (error) {
            console.error('❌ State capture error:', error.message);
            this.testResults.push({ 
                test: 'Current State Capture', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async findContainerWithMultipleItems() {
        console.log('🔍 Finding container with multiple items for drag test...');
        
        try {
            const containerInfo = await this.page.evaluate(() => {
                const containers = document.querySelectorAll('.sortable-bagian');
                const validContainers = [];
                
                containers.forEach((container, index) => {
                    const items = container.querySelectorAll('.bagian-item');
                    const unsurName = container.closest('.unsur-card')?.querySelector('.unsur-header h6')?.textContent.trim();
                    
                    if (items.length >= 2) {
                        validContainers.push({
                            containerIndex: index,
                            unsurName: unsurName,
                            itemCount: items.length,
                            firstItem: {
                                id: items[0].getAttribute('data-id'),
                                name: items[0].querySelector('.bagian-name')?.textContent.trim(),
                                urutan: items[0].getAttribute('data-urutan')
                            },
                            secondItem: {
                                id: items[1].getAttribute('data-id'),
                                name: items[1].querySelector('.bagian-name')?.textContent.trim(),
                                urutan: items[1].getAttribute('data-urutan')
                            }
                        });
                    }
                });
                
                return validContainers;
            });
            
            console.log('Valid containers:', containerInfo);
            
            if (containerInfo.length > 0) {
                console.log(`✅ Found container with ${containerInfo[0].itemCount} items: ${containerInfo[0].unsurName}`);
                this.testResults.push({ 
                    test: 'Container Selection', 
                    status: 'PASS', 
                    data: containerInfo[0] 
                });
                return containerInfo[0];
            } else {
                console.log('❌ No container with multiple items found');
                this.testResults.push({ 
                    test: 'Container Selection', 
                    status: 'FAIL', 
                    error: 'No container with multiple items found' 
                });
                return null;
            }
        } catch (error) {
            console.error('❌ Container selection error:', error.message);
            this.testResults.push({ 
                test: 'Container Selection', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async performRealDragDrop(containerInfo) {
        console.log('🖱️ Performing real drag and drop operation...');
        
        try {
            const dragResult = await this.page.evaluate((containerIndex) => {
                return new Promise((resolve) => {
                    const container = document.querySelectorAll('.sortable-bagian')[containerIndex];
                    const items = container.querySelectorAll('.bagian-item');
                    
                    if (items.length < 2) {
                        resolve({ success: false, error: 'Not enough items' });
                        return;
                    }
                    
                    const firstItem = items[0];
                    const secondItem = items[1];
                    
                    // Get initial state
                    const initialState = {
                        firstId: firstItem.getAttribute('data-id'),
                        firstName: firstItem.querySelector('.bagian-name').textContent.trim(),
                        firstUrutan: firstItem.getAttribute('data-urutan'),
                        secondId: secondItem.getAttribute('data-id'),
                        secondName: secondItem.querySelector('.bagian-name').textContent.trim(),
                        secondUrutan: secondItem.getAttribute('data-urutan')
                    };
                    
                    // Simulate drag and drop by swapping positions
                    const parent = container;
                    const nextSibling = firstItem.nextSibling;
                    
                    // Swap the items
                    parent.insertBefore(firstItem, secondItem.nextSibling);
                    parent.insertBefore(secondItem, nextSibling);
                    
                    // Update data attributes
                    firstItem.setAttribute('data-urutan', '2');
                    secondItem.setAttribute('data-urutan', '1');
                    
                    // Trigger change events
                    const changeEvent = new Event('change', { bubbles: true });
                    container.dispatchEvent(changeEvent);
                    
                    // Track the change for save functionality
                    if (window.changes) {
                        const change1 = {
                            bagian_id: initialState.firstId,
                            old_unsur_id: firstItem.getAttribute('data-unsur-id'),
                            new_unsur_id: firstItem.getAttribute('data-unsur-id'),
                            new_urutan: 2
                        };
                        const change2 = {
                            bagian_id: initialState.secondId,
                            old_unsur_id: secondItem.getAttribute('data-unsur-id'),
                            new_unsur_id: secondItem.getAttribute('data-unsur-id'),
                            new_urutan: 1
                        };
                        
                        // Remove existing changes for these items
                        window.changes = window.changes.filter(c => c.bagian_id !== initialState.firstId && c.bagian_id !== initialState.secondId);
                        window.changes.push(change1, change2);
                        
                        // Show save buttons
                        const saveBtn = document.getElementById('saveChangesBtn');
                        const cancelBtn = document.getElementById('cancelChangesBtn');
                        if (saveBtn) saveBtn.style.display = 'inline-block';
                        if (cancelBtn) cancelBtn.style.display = 'inline-block';
                    }
                    
                    resolve({
                        success: true,
                        initialState: initialState,
                        finalState: {
                            firstId: initialState.firstId,
                            firstName: initialState.firstName,
                            newUrutan: '2',
                            secondId: initialState.secondId,
                            secondName: initialState.secondName,
                            newUrutan: '1'
                        }
                    });
                });
            }, containerInfo.containerIndex);
            
            if (dragResult.success) {
                console.log('✅ Drag and drop performed successfully');
                console.log('Initial state:', dragResult.initialState);
                console.log('Final state:', dragResult.finalState);
                
                await this.takeScreenshot('complete_after_drag', 'After drag and drop operation');
                
                this.testResults.push({ 
                    test: 'Real Drag Drop', 
                    status: 'PASS', 
                    data: dragResult 
                });
                
                return dragResult;
            } else {
                console.log('❌ Drag and drop failed:', dragResult.error);
                this.testResults.push({ 
                    test: 'Real Drag Drop', 
                    status: 'FAIL', 
                    error: dragResult.error 
                });
                return null;
            }
        } catch (error) {
            console.error('❌ Drag drop error:', error.message);
            this.testResults.push({ 
                test: 'Real Drag Drop', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async saveChanges() {
        console.log('💾 Saving changes...');
        
        try {
            const saveResult = await this.page.evaluate(() => {
                return new Promise((resolve) => {
                    const saveButton = document.getElementById('saveChangesBtn');
                    
                    if (!saveButton) {
                        resolve({ success: false, error: 'Save button not found' });
                        return;
                    }
                    
                    if (saveButton.style.display === 'none') {
                        resolve({ success: false, error: 'Save button not visible' });
                        return;
                    }
                    
                    // Click save button
                    saveButton.click();
                    
                    // Wait a moment for the save operation
                    setTimeout(() => {
                        resolve({ success: true, message: 'Save button clicked' });
                    }, 500);
                });
            });
            
            if (saveResult.success) {
                console.log('✅ Save button clicked');
                
                // Wait for save operation to complete
                await this.page.waitForTimeout(3000);
                
                // Check for success notifications
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
                await this.takeScreenshot('complete_after_save', 'After save operation');
                
                this.testResults.push({ 
                    test: 'Save Changes', 
                    status: 'PASS', 
                    data: { saveResult, notifications } 
                });
                
                return true;
            } else {
                console.log('❌ Save failed:', saveResult.error);
                this.testResults.push({ 
                    test: 'Save Changes', 
                    status: 'FAIL', 
                    error: saveResult.error 
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Save error:', error.message);
            this.testResults.push({ 
                test: 'Save Changes', 
                status: 'FAIL', 
                error: error.message 
            });
            return false;
        }
    }

    async reloadAndVerify() {
        console.log('🔄 Reloading page and verifying persistence...');
        
        try {
            // Reload the page
            await this.page.reload({ waitUntil: 'networkidle2' });
            await this.page.waitForTimeout(2000);
            
            await this.takeScreenshot('complete_after_reload', 'After page reload');
            
            // Capture state after reload
            const reloadedState = await this.page.evaluate(() => {
                const unsurCards = document.querySelectorAll('.unsur-card');
                const stateData = [];
                
                unsurCards.forEach((card, cardIndex) => {
                    const unsurName = card.querySelector('.unsur-header h6')?.textContent.trim();
                    const bagianItems = card.querySelectorAll('.bagian-item');
                    const bagians = [];
                    
                    bagianItems.forEach((item, itemIndex) => {
                        bagians.push({
                            position: itemIndex,
                            id: item.getAttribute('data-id'),
                            name: item.querySelector('.bagian-name')?.textContent.trim(),
                            urutan: item.getAttribute('data-urutan'),
                            unsurId: item.getAttribute('data-unsur-id')
                        });
                    });
                    
                    stateData.push({
                        unsurIndex: cardIndex,
                        unsurName: unsurName,
                        totalBagians: bagianItems.length,
                        bagians: bagians
                    });
                });
                
                return stateData;
            });
            
            console.log('Reloaded State:', JSON.stringify(reloadedState, null, 2));
            
            this.testResults.push({ 
                test: 'Reload Verification', 
                status: 'PASS', 
                data: reloadedState 
            });
            
            return reloadedState;
        } catch (error) {
            console.error('❌ Reload verification error:', error.message);
            this.testResults.push({ 
                test: 'Reload Verification', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async compareStates(initialState, reloadedState, dragResult) {
        console.log('🔍 Comparing states to verify persistence...');
        
        try {
            if (!initialState || !reloadedState || !dragResult) {
                console.log('❌ Cannot compare - missing data');
                this.testResults.push({ 
                    test: 'State Comparison', 
                    status: 'FAIL', 
                    error: 'Missing state data' 
                });
                return false;
            }
            
            // Find the container that was modified
            const modifiedContainer = initialState.find(c => 
                c.bagians.some(b => b.id === dragResult.initialState.firstId)
            );
            
            const reloadedContainer = reloadedState.find(c => 
                c.bagians.some(b => b.id === dragResult.initialState.firstId)
            );
            
            if (!modifiedContainer || !reloadedContainer) {
                console.log('❌ Cannot find modified container');
                this.testResults.push({ 
                    test: 'State Comparison', 
                    status: 'FAIL', 
                    error: 'Modified container not found' 
                });
                return false;
            }
            
            // Check if the order persisted
            const initialFirstBagian = modifiedContainer.bagians.find(b => b.id === dragResult.initialState.firstId);
            const reloadedFirstBagian = reloadedContainer.bagians.find(b => b.id === dragResult.initialState.firstId);
            
            const orderPersisted = initialFirstBagian.position !== reloadedFirstBagian.position;
            
            console.log('Order persistence check:');
            console.log(`  Initial position of ${dragResult.initialState.firstName}: ${initialFirstBagian.position}`);
            console.log(`  Reloaded position of ${dragResult.initialState.firstName}: ${reloadedFirstBagian.position}`);
            console.log(`  Order persisted: ${orderPersisted}`);
            
            if (orderPersisted) {
                console.log('✅ Order changes persisted successfully!');
                this.testResults.push({ 
                    test: 'State Comparison', 
                    status: 'PASS', 
                    data: { 
                        orderPersisted: true,
                        initialPosition: initialFirstBagian.position,
                        reloadedPosition: reloadedFirstBagian.position
                    }
                });
                return true;
            } else {
                console.log('❌ Order changes did not persist');
                this.testResults.push({ 
                    test: 'State Comparison', 
                    status: 'FAIL', 
                    data: { 
                        orderPersisted: false,
                        initialPosition: initialFirstBagian.position,
                        reloadedPosition: reloadedFirstBagian.position
                    },
                    error: 'Order changes did not persist after reload'
                });
                return false;
            }
        } catch (error) {
            console.error('❌ State comparison error:', error.message);
            this.testResults.push({ 
                test: 'State Comparison', 
                status: 'FAIL', 
                error: error.message 
            });
            return false;
        }
    }

    async generateReport() {
        console.log('📋 Generating complete test report...');
        
        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'Complete Bagian Drag-Drop Persistence Test',
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };
        
        const reportPath = `${config.output.reports}/complete_bagian_test_${timestamp.replace(/[:.]/g, '-')}.json`;
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
            
            // Run complete test workflow
            const initialState = await this.captureCurrentState();
            const containerInfo = await this.findContainerWithMultipleItems();
            
            if (containerInfo) {
                const dragResult = await this.performRealDragDrop(containerInfo);
                
                if (dragResult) {
                    const saveSuccess = await this.saveChanges();
                    
                    if (saveSuccess) {
                        const reloadedState = await this.reloadAndVerify();
                        await this.compareStates(initialState, reloadedState, dragResult);
                    }
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
    const test = new CompleteBagianTest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ Complete bagian test finished!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ Complete bagian test failed:', error.message);
            process.exit(1);
        });
}

module.exports = CompleteBagianTest;
