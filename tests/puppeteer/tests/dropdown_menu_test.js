/**
 * Test Dropdown Navigation Menu with Puppeteer
 * SPRIN Application Testing
 */

const puppeteer = require('puppeteer');
const config = require('../config');

class DropdownMenuTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.screenshotDir = config.output.screenshots;
    }

    async init() {
        console.log('🧭 Starting Dropdown Menu Test...');
        this.browser = await puppeteer.launch(config.browser);
        this.page = await this.browser.newPage();
        await this.page.setViewport(config.browser.defaultViewport);
        
        // Setup console error tracking
        this.page.on('console', msg => {
            if (msg.type() === 'error') {
                console.log('Browser console error:', msg.text());
            }
        });
    }

    async takeScreenshot(name, description = '') {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filename = `${name}_${timestamp}.png`;
        const filepath = `${this.screenshotDir}/${filename}`;
        
        await this.page.screenshot({ 
            path: filepath, 
            fullPage: false 
        });
        
        console.log(`📸 Screenshot saved: ${filename} - ${description}`);
        return filepath;
    }

    async login() {
        console.log('🔐 Logging in...');
        try {
            await this.page.goto(config.baseUrl + '/login.php', { 
                waitUntil: 'networkidle2',
                timeout: config.timeouts.navigation 
            });
            
            // Quick login
            const quickLoginButton = await this.page.$(config.selectors.login.quickLoginButton);
            if (quickLoginButton) {
                await quickLoginButton.click();
                await this.page.waitForNavigation({ waitUntil: 'networkidle2' });
            }
            
            const currentUrl = this.page.url();
            if (currentUrl.includes('main.php') || currentUrl.includes('pages/')) {
                console.log('✅ Login successful');
                await this.takeScreenshot('dropdown_login_success', 'Login successful - Dashboard');
                return true;
            }
            return false;
        } catch (error) {
            console.error('❌ Login error:', error.message);
            return false;
        }
    }

    async testDropdownElements() {
        console.log('🔍 Testing dropdown elements existence...');
        
        try {
            const dropdownElements = await this.page.evaluate(() => {
                const dropdowns = document.querySelectorAll('a[data-bs-toggle="dropdown"]');
                const dropdownInfo = [];
                
                dropdowns.forEach((dropdown, index) => {
                    const text = dropdown.textContent.trim();
                    const hasDropdownToggle = dropdown.hasAttribute('data-bs-toggle');
                    const parent = dropdown.closest('.nav-item');
                    const hasDropdownMenu = parent ? parent.querySelector('.dropdown-menu') : null;
                    
                    dropdownInfo.push({
                        index: index,
                        text: text,
                        hasDropdownToggle: hasDropdownToggle,
                        hasDropdownMenu: !!hasDropdownMenu,
                        menuItems: hasDropdownMenu ? hasDropdownMenu.querySelectorAll('.dropdown-item').length : 0
                    });
                });
                
                return dropdownInfo;
            });
            
            console.log('Found dropdowns:', dropdownElements);
            
            if (dropdownElements.length > 0) {
                console.log(`✅ Found ${dropdownElements.length} dropdown elements`);
                this.testResults.push({ 
                    test: 'Dropdown Elements Exist', 
                    status: 'PASS', 
                    data: dropdownElements 
                });
                
                await this.takeScreenshot('dropdown_elements_found', 'Dropdown elements found');
                return dropdownElements;
            } else {
                console.log('❌ No dropdown elements found');
                this.testResults.push({ 
                    test: 'Dropdown Elements Exist', 
                    status: 'FAIL', 
                    error: 'No dropdown elements found' 
                });
                return [];
            }
        } catch (error) {
            console.error('❌ Dropdown elements test error:', error.message);
            this.testResults.push({ 
                test: 'Dropdown Elements Exist', 
                status: 'FAIL', 
                error: error.message 
            });
            return [];
        }
    }

    async testDropdownClick(dropdownInfo) {
        console.log('🖱️ Testing dropdown click interactions...');
        
        for (const dropdown of dropdownInfo) {
            try {
                console.log(`Testing dropdown: "${dropdown.text}"`);
                
                // Click dropdown using CSS selector
                const dropdownSelector = `a[data-bs-toggle="dropdown"]:contains("${dropdown.text}")`;
                
                // Try different approaches to find and click the dropdown
                const clicked = await this.page.evaluate((text) => {
                    const dropdowns = document.querySelectorAll('a[data-bs-toggle="dropdown"]');
                    for (const dropdown of dropdowns) {
                        if (dropdown.textContent.trim().includes(text)) {
                            dropdown.click();
                            return true;
                        }
                    }
                    return false;
                }, dropdown.text);
                
                if (clicked) {
                    console.log(`✅ Successfully clicked dropdown: "${dropdown.text}"`);
                    
                    // Wait for dropdown menu to appear
                    await this.page.waitForFunction(
                        () => document.querySelector('.dropdown-menu.show') !== null,
                        { timeout: 2000 }
                    );
                    
                    // Check if dropdown menu is visible
                    const menuVisible = await this.page.evaluate(() => {
                        const visibleMenu = document.querySelector('.dropdown-menu.show');
                        if (visibleMenu) {
                            return {
                                visible: true,
                                itemCount: visibleMenu.querySelectorAll('.dropdown-item').length,
                                menuItems: Array.from(visibleMenu.querySelectorAll('.dropdown-item')).map(item => ({
                                    text: item.textContent.trim(),
                                    href: item.getAttribute('href')
                                }))
                            };
                        }
                        return { visible: false };
                    });
                    
                    if (menuVisible.visible) {
                        console.log(`  ✅ Menu visible with ${menuVisible.itemCount} items`);
                        console.log(`  📋 Menu items:`, menuVisible.menuItems);
                        
                        await this.takeScreenshot(`dropdown_${dropdown.text.toLowerCase().replace(' ', '_')}_open`, `Dropdown "${dropdown.text}" opened`);
                        
                        this.testResults.push({ 
                            test: `Dropdown Click - ${dropdown.text}`, 
                            status: 'PASS', 
                            data: menuVisible 
                        });
                        
                        // Close dropdown by clicking elsewhere
                        await this.page.click('body');
                        await new Promise(resolve => setTimeout(resolve, 500));
                        
                    } else {
                        console.log(`  ❌ Menu not visible after click`);
                        this.testResults.push({ 
                            test: `Dropdown Click - ${dropdown.text}`, 
                            status: 'FAIL', 
                            error: 'Menu not visible after click' 
                        });
                    }
                } else {
                    console.log(`❌ Failed to click dropdown: "${dropdown.text}"`);
                    this.testResults.push({ 
                        test: `Dropdown Click - ${dropdown.text}`, 
                        status: 'FAIL', 
                        error: 'Failed to click dropdown' 
                    });
                }
                
            } catch (error) {
                console.error(`❌ Error testing dropdown "${dropdown.text}":`, error.message);
                this.testResults.push({ 
                    test: `Dropdown Click - ${dropdown.text}`, 
                    status: 'FAIL', 
                    error: error.message 
                });
            }
        }
    }

    async testBootstrapDropdowns() {
        console.log('🅱️ Testing Bootstrap dropdown functionality...');
        
        try {
            const bootstrapStatus = await this.page.evaluate(() => {
                // Check if Bootstrap is loaded
                const bootstrapLoaded = typeof bootstrap !== 'undefined';
                
                // Check if Dropdown component is available
                const dropdownAvailable = bootstrapLoaded && typeof bootstrap.Dropdown !== 'undefined';
                
                // Get all dropdown elements
                const dropdownElements = document.querySelectorAll('[data-bs-toggle="dropdown"]');
                
                // Check if dropdowns have Bootstrap instances
                const dropdownInstances = [];
                dropdownElements.forEach((element, index) => {
                    const instance = bootstrap.Dropdown.getInstance(element);
                    dropdownInstances.push({
                        index: index,
                        text: element.textContent.trim(),
                        hasInstance: !!instance
                    });
                });
                
                return {
                    bootstrapLoaded: bootstrapLoaded,
                    dropdownAvailable: dropdownAvailable,
                    totalDropdowns: dropdownElements.length,
                    dropdownInstances: dropdownInstances
                };
            });
            
            console.log('Bootstrap Dropdown Status:', bootstrapStatus);
            
            if (bootstrapStatus.bootstrapLoaded && bootstrapStatus.dropdownAvailable) {
                console.log('✅ Bootstrap and Dropdown component available');
                this.testResults.push({ 
                    test: 'Bootstrap Dropdown Available', 
                    status: 'PASS', 
                    data: bootstrapStatus 
                });
            } else {
                console.log('❌ Bootstrap Dropdown not available');
                this.testResults.push({ 
                    test: 'Bootstrap Dropdown Available', 
                    status: 'FAIL', 
                    error: 'Bootstrap or Dropdown component not available' 
                });
            }
            
            await this.takeScreenshot('bootstrap_dropdown_status', 'Bootstrap dropdown status check');
            
        } catch (error) {
            console.error('❌ Bootstrap dropdown test error:', error.message);
            this.testResults.push({ 
                test: 'Bootstrap Dropdown Available', 
                status: 'FAIL', 
                error: error.message 
            });
        }
    }

    async testDropdownNavigation() {
        console.log('🧭 Testing dropdown navigation functionality...');
        
        try {
            // Test navigation through dropdown menu items
            const navigationTest = await this.page.evaluate(() => {
                const dropdowns = document.querySelectorAll('a[data-bs-toggle="dropdown"]');
                const results = [];
                
                // Find the "Bagian" dropdown
                const bagianDropdown = Array.from(dropdowns).find(d => 
                    d.textContent.trim().includes('Bagian')
                );
                
                if (bagianDropdown) {
                    // Click the dropdown
                    bagianDropdown.click();
                    
                    // Wait for menu to appear
                    setTimeout(() => {
                        const menu = document.querySelector('.dropdown-menu.show');
                        if (menu) {
                            const bagianMenuItem = Array.from(menu.querySelectorAll('.dropdown-item')).find(item => 
                                item.textContent.trim().includes('Manajemen Bagian')
                            );
                            
                            if (bagianMenuItem) {
                                results.push({
                                    found: true,
                                    menuItem: bagianMenuItem.textContent.trim(),
                                    href: bagianMenuItem.getAttribute('href')
                                });
                            } else {
                                results.push({ found: false, error: 'Manajemen Bagian menu item not found' });
                            }
                        } else {
                            results.push({ found: false, error: 'Dropdown menu not visible' });
                        }
                    }, 500);
                } else {
                    results.push({ found: false, error: 'Bagian dropdown not found' });
                }
                
                return results;
            });
            
            // Wait for evaluation to complete
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            console.log('Navigation test results:', navigationTest);
            
            if (navigationTest.length > 0 && navigationTest[0].found) {
                console.log('✅ Dropdown navigation test passed');
                this.testResults.push({ 
                    test: 'Dropdown Navigation', 
                    status: 'PASS', 
                    data: navigationTest 
                });
            } else {
                console.log('❌ Dropdown navigation test failed');
                this.testResults.push({ 
                    test: 'Dropdown Navigation', 
                    status: 'FAIL', 
                    error: navigationTest[0]?.error || 'Unknown error' 
                });
            }
            
            await this.takeScreenshot('dropdown_navigation_test', 'Dropdown navigation test');
            
        } catch (error) {
            console.error('❌ Dropdown navigation test error:', error.message);
            this.testResults.push({ 
                test: 'Dropdown Navigation', 
                status: 'FAIL', 
                error: error.message 
            });
        }
    }

    async testResponsiveDropdowns() {
        console.log('📱 Testing responsive dropdown behavior...');
        
        const viewports = [
            { width: 1920, height: 1080, name: 'Desktop' },
            { width: 768, height: 1024, name: 'Tablet' },
            { width: 375, height: 667, name: 'Mobile' }
        ];
        
        for (const viewport of viewports) {
            try {
                console.log(`Testing ${viewport.name} (${viewport.width}x${viewport.height})`);
                
                await this.page.setViewport({ width: viewport.width, height: viewport.height });
                await new Promise(resolve => setTimeout(resolve, 500));
                
                // Check if navbar is still visible and functional
                const navbarStatus = await this.page.evaluate(() => {
                    const navbar = document.querySelector('.navbar');
                    const dropdowns = document.querySelectorAll('a[data-bs-toggle="dropdown"]');
                    const toggler = document.querySelector('.navbar-toggler');
                    
                    return {
                        navbarVisible: !!navbar,
                        dropdownCount: dropdowns.length,
                        togglerVisible: !!toggler,
                        navbarCollapsed: navbar ? navbar.classList.contains('navbar-collapse') : false
                    };
                });
                
                console.log(`  ${viewport.name} Status:`, navbarStatus);
                
                if (navbarStatus.navbarVisible && navbarStatus.dropdownCount > 0) {
                    console.log(`  ✅ ${viewport.name}: Navbar and dropdowns functional`);
                    
                    await this.takeScreenshot(`dropdown_responsive_${viewport.name.toLowerCase()}`, `Responsive dropdown test - ${viewport.name}`);
                    
                    this.testResults.push({ 
                        test: `Responsive Dropdown - ${viewport.name}`, 
                        status: 'PASS', 
                        data: navbarStatus 
                    });
                } else {
                    console.log(`  ❌ ${viewport.name}: Navbar or dropdowns not functional`);
                    this.testResults.push({ 
                        test: `Responsive Dropdown - ${viewport.name}`, 
                        status: 'FAIL', 
                        error: 'Navbar or dropdowns not functional' 
                    });
                }
                
            } catch (error) {
                console.error(`❌ Error testing ${viewport.name}:`, error.message);
                this.testResults.push({ 
                    test: `Responsive Dropdown - ${viewport.name}`, 
                    status: 'FAIL', 
                    error: error.message 
                });
            }
        }
        
        // Reset to default viewport
        await this.page.setViewport(config.browser.defaultViewport);
    }

    async generateReport() {
        console.log('📋 Generating dropdown menu test report...');
        
        const timestamp = new Date().toISOString();
        const report = {
            timestamp: timestamp,
            testType: 'Dropdown Navigation Menu Test',
            baseUrl: config.baseUrl,
            results: this.testResults,
            summary: {
                total: this.testResults.length,
                passed: this.testResults.filter(r => r.status === 'PASS').length,
                failed: this.testResults.filter(r => r.status === 'FAIL').length,
                warnings: this.testResults.filter(r => r.status === 'WARNING').length
            }
        };
        
        const reportPath = `${config.output.reports}/dropdown_menu_test_${timestamp.replace(/[:.]/g, '-')}.json`;
        require('fs').writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        console.log(`📄 Report saved: ${reportPath}`);
        console.log(`\n📊 Test Summary:`);
        console.log(`   Total Tests: ${report.summary.total}`);
        console.log(`   ✅ Passed: ${report.summary.passed}`);
        console.log(`   ❌ Failed: ${report.summary.failed}`);
        console.log(`   ⚠️ Warnings: ${report.summary.warnings}`);
        
        return report;
    }

    async cleanup() {
        console.log('🧹 Cleaning up...');
        if (this.page) {
            await this.page.close();
        }
        if (this.browser) {
            await this.browser.close();
        }
    }

    async runFullTest() {
        try {
            await this.init();
            
            const loginSuccess = await this.login();
            if (!loginSuccess) {
                throw new Error('Login failed');
            }
            
            // Run dropdown-specific tests
            const dropdownElements = await this.testDropdownElements();
            
            if (dropdownElements.length > 0) {
                await this.testBootstrapDropdowns();
                await this.testDropdownClick(dropdownElements);
                await this.testDropdownNavigation();
                await this.testResponsiveDropdowns();
            }
            
            // Generate report
            const report = await this.generateReport();
            
            return report;
            
        } catch (error) {
            console.error('❌ Test execution failed:', error.message);
            throw error;
        } finally {
            await this.cleanup();
        }
    }
}

// Run the test
if (require.main === module) {
    const test = new DropdownMenuTest();
    test.runFullTest()
        .then(report => {
            console.log('\n✅ Dropdown menu test completed successfully!');
            process.exit(0);
        })
        .catch(error => {
            console.error('\n❌ Dropdown menu test failed:', error.message);
            process.exit(1);
        });
}

module.exports = DropdownMenuTest;
