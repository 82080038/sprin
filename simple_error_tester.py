#!/usr/bin/env python3
"""
Simple Error Tester for SPRIN Application
Tests PHP files for syntax errors and common issues
"""

import os
import re
import subprocess
import json
from pathlib import Path
from datetime import datetime

class SimpleErrorTester:
    def __init__(self, base_path: str = "/opt/lampp/htdocs/sprint"):
        self.base_path = Path(base_path)
        self.errors_found = []
        self.fixes_applied = []
        
    def test_php_syntax(self):
        """Test PHP syntax for all files"""
        print("🔍 Testing PHP syntax...")
        
        php_files = list(self.base_path.rglob("*.php"))
        syntax_errors = []
        
        for php_file in php_files:
            try:
                # Test PHP syntax
                result = subprocess.run(
                    ['php', '-l', str(php_file)],
                    capture_output=True,
                    text=True,
                    timeout=10
                )
                
                if result.returncode != 0:
                    syntax_errors.append({
                        'file': str(php_file),
                        'type': 'syntax_error',
                        'message': result.stderr.strip(),
                        'return_code': result.returncode
                    })
                    print(f"❌ Syntax error in {php_file.relative_to(self.base_path)}")
                else:
                    print(f"✅ {php_file.relative_to(self.base_path)} - OK")
                    
            except Exception as e:
                syntax_errors.append({
                    'file': str(php_file),
                    'type': 'test_error',
                    'message': str(e)
                })
                print(f"⚠️ Error testing {php_file.relative_to(self.base_path)}: {e}")
        
        self.errors_found.extend(syntax_errors)
        return syntax_errors
    
    def test_api_endpoints(self):
        """Test API endpoints"""
        print("🌐 Testing API endpoints...")
        
        api_endpoints = [
            'http://localhost/sprint/api/personil.php',
            'http://localhost/sprint/api/bagian.php', 
            'http://localhost/sprint/api/unsur.php',
            'http://localhost/sprint/api/health_check_new.php',
            'http://localhost/sprint/api/performance_metrics.php'
        ]
        
        api_errors = []
        
        for endpoint in api_endpoints:
            try:
                result = subprocess.run(
                    ['curl', '-s', '-o', '/dev/null', '-w', '%{http_code}', endpoint],
                    capture_output=True,
                    text=True,
                    timeout=10
                )
                
                status_code = result.stdout.strip()
                
                if status_code != '200':
                    api_errors.append({
                        'endpoint': endpoint,
                        'type': 'http_error',
                        'status_code': status_code,
                        'message': f"HTTP {status_code}"
                    })
                    print(f"❌ {endpoint} - HTTP {status_code}")
                else:
                    print(f"✅ {endpoint} - OK")
                    
            except Exception as e:
                api_errors.append({
                    'endpoint': endpoint,
                    'type': 'test_error',
                    'message': str(e)
                })
                print(f"⚠️ Error testing {endpoint}: {e}")
        
        self.errors_found.extend(api_errors)
        return api_errors
    
    def test_page_access(self):
        """Test main page access"""
        print("📄 Testing page access...")
        
        pages = [
            'http://localhost/sprint/',
            'http://localhost/sprint/login.php',
            'http://localhost/sprint/pages/main.php',
            'http://localhost/sprint/pages/personil.php'
        ]
        
        page_errors = []
        
        for page in pages:
            try:
                result = subprocess.run(
                    ['curl', '-s', '-o', '/dev/null', '-w', '%{http_code}', page],
                    capture_output=True,
                    text=True,
                    timeout=10
                )
                
                status_code = result.stdout.strip()
                
                if status_code.startswith('4') or status_code.startswith('5'):
                    page_errors.append({
                        'page': page,
                        'type': 'http_error',
                        'status_code': status_code,
                        'message': f"HTTP {status_code}"
                    })
                    print(f"❌ {page} - HTTP {status_code}")
                else:
                    print(f"✅ {page} - OK")
                    
            except Exception as e:
                page_errors.append({
                    'page': page,
                    'type': 'test_error',
                    'message': str(e)
                })
                print(f"⚠️ Error testing {page}: {e}")
        
        self.errors_found.extend(page_errors)
        return page_errors
    
    def scan_for_common_errors(self):
        """Scan for common errors in code"""
        print("🔍 Scanning for common errors...")
        
        common_errors = []
        
        # Scan PHP files for common issues
        php_files = list(self.base_path.rglob("*.php"))
        
        for php_file in php_files:
            try:
                with open(php_file, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                # Check for common issues
                issues = []
                
                # Check for undefined variables
                if re.search(r'echo\s+\$\w+\s*;', content):
                    issues.append({
                        'type': 'undefined_variable_echo',
                        'pattern': 'echo $variable without null check'
                    })
                
                # Check for missing semicolons
                lines = content.split('\n')
                for i, line in enumerate(lines, 1):
                    stripped = line.strip()
                    if (stripped and not stripped.startswith('//') and not stripped.startswith('/*') and 
                        not stripped.endswith(';') and not stripped.endswith('?>') and 
                        not stripped.endswith('{') and not stripped.endswith('}') and
                        not 'if' in stripped and not 'else' in stripped and not 'for' in stripped and
                        not 'while' in stripped and not 'function' in stripped and not 'class' in stripped):
                        
                        issues.append({
                            'type': 'missing_semicolon',
                            'line': i,
                            'code': stripped
                        })
                
                # Check for deprecated functions
                deprecated_functions = ['mysql_', 'ereg', 'split', 'each']
                for func in deprecated_functions:
                    if func in content:
                        issues.append({
                            'type': 'deprecated_function',
                            'function': func
                        })
                
                if issues:
                    common_errors.append({
                        'file': str(php_file),
                        'issues': issues
                    })
                    print(f"⚠️ Issues found in {php_file.relative_to(self.base_path)}")
                else:
                    print(f"✅ {php_file.relative_to(self.base_path)} - Clean")
                    
            except Exception as e:
                print(f"⚠️ Error scanning {php_file.relative_to(self.base_path)}: {e}")
        
        self.errors_found.extend(common_errors)
        return common_errors
    
    def fix_common_errors(self):
        """Fix common errors automatically"""
        print("🔧 Fixing common errors...")
        
        fixes_applied = []
        
        php_files = list(self.base_path.rglob("*.php"))
        
        for php_file in php_files:
            try:
                with open(php_file, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                original_content = content
                
                # Fix undefined variable echoes
                content = re.sub(
                    r'echo\s+(\$\w+)\s*;',
                    r'echo \1 ?? "";',
                    content
                )
                
                # Fix missing semicolons (simple cases)
                content = re.sub(
                    r'(\$\w+\s*=\s*[^;\n]+)\n',
                    r'\1;\n',
                    content
                )
                
                # Fix header calls
                content = re.sub(
                    r'header\s*\(\s*[\'"]Location:([^\'"]+)[\'"]\s*\)',
                    r'header("Location:\1");',
                    content
                )
                
                # Add session start if missing and session is used
                if '$_SESSION' in content and 'session_start()' not in content:
                    content = re.sub(
                        r'<\?php',
                        '<?php\nsession_start();',
                        content
                    )
                
                if content != original_content:
                    with open(php_file, 'w', encoding='utf-8') as f:
                        f.write(content)
                    
                    fixes_applied.append({
                        'file': str(php_file),
                        'changes': 'Applied common error fixes'
                    })
                    print(f"✅ Fixed {php_file.relative_to(self.base_path)}")
                    
            except Exception as e:
                print(f"⚠️ Error fixing {php_file.relative_to(self.base_path)}: {e}")
        
        self.fixes_applied = fixes_applied
        return fixes_applied
    
    def create_error_test_report(self):
        """Create error test report"""
        print("📊 Creating error test report...")
        
        report = {
            'timestamp': datetime.now().isoformat(),
            'summary': {
                'total_errors': len(self.errors_found),
                'total_fixes': len(self.fixes_applied),
                'error_types': {}
            },
            'errors_found': self.errors_found,
            'fixes_applied': self.fixes_applied,
            'recommendations': [
                'Review and fix syntax errors manually',
                'Test all API endpoints after fixes',
                'Verify page access works correctly',
                'Run comprehensive testing after fixes'
            ]
        }
        
        # Count error types
        for error in self.errors_found:
            error_type = error.get('type', 'unknown')
            if error_type not in report['summary']['error_types']:
                report['summary']['error_types'][error_type] = 0
            report['summary']['error_types'][error_type] += 1
        
        # Save report
        report_file = self.base_path / 'error_test_report.json'
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, default=str)
        
        print(f"✅ Error test report saved to: {report_file}")
        return report
    
    def run_comprehensive_error_testing(self):
        """Run comprehensive error testing and fixing"""
        print("🚀 Starting Comprehensive Error Testing and Fixing...")
        
        # Step 1: Test PHP syntax
        print("\n" + "="*50)
        print("STEP 1: TESTING PHP SYNTAX")
        print("="*50)
        syntax_errors = self.test_php_syntax()
        
        # Step 2: Test API endpoints
        print("\n" + "="*50)
        print("STEP 2: TESTING API ENDPOINTS")
        print("="*50)
        api_errors = self.test_api_endpoints()
        
        # Step 3: Test page access
        print("\n" + "="*50)
        print("STEP 3: TESTING PAGE ACCESS")
        print("="*50)
        page_errors = self.test_page_access()
        
        # Step 4: Scan for common errors
        print("\n" + "="*50)
        print("STEP 4: SCANNING FOR COMMON ERRORS")
        print("="*50)
        common_errors = self.scan_for_common_errors()
        
        # Step 5: Fix common errors
        print("\n" + "="*50)
        print("STEP 5: FIXING COMMON ERRORS")
        print("="*50)
        fixes = self.fix_common_errors()
        
        # Step 6: Create report
        print("\n" + "="*50)
        print("STEP 6: CREATING ERROR TEST REPORT")
        print("="*50)
        report = self.create_error_test_report()
        
        # Print summary
        print(f"\n🎉 Comprehensive Error Testing and Fixing Completed!")
        print(f"📚 Total Errors Found: {len(self.errors_found)}")
        print(f"🔧 Total Fixes Applied: {len(self.fixes_applied)}")
        print(f"📊 Error Types: {report['summary']['error_types']}")
        
        return report

def main():
    """Main execution"""
    tester = SimpleErrorTester()
    report = tester.run_comprehensive_error_testing()
    
    print(f"\n🎉 Mission Accomplished!")
    print(f"📚 Comprehensive error testing and fixing completed")
    print(f"📊 Report generated for reference")
    
    return report

if __name__ == "__main__":
    main()
