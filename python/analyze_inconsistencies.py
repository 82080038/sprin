#!/usr/bin/env python3
"""
SPRIN Application Consistency Analyzer
Analyzes and fixes inconsistencies across FE, API, and BE
"""

import os
import re
import json
from pathlib import Path
from collections import defaultdict

class ConsistencyAnalyzer:
    def __init__(self, root_path):
        self.root = Path(root_path)
        self.issues = defaultdict(list)
        self.fixed_files = []
        
    def analyze_all(self):
        """Run all analysis"""
        print("=" * 70)
        print("SPRIN APPLICATION CONSISTENCY ANALYZER")
        print("=" * 70)
        
        self.analyze_error_reporting()
        self.analyze_csrf_patterns()
        self.analyze_session_management()
        self.analyze_api_response_formats()
        self.analyze_database_connections()
        self.analyze_includes()
        self.analyze_javascript_patterns()
        
        return self.issues
    
    def analyze_error_reporting(self):
        """Check error reporting patterns"""
        print("\n[1] Analyzing Error Reporting Patterns...")
        
        php_files = list(self.root.rglob("*.php"))
        patterns = {
            'direct_error_reporting': r'error_reporting\(E_ALL\)',
            'config_based': r'defined\([\'"]DEBUG_MODE[\'"]\)',
            'old_pattern': r'if \(ENVIRONMENT !== [\'"]development[\'"]\)',
        }
        
        for file in php_files:
            content = file.read_text(encoding='utf-8', errors='ignore')
            
            # Check for direct error reporting (should use config.php)
            if re.search(patterns['direct_error_reporting'], content) and 'config.php' not in content:
                if 'api/' in str(file) or 'pages/' in str(file):
                    self.issues['error_reporting'].append({
                        'file': str(file),
                        'issue': 'Direct error_reporting without config.php',
                        'severity': 'medium'
                    })
            
            # Check for old ENVIRONMENT pattern (should use DEBUG_MODE)
            if re.search(patterns['old_pattern'], content):
                self.issues['error_reporting'].append({
                    'file': str(file),
                    'issue': 'Uses old ENVIRONMENT check instead of DEBUG_MODE',
                    'severity': 'low'
                })
        
        print(f"   Found {len(self.issues['error_reporting'])} error reporting issues")
    
    def analyze_csrf_patterns(self):
        """Check CSRF token handling"""
        print("\n[2] Analyzing CSRF Patterns...")
        
        php_files = list(self.root.rglob("*.php"))
        
        for file in php_files:
            if 'api/' not in str(file):
                continue
                
            content = file.read_text(encoding='utf-8', errors='ignore')
            
            # Check for CSRF validation
            has_csrf_check = 'csrf_token' in content.lower() or 'CSRF' in content
            has_session_start = 'session_start' in content or 'SessionManager::start' in content
            
            if has_csrf_check and not has_session_start:
                self.issues['csrf'].append({
                    'file': str(file),
                    'issue': 'CSRF check without session_start',
                    'severity': 'high'
                })
            
            # Check for inconsistent token retrieval
            if re.search(r'\$_POST\[[\'"]csrf_token[\'"]\]', content) and \
               re.search(r'\$_SERVER\[[\'"]HTTP_X_CSRF_TOKEN[\'"]\]', content):
                # This is actually good - checks both
                pass
            elif re.search(r'\$_POST\[[\'"]csrf_token[\'"]\]', content) and \
                 not re.search(r'\$_SERVER\[[\'"]HTTP_X_CSRF_TOKEN[\'"]\]', content):
                self.issues['csrf'].append({
                    'file': str(file),
                    'issue': 'Only checks POST csrf_token, not header',
                    'severity': 'low'
                })
        
        print(f"   Found {len(self.issues['csrf'])} CSRF issues")
    
    def analyze_session_management(self):
        """Check session management patterns"""
        print("\n[3] Analyzing Session Management...")
        
        php_files = list(self.root.rglob("*.php"))
        
        for file in php_files:
            content = file.read_text(encoding='utf-8', errors='ignore')
            
            # Check for direct session_start vs SessionManager
            has_direct_session = 'session_start()' in content
            has_session_manager = 'SessionManager::start()' in content or \
                                  'SessionManager::isActive()' in content
            
            if has_direct_session and not has_session_manager:
                if 'core/' not in str(file):  # Exclude core files
                    self.issues['session'].append({
                        'file': str(file),
                        'issue': 'Uses direct session_start() instead of SessionManager',
                        'severity': 'medium'
                    })
        
        print(f"   Found {len(self.issues['session'])} session management issues")
    
    def analyze_api_response_formats(self):
        """Check API response format consistency"""
        print("\n[4] Analyzing API Response Formats...")
        
        api_files = list((self.root / 'api').rglob("*.php"))
        
        for file in api_files:
            content = file.read_text(encoding='utf-8', errors='ignore')
            
            # Check for inconsistent success response format
            if re.search(r"echo json_encode\(\[\s*'success'\s*=>", content):
                # Good - has success field
                pass
            else:
                # Check if it's a JSON API at all
                if 'application/json' in content or 'json_encode' in content:
                    self.issues['api_format'].append({
                        'file': str(file),
                        'issue': 'JSON response without success field',
                        'severity': 'medium'
                    })
            
            # Check for error response format
            if re.search(r'catch.*Exception', content, re.DOTALL):
                if not re.search(r"'success'\s*=>\s*false", content):
                    self.issues['api_format'].append({
                        'file': str(file),
                        'issue': 'Exception handler without standardized error response',
                        'severity': 'medium'
                    })
        
        print(f"   Found {len(self.issues['api_format'])} API format issues")
    
    def analyze_database_connections(self):
        """Check database connection patterns"""
        print("\n[5] Analyzing Database Connection Patterns...")
        
        php_files = list(self.root.rglob("*.php"))
        
        for file in php_files:
            if 'core/' in str(file):
                continue
                
            content = file.read_text(encoding='utf-8', errors='ignore')
            
            # Check for direct PDO vs Database class
            has_direct_pdo = re.search(r'new PDO\s*\(', content)
            has_database_class = 'Database::getInstance()' in content or \
                                 'Database::' in content
            
            if has_direct_pdo and not has_database_class:
                self.issues['database'].append({
                    'file': str(file),
                    'issue': 'Uses direct PDO instead of Database class',
                    'severity': 'low'
                })
        
        print(f"   Found {len(self.issues['database'])} database connection issues")
    
    def analyze_includes(self):
        """Check include/require patterns"""
        print("\n[6] Analyzing Include/Require Patterns...")
        
        php_files = list(self.root.rglob("*.php"))
        
        required_includes = [
            'config.php',
            'SessionManager.php',
            'auth_helper.php'
        ]
        
        for file in php_files:
            if 'core/' in str(file) or 'includes/' in str(file):
                continue
                
            content = file.read_text(encoding='utf-8', errors='ignore')
            
            # Check if API/page files have required includes
            if 'api/' in str(file) or 'pages/' in str(file):
                for include in required_includes:
                    if include not in content:
                        # Check if it's actually needed (has auth or session usage)
                        if include == 'auth_helper.php' and 'AuthHelper::' in content:
                            self.issues['includes'].append({
                                'file': str(file),
                                'issue': f'Uses AuthHelper but missing {include}',
                                'severity': 'high'
                            })
                        elif include == 'SessionManager.php' and 'SessionManager::' in content:
                            self.issues['includes'].append({
                                'file': str(file),
                                'issue': f'Uses SessionManager but missing {include}',
                                'severity': 'high'
                            })
        
        print(f"   Found {len(self.issues['includes'])} include issues")
    
    def analyze_javascript_patterns(self):
        """Check JavaScript patterns in PHP files"""
        print("\n[7] Analyzing JavaScript Patterns...")
        
        php_files = list((self.root / 'pages').rglob("*.php"))
        
        for file in php_files:
            content = file.read_text(encoding='utf-8', errors='ignore')
            
            # Check for old alert() usage (should use showToast)
            if 'alert(' in content and 'confirm(' not in content:
                # Skip if it's inside a comment
                lines = content.split('\n')
                for i, line in enumerate(lines):
                    if 'alert(' in line and 'confirm(' not in line:
                        # Check if it's a standalone alert (not in confirm flow)
                        if not line.strip().startswith('//'):
                            self.issues['javascript'].append({
                                'file': str(file),
                                'line': i + 1,
                                'issue': 'Uses alert() instead of showToast()',
                                'severity': 'low'
                            })
            
            # Check for inconsistent fetch patterns
            if 'fetch(' in content:
                if 'credentials' not in content:
                    self.issues['javascript'].append({
                        'file': str(file),
                        'issue': 'fetch() without credentials option',
                        'severity': 'medium'
                    })
        
        print(f"   Found {len(self.issues['javascript'])} JavaScript issues")
    
    def generate_report(self):
        """Generate comprehensive report"""
        print("\n" + "=" * 70)
        print("ANALYSIS REPORT")
        print("=" * 70)
        
        total_issues = sum(len(v) for v in self.issues.values())
        print(f"\nTotal Issues Found: {total_issues}")
        print(f"Categories: {len(self.issues)}")
        
        for category, issues in sorted(self.issues.items()):
            if issues:
                print(f"\n{category.upper().replace('_', ' ')} ({len(issues)} issues)")
                print("-" * 50)
                
                # Group by severity
                high = [i for i in issues if i.get('severity') == 'high']
                medium = [i for i in issues if i.get('severity') == 'medium']
                low = [i for i in issues if i.get('severity') == 'low']
                
                if high:
                    print("  HIGH SEVERITY:")
                    for issue in high[:5]:  # Show first 5
                        print(f"    ❌ {issue['file'].replace(str(self.root), '')}")
                        print(f"       {issue['issue']}")
                
                if medium:
                    print("  MEDIUM SEVERITY:")
                    for issue in medium[:3]:
                        print(f"    ⚠️  {issue['file'].replace(str(self.root), '')}")
                
                if low:
                    print(f"  LOW SEVERITY: {len(low)} issues")
        
        return total_issues
    
    def fix_issues(self, auto_fix=False):
        """Fix identified issues"""
        print("\n" + "=" * 70)
        print("FIXING ISSUES")
        print("=" * 70)
        
        if not auto_fix:
            print("Run with auto_fix=True to apply fixes")
            return
        
        fixed_count = 0
        
        # Fix error reporting issues
        for issue in self.issues.get('error_reporting', []):
            if issue['severity'] in ['medium', 'high']:
                if self._fix_error_reporting(issue['file']):
                    fixed_count += 1
        
        # Fix include issues
        for issue in self.issues.get('includes', []):
            if issue['severity'] == 'high':
                if self._fix_includes(issue['file'], issue['issue']):
                    fixed_count += 1
        
        print(f"\nFixed {fixed_count} issues")
        return fixed_count
    
    def _fix_error_reporting(self, file_path):
        """Fix error reporting pattern"""
        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Replace direct error_reporting with config-based
            if 'error_reporting(E_ALL)' in content and 'config.php' not in content:
                new_content = content.replace(
                    'error_reporting(E_ALL);',
                    "require_once __DIR__ . '/../core/config.php';\nerror_reporting(E_ALL);"
                )
                
                with open(file_path, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                
                print(f"  ✓ Fixed error reporting in {file_path}")
                return True
                
        except Exception as e:
            print(f"  ✗ Failed to fix {file_path}: {e}")
        
        return False
    
    def _fix_includes(self, file_path, issue):
        """Fix missing includes"""
        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            missing = None
            if 'auth_helper.php' in issue:
                missing = "require_once __DIR__ . '/../core/auth_helper.php';"
            elif 'SessionManager.php' in issue:
                missing = "require_once __DIR__ . '/../core/SessionManager.php';"
            
            if missing and missing not in content:
                # Add after config.php include
                if 'config.php' in content:
                    content = content.replace(
                        "config.php';",
                        "config.php';\n" + missing
                    )
                else:
                    # Add at the top after <?php
                    content = content.replace(
                        '<?php',
                        "<?php\n" + missing
                    )
                
                with open(file_path, 'w', encoding='utf-8') as f:
                    f.write(content)
                
                print(f"  ✓ Fixed includes in {file_path}")
                return True
                
        except Exception as e:
            print(f"  ✗ Failed to fix {file_path}: {e}")
        
        return False


def main():
    """Main entry point"""
    root_path = '/opt/lampp/htdocs/sprin'
    
    analyzer = ConsistencyAnalyzer(root_path)
    
    # Run analysis
    issues = analyzer.analyze_all()
    
    # Generate report
    total = analyzer.generate_report()
    
    # Export to JSON for detailed review
    report_file = os.path.join(root_path, 'consistency_report.json')
    with open(report_file, 'w') as f:
        json.dump(dict(issues), f, indent=2, default=str)
    
    print(f"\n📄 Detailed report saved to: {report_file}")
    
    # Ask for auto-fix
    if total > 0:
        print("\n" + "=" * 70)
        print("Run with auto_fix=True to automatically fix issues")
        print("=" * 70)


if __name__ == '__main__':
    main()
