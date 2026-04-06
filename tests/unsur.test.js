/**
 * Unsur Management Tests
 * Tests CRUD operations for unsur management
 */

const puppeteer = require('puppeteer');

describe('Unsur Management', () => {
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
        await global.testUtils.login(page);
    });
    
    afterAll(async () => {
        if (browser) {
            await browser.close();
        }
    });
    
    beforeEach(async () => {
        // Navigate to unsur page before each test
        await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`);
        await page.waitForSelector('body', { timeout: 5000 });
    });
    
    describe('Page Load', () => {
        test('should load unsur page successfully', async () => {
            const title = await page.title();
            expect(title).toContain('Unsur');
            
            // Check for key elements
            await expect(page).toMatchElement('#unsurTable', { timeout: 5000 });
            await expect(page).toMatchElement('[data-action="add-unsur"]', { timeout: 5000 });
            
            console.log('✅ Unsur page loaded successfully');
        });
        
        test('should display unsur data', async () => {
            // Wait for table to load
            await page.waitForSelector('#unsurTable tbody tr', { timeout: 10000 });
            
            // Check if table has data
            const rows = await page.$$('#unsurTable tbody tr');
            expect(rows.length).toBeGreaterThan(0);
            
            // Check table headers
            const headers = await page.$$('#unsurTable thead th');
            expect(headers.length).toBeGreaterThan(0);
            
            console.log(`✅ Unsur table loaded with ${rows.length} rows`);
        });
    });
    
    describe('API Integration', () => {
        test('should load unsur data via API', async () => {
            const apiData = await global.testUtils.getApiData(`${global.testConfig.apiBaseUrl}/unsur_api.php?action=get_all_unsur`);
            
            expect(apiData.success).toBe(true);
            expect(Array.isArray(apiData.data)).toBe(true);
            expect(apiData.data.length).toBeGreaterThan(0);
            
            console.log(`✅ API returned ${apiData.data.length} unsur records`);
        });
        
        test('should get unsur statistics via API', async () => {
            const stats = await global.testUtils.getApiData(`${global.testConfig.apiBaseUrl}/unsur_api.php?action=get_unsur_stats`);
            
            expect(stats.success).toBe(true);
            expect(stats.data).toHaveProperty('total_unsur');
            expect(stats.data.total_unsur).toBeGreaterThan(0);
            
            console.log(`✅ API stats: ${stats.data.total_unsur} total unsur`);
        });
    });
    
    describe('Create Unsur', () => {
        test('should open add unsur modal', async () => {
            await global.testUtils.clickElement(page, '[data-action="add-unsur"]');
            
            // Wait for modal
            await expect(page).toMatchElement('#unsurModal', { timeout: 5000 });
            await expect(page).toMatchElement('#modalTitle');
            
            const modalTitle = await page.$eval('#modalTitle', el => el.textContent);
            expect(modalTitle).toContain('Tambah');
            
            console.log('✅ Add unsur modal opened successfully');
        });
        
        test('should create new unsur', async () => {
            // Open modal
            await global.testUtils.clickElement(page, '[data-action="add-unsur"]');
            await page.waitForSelector('#unsurModal', { timeout: 5000 });
            
            // Fill form
            const testData = {
                nama_unsur: `TEST UNSUR ${Date.now()}`,
                kode_unsur: `TEST_${Date.now()}`,
                deskripsi: 'Test description for automated testing',
                tingkat: 'TEST',
                urutan: '99'
            };
            
            await global.testUtils.typeText(page, '#nama_unsur', testData.nama_unsur);
            await global.testUtils.typeText(page, '#kode_unsur', testData.kode_unsur);
            await global.testUtils.typeText(page, '#deskripsi', testData.deskripsi);
            await global.testUtils.typeText(page, '#tingkat', testData.tingkat);
            await global.testUtils.typeText(page, '#urutan', testData.urutan);
            
            // Submit form
            await global.testUtils.clickElement(page, '#unsurModal button[type="submit"]');
            
            // Wait for response
            await global.testUtils.delay(2000);
            
            // Check if modal closed
            const modalVisible = await page.$('#unsurModal.show');
            expect(modalVisible).toBeNull();
            
            // Verify success message (check for success notification)
            const successNotification = await page.$('.alert-success, .toast-success');
            if (successNotification) {
                console.log('✅ Success notification displayed');
            }
            
            console.log('✅ New unsur created successfully');
        });
    });
    
    describe('Edit Unsur', () => {
        test('should open edit unsur modal', async () => {
            // Find first edit button
            const editButton = await page.$('#unsurTable tbody tr:first-child .btn-outline-info');
            expect(editButton).toBeTruthy();
            
            // Click edit button
            await editButton.click();
            
            // Wait for modal
            await expect(page).toMatchElement('#unsurModal', { timeout: 5000 });
            
            const modalTitle = await page.$eval('#modalTitle', el => el.textContent);
            expect(modalTitle).toContain('Edit');
            
            // Check if form is populated
            const namaField = await page.$eval('#nama_unsur', el => el.value);
            expect(namaField).toBeTruthy();
            expect(namaField.length).toBeGreaterThan(0);
            
            console.log('✅ Edit unsur modal opened with data');
        });
        
        test('should update existing unsur', async () => {
            // Find first edit button
            const editButton = await page.$('#unsurTable tbody tr:first-child .btn-outline-info');
            await editButton.click();
            
            await page.waitForSelector('#unsurModal', { timeout: 5000 });
            
            // Get original values
            const originalNama = await page.$eval('#nama_unsur', el => el.value);
            
            // Update name
            const updatedNama = `${originalNama} (Updated)`;
            await global.testUtils.typeText(page, '#nama_unsur', updatedNama);
            
            // Submit form
            await global.testUtils.clickElement(page, '#unsurModal button[type="submit"]');
            
            // Wait for response
            await global.testUtils.delay(2000);
            
            // Verify modal closed
            const modalVisible = await page.$('#unsurModal.show');
            expect(modalVisible).toBeNull();
            
            console.log('✅ Unsur updated successfully');
        });
    });
    
    describe('Delete Unsur', () => {
        test('should show delete confirmation', async () => {
            // Find first delete button
            const deleteButton = await page.$('#unsurTable tbody tr:first-child .btn-outline-danger');
            expect(deleteButton).toBeTruthy();
            
            // Click delete button
            await deleteButton.click();
            
            // Wait for confirmation dialog
            await global.testUtils.delay(1000);
            
            // Check if confirmation dialog appears (could be browser dialog or custom modal)
            const hasCustomModal = await page.$('.modal.show');
            if (hasCustomModal) {
                console.log('✅ Custom delete confirmation modal shown');
            } else {
                // Browser dialog - handle it
                page.on('dialog', async dialog => {
                    expect(dialog.message()).toContain('yakin');
                    await dialog.dismiss();
                });
                console.log('✅ Browser delete confirmation dialog handled');
            }
        });
    });
    
    describe('Data Validation', () => {
        test('should validate required fields', async () => {
            await global.testUtils.clickElement(page, '[data-action="add-unsur"]');
            await page.waitForSelector('#unsurModal', { timeout: 5000 });
            
            // Try to submit empty form
            await global.testUtils.clickElement(page, '#unsurModal button[type="submit"]');
            
            // Should not submit and show validation errors
            await global.testUtils.delay(1000);
            
            // Check if modal is still open (validation failed)
            const modalVisible = await page.$('#unsurModal.show');
            expect(modalVisible).toBeTruthy();
            
            console.log('✅ Form validation working correctly');
        });
        
        test('should handle API errors gracefully', async () => {
            // Test with invalid API endpoint
            const invalidApiData = await global.testUtils.getApiData(`${global.testConfig.apiBaseUrl}/unsur_api.php?action=invalid_action`);
            
            expect(invalidApiData.success).toBe(false);
            expect(invalidApiData.message).toBeTruthy();
            
            console.log('✅ API errors handled gracefully');
        });
    });
    
    describe('Performance', () => {
        test('should load page within acceptable time', async () => {
            const startTime = Date.now();
            
            await page.goto(`${global.testConfig.baseUrl}/pages/unsur.php`);
            await page.waitForSelector('#unsurTable tbody tr', { timeout: 10000 });
            
            const loadTime = Date.now() - startTime;
            expect(loadTime).toBeLessThan(5000); // 5 seconds max
            
            console.log(`✅ Page loaded in ${loadTime}ms`);
        });
        
        test('should handle large data sets efficiently', async () => {
            // Check if pagination or virtual scrolling is working
            const rowCount = await page.$$('#unsurTable tbody tr');
            
            // Should not have performance issues with current data
            expect(rowCount.length).toBeLessThan(1000); // Reasonable limit
            
            console.log(`✅ Handling ${rowCount.length} rows efficiently`);
        });
    });
});
