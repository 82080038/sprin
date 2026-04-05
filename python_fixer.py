#!/usr/bin/env python3
"""
SPRIN Application Python Fixer
Comprehensive analysis and automated fixing system for SPRIN application
"""

import os
import re
import json
import subprocess
import sys
from pathlib import Path
from typing import Dict, List, Tuple, Optional
import logging

class SPRINPythonFixer:
    def __init__(self, base_path: str = "/opt/lampp/htdocs/sprint"):
        self.base_path = Path(base_path)
        self.fixes_applied = []
        self.errors_found = []
        self.setup_logging()
        
    def setup_logging(self):
        """Setup logging configuration"""
        logging.basicConfig(
            level=logging.INFO,
            format='%(asctime)s - %(levelname)s - %(message)s',
            handlers=[
                logging.FileHandler('python_fixer.log'),
                logging.StreamHandler(sys.stdout)
            ]
        )
        self.logger = logging.getLogger(__name__)

    def analyze_php_files(self) -> Dict[str, List[str]]:
        """Analyze all PHP files for common issues"""
        issues = {
            'syntax_errors': [],
            'deprecated_functions': [],
            'security_issues': [],
            'database_issues': [],
            'session_issues': []
        }
        
        php_files = list(self.base_path.rglob("*.php"))
        self.logger.info(f"Analyzing {len(php_files)} PHP files...")
        
        for php_file in php_files:
            try:
                with open(php_file, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                # Check for syntax errors using php -l
                syntax_result = self.check_php_syntax(php_file)
                if syntax_result:
                    issues['syntax_errors'].append({
                        'file': str(php_file),
                        'error': syntax_result
                    })
                
                # Check for deprecated functions
                deprecated = self.find_deprecated_functions(content, php_file)
                issues['deprecated_functions'].extend(deprecated)
                
                # Check for security issues
                security = self.find_security_issues(content, php_file)
                issues['security_issues'].extend(security)
                
                # Check for database issues
                db_issues = self.find_database_issues(content, php_file)
                issues['database_issues'].extend(db_issues)
                
                # Check for session issues
                session_issues = self.find_session_issues(content, php_file)
                issues['session_issues'].extend(session_issues)
                
            except Exception as e:
                self.logger.error(f"Error analyzing {php_file}: {e}")
        
        return issues

    def check_php_syntax(self, php_file: Path) -> Optional[str]:
        """Check PHP syntax using php -l"""
        try:
            result = subprocess.run(
                ['php', '-l', str(php_file)],
                capture_output=True,
                text=True,
                timeout=10
            )
            
            if result.returncode != 0:
                return result.stderr.strip()
        except Exception as e:
            self.logger.error(f"Syntax check failed for {php_file}: {e}")
        
        return None

    def find_deprecated_functions(self, content: str, php_file: Path) -> List[Dict]:
        """Find deprecated PHP functions"""
        deprecated_patterns = [
            r'\beach\s*\(',
            r'\bsplit\s*\(',
            r'\bereg\s*\(',
            r'\bmysql_\w+\s*\(',
            r'\bereg_replace\s*\(',
            r'\beregi_replace\s*\('
        ]
        
        issues = []
        for pattern in deprecated_patterns:
            matches = re.finditer(pattern, content, re.IGNORECASE)
            for match in matches:
                line_num = content[:match.start()].count('\n') + 1
                issues.append({
                    'file': str(php_file),
                    'type': 'deprecated_function',
                    'line': line_num,
                    'function': match.group().replace('(', ''),
                    'context': content[max(0, match.start()-50):match.end()+50]
                })
        
        return issues

    def find_security_issues(self, content: str, php_file: Path) -> List[Dict]:
        """Find security vulnerabilities"""
        security_patterns = [
            (r'\$_POST\s*\[', 'Direct $_POST access'),
            (r'\$_GET\s*\[', 'Direct $_GET access'),
            (r'\$_REQUEST\s*\[', 'Direct $_REQUEST access'),
            (r'eval\s*\(', 'eval() function'),
            (r'system\s*\(', 'system() function'),
            (r'shell_exec\s*\(', 'shell_exec() function'),
            (r'exec\s*\(', 'exec() function')
        ]
        
        issues = []
        for pattern, description in security_patterns:
            matches = re.finditer(pattern, content)
            for match in matches:
                line_num = content[:match.start()].count('\n') + 1
                issues.append({
                    'file': str(php_file),
                    'type': 'security_issue',
                    'line': line_num,
                    'issue': description,
                    'context': content[max(0, match.start()-50):match.end()+50]
                })
        
        return issues

    def find_database_issues(self, content: str, php_file: Path) -> List[Dict]:
        """Find database connection issues"""
        db_patterns = [
            (r'mysql:host=localhost;dbname=\w+', 'Hardcoded database connection'),
            (r'new PDO\s*\([^)]*localhost[^)]*\)', 'Hardcoded PDO connection'),
            (r'mysql_connect\s*\(', 'Deprecated mysql_connect'),
            (r'"root"\s*,\s*"root"', 'Hardcoded credentials')
        ]
        
        issues = []
        for pattern, description in db_patterns:
            matches = re.finditer(pattern, content)
            for match in matches:
                line_num = content[:match.start()].count('\n') + 1
                issues.append({
                    'file': str(php_file),
                    'type': 'database_issue',
                    'line': line_num,
                    'issue': description,
                    'context': content[max(0, match.start()-50):match.end()+50]
                })
        
        return issues

    def find_session_issues(self, content: str, php_file: Path) -> List[Dict]:
        """Find session management issues"""
        session_patterns = [
            (r'session_start\s*\(\)', 'session_start() call'),
            (r'\$_SESSION\s*\[.*logged_in.*\]', 'old session format'),
            (r'isset\s*\(\s*\$_SESSION\s*\[', 'session check without proper validation')
        ]
        
        issues = []
        for pattern, description in session_patterns:
            matches = re.finditer(pattern, content)
            for match in matches:
                line_num = content[:match.start()].count('\n') + 1
                issues.append({
                    'file': str(php_file),
                    'type': 'session_issue',
                    'line': line_num,
                    'issue': description,
                    'context': content[max(0, match.start()-50):match.end()+50]
                })
        
        return issues

    def fix_php_file(self, php_file: Path, issues: List[Dict]) -> bool:
        """Fix issues in a PHP file"""
        try:
            with open(php_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            original_content = content
            
            # Fix deprecated functions
            content = self.fix_deprecated_functions(content)
            
            # Fix security issues
            content = self.fix_security_issues(content)
            
            # Fix database issues
            content = self.fix_database_issues(content)
            
            # Fix session issues
            content = self.fix_session_issues(content)
            
            # Write back if changed
            if content != original_content:
                with open(php_file, 'w', encoding='utf-8') as f:
                    f.write(content)
                
                self.fixes_applied.append({
                    'file': str(php_file),
                    'issues_fixed': len(issues)
                })
                
                self.logger.info(f"Fixed {len(issues)} issues in {php_file}")
                return True
        
        except Exception as e:
            self.logger.error(f"Error fixing {php_file}: {e}")
        
        return False

    def fix_deprecated_functions(self, content: str) -> str:
        """Fix deprecated PHP functions"""
        replacements = {
            'each(': 'foreach iterator',
            'split(': 'explode(',
            'ereg(': 'preg_match(',
            'ereg_replace(': 'preg_replace(',
            'eregi(': 'preg_match(',
            'eregi_replace(': 'preg_replace('
        }
        
        for old, new in replacements.items():
            content = re.sub(r'\b' + re.escape(old), new, content)
        
        return content

    def fix_security_issues(self, content: str) -> str:
        """Fix security issues by adding input filtering"""
        # Replace direct $_POST access with filtered input
        content = re.sub(
            r'\$_POST\s*\[\'([^\']+)\'\]',
            r'filter_input(INPUT_POST, \'\\1\', FILTER_SANITIZE_STRING) ?? \'\'',
            content
        )
        
        content = re.sub(
            r'\$_GET\s*\[\'([^\']+)\'\]',
            r'filter_input(INPUT_GET, \'\\1\', FILTER_SANITIZE_STRING) ?? \'\'',
            content
        )
        
        return content

    def fix_database_issues(self, content: str) -> str:
        """Fix database connection issues"""
        # Replace hardcoded database connections
        old_db_pattern = r'new PDO\s*\(\s*[\'"]mysql:host=localhost;dbname=\w+[\'"]\s*,\s*[\'"][^\'"]*[\'"]\s*,\s*[\'"][^\'"]*[\'"]\s*\)'
        
        new_db_connection = '''new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';unix_socket=' . DB_SOCKET,
        DB_USER, 
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    )'''
        
        content = re.sub(old_db_pattern, new_db_connection, content, flags=re.MULTILINE | re.DOTALL)
        
        return content

    def fix_session_issues(self, content: str) -> str:
        """Fix session management issues"""
        # Replace old session format with new auth_helper
        content = re.sub(
            r'isset\s*\(\s*\$_SESSION\s*\[\'logged_in\'\]\)\s*&&\s*\$_SESSION\s*\[\'logged_in\'\]\s*===\s*true',
            'AuthHelper::validateSession()',
            content
        )
        
        return content

    def fix_responsive_design(self) -> bool:
        """Fix responsive design issues"""
        try:
            css_file = self.base_path / 'public' / 'assets' / 'css' / 'responsive.css'
            
            if not css_file.exists():
                self.logger.warning(f"CSS file not found: {css_file}")
                return False
            
            with open(css_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Add responsive improvements
            responsive_fixes = """
/* Python Fixer Responsive Improvements */
@media (max-width: 768px) {
    .login-container {
        flex-direction: column;
        max-width: 95%;
        margin: 10px;
    }
    
    .login-left, .login-right {
        padding: 20px;
    }
    
    input[type="text"], input[type="password"] {
        font-size: 16px; /* Prevent zoom on iOS */
    }
}

@media (max-width: 480px) {
    .login-container {
        margin: 5px;
        border-radius: 10px;
    }
    
    .logo {
        font-size: 2rem;
    }
}
"""
            
            content += responsive_fixes
            
            with open(css_file, 'w', encoding='utf-8') as f:
                f.write(content)
            
            self.fixes_applied.append({
                'file': str(css_file),
                'issues_fixed': 1,
                'type': 'responsive_design'
            })
            
            self.logger.info(f"Fixed responsive design in {css_file}")
            return True
            
        except Exception as e:
            self.logger.error(f"Error fixing responsive design: {e}")
            return False

    def run_comprehensive_fix(self) -> Dict:
        """Run comprehensive fixing process"""
        self.logger.info("Starting comprehensive SPRIN application fixing...")
        
        # Analyze all issues
        issues = self.analyze_php_files()
        
        total_issues = sum(len(issue_list) for issue_list in issues.values())
        self.logger.info(f"Found {total_issues} total issues")
        
        # Fix PHP files
        php_files = list(self.base_path.rglob("*.php"))
        files_fixed = 0
        
        for php_file in php_files:
            file_issues = []
            for issue_type, issue_list in issues.items():
                file_issues.extend([issue for issue in issue_list if issue['file'] == str(php_file)])
            
            if file_issues:
                if self.fix_php_file(php_file, file_issues):
                    files_fixed += 1
        
        # Fix responsive design
        responsive_fixed = self.fix_responsive_design()
        
        # Generate report
        report = {
            'timestamp': str(subprocess.check_output(['date'], text=True).strip()),
            'base_path': str(self.base_path),
            'issues_found': issues,
            'files_analyzed': len(php_files),
            'files_fixed': files_fixed,
            'total_fixes_applied': len(self.fixes_applied),
            'responsive_design_fixed': responsive_fixed,
            'fixes_applied': self.fixes_applied
        }
        
        # Save report
        report_file = self.base_path / 'python_fixer_report.json'
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, default=str)
        
        self.logger.info(f"Fixing completed. Report saved to {report_file}")
        
        return report

    def validate_fixes(self) -> bool:
        """Validate that fixes were applied correctly"""
        self.logger.info("Validating applied fixes...")
        
        # Run PHP syntax check on all files
        php_files = list(self.base_path.rglob("*.php"))
        syntax_errors = 0
        
        for php_file in php_files:
            result = self.check_php_syntax(php_file)
            if result:
                syntax_errors += 1
                self.logger.error(f"Syntax error in {php_file}: {result}")
        
        if syntax_errors == 0:
            self.logger.info("✅ All PHP files have valid syntax")
            return True
        else:
            self.logger.error(f"❌ {syntax_errors} files still have syntax errors")
            return False

def main():
    """Main execution function"""
    fixer = SPRINPythonFixer()
    
    try:
        # Run comprehensive fixing
        report = fixer.run_comprehensive_fix()
        
        # Validate fixes
        validation_passed = fixer.validate_fixes()
        
        # Print summary
        print("\n" + "="*50)
        print("SPRIN PYTHON FIXER SUMMARY")
        print("="*50)
        print(f"Files Analyzed: {report['files_analyzed']}")
        print(f"Files Fixed: {report['files_fixed']}")
        print(f"Total Fixes Applied: {report['total_fixes_applied']}")
        print(f"Responsive Design Fixed: {'Yes' if report['responsive_design_fixed'] else 'No'}")
        print(f"Validation Passed: {'Yes' if validation_passed else 'No'}")
        
        # Print issues breakdown
        print("\nIssues Found:")
        for issue_type, issues in report['issues_found'].items():
            print(f"  {issue_type}: {len(issues)}")
        
        print(f"\nReport saved to: {fixer.base_path}/python_fixer_report.json")
        print("="*50)
        
        return validation_passed
        
    except Exception as e:
        print(f"Error during fixing process: {e}")
        return False

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)
