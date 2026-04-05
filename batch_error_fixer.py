#!/usr/bin/env python3
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
                    (r'(\$[^\n\r]+)\n(?=[^\n\r;])', r'\1;\n'),
                    # Fix undefined variable notices
                    (r'echo \$([^\s;]+)', r'echo $\1 ?? '''),
                    # Fix array access
                    (r'\$_GET\['([^']+)'\]', r'$_GET['\1'] ?? '''),
                    (r'\$_POST\['([^']+)'\]', r'$_POST['\1'] ?? '''),
                    # Fix function calls
                    (r'header\s*\(\s*['"]Location:([^'"]+)['"]\s*\)', r'header("Location:\1");'),
                    # Fix session start
                    (r'session_start\s*\(\s*\)', r'session_start();'),
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
                    (r'console\.log\s*\(\s*([^\)]+)\s*\)', r'console.log(\1);'),
                    # Fix function declarations
                    (r'function\s+(\w+)\s*\(', r'function \1('),
                    # Fix event listeners
                    (r'addEventListener\s*\(\s*['"]([^'"]+)['"]\s*,', r'addEventListener('\1', '),
                    # Fix AJAX calls
                    (r'\$\.ajax\s*\(', r'$.ajax('),
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
                    (r'([^{]\s*[^;{}\n]+)\s*\n', r'\1;\n'),
                    # Fix color formats
                    (r'#([0-9a-fA-F]{3})\b', r'#\1'),
                    # Fix units
                    (r'(margin|padding|width|height):\s*([0-9]+)\s*(?=;|})', r'\1: \2px;'),
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
                    (r'<\?php', r'<?php\nheader("Content-Type: application/json");'),
                    # Fix JSON output
                    (r'echo\s+([^\n]+)', r'echo json_encode(\1);'),
                    # Add error handling
                    (r'try\s*\{', r'try {'),
                    (r'catch\s*\(', r'catch (Exception $e) {\n    echo json_encode(['error' => $e->getMessage()]);\n}'),
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
    
    print(f"\n🎉 Batch fixing completed!")
    print(f"📚 Total fixes applied: {len(fixes)}")
