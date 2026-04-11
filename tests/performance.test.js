/**
 * Performance Tests
 * Tests application performance and responsiveness
 */

const puppeteer = require('puppeteer');

describe('Performance Tests', () => {
    let browser;
    let page;
    
    beforeAll(async () => {
        browser = await puppeteer.launch({
            headless: 'new',
            executablePath: '/usr/bin/google-chrome',
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        page = await browser.newPage();
        global.page = page;
        
        // Enable performance monitoring
        await page.coverage.startJSCoverage();
        await page.coverage.startCSSCoverage();
    });
    
    afterAll(async () => {
        // Stop coverage and get results
        const [jsCoverage, cssCoverage] = await Promise.all([
            page.coverage.stopJSCoverage(),
            page.coverage.stopCSSCoverage()
        ]);
        
        console.log('📊 Coverage Report:');
        console.log(`JS Coverage: ${calculateCoverage(jsCoverage).used}% used`);
        console.log(`CSS Coverage: ${calculateCoverage(cssCoverage).used}% used`);
        
        if (browser) {
            await browser.close();
        }
    });
    
    function calculateCoverage(coverage) {
        const totalBytes = coverage.reduce((sum, entry) => sum + entry.text.length, 0);
        const usedBytes = coverage.reduce((sum, entry) => {
            return sum + entry.text.length - entry.ranges.reduce((sum, range) => sum + range.end - range.start, 0);
        }, 0);
        return {
            total: totalBytes,
            used: usedBytes,
            percentage: Math.round((usedBytes / totalBytes) * 100)
        };
    }
    
    describe('Page Load Performance', () => {
        test('should load login page quickly', async () => {
            const startTime = Date.now();
            
            await page.goto(`${global.testConfig.baseUrl}/login.php`, {
                waitUntil: 'networkidle0'
            });
            
            const loadTime = Date.now() - startTime;
            expect(loadTime).toBeLessThan(3000); // 3 seconds max
            
            console.log(`✅ Login page loaded in ${loadTime}ms`);
        });
        
        test('should load dashboard quickly after login', async () => {
            await global.testUtils.login(page);
            
            const startTime = Date.now();
            
            await page.goto(`${global.testConfig.baseUrl}/pages/main.php`, {
                waitUntil: 'networkidle0'
            });
            
            const loadTime = Date.now() - startTime;
            expect(loadTime).toBeLessThan(5000); // 5 seconds max
            
            console.log(`✅ Dashboard loaded in ${loadTime}ms`);
        });
        
        test('should load unsur page efficiently', async () => {
            const startTime = Date.now();
            
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`, {
                waitUntil: 'networkidle0'
            });
            
            await page.waitForSelector('#unsurTable tbody tr', { timeout: 10000 });
            
            const loadTime = Date.now() - startTime;
            expect(loadTime).toBeLessThan(4000); // 4 seconds max
            
            console.log(`✅ Unsur page loaded in ${loadTime}ms`);
        });
        
        test('should load bagian page efficiently', async () => {
            const startTime = Date.now();
            
            await page.goto(`${global.testConfig.baseUrl}/pages/bagian.php`, {
                waitUntil: 'networkidle0'
            });
            
            await page.waitForSelector('body', { timeout: 5000 });
            
            const loadTime = Date.now() - startTime;
            expect(loadTime).toBeLessThan(4000); // 4 seconds max
            
            console.log(`✅ Bagian page loaded in ${loadTime}ms`);
        });
        
        test('should load jabatan page efficiently', async () => {
            const startTime = Date.now();
            
            await page.goto(`${global.testConfig.baseUrl}/pages/jabatan.php`, {
                waitUntil: 'networkidle0'
            });
            
            await page.waitForSelector('body', { timeout: 5000 });
            
            const loadTime = Date.now() - startTime;
            expect(loadTime).toBeLessThan(4000); // 4 seconds max
            
            console.log(`✅ Jabatan page loaded in ${loadTime}ms`);
        });
    });
    
    describe('API Performance', () => {
        test('should respond to API calls quickly', async () => {
            const apiCalls = [
                `${global.testConfig.apiBaseUrl}/unified-api.php?resource=stats&action=dashboard`,
                `${global.testConfig.apiBaseUrl}/unified-api.php?resource=unsur&action=get_all`,
                `${global.testConfig.apiBaseUrl}/unified-api.php?resource=bagian&action=get_all`,
                `${global.testConfig.apiBaseUrl}/unified-api.php?resource=jabatan&action=get_all`
            ];
            
            const results = [];
            
            for (const url of apiCalls) {
                const startTime = Date.now();
                const response = await global.testUtils.getApiData(url);
                const responseTime = Date.now() - startTime;
                
                expect(response.success).toBe(true);
                expect(responseTime).toBeLessThan(2000); // 2 seconds max
                
                results.push({ url, responseTime });
            }
            
            const avgResponseTime = results.reduce((sum, r) => sum + r.responseTime, 0) / results.length;
            expect(avgResponseTime).toBeLessThan(1500); // 1.5 seconds average
            
            console.log(`✅ API average response time: ${avgResponseTime}ms`);
        });
        
        test('should handle concurrent API requests', async () => {
            const startTime = Date.now();
            
            const concurrentRequests = Array(5).fill().map(() => 
                global.testUtils.getApiData(`${global.testConfig.apiBaseUrl}/unified-api.php?resource=stats&action=dashboard`)
            );
            
            const results = await Promise.all(concurrentRequests);
            
            const totalTime = Date.now() - startTime;
            
            results.forEach(result => {
                expect(result.success).toBe(true);
            });
            
            expect(totalTime).toBeLessThan(3000); // 3 seconds max for 5 concurrent requests
            
            console.log(`✅ 5 concurrent API requests completed in ${totalTime}ms`);
        });
    });
    
    describe('Memory Usage', () => {
        test('should not have memory leaks during navigation', async () => {
            await global.testUtils.login(page);
            
            const initialMemory = await getMemoryUsage(page);
            
            // Navigate through multiple pages
            const pages = [
                '/pages/unsur.php',
                '/pages/bagian.php',
                '/pages/jabatan.php',
                '/pages/main.php'
            ];
            
            for (let i = 0; i < 3; i++) { // Repeat 3 times
                for (const pagePath of pages) {
                    await page.goto(`${global.testConfig.baseUrl}${pagePath}`, {
                        waitUntil: 'networkidle0'
                    });
                    await global.testUtils.delay(1000);
                }
            }
            
            const finalMemory = await getMemoryUsage(page);
            const memoryIncrease = finalMemory - initialMemory;
            
            // Memory increase should be reasonable (less than 50MB)
            expect(memoryIncrease).toBeLessThan(50 * 1024 * 1024); // 50MB in bytes
            
            console.log(`✅ Memory increase: ${Math.round(memoryIncrease / 1024 / 1024)}MB`);
        });
        
        async function getMemoryUsage(page) {
            const metrics = await page.metrics();
            return metrics.JSHeapUsedSize;
        }
    });
    
    describe('Resource Loading', () => {
        test('should load resources efficiently', async () => {
            await page.goto(`${global.testConfig.baseUrl}/pages/main.php`, {
                waitUntil: 'networkidle0'
            });
            
            const performanceMetrics = await page.evaluate(() => {
                const navigation = performance.getEntriesByType('navigation')[0];
                const resources = performance.getEntriesByType('resource');
                
                return {
                    domContentLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
                    loadComplete: navigation.loadEventEnd - navigation.loadEventStart,
                    resourceCount: resources.length,
                    totalResourceSize: resources.reduce((sum, resource) => sum + (resource.transferSize || 0), 0)
                };
            });
            
            expect(performanceMetrics.domContentLoaded).toBeLessThan(2000); // 2 seconds
            expect(performanceMetrics.loadComplete).toBeLessThan(3000); // 3 seconds
            expect(performanceMetrics.totalResourceSize).toBeLessThan(5 * 1024 * 1024); // 5MB
            
            console.log(`✅ Resource loading: ${performanceMetrics.resourceCount} resources, ${Math.round(performanceMetrics.totalResourceSize / 1024)}KB`);
        });
        
        test('should optimize image loading', async () => {
            await page.goto(`${global.testConfig.baseUrl}/pages/main.php`, {
                waitUntil: 'networkidle0'
            });
            
            const imageMetrics = await page.evaluate(() => {
                const images = performance.getEntriesByType('resource').filter(r => r.initiatorType === 'img');
                
                return {
                    count: images.length,
                    totalSize: images.reduce((sum, img) => sum + (img.transferSize || 0), 0),
                    averageSize: images.length > 0 ? images.reduce((sum, img) => sum + (img.transferSize || 0), 0) / images.length : 0
                };
            });
            
            // Images should be optimized
            if (imageMetrics.count > 0) {
                expect(imageMetrics.averageSize).toBeLessThan(500 * 1024); // 500KB average
            }
            
            console.log(`✅ Image optimization: ${imageMetrics.count} images, avg ${Math.round(imageMetrics.averageSize / 1024)}KB each`);
        });
    });
    
    describe('User Interface Performance', () => {
        test('should respond to user interactions quickly', async () => {
            await global.testUtils.login(page);
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`, {
                waitUntil: 'networkidle0'
            });
            
            // Test modal opening
            const modalStartTime = Date.now();
            await global.testUtils.clickElement(page, '[data-action="add-unsur"]');
            await page.waitForSelector('#unsurModal', { timeout: 5000 });
            const modalTime = Date.now() - modalStartTime;
            
            expect(modalTime).toBeLessThan(1000); // 1 second max
            
            // Test form interaction
            const formStartTime = Date.now();
            await global.testUtils.typeText(page, '#nama_unsur', 'Test Input');
            const formTime = Date.now() - formStartTime;
            
            expect(formTime).toBeLessThan(500); // 500ms max
            
            // Test modal closing
            const closeStartTime = Date.now();
            await page.keyboard.press('Escape');
            await page.waitForSelector('#unsurModal', { hidden: true, timeout: 5000 });
            const closeTime = Date.now() - closeStartTime;
            
            expect(closeTime).toBeLessThan(1000); // 1 second max
            
            console.log(`✅ UI interactions: Modal ${modalTime}ms, Form ${formTime}ms, Close ${closeTime}ms`);
        });
        
        test('should handle large data tables efficiently', async () => {
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`, {
                waitUntil: 'networkidle0'
            });
            
            // Measure table rendering time
            const tableStartTime = Date.now();
            await page.waitForSelector('#unsurTable tbody tr', { timeout: 10000 });
            const tableTime = Date.now() - tableStartTime;
            
            expect(tableTime).toBeLessThan(3000); // 3 seconds max
            
            // Test sorting/filtering if available
            const rowCount = await page.$$('#unsurTable tbody tr');
            console.log(`✅ Table rendering: ${rowCount.length} rows in ${tableTime}ms`);
        });
    });
    
    describe('Mobile Performance', () => {
        test('should perform well on mobile viewport', async () => {
            await page.setViewport(global.testConfig.mobileViewport);
            
            const startTime = Date.now();
            
            await page.goto(`${global.testConfig.baseUrl}/pages/main.php`, {
                waitUntil: 'networkidle0'
            });
            
            const loadTime = Date.now() - startTime;
            expect(loadTime).toBeLessThan(4000); // 4 seconds max for mobile
            
            console.log(`✅ Mobile page load: ${loadTime}ms`);
        });
        
        test('should handle touch interactions efficiently', async () => {
            await page.setViewport(global.testConfig.mobileViewport);
            await global.testUtils.login(page);
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`, {
                waitUntil: 'networkidle0'
            });
            
            // Test touch interactions
            const touchStartTime = Date.now();
            await page.tap('[data-action="add-unsur"]');
            await page.waitForSelector('#unsurModal', { timeout: 5000 });
            const touchTime = Date.now() - touchStartTime;
            
            expect(touchTime).toBeLessThan(1500); // 1.5 seconds max for mobile touch
            
            console.log(`✅ Mobile touch interaction: ${touchTime}ms`);
        });
    });
});
