#!/usr/bin/env python3
"""
Batch Fix Script for SPRIN Inconsistencies
Fixes all identified issues automatically
"""

import os
import re
from pathlib import Path

class BatchFixer:
    def __init__(self, root_path):
        self.root = Path(root_path)
        self.fixed = []
        self.failed = []
    
    def fix_all(self):
        """Run all fixes"""
        print("=" * 70)
        print("BATCH FIXING SPRIN INCONSISTENCIES")
        print("=" * 70)
        
        self.fix_error_reporting()
        self.fix_csrf_patterns()
        self.fix_session_management()
        self.fix_api_responses()
        self.fix_javascript_patterns()
        
        return len(self.fixed)
    
    def fix_error_reporting(self):
        """Fix error reporting patterns"""
        print("\n[1] Fixing Error Reporting...")
        
        files_to_fix = [
            'api/test_api.php',
            'api/v1/index.php',
            'api/export_personil.php',
            'api/advanced_search.php',
            'api/search_personil.php',
            'api/pagination_personil.php',
            'api/jabatan_crud.php',
        ]
        
        for file_path in files_to_fix:
            full_path = self.root / file_path
            if full_path.exists():
                try:
                    content = full_path.read_text(encoding='utf-8')
                    
                    # Check if already has config.php
                    if 'config.php' in content:
                        continue
                    
                    # Add config.php requirement
                    new_content = re.sub(
                        r'(error_reporting\(E_ALL\);\s*\nini_set\([\'"]display_errors[\'"],\s*1\);)',
                        r"require_once __DIR__ . '/../core/config.php';\n\n// Error reporting controlled by config\nerror_reporting(E_ALL);\nini_set('display_errors', defined('DEBUG_MODE') && DEBUG_MODE ? 1 : 0);\nini_set('log_errors', 1);",
                        content
                    )
                    
                    if new_content != content:
                        full_path.write_text(new_content, encoding='utf-8')
                        self.fixed.append(file_path)
                        print(f"  ✓ Fixed {file_path}")
                    
                except Exception as e:
                    self.failed.append((file_path, str(e)))
                    print(f"  ✗ Failed {file_path}: {e}")
    
    def fix_csrf_patterns(self):
        """Fix CSRF patterns"""
        print("\n[2] Fixing CSRF Patterns...")
        
        # Fix unified-api.php - add session start before CSRF check
        unified_api = self.root / 'api/unified-api.php'
        if unified_api.exists():
            try:
                content = unified_api.read_text(encoding='utf-8')
                
                # Check if already has session start before CSRF
                if 'SessionManager::start()' in content.split('// CSRF')[0]:
                    print(f"  ✓ unified-api.php already correct")
                else:
                    # Add SessionManager start
                    if 'SessionManager::start()' not in content:
                        new_content = content.replace(
                            '// CSRF validation',
                            'SessionManager::start();\n\n// CSRF validation'
                        )
                        unified_api.write_text(new_content, encoding='utf-8')
                        self.fixed.append('api/unified-api.php')
                        print(f"  ✓ Fixed unified-api.php")
                    
            except Exception as e:
                self.failed.append(('api/unified-api.php', str(e)))
                print(f"  ✗ Failed unified-api.php: {e}")
    
    def fix_session_management(self):
        """Fix session management"""
        print("\n[3] Fixing Session Management...")
        
        files_with_direct_session = [
            'index.php',
            'login.php',
            'api/bagian_api.php',
            'api/personil_detail.php',
        ]
        
        for file_path in files_with_direct_session:
            full_path = self.root / file_path
            if full_path.exists():
                try:
                    content = full_path.read_text(encoding='utf-8')
                    
                    # Skip if already uses SessionManager
                    if 'SessionManager::' in content:
                        continue
                    
                    # Replace session_start() with SessionManager
                    if 'session_start()' in content:
                        new_content = content.replace(
                            'session_start()',
                            'SessionManager::start()'
                        )
                        
                        # Add SessionManager include if missing
                        if 'SessionManager.php' not in new_content:
                            new_content = new_content.replace(
                                '<?php',
                                "<?php\nrequire_once __DIR__ . '/core/SessionManager.php';"
                            )
                        
                        full_path.write_text(new_content, encoding='utf-8')
                        self.fixed.append(file_path)
                        print(f"  ✓ Fixed {file_path}")
                    
                except Exception as e:
                    self.failed.append((file_path, str(e)))
                    print(f"  ✗ Failed {file_path}: {e}")
    
    def fix_api_responses(self):
        """Fix API response formats"""
        print("\n[4] Fixing API Response Formats...")
        
        api_files = [
            'api/personil_api.php',
            'api/unsur_terminal.php',
            'api/export_personil.php',
        ]
        
        for file_path in api_files:
            full_path = self.root / file_path
            if full_path.exists():
                try:
                    content = full_path.read_text(encoding='utf-8')
                    
                    # Check if already has standardized response
                    if "'success' =>" in content:
                        continue
                    
                    # Wrap existing response in success format (if it's a data response)
                    # This is a complex fix that needs manual review
                    print(f"  ⚠️  {file_path} needs manual review for API response format")
                    
                except Exception as e:
                    self.failed.append((file_path, str(e)))
                    print(f"  ✗ Failed {file_path}: {e}")
    
    def fix_javascript_patterns(self):
        """Fix JavaScript patterns"""
        print("\n[5] Fixing JavaScript Patterns...")
        
        # Fix fetch without credentials
        files_with_fetch = [
            'pages/reporting.php',
            'pages/user_management.php',
            'pages/bagian.php',
        ]
        
        for file_path in files_with_fetch:
            full_path = self.root / file_path
            if full_path.exists():
                try:
                    content = full_path.read_text(encoding='utf-8')
                    
                    # Check if fetch has credentials
                    if 'fetch(' in content and 'credentials' not in content:
                        # Add credentials: 'same-origin' to fetch calls
                        # This is a complex regex replacement
                        new_content = re.sub(
                            r'fetch\([\'"]([^\'"]+)[\'"],\s*\{',
                            r"fetch('\1', {\n            credentials: 'same-origin',",
                            content
                        )
                        
                        if new_content != content:
                            full_path.write_text(new_content, encoding='utf-8')
                            self.fixed.append(file_path)
                            print(f"  ✓ Fixed fetch credentials in {file_path}")
                    
                except Exception as e:
                    self.failed.append((file_path, str(e)))
                    print(f"  ✗ Failed {file_path}: {e}")
    
    def print_summary(self):
        """Print summary"""
        print("\n" + "=" * 70)
        print("FIX SUMMARY")
        print("=" * 70)
        print(f"Fixed: {len(self.fixed)} files")
        print(f"Failed: {len(self.failed)} files")
        
        if self.fixed:
            print("\nFixed files:")
            for f in self.fixed:
                print(f"  ✓ {f}")
        
        if self.failed:
            print("\nFailed files:")
            for f, e in self.failed:
                print(f"  ✗ {f}: {e}")


def main():
    root_path = '/opt/lampp/htdocs/sprin'
    fixer = BatchFixer(root_path)
    
    fixed_count = fixer.fix_all()
    fixer.print_summary()
    
    print("\n" + "=" * 70)
    print("NEXT STEPS")
    print("=" * 70)
    print("1. Review fixed files for any issues")
    print("2. Test the application functionality")
    print("3. Run analyzer again to verify fixes")


if __name__ == '__main__':
    main()
