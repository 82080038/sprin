/**
 * Comprehensive Layout Screenshot and Analysis Tool
 * Takes screenshots of all pages and analyzes layout structure, content, and Bootstrap compliance
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class LayoutAnalyzer {
    constructor() {
        this.baseUrl = 'http://localhost/sprin';
        this.screenshotsDir = path.join(__dirname, 'layout_screenshots');
        this.analysisResults = {
            pages: {},
            summary: {
                total_pages: 0,
                bootstrap_compliance: 0,
                layout_consistency: 0,
                content_quality: 0,
                overall_score: 0
            }
        };
        
        // Ensure screenshots directory exists
        if (!fs.existsSync(this.screenshotsDir)) {
            fs.mkdirSync(this.screenshotsDir, { recursive: true });
        }
    }

    async runCompleteAnalysis() {
        console.log('='.repeat(70));
        console.log('COMPREHENSIVE LAYOUT SCREENSHOT AND ANALYSIS');
        console.log('='.repeat(70));
        console.log(`Base URL: ${this.baseUrl}`);
        console.log(`Timestamp: ${new Date().toISOString()}`);
        console.log('='.repeat(70));

        const browser = await puppeteer.launch({
            headless: false,
            defaultViewport: {
                width: 1920,
                height: 1080
            },
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });

        try {
            const page = await browser.newPage();
            
            // Analyze each page
            await this.analyzeLoginPage(page);
            await this.analyzeDashboard(page);
            await this.analyzePersonilPage(page);
            await this.analyzeOperasiPage(page);
            await this.analyzeCalendarPage(page);
            
            // Generate comprehensive report
            this.generateComprehensiveReport();
            
        } catch (error) {
            console.error('Analysis error:', error);
        } finally {
            await browser.close();
        }
    }

    async analyzeLoginPage(page) {
        console.log('\n[PAGE 1] Login Page Analysis');
        
        try {
            await page.goto(`${this.baseUrl}/login.php`, { waitUntil: 'networkidle2' });
            
            // Take screenshot
            const screenshotPath = await this.takeScreenshot(page, '01_login_page');
            
            // Analyze layout structure
            const analysis = await page.evaluate(() => {
                const results = {
                    page_info: {
                        title: document.title,
                        url: window.location.href,
                        viewport: {
                            width: window.innerWidth,
                            height: window.innerHeight
                        }
                    },
                    bootstrap_elements: {
                        containers: document.querySelectorAll('.container, .container-fluid').length,
                        rows: document.querySelectorAll('.row').length,
                        columns: document.querySelectorAll('[class*="col-"]').length,
                        forms: document.querySelectorAll('form').length,
                        form_controls: document.querySelectorAll('.form-control, .form-select').length,
                        buttons: document.querySelectorAll('.btn').length,
                        cards: document.querySelectorAll('.card').length,
                        alerts: document.querySelectorAll('.alert').length
                    },
                    layout_structure: {
                        has_login_container: document.querySelector('.login-container') !== null,
                        has_sidebar: document.querySelector('.login-sidebar') !== null,
                        has_form: document.querySelector('.login-form') !== null,
                        has_input_groups: document.querySelectorAll('.input-group').length,
                        has_badges: document.querySelectorAll('.badge').length
                    },
                    content_analysis: {
                        form_fields: document.querySelectorAll('input, select, textarea').length,
                        labels: document.querySelectorAll('label').length,
                        required_fields: document.querySelectorAll('[required]').length,
                        placeholders: document.querySelectorAll('[placeholder]').length,
                        icons: document.querySelectorAll('.fas, .far, .fab').length
                    },
                    responsive_design: {
                        has_viewport_meta: document.querySelector('meta[name="viewport"]') !== null,
                        has_responsive_classes: document.querySelectorAll('[class*="col-"]').length > 0,
                        has_flex_elements: document.querySelectorAll('.d-flex').length,
                        has_responsive_utilities: document.querySelectorAll('[class*="d-"]').length
                    },
                    css_analysis: {
                        custom_styles: document.querySelector('style:not([src])') !== null,
                        external_css: document.querySelectorAll('link[rel="stylesheet"]').length,
                        bootstrap_css: document.querySelector('link[href*="bootstrap"]') !== null,
                        font_awesome: document.querySelector('link[href*="font-awesome"]') !== null
                    }
                };
                
                // Analyze form structure
                const form = document.querySelector('form');
                if (form) {
                    results.form_structure = {
                        method: form.method,
                        action: form.action,
                        field_count: form.querySelectorAll('input, select, textarea').length,
                        has_validation: form.querySelector('[required]') !== null,
                        has_submit_button: form.querySelector('button[type="submit"]') !== null
                    };
                }
                
                // Analyze color scheme
                const computedStyles = getComputedStyle(document.body);
                results.color_scheme = {
                    primary_color: this.extractColor(computedStyles.backgroundColor),
                    text_color: this.extractColor(computedStyles.color),
                    accent_colors: this.extractAccentColors()
                };
                
                return results;
            });
            
            this.analysisResults.pages.login = {
                screenshot: screenshotPath,
                analysis: analysis,
                timestamp: new Date().toISOString()
            };
            
            console.log(`  \ud83d\udcf8 Screenshot: ${path.basename(screenshotPath)}`);
            console.log(`  \ud83d\udcca Bootstrap Elements: ${analysis.bootstrap_elements.containers} containers, ${analysis.bootstrap_elements.rows} rows, ${analysis.bootstrap_elements.columns} columns`);
            console.log(`  \u2705 Login Structure: ${analysis.layout_structure.has_login_container ? 'Present' : 'Missing'}`);
            console.log(`  \ud83c\udfa8 CSS Analysis: Bootstrap CSS ${analysis.css_analysis.bootstrap_css ? 'Present' : 'Missing'}, Custom Styles ${analysis.css_analysis.custom_styles ? 'Present' : 'Missing'}`);
            
        } catch (error) {
            console.error('Login page analysis failed:', error);
            this.analysisResults.pages.login = { error: error.message };
        }
    }

    async analyzeDashboard(page) {
        console.log('\n[PAGE 2] Dashboard Analysis');
        
        try {
            // Login first
            await this.login(page);
            
            await page.goto(`${this.baseUrl}/pages/main.php`, { waitUntil: 'networkidle2' });
            
            // Take screenshot
            const screenshotPath = await this.takeScreenshot(page, '02_dashboard');
            
            // Analyze dashboard structure
            const analysis = await page.evaluate(() => {
                const results = {
                    page_info: {
                        title: document.title,
                        url: window.location.href
                    },
                    bootstrap_elements: {
                        containers: document.querySelectorAll('.container, .container-fluid').length,
                        rows: document.querySelectorAll('.row').length,
                        columns: document.querySelectorAll('[class*="col-"]').length,
                        cards: document.querySelectorAll('.card').length,
                        badges: document.querySelectorAll('.badge').length,
                        buttons: document.querySelectorAll('.btn').length
                    },
                    layout_structure: {
                        has_sidebar: document.querySelector('.sidebar') !== null,
                        has_main_content: document.querySelector('.main-content') !== null,
                        has_navigation: document.querySelector('.navbar') !== null,
                        has_breadcrumb: document.querySelector('.breadcrumb') !== null,
                        has_page_header: document.querySelector('.page-header') !== null
                    },
                    content_analysis: {
                        stats_cards: document.querySelectorAll('.stats-card').length,
                        stats_numbers: document.querySelectorAll('.stats-number').length,
                        stats_labels: document.querySelectorAll('.stats-label').length,
                        sections: document.querySelectorAll('.card-header').length,
                        data_points: document.querySelectorAll('td, th').length
                    },
                    navigation_analysis: {
                        nav_links: document.querySelectorAll('.nav-link').length,
                        active_links: document.querySelectorAll('.nav-link.active').length,
                        dropdown_menus: document.querySelectorAll('.dropdown-menu').length,
                        mobile_toggle: document.querySelector('.mobile-menu-toggle') !== null
                    },
                    responsive_design: {
                        responsive_columns: document.querySelectorAll('[class*="col-lg-"], [class*="col-md-"], [class*="col-sm-"]').length,
                        flex_utilities: document.querySelectorAll('[class*="d-"]').length,
                        responsive_spacing: document.querySelectorAll('[class*="g-"], [class*="p-"], [class*="m-"]').length
                    }
                };
                
                // Analyze sidebar content
                const sidebar = document.querySelector('.sidebar');
                if (sidebar) {
                    results.sidebar_content = {
                        menu_items: sidebar.querySelectorAll('.nav-link').length,
                        badges: sidebar.querySelectorAll('.badge').length,
                        icons: sidebar.querySelectorAll('.fas').length,
                        sections: sidebar.querySelectorAll('.nav-divider, .nav-header').length
                    };
                }
                
                return results;
            });
            
            this.analysisResults.pages.dashboard = {
                screenshot: screenshotPath,
                analysis: analysis,
                timestamp: new Date().toISOString()
            };
            
            console.log(`  \ud83d\udcf8 Screenshot: ${path.basename(screenshotPath)}`);
            console.log(`  \ud83d\udcca Dashboard Elements: ${analysis.content_analysis.stats_cards} stats cards, ${analysis.navigation_analysis.nav_links} nav links`);
            console.log(`  \ud83d\udd04 Layout Structure: ${analysis.layout_structure.has_sidebar ? 'Sidebar' : 'No Sidebar'}, ${analysis.layout_structure.has_main_content ? 'Main Content' : 'No Main Content'}`);
            console.log(`  \ud83d\udcf1 Navigation: ${analysis.navigation_analysis.mobile_toggle ? 'Mobile Toggle Present' : 'No Mobile Toggle'}`);
            
        } catch (error) {
            console.error('Dashboard analysis failed:', error);
            this.analysisResults.pages.dashboard = { error: error.message };
        }
    }

    async analyzePersonilPage(page) {
        console.log('\n[PAGE 3] Personil Page Analysis');
        
        try {
            await page.goto(`${this.baseUrl}/pages/personil.php`, { waitUntil: 'networkidle2' });
            
            // Take screenshot
            const screenshotPath = await this.takeScreenshot(page, '03_personil_page');
            
            // Analyze personil page structure
            const analysis = await page.evaluate(() => {
                const results = {
                    page_info: {
                        title: document.title,
                        url: window.location.href
                    },
                    bootstrap_elements: {
                        containers: document.querySelectorAll('.container, .container-fluid').length,
                        rows: document.querySelectorAll('.row').length,
                        columns: document.querySelectorAll('[class*="col-"]').length,
                        cards: document.querySelectorAll('.card').length,
                        modals: document.querySelectorAll('.modal').length,
                        buttons: document.querySelectorAll('.btn').length,
                        tables: document.querySelectorAll('.table').length,
                        forms: document.querySelectorAll('form').length
                    },
                    layout_structure: {
                        has_personil_stats: document.querySelector('.personil-stats') !== null,
                        has_search_section: document.querySelector('.search-section') !== null,
                        has_loading_indicator: document.querySelector('#loadingIndicator') !== null,
                        has_personil_data: document.querySelector('#personilData') !== null
                    },
                    content_analysis: {
                        search_inputs: document.querySelectorAll('#searchInput').length,
                        modal_count: document.querySelectorAll('.modal').length,
                        table_count: document.querySelectorAll('.table').length,
                        unsur_cards: document.querySelectorAll('.unsur-card').length,
                        action_buttons: document.querySelectorAll('.btn-outline-primary, .btn-outline-danger').length
                    },
                    functionality_analysis: {
                        has_search_functionality: document.querySelector('#searchInput') !== null,
                        has_add_modal: document.querySelector('#addPersonilModal') !== null,
                        has_edit_modal: document.querySelector('#editPersonilModal') !== null,
                        has_clear_search: document.querySelector('#clearSearch') !== null,
                        has_loading_state: document.querySelector('#loadingIndicator') !== null
                    },
                    data_display: {
                        personil_count: document.querySelector('#totalPersonil')?.textContent || '0',
                        has_pagination: document.querySelectorAll('.pagination').length > 0,
                        has_sorting: document.querySelectorAll('.sortable').length > 0,
                        has_filtering: document.querySelectorAll('.filter').length > 0
                    }
                };
                
                return results;
            });
            
            this.analysisResults.pages.personil = {
                screenshot: screenshotPath,
                analysis: analysis,
                timestamp: new Date().toISOString()
            };
            
            console.log(`  \ud83d\udcf8 Screenshot: ${path.basename(screenshotPath)}`);
            console.log(`  \ud83d\udcca Personil Elements: ${analysis.content_analysis.modal_count} modals, ${analysis.content_analysis.table_count} tables`);
            console.log(`  \ud83d\udd04 Layout Structure: ${analysis.layout_structure.has_personil_stats ? 'Stats Section' : 'No Stats'}, ${analysis.layout_structure.has_search_section ? 'Search Section' : 'No Search'}`);
            console.log(`  \u2699\ufe0f Functionality: ${analysis.functionality_analysis.has_search_functionality ? 'Search' : 'No Search'}, ${analysis.functionality_analysis.has_add_modal ? 'Add Modal' : 'No Add Modal'}`);
            
        } catch (error) {
            console.error('Personil page analysis failed:', error);
            this.analysisResults.pages.personil = { error: error.message };
        }
    }

    async analyzeOperasiPage(page) {
        console.log('\n[PAGE 4] Operasi Page Analysis');
        
        try {
            await page.goto(`${this.baseUrl}/pages/operasi.php`, { waitUntil: 'networkidle2' });
            
            // Take screenshot
            const screenshotPath = await this.takeScreenshot(page, '04_operasi_page');
            
            // Analyze operasi page structure
            const analysis = await page.evaluate(() => {
                const results = {
                    page_info: {
                        title: document.title,
                        url: window.location.href
                    },
                    bootstrap_elements: {
                        containers: document.querySelectorAll('.container, .container-fluid').length,
                        rows: document.querySelectorAll('.row').length,
                        columns: document.querySelectorAll('[class*="col-"]').length,
                        cards: document.querySelectorAll('.card').length,
                        modals: document.querySelectorAll('.modal').length,
                        buttons: document.querySelectorAll('.btn').length,
                        tables: document.querySelectorAll('.table').length,
                        badges: document.querySelectorAll('.badge').length
                    },
                    layout_structure: {
                        has_operations_header: document.querySelector('.operations-header') !== null,
                        has_stats_grid: document.querySelector('.stats-grid') !== null,
                        has_operations_table: document.querySelector('.operations-table') !== null,
                        has_empty_state: document.querySelector('.empty-state') !== null
                    },
                    content_analysis: {
                        stat_cards: document.querySelectorAll('.stat-card').length,
                        table_rows: document.querySelectorAll('.table tbody tr').length,
                        badges_by_type: {
                            tingkat: document.querySelectorAll('.badge:contains("Terpusat"), .badge:contains("Kewilayahan")').length,
                            jenis: document.querySelectorAll('.badge:contains("Intelijen"), .badge:contains("Operasi")').length,
                            status: document.querySelectorAll('.badge:contains("Aktif"), .badge:contains("Selesai")').length
                        },
                        action_buttons: document.querySelectorAll('.btn-group .btn').length
                    },
                    data_analysis: {
                        total_operations: document.querySelectorAll('.table tbody tr').length,
                        has_add_button: document.querySelector('[data-bs-target="#addOperationModal"]') !== null,
                        has_filter_options: document.querySelectorAll('select, input[type="search"]').length,
                        has_pagination: document.querySelectorAll('.pagination').length > 0
                    },
                    functionality_analysis: {
                        has_add_modal: document.querySelector('#addOperationModal') !== null,
                        has_view_buttons: document.querySelectorAll('.fa-eye').length,
                        has_edit_buttons: document.querySelectorAll('.fa-edit').length,
                        has_delete_buttons: document.querySelectorAll('.fa-trash').length,
                        has_badge_system: document.querySelectorAll('.badge').length > 0
                    }
                };
                
                return results;
            });
            
            this.analysisResults.pages.operasi = {
                screenshot: screenshotPath,
                analysis: analysis,
                timestamp: new Date().toISOString()
            };
            
            console.log(`  \ud83d\udcf8 Screenshot: ${path.basename(screenshotPath)}`);
            console.log(`  \ud83d\udcca Operasi Elements: ${analysis.content_analysis.stat_cards} stat cards, ${analysis.data_analysis.total_operations} operations`);
            console.log(`  \ud83d\udd04 Layout Structure: ${analysis.layout_structure.has_operations_header ? 'Header' : 'No Header'}, ${analysis.layout_structure.has_stats_grid ? 'Stats Grid' : 'No Stats'}`);
            console.log(`  \u2699\ufe0f Functionality: ${analysis.functionality_analysis.has_add_modal ? 'Add Modal' : 'No Add Modal'}, ${analysis.data_analysis.total_operations} data rows`);
            
        } catch (error) {
            console.error('Operasi page analysis failed:', error);
            this.analysisResults.pages.operasi = { error: error.message };
        }
    }

    async analyzeCalendarPage(page) {
        console.log('\n[PAGE 5] Calendar Page Analysis');
        
        try {
            await page.goto(`${this.baseUrl}/pages/calendar_dashboard.php`, { waitUntil: 'networkidle2' });
            
            // Take screenshot
            const screenshotPath = await this.takeScreenshot(page, '05_calendar_page');
            
            // Analyze calendar page structure
            const analysis = await page.evaluate(() => {
                const results = {
                    page_info: {
                        title: document.title,
                        url: window.location.href
                    },
                    bootstrap_elements: {
                        containers: document.querySelectorAll('.container, .container-fluid').length,
                        rows: document.querySelectorAll('.row').length,
                        columns: document.querySelectorAll('[class*="col-"]').length,
                        cards: document.querySelectorAll('.card').length,
                        modals: document.querySelectorAll('.modal').length,
                        buttons: document.querySelectorAll('.btn').length
                    },
                    layout_structure: {
                        has_calendar_header: document.querySelector('.calendar-header') !== null,
                        has_calendar_controls: document.querySelector('.calendar-controls') !== null,
                        has_calendar_container: document.querySelector('.calendar-container') !== null,
                        has_piket_schedule: document.querySelector('.piket-schedule') !== null,
                        has_event_legend: document.querySelector('.event-legend') !== null
                    },
                    content_analysis: {
                        calendar_element: document.querySelector('#calendar') !== null,
                        fullcalendar_loaded: typeof FullCalendar !== 'undefined',
                        piket_teams: document.querySelectorAll('.piket-team').length,
                        event_legend_items: document.querySelectorAll('.legend-item').length,
                        control_buttons: document.querySelectorAll('.calendar-controls .btn').length
                    },
                    functionality_analysis: {
                        has_month_navigation: document.querySelectorAll('#prevMonth, #nextMonth').length === 2,
                        has_add_event_modal: document.querySelector('#addEventModal') !== null,
                        has_refresh_button: document.querySelector('#refreshCalendar') !== null,
                        has_current_month_button: document.querySelector('#currentMonth') !== null
                    },
                    calendar_integration: {
                        has_fullcalendar_css: document.querySelector('link[href*="fullcalendar"]') !== null,
                        has_fullcalendar_js: document.querySelector('script[src*="fullcalendar"]') !== null,
                        calendar_height: document.querySelector('#calendar') ? getComputedStyle(document.querySelector('#calendar')).height : '0px',
                        calendar_initialized: document.querySelector('#calendar .fc') !== null
                    }
                };
                
                return results;
            });
            
            this.analysisResults.pages.calendar = {
                screenshot: screenshotPath,
                analysis: analysis,
                timestamp: new Date().toISOString()
            };
            
            console.log(`  \ud83d\udcf8 Screenshot: ${path.basename(screenshotPath)}`);
            console.log(`  \ud83d\udcca Calendar Elements: ${analysis.content_analysis.control_buttons} controls, ${analysis.content_analysis.piket_teams} piket teams`);
            console.log(`  \ud83d\udd04 Layout Structure: ${analysis.layout_structure.has_calendar_header ? 'Header' : 'No Header'}, ${analysis.layout_structure.has_calendar_container ? 'Calendar Container' : 'No Calendar'}`);
            console.log(`  \ud83d\udcc5 Integration: ${analysis.calendar_integration.fullcalendar_loaded ? 'FullCalendar Loaded' : 'FullCalendar Not Loaded'}`);
            
        } catch (error) {
            console.error('Calendar page analysis failed:', error);
            this.analysisResults.pages.calendar = { error: error.message };
        }
    }

    async login(page) {
        try {
            await page.goto(`${this.baseUrl}/login.php`, { waitUntil: 'networkidle2' });
            
            // Fill login form
            await page.type('#username', 'bagops');
            await page.type('#password', 'admin123');
            
            // Click login button
            await Promise.all([
                page.waitForNavigation({ waitUntil: 'networkidle2' }),
                page.click('button[type="submit"]')
            ]);
            
            return true;
        } catch (error) {
            console.error('Login failed:', error);
            return false;
        }
    }

    async takeScreenshot(page, filename) {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const screenshotPath = path.join(this.screenshotsDir, `${filename}_${timestamp}.png`);
        
        await page.screenshot({
            path: screenshotPath,
            fullPage: true
        });
        
        return screenshotPath;
    }

    generateComprehensiveReport() {
        console.log('\n' + '='.repeat(70));
        console.log('COMPREHENSIVE LAYOUT ANALYSIS REPORT');
        console.log('='.repeat(70));
        
        // Calculate summary statistics
        const pages = Object.keys(this.analysisResults.pages);
        this.analysisResults.summary.total_pages = pages.length;
        
        let totalBootstrapCompliance = 0;
        let totalLayoutConsistency = 0;
        let totalContentQuality = 0;
        
        pages.forEach(pageName => {
            const pageData = this.analysisResults.pages[pageName];
            if (pageData.analysis) {
                // Calculate Bootstrap compliance
                const bootstrapElements = pageData.analysis.bootstrap_elements || {};
                const bootstrapScore = this.calculateBootstrapScore(bootstrapElements);
                totalBootstrapCompliance += bootstrapScore;
                
                // Calculate layout consistency
                const layoutStructure = pageData.analysis.layout_structure || {};
                const layoutScore = this.calculateLayoutScore(layoutStructure);
                totalLayoutConsistency += layoutScore;
                
                // Calculate content quality
                const contentAnalysis = pageData.analysis.content_analysis || {};
                const contentScore = this.calculateContentScore(contentAnalysis);
                totalContentQuality += contentScore;
            }
        });
        
        this.analysisResults.summary.bootstrap_compliance = Math.round(totalBootstrapCompliance / pages.length);
        this.analysisResults.summary.layout_consistency = Math.round(totalLayoutConsistency / pages.length);
        this.analysisResults.summary.content_quality = Math.round(totalContentQuality / pages.length);
        this.analysisResults.summary.overall_score = Math.round(
            (this.analysisResults.summary.bootstrap_compliance + 
             this.analysisResults.summary.layout_consistency + 
             this.analysisResults.summary.content_quality) / 3
        );
        
        // Display summary
        console.log(`Total Pages Analyzed: ${this.analysisResults.summary.total_pages}`);
        console.log(`Bootstrap Compliance: ${this.analysisResults.summary.bootstrap_compliance}%`);
        console.log(`Layout Consistency: ${this.analysisResults.summary.layout_consistency}%`);
        console.log(`Content Quality: ${this.analysisResults.summary.content_quality}%`);
        console.log(`Overall Score: ${this.analysisResults.summary.overall_score}%`);
        console.log('='.repeat(70));
        
        // Save detailed report
        const reportPath = path.join(__dirname, 'comprehensive-layout-analysis-report.json');
        fs.writeFileSync(reportPath, JSON.stringify(this.analysisResults, null, 2));
        
        console.log(`\ud83d\udccb Detailed report saved to: ${reportPath}`);
        console.log(`\ud83d\udcf8 Screenshots saved to: ${this.screenshotsDir}`);
        
        return this.analysisResults;
    }

    calculateBootstrapScore(elements) {
        let score = 0;
        let checks = 0;
        
        if (elements.containers > 0) { score += 20; }
        checks++;
        
        if (elements.rows > 0) { score += 20; }
        checks++;
        
        if (elements.columns > 0) { score += 20; }
        checks++;
        
        if (elements.cards >= 0) { score += 15; }
        checks++;
        
        if (elements.buttons >= 0) { score += 15; }
        checks++;
        
        if (elements.modals >= 0) { score += 10; }
        checks++;
        
        return Math.min(100, score);
    }

    calculateLayoutScore(structure) {
        let score = 0;
        let checks = Object.keys(structure).length;
        
        Object.values(structure).forEach(hasElement => {
            if (hasElement) score += 100 / checks;
        });
        
        return Math.round(score);
    }

    calculateContentScore(content) {
        let score = 0;
        let checks = 1;
        
        // Base score for having content
        score += 30;
        
        // Bonus points for rich content
        if (content.stats_cards > 0) score += 20;
        if (content.modal_count > 0) score += 20;
        if (content.table_count > 0) score += 15;
        if (content.action_buttons > 0) score += 15;
        
        return Math.min(100, score);
    }
}

// Run the analysis
if (require.main === module) {
    const analyzer = new LayoutAnalyzer();
    analyzer.runCompleteAnalysis().catch(console.error);
}

module.exports = LayoutAnalyzer;
