const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class WorkingPuppeteerTest {
    constructor() {
        this.results = {
            timestamp: new Date().toISOString(),
            tests: [],
            errors: [],
            warnings: [],
            summary: {}
        };
        this.baseUrl = 'http://localhost/sprint';
    }

    async runTests() {
        console.log('🚀 Starting Working Puppeteer Test...');
        
        try {
            const browser = await puppeteer.launch({
                headless: true,
                args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
            });
            
            const page = await browser.newPage();
            
            // Setup error collection
            await page.evaluateOnNewDocument(() => {
                window.jsErrors = [];
                window.consoleWarnings = [];
                
                window.addEventListener('error', (event) => {
                    window.jsErrors.push({
                        message: event.message,
                        filename: event.filename,
                        lineno: event.lineno,
                        colno: event.colno,
                        timestamp: new Date().toISOString()
                    });
                });
                
                const originalConsoleError = console.error;
                console.error = function(...args) {
                    window.consoleWarnings.push({
                        type: 'error',
                        message: args.join(' '),
                        timestamp: new Date().toISOString()
                    });
                    originalConsoleError.apply(console, args);
                };
                
                const originalConsoleWarn = console.warn;
                console.warn = function(...args) {
                    window.consoleWarnings.push({
                        type: 'warning',
                        message: args.join(' '),
                        timestamp: new Date().toISOString()
                    });
                    originalConsoleWarn.apply(console, args);
                };
            });
            
            // Test pages
            const testPages = [
                '/',
                '/login.php',
                '/pages/main.php',
                '/pages/personil.php',
                '/pages/bagian.php'
            ];
            
            for (const pagePath of testPages) {
                await this.testPage(page, this.baseUrl + pagePath);
            }
            
            // Test API endpoints
            const apiEndpoints = [
                '/api/health_check.php',
                '/api/personil_list.php',
                '/api/bagian_crud.php',
                '/api/jabatan_crud.php',
                '/api/unsur_crud.php'
            ];
            
            for (const endpoint of apiEndpoints) {
                await this.testAPI(page, this.baseUrl + endpoint);
            }
            
            await browser.close();
            
        } catch (error) {
            console.error('❌ Puppeteer test failed:', error.message);
            this.results.errors.push({
                type: 'puppeteer_error',
                message: error.message,
                timestamp: new Date().toISOString()
            });
        }
        
        this.generateSummary();
        this.saveResults();
        this.displayResults();
        
        return this.results;
    }
    
    async testPage(page, url) {
        try {
            console.log(`Testing page: ${url}`);
            
            const response = await page.goto(url, { 
                waitUntil: 'networkidle2',
                timeout: 10000 
            });
            
            const testResult = {
                url: url,
                status: response ? response.status() : 'failed',
                title: await page.title(),
                timestamp: new Date().toISOString(),
                errors: [],
                warnings: [],
                cssIssues: [],
                jsIssues: []
            };
            
            // Check for JavaScript errors
            const jsErrors = await page.evaluate(() => window.jsErrors || []);
            if (jsErrors.length > 0) {
                testResult.jsIssues.push(...jsErrors);
            }
            
            // Check for console warnings
            const consoleWarnings = await page.evaluate(() => window.consoleWarnings || []);
            if (consoleWarnings.length > 0) {
                testResult.warnings.push(...consoleWarnings);
            }
            
            // Check CSS validation
            const cssIssues = await page.evaluate(() => {
                const issues = [];
                const stylesheets = Array.from(document.styleSheets);
                
                stylesheets.forEach((sheet, index) => {
                    try {
                        const rules = Array.from(sheet.cssRules || sheet.rules || []);
                        rules.forEach((rule, ruleIndex) => {
                            if (rule.type === CSSRule.STYLE_RULE) {
                                const style = rule.style;
                                for (let i = 0; i < style.length; i++) {
                                    const property = style[i];
                                    const value = style.getPropertyValue(property);
                                    
                                    // Check for invalid CSS
                                    if (value.includes('undefined') || value.includes('NaN')) {
                                        issues.push({
                                            type: 'invalid_css_value',
                                            property: property,
                                            value: value,
                                            sheet: index,
                                            rule: ruleIndex
                                        });
                                    }
                                }
                            }
                        });
                    } catch (e) {
                        issues.push({
                            type: 'css_access_error',
                            message: e.message,
                            sheet: index
                        });
                    }
                });
                
                return issues;
            });
            
            if (cssIssues.length > 0) {
                testResult.cssIssues.push(...cssIssues);
            }
            
            this.results.tests.push(testResult);
            console.log(`✅ Page tested: ${url} - Status: ${testResult.status}`);
            
        } catch (error) {
            const errorResult = {
                url: url,
                status: 'error',
                error: error.message,
                timestamp: new Date().toISOString()
            };
            this.results.errors.push(errorResult);
            console.log(`❌ Page test failed: ${url} - ${error.message}`);
        }
    }
    
    async testAPI(page, url) {
        try {
            console.log(`Testing API: ${url}`);
            
            const response = await page.goto(url, { 
                waitUntil: 'networkidle0',
                timeout: 10000 
            });
            
            const testResult = {
                url: url,
                status: response ? response.status() : 'failed',
                timestamp: new Date().toISOString(),
                errors: [],
                warnings: []
            };
            
            // Check if response is valid JSON
            try {
                const content = await page.content();
                const jsonData = JSON.parse(content);
                testResult.isValidJSON = true;
                testResult.responseData = jsonData;
            } catch (jsonError) {
                testResult.isValidJSON = false;
                testResult.errors.push(`Invalid JSON: ${jsonError.message}`);
                
                // Check if it's HTML error page
                if (content.includes('<!DOCTYPE') || content.includes('<html')) {
                    testResult.errors.push('API returned HTML instead of JSON');
                }
            }
            
            this.results.tests.push(testResult);
            console.log(`✅ API tested: ${url} - Status: ${testResult.status}`);
            
        } catch (error) {
            const errorResult = {
                url: url,
                status: 'error',
                error: error.message,
                timestamp: new Date().toISOString()
            };
            this.results.errors.push(errorResult);
            console.log(`❌ API test failed: ${url} - ${error.message}`);
        }
    }
    
    generateSummary() {
        const totalTests = this.results.tests.length;
        const passedTests = this.results.tests.filter(t => t.status === 200).length;
        const failedTests = this.results.tests.filter(t => t.status === 'error' || t.status >= 400).length;
        const totalErrors = this.results.errors.length;
        const totalWarnings = this.results.warnings.length;
        
        this.results.summary = {
            total: totalTests,
            passed: passedTests,
            failed: failedTests,
            errors: totalErrors,
            warnings: totalWarnings,
            successRate: totalTests > 0 ? ((passedTests / totalTests) * 100).toFixed(1) : 0
        };
    }
    
    saveResults() {
        const reportPath = path.join(__dirname, 'working_puppeteer_test_results.json');
        fs.writeFileSync(reportPath, JSON.stringify(this.results, null, 2));
        console.log(`📊 Test results saved to: ${reportPath}`);
    }
    
    displayResults() {
        console.log('\n📊 WORKING PUPPETEER TEST RESULTS:');
        console.log('===================================');
        console.log(`Total Tests: ${this.results.summary.total}`);
        console.log(`Passed: ${this.results.summary.passed}`);
        console.log(`Failed: ${this.results.summary.failed}`);
        console.log(`Errors: ${this.results.summary.errors}`);
        console.log(`Warnings: ${this.results.summary.warnings}`);
        console.log(`Success Rate: ${this.results.summary.successRate}%`);
        
        if (this.results.errors.length > 0) {
            console.log('\n❌ ERRORS FOUND:');
            this.results.errors.forEach((error, index) => {
                console.log(`${index + 1}. ${error.url || 'Unknown'}: ${error.error || error.message}`);
            });
        }
    }
}

// Run the test
const tester = new WorkingPuppeteerTest();
tester.runTests().catch(error => {
    console.error('❌ Test execution failed:', error);
    process.exit(1);
});