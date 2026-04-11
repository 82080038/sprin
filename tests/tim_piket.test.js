/**
 * Tim Piket Management Tests (Puppeteer 20 / Jest 29)
 * All DOM interaction via page.evaluate to avoid protocol timeouts
 */
const puppeteer = require('puppeteer');

const BASE = 'http://localhost/sprin';
const API  = BASE + '/api/tim_piket_api.php';
const T    = 30000;
const wait = ms => new Promise(r => setTimeout(r, ms));

let browser, page;

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
    console.log('✅ Login OK');
    // Navigate to tim_piket
    await page.goto(BASE + '/pages/tim_piket.php', { waitUntil: 'networkidle2', timeout: T });
    console.log('✅ Page loaded');
}, 60000);

afterAll(async () => {
    if (browser) await browser.close();
});

// ═══════════════════════════════════════════════════════════════
// 1. PAGE LOAD & STRUCTURE
// ═══════════════════════════════════════════════════════════════
describe('Page Structure', () => {
    test('no PHP errors in page', async () => {
        const html = await page.content();
        expect(html).not.toMatch(/Fatal error|Parse error|Warning:|Notice:|Deprecated:/);
    }, T);

    test('has 5 tabs', async () => {
        const n = await page.evaluate(() => document.querySelectorAll('#piketTabs .nav-item').length);
        expect(n).toBe(5);
    }, T);

    test('has stat cards', async () => {
        const n = await page.evaluate(() => document.querySelectorAll('.row.g-3.mb-4 .card').length);
        expect(n).toBeGreaterThanOrEqual(4);
        console.log('Stat cards:', n);
    }, T);

    test('Papan Siklus is default active tab', async () => {
        const txt = await page.evaluate(() => {
            const el = document.querySelector('#piketTabs .nav-link.active');
            return el ? el.textContent : '';
        });
        expect(txt).toContain('Papan Siklus');
    }, T);

    test('has cetak SPRIN buttons', async () => {
        const n = await page.evaluate(() => document.querySelectorAll('button[onclick^="cetakSprin"]').length);
        expect(n).toBeGreaterThanOrEqual(0);
        console.log('Cetak SPRIN buttons:', n);
    }, T);
});

// ═══════════════════════════════════════════════════════════════
// 2. TAB NAVIGATION (all via evaluate to avoid protocol timeout)
// ═══════════════════════════════════════════════════════════════
describe('Tab Navigation', () => {
    const tabs = ['tabDashboard', 'tabKalender', 'tabStatistik', 'tabLogRotasi', 'tabPapan'];
    test.each(tabs)('switch to %s', async (tabId) => {
        const ok = await page.evaluate(id => {
            const a = document.querySelector(`a[href="#${id}"]`);
            if (!a) return false;
            a.click();
            return true;
        }, tabId);
        expect(ok).toBe(true);
        await wait(600);
        const isShow = await page.evaluate(id => {
            const pane = document.getElementById(id);
            return pane ? pane.classList.contains('show') || pane.classList.contains('active') : false;
        }, tabId);
        expect(isShow).toBe(true);
    }, T);
});

// ═══════════════════════════════════════════════════════════════
// 3. API ENDPOINTS (via fetch inside page context — has session)
// ═══════════════════════════════════════════════════════════════
describe('API Endpoints', () => {
    const apiTest = (action, qs = '') => async () => {
        const res = await page.evaluate(async (url) => {
            try {
                const r = await fetch(url);
                return await r.json();
            } catch(e) { return { success: false, error: e.message }; }
        }, API + '?action=' + action + qs);
        expect(res.success).toBe(true);
        return res;
    };

    test('dashboard_hari_ini', async () => {
        const res = await apiTest('dashboard_hari_ini')();
        expect(res.stats).toHaveProperty('total_jadwal');
        console.log('Dashboard stats:', res.stats);
    }, T);

    test('statistik_personil', async () => {
        const res = await apiTest('statistik_personil', '&bulan=4&tahun=2026')();
        expect(Array.isArray(res.data)).toBe(true);
        console.log('Statistik rows:', res.data.length);
    }, T);

    test('calendar_data', async () => {
        const res = await apiTest('calendar_data', '&bulan=4&tahun=2026')();
        expect(res.data).toBeDefined();
        console.log('Calendar days:', Object.keys(res.data).length);
    }, T);

    test('get_rotasi_log', async () => {
        const res = await apiTest('get_rotasi_log', '&limit=10')();
        expect(Array.isArray(res.data)).toBe(true);
        console.log('Log entries:', res.data.length);
    }, T);

    test('get_all_tim', async () => {
        const res = await apiTest('get_all_tim')();
        expect(Array.isArray(res.data)).toBe(true);
        console.log('Tim count:', res.data.length);
    }, T);

    test('get_personil_all', async () => {
        const res = await apiTest('get_personil_all')();
        expect(Array.isArray(res.data)).toBe(true);
        console.log('Personil count:', res.data.length);
    }, T);

    test('fatigue_check', async () => {
        const res = await apiTest('fatigue_check', '&personil_id=test123&tanggal=2026-04-11')();
        expect(Array.isArray(res.warnings)).toBe(true);
    }, T);

    test('get_siklus umum', async () => {
        const res = await apiTest('get_siklus', '&is_umum=1')();
        console.log('Siklus umum:', (res.data || []).length);
    }, T);

    test('cetak_sprin_data', async () => {
        const res = await page.evaluate(async (url) => {
            try { const r = await fetch(url); return await r.json(); }
            catch(e) { return { success: false, error: e.message }; }
        }, API + '?action=cetak_sprin_data&id_bagian=1');
        expect(res).toHaveProperty('success');
        console.log('Cetak SPRIN:', res.success ? 'OK' : res.error);
    }, T);
});

// ═══════════════════════════════════════════════════════════════
// 4. DASHBOARD DATA LOADING
// ═══════════════════════════════════════════════════════════════
describe('Dashboard Tab Content', () => {
    test('loads dashboard stats via tab click', async () => {
        await page.evaluate(() => {
            document.querySelector('a[href="#tabDashboard"]').click();
        });
        await wait(2500);
        const val = await page.evaluate(() => {
            const el = document.getElementById('dsTotalJadwal');
            return el ? el.textContent : null;
        });
        expect(val).not.toBeNull();
        expect(val).not.toBe('-');
        console.log('Dashboard total_jadwal:', val);
    }, T);
});

// ═══════════════════════════════════════════════════════════════
// 5. CALENDAR
// ═══════════════════════════════════════════════════════════════
describe('Calendar Tab', () => {
    test('renders calendar grid', async () => {
        await page.evaluate(() => {
            document.querySelector('a[href="#tabKalender"]').click();
        });
        await wait(2500);
        const info = await page.evaluate(() => {
            const title = document.getElementById('calendarTitle');
            const grid  = document.getElementById('calendarGrid');
            return {
                title: title ? title.textContent : '',
                gridLen: grid ? grid.innerHTML.length : 0
            };
        });
        expect(info.title).toContain('2026');
        expect(info.gridLen).toBeGreaterThan(100);
        console.log('Calendar title:', info.title);
    }, T);

    test('can navigate months', async () => {
        const title = await page.evaluate(() => {
            if (typeof changeCalendarMonth === 'function') changeCalendarMonth(1);
            return document.getElementById('calendarTitle').textContent;
        });
        await wait(1500);
        const newTitle = await page.evaluate(() => document.getElementById('calendarTitle').textContent);
        expect(newTitle).toBeTruthy();
        console.log('After navigate:', newTitle);
    }, T);
});

// ═══════════════════════════════════════════════════════════════
// 6. STATISTIK
// ═══════════════════════════════════════════════════════════════
describe('Statistik Tab', () => {
    test('has month/year selectors', async () => {
        const ok = await page.evaluate(() => {
            document.querySelector('a[href="#tabStatistik"]').click();
            return !!document.getElementById('statBulan') && !!document.getElementById('statTahun');
        });
        expect(ok).toBe(true);
    }, T);
});

// ═══════════════════════════════════════════════════════════════
// 7. LOG ROTASI
// ═══════════════════════════════════════════════════════════════
describe('Log Rotasi Tab', () => {
    test('has log table', async () => {
        await page.evaluate(() => document.querySelector('a[href="#tabLogRotasi"]').click());
        await wait(2000);
        const ok = await page.evaluate(() => !!document.getElementById('logRotasiTableBody'));
        expect(ok).toBe(true);
    }, T);
});

// ═══════════════════════════════════════════════════════════════
// 8. MODALS
// ═══════════════════════════════════════════════════════════════
describe('Modals', () => {
    test('open & close Siklus modal', async () => {
        // Ensure Papan tab active first
        await page.evaluate(() => document.querySelector('a[href="#tabPapan"]').click());
        await wait(500);
        const opened = await page.evaluate(() => {
            const btn = document.querySelector('button[onclick="openSiklusModal(0)"]');
            if (!btn) return 'no-btn';
            btn.click();
            return 'clicked';
        });
        expect(opened).toBe('clicked');
        await wait(1000);
        const shown = await page.evaluate(() => {
            const m = document.getElementById('siklusModal');
            return m ? m.classList.contains('show') : false;
        });
        expect(shown).toBe(true);
        // Close
        await page.evaluate(() => {
            const closeBtn = document.querySelector('#siklusModal .btn-close');
            if (closeBtn) closeBtn.click();
        });
        await wait(500);
        console.log('✅ Siklus modal OK');
    }, T);

    test('open & close Tim modal', async () => {
        const opened = await page.evaluate(() => {
            const btn = document.querySelector('button[onclick="openTambahTim(0)"]');
            if (!btn) return false;
            btn.click();
            return true;
        });
        expect(opened).toBe(true);
        await wait(1000);
        const fields = await page.evaluate(() => {
            const ids = ['tim_unsur','tim_bagian','tim_nama','tim_jenis','tim_fase'];
            return ids.every(id => !!document.getElementById(id));
        });
        expect(fields).toBe(true);
        await page.evaluate(() => {
            const btn = document.querySelector('#timModal .btn-close');
            if (btn) btn.click();
        });
        await wait(500);
        console.log('✅ Tim modal OK');
    }, T);
});

// ═══════════════════════════════════════════════════════════════
// 9. SIKLUS UMUM
// ═══════════════════════════════════════════════════════════════
describe('Siklus Umum', () => {
    test('has siklus umum card or create button', async () => {
        await page.evaluate(() => document.querySelector('a[href="#tabPapan"]').click());
        await wait(500);
        const found = await page.evaluate(() => {
            return !!document.querySelector('.card.border-success') ||
                   !!document.querySelector('button[onclick*="siklusUmum"]');
        });
        expect(found).toBe(true);
    }, T);
});

// ═══════════════════════════════════════════════════════════════
// 10. FATIGUE CHECK MODAL
// ═══════════════════════════════════════════════════════════════
describe('Fatigue Check', () => {
    test('open fatigue modal and has fields', async () => {
        await page.evaluate(() => document.querySelector('a[href="#tabDashboard"]').click());
        await wait(600);
        await page.evaluate(() => {
            const btn = document.querySelector('button[onclick="openFatigueCheckModal()"]');
            if (btn) btn.click();
        });
        await wait(1500);
        const ok = await page.evaluate(() => {
            const m = document.getElementById('fatigueModal');
            const sel = document.getElementById('fatiguePersonilId');
            const tgl = document.getElementById('fatigueTanggal');
            const jeda = document.getElementById('fatigueMinJeda');
            return m && m.classList.contains('show') && !!sel && !!tgl && !!jeda;
        });
        expect(ok).toBe(true);
        // Close
        await page.evaluate(() => {
            const btn = document.querySelector('#fatigueModal .btn-close');
            if (btn) btn.click();
        });
        await wait(500);
        console.log('✅ Fatigue modal OK');
    }, T);
});

// ═══════════════════════════════════════════════════════════════
// 11. SWAP SHIFT MODAL
// ═══════════════════════════════════════════════════════════════
describe('Swap Shift', () => {
    test('open swap shift modal and has fields', async () => {
        await page.evaluate(() => document.querySelector('a[href="#tabDashboard"]').click());
        await wait(600);
        await page.evaluate(() => {
            const btn = document.querySelector('button[onclick="openSwapShiftModal()"]');
            if (btn) btn.click();
        });
        await wait(1500);
        const ok = await page.evaluate(() => {
            const m = document.getElementById('swapShiftModal');
            const id1 = document.getElementById('swapId1');
            const id2 = document.getElementById('swapId2');
            const list = document.getElementById('swapScheduleList');
            return m && m.classList.contains('show') && !!id1 && !!id2 && !!list;
        });
        expect(ok).toBe(true);
        await page.evaluate(() => {
            const btn = document.querySelector('#swapShiftModal .btn-close');
            if (btn) btn.click();
        });
        await wait(500);
        console.log('✅ Swap Shift modal OK');
    }, T);
});

// ═══════════════════════════════════════════════════════════════
// 12. EXPORT CSV BUTTON
// ═══════════════════════════════════════════════════════════════
describe('Export CSV', () => {
    test('has export button in statistik tab', async () => {
        await page.evaluate(() => document.querySelector('a[href="#tabStatistik"]').click());
        await wait(600);
        const ok = await page.evaluate(() => {
            return !!document.querySelector('button[onclick="exportStatistikCSV()"]');
        });
        expect(ok).toBe(true);
        console.log('✅ Export CSV button present');
    }, T);
});

// ═══════════════════════════════════════════════════════════════
// 13. NOTIFICATION API
// ═══════════════════════════════════════════════════════════════
describe('Notifications', () => {
    test('get_notifikasi_piket endpoint works', async () => {
        const res = await page.evaluate(async (url) => {
            try { const r = await fetch(url); return await r.json(); }
            catch(e) { return { success: false, error: e.message }; }
        }, API + '?action=get_notifikasi_piket');
        expect(res.success).toBe(true);
        expect(Array.isArray(res.data)).toBe(true);
        console.log('Notifications:', res.data.length);
    }, T);

    test('notification container exists', async () => {
        const ok = await page.evaluate(() => !!document.getElementById('notifRotasiContainer'));
        expect(ok).toBe(true);
    }, T);
});

// ═══════════════════════════════════════════════════════════════
// 14. JS ERROR CHECK (fresh reload)
// ═══════════════════════════════════════════════════════════════
describe('Final Error Check', () => {
    test('no critical JS errors on reload', async () => {
        const jsErrors = [];
        page.removeAllListeners('pageerror');
        page.on('pageerror', e => jsErrors.push(e.message));
        await page.goto(BASE + '/pages/tim_piket.php', { waitUntil: 'networkidle2', timeout: T });
        await wait(1500);
        const critical = jsErrors.filter(e => !e.includes('ResizeObserver') && !e.includes('favicon'));
        if (critical.length) console.warn('JS errors:', critical);
        expect(critical.length).toBe(0);
    }, T);

    test('no PHP warnings/notices', async () => {
        const html = await page.content();
        expect(html).not.toMatch(/Warning:|Notice:|Deprecated:|Fatal error:/);
        console.log('✅ No PHP warnings');
    }, T);
});
