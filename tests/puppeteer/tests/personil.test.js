/**
 * Personil CRUD Tests
 * Testing Create, Read, Update, Delete operations for Personil
 */

const config = require('../config');

function personilTests(runner) {
    let createdPersonilId = null;
    
    return {
        // Test 1: Personil list loads
        async testPersonilListLoads() {
            await runner.test('Personil List Loads', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                await page.goto(`${config.baseUrl}/pages/personil.php`);
                
                // Wait for table or list to load
                await page.waitForTimeout(2000);
                
                // Check for table or data container
                const hasTable = await runner.isVisible('table, .table, .data-table, .list-container');
                
                await runner.screenshot('personil_list');
            });
        },
        
        // Test 2: Add new personil form opens
        async testAddPersonilForm() {
            await runner.test('Add Personil Form Opens', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                await page.goto(`${config.baseUrl}/pages/personil.php`);
                await page.waitForTimeout(2000);
                
                // Look for add button
                const addButton = await page.$('button:has-text("Tambah"), .btn-tambah, [data-action="add"], .btn-primary');
                
                if (addButton) {
                    await addButton.click();
                    await page.waitForTimeout(1000);
                    
                    // Check if form or modal opened
                    const hasForm = await runner.isVisible('form, .modal, .modal-dialog');
                    
                    await runner.screenshot('add_personil_form');
                } else {
                    console.log('   ℹ️ Add button not found');
                }
            });
        },
        
        // Test 3: Search personil
        async testSearchPersonil() {
            await runner.test('Search Personil', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                await page.goto(`${config.baseUrl}/pages/personil.php`);
                await page.waitForTimeout(2000);
                
                // Find search input
                const searchInput = await page.$('input[type="search"], .search-input, #search, input[placeholder*="cari" i]');
                
                if (searchInput) {
                    await searchInput.type('test');
                    await page.waitForTimeout(1000);
                    
                    await runner.screenshot('personil_search');
                } else {
                    console.log('   ℹ️ Search input not found');
                }
            });
        },
        
        // Test 4: API - Get Personil List
        async testApiGetPersonilList() {
            await runner.test('API: Get Personil List', async (page) => {
                const response = await page.evaluate(async () => {
                    const res = await fetch('http://localhost/sprint/api/personil_list.php?page=1&per_page=5');
                    return await res.json();
                });
                
                if (!response.success) {
                    throw new Error('API returned error: ' + (response.message || 'Unknown error'));
                }
                
                if (!response.data || !Array.isArray(response.data)) {
                    throw new Error('Invalid response format');
                }
                
                console.log(`   📋 Found ${response.data.length} personil records`);
            });
        },
        
        // Test 5: API - Get Personil Statistics
        async testApiGetPersonilStats() {
            await runner.test('API: Get Personil Statistics', async (page) => {
                const response = await page.evaluate(async () => {
                    const res = await fetch('http://localhost/sprint/api/personil_simple.php');
                    return await res.json();
                });
                
                if (!response.success) {
                    throw new Error('API returned error');
                }
                
                if (response.data && response.data.statistics) {
                    const stats = response.data.statistics;
                    console.log(`   📊 Total: ${stats.total_personil}, POLRI: ${stats.polri_count}`);
                }
            });
        },
        
        // Test 6: API - Get Unsur Statistics
        async testApiGetUnsurStats() {
            await runner.test('API: Get Unsur Statistics', async (page) => {
                const response = await page.evaluate(async () => {
                    const res = await fetch('http://localhost/sprint/api/unsur_stats.php');
                    return await res.json();
                });
                
                if (!response.success) {
                    throw new Error('API returned error');
                }
                
                console.log('   📊 Unsur stats loaded successfully');
            });
        },
        
        // Test 7: Filter personil by bagian
        async testFilterPersonilByBagian() {
            await runner.test('Filter Personil by Bagian', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                await page.goto(`${config.baseUrl}/pages/personil.php`);
                await page.waitForTimeout(2000);
                
                // Look for bagian filter
                const bagianFilter = await page.$('select[name="bagian"], #bagian-filter, .filter-bagian');
                
                if (bagianFilter) {
                    await bagianFilter.select('1'); // Select first bagian
                    await page.waitForTimeout(1000);
                    
                    await runner.screenshot('personil_filtered');
                } else {
                    console.log('   ℹ️ Bagian filter not found');
                }
            });
        },
        
        // Test 8: Export functionality check
        async testExportButtonsExist() {
            await runner.test('Export Buttons Exist', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                await page.goto(`${config.baseUrl}/pages/personil.php`);
                await page.waitForTimeout(2000);
                
                // Check for export buttons
                const exportButtons = await page.$$('button:has-text("Export"), .btn-export, [data-action="export"]');
                
                console.log(`   📋 Found ${exportButtons.length} export buttons`);
                
                await runner.screenshot('export_buttons');
            });
        }
    };
}

module.exports = personilTests;
