/**
 * Test Unsur Delete Functionality
 * SPRIN Application Testing
 */

const puppeteer = require('puppeteer');
const config = require('../config');

class UnsurDeleteTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.screenshotDir = config.output.screenshots;
    }

    async init() {
        console.log('🗑️ Starting Unsur Delete Test...');
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
                await this.takeScreenshot('unsur_initial_load', 'Initial unsur page load');
                return true;
            }
            return false;
        } catch (error) {
            console.error('❌ Navigation error:', error.message);
            return false;
        }
    }

    async testDeleteUnsurWithBagian() {
        console.log('🗑️ Testing delete unsur with bagian...');
        
        try {
            const deleteTest = await this.page.evaluate(async () => {
                try {
                    // Test delete API for unsur with bagian (e.g., UNSUR PEMBANTU PIMPINAN)
                    const response = await fetch('./unsur.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'delete_unsur',
                            id: '2' // UNSUR PEMBANTU PIMPINAN
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
                        data: data
                    };
                } catch (error) {
                    return {
                        success: false,
                        error: error.message
                    };
                }
            });
            
            console.log('Delete test result:', JSON.stringify(deleteTest, null, 2));
            
            if (deleteTest.success && !deleteTest.data.success && deleteTest.data.details) {
                console.log('✅ Delete unsur with bagian properly blocked');
                console.log('Error message:', deleteTest.data.message);
                console.log('Bagian count:', deleteTest.data.details.bagian_count);
                console.log('Bagian list:', deleteTest.data.details.bagian_list);
                
                this.testResults.push({ 
                    test: 'Delete Unsur With Bagian', 
                    status: 'PASS', 
                    data: deleteTest 
                });
                return deleteTest.data;
            } else {
                console.log('❌ Delete test unexpected result');
                this.testResults.push({ 
                    test: 'Delete Unsur With Bagian', 
                    status: 'FAIL', 
                    data: deleteTest 
                });
                return null;
            }
        } catch (error) {
            console.error('❌ Delete test error:', error.message);
            this.testResults.push({ 
                test: 'Delete Unsur With Bagian', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async testForceDeleteUnsur() {
        console.log('⚡ Testing force delete unsur...');
        
        try {
            const forceDeleteTest = await this.page.evaluate(async () => {
                try {
                    // Test force delete API
                    const formData = new FormData();
                    formData.append('action', 'force_delete_unsur');
                    formData.append('id', '2'); // UNSUR PEMBANTU PIMPINAN
                    formData.append('reassign_to_unsur_id', '1'); // Reassign to UNSUR PIMPINAN
                    
                    const response = await fetch('./unsur.php', {
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
                        data: data
                    };
                } catch (error) {
                    return {
                        success: false,
                        error: error.message
                    };
                }
            });
            
            console.log('Force delete test result:', JSON.stringify(forceDeleteTest, null, 2));
            
            if (forceDeleteTest.success && forceDeleteTest.data.success) {
                console.log('✅ Force delete working correctly');
                console.log('Success message:', forceDeleteTest.data.message);
                
                this.testResults.push({ 
                    test: 'Force Delete Unsur', 
                    status: 'PASS', 
                    data: forceDeleteTest 
                });
                return true;
            } else {
                console.log('❌ Force delete failed');
                this.testResults.push({ 
                    test: 'Force Delete Unsur', 
                    status: 'FAIL', 
                    data: forceDeleteTest 
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Force delete test error:', error.message);
            this.testResults.push({ 
                test: 'Force Delete Unsur', 
                status: 'FAIL', 
                error: error.message 
            });
            return false;
        }
    }

    async testEmptyUnsurDelete() {
        console.log('🗑️ Testing delete empty unsur...');
        
        try {
            // First create a test unsur
            const createTest = await this.page.evaluate(async () => {
                try {
                    const formData = new FormData();
                    formData.append('action', 'create_unsur');
                    formData.append('nama_unsur', 'TEST UNSUR DELETE');
                    formData.append('urutan', '999');
                    formData.append('deskripsi', 'Test unsur for deletion');
                    
                    const response = await fetch('./unsur.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    return data;
                } catch (error) {
                    return { success: false, error: error.message };
                }
            });
            
            if (createTest.success) {
                console.log('✅ Test unsur created');
                
                // Now try to delete it
                const deleteTest = await this.page.evaluate(async (unsurName) => {
                    try {
                        // Get the ID of the created unsur
                        const listResponse = await fetch('./unsur.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                action: 'get_unsur_list'
                            })
                        });
                        
                        const listData = await listResponse.json();
                        const testUnsur = listData.data.find(u => u.nama_unsur === unsurName);
                        
                        if (!testUnsur) {
                            return { success: false, error: 'Test unsur not found' };
                        }
                        
                        // Delete it
                        const deleteResponse = await fetch('./unsur.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                action: 'delete_unsur',
                                id: testUnsur.id
                            })
                        });
                        
                        const deleteData = await deleteResponse.json();
                        return deleteData;
                    } catch (error) {
                        return { success: false, error: error.message };
                    }
                }, 'TEST UNSUR DELETE');
                
                console.log('Empty unsur delete result:', JSON.stringify(deleteTest, null, 2));
                
                if (deleteTest.success) {
                    console.log('✅ Empty unsur deleted successfully');
                    this.testResults.push({ 
                        test: 'Empty Unsur Delete', 
                        status: 'PASS', 
                        data: deleteTest 
                    });
                    return true;
                } else {
                    console.log('❌ Empty unsur delete failed');
                    this.testResults.push({ 
                        test: 'Empty Unsur Delete', 
                        status: 'FAIL', 
                        data: deleteTest 
                    });
                    return false;
                }
            } else {
                console.log('❌ Failed to create test unsur');
                this.testResults.push({ 
                    test: 'Empty Unsur Delete', 
                    status: 'FAIL', 
                    error: 'Failed to create test unsur'
                });
                return false;
            }
        } catch (error) {
            console.error('❌ Empty unsur delete test error:', error.message);
            this.testResults.push({ 
                test: 'Empty Unsur Delete', 
                status: 'FAIL', 
                error: error.message 
            });
            return false;
        }
    }

    async generateReport() {
        console.log('📋 Generating unsur delete test report...');
        
        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'Unsur Delete Functionality Test',
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };
        
        const reportPath = `${config.output.reports}/unsur_delete_test_${timestamp.replace(/[:.]/g, '-')}.json`;
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
            
            // Run unsur delete tests
            await this.testDeleteUnsurWithBagian();
            await this.testForceDeleteUnsur();
            await this.testEmptyUnsurDelete();
            
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
    const test = new UnsurDeleteTest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ Unsur delete test completed!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ Unsur delete test failed:', error.message);
            process.exit(1);
        });
}

module.exports = UnsurDeleteTest;
