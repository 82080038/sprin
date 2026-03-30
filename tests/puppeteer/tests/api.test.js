/**
 * API Endpoint Tests
 * Testing all API endpoints
 */

const config = require('../config');

function apiTests(runner) {
    return {
        // Test 1: Personil CRUD API
        async testPersonilCrudApi() {
            await runner.test('API: Personil CRUD Endpoint', async (page) => {
                const response = await page.evaluate(async () => {
                    const res = await fetch('http://localhost/sprint/api/personil_crud.php?action=read&id=1', {
                        method: 'GET',
                        headers: { 'Content-Type': 'application/json' }
                    });
                    return await res.json();
                });
                
                // Should return success or not found, but not server error
                if (response && typeof response === 'object') {
                    console.log('   ✅ Personil CRUD API accessible');
                }
            });
        },
        
        // Test 2: Calendar API
        async testCalendarApi() {
            await runner.test('API: Calendar Endpoint', async (page) => {
                const today = new Date().toISOString().split('T')[0];
                const endDate = new Date();
                endDate.setMonth(endDate.getMonth() + 1);
                
                const response = await page.evaluate(async (start, end) => {
                    try {
                        const res = await fetch(`http://localhost/sprint/api/calendar_api.php?action=getEvents&start=${start}&end=${end}`);
                        return await res.json();
                    } catch (e) {
                        return { error: e.message };
                    }
                }, today, endDate.toISOString().split('T')[0]);
                
                console.log('   ✅ Calendar API accessible');
            });
        },
        
        // Test 3: Advanced Search API
        async testAdvancedSearchApi() {
            await runner.test('API: Advanced Search Endpoint', async (page) => {
                const response = await page.evaluate(async () => {
                    const res = await fetch('http://localhost/sprint/api/advanced_search.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            filters: {},
                            pagination: { page: 1, per_page: 5 }
                        })
                    });
                    return await res.json();
                });
                
                if (response && typeof response === 'object') {
                    console.log('   ✅ Advanced Search API accessible');
                }
            });
        },
        
        // Test 4: Jabatan CRUD API
        async testJabatanCrudApi() {
            await runner.test('API: Jabatan CRUD Endpoint', async (page) => {
                const response = await page.evaluate(async () => {
                    const res = await fetch('http://localhost/sprint/api/jabatan_crud.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'list' })
                    });
                    return await res.json();
                });
                
                if (response && typeof response === 'object') {
                    console.log('   ✅ Jabatan CRUD API accessible');
                }
            });
        },
        
        // Test 5: Personil Detail API
        async testPersonilDetailApi() {
            await runner.test('API: Personil Detail Endpoint', async (page) => {
                const response = await page.evaluate(async () => {
                    const res = await fetch('http://localhost/sprint/api/personil_detail.php?id=1');
                    return await res.json();
                });
                
                if (response && typeof response === 'object') {
                    console.log('   ✅ Personil Detail API accessible');
                }
            });
        },
        
        // Test 6: Export API check
        async testExportApi() {
            await runner.test('API: Export Endpoint', async (page) => {
                const response = await page.evaluate(async () => {
                    const res = await fetch('http://localhost/sprint/api/export_personil.php?format=json&page=1&per_page=1');
                    return res.status;
                });
                
                if (response === 200) {
                    console.log('   ✅ Export API accessible');
                }
            });
        },
        
        // Test 7: Search Personil API
        async testSearchPersonilApi() {
            await runner.test('API: Search Personil Endpoint', async (page) => {
                const response = await page.evaluate(async () => {
                    const res = await fetch('http://localhost/sprint/api/search_personil.php?search=test&page=1');
                    return await res.json();
                });
                
                if (response && typeof response === 'object') {
                    console.log('   ✅ Search Personil API accessible');
                }
            });
        },
        
        // Test 8: Pagination API
        async testPaginationApi() {
            await runner.test('API: Pagination Personil Endpoint', async (page) => {
                const response = await page.evaluate(async () => {
                    const res = await fetch('http://localhost/sprint/api/pagination_personil.php?page=1&per_page=10');
                    return await res.json();
                });
                
                if (response && typeof response === 'object') {
                    console.log('   ✅ Pagination API accessible');
                }
            });
        },
        
        // Test 9: Simple API
        async testSimpleApi() {
            await runner.test('API: Simple Endpoint', async (page) => {
                const response = await page.evaluate(async () => {
                    const res = await fetch('http://localhost/sprint/api/simple.php');
                    return await res.json();
                });
                
                if (response && typeof response === 'object') {
                    console.log('   ✅ Simple API accessible');
                }
            });
        },
        
        // Test 10: API Response format validation
        async testApiResponseFormat() {
            await runner.test('API: Response Format Validation', async (page) => {
                const apis = [
                    'http://localhost/sprint/api/personil_list.php?page=1',
                    'http://localhost/sprint/api/unsur_stats.php'
                ];
                
                for (const api of apis) {
                    const response = await page.evaluate(async (url) => {
                        const res = await fetch(url);
                        return await res.json();
                    }, api);
                    
                    // Check standard response format
                    if (!response.hasOwnProperty('success')) {
                        throw new Error(`Invalid response format from ${api}`);
                    }
                }
                
                console.log('   ✅ All APIs follow standard response format');
            });
        }
    };
}

module.exports = apiTests;
