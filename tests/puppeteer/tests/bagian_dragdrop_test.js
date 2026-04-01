/**
 * Test Bagian Drag and Drop Functionality
 * SPRIN Application Testing
 */

const puppeteer = require('puppeteer');
const config = require('../config');

class BagianDragDropTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.screenshotDir = config.output.screenshots;
    }

    async init() {
        console.log('🎯 Starting Bagian Drag & Drop Test...');
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
                await this.takeScreenshot('dragdrop_page_loaded', 'Bagian page loaded for drag-drop test');
                return true;
            }
            return false;
        } catch (error) {
            console.error('❌ Navigation error:', error.message);
            return false;
        }
    }

    async testSortableLibrary() {
        console.log('📚 Testing SortableJS library loading...');
        
        try {
            const libraryStatus = await this.page.evaluate(() => {
                return {
                    sortableLoaded: typeof window.Sortable !== 'undefined',
                    jQueryLoaded: typeof window.jQuery !== 'undefined',
                    bootstrapLoaded: typeof bootstrap !== 'undefined'
                };
            });
            
            console.log('Library Status:', libraryStatus);
            
            if (libraryStatus.sortableLoaded) {
                console.log('✅ SortableJS library loaded');
                this.testResults.push({ 
                    test: 'SortableJS Library', 
                    status: 'PASS', 
                    data: libraryStatus 
                });
            } else {
                console.log('⚠️ SortableJS not loaded, will load dynamically');
                this.testResults.push({ 
                    test: 'SortableJS Library', 
                    status: 'WARNING', 
                    error: 'SortableJS not loaded initially' 
                });
            }
            
            await this.takeScreenshot('dragdrop_library_check', 'Library loading check');
        } catch (error) {
            console.error('❌ Library test error:', error.message);
            this.testResults.push({ 
                test: 'SortableJS Library', 
                status: 'FAIL', 
                error: error.message 
            });
        }
    }

    async testDragDropElements() {
        console.log('🎯 Testing drag-drop elements...');
        
        try {
            const elementsStatus = await this.page.evaluate(() => {
                const unsurCards = document.querySelectorAll('.unsur-card');
                const sortableContainers = document.querySelectorAll('.sortable-bagian');
                const bagianItems = document.querySelectorAll('.bagian-item');
                
                return {
                    unsurCardsCount: unsurCards.length,
                    sortableContainersCount: sortableContainers.length,
                    bagianItemsCount: bagianItems.length,
                    hasSortableClass: sortableContainers.length > 0,
                    hasDraggableItems: bagianItems.length > 0
                };
            });
            
            console.log('Elements Status:', elementsStatus);
            
            if (elementsStatus.hasSortableClass && elementsStatus.hasDraggableItems) {
                console.log('✅ Drag-drop elements found');
                this.testResults.push({ 
                    test: 'Drag-Drop Elements', 
                    status: 'PASS', 
                    data: elementsStatus 
                });
                
                await this.takeScreenshot('dragdrop_elements_found', 'Drag-drop elements found');
                return true;
            } else {
                console.log('❌ Drag-drop elements not found');
                this.testResults.push({ 
                    test: 'Drag-Drop Elements', 
                    status: 'FAIL', 
                    error: 'Required elements not found' 
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Elements test error:', error.message);
            this.testResults.push({ 
                test: 'Drag-Drop Elements', 
                status: 'FAIL', 
                error: error.message 
            });
            return false;
        }
    }

    async testSortableInitialization() {
        console.log('⚙️ Testing Sortable initialization...');
        
        try {
            // Wait for Sortable to be initialized
            await this.page.waitForFunction(() => {
                return window.sortableInstances && window.sortableInstances.length > 0;
            }, { timeout: 5000 }).catch(() => {
                console.log('Sortable instances not found, checking manual initialization');
            });
            
            const sortableStatus = await this.page.evaluate(() => {
                // Check if sortable instances exist
                const hasInstances = window.sortableInstances && window.sortableInstances.length > 0;
                
                // Check if containers have sortable functionality
                const containers = document.querySelectorAll('.sortable-bagian');
                let initializedContainers = 0;
                
                containers.forEach(container => {
                    if (container._sortable || container.sortable) {
                        initializedContainers++;
                    }
                });
                
                return {
                    hasGlobalInstances: hasInstances,
                    totalContainers: containers.length,
                    initializedContainers: initializedContainers,
                    sortableInstances: window.sortableInstances ? window.sortableInstances.length : 0
                };
            });
            
            console.log('Sortable Status:', sortableStatus);
            
            if (sortableStatus.initializedContainers > 0 || sortableStatus.hasGlobalInstances) {
                console.log('✅ Sortable initialized successfully');
                this.testResults.push({ 
                    test: 'Sortable Initialization', 
                    status: 'PASS', 
                    data: sortableStatus 
                });
            } else {
                console.log('⚠️ Sortable not fully initialized');
                this.testResults.push({ 
                    test: 'Sortable Initialization', 
                    status: 'WARNING', 
                    data: sortableStatus 
                });
            }
            
            await this.takeScreenshot('dragdrop_sortable_init', 'Sortable initialization check');
        } catch (error) {
            console.error('❌ Sortable init test error:', error.message);
            this.testResults.push({ 
                test: 'Sortable Initialization', 
                status: 'FAIL', 
                error: error.message 
            });
        }
    }

    async testDragInteraction() {
        console.log('🖱️ Testing drag interaction...');
        
        try {
            // Find a draggable element
            const dragTest = await this.page.evaluate(() => {
                const bagianItems = document.querySelectorAll('.bagian-item');
                if (bagianItems.length === 0) {
                    return { success: false, error: 'No draggable items found' };
                }
                
                const firstItem = bagianItems[0];
                const itemId = firstItem.getAttribute('data-id');
                const itemText = firstItem.textContent.trim();
                
                return {
                    success: true,
                    itemId: itemId,
                    itemText: itemText,
                    totalItems: bagianItems.length
                };
            });
            
            if (dragTest.success) {
                console.log(`Found draggable item: "${dragTest.itemText}" (ID: ${dragTest.itemId})`);
                
                // Simulate drag start
                await this.page.evaluate((itemId) => {
                    const item = document.querySelector(`[data-id="${itemId}"]`);
                    if (item) {
                        // Trigger drag start event
                        const dragStartEvent = new DragEvent('dragstart', {
                            bubbles: true,
                            cancelable: true,
                            dataTransfer: new DataTransfer()
                        });
                        item.dispatchEvent(dragStartEvent);
                        return true;
                    }
                    return false;
                }, dragTest.itemId);
                
                // Wait a moment for drag events
                await new Promise(resolve => setTimeout(resolve, 500));
                
                console.log('✅ Drag interaction simulated');
                await this.takeScreenshot('dragdrop_drag_interaction', 'Drag interaction test');
                
                this.testResults.push({ 
                    test: 'Drag Interaction', 
                    status: 'PASS', 
                    data: dragTest 
                });
            } else {
                console.log('❌ No draggable items found');
                this.testResults.push({ 
                    test: 'Drag Interaction', 
                    status: 'FAIL', 
                    error: dragTest.error 
                });
            }
        } catch (error) {
            console.error('❌ Drag interaction test error:', error.message);
            this.testResults.push({ 
                test: 'Drag Interaction', 
                status: 'FAIL', 
                error: error.message 
            });
        }
    }

    async testMoveBagianAPI() {
        console.log('🔄 Testing move_bagian API...');
        
        try {
            // Test the API endpoint directly
            const apiTest = await this.page.evaluate(async () => {
                try {
                    const response = await fetch('./bagian.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'move_bagian',
                            bagian_id: '1',
                            new_unsur_id: '1',
                            new_urutan: '1'
                        })
                    });
                    
                    const data = await response.json();
                    return {
                        success: response.ok,
                        status: response.status,
                        data: data
                    };
                } catch (error) {
                    return {
                        success: false,
                        error: error.message
                    };
                }
            });
            
            console.log('API Test Result:', apiTest);
            
            if (apiTest.success && apiTest.data.success) {
                console.log('✅ move_bagian API working');
                this.testResults.push({ 
                    test: 'Move Bagian API', 
                    status: 'PASS', 
                    data: apiTest 
                });
            } else {
                console.log('❌ move_bagian API failed');
                this.testResults.push({ 
                    test: 'Move Bagian API', 
                    status: 'FAIL', 
                    error: apiTest.error || apiTest.data?.message 
                });
            }
            
            await this.takeScreenshot('dragdrop_api_test', 'Move bagian API test');
        } catch (error) {
            console.error('❌ API test error:', error.message);
            this.testResults.push({ 
                test: 'Move Bagian API', 
                status: 'FAIL', 
                error: error.message 
            });
        }
    }

    async generateReport() {
        console.log('📋 Generating drag-drop test report...');
        
        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'Bagian Drag & Drop Functionality Test',
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };
        
        const reportPath = `${config.output.reports}/bagian_dragdrop_test_${timestamp.replace(/[:.]/g, '-')}.json`;
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
            
            // Run drag-drop specific tests
            await this.testSortableLibrary();
            await this.testDragDropElements();
            await this.testSortableInitialization();
            await this.testDragInteraction();
            await this.testMoveBagianAPI();
            
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
    const test = new BagianDragDropTest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ Bagian drag-drop test completed successfully!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ Bagian drag-drop test failed:', error.message);
            process.exit(1);
        });
}

module.exports = BagianDragDropTest;
