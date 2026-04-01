/**
 * SPRIN Test Suite
 * Main test runner for SPRIN application
 */

const { runTests } = require('./testRunner');

// Test configuration
const TEST_CONFIG = {
    baseUrl: 'http://localhost/sprint',
    timeout: 15000,
    headless: false,
    viewport: { width: 1366, height: 768 }
};

// Main test suites
const TEST_SUITES = [
    'login',
    'dashboard',
    'personil',
    'bagian',
    'unsur',
    'jabatan'
];

async function runAllTests() {
    console.log('🚀 Starting SPRIN Test Suite...');
    console.log(`📊 Base URL: ${TEST_CONFIG.baseUrl}`);
    console.log(`⏱️ Timeout: ${TEST_CONFIG.timeout}ms`);
    
    try {
        const results = await runTests(TEST_SUITES, TEST_CONFIG);
        
        console.log('\n📈 Test Results:');
        console.log(`✅ Passed: ${results.passed}`);
        console.log(`❌ Failed: ${results.failed}`);
        console.log(`⏱️ Duration: ${results.duration}ms`);
        
        if (results.failed > 0) {
            console.log('\n❌ Some tests failed. Check the logs for details.');
            process.exit(1);
        } else {
            console.log('\n✅ All tests passed!');
            process.exit(0);
        }
    } catch (error) {
        console.error('❌ Test suite failed:', error);
        process.exit(1);
    }
}

// Run tests if this file is executed directly
if (require.main === module) {
    runAllTests();
}

module.exports = { runAllTests, TEST_CONFIG, TEST_SUITES };
