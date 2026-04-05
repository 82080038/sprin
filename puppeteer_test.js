
const puppeteer = require('puppeteer');

async function runTests() {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    
    const testPages = [
        'http://localhost/sprint/',
        'http://localhost/sprint/login.php',
        'http://localhost/sprint/pages/main.php',
        'http://localhost/sprint/pages/personil.php',
        'http://localhost/sprint/pages/bagian.php'
    ];
    
    const results = {
        passed: 0,
        failed: 0,
        errors: []
    };
    
    for (const url of testPages) {
        try {
            console.log(\`Testing: ${url}\`);
            await page.goto(url, { waitUntil: 'networkidle2' });
            
            // Check for JavaScript errors
            const jsErrors = await page.evaluate(() => {
                return window.jsErrors || [];
            });
            
            if (jsErrors.length > 0) {
                results.failed++;
                results.errors.push({
                    url: url,
                    errors: jsErrors
                });
                console.log(\`  ❌ JavaScript errors: ${jsErrors.length}\`);
            } else {
                results.passed++;
                console.log(\`  ✅ Page loaded successfully\`);
            }
            
            // Check for console warnings
            const consoleWarnings = await page.evaluate(() => {
                return window.consoleWarnings || [];
            });
            
            if (consoleWarnings.length > 0) {
                console.log(\`  ⚠️  Warnings: ${consoleWarnings.length}\`);
            }
            
        } catch (error) {
            results.failed++;
            results.errors.push({
                url: url,
                error: error.message
            });
            console.log(\`  ❌ Failed: ${error.message}\`);
        }
    }
    
    console.log(JSON.stringify(results));
    await browser.close();
}

runTests().catch(console.error);
