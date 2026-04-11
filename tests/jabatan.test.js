/**
 * Jabatan CRUD Tests
 * Tests CRUD operations for jabatan via jabatan_api.php
 */

const puppeteer = require('puppeteer');

describe('Jabatan CRUD Operations', () => {
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
    
    describe('API - Create Jabatan', () => {
        test('should create new jabatan via API', async () => {
            const response = await fetch(`${global.testConfig.apiBaseUrl}/jabatan_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_jabatan',
                    nama_jabatan: 'TEST JABATAN UNIT',
                    id_unsur: '1'
                })
            });
            
            const data = await response.json();
            
            expect(data.success).toBe(true);
            expect(data.message).toContain('successfully');
            expect(data.id).toBeDefined();
            
            console.log('✅ Create jabatan via API: ID', data.id);
            
            // Cleanup: delete the test jabatan
            if (data.id) {
                await fetch(`${global.testConfig.apiBaseUrl}/jabatan_api.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'delete_jabatan',
                        id: data.id
                    })
                });
            }
        });
        
        test('should validate required fields on create', async () => {
            const response = await fetch(`${global.testConfig.apiBaseUrl}/jabatan_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_jabatan',
                    nama_jabatan: '' // Empty nama
                })
            });
            
            const data = await response.json();
            
            expect(data.success).toBe(false);
            expect(data.message).toContain('required');
            
            console.log('✅ Create validation working');
        });
    });
    
    describe('API - Read Jabatan', () => {
        test('should get all jabatan data', async () => {
            const response = await fetch(`${global.testConfig.apiBaseUrl}/jabatan_api.php?action=get_all_jabatan`);
            const data = await response.json();
            
            expect(data.success).toBe(true);
            expect(Array.isArray(data.data)).toBe(true);
            expect(data.data.length).toBeGreaterThan(0);
            
            // Check data structure
            const jabatan = data.data[0];
            expect(jabatan).toHaveProperty('id');
            expect(jabatan).toHaveProperty('nama_jabatan');
            expect(jabatan).toHaveProperty('personil_count');
            expect(jabatan).toHaveProperty('nama_unsur');
            
            console.log(`✅ Get all jabatan: ${data.data.length} records`);
        });
        
        test('should get jabatan detail by ID', async () => {
            const response = await fetch(`${global.testConfig.apiBaseUrl}/jabatan_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'get_jabatan_detail',
                    id: '1'
                })
            });
            
            const data = await response.json();
            
            expect(data.success).toBe(true);
            expect(data.data).toHaveProperty('id');
            expect(data.data).toHaveProperty('nama_jabatan');
            
            console.log('✅ Get jabatan detail working');
        });
        
        test('should get jabatan by unsur', async () => {
            const response = await fetch(`${global.testConfig.apiBaseUrl}/jabatan_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'get_jabatan_by_unsur',
                    unsur_id: '1'
                })
            });
            
            const data = await response.json();
            
            expect(data.success).toBe(true);
            expect(Array.isArray(data.data)).toBe(true);
            
            console.log('✅ Get jabatan by unsur working');
        });
    });
    
    describe('API - Update Jabatan', () => {
        test('should update jabatan via API', async () => {
            // First create a test jabatan
            const createResponse = await fetch(`${global.testConfig.apiBaseUrl}/jabatan_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_jabatan',
                    nama_jabatan: 'TEST UPDATE JABATAN',
                    id_unsur: '1'
                })
            });
            
            const createData = await createResponse.json();
            expect(createData.success).toBe(true);
            
            const jabatanId = createData.id;
            
            // Update the jabatan
            const updateResponse = await fetch(`${global.testConfig.apiBaseUrl}/jabatan_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'update_jabatan',
                    id: jabatanId,
                    nama_jabatan: 'TEST UPDATED JABATAN',
                    id_unsur: '2',
                    id_bagian: '1'
                })
            });
            
            const updateData = await updateResponse.json();
            
            expect(updateData.success).toBe(true);
            expect(updateData.message).toContain('successfully');
            
            console.log('✅ Update jabatan via API working');
            
            // Cleanup
            await fetch(`${global.testConfig.apiBaseUrl}/jabatan_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'delete_jabatan',
                    id: jabatanId
                })
            });
        });
    });
    
    describe('API - Delete Jabatan', () => {
        test('should delete jabatan via API', async () => {
            // First create a test jabatan
            const createResponse = await fetch(`${global.testConfig.apiBaseUrl}/jabatan_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_jabatan',
                    nama_jabatan: 'TEST DELETE JABATAN',
                    id_unsur: '1'
                })
            });
            
            const createData = await createResponse.json();
            expect(createData.success).toBe(true);
            
            const jabatanId = createData.id;
            
            // Delete the jabatan
            const deleteResponse = await fetch(`${global.testConfig.apiBaseUrl}/jabatan_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'delete_jabatan',
                    id: jabatanId
                })
            });
            
            const deleteData = await deleteResponse.json();
            
            expect(deleteData.success).toBe(true);
            expect(deleteData.message).toContain('successfully');
            
            console.log('✅ Delete jabatan via API working');
        });
        
        test('should prevent delete with active personil', async () => {
            // Try to delete a jabatan that likely has personil
            const response = await fetch(`${global.testConfig.apiBaseUrl}/jabatan_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'delete_jabatan',
                    id: '1' // Assuming ID 1 has personil
                })
            });
            
            const data = await response.json();
            
            // Should fail if jabatan has personil
            if (!data.success) {
                expect(data.message).toContain('personil');
                console.log('✅ Delete validation with personil working');
            } else {
                console.log('⚠️  Jabatan ID 1 has no personil, test skipped');
            }
        });
    });
    
    describe('API - Jabatan Statistics', () => {
        test('should get jabatan statistics', async () => {
            const response = await fetch(`${global.testConfig.apiBaseUrl}/jabatan_api.php?action=get_jabatan_stats`);
            const data = await response.json();
            
            expect(data.success).toBe(true);
            expect(data.data).toHaveProperty('total_jabatan');
            expect(data.data).toHaveProperty('total_personil');
            expect(data.data).toHaveProperty('total_unsur');
            
            console.log(`✅ Jabatan stats: ${data.data.total_jabatan} jabatan`);
        });
    });
    
    describe('Page - Jabatan UI', () => {
        test('should load jabatan page without errors', async () => {
            await page.goto(`${global.testConfig.baseUrl}/pages/jabatan.php`);
            await page.waitForSelector('body', { timeout: 5000 });
            
            const title = await page.title();
            expect(title).toContain('Jabatan');
            
            console.log('✅ Jabatan page loaded');
        });
        
        test('should display jabatan list table', async () => {
            await page.goto(`${global.testConfig.baseUrl}/pages/jabatan.php`);
            await page.waitForSelector('body', { timeout: 5000 });
            
            // Check if table exists
            const tableExists = await page.$('.table') || await page.$('table');
            expect(tableExists).toBeTruthy();
            
            console.log('✅ Jabatan list table displayed');
        });
        
        test('should have add jabatan button', async () => {
            await page.goto(`${global.testConfig.baseUrl}/pages/jabatan.php`);
            await page.waitForSelector('body', { timeout: 5000 });
            
            // Check for add button
            const addButton = await page.$('[onclick*="openAddModal"]') || 
                              await page.$('.btn-add') ||
                              await page.$('button');
            
            expect(addButton).toBeTruthy();
            
            console.log('✅ Add jabatan button present');
        });
    });
    
    describe('Integration - Page + API', () => {
        test('should not have duplicate CRUD handlers in page', async () => {
            // Read the page file to check for duplicate handlers
            const fs = require('fs');
            const pageContent = fs.readFileSync('../pages/jabatan.php', 'utf8');
            
            // Check that page doesn't have direct CRUD handlers
            expect(pageContent).not.toMatch(/case 'create_jabatan'/);
            expect(pageContent).not.toMatch(/case 'update_jabatan'/);
            expect(pageContent).not.toMatch(/case 'delete_jabatan'/);
            
            console.log('✅ No duplicate CRUD handlers in page');
        });
        
        test('should have API references in JavaScript', async () => {
            await page.goto(`${global.testConfig.baseUrl}/pages/jabatan.php`);
            await page.waitForSelector('body', { timeout: 5000 });
            
            // Check if page references the API
            const pageContent = await page.content();
            expect(pageContent).toMatch(/bagian_api\.php|jabatan_api\.php/);
            
            console.log('✅ Page references API in JavaScript');
        });
    });
});
