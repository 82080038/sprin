#!/usr/bin/env python3
"""
PHP Strict Types Fixer for SPRIN Development
Fix declare(strict_types=1) positioning after development error reporting
"""

import os
import re
from pathlib import Path

class PHPStrictTypesFixer:
    def __init__(self, base_path: str = "/opt/lampp/htdocs/sprint"):
        self.base_path = Path(base_path)
        self.files_fixed = []
        
    def fix_strict_types_positioning(self):
        """Fix declare(strict_types=1) positioning in all PHP files"""
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
                
                # Check if file has both declare and development code
                if 'declare(strict_types=1)' in content and 'Development Error Reporting' in content:
                    # Extract the first few lines
                    lines = content.split('\n')
                    
                    # Find declare(strict_types=1) line
                    declare_line = -1
                    dev_reporting_line = -1
                    
                    for i, line in enumerate(lines):
                        if 'declare(strict_types=1)' in line:
                            declare_line = i
                        elif 'Development Error Reporting' in line:
                            dev_reporting_line = i
                            break
                    
                    # If declare comes after development code, fix it
                    if declare_line > dev_reporting_line and declare_line != -1:
                        # Remove declare from current position
                        declare_statement = lines[declare_line]
                        lines.pop(declare_line)
                        
                        # Insert declare right after <?php
                        php_tag_line = -1
                        for i, line in enumerate(lines):
                            if line.strip() == '<?php':
                                php_tag_line = i
                                break
                        
                        if php_tag_line != -1:
                            lines.insert(php_tag_line + 1, declare_statement)
                            
                            # Reconstruct content
                            content = '\n'.join(lines)
                            
                            # Write back
                            with open(php_file, 'w', encoding='utf-8') as f:
                                f.write(content)
                            
                            files_fixed += 1
                            self.files_fixed.append(str(php_file.relative_to(self.base_path)))
                            
                            if files_fixed <= 5:
                                print(f"✅ Fixed {php_file.relative_to(self.base_path)}")
                            elif files_fixed == 6:
                                print(f"✅ Fixed {len(php_files)-5} more files...")
                
            except Exception as e:
                print(f"Error fixing {php_file}: {e}")
        
        return files_fixed > 0

    def run_strict_types_fix(self):
        """Run strict types fixing"""
        print("🔧 Fixing declare(strict_types=1) positioning...")
        
        success = self.fix_strict_types_positioning()
        
        if success:
            print(f"\n📊 Strict Types Fix Summary:")
            print(f"Files Fixed: {len(self.files_fixed)}")
            print("✅ All declare(strict_types=1) statements are now properly positioned")
        else:
            print("ℹ️ No strict types fixes needed")
        
        return success

def main():
    """Main execution"""
    fixer = PHPStrictTypesFixer()
    success = fixer.run_strict_types_fix()
    
    if success:
        print(f"\n🎉 Successfully fixed strict types in {len(fixer.files_fixed)} files!")
    else:
        print("\nℹ️ No strict types fixes were needed")
    
    return success

if __name__ == "__main__":
    main()
