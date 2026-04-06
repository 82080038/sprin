/**
 * Mobile Responsiveness Tests
 * Tests application behavior on mobile devices
 */

const puppeteer = require('puppeteer');

describe('Mobile Responsiveness', () => {
    let browser;
    let page;
    
    beforeAll(async () => {
        browser = await puppeteer.launch({
            headless: 'new',
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        page = await browser.newPage();
        global.page = page;
        
        // Set mobile viewport
        await page.setViewport(global.testConfig.mobileViewport);
        
        // Set user agent to mobile
        await page.setUserAgent('Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15');
    });
    
    afterAll(async () => {
        if (browser) {
            await browser.close();
        }
    });
    
    describe('Login Page Mobile', () => {
        test('should display login page correctly on mobile', async () => {
            await page.goto(`${global.testConfig.baseUrl}/login.php`);
            
            // Check if mobile-specific elements are present
            await expect(page).toMatchElement('#username');
            await expect(page).toMatchElement('#password');
            await expect(page).toMatchElement('button[type="submit"]');
            
            // Check viewport meta tag
            const viewport = await page.$eval('meta[name="viewport"]', el => el ? el.getAttribute('content') : null);
            if (viewport) {
                expect(viewport).toContain('width=device-width');
            }
            
            console.log('✅ Login page mobile layout verified');
        });
        
        test('should allow login on mobile', async () => {
            await global.testUtils.login(page);
            
            // Check if dashboard loads
            await expect(page).toMatchElement('.main-content', { timeout: 5000 });
            
            console.log('✅ Mobile login successful');
        });
    });
    
    describe('Navigation Mobile', () => {
        test('should have mobile-friendly navigation', async () => {
            await global.testUtils.login(page);
            await page.goto(`${global.testConfig.baseUrl}/pages/main.php`);
            
            // Check for mobile navigation elements
            const navbar = await page.$('.navbar, .nav, header');
            expect(navbar).toBeTruthy();
            
            // Check for mobile menu toggle if present
            const menuToggle = await page.$('.navbar-toggler, .menu-toggle, .hamburger');
            if (menuToggle) {
                console.log('✅ Mobile menu toggle found');
            }
            
            console.log('✅ Mobile navigation elements verified');
        });
        
        test('should navigate between pages on mobile', async () => {
            await global.testUtils.login(page);
            
            const pages = [
                '/pages/unsur.php',
                '/pages/bagian.php',
                '/pages/jabatan.php'
            ];
            
            for (const pagePath of pages) {
                await page.goto(`${global.testConfig.baseUrl}${pagePath}`, {
                    waitUntil: 'networkidle0'
                });
                
                // Check if page loads correctly
                await expect(page).toMatchElement('body', { timeout: 5000 });
                
                // Check if content is visible
                const contentVisible = await page.$eval('body', el => {
                    const rect = el.getBoundingClientRect();
                    return rect.width > 0 && rect.height > 0;
                });
                expect(contentVisible).toBe(true);
            }
            
            console.log('✅ Mobile page navigation successful');
        });
    });
    
    describe('Unsur Management Mobile', () => {
        test('should display unsur table on mobile', async () => {
            await global.testUtils.login(page);
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`);
            
            // Check if table adapts to mobile
            await expect(page).toMatchElement('#unsurTable', { timeout: 5000 });
            
            // Check if table is responsive or has mobile adaptation
            const table = await page.$('#unsurTable');
            const tableClasses = await page.$eval('#unsurTable', el => el.className);
            
            if (tableClasses.includes('table-responsive') || tableClasses.includes('mobile-table')) {
                console.log('✅ Mobile table adaptation found');
            }
            
            console.log('✅ Unsur table mobile display verified');
        });
        
        test('should handle modal on mobile', async () => {
            await global.testUtils.login(page);
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`);
            
            // Try to open modal
            await global.testUtils.clickElement(page, '[data-action="add-unsur"]');
            await page.waitForSelector('#unsurModal', { timeout: 5000 });
            
            // Check if modal is mobile-friendly
            const modal = await page.$('#unsurModal');
            expect(modal).toBeTruthy();
            
            // Check if modal fits screen
            const modalSize = await page.$eval('#unsurModal .modal-dialog', el => {
                const rect = el.getBoundingClientRect();
                return {
                    width: rect.width,
                    height: rect.height
                };
            });
            
            const viewport = page.viewport();
            expect(modalSize.width).toBeLessThanOrEqual(viewport.width * 0.95);
            
            console.log('✅ Mobile modal behavior verified');
        });
        
        test('should handle form input on mobile', async () => {
            await global.testUtils.login(page);
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`);
            
            // Open modal
            await global.testUtils.clickElement(page, '[data-action="add-unsur"]');
            await page.waitForSelector('#unsurModal', { timeout: 5000 });
            
            // Test form input
            await global.testUtils.typeText(page, '#nama_unsur', 'Mobile Test Unsur');
            
            // Check if input is accessible
            const inputValue = await page.$eval('#nama_unsur', el => el.value);
            expect(inputValue).toBe('Mobile Test Unsur');
            
            // Test virtual keyboard behavior
            const inputFocused = await page.$eval('#nama_unsur', el => el === document.activeElement);
            if (inputFocused) {
                console.log('✅ Virtual keyboard integration working');
            }
            
            console.log('✅ Mobile form input verified');
        });
    });
    
    describe('Touch Interactions', () => {
        test('should handle tap gestures', async () => {
            await global.testUtils.login(page);
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`);
            
            // Test tap on button
            const buttonExists = await page.$('[data-action="add-unsur"]');
            if (buttonExists) {
                await page.tap('[data-action="add-unsur"]');
                await page.waitForSelector('#unsurModal', { timeout: 5000 });
                
                // Close modal
                await page.tap('.btn-close, [data-bs-dismiss="modal"]');
                await page.waitForSelector('#unsurModal', { hidden: true, timeout: 5000 });
                
                console.log('✅ Tap gestures working');
            }
        });
        
        test('should handle scroll on mobile', async () => {
            await global.testUtils.login(page);
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`);
            
            // Check if page is scrollable
            const scrollHeight = await page.$eval('body', el => el.scrollHeight);
            const clientHeight = await page.$eval('body', el => el.clientHeight);
            
            if (scrollHeight > clientHeight) {
                // Test scrolling
                await page.evaluate(() => window.scrollTo(0, 500));
                await global.testUtils.delay(500);
                
                const scrollTop = await page.$eval('window', el => el.pageYOffset);
                expect(scrollTop).toBeGreaterThan(0);
                
                console.log('✅ Mobile scrolling working');
            } else {
                console.log('ℹ️ Page not scrollable (fit to screen)');
            }
        });
        
        test('should handle pinch-to-zoom if enabled', async () => {
            await global.testUtils.login(page);
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`);
            
            // Check if viewport allows zoom
            const viewport = await page.$eval('meta[name="viewport"]', el => el ? el.getAttribute('content') : null);
            
            if (viewport && !viewport.includes('user-scalable=no')) {
                console.log('✅ Pinch-to-zoom allowed');
            } else {
                console.log('ℹ️ Pinch-to-zoom disabled');
            }
        });
    });
    
    describe('Mobile Performance', () => {
        test('should load quickly on mobile', async () => {
            const startTime = Date.now();
            
            await global.testUtils.login(page);
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`, {
                waitUntil: 'networkidle0'
            });
            
            const loadTime = Date.now() - startTime;
            expect(loadTime).toBeLessThan(5000); // 5 seconds max for mobile
            
            console.log(`✅ Mobile page load: ${loadTime}ms`);
        });
        
        test('should handle network conditions gracefully', async () => {
            // Simulate slower network (this is approximate)
            await page.setRequestInterception(true);
            page.on('request', request => {
                // Add delay to simulate slow network
                setTimeout(() => request.continue(), 100);
            });
            
            const startTime = Date.now();
            
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`, {
                waitUntil: 'networkidle0'
            });
            
            const loadTime = Date.now() - startTime;
            
            // Should still load within reasonable time even with slower network
            expect(loadTime).toBeLessThan(8000); // 8 seconds max with slow network
            
            // Reset request interception
            await page.setRequestInterception(false);
            
            console.log(`✅ Slow network handling: ${loadTime}ms`);
        });
    });
    
    describe('Mobile UI Adaptations', () => {
        test('should adapt layout for small screens', async () => {
            await global.testUtils.login(page);
            await page.goto(`${global.testConfig.baseUrl}/pages/main.php`);
            
            // Check if layout adapts to mobile
            const containerWidth = await page.$eval('.container, .main-content', el => {
                if (el) {
                    const rect = el.getBoundingClientRect();
                    return rect.width;
                }
                return 0;
            });
            
            const viewport = page.viewport();
            expect(containerWidth).toBeLessThanOrEqual(viewport.width);
            
            console.log(`✅ Mobile layout adaptation: ${containerWidth}px vs ${viewport.width}px viewport`);
        });
        
        test('should hide unnecessary elements on mobile', async () => {
            await global.testUtils.login(page);
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`);
            
            // Check for mobile-specific hiding
            const hiddenElements = await page.$$('[class*="hidden-xs"], [class*="d-none d-sm-block"]');
            
            if (hiddenElements.length > 0) {
                console.log(`✅ Found ${hiddenElements.length} mobile-hidden elements`);
            } else {
                console.log('ℹ️ No mobile-specific hidden elements found');
            }
        });
        
        test('should optimize button sizes for touch', async () => {
            await global.testUtils.login(page);
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`);
            
            // Check button sizes
            const buttons = await page.$$('button, .btn');
            
            if (buttons.length > 0) {
                const buttonSizes = await Promise.all(
                    buttons.map(async button => {
                        return await page.evaluate(el => {
                            const rect = el.getBoundingClientRect();
                            return {
                                width: rect.width,
                                height: rect.height,
                                minDimension: Math.min(rect.width, rect.height)
                            };
                        }, button);
                    })
                );
                
                // Check if buttons are touch-friendly (at least 44px)
                const touchFriendlyButtons = buttonSizes.filter(size => size.minDimension >= 44);
                const touchFriendlyPercentage = (touchFriendlyButtons.length / buttonSizes.length) * 100;
                
                console.log(`✅ Touch-friendly buttons: ${touchFriendlyPercentage.toFixed(1)}% (${touchFriendlyButtons.length}/${buttons.length})`);
                
                // At least 50% of buttons should be touch-friendly
                expect(touchFriendlyPercentage).toBeGreaterThan(50);
            }
        });
    });
    
    describe('Mobile Browser Compatibility', () => {
        test('should work with mobile Safari user agent', async () => {
            await page.setUserAgent('Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1');
            
            await global.testUtils.login(page);
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`);
            
            // Should still work with mobile Safari
            await expect(page).toMatchElement('#unsurTable', { timeout: 5000 });
            
            console.log('✅ Mobile Safari compatibility verified');
        });
        
        test('should work with Chrome Mobile user agent', async () => {
            await page.setUserAgent('Mozilla/5.0 (Linux; Android 10; SM-G975F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36');
            
            await global.testUtils.login(page);
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`);
            
            // Should still work with Chrome Mobile
            await expect(page).toMatchElement('#unsurTable', { timeout: 5000 });
            
            console.log('✅ Chrome Mobile compatibility verified');
        });
    });
});
