/**
 * Test Delete UNSUR LAINNYA
 * SPRIN Application Testing
 */

const puppeteer = require('puppeteer');
const config = require('../config');

class DeleteUnsurLainnyaTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.screenshotDir = config.output.screenshots;
    }

    async init() {
        console.log('🗑️ Starting Delete UNSUR LAINNYA Test...');
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
                await this.takeScreenshot('delete_lainnya_initial', 'Initial unsur page for delete test');
                return true;
            }
            return false;
        } catch (error) {
            console.error('❌ Navigation error:', error.message);
            return false;
        }
    }

    async findUnsurLainnyaId() {
        console.log('🔍 Finding UNSUR LAINNYA ID...');
        
        try {
            const unsurInfo = await this.page.evaluate(async () => {
                try {
                    const response = await fetch('./unsur.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'get_unsur_list'
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success && data.data) {
                        const lainnyaUnsur = data.data.find(u => u.nama_unsur === 'UNSUR LAINNYA');
                        return {
                            success: true,
                            unsur: lainnyaUnsur,
                            allUnsurs: data.data
                        };
                    } else {
                        return { success: false, error: 'Failed to get unsur list' };
                    }
                } catch (error) {
                    return { success: false, error: error.message };
                }
            });
            
            console.log('Unsur info:', JSON.stringify(unsurInfo, null, 2));
            
            if (unsurInfo.success && unsurInfo.unsur) {
                console.log(`✅ Found UNSUR LAINNYA with ID: ${unsurInfo.unsur.id}`);
                this.testResults.push({ 
                    test: 'Find Unsur Lainnya', 
                    status: 'PASS', 
                    data: unsurInfo 
                });
                return unsurInfo.unsur;
            } else {
                console.log('❌ UNSUR LAINNYA not found');
                this.testResults.push({ 
                    test: 'Find Unsur Lainnya', 
                    status: 'FAIL', 
                    error: 'UNSUR LAINNYA not found' 
                });
                return null;
            }
        } catch (error) {
            console.error('❌ Find error:', error.message);
            this.testResults.push({ 
                test: 'Find Unsur Lainnya', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async testDeleteUnsurLainnya(unsurLainnya) {
        console.log('🗑️ Testing delete UNSUR LAINNYA...');
        
        try {
            const deleteTest = await this.page.evaluate(async (unsurData) => {
                try {
                    // Test delete API
                    const response = await fetch('./unsur.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'delete_unsur',
                            id: unsurData.id
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
            }, unsurLainnya);
            
            console.log('Delete UNSUR LAINNYA result:', JSON.stringify(deleteTest, null, 2));
            
            if (deleteTest.success) {
                if (deleteTest.data.success) {
                    console.log('✅ UNSUR LAINNYA deleted successfully');
                    this.testResults.push({ 
                        test: 'Delete Unsur Lainnya', 
                        status: 'PASS', 
                        data: deleteTest 
                    });
                } else {
                    console.log('❌ Delete rejected by system');
                    console.log('Error message:', deleteTest.data.message);
                    
                    if (deleteTest.data.details) {
                        console.log('Details:', deleteTest.data.details);
                    }
                    
                    this.testResults.push({ 
                        test: 'Delete Unsur Lainnya', 
                        status: 'FAIL', 
                        data: deleteTest 
                    });
                }
            } else {
                console.log('❌ Delete request failed');
                this.testResults.push({ 
                    test: 'Delete Unsur Lainnya', 
                    status: 'FAIL', 
                    data: deleteTest 
                });
            }
            
            return deleteTest;
        } catch (error) {
            console.error('❌ Delete test error:', error.message);
            this.testResults.push({ 
                test: 'Delete Unsur Lainnya', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async investigateWhyRejected() {
        console.log('🔍 Investigating why delete was rejected...');
        
        try {
            const investigation = await this.page.evaluate(async () => {
                try {
                    // Check if there are any bagian assigned to UNSUR LAINNYA
                    const bagianResponse = await fetch('./bagian.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'get_bagian_list'
                        })
                    });
                    
                    const bagianText = await bagianResponse.text();
                    
                    let bagianData;
                    try {
                        bagianData = JSON.parse(bagianText);
                    } catch (e) {
                        bagianData = { rawResponse: bagianText, parseError: e.message };
                    }
                    
                    // Check personil assignments
                    const personilResponse = await fetch('./api/personil_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'get_personil_list'
                        })
                    });
                    
                    const personilText = await personilResponse.text();
                    
                    let personilData;
                    try {
                        personilData = JSON.parse(personilText);
                    } catch (e) {
                        personilData = { rawResponse: personilText, parseError: e.message };
                    }
                    
                    // Check schedules
                    const scheduleResponse = await fetch('./api/calendar_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'get_schedules'
                        })
                    });
                    
                    const scheduleText = await scheduleResponse.text();
                    
                    let scheduleData;
                    try {
                        scheduleData = JSON.parse(scheduleText);
                    } catch (e) {
                        scheduleData = { rawResponse: scheduleText, parseError: e.message };
                    }
                    
                    return {
                        bagianData: bagianData,
                        personilData: personilData,
                        scheduleData: scheduleData
                    };
                    
                } catch (error) {
                    return {
                        success: false,
                        error: error.message
                    };
                }
            });
            
            console.log('Investigation result:', JSON.stringify(investigation, null, 2));
            
            this.testResults.push({ 
                test: 'Delete Rejection Investigation', 
                status: 'PASS', 
                data: investigation 
            });
            
            return investigation;
        } catch (error) {
            console.error('❌ Investigation error:', error.message);
            this.testResults.push({ 
                test: 'Delete Rejection Investigation', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async generateReport() {
        console.log('📋 Generating delete UNSUR LAINNYA test report...');
        
        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'Delete UNSUR LAINNYA Test',
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };
        
        const reportPath = `${config.output.reports}/delete_unsur_lainnya_test_${timestamp.replace(/[:.]/g, '-')}.json`;
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
            const unsurLainnya = await this.findUnsurLainnyaId();
            
            if (unsurLainnya) {
                const deleteResult = await this.testDeleteUnsurLainnya(unsurLainnya);
                
                if (!deleteResult || !deleteResult.data.success) {
                    await this.investigateWhyRejected();
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
    const test = new DeleteUnsurLainnyaTest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ Delete UNSUR LAINNYA test completed!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ Delete UNSUR LAINNYA test failed:', error.message);
            process.exit(1);
        });
}

module.exports = DeleteUnsurLainnyaTest;
