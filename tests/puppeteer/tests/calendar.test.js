/**
 * Calendar and Schedule Tests
 * Testing calendar dashboard and schedule management
 */

const config = require('../config');

function calendarTests(runner) {
    return {
        // Test 1: Calendar page loads
        async testCalendarPageLoads() {
            await runner.test('Calendar Page Loads', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                await page.goto(`${config.baseUrl}/pages/calendar_dashboard.php`);
                await page.waitForTimeout(3000); // Wait for calendar to render
                
                // Check for calendar component
                const hasCalendar = await page.evaluate(() => {
                    return !!(
                        document.querySelector('#calendar') ||
                        document.querySelector('.fc') || // FullCalendar
                        document.querySelector('.calendar') ||
                        document.querySelector('[class*="calendar"]')
                    );
                });
                
                if (!hasCalendar) {
                    console.log('   ⚠️ Calendar component not detected');
                }
                
                await runner.screenshot('calendar_page');
            });
        },
        
        // Test 2: Calendar API stats
        async testCalendarStatsApi() {
            await runner.test('API: Calendar Stats', async (page) => {
                const response = await page.evaluate(async () => {
                    const res = await fetch('http://localhost/sprint/api/calendar_api.php?action=getStats');
                    return await res.json();
                });
                
                if (response && response.success) {
                    const stats = response.data || {};
                    console.log(`   📊 Today: ${stats.today || 0}, Week: ${stats.week || 0}`);
                } else {
                    console.log('   ⚠️ Calendar stats not available');
                }
            });
        },
        
        // Test 3: Calendar events API
        async testCalendarEventsApi() {
            await runner.test('API: Calendar Events', async (page) => {
                const today = new Date();
                const start = today.toISOString().split('T')[0];
                const end = new Date(today.setMonth(today.getMonth() + 1)).toISOString().split('T')[0];
                
                const response = await page.evaluate(async (s, e) => {
                    const res = await fetch(`http://localhost/sprint/api/calendar_api.php?action=getEvents&start=${s}&end=${e}`);
                    return await res.json();
                }, start, end);
                
                if (response && response.success) {
                    const events = response.data || [];
                    console.log(`   📅 Found ${events.length} events`);
                }
            });
        },
        
        // Test 4: Schedule page elements
        async testScheduleElements() {
            await runner.test('Schedule Page Elements', async (page) => {
                await page.goto(`${config.baseUrl}/login.php`);
                await runner.type(config.selectors.login.usernameInput, config.credentials.username);
                await runner.type(config.selectors.login.passwordInput, config.credentials.password);
                await runner.click(config.selectors.login.submitButton);
                await page.waitForNavigation({ waitUntil: 'networkidle0' });
                
                await page.goto(`${config.baseUrl}/pages/calendar_dashboard.php`);
                await page.waitForTimeout(2000);
                
                // Look for schedule-related elements
                const elements = await page.evaluate(() => {
                    return {
                        hasCalendar: !!document.querySelector('#calendar, .fc, .calendar'),
                        hasAddButton: !!document.querySelector('button:contains("Tambah"), .btn-add, [data-action="add"]'),
                        hasFilters: !!document.querySelector('select, .filter'),
                        hasLegend: !!document.querySelector('.legend, [class*="legend"]')
                    };
                });
                
                console.log('   📋 Elements found:', JSON.stringify(elements));
                
                await runner.screenshot('schedule_elements');
            });
        },
        
        // Test 5: Google Calendar integration check
        async testGoogleCalendarIntegration() {
            await runner.test('Google Calendar Integration Check', async (page) => {
                const response = await page.evaluate(async () => {
                    const res = await fetch('http://localhost/sprint/api/google_calendar_api.php?action=status');
                    return await res.json();
                });
                
                if (response && typeof response === 'object') {
                    console.log('   ✅ Google Calendar API accessible');
                } else {
                    console.log('   ℹ️ Google Calendar integration status unknown');
                }
            });
        }
    };
}

module.exports = calendarTests;
