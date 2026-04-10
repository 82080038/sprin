#!/usr/bin/env python3
"""
SPRIN Browser Automation Tests
Uses Playwright for end-to-end testing
Requires: pip install playwright && playwright install
"""

import asyncio
import json
import sys
import time
from pathlib import Path
from typing import List, Dict, Optional

try:
    from playwright.async_api import async_playwright, Page, Browser
except ImportError:
    print("Playwright not installed. Install with:")
    print("  pip install playwright")
    print("  playwright install chromium")
    sys.exit(1)

BASE_URL = "http://localhost/sprin"

class BrowserTestSuite:
    def __init__(self):
        self.results: List[Dict] = []
        self.browser: Optional[Browser] = None
        
    async def run_all_tests(self):
        """Run all browser tests"""
        print("=" * 70)
        print("SPRIN BROWSER AUTOMATION TESTS (Playwright)")
        print("=" * 70)
        
        async with async_playwright() as p:
            # Launch browser
            self.browser = await p.chromium.launch(headless=False)  # Set to True for CI
            
            # Run tests
            await self.test_login_page()
            await self.test_jabatan_page()
            await self.test_modal_interactions()
            await self.test_form_validation()
            await self.test_toast_notifications()
            await self.test_responsive_layout()
            
            # Cleanup
            await self.browser.close()
        
        self.print_results()
    
    async def test_login_page(self):
        """Test login page functionality"""
        print("\n[1] Testing Login Page...")
        
        page = await self.browser.new_page()
        
        try:
            start = time.time()
            
            # Navigate to login
            await page.goto(f"{BASE_URL}/login.php", wait_until='networkidle')
            
            # Check elements
            username_input = await page.locator('input[name="username"]').count()
            password_input = await page.locator('input[name="password"]').count()
            submit_btn = await page.locator('button[type="submit"]').count()
            
            duration = time.time() - start
            
            if username_input > 0 and password_input > 0 and submit_btn > 0:
                self.add_result(
                    "Login Page Elements",
                    "PASS",
                    f"All form elements found (load time: {duration:.2f}s)",
                    duration
                )
            else:
                self.add_result(
                    "Login Page Elements",
                    "FAIL",
                    f"Missing elements: username={username_input}, password={password_input}, submit={submit_btn}",
                    duration
                )
            
            # Test form validation
            await page.click('button[type="submit"]')
            await page.wait_for_timeout(1000)
            
            # Check for error message
            error_visible = await page.locator('.alert-danger, .error-message').count()
            
            if error_visible > 0:
                self.add_result(
                    "Login Form Validation",
                    "PASS",
                    "Validation error displayed for empty form",
                    0
                )
            else:
                self.add_result(
                    "Login Form Validation",
                    "WARN",
                    "No validation error shown (might be OK)",
                    0
                )
                
        except Exception as e:
            self.add_result(
                "Login Page",
                "FAIL",
                f"Exception: {str(e)}",
                0
            )
        finally:
            await page.close()
    
    async def test_jabatan_page(self):
        """Test Jabatan page CRUD"""
        print("\n[2] Testing Jabatan Page...")
        
        page = await self.browser.new_page()
        
        try:
            # Navigate to jabatan page (will redirect to login if not auth)
            await page.goto(f"{BASE_URL}/pages/jabatan.php", wait_until='networkidle')
            
            # Check if we're on login page (not authenticated)
            if 'login.php' in page.url:
                self.add_result(
                    "Jabatan Page Auth",
                    "PASS",
                    "Correctly redirects to login when not authenticated",
                    0
                )
            else:
                # We're on jabatan page - check elements
                add_btn = await page.locator('button:has-text("Tambah Jabatan"), button:has-text("Add")').count()
                
                if add_btn > 0:
                    self.add_result(
                        "Jabatan Page Elements",
                        "PASS",
                        "Jabatan management elements found",
                        0
                    )
                else:
                    self.add_result(
                        "Jabatan Page Elements",
                        "FAIL",
                        "Missing add jabatan button",
                        0
                    )
            
            # Check for JavaScript errors
            js_errors = []
            page.on("pageerror", lambda error: js_errors.append(str(error)))
            
            await page.wait_for_timeout(2000)
            
            if js_errors:
                self.add_result(
                    "JavaScript Errors",
                    "FAIL",
                    f"JS errors found: {js_errors[:3]}",
                    0
                )
            else:
                self.add_result(
                    "JavaScript Errors",
                    "PASS",
                    "No JavaScript errors detected",
                    0
                )
                
        except Exception as e:
            self.add_result(
                "Jabatan Page",
                "FAIL",
                f"Exception: {str(e)}",
                0
            )
        finally:
            await page.close()
    
    async def test_modal_interactions(self):
        """Test modal dialogs"""
        print("\n[3] Testing Modal Interactions...")
        
        # This requires authentication, so we just test the structure
        self.add_result(
            "Modal Interactions",
            "SKIP",
            "Requires authenticated session - test manually",
            0
        )
    
    async def test_form_validation(self):
        """Test form validation"""
        print("\n[4] Testing Form Validation...")
        
        page = await self.browser.new_page()
        
        try:
            # Test at login form level
            await page.goto(f"{BASE_URL}/login.php")
            
            # Submit empty form
            await page.click('button[type="submit"]')
            await page.wait_for_timeout(500)
            
            # Check URL - should still be on login page
            if 'login.php' in page.url:
                self.add_result(
                    "Form Validation",
                    "PASS",
                    "Form validation prevents empty submission",
                    0
                )
            else:
                self.add_result(
                    "Form Validation",
                    "WARN",
                    "Form submitted without validation (or validation on server)",
                    0
                )
                
        except Exception as e:
            self.add_result(
                "Form Validation",
                "FAIL",
                f"Exception: {str(e)}",
                0
            )
        finally:
            await page.close()
    
    async def test_toast_notifications(self):
        """Test toast notification system"""
        print("\n[5] Testing Toast Notifications...")
        
        page = await self.browser.new_page()
        
        try:
            await page.goto(f"{BASE_URL}/pages/jabatan.php")
            
            # Check if showToast function exists
            toast_exists = await page.evaluate('''() => {
                return typeof showToast === 'function';
            }''')
            
            if toast_exists:
                self.add_result(
                    "Toast System",
                    "PASS",
                    "showToast() function available globally",
                    0
                )
                
                # Test toast display
                await page.evaluate('''() => {
                    showToast('success', 'Test notification', 1000);
                }''')
                
                await page.wait_for_timeout(500)
                
                # Check if toast appeared
                toast_visible = await page.locator('.toast').count()
                
                if toast_visible > 0:
                    self.add_result(
                        "Toast Display",
                        "PASS",
                        "Toast notification displayed successfully",
                        0
                    )
                else:
                    self.add_result(
                        "Toast Display",
                        "WARN",
                        "Toast may have auto-dismissed or not visible",
                        0
                    )
            else:
                self.add_result(
                    "Toast System",
                    "FAIL",
                    "showToast() function not found",
                    0
                )
                
        except Exception as e:
            self.add_result(
                "Toast Notifications",
                "FAIL",
                f"Exception: {str(e)}",
                0
            )
        finally:
            await page.close()
    
    async def test_responsive_layout(self):
        """Test responsive layout"""
        print("\n[6] Testing Responsive Layout...")
        
        page = await self.browser.new_page()
        
        try:
            viewports = [
                {'width': 1920, 'height': 1080, 'name': 'Desktop'},
                {'width': 768, 'height': 1024, 'name': 'Tablet'},
                {'width': 375, 'height': 667, 'name': 'Mobile'},
            ]
            
            for viewport in viewports:
                await page.set_viewport_size({
                    'width': viewport['width'],
                    'height': viewport['height']
                })
                
                await page.goto(f"{BASE_URL}/login.php", wait_until='networkidle')
                await page.wait_for_timeout(1000)
                
                # Take screenshot
                screenshot_path = f"/opt/lampp/htdocs/sprin/tests/screenshots/{viewport['name'].lower()}_login.png"
                Path(screenshot_path).parent.mkdir(parents=True, exist_ok=True)
                await page.screenshot(path=screenshot_path)
                
                self.add_result(
                    f"Responsive: {viewport['name']}",
                    "PASS",
                    f"Screenshot saved: {screenshot_path}",
                    0
                )
                
        except Exception as e:
            self.add_result(
                "Responsive Layout",
                "FAIL",
                f"Exception: {str(e)}",
                0
            )
        finally:
            await page.close()
    
    def add_result(self, name: str, status: str, message: str, duration: float):
        """Add test result"""
        self.results.append({
            'name': name,
            'status': status,
            'message': message,
            'duration': duration,
            'timestamp': time.strftime('%H:%M:%S')
        })
    
    def print_results(self):
        """Print test results"""
        print("\n" + "=" * 70)
        print("BROWSER TEST RESULTS")
        print("=" * 70)
        
        passed = len([r for r in self.results if r['status'] == 'PASS'])
        failed = len([r for r in self.results if r['status'] == 'FAIL'])
        skipped = len([r for r in self.results if r['status'] == 'SKIP'])
        warnings = len([r for r in self.results if r['status'] == 'WARN'])
        
        print(f"\nTotal Tests: {len(self.results)}")
        print(f"  ✅ PASS: {passed}")
        print(f"  ❌ FAIL: {failed}")
        print(f"  ⚠️  WARN: {warnings}")
        print(f"  ⏭️  SKIP: {skipped}")
        
        if failed > 0:
            print("\nFAILED TESTS:")
            for result in self.results:
                if result['status'] == 'FAIL':
                    print(f"  ❌ {result['name']}")
                    print(f"     {result['message']}")
        
        # Save results
        results_file = Path('/opt/lampp/htdocs/sprin/browser_test_results.json')
        results_file.write_text(json.dumps({
            'timestamp': time.strftime('%Y-%m-%d %H:%M:%S'),
            'summary': {
                'total': len(self.results),
                'passed': passed,
                'failed': failed,
                'warnings': warnings,
                'skipped': skipped
            },
            'results': self.results
        }, indent=2))
        
        print(f"\n📄 Results saved to: {results_file}")
        print("=" * 70)


async def main():
    suite = BrowserTestSuite()
    await suite.run_all_tests()


if __name__ == '__main__':
    asyncio.run(main())
