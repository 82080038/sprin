#!/usr/bin/env node
/**
 * Fast Comprehensive Test Suite for SPRIN
 * Optimized for speed with single browser session
 */

const TestRunner = require('./testRunner');

async function runFastTests() {
    console.log('╔════════════════════════════════════════════════════════╗');
    console.log('║     SPRIN Fast Test Suite                            ║');
    console.log('║     Optimized for Speed                              ║');
    console.log('╚════════════════════════════════════════════════════════╝\n');
    
    const runner = new TestRunner();
    const startTime = Date.now();
    
    try {
        // Initialize browser once
        console.log('🚀 Starting browser...');
        await runner.initialize();
        
        // Login once for all UI tests
        console.log('\n🔐 Logging in for all tests...');
        await runner.login();
        
        // Run tests with shared session
        console.log('\n✅ Running tests with shared session...');
        
        // Test 1: Dashboard
        await runner.test('Dashboard Loads', async (page) => {
            await runner.waitForTimeout(2000);
            const content = await page.content();
            if (!content.includes('POLRES SAMOSIR') && !content.includes('Dashboard')) {
                throw new Error('Dashboard content not found');
            }
        });
        
        // Test 2: Statistics API
        await runner.test('API: Personil Statistics', async (page) => {
            const response = await page.evaluate(async () => {
                const res = await fetch('/sprint/api/personil_simple.php');
                return res.json();
            });
            if (!response.success) {
                throw new Error('API failed: ' + response.message);
            }
            console.log(`   📊 Total: ${response.data.statistics.total_personil}`);
        });
        
        // Test 3: Personil Page
        await runner.test('Personil Page Loads', async (page) => {
            await page.goto('http://localhost/sprint/pages/personil.php');
            await runner.waitForTimeout(2000);
            const content = await page.content();
            if (!content.includes('Manajemen Personil')) {
                throw new Error('Personil page not loaded');
            }
        });
        
        // Test 4: API List
        await runner.test('API: Personil List', async (page) => {
            const response = await page.evaluate(async () => {
                const res = await fetch('/sprint/api/personil_list.php');
                return res.json();
            });
            if (!response.success) {
                throw new Error('API failed: ' + response.message);
            }
        });
        
        // Test 5: Bagian Page
        await runner.test('Bagian Page Loads', async (page) => {
            await page.goto('http://localhost/sprint/pages/bagian.php');
            await runner.waitForTimeout(2000);
            const content = await page.content();
            if (!content.includes('Manajemen Bagian')) {
                throw new Error('Bagian page not loaded');
            }
        });
        
        // Test 6: Jabatan Page
        await runner.test('Jabatan Page Loads', async (page) => {
            await page.goto('http://localhost/sprint/pages/jabatan.php');
            await runner.waitForTimeout(2000);
            const content = await page.content();
            if (!content.includes('Manajemen Jabatan')) {
                throw new Error('Jabatan page not loaded');
            }
        });
        
        // Test 7: Unsur Page
        await runner.test('Unsur Page Loads', async (page) => {
            await page.goto('http://localhost/sprint/pages/unsur.php');
            await runner.waitForTimeout(2000);
            const content = await page.content();
            if (!content.includes('Manajemen Unsur')) {
                throw new Error('Unsur page not loaded');
            }
        });
        
        // Test 8: Calendar Page
        await runner.test('Calendar Page Loads', async (page) => {
            await page.goto('http://localhost/sprint/pages/calendar_dashboard.php');
            await runner.waitForTimeout(2000);
            const content = await page.content();
            if (!content.includes('Jadwal') && !content.includes('Calendar')) {
                throw new Error('Calendar page not loaded');
            }
        });
        
        // Test 9: All APIs Response Format
        await runner.test('API: Response Format Check', async (page) => {
            const apis = [
                '/sprint/api/personil_simple.php',
                '/sprint/api/personil_list.php',
                '/sprint/api/unsur_stats.php',
                '/sprint/api/simple.php',
                '/sprint/api/calendar_api.php',
                '/sprint/api/advanced_search.php'
            ];
            
            for (const api of apis) {
                const response = await page.evaluate(async (url) => {
                    const res = await fetch(url);
                    return res.json();
                }, api);
                
                if (!response.hasOwnProperty('success') || 
                    !response.hasOwnProperty('message') || 
                    !response.hasOwnProperty('timestamp')) {
                    throw new Error(`Invalid format in ${api}`);
                }
            }
            console.log(`   ✅ All ${apis.length} APIs follow standard format`);
        });
        
        // Test 10: Logout
        await runner.test('Logout Works', async (page) => {
            await runner.logout();
            const url = page.url();
            if (!url.includes('login.php')) {
                throw new Error('Not redirected to login page');
            }
        });
        
    } catch (error) {
        console.error('\n❌ Test execution failed:', error.message);
    } finally {
        // Close browser
        await runner.close();
        
        // Generate report
        const report = runner.generateReport();
        const duration = ((Date.now() - startTime) / 1000).toFixed(2);
        
        console.log('\n╔════════════════════════════════════════════════════════╗');
        console.log('║                   TEST SUMMARY                         ║');
        console.log('╠════════════════════════════════════════════════════════╣');
        console.log(`║  Total Tests:  ${report.summary.total.toString().padEnd(37)} ║`);
        console.log(`║  Passed:       ${report.summary.passed.toString().padEnd(37)} ║`);
        console.log(`║  Failed:       ${report.summary.failed.toString().padEnd(37)} ║`);
        console.log(`║  Pass Rate:    ${(report.summary.passed / report.summary.total * 100).toFixed(2)}%${''.padEnd(34)} ║`);
        console.log(`║  Duration:     ${duration}s${''.padEnd(36)} ║`);
        console.log('╚════════════════════════════════════════════════════════╝');
        
        // Save report
        const fs = require('fs');
        const path = require('path');
        const reportPath = path.join(__dirname, 'results', 'fast-test-report.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        console.log(`\n📊 Report saved: ${reportPath}`);
    }
}

// Run if called directly
if (require.main === module) {
    runFastTests().catch(console.error);
}

module.exports = runFastTests;
