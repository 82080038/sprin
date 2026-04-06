/**
 * Jest Setup File
 * Global configuration for all tests
 */

// Set timeout for Puppeteer tests
jest.setTimeout(30000);

// Global test configuration
global.testConfig = {
    baseUrl: 'http://localhost/sprin',
    apiBaseUrl: 'http://localhost/sprin/api',
    timeout: 30000,
    viewport: {
        width: 1920,
        height: 1080
    },
    mobileViewport: {
        width: 375,
        height: 667
    },
    credentials: {
        username: 'bagops',
        password: 'admin123'
    }
};

// Global test utilities
global.testUtils = {
    async delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    },
    
    async waitForElement(page, selector, timeout = 5000) {
        await page.waitForSelector(selector, { timeout });
        return page.$(selector);
    },
    
    async clickElement(page, selector) {
        await page.waitForSelector(selector);
        await page.click(selector);
    },
    
    async typeText(page, selector, text) {
        await page.waitForSelector(selector);
        await page.focus(selector);
        await page.keyboard.down('Control');
        await page.keyboard.press('a');
        await page.keyboard.up('Control');
        await page.type(selector, text);
    },
    
    async takeScreenshot(page, name) {
        const screenshot = await page.screenshot({
            path: `./screenshots/${name}.png`,
            fullPage: true
        });
        return screenshot;
    },
    
    async login(page) {
        await page.goto(`${global.testConfig.baseUrl}/login.php`);
        await global.testUtils.typeText(page, '#username', global.testConfig.credentials.username);
        await global.testUtils.typeText(page, '#password', global.testConfig.credentials.password);
        await global.testUtils.clickElement(page, 'button[type="submit"]');
        await page.waitForNavigation();
        
        // Verify login success
        const currentUrl = page.url();
        if (!currentUrl.includes('main.php') && !currentUrl.includes('dashboard')) {
            throw new Error('Login failed');
        }
        
        console.log('✅ Login successful');
    },
    
    async logout(page) {
        await page.goto(`${global.testConfig.baseUrl}/core/logout.php`);
        await page.waitForNavigation();
        console.log('✅ Logout successful');
    },
    
    async checkApiResponse(url, expectedStatus = 200) {
        try {
            const response = await fetch(url);
            return {
                status: response.status,
                ok: response.ok,
                statusText: response.statusText
            };
        } catch (error) {
            return {
                status: 0,
                ok: false,
                statusText: error.message
            };
        }
    },
    
    async getApiData(url) {
        try {
            const response = await fetch(url);
            const data = await response.json();
            return {
                success: true,
                data: data,
                status: response.status
            };
        } catch (error) {
            return {
                success: false,
                error: error.message,
                status: 0
            };
        }
    }
};

// Global test hooks
beforeAll(async () => {
    console.log('🚀 Starting SPRIN E2E Tests...');
    console.log(`📡 Base URL: ${global.testConfig.baseUrl}`);
    console.log(`🔗 API URL: ${global.testConfig.apiBaseUrl}`);
    
    // Create screenshots directory
    const fs = require('fs');
    if (!fs.existsSync('./screenshots')) {
        fs.mkdirSync('./screenshots');
    }
    
    // Verify application is running
    const apiCheck = await global.testUtils.checkApiResponse(`${global.testConfig.apiBaseUrl}/test_api.php`);
    if (!apiCheck.ok) {
        console.error('❌ Application is not running or API is not accessible');
        process.exit(1);
    }
    
    console.log('✅ Application is running and accessible');
});

afterAll(async () => {
    console.log('🏁 SPRIN E2E Tests completed');
});

// Test failure handling (alternative approach)
process.on('unhandledRejection', (reason, promise) => {
    console.error('❌ Unhandled Promise Rejection:', reason);
});

process.on('uncaughtException', (error) => {
    console.error('❌ Uncaught Exception:', error);
    process.exit(1);
});
