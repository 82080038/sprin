#!/usr/bin/env node
/**
 * Comprehensive Puppeteer Test Suite for SPRIN
 * Run all tests: node tests/puppeteer/run-all-tests.js
 */

const TestRunner = require('./testRunner');
const loginTests = require('./tests/login.test');
const dashboardTests = require('./tests/dashboard.test');
const personilTests = require('./tests/personil.test');
const apiTests = require('./tests/api.test');
const organizationTests = require('./tests/organization.test');
const calendarTests = require('./tests/calendar.test');

async function runAllTests() {
    console.log('╔════════════════════════════════════════════════════════╗');
    console.log('║     SPRIN Comprehensive Puppeteer Test Suite         ║');
    console.log('║     POLRES Samosir Management System                 ║');
    console.log('╚════════════════════════════════════════════════════════╝\n');
    
    const runner = new TestRunner();
    
    try {
        // Initialize browser
        await runner.initialize();
        
        // Run Login Tests
        console.log('\n┌────────────────────────────────────────────────────────┐');
        console.log('│ LOGIN & AUTHENTICATION TESTS                           │');
        console.log('└────────────────────────────────────────────────────────┘');
        
        const login = loginTests(runner);
        await login.testLoginPageLoads();
        await login.testLandingPage();
        await login.testValidLogin();
        await login.testInvalidLogin();
        await login.testQuickLogin();
        await login.testLogout();
        
        // Run Dashboard Tests
        console.log('\n┌────────────────────────────────────────────────────────┐');
        console.log('│ DASHBOARD & NAVIGATION TESTS                           │');
        console.log('└────────────────────────────────────────────────────────┘');
        
        const dashboard = dashboardTests(runner);
        await dashboard.testDashboardLoads();
        await dashboard.testStatisticsLoad();
        await dashboard.testNavigationMenu();
        await dashboard.testNavigateToPersonil();
        await dashboard.testNavigateToBagian();
        await dashboard.testNavigateToUnsur();
        await dashboard.testNavigateToJabatan();
        await dashboard.testNavigateToCalendar();
        
        // Run Personil Tests
        console.log('\n┌────────────────────────────────────────────────────────┐');
        console.log('│ PERSONIL MANAGEMENT TESTS                              │');
        console.log('└────────────────────────────────────────────────────────┘');
        
        const personil = personilTests(runner);
        await personil.testPersonilListLoads();
        await personil.testAddPersonilForm();
        await personil.testSearchPersonil();
        await personil.testFilterPersonilByBagian();
        await personil.testExportButtonsExist();
        
        // Run Organization Tests
        console.log('\n┌────────────────────────────────────────────────────────┐');
        console.log('│ ORGANIZATION STRUCTURE TESTS                           │');
        console.log('└────────────────────────────────────────────────────────┘');
        
        const organization = organizationTests(runner);
        await organization.testBagianPageData();
        await organization.testUnsurPageData();
        await organization.testJabatanPageLoads();
        await organization.testOrganizationStructure();
        
        // Run Calendar Tests
        console.log('\n┌────────────────────────────────────────────────────────┐');
        console.log('│ CALENDAR & SCHEDULE TESTS                              │');
        console.log('└────────────────────────────────────────────────────────┘');
        
        const calendar = calendarTests(runner);
        await calendar.testCalendarPageLoads();
        await calendar.testCalendarStatsApi();
        await calendar.testCalendarEventsApi();
        await calendar.testScheduleElements();
        
        // Run API Tests
        console.log('\n┌────────────────────────────────────────────────────────┐');
        console.log('│ API ENDPOINT TESTS                                     │');
        console.log('└────────────────────────────────────────────────────────┘');
        
        const api = apiTests(runner);
        await api.testPersonilCrudApi();
        await api.testPersonilDetailApi();
        await api.testCalendarApi();
        await api.testAdvancedSearchApi();
        await api.testJabatanCrudApi();
        await api.testSearchPersonilApi();
        await api.testPaginationApi();
        await api.testSimpleApi();
        await api.testApiResponseFormat();
        
        // Additional API Tests from Personil
        await personil.testApiGetPersonilList();
        await personil.testApiGetPersonilStats();
        await personil.testApiGetUnsurStats();
        
        // Additional Org API Tests
        await organization.testBagianApi();
        await organization.testUnsurStatsDetail();
        
        // Google Calendar
        await calendar.testGoogleCalendarIntegration();
        await api.testExportApi();
        
    } catch (error) {
        console.error('\n❌ Fatal error during test execution:', error.message);
    } finally {
        // Generate report
        const report = runner.generateReport();
        
        // Close browser
        await runner.close();
        
        // Print summary
        console.log('\n╔════════════════════════════════════════════════════════╗');
        console.log('║                   TEST SUMMARY                         ║');
        console.log('╠════════════════════════════════════════════════════════╣');
        console.log(`║  Total Tests:  ${String(report.summary.total).padEnd(35)}║`);
        console.log(`║  Passed:        ${String(report.summary.passed).padEnd(35)}║`);
        console.log(`║  Failed:        ${String(report.summary.failed).padEnd(35)}║`);
        console.log(`║  Pass Rate:     ${String(report.summary.passRate).padEnd(35)}║`);
        console.log(`║  Duration:      ${String(report.summary.totalDuration).padEnd(35)}║`);
        console.log('╚════════════════════════════════════════════════════════╝\n');
        
        // Exit with appropriate code
        process.exit(report.summary.failed > 0 ? 1 : 0);
    }
}

// Run tests if called directly
if (require.main === module) {
    runAllTests();
}

module.exports = runAllTests;
