const puppeteer = require('puppeteer-core');

async function testLogin() {
    console.log('Testing Quick Login dengan Puppeteer...');
    
    let browser;
    try {
        browser = await puppeteer.launch({
            headless: true,
            executablePath: '/usr/bin/google-chrome',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-web-security',
                '--disable-features=VizDisplayCompositor'
            ]
        });
        
        const page = await browser.newPage();
        
        // Clear cookies and storage
        await page.evaluate(() => {
            localStorage.clear();
            sessionStorage.clear();
        });
        
        await page.goto('http://localhost/sprint/login.php', { 
            waitUntil: 'networkidle2',
            timeout: 10000 
        });
        
        console.log('Halaman login loaded');
        
        // Wait for Quick Login button
        await page.waitForSelector('.btn-quick-login', { timeout: 5000 });
        console.log('Tombol Quick Login ditemukan');
        
        // Click Quick Login
        await page.click('.btn-quick-login');
        console.log('Quick Login diklik');
        
        // Wait for navigation or form submission
        await page.waitForTimeout(2000);
        
        // Check current URL
        const currentUrl = page.url();
        console.log('Current URL:', currentUrl);
        
        if (currentUrl.includes('pages/main.php')) {
            console.log('✅ LOGIN BERHASIL!');
            
            // Check for dashboard content
            try {
                await page.waitForSelector('h1, .dashboard, .container', { timeout: 3000 });
                const pageTitle = await page.title();
                console.log('Page title:', pageTitle);
            } catch (e) {
                console.log('Dashboard content may not be loaded yet');
            }
        } else {
            console.log('❌ LOGIN GAGAL');
            
            // Check for error message
            try {
                const errorElement = await page.$('.alert-danger, .error, .text-danger');
                if (errorElement) {
                    const errorMessage = await page.evaluate(el => el.textContent.trim(), errorElement);
                    console.log('Error message:', errorMessage);
                }
                
                const pageTitle = await page.title();
                console.log('Page title:', pageTitle);
            } catch (e) {
                console.log('No error message found');
            }
        }
        
    } catch (error) {
        console.error('Error:', error.message);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
}

testLogin();
