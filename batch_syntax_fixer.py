#!/usr/bin/env python3
"""
Batch Syntax Fixer for SPRIN Application
Focus on fixing critical syntax errors that prevent application from working
"""

import os
import re
from pathlib import Path

def fix_filter_input_syntax(content):
    """Fix complex filter_input syntax errors"""
    # Pattern to match complex filter_input calls
    patterns = [
        (r'filter_input\s*\(\s*\$_POST\s*===\s*\\?\$_GET\s*\?\s*INPUT_GET\s*:\s*\(\s*\$_POST\s*===\s*\\?\$_POST\s*\?\s*INPUT_POST\s*:\s*INPUT_REQUEST\s*\)\s*,\s*\'([^\']+)\'\s*,\s*FILTER_SANITIZE_STRING\s*\)\s*\?\?\s*([^\)]+)', 
         r'filter_input(INPUT_POST, \'\\1\', FILTER_SANITIZE_STRING) ?? \\2'),
        
        (r'filter_input\s*\(\s*\$_GET\s*===\s*\\?\$_GET\s*\?\s*INPUT_GET\s*:\s*\(\s*\$_GET\s*===\s*\\?\$_POST\s*\?\s*INPUT_POST\s*:\s*INPUT_REQUEST\s*\)\s*,\s*\'([^\']+)\'\s*,\s*FILTER_SANITIZE_STRING\s*\)\s*\?\?\s*([^\)]+)', 
         r'filter_input(INPUT_GET, \'\\1\', FILTER_SANITIZE_STRING) ?? \\2'),
         
        (r'filter_input\s*\(\s*\$_POST\s*===\s*\\?\$_GET\s*\?\s*INPUT_GET\s*:\s*\(\s*\$_POST\s*===\s*\\?\$_POST\s*\?\s*INPUT_POST\s*:\s*INPUT_REQUEST\s*\)\s*,\s*\'([^\']+)\'\s*,\s*FILTER_SANITIZE_STRING\s*\)', 
         r'filter_input(INPUT_POST, \'\\1\', FILTER_SANITIZE_STRING)'),
         
        (r'filter_input\s*\(\s*\$_GET\s*===\s*\\?\$_GET\s*\?\s*INPUT_GET\s*:\s*\(\s*\$_GET\s*===\s*\\?\$_POST\s*\?\s*INPUT_POST\s*:\s*INPUT_REQUEST\s*\)\s*,\s*\'([^\']+)\'\s*,\s*FILTER_SANITIZE_STRING\s*\)', 
         r'filter_input(INPUT_GET, \'\\1\', FILTER_SANITIZE_STRING)'),
    ]
    
    for pattern, replacement in patterns:
        content = re.sub(pattern, replacement, content, flags=re.MULTILINE | re.DOTALL)
    
    return content

def fix_escape_sequences(content):
    """Fix escape sequence issues"""
    # Fix common escape sequence problems
    content = re.sub(r'\\\$', '$', content)
    content = re.sub(r'\\\$_', '$_', content)
    content = re.sub(r'\\\?\?', '??', content)
    content = re.sub(r'\\?\$', '$', content)
    
    return content

def fix_array_syntax(content):
    """Fix array syntax issues"""
    # Fix array syntax problems
    content = re.sub(r'\[\s*\]\s*=\s*\[', '[', content)
    content = re.sub(r'\[\s*\'([^\']+)\'\s*\]\s*=\s*', r'\'\\1\' => ', content)
    
    return content

def fix_string_termination(content):
    """Fix string termination issues"""
    lines = content.split('\n')
    fixed_lines = []
    
    for line in lines:
        # Count quotes to detect unterminated strings
        if '?>' in line and line.count('"') % 2 == 1:
            line += '"'
        if '?>' in line and line.count("'") % 2 == 1:
            line += "'"
        
        fixed_lines.append(line)
    
    return '\n'.join(fixed_lines)

def fix_php_file(file_path):
    """Fix syntax errors in a single PHP file"""
    try:
        with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        original_content = content
        
        # Apply fixes
        content = fix_filter_input_syntax(content)
        content = fix_escape_sequences(content)
        content = fix_array_syntax(content)
        content = fix_string_termination(content)
        
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
    
    # Find all PHP files with syntax errors
    error_files = [
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
        "api/v1/index.php"
    ]
    
    files_fixed = 0
    
    for file_path in error_files:
        full_path = base_path / file_path
        if full_path.exists():
            if fix_php_file(full_path):
                files_fixed += 1
                print(f"✅ Fixed: {file_path}")
            else:
                print(f"❌ Failed to fix: {file_path}")
        else:
            print(f"⚠️ File not found: {file_path}")
    
    print(f"\nBatch syntax fixing completed!")
    print(f"Files processed: {len(error_files)}")
    print(f"Files fixed: {files_fixed}")

if __name__ == "__main__":
    main()
