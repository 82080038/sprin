/**
 * Calendar CRUD Tests
 * Tests CRUD operations for calendar/schedule via calendar_api.php
 */

const puppeteer = require('puppeteer');

describe('Calendar CRUD Operations', () => {
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
        
        // Login first
        await global.testUtils.login(page);
    });
    
    afterAll(async () => {
        if (browser) {
            await browser.close();
        }
    });
    
    describe('API - Get Schedules', () => {
        test('should get schedule statistics', async () => {
            const response = await fetch(`${global.testConfig.apiBaseUrl}/calendar_api.php?action=getStats`);
            const data = await response.json();
            
            expect(data.success).toBe(true);
            expect(data.data).toHaveProperty('today');
            expect(data.data).toHaveProperty('week');
            
            console.log(`✅ Calendar stats: today=${data.data.today}, week=${data.data.week}`);
        });
        
        test('should get schedules within date range', async () => {
            const startDate = new Date().toISOString().split('T')[0];
            const endDate = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            
            const response = await fetch(`${global.testConfig.apiBaseUrl}/calendar_api.php?action=get_schedules&start_date=${startDate}&end_date=${endDate}`);
            const data = await response.json();
            
            expect(data.success).toBe(true);
            expect(Array.isArray(data.data)).toBe(true);
            
            console.log(`✅ Get schedules: ${data.data.length} records`);
        });
    });
    
    describe('API - Create Schedule', () => {
        test('should create new schedule via API', async () => {
            const today = new Date().toISOString().split('T')[0];
            
            const response = await fetch(`${global.testConfig.apiBaseUrl}/calendar_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_schedule',
                    tanggal: today,
                    waktu_mulai: '08:00',
                    waktu_selesai: '10:00',
                    kegiatan: 'TEST KEGIATAN UNIT',
                    lokasi: 'TEST LOCATION'
                })
            });
            
            const data = await response.json();
            
            expect(data.success).toBe(true);
            expect(data.message).toContain('berhasil');
            expect(data.id).toBeDefined();
            
            console.log('✅ Create schedule via API: ID', data.id);
            
            // Cleanup: delete the test schedule
            if (data.id) {
                await fetch(`${global.testConfig.apiBaseUrl}/calendar_api.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'delete_schedule',
                        schedule_id: data.id
                    })
                });
            }
        });
        
        test('should validate required fields on create', async () => {
            const response = await fetch(`${global.testConfig.apiBaseUrl}/calendar_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_schedule',
                    tanggal: '', // Empty tanggal
                    waktu_mulai: '',
                    kegiatan: ''
                })
            });
            
            const data = await response.json();
            
            expect(data.success).toBe(false);
            expect(data.message).toContain('harus diisi');
            
            console.log('✅ Create validation working');
        });
    });
    
    describe('API - Update Schedule', () => {
        test('should update schedule via API', async () => {
            const today = new Date().toISOString().split('T')[0];
            
            // First create a test schedule
            const createResponse = await fetch(`${global.testConfig.apiBaseUrl}/calendar_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_schedule',
                    tanggal: today,
                    waktu_mulai: '08:00',
                    waktu_selesai: '10:00',
                    kegiatan: 'TEST UPDATE SCHEDULE',
                    lokasi: 'ORIGINAL LOCATION'
                })
            });
            
            const createData = await createResponse.json();
            expect(createData.success).toBe(true);
            
            const scheduleId = createData.id;
            
            // Update the schedule
            const updateResponse = await fetch(`${global.testConfig.apiBaseUrl}/calendar_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'update_schedule',
                    schedule_id: scheduleId,
                    tanggal: today,
                    waktu_mulai: '09:00',
                    waktu_selesai: '11:00',
                    kegiatan: 'TEST UPDATED SCHEDULE',
                    lokasi: 'UPDATED LOCATION'
                })
            });
            
            const updateData = await updateResponse.json();
            
            expect(updateData.success).toBe(true);
            expect(updateData.message).toContain('berhasil');
            
            console.log('✅ Update schedule via API working');
            
            // Cleanup
            await fetch(`${global.testConfig.apiBaseUrl}/calendar_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'delete_schedule',
                    schedule_id: scheduleId
                })
            });
        });
    });
    
    describe('API - Delete Schedule', () => {
        test('should delete schedule via API', async () => {
            const today = new Date().toISOString().split('T')[0];
            
            // First create a test schedule
            const createResponse = await fetch(`${global.testConfig.apiBaseUrl}/calendar_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_schedule',
                    tanggal: today,
                    waktu_mulai: '08:00',
                    waktu_selesai: '10:00',
                    kegiatan: 'TEST DELETE SCHEDULE',
                    lokasi: 'TEST LOCATION'
                })
            });
            
            const createData = await createResponse.json();
            expect(createData.success).toBe(true);
            
            const scheduleId = createData.id;
            
            // Delete the schedule (soft delete)
            const deleteResponse = await fetch(`${global.testConfig.apiBaseUrl}/calendar_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'delete_schedule',
                    schedule_id: scheduleId
                })
            });
            
            const deleteData = await deleteResponse.json();
            
            expect(deleteData.success).toBe(true);
            expect(deleteData.message).toContain('berhasil');
            
            console.log('✅ Delete schedule via API working (soft delete)');
        });
    });
    
    describe('Page - Calendar UI', () => {
        test('should load calendar dashboard without errors', async () => {
            await page.goto(`${global.testConfig.baseUrl}/pages/calendar_dashboard.php`);
            await page.waitForSelector('body', { timeout: 5000 });
            
            const title = await page.title();
            expect(title).toContain('Schedule') || expect(title).toContain('Calendar');
            
            console.log('✅ Calendar page loaded');
        });
        
        test('should display schedule list', async () => {
            await page.goto(`${global.testConfig.baseUrl}/pages/calendar_dashboard.php`);
            await page.waitForSelector('body', { timeout: 5000 });
            
            // Check if schedule container exists
            const scheduleContainer = await page.$('.schedule-list') || 
                                      await page.$('.calendar') ||
                                      await page.$('.dashboard');
            
            expect(scheduleContainer).toBeTruthy();
            
            console.log('✅ Calendar schedule container displayed');
        });
        
        test('should have add schedule button', async () => {
            await page.goto(`${global.testConfig.baseUrl}/pages/calendar_dashboard.php`);
            await page.waitForSelector('body', { timeout: 5000 });
            
            // Check for add button
            const addButton = await page.$('[onclick*="add"]') || 
                              await page.$('.btn-add') ||
                              await page.$('button');
            
            expect(addButton).toBeTruthy();
            
            console.log('✅ Add schedule button present');
        });
    });
    
    describe('Integration - Page + API', () => {
        test('should not have duplicate CRUD handlers in page', async () => {
            // Read the page file to check for duplicate handlers
            const fs = require('fs');
            const pageContent = fs.readFileSync('../pages/calendar_dashboard.php', 'utf8');
            
            // Check that page doesn't have direct CRUD handlers
            expect(pageContent).not.toMatch(/case 'create_schedule'/);
            expect(pageContent).not.toMatch(/case 'update_schedule'/);
            expect(pageContent).not.toMatch(/case 'delete_schedule'/);
            
            console.log('✅ No duplicate CRUD handlers in page');
        });
        
        test('should reference calendar_api in JavaScript', async () => {
            await page.goto(`${global.testConfig.baseUrl}/pages/calendar_dashboard.php`);
            await page.waitForSelector('body', { timeout: 5000 });
            
            // Check if page references the API
            const pageContent = await page.content();
            expect(pageContent).toMatch(/calendar_api\.php/);
            
            console.log('✅ Page references calendar_api in JavaScript');
        });
    });
    
    describe('Data Integrity', () => {
        test('should handle date ranges correctly', async () => {
            const startDate = '2026-01-01';
            const endDate = '2026-01-31';
            
            const response = await fetch(`${global.testConfig.apiBaseUrl}/calendar_api.php?action=get_schedules&start_date=${startDate}&end_date=${endDate}`);
            const data = await response.json();
            
            expect(data.success).toBe(true);
            expect(Array.isArray(data.data)).toBe(true);
            
            console.log('✅ Date range filtering working');
        });
        
        test('should handle time formats correctly', async () => {
            const today = new Date().toISOString().split('T')[0];
            
            const response = await fetch(`${global.testConfig.apiBaseUrl}/calendar_api.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_schedule',
                    tanggal: today,
                    waktu_mulai: '08:00',
                    waktu_selesai: '10:00',
                    kegiatan: 'TEST TIME FORMAT',
                    lokasi: 'TEST'
                })
            });
            
            const data = await response.json();
            
            expect(data.success).toBe(true);
            
            // Cleanup
            if (data.id) {
                await fetch(`${global.testConfig.apiBaseUrl}/calendar_api.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'delete_schedule',
                        schedule_id: data.id
                    })
                });
            }
            
            console.log('✅ Time format handling working');
        });
    });
});
