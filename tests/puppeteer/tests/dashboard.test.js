/**
 * Dashboard and Navigation Tests
 * Testing main dashboard and menu navigation
 */

const config = require('../config');

function dashboardTests(runner) {
    return {
        // Test 1: Dashboard loads after login
        async testDashboardLoads() {
            await runner.test('Dashboard Loads', async (page) => {
                // Use login helper
                await runner.login();
                
                // Check dashboard content - look for actual elements
                await runner.waitForTimeout(2000); // Wait for content to load
                
                const content = await page.content();
                if (!content.includes('Dashboard') && !content.includes('Sistem Manajemen') && !content.includes('POLRES SAMOSIR')) {
                    throw new Error('Dashboard content not found');
                }
                
                await runner.screenshot('dashboard');
            });
        },
        
        // Test 2: Statistics load
        async testStatisticsLoad() {
            await runner.test('Statistics Load', async (page) => {
                // Use login helper
                await runner.login();
                
                // Wait for stats to load
                await runner.waitForTimeout(3000);
                
                // Check stat numbers are loaded using multiple selectors
                const hasStats = await page.evaluate(() => {
                    const selectors = [
                        '.stat-number',
                        '.stats h3',
                        '#totalPersonil',
                        '#polriCount',
                        '.stat-box h3',
                        'h3'
                    ];
                    
                    for (const selector of selectors) {
                        const elements = document.querySelectorAll(selector);
                        if (elements.length > 0) {
                            // Check if any element has numeric content
                            for (const el of elements) {
                                if (/\d/.test(el.textContent)) {
                                    return true;
                                }
                            }
                        }
                    }
                    return false;
                });
                
                if (!hasStats) {
                    throw new Error('Statistics not loaded');
                }
                
                await runner.screenshot('dashboard_stats');
            });
        },
        
        // Test 3: Navigation menu works
        async testNavigationMenu() {
            await runner.test('Navigation Menu', async (page) => {
                // Login
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                // Check navbar exists
                const hasNavbar = await runner.isVisible('.navbar, nav');
                if (!hasNavbar) {
                    throw new Error('Navigation menu not found');
                }
                
                // Check menu items
                const menuItems = await page.evaluate(() => {
                    const links = document.querySelectorAll('.navbar a, nav a');
                    return Array.from(links).map(a => a.textContent.trim());
                });
                
                console.log('   📋 Menu items found:', menuItems.slice(0, 5).join(', '), '...');
                
                await runner.screenshot('navigation_menu');
            });
        },
        
        // Test 4: Navigate to Personil page
        async testNavigateToPersonil() {
            await runner.test('Navigate to Personil Page', async (page) => {
                // Login
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                // Click Personil menu
                const personilLink = await page.$('a[href*="personil.php"]');
                if (personilLink) {
                    await personilLink.click();
                    await page.waitForNavigation({ waitUntil: 'networkidle0' });
                    
                    if (!page.url().includes('personil.php')) {
                        throw new Error('Did not navigate to personil page');
                    }
                } else {
                    // Direct navigation
                    await page.goto(`${config.baseUrl}/pages/personil.php`);
                }
                
                await runner.screenshot('personil_page');
            });
        },
        
        // Test 5: Navigate to Bagian page
        async testNavigateToBagian() {
            await runner.test('Navigate to Bagian Page', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                await page.goto(`${config.baseUrl}/pages/bagian.php`);
                
                const content = await page.content();
                if (!content.includes('Bagian') && !content.includes('bagian')) {
                    throw new Error('Bagian page content not found');
                }
                
                await runner.screenshot('bagian_page');
            });
        },
        
        // Test 6: Navigate to Unsur page
        async testNavigateToUnsur() {
            await runner.test('Navigate to Unsur Page', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                await page.goto(`${config.baseUrl}/pages/unsur.php`);
                
                const content = await page.content();
                if (!content.includes('Unsur') && !content.includes('unsur')) {
                    throw new Error('Unsur page content not found');
                }
                
                await runner.screenshot('unsur_page');
            });
        },
        
        // Test 7: Navigate to Jabatan page
        async testNavigateToJabatan() {
            await runner.test('Navigate to Jabatan Page', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                await page.goto(`${config.baseUrl}/pages/jabatan.php`);
                
                const content = await page.content();
                if (!content.includes('Jabatan') && !content.includes('jabatan')) {
                    throw new Error('Jabatan page content not found');
                }
                
                await runner.screenshot('jabatan_page');
            });
        },
        
        // Test 8: Navigate to Calendar page
        async testNavigateToCalendar() {
            await runner.test('Navigate to Calendar Page', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                await page.goto(`${config.baseUrl}/pages/calendar_dashboard.php`);
                
                const content = await page.content();
                if (!content.includes('Calendar') && !content.includes('calendar') && 
                    !content.includes('Jadwal') && !content.includes('jadwal')) {
                    throw new Error('Calendar page content not found');
                }
                
                await runner.screenshot('calendar_page');
            });
        }
    };
}

module.exports = dashboardTests;
