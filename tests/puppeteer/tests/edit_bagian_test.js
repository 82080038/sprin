/**
 * Test Edit Bagian Functionality
 * SPRIN Application Testing
 */

const puppeteer = require('puppeteer');
const config = require('../config');

class EditBagianTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.screenshotDir = config.output.screenshots;
    }

    async init() {
        console.log('✏️ Starting Edit Bagian Test...');
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
                await this.takeScreenshot('edit_initial_load', 'Initial bagian page load');
                return true;
            }
            return false;
        } catch (error) {
            console.error('❌ Navigation error:', error.message);
            return false;
        }
    }

    async findEditButtons() {
        console.log('🔍 Finding edit buttons...');
        
        try {
            const editButtons = await this.page.evaluate(() => {
                const buttons = document.querySelectorAll('button[onclick*="editBagian"]');
                const buttonInfo = [];
                
                buttons.forEach((button, index) => {
                    const onclick = button.getAttribute('onclick');
                    const bagianId = onclick.match(/editBagian\((\d+)\)/);
                    const bagianName = button.closest('.bagian-item')?.querySelector('.bagian-name')?.textContent.trim();
                    
                    buttonInfo.push({
                        index: index,
                        onclick: onclick,
                        bagianId: bagianId ? bagianId[1] : null,
                        bagianName: bagianName || 'Unknown',
                        visible: button.offsetParent !== null
                    });
                });
                
                return buttonInfo;
            });
            
            console.log('Edit buttons found:', editButtons);
            
            if (editButtons.length > 0) {
                console.log(`✅ Found ${editButtons.length} edit buttons`);
                this.testResults.push({ 
                    test: 'Edit Buttons Found', 
                    status: 'PASS', 
                    data: editButtons 
                });
                return editButtons;
            } else {
                console.log('❌ No edit buttons found');
                this.testResults.push({ 
                    test: 'Edit Buttons Found', 
                    status: 'FAIL', 
                    error: 'No edit buttons found' 
                });
                return [];
            }
        } catch (error) {
            console.error('❌ Edit buttons search error:', error.message);
            this.testResults.push({ 
                test: 'Edit Buttons Found', 
                status: 'FAIL', 
                error: error.message 
            });
            return [];
        }
    }

    async clickEditButton(buttonInfo) {
        console.log('🖱️ Clicking edit button...');
        
        try {
            const clickResult = await this.page.evaluate((buttonIndex) => {
                const buttons = document.querySelectorAll('button[onclick*="editBagian"]');
                const button = buttons[buttonIndex];
                
                if (!button) {
                    return { success: false, error: 'Button not found' };
                }
                
                // Click the button
                button.click();
                return { success: true, message: 'Edit button clicked' };
            }, buttonInfo.index);
            
            if (clickResult.success) {
                console.log('✅ Edit button clicked');
                
                // Wait for modal to appear
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                await this.takeScreenshot('edit_after_click', 'After edit button click');
                
                this.testResults.push({ 
                    test: 'Edit Button Click', 
                    status: 'PASS', 
                    data: clickResult 
                });
                
                return true;
            } else {
                console.log('❌ Edit button click failed:', clickResult.error);
                this.testResults.push({ 
                    test: 'Edit Button Click', 
                    status: 'FAIL', 
                    error: clickResult.error 
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Edit button click error:', error.message);
            this.testResults.push({ 
                test: 'Edit Button Click', 
                status: 'FAIL', 
                error: error.message 
            });
            return false;
        }
    }

    async checkModalState() {
        console.log('🪟 Checking modal state...');
        
        try {
            const modalState = await this.page.evaluate(() => {
                const modal = document.getElementById('bagianModal');
                const modalTitle = document.getElementById('modalTitle');
                const formAction = document.getElementById('formAction');
                const formId = document.getElementById('formId');
                const namaBagian = document.getElementById('nama_bagian');
                const idUnsur = document.getElementById('id_unsur');
                const type = document.getElementById('type');
                
                return {
                    modalExists: !!modal,
                    modalVisible: modal ? modal.classList.contains('show') : false,
                    modalTitle: modalTitle ? modalTitle.textContent.trim() : null,
                    formAction: formAction ? formAction.value : null,
                    formId: formId ? formId.value : null,
                    namaBagian: namaBagian ? namaBagian.value : null,
                    idUnsur: idUnsur ? idUnsur.value : null,
                    type: type ? type.value : null
                };
            });
            
            console.log('Modal state:', modalState);
            
            if (modalState.modalExists && modalState.modalVisible) {
                console.log('✅ Modal is visible and populated');
                this.testResults.push({ 
                    test: 'Modal State', 
                    status: 'PASS', 
                    data: modalState 
                });
                return modalState;
            } else {
                console.log('❌ Modal not visible or not populated');
                this.testResults.push({ 
                    test: 'Modal State', 
                    status: 'FAIL', 
                    error: 'Modal not visible or not populated' 
                });
                return null;
            }
        } catch (error) {
            console.error('❌ Modal state check error:', error.message);
            this.testResults.push({ 
                test: 'Modal State', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async testEditBagianAPI() {
        console.log('🔌 Testing edit bagian API...');
        
        try {
            const apiTest = await this.page.evaluate(async () => {
                try {
                    // Test get_bagian_detail API
                    const response = await fetch('./bagian.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'get_bagian_detail',
                            id: '5' // BAG LOG
                        })
                    });
                    
                    const responseText = await response.text();
                    
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
            
            console.log('Edit API test result:', JSON.stringify(apiTest, null, 2));
            
            if (apiTest.success && apiTest.data.success) {
                console.log('✅ Edit bagian API working');
                this.testResults.push({ 
                    test: 'Edit Bagian API', 
                    status: 'PASS', 
                    data: apiTest 
                });
                return true;
            } else {
                console.log('❌ Edit bagian API failed');
                this.testResults.push({ 
                    test: 'Edit Bagian API', 
                    status: 'FAIL', 
                    data: apiTest 
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Edit API test error:', error.message);
            this.testResults.push({ 
                test: 'Edit Bagian API', 
                status: 'FAIL', 
                error: error.message 
            });
            return false;
        }
    }

    async testUpdateBagianAPI() {
        console.log('🔄 Testing update bagian API...');
        
        try {
            const updateTest = await this.page.evaluate(async () => {
                try {
                    // Test update_bagian API
                    const formData = new FormData();
                    formData.append('action', 'update_bagian');
                    formData.append('id', '5');
                    formData.append('nama_bagian', 'BAG LOG - TEST EDIT');
                    formData.append('id_unsur', '2');
                    formData.append('type', 'BAG');
                    
                    const response = await fetch('./bagian.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const responseText = await response.text();
                    
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
            
            console.log('Update API test result:', JSON.stringify(updateTest, null, 2));
            
            if (updateTest.success && updateTest.data.success) {
                console.log('✅ Update bagian API working');
                this.testResults.push({ 
                    test: 'Update Bagian API', 
                    status: 'PASS', 
                    data: updateTest 
                });
                return true;
            } else {
                console.log('❌ Update bagian API failed');
                console.log('Error details:', updateTest.data);
                this.testResults.push({ 
                    test: 'Update Bagian API', 
                    status: 'FAIL', 
                    data: updateTest 
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Update API test error:', error.message);
            this.testResults.push({ 
                test: 'Update Bagian API', 
                status: 'FAIL', 
                error: error.message 
            });
            return false;
        }
    }

    async generateReport() {
        console.log('📋 Generating edit bagian test report...');
        
        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'Edit Bagian Functionality Test',
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };
        
        const reportPath = `${config.output.reports}/edit_bagian_test_${timestamp.replace(/[:.]/g, '-')}.json`;
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
            
            // Run edit bagian tests
            const editButtons = await this.findEditButtons();
            
            if (editButtons.length > 0) {
                const firstButton = editButtons[0];
                await this.clickEditButton(firstButton);
                await this.checkModalState();
            }
            
            await this.testEditBagianAPI();
            await this.testUpdateBagianAPI();
            
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
    const test = new EditBagianTest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ Edit bagian test completed!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ Edit bagian test failed:', error.message);
            process.exit(1);
        });
}

module.exports = EditBagianTest;
