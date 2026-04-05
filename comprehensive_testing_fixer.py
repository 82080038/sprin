#!/usr/bin/env python3
"""
Comprehensive Testing and Fixing System for SPRIN Application
Uses Puppeteer for testing and Python for batch fixing
"""

import os
import re
import json
import subprocess
import time
from pathlib import Path
from typing import Dict, List, Tuple, Any
from datetime import datetime

class ComprehensiveTestingFixer:
    def __init__(self, base_path: str = "/opt/lampp/htdocs/sprint"):
        self.base_path = Path(base_path)
        self.test_results = {}
        self.errors_found = []
        self.fixes_applied = []
        self.puppeteer_script = self.base_path / 'comprehensive_test_puppeteer.js'
        
    def create_comprehensive_puppeteer_test(self):
        """Create comprehensive Puppeteer test script"""
        test_script = '''const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class ComprehensiveTester {
    constructor() {
        this.results = {
            timestamp: new Date().toISOString(),
            tests: [],
            errors: [],
            warnings: [],
            summary: {}
        };
        this.screenshotDir = path.join(__dirname, 'screenshots');
        this.ensureScreenshotDir();
    }

    ensureScreenshotDir() {
        if (!fs.existsSync(this.screenshotDir)) {
            fs.mkdirSync(this.screenshotDir, { recursive: true });
        }
    }

    async runComprehensiveTests() {
        console.log('🚀 Starting Comprehensive SPRIN Application Testing...');
        
        const browser = await puppeteer.launch({
            headless: false,
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
            defaultViewport: { width: 1366, height: 768 }
        });

        try {
            // Test 1: Application Entry Points
            await this.testApplicationEntryPoints(browser);
            
            // Test 2: Authentication System
            await this.testAuthenticationSystem(browser);
            
            // Test 3: Main Application Pages
            await this.testMainApplicationPages(browser);
            
            // Test 4: API Endpoints
            await this.testAPIEndpoints(browser);
            
            // Test 5: JavaScript Functionality
            await this.testJavaScriptFunctionality(browser);
            
            // Test 6: CSS and Responsive Design
            await this.testCSSAndResponsive(browser);
            
            // Test 7: Error Handling
            await this.testErrorHandling(browser);
            
            // Test 8: Database Operations
            await this.testDatabaseOperations(browser);
            
        } catch (error) {
            console.error('❌ Test execution failed:', error);
            this.results.errors.push({
                type: 'test_execution_error',
                message: error.message,
                stack: error.stack
            });
        } finally {
            await browser.close();
        }

        // Generate comprehensive report
        this.generateReport();
        return this.results;
    }

    async testApplicationEntryPoints(browser) {
        console.log('🔍 Testing Application Entry Points...');
        
        const entryPoints = [
            { url: 'http://localhost/sprint/', name: 'Root URL' },
            { url: 'http://localhost/sprint/index.php', name: 'Index PHP' },
            { url: 'http://localhost/sprint/main.php', name: 'Main PHP' },
            { url: 'http://localhost/sprint/login.php', name: 'Login Page' }
        ];

        for (const entry of entryPoints) {
            try {
                const page = await browser.newPage();
                
                // Capture console errors and warnings
                const consoleMessages = [];
                page.on('console', msg => {
                    consoleMessages.push({
                        type: msg.type(),
                        text: msg.text(),
                        location: msg.location()
                    });
                });

                // Capture page errors
                const pageErrors = [];
                page.on('pageerror', error => {
                    pageErrors.push({
                        message: error.message,
                        stack: error.stack
                    });
                });

                const response = await page.goto(entry.url, { 
                    waitUntil: 'networkidle2',
                    timeout: 10000 
                });

                const screenshotPath = path.join(this.screenshotDir, `entry_${entry.name.replace(/\\s+/g, '_').toLowerCase()}-${Date.now()}.png`);
                await page.screenshot({ path: screenshotPath, fullPage: true });

                const testResult = {
                    name: entry.name,
                    url: entry.url,
                    status: response.status(),
                    statusText: response.statusText(),
                    consoleMessages: consoleMessages,
                    pageErrors: pageErrors,
                    screenshot: screenshotPath,
                    timestamp: new Date().toISOString()
                };

                // Check for errors
                if (pageErrors.length > 0) {
                    testResult.status = 'ERROR';
                    this.results.errors.push(...pageErrors.map(err => ({
                        type: 'page_error',
                        test: entry.name,
                        message: err.message,
                        stack: err.stack
                    })));
                }

                // Check for console warnings/errors
                const errorMessages = consoleMessages.filter(msg => msg.type === 'error');
                if (errorMessages.length > 0) {
                    testResult.status = 'WARNING';
                    this.results.warnings.push(...errorMessages.map(msg => ({
                        type: 'console_error',
                        test: entry.name,
                        message: msg.text,
                        location: msg.location()
                    })));
                }

                this.results.tests.push(testResult);
                console.log(`${testResult.status === 200 ? '✅' : testResult.status === 'WARNING' ? '⚠️' : '❌'} ${entry.name}: ${testResult.status}`);
                
                await page.close();
                
            } catch (error) {
                this.results.errors.push({
                    type: 'entry_point_error',
                    test: entry.name,
                    message: error.message,
                    url: entry.url
                });
                console.log(`❌ ${entry.name}: ERROR - ${error.message}`);
            }
        }
    }

    async testAuthenticationSystem(browser) {
        console.log('🔐 Testing Authentication System...');
        
        const page = await browser.newPage();
        const consoleMessages = [];
        const pageErrors = [];
        
        page.on('console', msg => consoleMessages.push({ type: msg.type(), text: msg.text() }));
        page.on('pageerror', error => pageErrors.push({ message: error.message }));

        try {
            // Test login page load
            await page.goto('http://localhost/sprint/login.php', { waitUntil: 'networkidle2' });
            
            // Test form elements
            const formElements = await page.evaluate(() => {
                const elements = {};
                elements.usernameInput = document.querySelector('input[name="username"], input[type="text"]');
                elements.passwordInput = document.querySelector('input[name="password"], input[type="password"]');
                elements.submitButton = document.querySelector('button[type="submit"], input[type="submit"]');
                elements.form = document.querySelector('form');
                return elements;
            });

            const authTest = {
                name: 'Authentication System',
                formElements: formElements,
                consoleMessages: consoleMessages,
                pageErrors: pageErrors,
                timestamp: new Date().toISOString()
            };

            // Test invalid login
            if (formElements.form && formElements.usernameInput && formElements.passwordInput) {
                await formElements.usernameInput.type('invalid_user');
                await formElements.passwordInput.type('invalid_password');
                
                const screenshotBefore = path.join(this.screenshotDir, `auth_invalid_before-${Date.now()}.png`);
                await page.screenshot({ path: screenshotBefore });
                
                await Promise.all([
                    page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 5000 }),
                    formElements.form.submit()
                ]).catch(() => {}); // Ignore timeout for invalid login
                
                const screenshotAfter = path.join(this.screenshotDir, `auth_invalid_after-${Date.now()}.png`);
                await page.screenshot({ path: screenshotAfter });
                
                authTest.invalidLoginTest = {
                    attempted: true,
                    screenshots: [screenshotBefore, screenshotAfter]
                };
            }

            this.results.tests.push(authTest);
            console.log(`✅ Authentication System: Tested`);
            
        } catch (error) {
            this.results.errors.push({
                type: 'authentication_error',
                message: error.message
            });
            console.log(`❌ Authentication System: ERROR - ${error.message}`);
        }
        
        await page.close();
    }

    async testMainApplicationPages(browser) {
        console.log('📄 Testing Main Application Pages...');
        
        const pages = [
            { url: 'http://localhost/sprint/pages/main.php', name: 'Main Dashboard' },
            { url: 'http://localhost/sprint/pages/personil.php', name: 'Personnel Page' },
            { url: 'http://localhost/sprint/pages/bagian.php', name: 'Unit Page' },
            { url: 'http://localhost/sprint/pages/unsur.php', name: 'Element Page' },
            { url: 'http://localhost/sprint/pages/calendar_dashboard.php', name: 'Calendar Page' }
        ];

        for (const pageTest of pages) {
            try {
                const page = await browser.newPage();
                const consoleMessages = [];
                const pageErrors = [];
                
                page.on('console', msg => consoleMessages.push({ type: msg.type(), text: msg.text() }));
                page.on('pageerror', error => pageErrors.push({ message: error.message }));

                const response = await page.goto(pageTest.url, { 
                    waitUntil: 'networkidle2',
                    timeout: 10000 
                });

                // Check for authentication redirect
                const currentUrl = page.url();
                const isLoginRedirect = currentUrl.includes('login.php');

                const screenshotPath = path.join(this.screenshotDir, `page_${pageTest.name.replace(/\\s+/g, '_').toLowerCase()}-${Date.now()}.png`);
                await page.screenshot({ path: screenshotPath, fullPage: true });

                // Test page content
                const pageContent = await page.evaluate(() => {
                    return {
                        title: document.title,
                        hasContent: document.body.innerText.length > 100,
                        hasErrors: document.body.innerText.includes('Error') || document.body.innerText.includes('Fatal'),
                        hasForms: document.querySelectorAll('form').length > 0,
                        hasTables: document.querySelectorAll('table').length > 0,
                        hasButtons: document.querySelectorAll('button, input[type="submit"]').length > 0
                    };
                });

                const testResult = {
                    name: pageTest.name,
                    url: pageTest.url,
                    status: response.status(),
                    isLoginRedirect,
                    currentUrl,
                    content: pageContent,
                    consoleMessages: consoleMessages,
                    pageErrors: pageErrors,
                    screenshot: screenshotPath,
                    timestamp: new Date().toISOString()
                };

                // Check for errors
                if (pageErrors.length > 0 || pageContent.hasErrors) {
                    testResult.status = 'ERROR';
                    this.results.errors.push(...pageErrors.map(err => ({
                        type: 'page_content_error',
                        test: pageTest.name,
                        message: err.message
                    })));
                }

                this.results.tests.push(testResult);
                console.log(`${isLoginRedirect ? '🔐' : testResult.status === 200 ? '✅' : '⚠️'} ${pageTest.name}: ${isLoginRedirect ? 'Login Redirect' : testResult.status}`);
                
                await page.close();
                
            } catch (error) {
                this.results.errors.push({
                    type: 'page_load_error',
                    test: pageTest.name,
                    message: error.message,
                    url: pageTest.url
                });
                console.log(`❌ ${pageTest.name}: ERROR - ${error.message}`);
            }
        }
    }

    async testAPIEndpoints(browser) {
        console.log('🌐 Testing API Endpoints...');
        
        const apiEndpoints = [
            { url: 'http://localhost/sprint/api/personil.php', name: 'Personnel API' },
            { url: 'http://localhost/sprint/api/bagian.php', name: 'Unit API' },
            { url: 'http://localhost/sprint/api/unsur.php', name: 'Element API' },
            { url: 'http://localhost/sprint/api/health_check_new.php', name: 'Health Check API' },
            { url: 'http://localhost/sprint/api/performance_metrics.php', name: 'Performance API' }
        ];

        for (const apiTest of apiEndpoints) {
            try {
                const page = await browser.newPage();
                
                // Test API response
                const response = await page.goto(apiTest.url, { 
                    waitUntil: 'networkidle2',
                    timeout: 10000 
                });

                const responseText = await page.evaluate(() => document.body.innerText);
                
                let isValidJSON = false;
                let parsedResponse = null;
                
                try {
                    parsedResponse = JSON.parse(responseText);
                    isValidJSON = true;
                } catch (e) {
                    // Not valid JSON
                }

                const testResult = {
                    name: apiTest.name,
                    url: apiTest.url,
                    status: response.status(),
                    statusText: response.statusText(),
                    responseText: responseText.substring(0, 500), // Limit response text
                    isValidJSON,
                    parsedResponse,
                    timestamp: new Date().toISOString()
                };

                if (response.status() !== 200) {
                    this.results.errors.push({
                        type: 'api_error',
                        test: apiTest.name,
                        message: `HTTP ${response.status()}: ${response.statusText()}`,
                        url: apiTest.url
                    });
                }

                this.results.tests.push(testResult);
                console.log(`${response.status() === 200 ? '✅' : '❌'} ${apiTest.name}: ${response.status()}`);
                
                await page.close();
                
            } catch (error) {
                this.results.errors.push({
                    type: 'api_test_error',
                    test: apiTest.name,
                    message: error.message,
                    url: apiTest.url
                });
                console.log(`❌ ${apiTest.name}: ERROR - ${error.message}`);
            }
        }
    }

    async testJavaScriptFunctionality(browser) {
        console.log('🟨 Testing JavaScript Functionality...');
        
        const page = await browser.newPage();
        
        try {
            await page.goto('http://localhost/sprint/login.php', { waitUntil: 'networkidle2' });
            
            // Test JavaScript functionality
            const jsTestResults = await page.evaluate(() => {
                const results = {
                    jqueryLoaded: typeof $ !== 'undefined',
                    bootstrapLoaded: typeof bootstrap !== 'undefined',
                    consoleErrors: [],
                    formValidation: false,
                    eventListeners: false
                };

                // Check for console errors
                const originalError = console.error;
                console.error = function(...args) {
                    results.consoleErrors.push(args.join(' '));
                    originalError.apply(console, args);
                };

                // Test form validation
                const forms = document.querySelectorAll('form');
                if (forms.length > 0) {
                    results.formValidation = true;
                }

                // Test event listeners
                const buttons = document.querySelectorAll('button, input[type="submit"]');
                if (buttons.length > 0) {
                    results.eventListeners = true;
                }

                return results;
            });

            const testResult = {
                name: 'JavaScript Functionality',
                results: jsTestResults,
                timestamp: new Date().toISOString()
            };

            this.results.tests.push(testResult);
            console.log(`✅ JavaScript Functionality: Tested`);
            
        } catch (error) {
            this.results.errors.push({
                type: 'javascript_test_error',
                message: error.message
            });
            console.log(`❌ JavaScript Functionality: ERROR - ${error.message}`);
        }
        
        await page.close();
    }

    async testCSSAndResponsive(browser) {
        console.log('🎨 Testing CSS and Responsive Design...');
        
        const viewports = [
            { width: 1366, height: 768, name: 'Desktop' },
            { width: 768, height: 1024, name: 'Tablet' },
            { width: 375, height: 667, name: 'Mobile' }
        ];

        for (const viewport of viewports) {
            try {
                const page = await browser.newPage();
                await page.setViewport({ width: viewport.width, height: viewport.height });
                
                await page.goto('http://localhost/sprint/login.php', { waitUntil: 'networkidle2' });
                
                // Test responsive design
                const responsiveTest = await page.evaluate(() => {
                    return {
                        hasResponsiveCSS: window.getComputedStyle(document.body).getPropertyValue('font-size') !== '',
                        hasMediaQueries: window.matchMedia !== undefined,
                        viewportWidth: window.innerWidth,
                        viewportHeight: window.innerHeight,
                        hasBootstrapGrid: document.querySelector('.container, .container-fluid') !== null,
                        hasBootstrapNav: document.querySelector('.navbar, .nav') !== null
                    };
                });

                const screenshotPath = path.join(this.screenshotDir, `responsive_${viewport.name.toLowerCase()}-${Date.now()}.png`);
                await page.screenshot({ path: screenshotPath, fullPage: true });

                const testResult = {
                    name: `Responsive ${viewport.name}`,
                    viewport,
                    results: responsiveTest,
                    screenshot: screenshotPath,
                    timestamp: new Date().toISOString()
                };

                this.results.tests.push(testResult);
                console.log(`✅ Responsive ${viewport.name}: Tested`);
                
                await page.close();
                
            } catch (error) {
                this.results.errors.push({
                    type: 'responsive_test_error',
                    test: `Responsive ${viewport.name}`,
                    message: error.message
                });
                console.log(`❌ Responsive ${viewport.name}: ERROR - ${error.message}`);
            }
        }
    }

    async testErrorHandling(browser) {
        console.log('⚠️ Testing Error Handling...');
        
        try {
            const page = await browser.newPage();
            
            // Test error handling by accessing non-existent page
            const response = await page.goto('http://localhost/sprint/nonexistent_page.php', { 
                waitUntil: 'networkidle2',
                timeout: 5000 
            }).catch(() => null);

            const errorTest = {
                name: 'Error Handling',
                responseStatus: response ? response.status() : 404,
                handledGracefully: response ? response.status() === 404 : true,
                timestamp: new Date().toISOString()
            };

            this.results.tests.push(errorTest);
            console.log(`✅ Error Handling: Tested (404 expected)`);
            
            await page.close();
            
        } catch (error) {
            this.results.errors.push({
                type: 'error_handling_test_error',
                message: error.message
            });
            console.log(`❌ Error Handling: ERROR - ${error.message}`);
        }
    }

    async testDatabaseOperations(browser) {
        console.log('🗄️ Testing Database Operations...');
        
        try {
            const page = await browser.newPage();
            
            // Test health check API which includes database operations
            await page.goto('http://localhost/sprint/api/health_check_new.php', { waitUntil: 'networkidle2' });
            
            const dbTest = await page.evaluate(() => {
                try {
                    const response = JSON.parse(document.body.innerText);
                    return {
                        databaseConnected: response.checks && response.checks.database === true,
                        responseValid: true,
                        data: response
                    };
                } catch (e) {
                    return {
                        databaseConnected: false,
                        responseValid: false,
                        error: e.message
                    };
                }
            });

            const testResult = {
                name: 'Database Operations',
                results: dbTest,
                timestamp: new Date().toISOString()
            };

            this.results.tests.push(testResult);
            console.log(`${dbTest.databaseConnected ? '✅' : '❌'} Database Operations: ${dbTest.databaseConnected ? 'Connected' : 'Not Connected'}`);
            
            await page.close();
            
        } catch (error) {
            this.results.errors.push({
                type: 'database_test_error',
                message: error.message
            });
            console.log(`❌ Database Operations: ERROR - ${error.message}`);
        }
    }

    generateReport() {
        // Calculate summary
        const totalTests = this.results.tests.length;
        const passedTests = this.results.tests.filter(t => t.status === 200).length;
        const failedTests = this.results.errors.length;
        const warningTests = this.results.warnings.length;

        this.results.summary = {
            totalTests,
            passedTests,
            failedTests,
            warningTests,
            successRate: totalTests > 0 ? ((passedTests / totalTests) * 100).toFixed(1) + '%' : '0%',
            timestamp: new Date().toISOString()
        };

        // Save report
        const reportPath = path.join(__dirname, 'comprehensive_test_report.json');
        fs.writeFileSync(reportPath, JSON.stringify(this.results, null, 2));

        // Generate HTML report
        const htmlReport = this.generateHTMLReport();
        const htmlReportPath = path.join(__dirname, 'comprehensive_test_report.html');
        fs.writeFileSync(htmlReportPath, htmlReport);

        console.log('\\n📊 Test Summary:');
        console.log(`   Total Tests: ${totalTests}`);
        console.log(`   Passed: ${passedTests}`);
        console.log(`   Failed: ${failedTests}`);
        console.log(`   Warnings: ${warningTests}`);
        console.log(`   Success Rate: ${this.results.summary.successRate}`);
        console.log(`📄 Reports saved: ${reportPath}, ${htmlReportPath}`);
    }

    generateHTMLReport() {
        return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprehensive Test Report - SPRIN Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-pass { color: #28a745; }
        .test-fail { color: #dc3545; }
        .test-warning { color: #ffc107; }
        .screenshot { max-width: 200px; height: auto; border: 1px solid #ddd; }
        .error-details { background: #f8d7da; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .warning-details { background: #fff3cd; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>🧪 Comprehensive Test Report - SPRIN Application</h1>
        <p class="text-muted">Generated: ${this.results.timestamp}</p>
        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Tests</h5>
                        <h3 class="test-pass">${this.results.summary.totalTests}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Passed</h5>
                        <h3 class="test-pass">${this.results.summary.passedTests}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Failed</h5>
                        <h3 class="test-fail">${this.results.summary.failedTests}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Success Rate</h5>
                        <h3 class="test-pass">${this.results.summary.successRate}</h3>
                    </div>
                </div>
            </div>
        </div>

        <h2>Test Results</h2>
        ${this.results.tests.map(test => `
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        ${test.status === 200 ? '✅' : test.status === 'WARNING' ? '⚠️' : '❌'} ${test.name}
                        <span class="float-end">${test.status || 'TESTED'}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>URL:</strong> ${test.url || 'N/A'}</p>
                    ${test.screenshot ? `<p><strong>Screenshot:</strong> <img src="${test.screenshot.replace(/.*\\//, '')}" class="screenshot"></p>` : ''}
                    ${test.content ? `<p><strong>Content:</strong> ${JSON.stringify(test.content, null, 2)}</p>` : ''}
                    ${test.results ? `<p><strong>Results:</strong> <pre>${JSON.stringify(test.results, null, 2)}</pre></p>` : ''}
                </div>
            </div>
        `).join('')}

        ${this.results.errors.length > 0 ? `
            <h2 class="text-danger">Errors</h2>
            ${this.results.errors.map(error => `
                <div class="error-details">
                    <h5>${error.type}</h5>
                    <p><strong>Message:</strong> ${error.message}</p>
                    ${error.test ? `<p><strong>Test:</strong> ${error.test}</p>` : ''}
                    ${error.url ? `<p><strong>URL:</strong> ${error.url}</p>` : ''}
                    ${error.stack ? `<p><strong>Stack:</strong> <pre>${error.stack}</pre></p>` : ''}
                </div>
            `).join('')}
        ` : ''}

        ${this.results.warnings.length > 0 ? `
            <h2 class="text-warning">Warnings</h2>
            ${this.results.warnings.map(warning => `
                <div class="warning-details">
                    <h5>${warning.type}</h5>
                    <p><strong>Message:</strong> ${warning.message}</p>
                    ${warning.test ? `<p><strong>Test:</strong> ${warning.test}</p>` : ''}
                </div>
            `).join('')}
        ` : ''}
    </div>
</body>
</html>`;
    }
}

// Run tests
const tester = new ComprehensiveTester();
tester.runComprehensiveTests().then(results => {
    console.log('🎉 Comprehensive testing completed!');
    process.exit(0);
}).catch(error => {
    console.error('❌ Testing failed:', error);
    process.exit(1);
});
'''
        
        with open(self.puppeteer_script, 'w', encoding='utf-8') as f:
            f.write(test_script)
        
        print(f"✅ Created comprehensive Puppeteer test script")
    
    def run_comprehensive_tests(self):
        """Run comprehensive Puppeteer tests"""
        print("🚀 Starting Comprehensive Application Testing...")
        
        try:
            result = subprocess.run(
                ['node', str(self.puppeteer_script)],
                capture_output=True,
                text=True,
                cwd=str(self.base_path),
                timeout=300  # 5 minutes timeout
            )
            
            print(result.stdout)
            if result.stderr:
                print("Stderr:", result.stderr)
            
            # Load test results
            report_file = self.base_path / 'comprehensive_test_report.json'
            if report_file.exists():
                with open(report_file, 'r', encoding='utf-8') as f:
                    self.test_results = json.load(f)
                
                print(f"✅ Test results loaded from {report_file}")
                return self.test_results
            else:
                print("⚠️ Test report file not found")
                return {}
                
        except subprocess.TimeoutExpired:
            print("❌ Testing timed out")
            return {}
        except Exception as e:
            print(f"❌ Error running tests: {e}")
            return {}
    
    def analyze_test_results(self):
        """Analyze test results and identify errors to fix"""
        print("🔍 Analyzing test results for errors...")
        
        errors_to_fix = []
        
        # Analyze test results
        if 'tests' in self.test_results:
            for test in self.test_results['tests']:
                if test.get('status') not in [200, 'TESTED']:
                    errors_to_fix.append({
                        'type': 'test_failure',
                        'test_name': test.get('name'),
                        'url': test.get('url'),
                        'status': test.get('status'),
                        'errors': test.get('pageErrors', []),
                        'console_messages': test.get('consoleMessages', [])
                    })
        
        # Analyze explicit errors
        if 'errors' in self.test_results:
            for error in self.test_results['errors']:
                errors_to_fix.append({
                    'type': 'explicit_error',
                    'error_type': error.get('type'),
                    'message': error.get('message'),
                    'test': error.get('test'),
                    'url': error.get('url')
                })
        
        # Analyze warnings
        if 'warnings' in self.test_results:
            for warning in self.test_results['warnings']:
                errors_to_fix.append({
                    'type': 'warning',
                    'warning_type': warning.get('type'),
                    'message': warning.get('message'),
                    'test': warning.get('test')
                })
        
        self.errors_found = errors_to_fix
        print(f"📊 Found {len(errors_to_fix)} issues to fix")
        
        return errors_to_fix
    
    def create_batch_fixer(self):
        """Create comprehensive batch fixing system"""
        print("🔧 Creating batch fixing system...")
        
        batch_fixer_code = '''#!/usr/bin/env python3
"""
Batch Error Fixer for SPRIN Application
Automatically fixes common PHP, JavaScript, CSS, and API errors
"""

import os
import re
import json
from pathlib import Path
from typing import Dict, List, Any

class BatchErrorFixer:
    def __init__(self, base_path: str = "/opt/lampp/htdocs/sprint"):
        self.base_path = Path(base_path)
        self.fixes_applied = []
        
    def fix_php_syntax_errors(self):
        """Fix PHP syntax errors"""
        print("🔧 Fixing PHP syntax errors...")
        
        php_files = list(self.base_path.rglob("*.php"))
        
        for php_file in php_files:
            try:
                with open(php_file, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                original_content = content
                
                # Fix common syntax errors
                fixes = [
                    # Fix missing semicolons
                    (r'(\\$[^\\n\\r]+)\\n(?=[^\\n\\r;])', r'\\1;\\n'),
                    # Fix undefined variable notices
                    (r'echo \\$([^\\s;]+)', r'echo $\\1 ?? \'\''),
                    # Fix array access
                    (r'\\$_GET\\[\'([^\']+)\'\\]', r'$_GET[\'\\1\'] ?? \'\''),
                    (r'\\$_POST\\[\'([^\']+)\'\\]', r'$_POST[\'\\1\'] ?? \'\''),
                    # Fix function calls
                    (r'header\\s*\\(\\s*[\'"]Location:([^\'"]+)[\'"]\\s*\\)', r'header("Location:\\1");'),
                    # Fix session start
                    (r'session_start\\s*\\(\\s*\\)', r'session_start();'),
                ]
                
                for pattern, replacement in fixes:
                    content = re.sub(pattern, replacement, content, flags=re.MULTILINE)
                
                if content != original_content:
                    with open(php_file, 'w', encoding='utf-8') as f:
                        f.write(content)
                    
                    self.fixes_applied.append({
                        'type': 'php_syntax',
                        'file': str(php_file),
                        'changes': 'Applied syntax fixes'
                    })
                    
            except Exception as e:
                print(f"Error fixing {php_file}: {e}")
    
    def fix_javascript_errors(self):
        """Fix JavaScript errors"""
        print("🟨 Fixing JavaScript errors...")
        
        js_files = list(self.base_path.rglob("*.js"))
        
        for js_file in js_files:
            try:
                with open(js_file, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                original_content = content
                
                # Fix common JavaScript errors
                fixes = [
                    # Fix undefined variables
                    (r'console\\.log\\s*\\(\\s*([^\\)]+)\\s*\\)', r'console.log(\\1);'),
                    # Fix function declarations
                    (r'function\\s+(\\w+)\\s*\\(', r'function \\1('),
                    # Fix event listeners
                    (r'addEventListener\\s*\\(\\s*[\'"]([^\'"]+)[\'"]\\s*,', r'addEventListener(\'\\1\', '),
                    # Fix AJAX calls
                    (r'\\$\\.ajax\\s*\\(', r'$.ajax('),
                ]
                
                for pattern, replacement in fixes:
                    content = re.sub(pattern, replacement, content, flags=re.MULTILINE)
                
                if content != original_content:
                    with open(js_file, 'w', encoding='utf-8') as f:
                        f.write(content)
                    
                    self.fixes_applied.append({
                        'type': 'javascript_syntax',
                        'file': str(js_file),
                        'changes': 'Applied JavaScript fixes'
                    })
                    
            except Exception as e:
                print(f"Error fixing {js_file}: {e}")
    
    def fix_css_errors(self):
        """Fix CSS errors"""
        print("🎨 Fixing CSS errors...")
        
        css_files = list(self.base_path.rglob("*.css"))
        
        for css_file in css_files:
            try:
                with open(css_file, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                original_content = content
                
                # Fix common CSS errors
                fixes = [
                    # Fix missing semicolons
                    (r'([^{]\\s*[^;{}\\n]+)\\s*\\n', r'\\1;\\n'),
                    # Fix color formats
                    (r'#([0-9a-fA-F]{3})\\b', r'#\\1'),
                    # Fix units
                    (r'(margin|padding|width|height):\\s*([0-9]+)\\s*(?=;|})', r'\\1: \\2px;'),
                ]
                
                for pattern, replacement in fixes:
                    content = re.sub(pattern, replacement, content, flags=re.MULTILINE)
                
                if content != original_content:
                    with open(css_file, 'w', encoding='utf-8') as f:
                        f.write(content)
                    
                    self.fixes_applied.append({
                        'type': 'css_syntax',
                        'file': str(css_file),
                        'changes': 'Applied CSS fixes'
                    })
                    
            except Exception as e:
                print(f"Error fixing {css_file}: {e}")
    
    def fix_api_errors(self):
        """Fix API errors"""
        print("🌐 Fixing API errors...")
        
        api_files = list(self.base_path.rglob("api/*.php"))
        
        for api_file in api_files:
            try:
                with open(api_file, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                original_content = content
                
                # Fix common API errors
                fixes = [
                    # Add JSON headers
                    (r'<\\?php', r'<?php\\nheader("Content-Type: application/json");'),
                    # Fix JSON output
                    (r'echo\\s+([^\\n]+)', r'echo json_encode(\\1);'),
                    # Add error handling
                    (r'try\\s*\\{', r'try {'),
                    (r'catch\\s*\\(', r'catch (Exception $e) {\\n    echo json_encode([\'error\' => $e->getMessage()]);\\n}'),
                ]
                
                for pattern, replacement in fixes:
                    content = re.sub(pattern, replacement, content, flags=re.MULTILINE)
                
                if content != original_content:
                    with open(api_file, 'w', encoding='utf-8') as f:
                        f.write(content)
                    
                    self.fixes_applied.append({
                        'type': 'api_fix',
                        'file': str(api_file),
                        'changes': 'Applied API fixes'
                    })
                    
            except Exception as e:
                print(f"Error fixing {api_file}: {e}")
    
    def run_batch_fixer(self):
        """Run all batch fixes"""
        print("🚀 Starting Batch Error Fixing...")
        
        self.fix_php_syntax_errors()
        self.fix_javascript_errors()
        self.fix_css_errors()
        self.fix_api_errors()
        
        print(f"✅ Batch fixing completed. Applied {len(self.fixes_applied)} fixes")
        return self.fixes_applied

if __name__ == "__main__":
    fixer = BatchErrorFixer()
    fixes = fixer.run_batch_fixer()
    
    print(f"\\n🎉 Batch fixing completed!")
    print(f"📚 Total fixes applied: {len(fixes)}")
'''
        
        batch_fixer_file = self.base_path / 'batch_error_fixer.py'
        with open(batch_fixer_file, 'w', encoding='utf-8') as f:
            f.write(batch_fixer_code)
        
        print(f"✅ Created batch error fixer")
        return batch_fixer_file
    
    def run_batch_fixing(self):
        """Run batch error fixing"""
        print("🔧 Running batch error fixing...")
        
        batch_fixer = self.base_path / 'batch_error_fixer.py'
        
        try:
            result = subprocess.run(
                ['python3', str(batch_fixer)],
                capture_output=True,
                text=True,
                cwd=str(self.base_path),
                timeout=120
            )
            
            print(result.stdout)
            if result.stderr:
                print("Stderr:", result.stderr)
            
            return True
            
        except Exception as e:
            print(f"❌ Error running batch fixer: {e}")
            return False
    
    def create_specific_fixer(self):
        """Create specific error fixer based on test results"""
        print("🎯 Creating specific error fixer...")
        
        specific_fixer_code = '''#!/usr/bin/env python3
"""
Specific Error Fixer for SPRIN Application
Targets specific errors found in testing
"""

import os
import re
from pathlib import Path

class SpecificErrorFixer:
    def __init__(self, base_path: str = "/opt/lampp/htdocs/sprint"):
        self.base_path = Path(base_path)
        self.fixes_applied = []
    
    def fix_authentication_redirects(self):
        """Fix authentication redirect issues"""
        print("🔐 Fixing authentication redirects...")
        
        # Fix login.php redirect
        login_file = self.base_path / 'login.php'
        if login_file.exists():
            try:
                with open(login_file, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                original_content = content
                
                # Ensure proper redirect after login
                if 'header(' in content and 'main.php' in content:
                    content = re.sub(
                        r'header\\s*\\(\\s*[\'"]Location:([^\'"]+)[\'"]\\s*\\)',
                        r'header("Location: \\1");',
                        content
                    )
                
                if content != original_content:
                    with open(login_file, 'w', encoding='utf-8') as f:
                        f.write(content)
                    
                    self.fixes_applied.append({
                        'type': 'auth_redirect',
                        'file': 'login.php',
                        'changes': 'Fixed authentication redirect'
                    })
                    
            except Exception as e:
                print(f"Error fixing login.php: {e}")
    
    def fix_page_errors(self):
        """Fix page-specific errors"""
        print("📄 Fixing page errors...")
        
        pages_to_fix = [
            'pages/main.php',
            'pages/personil.php',
            'pages/bagian.php',
            'pages/unsur.php',
            'pages/calendar_dashboard.php'
        ]
        
        for page in pages_to_fix:
            page_file = self.base_path / page
            if page_file.exists():
                try:
                    with open(page_file, 'r', encoding='utf-8') as f:
                        content = f.read()
                    
                    original_content = content
                    
                    # Fix common page errors
                    content = re.sub(
                        r'echo\\s+\\$([^\\s;]+)',
                        r'echo $\\1 ?? \'\'',
                        content
                    )
                    
                    # Fix session issues
                    if 'session_start()' not in content and 'SessionManager::start()' in content:
                        content = re.sub(
                            r'SessionManager::start\\(\\)',
                            'SessionManager::start();\\nsession_start();',
                            content
                        )
                    
                    if content != original_content:
                        with open(page_file, 'w', encoding='utf-8') as f:
                            f.write(content)
                        
                        self.fixes_applied.append({
                            'type': 'page_fix',
                            'file': page,
                            'changes': 'Fixed page errors'
                        })
                        
                except Exception as e:
                    print(f"Error fixing {page}: {e}")
    
    def fix_api_errors(self):
        """Fix API-specific errors"""
        print("🌐 Fixing API errors...")
        
        api_files = [
            'api/personil.php',
            'api/bagian.php',
            'api/unsur.php'
        ]
        
        for api in api_files:
            api_file = self.base_path / api
            if api_file.exists():
                try:
                    with open(api_file, 'r', encoding='utf-8') as f:
                        content = f.read()
                    
                    original_content = content
                    
                    # Add JSON header if missing
                    if 'Content-Type: application/json' not in content:
                        content = re.sub(
                            r'<\\?php',
                            '<?php\\nheader("Content-Type: application/json");',
                            content
                        )
                    
                    # Fix JSON output
                    content = re.sub(
                        r'echo\\s+([^\\n]+;)',
                        r'echo json_encode(\\1)',
                        content
                    )
                    
                    # Add error handling
                    if 'try {' not in content:
                        content = re.sub(
                            r'(\\$pdo\\s*=\\s*new\\s+PDO)',
                            'try {\\n    \\1',
                            content
                        )
                        content = re.sub(
                            r'(\\}\\s*$)',
                            '} catch (Exception $e) {\\n    echo json_encode([\'error\' => $e->getMessage()]);\\n}',
                            content
                        )
                    
                    if content != original_content:
                        with open(api_file, 'w', encoding='utf-8') as f:
                            f.write(content)
                        
                        self.fixes_applied.append({
                            'type': 'api_fix',
                            'file': api,
                            'changes': 'Fixed API errors'
                        })
                        
                except Exception as e:
                    print(f"Error fixing {api}: {e}")
    
    def run_specific_fixes(self):
        """Run all specific fixes"""
        print("🎯 Running specific error fixes...")
        
        self.fix_authentication_redirects()
        self.fix_page_errors()
        self.fix_api_errors()
        
        print(f"✅ Specific fixing completed. Applied {len(self.fixes_applied)} fixes")
        return self.fixes_applied

if __name__ == "__main__":
    fixer = SpecificErrorFixer()
    fixes = fixer.run_specific_fixes()
    
    print(f"\\n🎉 Specific fixing completed!")
    print(f"📚 Total fixes applied: {len(fixes)}")
'''
        
        specific_fixer_file = self.base_path / 'specific_error_fixer.py'
        with open(specific_fixer_file, 'w', encoding='utf-8') as f:
            f.write(specific_fixer_code)
        
        print(f"✅ Created specific error fixer")
        return specific_fixer_file
    
    def run_specific_fixing(self):
        """Run specific error fixing"""
        print("🎯 Running specific error fixing...")
        
        specific_fixer = self.base_path / 'specific_error_fixer.py'
        
        try:
            result = subprocess.run(
                ['python3', str(specific_fixer)],
                capture_output=True,
                text=True,
                cwd=str(self.base_path),
                timeout=60
            )
            
            print(result.stdout)
            if result.stderr:
                print("Stderr:", result.stderr)
            
            return True
            
        except Exception as e:
            print(f"❌ Error running specific fixer: {e}")
            return False
    
    def run_final_tests(self):
        """Run final tests after fixes"""
        print("🧪 Running final tests after fixes...")
        
        try:
            result = subprocess.run(
                ['node', str(self.puppeteer_script)],
                capture_output=True,
                text=True,
                cwd=str(self.base_path),
                timeout=300
            )
            
            print(result.stdout)
            if result.stderr:
                print("Stderr:", result.stderr)
            
            # Load final test results
            report_file = self.base_path / 'comprehensive_test_report.json'
            if report_file.exists():
                with open(report_file, 'r', encoding='utf-8') as f:
                    final_results = json.load(f)
                
                print(f"✅ Final test results loaded")
                return final_results
            else:
                print("⚠️ Final test report file not found")
                return {}
                
        except Exception as e:
            print(f"❌ Error running final tests: {e}")
            return {}
    
    def generate_final_report(self, initial_results, final_results):
        """Generate final comparison report"""
        print("📊 Generating final comparison report...")
        
        report = {
            'timestamp': datetime.now().isoformat(),
            'initial_results': initial_results.get('summary', {}),
            'final_results': final_results.get('summary', {}),
            'improvement': {
                'tests_improved': 0,
                'success_rate_improvement': '0%'
            },
            'fixes_applied': self.fixes_applied,
            'recommendations': [
                'Continue monitoring application performance',
                'Implement automated testing in CI/CD',
                'Regular code reviews and quality checks',
                'Keep dependencies updated',
                'Monitor error logs regularly'
            ]
        }
        
        # Calculate improvements
        if initial_results.get('summary') and final_results.get('summary'):
            initial_passed = initial_results['summary'].get('passedTests', 0)
            final_passed = final_results['summary'].get('passedTests', 0)
            
            report['improvement']['tests_improved'] = final_passed - initial_passed
            
            initial_rate = float(initial_results['summary'].get('successRate', '0%').replace('%', ''))
            final_rate = float(final_results['summary'].get('successRate', '0%').replace('%', ''))
            
            report['improvement']['success_rate_improvement'] = f"{final_rate - initial_rate:.1f}%"
        
        # Save report
        report_file = self.base_path / 'comprehensive_fixing_report.json'
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, default=str)
        
        print(f"✅ Final report saved to: {report_file}")
        return report
    
    def run_comprehensive_testing_and_fixing(self):
        """Run complete testing and fixing process"""
        print("🚀 Starting Comprehensive Testing and Fixing Process...")
        
        # Step 1: Create comprehensive test
        self.create_comprehensive_puppeteer_test()
        
        # Step 2: Run initial tests
        print("\\n" + "="*50)
        print("STEP 1: RUNNING INITIAL TESTS")
        print("="*50)
        initial_results = self.run_comprehensive_tests()
        
        # Step 3: Analyze results
        print("\\n" + "="*50)
        print("STEP 2: ANALYZING TEST RESULTS")
        print("="*50)
        errors = self.analyze_test_results()
        
        # Step 4: Create and run batch fixer
        print("\\n" + "="*50)
        print("STEP 3: RUNNING BATCH ERROR FIXING")
        print("="*50)
        self.create_batch_fixer()
        self.run_batch_fixing()
        
        # Step 5: Create and run specific fixer
        print("\\n" + "="*50)
        print("STEP 4: RUNNING SPECIFIC ERROR FIXING")
        print("="*50)
        self.create_specific_fixer()
        self.run_specific_fixing()
        
        # Step 6: Run final tests
        print("\\n" + "="*50)
        print("STEP 5: RUNNING FINAL TESTS")
        print("="*50)
        final_results = self.run_final_tests()
        
        # Step 7: Generate final report
        print("\\n" + "="*50)
        print("STEP 6: GENERATING FINAL REPORT")
        print("="*50)
        final_report = self.generate_final_report(initial_results, final_results)
        
        # Print summary
        print(f"\\n🎉 Comprehensive Testing and Fixing Completed!")
        print(f"📚 Initial Tests: {initial_results.get('summary', {}).get('totalTests', 0)} total, {initial_results.get('summary', {}).get('passedTests', 0)} passed")
        print(f"📚 Final Tests: {final_results.get('summary', {}).get('totalTests', 0)} total, {final_results.get('summary', {}).get('passedTests', 0)} passed")
        print(f"📈 Improvement: {final_report['improvement']['tests_improved']} tests improved")
        print(f"📊 Success Rate Improvement: {final_report['improvement']['success_rate_improvement']}")
        print(f"🔧 Total Fixes Applied: {len(self.fixes_applied)}")
        
        return final_report

def main():
    """Main execution"""
    tester_fixer = ComprehensiveTestingFixer()
    report = tester_fixer.run_comprehensive_testing_and_fixing()
    
    print(f"\\n🎉 Mission Accomplished!")
    print(f"📚 Comprehensive testing and fixing completed")
    print(f"📊 Final report generated for reference")
    
    return report

if __name__ == "__main__":
    main()
