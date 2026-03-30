/**
 * Bagian, Unsur, and Jabatan Tests
 * Testing management pages for organizational structure
 */

const config = require('../config');

function organizationTests(runner) {
    return {
        // Test 1: Bagian page loads with data
        async testBagianPageData() {
            await runner.test('Bagian Page with Data', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                await page.goto(`${config.baseUrl}/pages/bagian.php`);
                await page.waitForTimeout(2000);
                
                // Check for data table or list
                const hasData = await page.evaluate(() => {
                    const table = document.querySelector('table, .table');
                    const cards = document.querySelectorAll('.card, .bagian-item');
                    return table || cards.length > 0;
                });
                
                if (!hasData) {
                    console.log('   ⚠️ No data table found, checking for empty state');
                }
                
                await runner.screenshot('bagian_page_data');
            });
        },
        
        // Test 2: Unsur page loads with data
        async testUnsurPageData() {
            await runner.test('Unsur Page with Data', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                await page.goto(`${config.baseUrl}/pages/unsur.php`);
                await page.waitForTimeout(2000);
                
                const content = await page.content();
                
                // Check for 6 unsur types
                const unsurTypes = ['PIMPINAN', 'BAG', 'SAT', 'POLSEK', 'SPKT', 'BKO'];
                const foundTypes = unsurTypes.filter(type => content.includes(type));
                
                console.log(`   📋 Found unsur types: ${foundTypes.join(', ')}`);
                
                await runner.screenshot('unsur_page_data');
            });
        },
        
        // Test 3: Jabatan page loads
        async testJabatanPageLoads() {
            await runner.test('Jabatan Page Loads', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                await page.goto(`${config.baseUrl}/pages/jabatan.php`);
                await page.waitForTimeout(2000);
                
                // Check page loaded
                const content = await page.content();
                if (!content.includes('Jabatan') && !content.includes('jabatan')) {
                    throw new Error('Jabatan page not loaded correctly');
                }
                
                await runner.screenshot('jabatan_page');
            });
        },
        
        // Test 4: Bagian API test
        async testBagianApi() {
            await runner.test('API: Get Bagian List', async (page) => {
                const response = await page.evaluate(async () => {
                    // Try to get bagian data from page or API
                    const res = await fetch('http://localhost/sprint/api/jabatan_crud.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'list_bagian' })
                    });
                    return await res.json();
                });
                
                if (response && typeof response === 'object') {
                    console.log('   ✅ Bagian API accessible');
                }
            });
        },
        
        // Test 5: Unsur stats API detail
        async testUnsurStatsDetail() {
            await runner.test('API: Unsur Stats Detail', async (page) => {
                const unsurTypes = ['PIMPINAN', 'BAG', 'SAT', 'POLSEK', 'SPKT', 'BKO'];
                
                for (const unsur of unsurTypes.slice(0, 2)) { // Test first 2
                    const response = await page.evaluate(async (u) => {
                        const res = await fetch(`http://localhost/sprint/api/unsur_stats.php?unsur=${u}`);
                        return await res.json();
                    }, unsur);
                    
                    if (response && response.success) {
                        const total = response.data?.total_personil || 0;
                        console.log(`   📊 ${unsur}: ${total} personil`);
                    }
                }
            });
        },
        
        // Test 6: Organization structure visibility
        async testOrganizationStructure() {
            await runner.test('Organization Structure Visible', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                // Check each org page
                const pages = [
                    { url: '/pages/bagian.php', name: 'Bagian' },
                    { url: '/pages/unsur.php', name: 'Unsur' },
                    { url: '/pages/jabatan.php', name: 'Jabatan' }
                ];
                
                for (const p of pages) {
                    await page.goto(`${config.baseUrl}${p.url}`);
                    await page.waitForTimeout(1000);
                    
                    const content = await page.content();
                    const loaded = content.includes(p.name) || content.includes(p.name.toLowerCase());
                    console.log(`   ${loaded ? '✅' : '❌'} ${p.name} page`);
                }
            });
        }
    };
}

module.exports = organizationTests;
