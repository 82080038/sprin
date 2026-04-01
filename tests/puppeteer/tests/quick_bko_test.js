/**
 * Quick Test BKO JavaScript Interference
 */

const puppeteer = require('puppeteer');
const config = require('../config');

async function quickTest() {
    const browser = await puppeteer.launch(config.browser);
    const page = await browser.newPage();
    
    try {
        console.log('🔍 Quick BKO Test...');
        
        // Go directly to bagian page (bypass login for testing)
        await page.goto(config.baseUrl + '/pages/bagian.php', { 
            waitUntil: 'domcontentloaded',
            timeout: 10000 
        });
        
        // Check HTML immediately
        const initialCheck = await page.evaluate(() => {
            const unsurCards = document.querySelectorAll('.unsur-card');
            let result = null;
            
            unsurCards.forEach(card => {
                const unsurName = card.querySelector('h6')?.textContent.trim();
                if (unsurName === 'UNSUR LAINNYA') {
                    const badge = card.querySelector('.badge');
                    const bagianList = card.querySelector('.bagian-list');
                    const bagians = bagianList ? bagianList.querySelectorAll('.bagian-item') : [];
                    
                    result = {
                        unsurName: unsurName,
                        badgeCount: badge ? parseInt(badge.textContent.trim()) : 0,
                        bagianCount: bagians.length,
                        bagianNames: Array.from(bagians).map(b => b.querySelector('.bagian-name')?.textContent.trim())
                    };
                }
            });
            
            return result;
        });
        
        console.log('Initial Check Result:');
        console.log(`  Badge: ${initialCheck?.badgeCount || 'N/A'}`);
        console.log(`  Bagians: ${initialCheck?.bagianCount || 'N/A'}`);
        console.log(`  Names: ${initialCheck?.bagianNames.join(', ') || 'None'}`);
        
        // Wait for JavaScript
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Check after JavaScript
        const afterJSCheck = await page.evaluate(() => {
            const unsurCards = document.querySelectorAll('.unsur-card');
            let result = null;
            
            unsurCards.forEach(card => {
                const unsurName = card.querySelector('h6')?.textContent.trim();
                if (unsurName === 'UNSUR LAINNYA') {
                    const badge = card.querySelector('.badge');
                    const bagianList = card.querySelector('.bagian-list');
                    const bagians = bagianList ? bagianList.querySelectorAll('.bagian-item') : [];
                    
                    result = {
                        unsurName: unsurName,
                        badgeCount: badge ? parseInt(badge.textContent.trim()) : 0,
                        bagianCount: bagians.length,
                        bagianNames: Array.from(bagians).map(b => b.querySelector('.bagian-name')?.textContent.trim())
                    };
                }
            });
            
            return result;
        });
        
        console.log('After JS Check Result:');
        console.log(`  Badge: ${afterJSCheck?.badgeCount || 'N/A'}`);
        console.log(`  Bagians: ${afterJSCheck?.bagianCount || 'N/A'}`);
        console.log(`  Names: ${afterJSCheck?.bagianNames.join(', ') || 'None'}`);
        
        // Test with JavaScript disabled
        const noJSPage = await browser.newPage();
        await noJSPage.setJavaScriptEnabled(false);
        
        await noJSPage.goto(config.baseUrl + '/pages/bagian.php', { 
            waitUntil: 'networkidle2',
            timeout: 10000 
        });
        
        const noJSCheck = await noJSPage.evaluate(() => {
            const unsurCards = document.querySelectorAll('.unsur-card');
            let result = null;
            
            unsurCards.forEach(card => {
                const unsurName = card.querySelector('h6')?.textContent.trim();
                if (unsurName === 'UNSUR LAINNYA') {
                    const badge = card.querySelector('.badge');
                    const bagianList = card.querySelector('.bagian-list');
                    const bagians = bagianList ? bagianList.querySelectorAll('.bagian-item') : [];
                    
                    result = {
                        unsurName: unsurName,
                        badgeCount: badge ? parseInt(badge.textContent.trim()) : 0,
                        bagianCount: bagians.length,
                        bagianNames: Array.from(bagians).map(b => b.querySelector('.bagian-name')?.textContent.trim())
                    };
                }
            });
            
            return result;
        });
        
        console.log('No JS Check Result:');
        console.log(`  Badge: ${noJSCheck?.badgeCount || 'N/A'}`);
        console.log(`  Bagians: ${noJSCheck?.bagianCount || 'N/A'}`);
        console.log(`  Names: ${noJSCheck?.bagianNames.join(', ') || 'None'}`);
        
        await noJSPage.close();
        
        // Analysis
        console.log('\n🔍 ANALYSIS:');
        console.log(`Initial HTML: ${initialCheck?.bagianCount || 0} bagians`);
        console.log(`After JS: ${afterJSCheck?.bagianCount || 0} bagians`);
        console.log(`No JS: ${noJSCheck?.bagianCount || 0} bagians`);
        
        if (noJSCheck?.bagianCount > 0) {
            console.log('✅ BKO appears without JavaScript - PHP HTML is correct');
        } else {
            console.log('❌ BKO missing even without JavaScript - PHP HTML issue');
        }
        
        if (initialCheck?.bagianCount !== afterJSCheck?.bagianCount) {
            console.log('⚠️ JavaScript is modifying the DOM');
        } else {
            console.log('✅ JavaScript not affecting DOM');
        }
        
    } catch (error) {
        console.error('Error:', error.message);
    } finally {
        await browser.close();
    }
}

quickTest();
