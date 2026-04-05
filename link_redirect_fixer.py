#!/usr/bin/env python3
"""
Link and Redirect Fixer for SPRIN Application
Detects and fixes broken links and incorrect redirects
"""

import os
import re
import json
import urllib.parse
from datetime import datetime
from pathlib import Path
from typing import Dict, List, Tuple, Set
from collections import defaultdict

class LinkRedirectFixer:
    def __init__(self, base_path: str = "/opt/lampp/htdocs/sprint"):
        self.base_path = Path(base_path)
        self.base_url = "http://localhost/sprint"
        self.issues_found = []
        self.fixes_applied = []
        
    def scan_all_links_and_redirects(self):
        """Scan all files for links and redirects"""
        print("🔍 Scanning all files for links and redirects...")
        
        all_links = []
        all_redirects = []
        
        # Scan PHP files
        php_files = list(self.base_path.rglob("*.php"))
        for php_file in php_files:
            try:
                with open(php_file, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                # Find links
                links = self.extract_links(content, str(php_file))
                all_links.extend(links)
                
                # Find redirects
                redirects = self.extract_redirects(content, str(php_file))
                all_redirects.extend(redirects)
                
            except Exception as e:
                print(f"Error scanning {php_file}: {e}")
        
        # Scan HTML/JS/CSS files
        html_files = list(self.base_path.rglob("*.html")) + list(self.base_path.rglob("*.htm"))
        js_files = list(self.base_path.rglob("*.js"))
        css_files = list(self.base_path.rglob("*.css"))
        
        for file_list in [html_files, js_files, css_files]:
            for file_path in file_list:
                try:
                    with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                        content = f.read()
                    
                    links = self.extract_links(content, str(file_path))
                    all_links.extend(links)
                    
                except Exception as e:
                    print(f"Error scanning {file_path}: {e}")
        
        return all_links, all_redirects
    
    def extract_links(self, content: str, file_path: str) -> List[Dict]:
        """Extract all links from content"""
        links = []
        
        # HTML links
        html_links = re.findall(r'<a[^>]+href=[\'"]([^\'"]+)[\'"][^>]*>', content, re.IGNORECASE)
        for link in html_links:
            links.append({
                'type': 'html_link',
                'url': link,
                'file': file_path,
                'context': 'HTML anchor tag'
            })
        
        # CSS links
        css_links = re.findall(r'url\([\'"]?([^\'")\s]+)[\'"]?\)', content, re.IGNORECASE)
        for link in css_links:
            links.append({
                'type': 'css_link',
                'url': link,
                'file': file_path,
                'context': 'CSS url() function'
            })
        
        # JavaScript links
        js_links = re.findall(r'(?:location\.href|window\.location|fetch\(|ajax\(|\.load\()[\'"]([^\'"]+)[\'"]', content, re.IGNORECASE)
        for link in js_links:
            links.append({
                'type': 'javascript_link',
                'url': link,
                'file': file_path,
                'context': 'JavaScript navigation/AJAX'
            })
        
        # Form actions
        form_actions = re.findall(r'<form[^>]+action=[\'"]([^\'"]+)[\'"][^>]*>', content, re.IGNORECASE)
        for link in form_actions:
            links.append({
                'type': 'form_action',
                'url': link,
                'file': file_path,
                'context': 'HTML form action'
            })
        
        return links
    
    def extract_redirects(self, content: str, file_path: str) -> List[Dict]:
        """Extract all redirects from content"""
        redirects = []
        
        # PHP header redirects
        header_redirects = re.findall(r'header\s*\(\s*[\'"]Location:[^\']*([^\s\'"]+)[^\s\'"]*[\'"]\s*\)', content, re.IGNORECASE)
        for redirect in header_redirects:
            redirects.append({
                'type': 'php_header_redirect',
                'url': redirect.strip(),
                'file': file_path,
                'context': 'PHP header() redirect'
            })
        
        # JavaScript redirects
        js_redirects = re.findall(r'(?:location\.href|window\.location)\s*=\s*[\'"]([^\'"]+)[\'"]', content, re.IGNORECASE)
        for redirect in js_redirects:
            redirects.append({
                'type': 'javascript_redirect',
                'url': redirect,
                'file': file_path,
                'context': 'JavaScript redirect'
            })
        
        # Meta refresh redirects
        meta_redirects = re.findall(r'<meta[^>]+http-equiv=[\'"]refresh[\'"][^>]+url=[\'"]([^\'"]+)[\'"][^>]*>', content, re.IGNORECASE)
        for redirect in meta_redirects:
            redirects.append({
                'type': 'meta_refresh_redirect',
                'url': redirect,
                'file': file_path,
                'context': 'HTML meta refresh'
            })
        
        return redirects
    
    def validate_links(self, links: List[Dict]) -> List[Dict]:
        """Validate all links and identify issues"""
        print("🔗 Validating links...")
        
        issues = []
        existing_files = set()
        
        # Get all existing files
        for root, dirs, files in os.walk(self.base_path):
            for file in files:
                existing_files.add(file)
        
        for link in links:
            url = link['url']
            issue = None
            
            # Skip external links and anchors
            if url.startswith(('http://', 'https://', '#', 'mailto:', 'tel:')):
                continue
            
            # Skip protocol-relative links
            if url.startswith('//'):
                continue
            
            # Clean URL
            clean_url = url.strip()
            if clean_url.startswith('/'):
                clean_url = clean_url[1:]
            
            # Check for common issues
            if clean_url == '':
                issue = {
                    'type': 'empty_link',
                    'severity': 'medium',
                    'description': 'Empty link URL',
                    'link': link
                }
            elif clean_url.startswith(('../', './')):
                issue = {
                    'type': 'relative_path',
                    'severity': 'medium',
                    'description': 'Relative path link',
                    'link': link,
                    'suggested_fix': self.normalize_relative_path(clean_url, link['file'])
                }
            elif '?' in clean_url and not clean_url.endswith('.php'):
                issue = {
                    'type': 'query_string_without_php',
                    'severity': 'high',
                    'description': 'Query string without .php extension',
                    'link': link,
                    'suggested_fix': self.fix_query_string_link(clean_url)
                }
            else:
                # Check if file exists
                file_name = Path(clean_url).name
                if file_name not in existing_files and not any(existing_file.endswith(clean_url) for existing_file in existing_files):
                    # Try to find similar files
                    similar_files = self.find_similar_files(clean_url, existing_files)
                    issue = {
                        'type': 'file_not_found',
                        'severity': 'high',
                        'description': f'Target file not found: {clean_url}',
                        'link': link,
                        'similar_files': similar_files,
                        'suggested_fix': self.suggest_file_fix(clean_url, similar_files)
                    }
            
            if issue:
                issues.append(issue)
                self.issues_found.append(issue)
        
        return issues
    
    def normalize_relative_path(self, path: str, current_file: str) -> str:
        """Normalize relative path to absolute path"""
        current_dir = Path(current_file).parent
        target_path = (current_dir / path).resolve()
        
        # Convert to URL path
        relative_to_base = target_path.relative_to(self.base_path)
        return str(relative_to_base).replace('\\', '/')
    
    def fix_query_string_link(self, url: str) -> str:
        """Fix query string links without .php extension"""
        if '?' in url:
            base_part = url.split('?')[0]
            query_part = url.split('?')[1]
            
            # Try common .php files
            common_files = ['index.php', 'main.php', 'dashboard.php']
            for common_file in common_files:
                if (self.base_path / common_file).exists():
                    return f"{common_file}?{query_part}"
        
        return url
    
    def find_similar_files(self, target: str, existing_files: Set[str]) -> List[str]:
        """Find similar files for suggestions"""
        similar = []
        target_lower = target.lower()
        
        for existing in existing_files:
            existing_lower = existing.lower()
            
            # Check for partial matches
            if (target_lower in existing_lower or 
                existing_lower in target_lower or
                self.calculate_similarity(target_lower, existing_lower) > 0.6):
                similar.append(existing)
        
        return similar[:5]  # Return top 5 suggestions
    
    def calculate_similarity(self, str1: str, str2: str) -> float:
        """Calculate similarity between two strings"""
        # Simple Levenshtein distance approximation
        if str1 == str2:
            return 1.0
        
        len1, len2 = len(str1), len(str2)
        if len1 == 0 or len2 == 0:
            return 0.0
        
        # Count common characters
        common = sum(1 for c in str1 if c in str2)
        return common / max(len1, len2)
    
    def suggest_file_fix(self, target: str, similar_files: List[str]) -> str:
        """Suggest file fix based on similar files"""
        if not similar_files:
            return target
        
        # Prefer files with .php extension
        php_files = [f for f in similar_files if f.endswith('.php')]
        if php_files:
            return php_files[0]
        
        return similar_files[0]
    
    def validate_redirects(self, redirects: List[Dict]) -> List[Dict]:
        """Validate all redirects and identify issues"""
        print("🔄 Validating redirects...")
        
        issues = []
        
        for redirect in redirects:
            url = redirect['url']
            issue = None
            
            # Check for common redirect issues
            if url == '':
                issue = {
                    'type': 'empty_redirect',
                    'severity': 'high',
                    'description': 'Empty redirect URL',
                    'redirect': redirect
                }
            elif url.startswith(('http://', 'https://')):
                # Check if external redirect is appropriate
                if 'localhost' not in url and 'sprint' not in url:
                    issue = {
                        'type': 'external_redirect',
                        'severity': 'medium',
                        'description': f'External redirect to: {url}',
                        'redirect': redirect
                    }
            elif url.startswith('../') or url.startswith('./'):
                issue = {
                    'type': 'relative_redirect',
                    'severity': 'medium',
                    'description': 'Relative path redirect',
                    'redirect': redirect,
                    'suggested_fix': self.normalize_relative_path(url, redirect['file'])
                }
            elif not url.endswith('.php') and '?' not in url and '#' not in url:
                # Check if it should be a .php file
                potential_php = f"{url}.php"
                if (self.base_path / potential_php).exists():
                    issue = {
                        'type': 'missing_php_extension',
                        'severity': 'high',
                        'description': f'Redirect should use .php extension: {url}',
                        'redirect': redirect,
                        'suggested_fix': potential_php
                    }
            
            if issue:
                issues.append(issue)
                self.issues_found.append(issue)
        
        return issues
    
    def fix_link_issues(self):
        """Fix identified link issues"""
        print("🔧 Fixing link issues...")
        
        # Group issues by file
        issues_by_file = defaultdict(list)
        for issue in self.issues_found:
            if 'link' in issue:
                issues_by_file[issue['link']['file']].append(issue)
        
        for file_path, file_issues in issues_by_file.items():
            try:
                with open(file_path, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                original_content = content
                
                for issue in file_issues:
                    if issue['type'] == 'relative_path' and 'suggested_fix' in issue:
                        # Fix relative path
                        old_url = issue['link']['url']
                        new_url = issue['suggested_fix']
                        content = content.replace(old_url, new_url)
                        
                        self.fixes_applied.append({
                            'type': 'relative_path_fix',
                            'file': file_path,
                            'old_url': old_url,
                            'new_url': new_url
                        })
                    
                    elif issue['type'] == 'file_not_found' and 'suggested_fix' in issue:
                        # Fix missing file reference
                        old_url = issue['link']['url']
                        new_url = issue['suggested_fix']
                        content = content.replace(old_url, new_url)
                        
                        self.fixes_applied.append({
                            'type': 'missing_file_fix',
                            'file': file_path,
                            'old_url': old_url,
                            'new_url': new_url
                        })
                    
                    elif issue['type'] == 'query_string_without_php':
                        # Fix query string without .php
                        old_url = issue['link']['url']
                        new_url = issue['suggested_fix']
                        content = content.replace(old_url, new_url)
                        
                        self.fixes_applied.append({
                            'type': 'query_string_fix',
                            'file': file_path,
                            'old_url': old_url,
                            'new_url': new_url
                        })
                
                # Write back if changed
                if content != original_content:
                    with open(file_path, 'w', encoding='utf-8') as f:
                        f.write(content)
                    
                    print(f"✅ Fixed link issues in {file_path}")
                
            except Exception as e:
                print(f"Error fixing {file_path}: {e}")
    
    def fix_redirect_issues(self):
        """Fix identified redirect issues"""
        print("🔄 Fixing redirect issues...")
        
        # Group issues by file
        issues_by_file = defaultdict(list)
        for issue in self.issues_found:
            if 'redirect' in issue:
                issues_by_file[issue['redirect']['file']].append(issue)
        
        for file_path, file_issues in issues_by_file.items():
            try:
                with open(file_path, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                original_content = content
                
                for issue in file_issues:
                    if issue['type'] == 'relative_redirect' and 'suggested_fix' in issue:
                        # Fix relative redirect
                        old_url = issue['redirect']['url']
                        new_url = issue['suggested_fix']
                        content = content.replace(old_url, new_url)
                        
                        self.fixes_applied.append({
                            'type': 'relative_redirect_fix',
                            'file': file_path,
                            'old_url': old_url,
                            'new_url': new_url
                        })
                    
                    elif issue['type'] == 'missing_php_extension' and 'suggested_fix' in issue:
                        # Fix missing .php extension
                        old_url = issue['redirect']['url']
                        new_url = issue['suggested_fix']
                        content = content.replace(old_url, new_url)
                        
                        self.fixes_applied.append({
                            'type': 'php_extension_fix',
                            'file': file_path,
                            'old_url': old_url,
                            'new_url': new_url
                        })
                
                # Write back if changed
                if content != original_content:
                    with open(file_path, 'w', encoding='utf-8') as f:
                        f.write(content)
                    
                    print(f"✅ Fixed redirect issues in {file_path}")
                
            except Exception as e:
                print(f"Error fixing {file_path}: {e}")
    
    def fix_common_url_patterns(self):
        """Fix common URL pattern issues"""
        print("🔧 Fixing common URL patterns...")
        
        # Common URL pattern fixes
        pattern_fixes = [
            # Fix double slashes
            (r'([^:])//+', r'\1/', 'Double slashes'),
            # Fix spaces in URLs
            (r'\s+', '', 'Spaces in URLs'),
            # Fix inconsistent base URLs
            (r'http://localhost/sprint/sprint/', 'http://localhost/sprint/', 'Double sprint in URL'),
            (r'http://localhost/sprint/pages/pages/', 'http://localhost/sprint/pages/', 'Double pages in URL'),
        ]
        
        php_files = list(self.base_path.rglob("*.php"))
        for php_file in php_files:
            try:
                with open(php_file, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                original_content = content
                changes_made = []
                
                for pattern, replacement, description in pattern_fixes:
                    if re.search(pattern, content):
                        content = re.sub(pattern, replacement, content)
                        changes_made.append(description)
                
                if content != original_content:
                    with open(php_file, 'w', encoding='utf-8') as f:
                        f.write(content)
                    
                    self.fixes_applied.append({
                        'type': 'pattern_fix',
                        'file': str(php_file),
                        'changes': changes_made
                    })
                    
                    print(f"✅ Fixed URL patterns in {php_file}")
                
            except Exception as e:
                print(f"Error fixing patterns in {php_file}: {e}")
    
    def create_url_helper_function(self):
        """Create a URL helper function for consistent URL generation"""
        url_helper_file = self.base_path / 'core' / 'url_helper.php'
        
        url_helper_code = '''<?php
declare(strict_types=1);

/**
 * URL Helper Functions
 * Provides consistent URL generation and validation
 */

/**
 * Generate base URL
 */
function base_url(string $path = ''): string {
    $base_url = 'http://localhost/sprint';
    return rtrim($base_url, '/') . '/' . ltrim($path, '/');
}

/**
 * Generate URL for pages
 */
function page_url(string $page): string {
    return base_url('pages/' . ltrim($page, '/'));
}

/**
 * Generate API URL
 */
function api_url(string $endpoint): string {
    return base_url('api/' . ltrim($endpoint, '/'));
}

/**
 * Generate asset URL
 */
function asset_url(string $asset): string {
    return base_url('public/assets/' . ltrim($asset, '/'));
}

/**
 * Validate and normalize URL
 */
function normalize_url(string $url): string {
    // Remove double slashes
    $url = preg_replace('/([^:])\/\//', '$1/', $url);
    
    // Remove spaces
    $url = str_replace(' ', '', $url);
    
    // Ensure proper format
    if (!str_starts_with($url, 'http') && !str_starts_with($url, '/')) {
        $url = base_url($url);
    }
    
    return $url;
}

/**
 * Check if URL is valid
 */
function is_valid_url(string $url): bool {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Get current URL
 */
function current_url(): string {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    return $protocol . '://' . $host . $uri;
}

/**
 * Redirect with proper URL handling
 */
function safe_redirect(string $url, int $status_code = 302): void {
    $url = normalize_url($url);
    
    // Validate URL
    if (!is_valid_url($url)) {
        throw new InvalidArgumentException("Invalid redirect URL: {$url}");
    }
    
    header("Location: {$url}", true, $status_code);
    exit;
}
?>
'''
        
        try:
            with open(url_helper_file, 'w', encoding='utf-8') as f:
                f.write(url_helper_code)
            
            self.fixes_applied.append({
                'type': 'url_helper_created',
                'file': 'core/url_helper.php',
                'description': 'Created URL helper functions for consistent URL handling'
            })
            
            print("✅ Created URL helper functions")
            
        except Exception as e:
            print(f"Error creating URL helper: {e}")
    
    def generate_fix_report(self):
        """Generate comprehensive fix report"""
        print("📊 Generating fix report...")
        
        report = {
            'timestamp': datetime.now().isoformat(),
            'summary': {
                'total_issues_found': len(self.issues_found),
                'total_fixes_applied': len(self.fixes_applied),
                'issues_by_type': {},
                'fixes_by_type': {}
            },
            'issues_found': self.issues_found,
            'fixes_applied': self.fixes_applied,
            'recommendations': [
                'Use the new url_helper.php functions for consistent URL generation',
                'Test all fixed links and redirects',
                'Implement automated link checking in CI/CD',
                'Use absolute URLs to avoid relative path issues',
                'Regularly scan for broken links'
            ]
        }
        
        # Count issues by type
        for issue in self.issues_found:
            issue_type = issue['type']
            if issue_type not in report['summary']['issues_by_type']:
                report['summary']['issues_by_type'][issue_type] = 0
            report['summary']['issues_by_type'][issue_type] += 1
        
        # Count fixes by type
        for fix in self.fixes_applied:
            fix_type = fix['type']
            if fix_type not in report['summary']['fixes_by_type']:
                report['summary']['fixes_by_type'][fix_type] = 0
            report['summary']['fixes_by_type'][fix_type] += 1
        
        report_file = self.base_path / 'link_redirect_fix_report.json'
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, default=str)
        
        print(f"✅ Fix report saved to: {report_file}")
        return report
    
    def run_link_redirect_fixer(self):
        """Run complete link and redirect fixing process"""
        print("🚀 Starting Link and Redirect Fixer...")
        
        # Scan all links and redirects
        links, redirects = self.scan_all_links_and_redirects()
        
        print(f"Found {len(links)} links and {len(redirects)} redirects")
        
        # Validate links and redirects
        link_issues = self.validate_links(links)
        redirect_issues = self.validate_redirects(redirects)
        
        print(f"Found {len(link_issues)} link issues and {len(redirect_issues)} redirect issues")
        
        # Apply fixes
        self.fix_link_issues()
        self.fix_redirect_issues()
        self.fix_common_url_patterns()
        self.create_url_helper_function()
        
        # Generate report
        report = self.generate_fix_report()
        
        print(f"\n📊 Fix Summary:")
        print(f"Total Issues Found: {report['summary']['total_issues_found']}")
        print(f"Total Fixes Applied: {report['summary']['total_fixes_applied']}")
        print(f"Issues by Type: {report['summary']['issues_by_type']}")
        print(f"Fixes by Type: {report['summary']['fixes_by_type']}")
        
        return report

def main():
    """Main execution"""
    fixer = LinkRedirectFixer()
    report = fixer.run_link_redirect_fixer()
    
    print(f"\n🎉 Link and Redirect Fixer completed!")
    print(f"📚 Total fixes applied: {report['summary']['total_fixes_applied']}")
    print(f"🔧 URL helper functions created for future consistency")
    print(f"📊 Comprehensive report generated for reference")
    
    return report

if __name__ == "__main__":
    main()
