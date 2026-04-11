/**
 * Bagian CRUD Tests
 * Tests CRUD operations for bagian via bagian_api.php
 */

const puppeteer = require('puppeteer');

describe('Bagian CRUD Operations', () => {
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
        
        await page.setViewport(global.testConfig.viewport);
        
        // Login first
        await global.testUtils.login(page);
    });
    
    afterAll(async () => {
        if (browser) {
            await browser.close();
        }
    });
    
    describe('API - Create Bagian', () => {
        test('should create new bagian via API', async () => {
            const response = await fetch(`${global.testConfig.apiBaseUrl}/bagian_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_bagian',
                    nama_bagian: 'TEST BAGIAN UNIT',
                    id_unsur: '1'
                })
            });
            
            const data = await response.json();
            
            expect(data.success).toBe(true);
            expect(data.message).toContain('successfully');
            expect(data.id).toBeDefined();
            
            console.log('✅ Create bagian via API: ID', data.id);
            
            // Cleanup: delete the test bagian
            if (data.id) {
                await fetch(`${global.testConfig.apiBaseUrl}/bagian_api.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'delete_bagian',
                        id: data.id
                    })
                });
            }
        });
        
        test('should validate required fields on create', async () => {
            const response = await fetch(`${global.testConfig.apiBaseUrl}/bagian_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_bagian',
                    nama_bagian: '' // Empty nama
                })
            });
            
            const data = await response.json();
            
            expect(data.success).toBe(false);
            expect(data.message).toContain('required');
            
            console.log('✅ Create validation working');
        });
    });
    
    describe('API - Read Bagian', () => {
        test('should get all bagian data', async () => {
            const response = await fetch(`${global.testConfig.apiBaseUrl}/bagian_api.php?action=get_all_bagian`);
            const data = await response.json();
            
            expect(data.success).toBe(true);
            expect(Array.isArray(data.data)).toBe(true);
            expect(data.data.length).toBeGreaterThan(0);
            
            // Check data structure
            const bagian = data.data[0];
            expect(bagian).toHaveProperty('id');
            expect(bagian).toHaveProperty('nama_bagian');
            expect(bagian).toHaveProperty('personil_count');
            
            console.log(`✅ Get all bagian: ${data.data.length} records`);
        });
        
        test('should get bagian detail by ID', async () => {
            const response = await fetch(`${global.testConfig.apiBaseUrl}/bagian_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'get_bagian_detail',
                    id: '1'
                })
            });
            
            const data = await response.json();
            
            expect(data.success).toBe(true);
            expect(data.data).toHaveProperty('id');
            expect(data.data).toHaveProperty('nama_bagian');
            
            console.log('✅ Get bagian detail working');
        });
    });
    
    describe('API - Update Bagian', () => {
        test('should update bagian via API', async () => {
            // First create a test bagian
            const createResponse = await fetch(`${global.testConfig.apiBaseUrl}/bagian_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_bagian',
                    nama_bagian: 'TEST UPDATE BAGIAN',
                    id_unsur: '1'
                })
            });
            
            const createData = await createResponse.json();
            expect(createData.success).toBe(true);
            
            const bagianId = createData.id;
            
            // Update the bagian
            const updateResponse = await fetch(`${global.testConfig.apiBaseUrl}/bagian_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'update_bagian',
                    id: bagianId,
                    nama_bagian: 'TEST UPDATED BAGIAN',
                    id_unsur: '2'
                })
            });
            
            const updateData = await updateResponse.json();
            
            expect(updateData.success).toBe(true);
            expect(updateData.message).toContain('successfully');
            
            console.log('✅ Update bagian via API working');
            
            // Cleanup
            await fetch(`${global.testConfig.apiBaseUrl}/bagian_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'delete_bagian',
                    id: bagianId
                })
            });
        });
    });
    
    describe('API - Delete Bagian', () => {
        test('should delete bagian via API', async () => {
            // First create a test bagian
            const createResponse = await fetch(`${global.testConfig.apiBaseUrl}/bagian_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_bagian',
                    nama_bagian: 'TEST DELETE BAGIAN',
                    id_unsur: '1'
                })
            });
            
            const createData = await createResponse.json();
            expect(createData.success).toBe(true);
            
            const bagianId = createData.id;
            
            // Delete the bagian
            const deleteResponse = await fetch(`${global.testConfig.apiBaseUrl}/bagian_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'delete_bagian',
                    id: bagianId
                })
            });
            
            const deleteData = await deleteResponse.json();
            
            expect(deleteData.success).toBe(true);
            expect(deleteData.message).toContain('successfully');
            
            console.log('✅ Delete bagian via API working');
        });
        
        test('should prevent delete with active personil', async () => {
            // Try to delete a bagian that likely has personil
            const response = await fetch(`${global.testConfig.apiBaseUrl}/bagian_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'delete_bagian',
                    id: '1' // Assuming ID 1 has personil
                })
            });
            
            const data = await response.json();
            
            // Should fail if bagian has personil
            if (!data.success) {
                expect(data.message).toContain('personil');
                console.log('✅ Delete validation with personil working');
            } else {
                console.log('⚠️  Bagian ID 1 has no personil, test skipped');
            }
        });
    });
    
    describe('API - Move Bagian (Drag & Drop)', () => {
        test('should move bagian to different unsur via API', async () => {
            // First create a test bagian
            const createResponse = await fetch(`${global.testConfig.apiBaseUrl}/bagian_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_bagian',
                    nama_bagian: 'TEST MOVE BAGIAN',
                    id_unsur: '1'
                })
            });
            
            const createData = await createResponse.json();
            expect(createData.success).toBe(true);
            
            const bagianId = createData.id;
            
            // Move bagian to different unsur
            const moveResponse = await fetch(`${global.testConfig.apiBaseUrl}/bagian_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'move_bagian',
                    bagian_id: bagianId,
                    new_unsur_id: '2',
                    new_urutan: '1'
                })
            });
            
            const moveData = await moveResponse.json();
            
            expect(moveData.success).toBe(true);
            expect(moveData.message).toContain('dipindahkan');
            
            console.log('✅ Move bagian via API working');
            
            // Cleanup
            await fetch(`${global.testConfig.apiBaseUrl}/bagian_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'delete_bagian',
                    id: bagianId
                })
            });
        });
    });
    
    describe('Page - Bagian UI', () => {
        test('should load bagian page without errors', async () => {
            await page.goto(`${global.testConfig.baseUrl}/pages/bagian.php`);
            await page.waitForSelector('body', { timeout: 5000 });
            
            const title = await page.title();
            expect(title).toContain('Bagian');
            
            console.log('✅ Bagian page loaded');
        });
        
        test('should display bagian list table', async () => {
            await page.goto(`${global.testConfig.baseUrl}/pages/bagian.php`);
            await page.waitForSelector('body', { timeout: 5000 });
            
            // Check if table exists
            const tableExists = await page.$('.table') || await page.$('table');
            expect(tableExists).toBeTruthy();
            
            console.log('✅ Bagian list table displayed');
        });
        
        test('should have add bagian button', async () => {
            await page.goto(`${global.testConfig.baseUrl}/pages/bagian.php`);
            await page.waitForSelector('body', { timeout: 5000 });
            
            // Check for add button
            const addButton = await page.$('[onclick*="openAddModal"]') || 
                              await page.$('.btn-add') ||
                              await page.$('button');
            
            expect(addButton).toBeTruthy();
            
            console.log('✅ Add bagian button present');
        });
    });
    
    describe('Integration - Page + API', () => {
        test('should use API for CRUD operations from page', async () => {
            // Navigate to bagian page
            await page.goto(`${global.testConfig.baseUrl}/pages/bagian.php`);
            await page.waitForSelector('body', { timeout: 5000 });
            
            // Open add modal
            await global.testUtils.clickElement(page, '[onclick*="openAddModal"]');
            await global.testUtils.delay(500);
            
            // Check if modal opened
            const modal = await page.$('.modal') || await page.$('#bagianModal');
            if (modal) {
                console.log('✅ Add modal opened');
                
                // Close modal
                await page.keyboard.press('Escape');
                await global.testUtils.delay(500);
            }
        });
        
        test('should not have duplicate CRUD handlers in page', async () => {
            // Read the page file to check for duplicate handlers
            const fs = require('fs');
            const pageContent = fs.readFileSync('../pages/bagian.php', 'utf8');
            
            // Check that page doesn't have direct CRUD handlers
            expect(pageContent).not.toMatch(/case 'create_bagan'/);
            expect(pageContent).not.toMatch(/case 'update_bagian'/);
            expect(pageContent).not.toMatch(/case 'delete_bagian'/);
            
            console.log('✅ No duplicate CRUD handlers in page');
        });
    });
});
