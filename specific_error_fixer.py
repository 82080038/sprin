#!/usr/bin/env python3
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
                        r'header\s*\(\s*['"]Location:([^'"]+)['"]\s*\)',
                        r'header("Location: \1");',
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
                        r'echo\s+\$([^\s;]+)',
                        r'echo $\1 ?? ''',
                        content
                    )
                    
                    # Fix session issues
                    if 'session_start()' not in content and 'SessionManager::start()' in content:
                        content = re.sub(
                            r'SessionManager::start\(\)',
                            'SessionManager::start();\nsession_start();',
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
                            r'<\?php',
                            '<?php\nheader("Content-Type: application/json");',
                            content
                        )
                    
                    # Fix JSON output
                    content = re.sub(
                        r'echo\s+([^\n]+;)',
                        r'echo json_encode(\1)',
                        content
                    )
                    
                    # Add error handling
                    if 'try {' not in content:
                        content = re.sub(
                            r'(\$pdo\s*=\s*new\s+PDO)',
                            'try {\n    \1',
                            content
                        )
                        content = re.sub(
                            r'(\}\s*$)',
                            '} catch (Exception $e) {\n    echo json_encode(['error' => $e->getMessage()]);\n}',
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
    
    print(f"\n🎉 Specific fixing completed!")
    print(f"📚 Total fixes applied: {len(fixes)}")
