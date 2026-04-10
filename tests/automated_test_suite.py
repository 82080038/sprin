#!/usr/bin/env python3
"""
SPRIN Automated Test Suite
Uses Playwright for browser automation and requests for API testing
"""

import json
import sys
import time
import requests
from pathlib import Path
from typing import Dict, List, Optional
from dataclasses import dataclass

# Test configuration
BASE_URL = "http://localhost/sprin"
API_URL = f"{BASE_URL}/api"
PAGE_URL = f"{BASE_URL}/pages"

@dataclass
class TestResult:
    name: str
    status: str  # 'PASS', 'FAIL', 'SKIP'
    message: str
    duration: float
    details: Optional[Dict] = None

class SPRINTestSuite:
    def __init__(self):
        self.results: List[TestResult] = []
        self.session = requests.Session()
        self.csrf_token: Optional[str] = None
        self.cookies: Dict = {}
        
    def run_all_tests(self):
        """Run complete test suite"""
        print("=" * 70)
        print("SPRIN AUTOMATED TEST SUITE")
        print("=" * 70)
        print(f"Base URL: {BASE_URL}")
        print(f"Time: {time.strftime('%Y-%m-%d %H:%M:%S')}")
        print("=" * 70)
        
        # API Tests
        self.test_api_health()
        self.test_csrf_token_generation()
        self.test_authentication()
        
        # CRUD Tests
        self.test_jabatan_crud()
        self.test_unsur_crud()
        self.test_bagian_crud()
        
        # Page Tests
        self.test_page_loads()
        self.test_javascript_execution()
        
        # Print results
        self.print_results()
        
        return self.get_summary()
    
    def test_api_health(self):
        """Test API endpoints are reachable"""
        print("\n[1] Testing API Health...")
        
        endpoints = [
            f"{API_URL}/jabatan_api.php?action=get_all_jabatan",
            f"{API_URL}/unsur_api.php?action=get_all_unsur",
            f"{API_URL}/bagian_api.php?action=get_all_bagian",
        ]
        
        for endpoint in endpoints:
            try:
                start = time.time()
                response = self.session.get(endpoint, timeout=10)
                duration = time.time() - start
                
                if response.status_code == 200:
                    try:
                        data = response.json()
                        if isinstance(data, dict) and 'success' in data:
                            self.add_result(
                                f"API Health: {endpoint.split('/')[-1]}",
                                "PASS",
                                f"Response time: {duration:.2f}s",
                                duration
                            )
                        else:
                            self.add_result(
                                f"API Health: {endpoint.split('/')[-1]}",
                                "FAIL",
                                f"Invalid JSON structure: {str(data)[:100]}",
                                duration
                            )
                    except json.JSONDecodeError:
                        self.add_result(
                            f"API Health: {endpoint.split('/')[-1]}",
                            "FAIL",
                            f"Invalid JSON response",
                            duration
                        )
                else:
                    self.add_result(
                        f"API Health: {endpoint.split('/')[-1]}",
                        "FAIL",
                        f"HTTP {response.status_code}",
                        duration
                    )
                    
            except Exception as e:
                self.add_result(
                    f"API Health: {endpoint.split('/')[-1]}",
                    "FAIL",
                    f"Exception: {str(e)}",
                    0
                )
    
    def test_csrf_token_generation(self):
        """Test CSRF token can be generated"""
        print("\n[2] Testing CSRF Token Generation...")
        
        try:
            start = time.time()
            response = self.session.post(
                f"{API_URL}/jabatan_api.php",
                data={'action': 'get_csrf_token'},
                timeout=10
            )
            duration = time.time() - start
            
            if response.status_code == 200:
                try:
                    data = response.json()
                    if data.get('success') and data.get('csrf_token'):
                        self.csrf_token = data['csrf_token']
                        self.add_result(
                            "CSRF Token Generation",
                            "PASS",
                            f"Token generated: {self.csrf_token[:20]}...",
                            duration
                        )
                    else:
                        self.add_result(
                            "CSRF Token Generation",
                            "FAIL",
                            f"No token in response: {data}",
                            duration
                        )
                except json.JSONDecodeError:
                    self.add_result(
                        "CSRF Token Generation",
                        "FAIL",
                        "Invalid JSON response",
                        duration
                    )
            else:
                self.add_result(
                    "CSRF Token Generation",
                    "FAIL",
                    f"HTTP {response.status_code}",
                    duration
                )
                
        except Exception as e:
            self.add_result(
                "CSRF Token Generation",
                "FAIL",
                f"Exception: {str(e)}",
                0
            )
    
    def test_authentication(self):
        """Test authentication requirements"""
        print("\n[3] Testing Authentication...")
        
        # Test protected page without auth
        try:
            start = time.time()
            response = self.session.get(
                f"{PAGE_URL}/jabatan.php",
                allow_redirects=False,
                timeout=10
            )
            duration = time.time() - start
            
            # Should redirect to login if not authenticated
            if response.status_code in [302, 301] and 'login.php' in response.headers.get('Location', ''):
                self.add_result(
                    "Auth: Redirect to login",
                    "PASS",
                    "Unauthenticated users redirected to login",
                    duration
                )
            elif response.status_code == 200:
                self.add_result(
                    "Auth: Redirect to login",
                    "FAIL",
                    "Protected page accessible without auth",
                    duration
                )
            else:
                self.add_result(
                    "Auth: Redirect to login",
                    "SKIP",
                    f"Unexpected status: {response.status_code}",
                    duration
                )
                
        except Exception as e:
            self.add_result(
                "Auth: Redirect to login",
                "FAIL",
                f"Exception: {str(e)}",
                0
            )
    
    def test_jabatan_crud(self):
        """Test Jabatan CRUD operations"""
        print("\n[4] Testing Jabatan CRUD...")
        
        if not self.csrf_token:
            self.add_result(
                "Jabatan CRUD",
                "SKIP",
                "No CSRF token available",
                0
            )
            return
        
        # Create test jabatan
        test_data = {
            'action': 'create_jabatan',
            'nama_jabatan': f'TEST_JABATAN_{int(time.time())}',
            'id_unsur': '1',
            'csrf_token': self.csrf_token
        }
        
        try:
            start = time.time()
            response = self.session.post(
                f"{API_URL}/jabatan_api.php",
                data=test_data,
                headers={'X-CSRF-TOKEN': self.csrf_token},
                timeout=10
            )
            duration = time.time() - start
            
            if response.status_code == 200:
                try:
                    data = response.json()
                    if data.get('success'):
                        self.add_result(
                            "Jabatan: Create",
                            "PASS",
                            f"Created with ID: {data.get('id')}",
                            duration,
                            {'response': data}
                        )
                        
                        # Store ID for cleanup
                        created_id = data.get('id')
                        
                        # Test delete
                        self._test_jabatan_delete(created_id)
                    else:
                        self.add_result(
                            "Jabatan: Create",
                            "FAIL",
                            f"API error: {data.get('message')}",
                            duration,
                            {'response': data}
                        )
                except json.JSONDecodeError:
                    self.add_result(
                        "Jabatan: Create",
                        "FAIL",
                        "Invalid JSON response",
                        duration
                    )
            else:
                self.add_result(
                    "Jabatan: Create",
                    "FAIL",
                    f"HTTP {response.status_code}",
                    duration
                )
                
        except Exception as e:
            self.add_result(
                "Jabatan: Create",
                "FAIL",
                f"Exception: {str(e)}",
                0
            )
    
    def _test_jabatan_delete(self, jabatan_id):
        """Helper to test jabatan delete"""
        delete_data = {
            'action': 'delete_jabatan',
            'id': str(jabatan_id),
            'csrf_token': self.csrf_token
        }
        
        try:
            start = time.time()
            response = self.session.post(
                f"{API_URL}/jabatan_api.php",
                data=delete_data,
                headers={'X-CSRF-TOKEN': self.csrf_token},
                timeout=10
            )
            duration = time.time() - start
            
            if response.status_code == 200:
                try:
                    data = response.json()
                    if data.get('success'):
                        self.add_result(
                            "Jabatan: Delete",
                            "PASS",
                            f"Deleted ID: {jabatan_id}",
                            duration
                        )
                    else:
                        self.add_result(
                            "Jabatan: Delete",
                            "FAIL",
                            f"API error: {data.get('message')}",
                            duration
                        )
                except json.JSONDecodeError:
                    self.add_result(
                        "Jabatan: Delete",
                        "FAIL",
                        "Invalid JSON response",
                        duration
                    )
            else:
                self.add_result(
                    "Jabatan: Delete",
                    "FAIL",
                    f"HTTP {response.status_code}",
                    duration
                )
                
        except Exception as e:
            self.add_result(
                "Jabatan: Delete",
                "FAIL",
                f"Exception: {str(e)}",
                0
            )
    
    def test_unsur_crud(self):
        """Test Unsur CRUD"""
        print("\n[5] Testing Unsur CRUD...")
        
        # Test get all
        try:
            start = time.time()
            response = self.session.get(
                f"{API_URL}/unsur_api.php?action=get_all_unsur",
                timeout=10
            )
            duration = time.time() - start
            
            if response.status_code == 200:
                try:
                    data = response.json()
                    if data.get('success') and isinstance(data.get('data'), list):
                        count = len(data['data'])
                        self.add_result(
                            "Unsur: Get All",
                            "PASS",
                            f"Retrieved {count} unsur records",
                            duration
                        )
                    else:
                        self.add_result(
                            "Unsur: Get All",
                            "FAIL",
                            f"Invalid response structure",
                            duration
                        )
                except json.JSONDecodeError:
                    self.add_result(
                        "Unsur: Get All",
                        "FAIL",
                        "Invalid JSON response",
                        duration
                    )
            else:
                self.add_result(
                    "Unsur: Get All",
                    "FAIL",
                    f"HTTP {response.status_code}",
                    duration
                )
                
        except Exception as e:
            self.add_result(
                "Unsur: Get All",
                "FAIL",
                f"Exception: {str(e)}",
                0
            )
    
    def test_bagian_crud(self):
        """Test Bagian CRUD"""
        print("\n[6] Testing Bagian CRUD...")
        
        # Similar to unsur test
        try:
            start = time.time()
            response = self.session.get(
                f"{API_URL}/bagian_api.php?action=get_all_bagian",
                timeout=10
            )
            duration = time.time() - start
            
            if response.status_code == 200:
                try:
                    data = response.json()
                    if data.get('success') and isinstance(data.get('data'), list):
                        count = len(data['data'])
                        self.add_result(
                            "Bagian: Get All",
                            "PASS",
                            f"Retrieved {count} bagian records",
                            duration
                        )
                    else:
                        self.add_result(
                            "Bagian: Get All",
                            "FAIL",
                            f"Invalid response structure",
                            duration
                        )
                except json.JSONDecodeError:
                    self.add_result(
                        "Bagian: Get All",
                        "FAIL",
                        "Invalid JSON response",
                        duration
                    )
            else:
                self.add_result(
                    "Bagian: Get All",
                    "FAIL",
                    f"HTTP {response.status_code}",
                    duration
                )
                
        except Exception as e:
            self.add_result(
                "Bagian: Get All",
                "FAIL",
                f"Exception: {str(e)}",
                0
            )
    
    def test_page_loads(self):
        """Test page load performance"""
        print("\n[7] Testing Page Load Performance...")
        
        pages = [
            'login.php',
            'index.php',
        ]
        
        for page in pages:
            try:
                start = time.time()
                response = self.session.get(
                    f"{BASE_URL}/{page}",
                    timeout=10
                )
                duration = time.time() - start
                
                if response.status_code == 200:
                    self.add_result(
                        f"Page Load: {page}",
                        "PASS",
                        f"Load time: {duration:.2f}s, Size: {len(response.text)} bytes",
                        duration
                    )
                else:
                    self.add_result(
                        f"Page Load: {page}",
                        "FAIL",
                        f"HTTP {response.status_code}",
                        duration
                    )
                    
            except Exception as e:
                self.add_result(
                    f"Page Load: {page}",
                    "FAIL",
                    f"Exception: {str(e)}",
                    0
                )
    
    def test_javascript_execution(self):
        """Test if JS files are accessible"""
        print("\n[8] Testing JavaScript Files...")
        
        js_files = [
            'public/assets/js/api-client.js',
            'public/assets/js/sprin-core.js',
            'public/assets/js/realtime-client.js',
        ]
        
        for js_file in js_files:
            try:
                start = time.time()
                response = self.session.get(
                    f"{BASE_URL}/{js_file}",
                    timeout=10
                )
                duration = time.time() - start
                
                if response.status_code == 200:
                    self.add_result(
                        f"JS File: {js_file.split('/')[-1]}",
                        "PASS",
                        f"Size: {len(response.text)} bytes",
                        duration
                    )
                else:
                    self.add_result(
                        f"JS File: {js_file.split('/')[-1]}",
                        "WARN",
                        f"HTTP {response.status_code} (file may not exist)",
                        duration
                    )
                    
            except Exception as e:
                self.add_result(
                    f"JS File: {js_file.split('/')[-1]}",
                    "WARN",
                    f"Exception: {str(e)}",
                    0
                )
    
    def add_result(self, name: str, status: str, message: str, duration: float, details: Optional[Dict] = None):
        """Add test result"""
        result = TestResult(
            name=name,
            status=status,
            message=message,
            duration=duration,
            details=details
        )
        self.results.append(result)
    
    def print_results(self):
        """Print test results"""
        print("\n" + "=" * 70)
        print("TEST RESULTS")
        print("=" * 70)
        
        passed = len([r for r in self.results if r.status == "PASS"])
        failed = len([r for r in self.results if r.status == "FAIL"])
        skipped = len([r for r in self.results if r.status == "SKIP"])
        
        print(f"\nTotal Tests: {len(self.results)}")
        print(f"  ✅ PASS: {passed}")
        print(f"  ❌ FAIL: {failed}")
        print(f"  ⏭️  SKIP: {skipped}")
        
        if failed > 0:
            print("\nFAILED TESTS:")
            for result in self.results:
                if result.status == "FAIL":
                    print(f"  ❌ {result.name}")
                    print(f"     {result.message}")
                    if result.details:
                        print(f"     Details: {json.dumps(result.details, indent=2)[:200]}")
        
        print("\n" + "=" * 70)
    
    def get_summary(self) -> Dict:
        """Get test summary"""
        passed = len([r for r in self.results if r.status == "PASS"])
        failed = len([r for r in self.results if r.status == "FAIL"])
        skipped = len([r for r in self.results if r.status == "SKIP"])
        
        return {
            'total': len(self.results),
            'passed': passed,
            'failed': failed,
            'skipped': skipped,
            'success_rate': (passed / len(self.results) * 100) if self.results else 0
        }


def main():
    """Main entry point"""
    suite = SPRINTestSuite()
    summary = suite.run_all_tests()
    
    # Export results to JSON
    results_file = Path('/opt/lampp/htdocs/sprin/test_results.json')
    results_data = {
        'timestamp': time.strftime('%Y-%m-%d %H:%M:%S'),
        'summary': summary,
        'results': [
            {
                'name': r.name,
                'status': r.status,
                'message': r.message,
                'duration': r.duration
            }
            for r in suite.results
        ]
    }
    
    results_file.write_text(json.dumps(results_data, indent=2))
    print(f"\n📄 Results exported to: {results_file}")
    
    # Exit with error code if tests failed
    if summary['failed'] > 0:
        print(f"\n⚠️  {summary['failed']} tests failed!")
        sys.exit(1)
    else:
        print("\n✅ All tests passed!")
        sys.exit(0)


if __name__ == '__main__':
    main()
