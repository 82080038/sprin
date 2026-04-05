const http = require('http');
const https = require('https');
const fs = require('fs');
const path = require('path');

class SimpleBrowserTest {
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

    async runComprehensiveTests() {
        console.log('🚀 Starting Simple Browser Testing...');
        
        // Test pages
        const testPages = [
            '/',
            '/login.php',
            '/pages/main.php',
            '/pages/personil.php',
            '/pages/bagian.php'
        ];

        for (const page of testPages) {
            await this.testPage(this.baseUrl + page);
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
            await this.testAPI(this.baseUrl + endpoint);
        }

        this.generateSummary();
        this.saveResults();
        this.displayResults();
        
        return this.results;
    }

    async testPage(url) {
        try {
            console.log(`Testing page: ${url}`);
            
            const startTime = Date.now();
            const response = await this.makeRequest(url);
            const endTime = Date.now();
            
            const testResult = {
                url: url,
                status: response.statusCode,
                responseTime: endTime - startTime,
                contentType: response.headers['content-type'],
                contentLength: response.data.length,
                timestamp: new Date().toISOString(),
                errors: [],
                warnings: []
            };

            // Check if page loads successfully
            if (response.statusCode === 200) {
                // Check for common HTML structure
                if (response.data.includes('<!DOCTYPE') || response.data.includes('<html')) {
                    testResult.isValidHTML = true;
                } else {
                    testResult.warnings.push('No valid HTML structure found');
                }

                // Check for common errors
                if (response.data.includes('Fatal error') || response.data.includes('Parse error')) {
                    testResult.errors.push('PHP error detected in response');
                }

                // Check for CSS links
                const cssLinks = (response.data.match(/<link[^>]*\.css[^>]*>/g) || []).length;
                testResult.cssLinks = cssLinks;
                
                // Check for JS scripts
                const jsScripts = (response.data.match(/<script[^>]*\.js[^>]*>/g) || []).length;
                testResult.jsScripts = jsScripts;

            } else {
                testResult.errors.push(`HTTP ${response.statusCode}: ${response.statusMessage}`);
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

    async testAPI(url) {
        try {
            console.log(`Testing API: ${url}`);
            
            const startTime = Date.now();
            const response = await this.makeRequest(url);
            const endTime = Date.now();
            
            const testResult = {
                url: url,
                status: response.statusCode,
                responseTime: endTime - startTime,
                contentType: response.headers['content-type'],
                contentLength: response.data.length,
                timestamp: new Date().toISOString(),
                errors: [],
                warnings: []
            };

            // Check if response is valid JSON
            try {
                const jsonData = JSON.parse(response.data);
                testResult.isValidJSON = true;
                testResult.responseData = jsonData;
                
                // Check for common API response structure
                if (jsonData.status !== undefined) {
                    testResult.hasStatusField = true;
                }
                if (jsonData.data !== undefined) {
                    testResult.hasDataField = true;
                }
                if (jsonData.error !== undefined) {
                    testResult.errors.push('API returned error: ' + jsonData.error);
                }
                
            } catch (jsonError) {
                testResult.isValidJSON = false;
                testResult.errors.push(`Invalid JSON: ${jsonError.message}`);
                
                // Check if it's HTML error page
                if (response.data.includes('<!DOCTYPE') || response.data.includes('<html')) {
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

    makeRequest(url) {
        return new Promise((resolve, reject) => {
            const protocol = url.startsWith('https') ? https : http;
            
            const request = protocol.get(url, (response) => {
                let data = '';
                
                response.on('data', (chunk) => {
                    data += chunk;
                });
                
                response.on('end', () => {
                    resolve({
                        statusCode: response.statusCode,
                        statusMessage: response.statusMessage,
                        headers: response.headers,
                        data: data
                    });
                });
            });

            request.on('error', (error) => {
                reject(error);
            });

            request.setTimeout(10000, () => {
                request.destroy();
                reject(new Error('Request timeout'));
            });
        });
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
        const reportPath = path.join(__dirname, 'browser_test_report.json');
        fs.writeFileSync(reportPath, JSON.stringify(this.results, null, 2));
        console.log(`📊 Test report saved to: ${reportPath}`);
    }

    displayResults() {
        console.log('\n📊 BROWSER TEST RESULTS SUMMARY:');
        console.log('==================================');
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

        if (this.results.tests.length > 0) {
            console.log('\n📄 DETAILED RESULTS:');
            this.results.tests.forEach((test, index) => {
                const status = test.status === 200 ? '✅' : '❌';
                console.log(`${status} ${index + 1}. ${test.url}`);
                console.log(`   Status: ${test.status}`);
                console.log(`   Response Time: ${test.responseTime}ms`);
                console.log(`   Content Type: ${test.contentType}`);
                
                if (test.errors.length > 0) {
                    console.log(`   Errors: ${test.errors.join(', ')}`);
                }
                
                if (test.warnings.length > 0) {
                    console.log(`   Warnings: ${test.warnings.join(', ')}`);
                }
                
                if (test.isValidJSON !== undefined) {
                    console.log(`   JSON Valid: ${test.isValidJSON ? 'Yes' : 'No'}`);
                }
                
                if (test.isValidHTML !== undefined) {
                    console.log(`   HTML Valid: ${test.isValidHTML ? 'Yes' : 'No'}`);
                }
                
                console.log('');
            });
        }
        
        if (this.results.summary.errors === 0 && this.results.summary.failed === 0) {
            console.log('\n🎉 ALL TESTS PASSED! Application is ready for production.');
        } else {
            console.log('\n⚠️  Some tests failed. Please review the errors above.');
        }
    }
}

// Run the tests
async function main() {
    const tester = new SimpleBrowserTest();
    
    try {
        await tester.runComprehensiveTests();
    } catch (error) {
        console.error('❌ Test execution failed:', error.message);
        process.exit(1);
    }
}

// Run main function
main().catch(error => {
    console.error('❌ Fatal error:', error);
    process.exit(1);
});
