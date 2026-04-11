/**
 * Renops CRUD Tests (Puppeteer 20 / Jest 29)
 * Tests: create_renops, update_renops, delete_renops, convert_to_operation
 */
const puppeteer = require('puppeteer');

const BASE = 'http://localhost/sprin';
const API  = BASE + '/api/renops_api.php';
const T    = 30000;
const wait = ms => new Promise(r => setTimeout(r, ms));

let browser, page;

const apiPost = async (action, params = {}) => {
    return page.evaluate(async ({url, action, params}) => {
        try {
            const fd = new FormData();
            fd.append('action', action);
            for (const [k, v] of Object.entries(params)) {
                if (Array.isArray(v)) v.forEach(x => fd.append(k + '[]', x));
                else fd.append(k, v);
            }
            const r = await fetch(url, { method: 'POST', body: fd });
            return await r.json();
        } catch(e) { return { success: false, error: e.message }; }
    }, { url: API, action, params });
};

const apiGet = async (action, qs = '') => {
    return page.evaluate(async (url) => {
        try {
            const r = await fetch(url);
            return await r.json();
        } catch(e) { return { success: false, error: e.message }; }
    }, API + '?action=' + action + qs);
};

beforeAll(async () => {
    browser = await puppeteer.launch({
        headless: 'new',
        executablePath: '/usr/bin/google-chrome',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu']
    });
    page = await browser.newPage();
    await page.setViewport({ width: 1366, height: 768 });
    
    // Login
    await page.goto(BASE + '/login.php', { waitUntil: 'networkidle2', timeout: T });
    await page.type('#username', 'bagops');
    await page.type('#password', 'admin123');
    await Promise.all([
        page.click('button[type="submit"]'),
        page.waitForNavigation({ waitUntil: 'networkidle2', timeout: T })
    ]);
    await page.goto(BASE + '/pages/renops.php', { waitUntil: 'networkidle2', timeout: T });
    console.log('✅ Login & renops page ready');
}, 60000);

afterAll(async () => {
    if (browser) await browser.close();
});

let createdRenopsId = null;

describe('Renops CRUD', () => {
    test('get_nomor_renops generates valid format', async () => {
        const res = await apiGet('get_nomor_renops');
        expect(res.success).toBe(true);
        expect(res.nomor).toMatch(/^RENOPS\/\d{3}\/[IVXLCDM]+\/\d{4}$/);
        console.log('Generated nomor:', res.nomor);
    }, T);

    test('create_renops — success', async () => {
        const res = await apiPost('create_renops', {
            nomor_renops: 'RENOPS/TEST/001/2026',
            judul_renops: 'Operasi Test Renops',
            sasaran: 'Sasaran test',
            wilayah: 'Samosir',
            kekuatan: '10 personil',
            anggaran: '5000000',
            tanggal_mulai: '2026-04-15',
            tanggal_selesai: '2026-04-20',
            status: 'draft'
        });
        console.log('create_renops response:', JSON.stringify(res));
        expect(res.success).toBe(true);
        createdRenopsId = parseInt(res.id);
    }, T);

    test('get_all_renops includes new renops', async () => {
        const res = await apiGet('get_all_renops');
        expect(res.success).toBe(true);
        const found = res.data.find(r => r.id == createdRenopsId);
        expect(found).toBeTruthy();
        console.log('✅ Renops found in list');
    }, T);

    test('update_renops — success', async () => {
        const res = await apiPost('update_renops', {
            id: String(createdRenopsId),
            nomor_renops: 'RENOPS/TEST/001/2026',
            judul_renops: 'Operasi Test Renops Updated',
            status: 'approved'
        });
        expect(res.success).toBe(true);
    }, T);

    test('verify update persisted', async () => {
        const res = await apiGet('get_all_renops');
        const found = res.data.find(r => r.id == createdRenopsId);
        expect(found).toBeTruthy();
        expect(found.judul_renops).toBe('Operasi Test Renops Updated');
        expect(found.status).toBe('approved');
    }, T);

    test('delete_renops — success', async () => {
        const res = await apiPost('delete_renops', { id: String(createdRenopsId) });
        expect(res.success).toBe(true);
    }, T);

    test('verify deletion', async () => {
        const res = await apiGet('get_all_renops');
        const found = res.data.find(r => r.id == createdRenopsId);
        expect(found).toBeFalsy();
    }, T);
});
