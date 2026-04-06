/**
 * Simple API Test - Direct API testing without Puppeteer
 */

const baseUrl = 'http://localhost/sprin/api';

// Simple fetch wrapper
async function testApi(url) {
    try {
        const response = await fetch(url);
        const data = await response.json();
        return {
            success: response.ok,
            status: response.status,
            data: data
        };
    } catch (error) {
        return {
            success: false,
            error: error.message,
            status: 0
        };
    }
}

// Test functions
async function testUnifiedApi() {
    console.log('🔍 Testing Unified API Gateway...');
    
    const tests = [
        {
            name: 'Dashboard Stats',
            url: `${baseUrl}/unified-api.php?resource=stats&action=dashboard`
        },
        {
            name: 'Unsur List',
            url: `${baseUrl}/unified-api.php?resource=unsur&action=get_all`
        },
        {
            name: 'Bagian List',
            url: `${baseUrl}/unified-api.php?resource=bagian&action=get_all`
        },
        {
            name: 'Jabatan List',
            url: `${baseUrl}/unified-api.php?resource=jabatan&action=get_all`
        },
        {
            name: 'Personil Stats',
            url: `${baseUrl}/unified-api.php?resource=personil&action=stats`
        }
    ];
    
    let passed = 0;
    let failed = 0;
    
    for (const test of tests) {
        console.log(`Testing ${test.name}...`);
        const result = await testApi(test.url);
        
        if (result.success && result.data.success) {
            console.log(`✅ ${test.name} - SUCCESS`);
            console.log(`   Status: ${result.status}`);
            console.log(`   Data: ${JSON.stringify(result.data).substring(0, 100)}...`);
            passed++;
        } else {
            console.log(`❌ ${test.name} - FAILED`);
            console.log(`   Status: ${result.status}`);
            console.log(`   Error: ${result.error || result.data?.message}`);
            failed++;
        }
        console.log('');
    }
    
    console.log(`📊 API Test Results: ${passed} passed, ${failed} failed`);
    return { passed, failed };
}

async function testIndividualApis() {
    console.log('🔍 Testing Individual APIs...');
    
    const tests = [
        {
            name: 'Unsur API',
            url: `${baseUrl}/unsur_api.php?action=get_all_unsur`
        },
        {
            name: 'Bagian API',
            url: `${baseUrl}/bagian_api.php?action=get_all_bagian`
        },
        {
            name: 'Jabatan API',
            url: `${baseUrl}/jabatan_api.php?action=get_all_jabatan`
        },
        {
            name: 'Calendar API',
            url: `${baseUrl}/calendar_api_public.php?action=get_stats`
        },
        {
            name: 'Personil Stats',
            url: `${baseUrl}/personil_stats_public.php`
        }
    ];
    
    let passed = 0;
    let failed = 0;
    
    for (const test of tests) {
        console.log(`Testing ${test.name}...`);
        const result = await testApi(test.url);
        
        if (result.success && result.data.success) {
            console.log(`✅ ${test.name} - SUCCESS`);
            passed++;
        } else {
            console.log(`❌ ${test.name} - FAILED`);
            console.log(`   Error: ${result.error || result.data?.message}`);
            failed++;
        }
        console.log('');
    }
    
    console.log(`📊 Individual API Test Results: ${passed} passed, ${failed} failed`);
    return { passed, failed };
}

async function testApiPerformance() {
    console.log('🚀 Testing API Performance...');
    
    const url = `${baseUrl}/unified-api.php?resource=stats&action=dashboard`;
    
    // Test single request
    const start1 = Date.now();
    const result1 = await testApi(url);
    const time1 = Date.now() - start1;
    
    console.log(`Single request: ${time1}ms`);
    
    // Test concurrent requests
    const start2 = Date.now();
    const promises = Array(5).fill().map(() => testApi(url));
    const results = await Promise.all(promises);
    const time2 = Date.now() - start2;
    
    const successCount = results.filter(r => r.success).length;
    console.log(`5 concurrent requests: ${time2}ms (${successCount}/5 successful)`);
    
    return {
        singleRequest: time1,
        concurrentRequests: time2,
        successRate: successCount / 5
    };
}

async function testDataIntegrity() {
    console.log('🔍 Testing Data Integrity...');
    
    // Get stats from unified API
    const statsResult = await testApi(`${baseUrl}/unified-api.php?resource=stats&action=dashboard`);
    
    if (!statsResult.success) {
        console.log('❌ Cannot test data integrity - stats API failed');
        return false;
    }
    
    const stats = statsResult.data.data;
    
    // Get individual counts
    const unsurResult = await testApi(`${baseUrl}/unified-api.php?resource=unsur&action=get_all`);
    const bagianResult = await testApi(`${baseUrl}/unified-api.php?resource=bagian&action=get_all`);
    const jabatanResult = await testApi(`${baseUrl}/unified-api.php?resource=jabatan&action=get_all`);
    
    if (unsurResult.success && bagianResult.success && jabatanResult.success) {
        const unsurCount = unsurResult.data.data?.length || 0;
        const bagianCount = bagianResult.data.data?.length || 0;
        const jabatanCount = jabatanResult.data.data?.length || 0;
        
        console.log(`📊 Data Comparison:`);
        console.log(`   Unsur: Stats=${stats.total_unsur}, Actual=${unsurCount}`);
        console.log(`   Bagian: Stats=${stats.total_bagian}, Actual=${bagianCount}`);
        console.log(`   Jabatan: Stats=${stats.total_jabatan}, Actual=${jabatanCount}`);
        
        const consistent = 
            stats.total_unsur === unsurCount &&
            stats.total_bagian === bagianCount &&
            stats.total_jabatan === jabatanCount;
        
        console.log(`✅ Data Integrity: ${consistent ? 'CONSISTENT' : 'INCONSISTENT'}`);
        return consistent;
    }
    
    console.log('❌ Cannot test data integrity - individual APIs failed');
    return false;
}

// Main test runner
async function runAllTests() {
    console.log('🚀 Starting Simple API Tests...');
    console.log('');
    
    try {
        // Test unified API
        const unifiedResults = await testUnifiedApi();
        console.log('');
        
        // Test individual APIs
        const individualResults = await testIndividualApis();
        console.log('');
        
        // Test performance
        const performanceResults = await testApiPerformance();
        console.log('');
        
        // Test data integrity
        const integrityResults = await testDataIntegrity();
        console.log('');
        
        // Summary
        const totalPassed = unifiedResults.passed + individualResults.passed;
        const totalFailed = unifiedResults.failed + individualResults.failed;
        
        console.log('🏆 FINAL RESULTS:');
        console.log(`   Total Tests: ${totalPassed + totalFailed}`);
        console.log(`   Passed: ${totalPassed}`);
        console.log(`   Failed: ${totalFailed}`);
        console.log(`   Success Rate: ${((totalPassed / (totalPassed + totalFailed)) * 100).toFixed(1)}%`);
        console.log(`   Performance: ${performanceResults.singleRequest}ms (single), ${performanceResults.concurrentRequests}ms (concurrent)`);
        console.log(`   Data Integrity: ${integrityResults ? '✅ PASS' : '❌ FAIL'}`);
        
        if (totalFailed === 0 && integrityResults) {
            console.log('');
            console.log('🎉 ALL TESTS PASSED! Application is ready for production.');
        } else {
            console.log('');
            console.log('⚠️  Some tests failed. Please review the results above.');
        }
        
    } catch (error) {
        console.error('❌ Test execution failed:', error.message);
    }
}

// Run tests
runAllTests();
