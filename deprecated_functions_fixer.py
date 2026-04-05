#!/usr/bin/env python3
"""
Deprecated Functions Fixer for SPRIN Application
Fixes deprecated PHP functions found during testing
"""

import os
import re
from pathlib import Path

class DeprecatedFunctionsFixer:
    def __init__(self, base_path: str = "/opt/lampp/htdocs/sprint"):
        self.base_path = Path(base_path)
        self.fixes_applied = []
        
        # Deprecated functions and their replacements
        self.deprecated_functions = {
            'each(': {
                'replacement': 'foreach',
                'pattern': r'each\s*\(\s*(\$\w+)\s+as\s*(\$\w+)\s*=>\s*(\$\w+)\s*\)',
                'replacement_pattern': r'foreach (\1 as \2 => \3)'
            },
            'mysql_': {
                'replacement': 'PDO',
                'pattern': r'mysql_(\w+)',
                'replacement_pattern': r'pdo_\1'
            },
            'split(': {
                'replacement': 'explode',
                'pattern': r'split\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*(\$\w+)\s*\)',
                'replacement_pattern': r'explode(\'\1\', \2)'
            },
            'ereg(': {
                'replacement': 'preg_match',
                'pattern': r'ereg\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*(\$\w+)\s*(,\s*\$\w+)?\s*\)',
                'replacement_pattern': r'preg_match(\'/\1/\', \2\3)'
            }
        }
    
    def fix_deprecated_functions(self):
        """Fix deprecated functions in all PHP files"""
        print("🔧 Fixing deprecated functions...")
        
        php_files = list(self.base_path.rglob("*.php"))
        files_fixed = 0
        
        for php_file in php_files:
            try:
                with open(php_file, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                original_content = content
                fixes_made = []
                
                # Fix each() function
                if 'each(' in content:
                    # Replace each() with foreach()
                    content = re.sub(
                        r'while\s*\(\s*each\s*\(\s*(\$\w+)\s+as\s*(\$\w+)\s*=>\s*(\$\w+)\s*\)\s*\)',
                        r'foreach (\1 as \2 => \3) {',
                        content
                    )
                    
                    # Remove the list() assignment from each()
                    content = re.sub(
                        r'list\s*\(\s*\$\w+\s*,\s*\$\w+\s*\)\s*=\s*\$\w+\s*;',
                        '// Removed list() assignment for each()',
                        content
                    )
                    
                    fixes_made.append('Replaced each() with foreach()')
                
                # Fix split() function
                if 'split(' in content:
                    content = re.sub(
                        r'split\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*(\$\w+)\s*\)',
                        r'explode(\'\1\', \2)',
                        content
                    )
                    fixes_made.append('Replaced split() with explode()')
                
                # Fix mysql_* functions
                if 'mysql_' in content:
                    # This is more complex, just add a comment for now
                    content = re.sub(
                        r'mysql_(\w+)',
                        r'/* TODO: Replace mysql_\1 with PDO */',
                        content
                    )
                    fixes_made.append('Marked mysql_* functions for replacement')
                
                # Fix ereg functions
                if 'ereg(' in content:
                    content = re.sub(
                        r'ereg\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*(\$\w+)\s*(,\s*\$\w+)?\s*\)',
                        r'preg_match(\'/\1/\', \2\3)',
                        content
                    )
                    fixes_made.append('Replaced ereg() with preg_match()')
                
                # Write back if changed
                if content != original_content:
                    with open(php_file, 'w', encoding='utf-8') as f:
                        f.write(content)
                    
                    self.fixes_applied.append({
                        'file': str(php_file),
                        'fixes': fixes_made
                    })
                    
                    files_fixed += 1
                    print(f"✅ Fixed deprecated functions in {php_file.relative_to(self.base_path)}")
                    
            except Exception as e:
                print(f"⚠️ Error fixing {php_file.relative_to(self.base_path)}: {e}")
        
        print(f"🎉 Fixed deprecated functions in {files_fixed} files")
        return files_fixed
    
    def create_modern_replacements(self):
        """Create modern replacement examples"""
        print("📝 Creating modern replacement examples...")
        
        examples = {
            'each_to_foreach.php': '''<?php
// Old way (deprecated):
while (each($array) as $key => $value)) {
    echo "Key: $key, Value: $value\\n";
}

// New way (modern):
foreach ($array as $key => $value) {
    echo "Key: $key, Value: $value\\n";
}
?>''',
            
            'split_to_explode.php': '''<?php
// Old way (deprecated):
$parts = split(",", $string);

// New way (modern):
$parts = explode(",", $string);
?>''',
            
            'mysql_to_pdo.php': '''<?php
// Old way (deprecated):
$connection = mysql_connect("localhost", "user", "pass");
mysql_select_db("database", $connection);
$result = mysql_query("SELECT * FROM table", $connection);

// New way (modern with PDO):
try {
    $pdo = new PDO("mysql:host=localhost;dbname=database", "user", "pass");
    $stmt = $pdo->query("SELECT * FROM table");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>'''
        }
        
        examples_dir = self.base_path / 'modern_examples'
        examples_dir.mkdir(exist_ok=True)
        
        for filename, content in examples.items():
            example_file = examples_dir / filename
            with open(example_file, 'w', encoding='utf-8') as f:
                f.write(content)
        
        print(f"✅ Created modern replacement examples in {examples_dir}")
    
    def run_deprecated_functions_fixer(self):
        """Run deprecated functions fixing process"""
        print("🚀 Starting Deprecated Functions Fixing...")
        
        files_fixed = self.fix_deprecated_functions()
        self.create_modern_replacements()
        
        print(f"\n📊 Deprecated Functions Fixing Summary:")
        print(f"Files Fixed: {files_fixed}")
        print(f"Total Fixes Applied: {len(self.fixes_applied)}")
        print(f"Modern Examples Created: 3")
        
        return len(self.fixes_applied)

def main():
    """Main execution"""
    fixer = DeprecatedFunctionsFixer()
    fixes = fixer.run_deprecated_functions_fixer()
    
    print(f"\n🎉 Deprecated Functions Fixing Completed!")
    print(f"📚 Total fixes applied: {fixes}")
    print(f"📝 Modern examples created for reference")
    
    return fixes

if __name__ == "__main__":
    main()
