/**
 * Authentication Tests
 * Tests login, logout, and session management
 */

const puppeteer = require('puppeteer');

describe('Authentication', () => {
    let browser;
    let page;
    
    beforeAll(async () => {
        browser = await puppeteer.launch({
            headless: 'new',
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        page = await browser.newPage();
        global.page = page;
        
        await page.setViewport(global.testConfig.viewport);
    });
    
    afterAll(async () => {
        if (browser) {
            await browser.close();
        }
    });
    
    describe('Login Flow', () => {
        test('should display login page', async () => {
            await page.goto(`${global.testConfig.baseUrl}/login.php`);
            
            // Check if login page elements are present
            await expect(page).toMatchElement('#username');
            await expect(page).toMatchElement('#password');
            await expect(page).toMatchElement('button[type="submit"]');
            
            // Check page title
            const title = await page.title();
            expect(title).toContain('Login');
            
            console.log('✅ Login page loaded successfully');
        });
        
        test('should show error with invalid credentials', async () => {
            await page.goto(`${global.testConfig.baseUrl}/login.php`);
            
            // Try invalid login
            await global.testUtils.typeText(page, '#username', 'invalid');
            await global.testUtils.typeText(page, '#password', 'invalid');
            await global.testUtils.clickElement(page, 'button[type="submit"]');
            
            // Wait for response
            await global.testUtils.delay(2000);
            
            // Check if still on login page (failed login)
            const currentUrl = page.url();
            expect(currentUrl).toContain('login.php');
            
            console.log('✅ Invalid credentials properly rejected');
        });
        
        test('should login successfully with valid credentials', async () => {
            await global.testUtils.login(page);
            
            // Verify we're logged in
            const currentUrl = page.url();
            expect(currentUrl).toMatch(/main\.php|dashboard/);
            
            // Check for dashboard elements
            await expect(page).toMatchElement('.main-content', { timeout: 5000 });
            
            console.log('✅ Login successful with valid credentials');
        });
        
        test('should maintain session across page navigation', async () => {
            // Navigate to different pages
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`);
            await page.waitForSelector('body', { timeout: 5000 });
            
            // Should not redirect to login
            const currentUrl = page.url();
            expect(currentUrl).not.toContain('login.php');
            
            // Navigate to bagian page
            await page.goto(`${global.testConfig.baseUrl}/pages/bagian.php`);
            await page.waitForSelector('body', { timeout: 5000 });
            
            // Should still be logged in
            const bagianUrl = page.url();
            expect(bagianUrl).not.toContain('login.php');
            
            console.log('✅ Session maintained across navigation');
        });
    });
    
    describe('Logout Flow', () => {
        test('should logout successfully', async () => {
            await global.testUtils.logout(page);
            
            // Should be redirected to login page
            const currentUrl = page.url();
            expect(currentUrl).toContain('login.php');
            
            console.log('✅ Logout successful');
        });
        
        test('should require login after logout', async () => {
            // Try to access protected page after logout
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`);
            
            // Should redirect to login
            await page.waitForNavigation({ timeout: 5000 });
            const currentUrl = page.url();
            expect(currentUrl).toContain('login.php');
            
            console.log('✅ Login required after logout');
        });
    });
    
    describe('Session Security', () => {
        test('should handle session timeout', async () => {
            await global.testUtils.login(page);
            
            // Clear cookies to simulate session timeout
            await page.deleteCookie(...await page.cookies());
            
            // Try to access protected page
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`);
            
            // Should redirect to login
            await page.waitForNavigation({ timeout: 5000 });
            const currentUrl = page.url();
            expect(currentUrl).toContain('login.php');
            
            console.log('✅ Session timeout handled correctly');
        });
    });
});
