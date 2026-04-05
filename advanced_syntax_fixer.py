#!/usr/bin/env python3
"""
Advanced Syntax Fixer for SPRIN Application
Handle complex syntax patterns and edge cases
"""

import os
import re
from pathlib import Path

def fix_complex_filter_input(content):
    """Fix very complex filter_input patterns"""
    
    # Pattern 1: Complex ternary with multiple conditions
    pattern1 = r'filter_input\s*\(\s*\$_POST\s*===\s*\\\$_GET\s*\?\s*INPUT_GET\s*:\s*\(\s*\$_POST\s*===\s*\\\$_POST\s*\?\s*INPUT_POST\s*:\s*INPUT_REQUEST\s*\)\s*,\s*\'([^\']+)\'\s*,\s*FILTER_SANITIZE_STRING\s*\)\s*\?\?\s*([^\)]+)'
    replacement1 = r'filter_input(INPUT_POST, \'\\1\', FILTER_SANITIZE_STRING) ?? \\2'
    content = re.sub(pattern1, replacement1, content, flags=re.MULTILINE | re.DOTALL)
    
    # Pattern 2: Simple escape sequences
    content = re.sub(r'filter_input\s*\(\s*\\\$_POST\s*,', 'filter_input($_POST,', content)
    content = re.sub(r'filter_input\s*\(\s*\\\$_GET\s*,', 'filter_input($_GET,', content)
    
    # Pattern 3: Complex array access
    content = re.sub(r'\\\$_POST\[\'([^\']+)\'\]', '$_POST[\'\\1\']', content)
    content = re.sub(r'\\\$_GET\[\'([^\']+)\'\]', '$_GET[\'\\1\']', content)
    
    return content

def fix_function_calls(content):
    """Fix function call syntax issues"""
    
    # Fix incomplete function calls
    content = re.sub(r'filter_input\s*\(\s*INPUT_POST\s*,\s*\'([^\']+)\'\s*,\s*FILTER_SANITIZE_STRING\s*\)\s*\?\?\s*([^\)]+)\s*\)', 
                   r'filter_input(INPUT_POST, \'\\1\', FILTER_SANITIZE_STRING) ?? \\2', content)
    
    # Fix array syntax in function calls
    content = re.sub(r'\[\s*\'([^\']+)\'\s*=>\s*([^\]]+)\s*\]', r'\'\\1\' => \\2', content)
    
    return content

def fix_string_escapes(content):
    """Fix string escape issues"""
    
    # Fix common escape patterns
    content = re.sub(r'\\\$', '$', content)
    content = re.sub(r'\\?\$', '$', content)
    content = re.sub(r'\\\?\?', '??', content)
    content = re.sub(r'\\?\?\?', '??', content)
    
    # Fix quote issues
    content = re.sub(r'\"([^\"]*)\\\"', r'"\1"', content)
    content = re.sub(r'\'([^\']*)\\\'', r"'\1'", content)
    
    return content

def fix_bracket_issues(content):
    """Fix bracket and parenthesis mismatches"""
    
    # Fix common bracket issues
    content = re.sub(r'\)\s*\)\s*\)', ')', content)
    content = re.sub(r'\]\s*\]\s*\]', ']', content)
    
    # Fix incomplete array syntax
    content = re.sub(r'\[\s*\]\s*=\s*\[', '[', content)
    
    return content

def fix_specific_patterns(content):
    """Fix specific known problematic patterns"""
    
    # Pattern 1: rowCount issue
    content = re.sub(r'\$stmt->rowCount\(\)\s*\+\s*', '0 + ', content)
    
    # Pattern 2: Complex filter_input with multiple parameters
    content = re.sub(
        r'filter_input\s*\(\s*INPUT_POST\s*,\s*\'([^\']+)\'\s*,\s*FILTER_SANITIZE_STRING\s*\)\s*\?\?\s*([^\)]+?)\s*\)',
        r'filter_input(INPUT_POST, \'\\1\', FILTER_SANITIZE_STRING) ?? \\2',
        content
    )
    
    return content

def fix_file_advanced(file_path):
    """Advanced fixing for a specific file"""
    try:
        with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        original_content = content
        
        # Apply all fixes
        content = fix_complex_filter_input(content)
        content = fix_function_calls(content)
        content = fix_string_escapes(content)
        content = fix_bracket_issues(content)
        content = fix_specific_patterns(content)
        
        # Additional cleanup
        lines = content.split('\n')
        cleaned_lines = []
        
        for line in lines:
            # Remove extra whitespace
            line = re.sub(r'\s+', ' ', line)
            # Fix trailing spaces
            line = line.strip()
            cleaned_lines.append(line)
        
        content = '\n'.join(cleaned_lines)
        
        # Write back if changed
        if content != original_content:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
    
    except Exception as e:
        print(f"Error fixing {file_path}: {e}")
        return False

def main():
    """Main execution"""
    base_path = Path("/opt/lampp/htdocs/sprint")
    
    # Files that still have errors based on the previous run
    remaining_error_files = [
        "api/master_kepegawaian_crud.php",
        "api/router.php", 
        "api/jabatan_crud.php",
        "api/penugasan_management_v2.php",
        "api/update_pangkat.php",
        "api/personil_list.php",
        "api/unsur_crud.php",
        "api/search_personil.php",
        "api/DatabaseStructureChecker.php",
        "api/calendar_api.php",
        "api/health_check.php",
        "api/personil_simple.php",
        "api/backup_api.php",
        "api/personil_crud.php",
        "api/penugasan_crud.php",
        "api/calendar_api_fixed.php",
        "api/unsur_stats.php",
        "api/critical_tables_crud.php",
        "api/v1/index.php",
        "includes/components/nav_header_v2.php"
    ]
    
    files_fixed = 0
    
    for file_path in remaining_error_files:
        full_path = base_path / file_path
        if full_path.exists():
            if fix_file_advanced(full_path):
                files_fixed += 1
                print(f"✅ Fixed: {file_path}")
            else:
                print(f"❌ Failed to fix: {file_path}")
        else:
            print(f"⚠️ File not found: {file_path}")
    
    print(f"\nAdvanced syntax fixing completed!")
    print(f"Files processed: {len(remaining_error_files)}")
    print(f"Files fixed: {files_fixed}")

if __name__ == "__main__":
    main()
