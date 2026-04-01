/**
 * Test Bagian in "UNSUR LAINNYA"
 * SPRIN Application Testing
 */

const puppeteer = require('puppeteer');
const config = require('../config');

class UnsurLainnyaBagianTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.screenshotDir = config.output.screenshots;
    }

    async init() {
        console.log('🔍 Starting Unsur Lainnya Bagian Test...');
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
                await this.takeScreenshot('lainnya_initial_load', 'Initial bagian page load');
                return true;
            }
            return false;
        } catch (error) {
            console.error('❌ Navigation error:', error.message);
            return false;
        }
    }

    async checkUnsurLainnyaBagian() {
        console.log('🔍 Checking bagian in "UNSUR LAINNYA"...');
        
        try {
            const bagianCheck = await this.page.evaluate(() => {
                const unsurCards = document.querySelectorAll('.unsur-card');
                const results = [];
                
                unsurCards.forEach((card, index) => {
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
                    
                    results.push({
                        unsurIndex: index,
                        unsurName: unsurName,
                        totalBagians: bagianItems.length,
                        bagians: bagians
                    });
                });
                
                return results;
            });
            
            console.log('Bagian distribution:', JSON.stringify(bagianCheck, null, 2));
            
            // Find "UNSUR LAINNYA"
            const lainnyaData = bagianCheck.find(u => u.unsurName === 'UNSUR LAINNYA');
            
            if (lainnyaData) {
                console.log(`✅ Found "UNSUR LAINNYA" with ${lainnyaData.totalBagians} bagian`);
                
                if (lainnyaData.totalBagians > 0) {
                    console.log('Bagian in UNSUR LAINNYA:');
                    lainnyaData.bagians.forEach((bagian, index) => {
                        console.log(`  ${index + 1}. ${bagian.name} (ID: ${bagian.id})`);
                    });
                    
                    this.testResults.push({ 
                        test: 'Unsur Lainnya Bagian Check', 
                        status: 'PASS', 
                        data: lainnyaData 
                    });
                } else {
                    console.log('⚠️ UNSUR LAINNYA exists but has no bagian');
                    
                    this.testResults.push({ 
                        test: 'Unsur Lainnya Bagian Check', 
                        status: 'WARNING', 
                        data: { message: 'UNSUR LAINNYA exists but has no bagian', data: lainnyaData }
                    });
                }
                
                return lainnyaData;
            } else {
                console.log('❌ UNSUR LAINNYA not found in the page');
                
                this.testResults.push({ 
                    test: 'Unsur Lainnya Bagian Check', 
                    status: 'FAIL', 
                    error: 'UNSUR LAINNYA not found'
                });
                return null;
            }
        } catch (error) {
            console.error('❌ Check error:', error.message);
            this.testResults.push({ 
                test: 'Unsur Lainnya Bagian Check', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async checkDatabaseUnsurLainnya() {
        console.log('🔍 Checking database for UNSUR LAINNYA bagian...');
        
        try {
            const dbCheck = await this.page.evaluate(async () => {
                try {
                    // Get UNSUR LAINNYA ID
                    const unsurResponse = await fetch('./bagian.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'get_unsur_list'
                        })
                    });
                    
                    const unsurData = await unsurResponse.json();
                    
                    if (!unsurData.success) {
                        return { success: false, error: 'Failed to get unsur list' };
                    }
                    
                    const lainnyaUnsur = unsurData.data.find(u => u.nama_unsur === 'UNSUR LAINNYA');
                    
                    if (!lainnyaUnsur) {
                        return { success: false, error: 'UNSUR LAINNYA not found in database' };
                    }
                    
                    // Get bagian for UNSUR LAINNYA
                    const bagianResponse = await fetch('./bagian.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'get_bagian_list'
                        })
                    });
                    
                    const bagianData = await bagianResponse.json();
                    
                    if (!bagianData.success) {
                        return { success: false, error: 'Failed to get bagian list' };
                    }
                    
                    const lainnyaBagians = bagianData.data.filter(b => b.id_unsur == lainnyaUnsur.id);
                    
                    return {
                        success: true,
                        unsurLainnya: {
                            id: lainnyaUnsur.id,
                            nama_unsur: lainnyaUnsur.nama_unsur,
                            urutan: lainnyaUnsur.urutan
                        },
                        bagians: lainnyaBagians.map(b => ({
                            id: b.id,
                            nama_bagian: b.nama_bagian,
                            urutan: b.urutan,
                            kode_bagian: b.kode_bagian
                        })),
                        totalBagians: lainnyaBagians.length
                    };
                    
                } catch (error) {
                    return { success: false, error: error.message };
                }
            });
            
            console.log('Database check result:', JSON.stringify(dbCheck, null, 2));
            
            if (dbCheck.success) {
                console.log(`✅ Database check: UNSUR LAINNYA has ${dbCheck.totalBagians} bagian`);
                
                if (dbCheck.totalBagians > 0) {
                    console.log('Bagian from database:');
                    dbCheck.bagians.forEach((bagian, index) => {
                        console.log(`  ${index + 1}. ${bagian.nama_bagian} (${bagian.kode_bagian}) - ID: ${bagian.id}`);
                    });
                    
                    this.testResults.push({ 
                        test: 'Database Unsur Lainnya Check', 
                        status: 'PASS', 
                        data: dbCheck 
                    });
                } else {
                    console.log('⚠️ Database shows UNSUR LAINNYA has no bagian');
                    
                    this.testResults.push({ 
                        test: 'Database Unsur Lainnya Check', 
                        status: 'WARNING', 
                        data: dbCheck 
                    });
                }
                
                return dbCheck;
            } else {
                console.log('❌ Database check failed:', dbCheck.error);
                
                this.testResults.push({ 
                    test: 'Database Unsur Lainnya Check', 
                    status: 'FAIL', 
                    error: dbCheck.error 
                });
                return null;
            }
        } catch (error) {
            console.error('❌ Database check error:', error.message);
            this.testResults.push({ 
                test: 'Database Unsur Lainnya Check', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async verifyDirectQuery() {
        console.log('🔍 Verifying with direct database query...');
        
        try {
            const directQuery = await this.page.evaluate(async () => {
                try {
                    // Direct API call to check bagian for UNSUR LAINNYA
                    const response = await fetch('./bagian.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'get_bagian_detail',
                            id: '6' // Assuming UNSUR LAINNYA has ID 6
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
            
            console.log('Direct query result:', JSON.stringify(directQuery, null, 2));
            
            if (directQuery.success) {
                this.testResults.push({ 
                    test: 'Direct Query Check', 
                    status: 'PASS', 
                    data: directQuery 
                });
            } else {
                this.testResults.push({ 
                    test: 'Direct Query Check', 
                    status: 'FAIL', 
                    data: directQuery 
                });
            }
            
            return directQuery;
        } catch (error) {
            console.error('❌ Direct query error:', error.message);
            this.testResults.push({ 
                test: 'Direct Query Check', 
                status: 'FAIL', 
                error: error.message 
            });
            return null;
        }
    }

    async generateReport() {
        console.log('📋 Generating UNSUR LAINNYA test report...');
        
        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'Unsur Lainnya Bagian Verification',
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };
        
        const reportPath = `${config.output.reports}/unsur_lainnya_test_${timestamp.replace(/[:.]/g, '-')}.json`;
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
            await this.checkUnsurLainnyaBagian();
            await this.checkDatabaseUnsurLainnya();
            await this.verifyDirectQuery();
            
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
    const test = new UnsurLainnyaBagianTest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ UNSUR LAINNYA test completed!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ UNSUR LAINNYA test failed:', error.message);
            process.exit(1);
        });
}

module.exports = UnsurLainnyaBagianTest;
