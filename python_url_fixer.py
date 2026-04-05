#!/usr/bin/env python3
"""
Python URL Fixer for SPRIN Application
Fix URL routing and base path issues
"""

import os
import re
import json
import subprocess
from pathlib import Path

class SPRINURLFixer:
    def __init__(self, base_path: str = "/opt/lampp/htdocs/sprint"):
        self.base_path = Path(base_path)
        self.fixes_applied = []
        
    def fix_config_base_url(self):
        """Fix base URL configuration"""
        config_file = self.base_path / 'core' / 'config.php'
        
        try:
            with open(config_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            original_content = content
            
            # Ensure BASE_URL is correct
            if 'define(\'BASE_URL\'' in content:
                content = re.sub(
                    r'define\s*\(\s*[\'"]BASE_URL[\'"]\s*,\s*[\'"][^\'\"]*[\'"]\s*\)',
                    "define('BASE_URL', 'http://localhost/sprint')",
                    content
                )
            
            # Add URL helper function if missing
            if 'function url(' not in content:
                url_function = '''
// URL helper function for consistent URL generation
function url($path) {
    return BASE_URL . '/' . ltrim($path, '/');
}
'''
                
                # Add before closing PHP tag
                if content.endswith('?>'):
                    content = content[:-2] + url_function + '?>'
                else:
                    content += url_function
            
            # Write back if changed
            if content != original_content:
                with open(config_file, 'w', encoding='utf-8') as f:
                    f.write(content)
                
                self.fixes_applied.append({
                    'type': 'config_base_url',
                    'file': str(config_file),
                    'fix': 'Ensured BASE_URL and url() function are correct'
                })
                
                print(f"✅ Fixed base URL configuration in {config_file}")
                return True
            else:
                print("ℹ️ Base URL configuration already correct")
                return True
        
        except Exception as e:
            print(f"Error fixing base URL config: {e}")
            return False

    def fix_login_redirect(self):
        """Fix login redirect URLs"""
        login_file = self.base_path / 'login.php'
        
        try:
            with open(login_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            original_content = content
            
            # Fix redirect URLs in login
            content = re.sub(
                r'header\s*\(\s*[\'"]Location:\s*[^\'\"]*[\'"]\s*\)',
                "header('Location: ' . url('pages/main.php'))",
                content
            )
            
            # Write back if changed
            if content != original_content:
                with open(login_file, 'w', encoding='utf-8') as f:
                    f.write(content)
                
                self.fixes_applied.append({
                    'type': 'login_redirect',
                    'file': str(login_file),
                    'fix': 'Fixed redirect URLs to use url() helper'
                })
                
                print(f"✅ Fixed login redirect URLs in {login_file}")
                return True
            else:
                print("ℹ️ Login redirect URLs already correct")
                return True
        
        except Exception as e:
            print(f"Error fixing login redirect: {e}")
            return False

    def fix_auth_helper_redirects(self):
        """Fix redirect URLs in auth_helper.php"""
        auth_file = self.base_path / 'core' / 'auth_helper.php'
        
        try:
            with open(auth_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            original_content = content
            
            # Fix redirect URLs
            content = re.sub(
                r'header\s*\(\s*[\'"]Location:\s*[^\'\"]*[\'"]\s*\)',
                "header('Location: ' . url('login.php'))",
                content
            )
            
            # Write back if changed
            if content != original_content:
                with open(auth_file, 'w', encoding='utf-8') as f:
                    f.write(content)
                
                self.fixes_applied.append({
                    'type': 'auth_helper_redirects',
                    'file': str(auth_file),
                    'fix': 'Fixed redirect URLs in auth_helper'
                })
                
                print(f"✅ Fixed auth_helper redirect URLs in {auth_file}")
                return True
            else:
                print("ℹ️ Auth_helper redirect URLs already correct")
                return True
        
        except Exception as e:
            print(f"Error fixing auth_helper redirects: {e}")
            return False

    def fix_page_redirects(self):
        """Fix redirect URLs in all page files"""
        page_files = [
            'pages/main.php',
            'pages/personil.php', 
            'pages/bagian.php',
            'pages/unsur.php',
            'pages/calendar_dashboard.php'
        ]
        
        files_fixed = 0
        
        for page_file in page_files:
            file_path = self.base_path / page_file
            
            if not file_path.exists():
                print(f"⚠️ File not found: {file_path}")
                continue
            
            try:
                with open(file_path, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                original_content = content
                
                # Fix redirect URLs
                content = re.sub(
                    r'header\s*\(\s*[\'"]Location:\s*[^\'\"]*[\'"]\s*\)',
                    "header('Location: ' . url('login.php'))",
                    content
                )
                
                # Write back if changed
                if content != original_content:
                    with open(file_path, 'w', encoding='utf-8') as f:
                        f.write(content)
                    
                    files_fixed += 1
                    print(f"✅ Fixed redirect URLs in {page_file}")
                
            except Exception as e:
                print(f"Error fixing {page_file}: {e}")
        
        if files_fixed > 0:
            self.fixes_applied.append({
                'type': 'page_redirects',
                'files_fixed': files_fixed,
                'fix': 'Fixed redirect URLs in page files'
            })
        
        return files_fixed > 0

    def create_main_redirect_file(self):
        """Create main.php file in root for direct access"""
        main_file = self.base_path / 'main.php'
        
        try:
            main_content = '''<?php
/**
 * Main Application Entry Point
 * Redirect to the actual main page
 */

// Include configuration
require_once __DIR__ . '/core/config.php';

// Redirect to main application page
header('Location: ' . url('pages/main.php'));
exit();

?>'''
            
            with open(main_file, 'w', encoding='utf-8') as f:
                f.write(main_content)
            
            self.fixes_applied.append({
                'type': 'main_redirect_file',
                'file': str(main_file),
                'fix': 'Created main.php redirect file for direct access'
            })
            
            print(f"✅ Created main.php redirect file")
            return True
            
        except Exception as e:
            print(f"Error creating main.php file: {e}")
            return False

    def update_puppeteer_test_urls(self):
        """Update Puppeteer test URLs to use correct paths"""
        test_file = self.base_path / 'test_comprehensive_puppeteer.js'
        
        try:
            with open(test_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            original_content = content
            
            # Update baseURL to ensure correct path
            content = re.sub(
                r'this\.baseURL\s*=\s*[\'"][^\'\"]*[\'"]',
                "this.baseURL = 'http://localhost/sprint'",
                content
            )
            
            # Write back if changed
            if content != original_content:
                with open(test_file, 'w', encoding='utf-8') as f:
                    f.write(content)
                
                self.fixes_applied.append({
                    'type': 'puppeteer_test_urls',
                    'file': str(test_file),
                    'fix': 'Updated Puppeteer test URLs'
                })
                
                print(f"✅ Updated Puppeteer test URLs in {test_file}")
                return True
            else:
                print("ℹ️ Puppeteer test URLs already correct")
                return True
        
        except Exception as e:
            print(f"Error updating Puppeteer test URLs: {e}")
            return False

    def run_url_fix(self):
        """Run comprehensive URL fixing"""
        print("🔧 Starting URL Fix Process...")
        
        fixes = [
            self.fix_config_base_url(),
            self.fix_login_redirect(),
            self.fix_auth_helper_redirects(),
            self.fix_page_redirects(),
            self.create_main_redirect_file(),
            self.update_puppeteer_test_urls()
        ]
        
        successful_fixes = sum(1 for fix in fixes if fix)
        
        # Generate report
        report = {
            'timestamp': subprocess.check_output(['date'], text=True).strip(),
            'url_fixes_resolved': successful_fixes,
            'total_url_fixes': len(fixes),
            'fixes_applied': self.fixes_applied,
            'success_rate': f"{(successful_fixes/len(fixes)*100):.1f}%"
        }
        
        report_file = self.base_path / 'python_url_fix_report.json'
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, default=str)
        
        print(f"\n📊 URL Fix Summary:")
        print(f"Fixes Applied: {successful_fixes}/{len(fixes)}")
        print(f"Success Rate: {report['success_rate']}")
        print(f"Report saved to: {report_file}")
        
        return successful_fixes

def main():
    """Main execution"""
    fixer = SPRINURLFixer()
    success = fixer.run_url_fix()
    
    if success > 0:
        print(f"\n🎉 Successfully applied {success} URL fixes!")
    else:
        print("\n❌ No URL fixes were applied")
    
    return success > 0

if __name__ == "__main__":
    main()
