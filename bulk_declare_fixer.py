#!/usr/bin/env python3
"""
Bulk Declare Fixer for SPRIN Development
Fix all declare(strict_types=1) positioning issues
"""

import os
import re
from pathlib import Path

class BulkDeclareFixer:
    def __init__(self, base_path: str = "/opt/lampp/htdocs/sprint"):
        self.base_path = Path(base_path)
        self.files_fixed = []
        
    def fix_all_declare_statements(self):
        """Fix declare(strict_types=1) in all PHP files"""
        php_files = list(self.base_path.rglob("*.php"))
        files_fixed = 0
        
        for php_file in php_files:
            # Skip certain files
            if any(skip in str(php_file) for skip in ['vendor', 'node_modules', '.git', 'test_php_errors.php']):
                continue
                
            try:
                with open(php_file, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                original_content = content
                
                # Check if file has declare statement
                if 'declare(strict_types=1)' in content:
                    lines = content.split('\n')
                    
                    # Find first non-comment, non-empty line after <?php
                    php_line_idx = -1
                    declare_line_idx = -1
                    first_code_line_idx = -1
                    
                    for i, line in enumerate(lines):
                        stripped = line.strip()
                        
                        # Find PHP opening tag
                        if stripped == '<?php' and php_line_idx == -1:
                            php_line_idx = i
                            continue
                        
                        # Find declare statement
                        if 'declare(strict_types=1)' in line and declare_line_idx == -1:
                            declare_line_idx = i
                            continue
                        
                        # Find first actual code line (not comments or empty)
                        if (php_line_idx != -1 and 
                            first_code_line_idx == -1 and 
                            stripped and 
                            not stripped.startswith('//') and 
                            not stripped.startswith('/*') and 
                            not stripped.startswith('*') and
                            not stripped == '?>'):
                            first_code_line_idx = i
                            break
                    
                    # Fix positioning if declare is not right after <?php
                    if (php_line_idx != -1 and 
                        declare_line_idx != -1 and 
                        declare_line_idx != php_line_idx + 1):
                        
                        # Extract declare statement
                        declare_line = lines[declare_line_idx]
                        
                        # Remove declare from current position
                        lines.pop(declare_line_idx)
                        
                        # Adjust indices if declare was before the target position
                        if declare_line_idx < php_line_idx + 1:
                            php_line_idx -= 1
                        
                        # Insert declare right after <?php
                        lines.insert(php_line_idx + 1, declare_line)
                        
                        # Reconstruct content
                        content = '\n'.join(lines)
                        
                        # Write back
                        with open(php_file, 'w', encoding='utf-8') as f:
                            f.write(content)
                        
                        files_fixed += 1
                        self.files_fixed.append(str(php_file.relative_to(self.base_path)))
                        
                        if files_fixed <= 10:
                            print(f"✅ Fixed {php_file.relative_to(self.base_path)}")
                        elif files_fixed == 11:
                            print(f"✅ Fixed {len(php_files)-10} more files...")
                
            except Exception as e:
                print(f"Error fixing {php_file}: {e}")
        
        return files_fixed > 0

    def run_bulk_fix(self):
        """Run bulk declare fixing"""
        print("🔧 Starting Bulk Declare Fixing...")
        
        success = self.fix_all_declare_statements()
        
        if success:
            print(f"\n📊 Bulk Declare Fix Summary:")
            print(f"Files Fixed: {len(self.files_fixed)}")
            print("✅ All declare(strict_types=1) statements are now properly positioned")
        else:
            print("ℹ️ No declare fixes needed")
        
        return success

def main():
    """Main execution"""
    fixer = BulkDeclareFixer()
    success = fixer.run_bulk_fix()
    
    if success:
        print(f"\n🎉 Successfully fixed declare statements in {len(fixer.files_fixed)} files!")
    else:
        print("\nℹ️ No declare fixes were needed")
    
    return success

if __name__ == "__main__":
    main()
