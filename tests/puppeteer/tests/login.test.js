/**
 * Login Tests
 * Testing authentication functionality
 */

const config = require('../config');

function loginTests(runner) {
    return {
        // Test 1: Page loads correctly
        async testLoginPageLoads() {
            await runner.test('Login Page Loads', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.waitForSelector('input#username');
                await runner.waitForSelector('input#password');
                await runner.screenshot('login_page');
            });
        },
        
        // Test 2: Login with valid credentials
        async testValidLogin() {
            await runner.test('Valid Login', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                
                // Wait for redirect to main page (shorter timeout)
                try {
                    await page.waitForNavigation({ 
                        waitUntil: 'networkidle0',
                        timeout: config.timeouts.navigation 
                    });
                } catch (e) {
                    // Check if we're already on main page
                    const url = page.url();
                    if (url.includes('main.php')) {
                        await runner.log('Already redirected to main page');
                    } else {
                        throw e;
                    }
                }
                
                // Verify we're on main page
                const url = page.url();
                if (!url.includes('main.php')) {
                    throw new Error('Not redirected to main page. Current URL: ' + url);
                }
                
                await runner.screenshot('after_login');
            });
        },
        
        // Test 3: Login with invalid credentials
        async testInvalidLogin() {
            await runner.test('Invalid Login - Wrong Password', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, 'wrongpassword');
                await runner.click(config.selectors.login.submitButton);
                
                // Wait for error message
                await runner.waitForSelector('.alert-danger, .alert, .error-message');
                await runner.screenshot('login_error');
            });
        },
        
        // Test 4: Quick login button
        async testQuickLogin() {
            await runner.test('Quick Login Button', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                
                // Check if quick login button exists and click
                const hasQuickLogin = await runner.isVisible(config.selectors.login.quickLoginButton);
                if (hasQuickLogin) {
                    await runner.click(config.selectors.login.quickLoginButton);
                    await page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 10000 });
                    
                    const url = page.url();
                    if (!url.includes('main.php')) {
                        throw new Error('Quick login did not redirect to main page');
                    }
                } else {
                    console.log('   ℹ️ Quick login button not found, skipping');
                }
            });
        },
        
        // Test 5: Landing page loads
        async testLandingPage() {
            await runner.test('Landing Page', async (page) => {
                await page.goto(`${config.baseUrl}/index.php`);
                
                // Check for welcome content
                const content = await page.content();
                if (!content.includes('POLRES') && !content.includes('Samosir')) {
                    throw new Error('Landing page content not found');
                }
                
                await runner.screenshot('landing_page');
            });
        },
        
        // Test 6: Logout functionality
        async testLogout() {
            await runner.test('Logout', async (page) => {
                // First login
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                // Then logout
                await runner.click(config.selectors.navigation.logoutButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                // Verify redirected to login or index
                const url = page.url();
                if (!url.includes('login.php') && !url.includes('index.php')) {
                    throw new Error('Not redirected after logout');
                }
                
                await runner.screenshot('after_logout');
            });
        }
    };
}

module.exports = loginTests;
