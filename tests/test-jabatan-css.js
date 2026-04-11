/**
 * Puppeteer Test for Jabatan Page CSS Verification
 * Test URL: http://localhost/sprin/pages/jabatan.php
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://localhost/sprin';
const TEST_URL = `${BASE_URL}/pages/jabatan.php`;
const SCREENSHOT_DIR = path.join(__dirname, 'screenshots');

// Ensure screenshot directory exists
if (!fs.existsSync(SCREENSHOT_DIR)) {
    fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

async function testJabatanPageCSS() {
    console.log('========================================');
    console.log('JABATAN PAGE CSS TEST');
    console.log('========================================');
    console.log(`Testing: ${TEST_URL}`);
    console.log('');

    const browser = await puppeteer.launch({
        headless: false,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--window-size=1920,1080'
        ]
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });

    try {
        console.log('[1] Logging in first...');
        await page.goto(`${BASE_URL}/login.php`, { waitUntil: 'networkidle2' });
        
        // Check if login form exists
        const loginForm = await page.$('form[method="POST"]');
        const usernameInput = await page.$('#username');
        const passwordInput = await page.$('#password');
        
        if (loginForm && usernameInput && passwordInput) {
            console.log('  - Login form found, entering credentials...');
            await page.type('#username', 'bagops');
            await page.type('#password', 'admin123');
            await page.click('button[type="submit"]');
            
            // Wait for login to complete
            await page.waitForFunction(
                () => window.location.href.includes('main.php') || window.location.href.includes('dashboard'),
                { timeout: 10000 }
            );
            console.log('  - Login successful');
        } else {
            // Try Quick Login button
            console.log('  - Trying Quick Login button...');
            await page.click('button.btn-quick-login');
            await page.waitForFunction(
                () => window.location.href.includes('main.php') || window.location.href.includes('dashboard'),
                { timeout: 10000 }
            );
            console.log('  - Quick Login successful');
        }

        console.log('[2] Navigating to jabatan.php...');
        await page.goto(TEST_URL, { waitUntil: 'networkidle2' });

        // Take initial screenshot
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const screenshotPath = path.join(SCREENSHOT_DIR, `jabatan_css_test_${timestamp}.png`);
        await page.screenshot({ path: screenshotPath, fullPage: true });
        console.log(`✓ Screenshot saved: ${screenshotPath}`);

        console.log('[3] Checking CSS variables...');
        const cssVariables = await page.evaluate(() => {
            const styles = getComputedStyle(document.documentElement);
            return {
                primaryColor: styles.getPropertyValue('--primary-color').trim(),
                secondaryColor: styles.getPropertyValue('--secondary-color').trim(),
                accentColor: styles.getPropertyValue('--accent-color').trim(),
                bgColor: styles.getPropertyValue('--bg-color').trim(),
                cardBg: styles.getPropertyValue('--bg-card').trim(),
                textColor: styles.getPropertyValue('--text-color').trim()
            };
        });
        console.log('CSS Variables:', JSON.stringify(cssVariables, null, 2));

        console.log('[4] Checking page elements styling...');
        const elementStyles = await page.evaluate(() => {
            const results = {};

            // Check navbar
            const navbar = document.querySelector('.navbar');
            if (navbar) {
                const navbarStyles = window.getComputedStyle(navbar);
                results.navbar = {
                    background: navbarStyles.background,
                    boxShadow: navbarStyles.boxShadow
                };
            }

            // Check cards
            const cards = document.querySelectorAll('.card');
            if (cards.length > 0) {
                const cardStyles = window.getComputedStyle(cards[0]);
                results.card = {
                    backgroundColor: cardStyles.backgroundColor,
                    borderRadius: cardStyles.borderRadius,
                    boxShadow: cardStyles.boxShadow,
                    count: cards.length
                };
            }

            // Check buttons
            const buttons = document.querySelectorAll('.btn');
            if (buttons.length > 0) {
                const buttonStyles = window.getComputedStyle(buttons[0]);
                results.button = {
                    borderRadius: buttonStyles.borderRadius,
                    padding: buttonStyles.padding,
                    count: buttons.length
                };
            }

            // Check tables
            const tables = document.querySelectorAll('.table');
            if (tables.length > 0) {
                const tableStyles = window.getComputedStyle(tables[0]);
                results.table = {
                    backgroundColor: tableStyles.backgroundColor,
                    borderRadius: tableStyles.borderRadius,
                    boxShadow: tableStyles.boxShadow,
                    count: tables.length
                };
            }

            return results;
        });
        console.log('Element Styles:', JSON.stringify(elementStyles, null, 2));

        console.log('[5] Checking for inline styles...');
        const inlineStyles = await page.evaluate(() => {
            const elements = document.querySelectorAll('*[style]');
            return {
                count: elements.length,
                elements: Array.from(elements).slice(0, 5).map(el => ({
                    tag: el.tagName,
                    class: el.className,
                    style: el.getAttribute('style')
                }))
            };
        });
        console.log('Inline Styles:', JSON.stringify(inlineStyles, null, 2));

        console.log('[6] Checking loaded CSS files...');
        const cssFiles = await page.evaluate(() => {
            const links = Array.from(document.querySelectorAll('link[rel="stylesheet"]'));
            return links.map(link => link.href);
        });
        console.log('Loaded CSS Files:', cssFiles);

        console.log('');
        console.log('========================================');
        console.log('TEST SUMMARY');
        console.log('========================================');
        console.log(`✓ Page loaded successfully`);
        console.log(`✓ CSS variables defined: ${Object.keys(cssVariables).length}`);
        console.log(`✓ Cards found: ${elementStyles.card?.count || 0}`);
        console.log(`✓ Buttons found: ${elementStyles.button?.count || 0}`);
        console.log(`✓ Tables found: ${elementStyles.table?.count || 0}`);
        console.log(`⚠ Inline styles: ${inlineStyles.count}`);
        console.log(`✓ CSS files loaded: ${cssFiles.length}`);
        console.log('');
        console.log('CSS Consistency Check:');
        console.log(`- Primary color: ${cssVariables.primaryColor}`);
        console.log(`- Using sprin.css: ${cssFiles.some(f => f.includes('sprin.css')) ? '✓' : '✗'}`);
        console.log(`- Using responsive.css: ${cssFiles.some(f => f.includes('responsive.css')) ? '✓' : '✗'}`);
        console.log('');

    } catch (error) {
        console.error('❌ Test failed:', error.message);
    } finally {
        console.log('Waiting 5 seconds before closing browser...');
        await new Promise(resolve => setTimeout(resolve, 5000));
        await browser.close();
        console.log('✓ Browser closed');
    }
}

testJabatanPageCSS().catch(console.error);
