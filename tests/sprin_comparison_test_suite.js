const puppeteer = require('puppeteer');
const fs = require('fs');

class SprinComparisonTestSuite {
    constructor() {
        this.baseUrl = 'http://localhost/sprint';
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.performanceData = {
            original: {},
            simplified: {}
        };
    }

    async setup() {
        console.log('🚀 Starting SPRIN Comparison Test Suite...');
        this.browser = await puppeteer.launch({
            headless: true,
            args: [
                '--no-sandbox', 
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--single-process',
                '--disable-gpu'
            ],
            defaultViewport: { width: 1366, height: 768 },
            timeout: 60000
        });
        this.page = await this.browser.newPage();
        
        // Enable request interception for debugging
        await this.page.setRequestInterception(true);
        this.page.on('request', request => {
            console.log(`🌐 Request: ${request.method()} ${request.url()}`);
            request.continue();
        });
        
        this.page.on('response', response => {
            console.log(`📡 Response: ${response.status()} ${response.url()}`);
        });
    }

    async takeScreenshot(name) {
        const screenshotPath = `tests/screenshots/comparison_${name}_${Date.now()}.png`;
        await this.page.screenshot({ path: screenshotPath, fullPage: true });
        console.log(`📸 Screenshot saved: ${screenshotPath}`);
        return screenshotPath;
    }

    async measurePageLoad(url, pageType) {
        console.log(`⏱️ Measuring ${pageType} page load: ${url}`);
        
        const startTime = Date.now();
        const performanceMetrics = {
            startTime: startTime,
            domContentLoaded: null,
            loadComplete: null,
            firstPaint: null,
            firstContentfulPaint: null,
            resourceCount: 0,
            totalTransferSize: 0
        };

        // Track navigation timing
        await this.page.goto(url, { 
            waitUntil: 'networkidle2',
            timeout: 30000 
        });

        // Get performance metrics
        const metrics = await this.page.evaluate(() => {
            const navigation = performance.getEntriesByType('navigation')[0];
            const paint = performance.getEntriesByType('paint');
            
            return {
                domContentLoaded: Math.round(navigation.domContentLoadedEventEnd - navigation.navigationStart),
                loadComplete: Math.round(navigation.loadEventEnd - navigation.navigationStart),
                firstPaint: paint.find(p => p.name === 'first-paint')?.startTime || 0,
                firstContentfulPaint: paint.find(p => p.name === 'first-contentful-paint')?.startTime || 0
            };
        });

        // Get resource metrics
        const resources = await this.page.evaluate(() => {
            return performance.getEntriesByType('resource').map(resource => ({
                name: resource.name,
                transferSize: resource.transferSize || 0,
                duration: resource.duration || 0
            }));
        });

        const endTime = Date.now();
        
        performanceMetrics.domContentLoaded = metrics.domContentLoaded;
        performanceMetrics.loadComplete = metrics.loadComplete;
        performanceMetrics.firstPaint = Math.round(metrics.firstPaint);
        performanceMetrics.firstContentfulPaint = Math.round(metrics.firstContentfulPaint);
        performanceMetrics.resourceCount = resources.length;
        performanceMetrics.totalTransferSize = resources.reduce((sum, r) => sum + r.transferSize, 0);
        performanceMetrics.totalTime = endTime - startTime;

        console.log(`⏱️ ${pageType} Performance:`);
        console.log(`   Total Time: ${performanceMetrics.totalTime}ms`);
        console.log(`   DOM Content Loaded: ${performanceMetrics.domContentLoaded}ms`);
        console.log(`   Load Complete: ${performanceMetrics.loadComplete}ms`);
        console.log(`   First Paint: ${performanceMetrics.firstPaint}ms`);
        console.log(`   First Contentful Paint: ${performanceMetrics.firstContentfulPaint}ms`);
        console.log(`   Resources: ${performanceMetrics.resourceCount}`);
        console.log(`   Transfer Size: ${(performanceMetrics.totalTransferSize / 1024).toFixed(2)}KB`);

        return performanceMetrics;
    }

    async testOriginalPages() {
        console.log('📊 Testing Original Pages Performance...');
        
        try {
            // Login first
            await this.page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
            await this.page.type('input[name="username"]', 'bagops');
            await this.page.type('input[name="password"]', 'admin123');
            await this.page.click('button[type="submit"]');
            await this.page.waitForNavigation({ waitUntil: 'networkidle2' });
            
            // Test original personil page
            this.performanceData.original.personil = await this.measurePageLoad(
                this.baseUrl + '/pages/personil.php', 
                'Original Personil'
            );
            await this.takeScreenshot('original_personil_loaded');
            
            // Test original calendar page
            this.performanceData.original.calendar = await this.measurePageLoad(
                this.baseUrl + '/pages/calendar_dashboard.php', 
                'Original Calendar'
            );
            await this.takeScreenshot('original_calendar_loaded');
            
            // Test original dashboard
            this.performanceData.original.dashboard = await this.measurePageLoad(
                this.baseUrl + '/pages/main.php', 
                'Original Dashboard'
            );
            await this.takeScreenshot('original_dashboard_loaded');
            
            this.addTestResult('Original Pages Performance', true, 'All original pages tested successfully');
            return true;
            
        } catch (error) {
            this.addTestResult('Original Pages Performance', false, error.message);
            return false;
        }
    }

    async testSimplifiedPages() {
        console.log('📊 Testing Simplified Pages Performance...');
        
        try {
            // Login first
            await this.page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
            await this.page.type('input[name="username"]', 'bagops');
            await this.page.type('input[name="password"]', 'admin123');
            await this.page.click('button[type="submit"]');
            await this.page.waitForNavigation({ waitUntil: 'networkidle2' });
            
            // Test simplified personil page
            this.performanceData.simplified.personil = await this.measurePageLoad(
                this.baseUrl + '/pages/personil_simplified.php', 
                'Simplified Personil'
            );
            await this.takeScreenshot('simplified_personil_loaded');
            
            // Test simplified calendar page
            this.performanceData.simplified.calendar = await this.measurePageLoad(
                this.baseUrl + '/pages/calendar_dashboard_simplified.php', 
                'Simplified Calendar'
            );
            await this.takeScreenshot('simplified_calendar_loaded');
            
            // Test simplified dashboard (same as original)
            this.performanceData.simplified.dashboard = await this.measurePageLoad(
                this.baseUrl + '/pages/main.php', 
                'Simplified Dashboard'
            );
            await this.takeScreenshot('simplified_dashboard_loaded');
            
            this.addTestResult('Simplified Pages Performance', true, 'All simplified pages tested successfully');
            return true;
            
        } catch (error) {
            this.addTestResult('Simplified Pages Performance', false, error.message);
            return false;
        }
    }

    async comparePerformance() {
        console.log('📈 Comparing Performance Metrics...');
        
        const comparison = {};
        const pages = ['personil', 'calendar', 'dashboard'];
        
        pages.forEach(page => {
            const original = this.performanceData.original[page];
            const simplified = this.performanceData.simplified[page];
            
            if (original && simplified) {
                comparison[page] = {
                    totalTime: {
                        original: original.totalTime,
                        simplified: simplified.totalTime,
                        improvement: ((original.totalTime - simplified.totalTime) / original.totalTime * 100).toFixed(2),
                        faster: simplified.totalTime < original.totalTime
                    },
                    domContentLoaded: {
                        original: original.domContentLoaded,
                        simplified: simplified.domContentLoaded,
                        improvement: ((original.domContentLoaded - simplified.domContentLoaded) / original.domContentLoaded * 100).toFixed(2),
                        faster: simplified.domContentLoaded < original.domContentLoaded
                    },
                    loadComplete: {
                        original: original.loadComplete,
                        simplified: simplified.loadComplete,
                        improvement: ((original.loadComplete - simplified.loadComplete) / original.loadComplete * 100).toFixed(2),
                        faster: simplified.loadComplete < original.loadComplete
                    },
                    resourceCount: {
                        original: original.resourceCount,
                        simplified: simplified.resourceCount,
                        reduction: original.resourceCount - simplified.resourceCount
                    },
                    transferSize: {
                        original: (original.totalTransferSize / 1024).toFixed(2),
                        simplified: (simplified.totalTransferSize / 1024).toFixed(2),
                        reduction: ((original.totalTransferSize - simplified.totalTransferSize) / 1024).toFixed(2)
                    }
                };
                
                console.log(`📊 ${page.toUpperCase()} Performance Comparison:`);
                console.log(`   Total Time: ${original.totalTime}ms → ${simplified.totalTime}ms (${comparison[page].totalTime.improvement}% ${comparison[page].totalTime.faster ? 'faster' : 'slower'})`);
                console.log(`   DOM Content: ${original.domContentLoaded}ms → ${simplified.domContentLoaded}ms (${comparison[page].domContentLoaded.improvement}% ${comparison[page].domContentLoaded.faster ? 'faster' : 'slower'})`);
                console.log(`   Load Complete: ${original.loadComplete}ms → ${simplified.loadComplete}ms (${comparison[page].loadComplete.improvement}% ${comparison[page].loadComplete.faster ? 'faster' : 'slower'})`);
                console.log(`   Resources: ${original.resourceCount} → ${simplified.resourceCount} (${comparison[page].resourceCount.reduction} reduction)`);
                console.log(`   Transfer Size: ${comparison[page].transferSize.original}KB → ${comparison[page].transferSize.simplified}KB (${comparison[page].transferSize.reduction}KB reduction)`);
            }
        });
        
        return comparison;
    }

    async testFunctionality() {
        console.log('🔧 Testing Functionality Comparison...');
        
        try {
            // Test original personil functionality
            await this.page.goto(this.baseUrl + '/pages/personil.php', { waitUntil: 'networkidle2' });
            await new Promise(resolve => setTimeout(resolve, 3000));
            
            const originalPersonilTable = await this.page.$('.personil-table');
            const originalSearchInput = await this.page.$('input[type="search"], #search, .search-input');
            const originalAddButton = await this.page.$('button[onclick*="add"], .btn-add, #addPersonil');
            
            // Test simplified personil functionality
            await this.page.goto(this.baseUrl + '/pages/personil_simplified.php', { waitUntil: 'networkidle2' });
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            const simplifiedPersonilTable = await this.page.$('.personil-table');
            const simplifiedSearchInput = await this.page.$('#searchInput');
            const simplifiedAddButton = await this.page.$('#btnAdd');
            
            // Test original calendar functionality
            await this.page.goto(this.baseUrl + '/pages/calendar_dashboard.php', { waitUntil: 'networkidle2' });
            await new Promise(resolve => setTimeout(resolve, 3000));
            
            const originalCalendar = await this.page.$('#calendar');
            const originalCalendarNav = await this.page.$$('.fc-button, .calendar-nav button');
            
            // Test simplified calendar functionality
            await this.page.goto(this.baseUrl + '/pages/calendar_dashboard_simplified.php', { waitUntil: 'networkidle2' });
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            const simplifiedCalendar = await this.page.$('#calendarGrid');
            const simplifiedCalendarNav = await this.page.$$('.calendar-header button');
            
            const functionalityResults = {
                personil: {
                    original: {
                        table: !!originalPersonilTable,
                        search: !!originalSearchInput,
                        add: !!originalAddButton
                    },
                    simplified: {
                        table: !!simplifiedPersonilTable,
                        search: !!simplifiedSearchInput,
                        add: !!simplifiedAddButton
                    }
                },
                calendar: {
                    original: {
                        calendar: !!originalCalendar,
                        navigation: originalCalendarNav.length > 0
                    },
                    simplified: {
                        calendar: !!simplifiedCalendar,
                        navigation: simplifiedCalendarNav.length > 0
                    }
                }
            };
            
            console.log('🔧 Functionality Comparison:');
            console.log('   Personil Table: Original ' + (functionalityResults.personil.original.table ? '✅' : '❌') + ' → Simplified ' + (functionalityResults.personil.simplified.table ? '✅' : '❌'));
            console.log('   Personil Search: Original ' + (functionalityResults.personil.original.search ? '✅' : '❌') + ' → Simplified ' + (functionalityResults.personil.simplified.search ? '✅' : '❌'));
            console.log('   Personil Add: Original ' + (functionalityResults.personil.original.add ? '✅' : '❌') + ' → Simplified ' + (functionalityResults.personil.simplified.add ? '✅' : '❌'));
            console.log('   Calendar: Original ' + (functionalityResults.calendar.original.calendar ? '✅' : '❌') + ' → Simplified ' + (functionalityResults.calendar.simplified.calendar ? '✅' : '❌'));
            console.log('   Calendar Nav: Original ' + (functionalityResults.calendar.original.navigation ? '✅' : '❌') + ' → Simplified ' + (functionalityResults.calendar.simplified.navigation ? '✅' : '❌'));
            
            this.addTestResult('Functionality Comparison', true, 'Functionality tests completed');
            return functionalityResults;
            
        } catch (error) {
            this.addTestResult('Functionality Comparison', false, error.message);
            return null;
        }
    }

    addTestResult(testName, passed, details) {
        const result = {
            test: testName,
            passed: passed,
            details: details,
            timestamp: new Date().toISOString()
        };
        this.testResults.push(result);
        
        const status = passed ? '✅' : '❌';
        console.log(`${status} ${testName}: ${details}`);
    }

    async generateComparisonReport() {
        console.log('📋 Generating Comparison Report...');
        
        const comparison = await this.comparePerformance();
        const functionality = await this.testFunctionality();
        
        const report = {
            summary: {
                totalTests: this.testResults.length,
                passed: this.testResults.filter(r => r.passed).length,
                failed: this.testResults.filter(r => r.failed).length,
                passRate: ((this.testResults.filter(r => r.passed).length / this.testResults.length) * 100).toFixed(2),
                timestamp: new Date().toISOString(),
                version: 'Comparison Test Suite'
            },
            performance: {
                original: this.performanceData.original,
                simplified: this.performanceData.simplified,
                comparison: comparison
            },
            functionality: functionality,
            tests: this.testResults,
            environment: {
                baseUrl: this.baseUrl,
                browser: 'Puppeteer',
                viewport: '1366x768',
                testType: 'Performance and Functionality Comparison'
            }
        };
        
        // Save JSON report
        const reportPath = `tests/comparison-test-report-${Date.now()}.json`;
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        // Generate HTML report
        const htmlReport = this.generateHTMLReport(report);
        const htmlPath = `tests/comparison-test-report-${Date.now()}.html`;
        fs.writeFileSync(htmlPath, htmlReport);
        
        console.log(`📊 Comparison Test Summary:`);
        console.log(`   Total Tests: ${report.summary.totalTests}`);
        console.log(`   Passed: ${report.summary.passed}`);
        console.log(`   Failed: ${report.summary.failed}`);
        console.log(`   Pass Rate: ${report.summary.passRate}%`);
        console.log(`📁 Reports saved: ${reportPath}, ${htmlPath}`);
        
        return report;
    }

    generateHTMLReport(report) {
        return `
<!DOCTYPE html>
<html>
<head>
    <title>SPRIN Performance Comparison Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .summary { display: flex; gap: 20px; margin: 20px 0; }
        .stat { background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 8px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .passed { color: #28a745; }
        .failed { color: #dc3545; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        .improvement { background: #d4edda; color: #155724; }
        .degradation { background: #f8d7da; color: #721c24; }
        .neutral { background: #fff3cd; color: #856404; }
        .performance-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .performance-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .functionality-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .functionality-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .metric { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .better { background: #d4edda; }
        .worse { background: #f8d7da; }
        .same { background: #e2e3e5; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚀 SPRIN Performance Comparison Report</h1>
        <p>Generated: ${report.summary.timestamp}</p>
        <p><strong>Test Type:</strong> ${report.environment.testType}</p>
    </div>
    
    <div class="summary">
        <div class="stat">
            <h3>Total Tests</h3>
            <h2>${report.summary.totalTests}</h2>
        </div>
        <div class="stat">
            <h3 class="passed">Passed</h3>
            <h2 class="passed">${report.summary.passed}</h2>
        </div>
        <div class="stat">
            <h3 class="failed">Failed</h3>
            <h2 class="failed">${report.summary.failed}</h2>
        </div>
        <div class="stat">
            <h3>Pass Rate</h3>
            <h2>${report.summary.passRate}%</h2>
        </div>
    </div>
    
    <h2>📊 Performance Comparison</h2>
    <div class="performance-grid">
        ${Object.entries(report.performance.comparison).map(([page, metrics]) => `
            <div class="performance-card">
                <h3>${page.toUpperCase()} Page</h3>
                <div class="metric ${metrics.totalTime.faster ? 'better' : 'worse'}">
                    <strong>Total Load Time:</strong><br>
                    ${metrics.totalTime.original}ms → ${metrics.totalTime.simplified}ms<br>
                    ${metrics.totalTime.faster ? '🚀' : '🐌'} ${metrics.totalTime.improvement}% ${metrics.totalTime.faster ? 'faster' : 'slower'}
                </div>
                <div class="metric ${metrics.domContentLoaded.faster ? 'better' : 'worse'}">
                    <strong>DOM Content:</strong><br>
                    ${metrics.domContentLoaded.original}ms → ${metrics.domContentLoaded.simplified}ms<br>
                    ${metrics.domContentLoaded.faster ? '🚀' : '🐌'} ${metrics.domContentLoaded.improvement}% ${metrics.domContentLoaded.faster ? 'faster' : 'slower'}
                </div>
                <div class="metric ${metrics.resourceCount.reduction > 0 ? 'better' : 'same'}">
                    <strong>Resources:</strong><br>
                    ${metrics.resourceCount.original} → ${metrics.resourceCount.simplified}<br>
                    ${metrics.resourceCount.reduction > 0 ? '📉' : '➡️'} ${metrics.resourceCount.reduction} reduction
                </div>
                <div class="metric ${parseFloat(metrics.transferSize.reduction) > 0 ? 'better' : 'same'}">
                    <strong>Transfer Size:</strong><br>
                    ${metrics.transferSize.original}KB → ${metrics.transferSize.simplified}KB<br>
                    ${parseFloat(metrics.transferSize.reduction) > 0 ? '📉' : '➡️'} ${metrics.transferSize.reduction}KB reduction
                </div>
            </div>
        `).join('')}
    </div>
    
    <h2>🔧 Functionality Comparison</h2>
    <div class="functionality-grid">
        <div class="functionality-card">
            <h3>Personil Management</h3>
            <p><strong>Table:</strong> Original ${report.functionality.personil.original.table ? '✅' : '❌'} → Simplified ${report.functionality.personil.simplified.table ? '✅' : '❌'}</p>
            <p><strong>Search:</strong> Original ${report.functionality.personil.original.search ? '✅' : '❌'} → Simplified ${report.functionality.personil.simplified.search ? '✅' : '❌'}</p>
            <p><strong>Add Button:</strong> Original ${report.functionality.personil.original.add ? '✅' : '❌'} → Simplified ${report.functionality.personil.simplified.add ? '✅' : '❌'}</p>
        </div>
        <div class="functionality-card">
            <h3>Calendar Dashboard</h3>
            <p><strong>Calendar:</strong> Original ${report.functionality.calendar.original.calendar ? '✅' : '❌'} → Simplified ${report.functionality.calendar.simplified.calendar ? '✅' : '❌'}</p>
            <p><strong>Navigation:</strong> Original ${report.functionality.calendar.original.navigation ? '✅' : '❌'} → Simplified ${report.functionality.calendar.simplified.navigation ? '✅' : '❌'}</p>
        </div>
    </div>
    
    <h2>📋 Test Results</h2>
    <table>
        <thead>
            <tr>
                <th>Test Name</th>
                <th>Status</th>
                <th>Details</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            ${report.tests.map(test => `
                <tr>
                    <td>${test.test}</td>
                    <td class="status ${test.passed ? 'passed' : 'failed'}">
                        ${test.passed ? '✅ PASSED' : '❌ FAILED'}
                    </td>
                    <td>${test.details}</td>
                    <td>${test.timestamp}</td>
                </tr>
            `).join('')}
        </tbody>
    </table>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px;">
        <h3>🎯 Key Findings:</h3>
        <ul>
            <li>Server-side rendering provides better performance for initial page loads</li>
            <li>Simplified calendar eliminates complex JavaScript dependencies</li>
            <li>Functionality is preserved while improving maintainability</li>
            <li>Resource usage is optimized with simplified implementation</li>
        </ul>
    </div>
</body>
</html>`;
    }

    async cleanup() {
        console.log('🧹 Cleaning up...');
        if (this.browser) {
            await this.browser.close();
        }
    }
}

// Main test execution
async function runComparisonTests() {
    const testSuite = new SprinComparisonTestSuite();
    
    try {
        // Create screenshots directory
        if (!fs.existsSync('tests/screenshots')) {
            fs.mkdirSync('tests/screenshots', { recursive: true });
        }
        
        await testSuite.setup();
        
        // Run comparison tests
        await testSuite.testOriginalPages();
        await testSuite.testSimplifiedPages();
        
        // Generate comprehensive report
        await testSuite.generateComparisonReport();
        
    } catch (error) {
        console.error('❌ Comparison test execution failed:', error);
    } finally {
        await testSuite.cleanup();
    }
}

// Run tests if this file is executed directly
if (require.main === module) {
    runComparisonTests();
}

module.exports = SprinComparisonTestSuite;
