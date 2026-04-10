#!/usr/bin/env python3
"""
SPRIN Test Runner
Runs all test suites: API tests and Browser automation tests
"""

import sys
import time
import subprocess
from pathlib import Path

def run_api_tests():
    """Run API test suite"""
    print("\n" + "=" * 70)
    print("RUNNING API TESTS")
    print("=" * 70)
    
    result = subprocess.run(
        [sys.executable, 'tests/automated_test_suite.py'],
        cwd='/opt/lampp/htdocs/sprin',
        capture_output=False,
        text=True
    )
    
    return result.returncode == 0

def run_browser_tests():
    """Run browser automation tests"""
    print("\n" + "=" * 70)
    print("RUNNING BROWSER AUTOMATION TESTS")
    print("=" * 70)
    print("Note: This will open a browser window. Close it manually after tests.")
    
    result = subprocess.run(
        [sys.executable, 'tests/browser_automation_test.py'],
        cwd='/opt/lampp/htdocs/sprin',
        capture_output=False,
        text=True
    )
    
    return result.returncode == 0

def check_requirements():
    """Check if all requirements are installed"""
    print("Checking requirements...")
    
    # Check requests
    try:
        import requests
        print("  ✓ requests installed")
    except ImportError:
        print("  ✗ requests not installed. Install with: pip install requests")
        return False
    
    # Check playwright (optional)
    try:
        import playwright
        print("  ✓ playwright installed")
    except ImportError:
        print("  ⚠ playwright not installed (browser tests will be skipped)")
        print("    Install with: pip install playwright && playwright install chromium")
    
    return True

def main():
    """Main test runner"""
    print("=" * 70)
    print("SPRIN COMPLETE TEST SUITE")
    print("=" * 70)
    print(f"Started: {time.strftime('%Y-%m-%d %H:%M:%S')}")
    
    # Check requirements
    if not check_requirements():
        print("\n❌ Requirements not met. Please install missing packages.")
        sys.exit(1)
    
    # Run API tests
    api_success = run_api_tests()
    
    # Ask for browser tests
    print("\n" + "=" * 70)
    response = input("Run browser automation tests? (requires Playwright) [y/N]: ")
    
    browser_success = True
    if response.lower() == 'y':
        try:
            import playwright
            browser_success = run_browser_tests()
        except ImportError:
            print("⚠️  Playwright not installed. Skipping browser tests.")
            browser_success = True  # Don't fail if optional dependency missing
    
    # Print summary
    print("\n" + "=" * 70)
    print("TEST SUITE SUMMARY")
    print("=" * 70)
    print(f"API Tests: {'✅ PASSED' if api_success else '❌ FAILED'}")
    print(f"Browser Tests: {'✅ PASSED' if browser_success else '❌ FAILED'}")
    
    if api_success and browser_success:
        print("\n✅ All tests passed!")
        sys.exit(0)
    else:
        print("\n❌ Some tests failed!")
        sys.exit(1)

if __name__ == '__main__':
    main()
