#!/usr/bin/env python3
"""
Comprehensive SPRIN Application Analyzer
Complete analysis for AI understanding and .windsurf configuration
"""

import os
import re
import json
import subprocess
from pathlib import Path
from typing import Dict, List, Tuple, Any
from collections import defaultdict, Counter

class ComprehensiveApplicationAnalyzer:
    def __init__(self, base_path: str = "/opt/lampp/htdocs/sprint"):
        self.base_path = Path(base_path)
        self.analysis_results = {}
        self.application_structure = {}
        self.code_patterns = {}
        self.dependencies = {}
        self.api_endpoints = {}
        self.database_structure = {}
        self.security_features = {}
        self.user_workflows = {}
        
    def analyze_application_structure(self):
        """Analyze complete application structure"""
        print("🔍 Analyzing Application Structure...")
        
        structure = {
            'directories': {},
            'file_types': Counter(),
            'php_files': [],
            'javascript_files': [],
            'css_files': [],
            'config_files': [],
            'api_files': [],
            'page_files': [],
            'core_files': []
        }
        
        # Walk through entire application
        for root, dirs, files in os.walk(self.base_path):
            root_path = Path(root)
            relative_path = root_path.relative_to(self.base_path)
            
            # Analyze directories
            if relative_path != Path('.'):
                structure['directories'][str(relative_path)] = {
                    'file_count': len(files),
                    'subdirectories': dirs,
                    'files': files
                }
            
            # Analyze files
            for file in files:
                file_path = root_path / file
                relative_file_path = file_path.relative_to(self.base_path)
                file_ext = file_path.suffix.lower()
                
                structure['file_types'][file_ext] += 1
                
                # Categorize files
                if file_ext == '.php':
                    structure['php_files'].append(str(relative_file_path))
                    
                    # Further categorize PHP files
                    if 'api/' in str(relative_file_path):
                        structure['api_files'].append(str(relative_file_path))
                    elif 'pages/' in str(relative_file_path):
                        structure['page_files'].append(str(relative_file_path))
                    elif 'core/' in str(relative_file_path):
                        structure['core_files'].append(str(relative_file_path))
                    elif 'config' in str(relative_file_path).lower():
                        structure['config_files'].append(str(relative_file_path))
                        
                elif file_ext == '.js':
                    structure['javascript_files'].append(str(relative_file_path))
                elif file_ext == '.css':
                    structure['css_files'].append(str(relative_file_path))
        
        self.application_structure = structure
        return structure
    
    def analyze_code_patterns(self):
        """Analyze code patterns and architecture"""
        print("🔍 Analyzing Code Patterns...")
        
        patterns = {
            'php_patterns': {
                'classes': [],
                'functions': [],
                'database_queries': [],
                'api_endpoints': [],
                'authentication_checks': [],
                'error_handling': [],
                'session_management': []
            },
            'javascript_patterns': {
                'functions': [],
                'ajax_calls': [],
                'event_handlers': [],
                'libraries': []
            },
            'css_patterns': {
                'responsive_rules': [],
                'animations': [],
                'component_styles': []
            }
        }
        
        # Analyze PHP files
        php_files = self.application_structure.get('php_files', [])
        for php_file in php_files[:50]:  # Limit to 50 files for performance
            file_path = self.base_path / php_file
            try:
                with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                # Find classes
                classes = re.findall(r'class\s+(\w+)', content, re.IGNORECASE)
                patterns['php_patterns']['classes'].extend(classes)
                
                # Find functions
                functions = re.findall(r'function\s+(\w+)\s*\(', content, re.IGNORECASE)
                patterns['php_patterns']['functions'].extend(functions)
                
                # Find database queries
                queries = re.findall(r'SELECT|INSERT|UPDATE|DELETE|FROM|WHERE', content, re.IGNORECASE)
                patterns['php_patterns']['database_queries'].extend(queries)
                
                # Find API endpoints
                endpoints = re.findall(r'/api/(\w+)', content)
                patterns['php_patterns']['api_endpoints'].extend(endpoints)
                
                # Find authentication checks
                auth_checks = re.findall(r'AuthHelper::|SessionManager::|$_SESSION\[', content)
                patterns['php_patterns']['authentication_checks'].extend(auth_checks)
                
                # Find error handling
                error_handling = re.findall(r'try\s*\{|catch\s*\(|throw\s+new', content)
                patterns['php_patterns']['error_handling'].extend(error_handling)
                
                # Find session management
                session_mgmt = re.findall(r'session_start|session_destroy|$_SESSION', content)
                patterns['php_patterns']['session_management'].extend(session_mgmt)
                
            except Exception as e:
                print(f"Error analyzing {php_file}: {e}")
        
        # Analyze JavaScript files
        js_files = self.application_structure.get('javascript_files', [])
        for js_file in js_files[:20]:  # Limit for performance
            file_path = self.base_path / js_file
            try:
                with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                # Find functions
                functions = re.findall(r'function\s+(\w+)|const\s+(\w+)\s*=|\s*(\w+)\s*\(', content)
                patterns['javascript_patterns']['functions'].extend([f[0] or f[1] or f[2] for f in functions if f[0] or f[1] or f[2]])
                
                # Find AJAX calls
                ajax_calls = re.findall(r'\$\.ajax|fetch\s*\(|XMLHttpRequest', content)
                patterns['javascript_patterns']['ajax_calls'].extend(ajax_calls)
                
                # Find event handlers
                event_handlers = re.findall(r'addEventListener|onclick|onchange|onsubmit', content)
                patterns['javascript_patterns']['event_handlers'].extend(event_handlers)
                
                # Find libraries
                libraries = re.findall(r'require\s*\(|import\s+|from\s+', content)
                patterns['javascript_patterns']['libraries'].extend(libraries)
                
            except Exception as e:
                print(f"Error analyzing {js_file}: {e}")
        
        self.code_patterns = patterns
        return patterns
    
    def analyze_dependencies(self):
        """Analyze application dependencies"""
        print("🔍 Analyzing Dependencies...")
        
        dependencies = {
            'php_libraries': [],
            'javascript_libraries': [],
            'css_frameworks': [],
            'external_apis': [],
            'database_dependencies': []
        }
        
        # Check composer.json if exists
        composer_file = self.base_path / 'composer.json'
        if composer_file.exists():
            try:
                with open(composer_file, 'r', encoding='utf-8') as f:
                    composer_data = json.load(f)
                    if 'require' in composer_data:
                        dependencies['php_libraries'] = list(composer_data['require'].keys())
            except Exception as e:
                print(f"Error reading composer.json: {e}")
        
        # Analyze PHP files for require/include statements
        php_files = self.application_structure.get('php_files', [])
        for php_file in php_files[:30]:
            file_path = self.base_path / php_file
            try:
                with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                # Find require/include statements
                requires = re.findall(r'require_once\s*[\'"]([^\'"]+)[\'"]', content)
                includes = re.findall(r'include_once\s*[\'"]([^\'"]+)[\'"]', content)
                
                dependencies['php_libraries'].extend(requires)
                dependencies['php_libraries'].extend(includes)
                
            except Exception as e:
                print(f"Error analyzing dependencies in {php_file}: {e}")
        
        # Analyze HTML/PHP files for JavaScript libraries
        for php_file in php_files[:20]:
            file_path = self.base_path / php_file
            try:
                with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                # Find JavaScript libraries
                js_libs = re.findall(r'<script[^>]*src=[\'"]([^\'"]+)[\'"]', content)
                dependencies['javascript_libraries'].extend(js_libs)
                
                # Find CSS frameworks
                css_libs = re.findall(r'<link[^>]*href=[\'"]([^\'"]+)[\'"]', content)
                dependencies['css_frameworks'].extend(css_libs)
                
            except Exception as e:
                print(f"Error analyzing JS/CSS dependencies in {php_file}: {e}")
        
        self.dependencies = dependencies
        return dependencies
    
    def analyze_api_endpoints(self):
        """Analyze API endpoints and functionality"""
        print("🔍 Analyzing API Endpoints...")
        
        endpoints = {
            'rest_apis': {},
            'crud_operations': {},
            'authentication_endpoints': {},
            'data_endpoints': {}
        }
        
        # Analyze API files
        api_files = self.application_structure.get('api_files', [])
        for api_file in api_files:
            file_path = self.base_path / api_file
            try:
                with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                api_info = {
                    'file': api_file,
                    'methods': [],
                    'endpoints': [],
                    'authentication': False,
                    'database_operations': []
                }
                
                # Find HTTP methods
                methods = re.findall(r'\$_(GET|POST|PUT|DELETE|REQUEST)', content)
                api_info['methods'] = list(set(methods))
                
                # Find endpoints
                endpoint_patterns = re.findall(r'/(\w+)', content)
                api_info['endpoints'] = list(set(endpoint_patterns))
                
                # Check for authentication
                if 'AuthHelper::' in content or 'SessionManager::' in content:
                    api_info['authentication'] = True
                
                # Find database operations
                db_ops = re.findall(r'SELECT|INSERT|UPDATE|DELETE', content, re.IGNORECASE)
                api_info['database_operations'] = list(set(db_ops))
                
                # Categorize API
                if 'auth' in api_file.lower() or 'login' in api_file.lower():
                    endpoints['authentication_endpoints'][api_file] = api_info
                elif any(op in db_ops for op in ['SELECT', 'INSERT', 'UPDATE', 'DELETE']):
                    endpoints['data_endpoints'][api_file] = api_info
                    endpoints['crud_operations'][api_file] = api_info
                else:
                    endpoints['rest_apis'][api_file] = api_info
                
            except Exception as e:
                print(f"Error analyzing API file {api_file}: {e}")
        
        self.api_endpoints = endpoints
        return endpoints
    
    def analyze_security_features(self):
        """Analyze security features"""
        print("🔍 Analyzing Security Features...")
        
        security = {
            'authentication': {
                'methods': [],
                'session_management': False,
                'password_hashing': False,
                'csrf_protection': False
            },
            'input_validation': {
                'sanitization': False,
                'validation_functions': [],
                'sql_injection_protection': False
            },
            'access_control': {
                'role_based_access': False,
                'permission_checks': [],
                'secure_headers': False
            },
            'data_protection': {
                'encryption': False,
                'secure_connections': False,
                'data_sanitization': False
            }
        }
        
        # Analyze PHP files for security features
        php_files = self.application_structure.get('php_files', [])
        for php_file in php_files[:30]:
            file_path = self.base_path / php_file
            try:
                with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                # Authentication methods
                auth_methods = re.findall(r'password_verify|hash\(|AuthHelper::|SessionManager::', content)
                security['authentication']['methods'].extend(auth_methods)
                
                # Session management
                if 'session_start' in content or 'SessionManager::' in content:
                    security['authentication']['session_management'] = True
                
                # Password hashing
                if 'password_hash' in content or 'password_verify' in content:
                    security['authentication']['password_hashing'] = True
                
                # CSRF protection
                if 'csrf' in content.lower() or 'token' in content.lower():
                    security['authentication']['csrf_protection'] = True
                
                # Input validation
                if 'filter_input' in content or 'htmlspecialchars' in content:
                    security['input_validation']['sanitization'] = True
                
                # SQL injection protection
                if 'PDO' in content or 'prepared statements' in content.lower() or 'bindParam' in content:
                    security['input_validation']['sql_injection_protection'] = True
                
                # Permission checks
                permissions = re.findall(r'if\s*\([^)]*permission[^)]*\)', content, re.IGNORECASE)
                security['access_control']['permission_checks'].extend(permissions)
                
                # Secure headers
                if 'X-Frame-Options' in content or 'CORS' in content or 'Content-Security-Policy' in content:
                    security['access_control']['secure_headers'] = True
                
                # Encryption
                if 'openssl_encrypt' in content or 'password_hash' in content:
                    security['data_protection']['encryption'] = True
                
                # HTTPS/secure connections
                if 'https' in content or 'SSL' in content:
                    security['data_protection']['secure_connections'] = True
                
            except Exception as e:
                print(f"Error analyzing security in {php_file}: {e}")
        
        self.security_features = security
        return security
    
    def analyze_user_workflows(self):
        """Analyze user workflows and application flow"""
        print("🔍 Analyzing User Workflows...")
        
        workflows = {
            'authentication_flow': {
                'login_page': 'login.php',
                'authentication_methods': [],
                'session_management': False,
                'redirect_after_login': 'pages/main.php'
            },
            'main_workflows': {
                'dashboard': 'pages/main.php',
                'personnel_management': 'pages/personil.php',
                'unit_management': 'pages/bagian.php',
                'element_management': 'pages/unsur.php',
                'calendar': 'pages/calendar_dashboard.php'
            },
            'api_workflows': {
                'personnel_crud': 'api/personil.php',
                'unit_crud': 'api/bagian.php',
                'element_crud': 'api/unsur.php',
                'authentication': 'api/auth_helper.php'
            },
            'data_flow': {
                'user_input': [],
                'processing': [],
                'storage': [],
                'output': []
            }
        }
        
        # Analyze authentication flow
        login_file = self.base_path / 'login.php'
        if login_file.exists():
            try:
                with open(login_file, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                auth_methods = re.findall(r'password_verify|hash\(|AuthHelper::', content)
                workflows['authentication_flow']['authentication_methods'] = list(set(auth_methods))
                
                if 'SessionManager::' in content:
                    workflows['authentication_flow']['session_management'] = True
                
            except Exception as e:
                print(f"Error analyzing login flow: {e}")
        
        # Analyze main workflows
        for workflow_name, workflow_file in workflows['main_workflows'].items():
            file_path = self.base_path / workflow_file
            if file_path.exists():
                try:
                    with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                        content = f.read()
                    
                    workflow_info = {
                        'authentication_required': 'AuthHelper::validateSession()' in content,
                        'database_operations': 'PDO' in content or 'new PDO' in content,
                        'api_calls': 'api/' in content,
                        'user_interface': 'bootstrap' in content.lower() or 'css' in content.lower()
                    }
                    
                    workflows['main_workflows'][workflow_name] = workflow_info
                    
                except Exception as e:
                    print(f"Error analyzing workflow {workflow_name}: {e}")
        
        self.user_workflows = workflows
        return workflows
    
    def generate_comprehensive_report(self):
        """Generate comprehensive analysis report"""
        print("📊 Generating Comprehensive Analysis Report...")
        
        report = {
            'application_overview': {
                'name': 'SPRIN - Sistem Manajemen POLRES Samosir',
                'type': 'Web Application',
                'technology_stack': {
                    'backend': 'PHP 8.2',
                    'database': 'MySQL',
                    'frontend': 'HTML5, CSS3, JavaScript',
                    'frameworks': 'Bootstrap 5',
                    'server': 'Apache (XAMPP)'
                },
                'purpose': 'Police personnel and organizational management system',
                'architecture': 'MVC-like with API endpoints',
                'development_status': 'Active development with comprehensive error reporting'
            },
            'application_structure': self.application_structure,
            'code_patterns': self.code_patterns,
            'dependencies': self.dependencies,
            'api_endpoints': self.api_endpoints,
            'security_features': self.security_features,
            'user_workflows': self.user_workflows,
            'ai_assistance_guidelines': {
                'primary_objectives': [
                    'Maintain code quality and standards',
                    'Ensure security best practices',
                    'Optimize performance',
                    'Enhance user experience',
                    'Debug and resolve issues efficiently'
                ],
                'key_files_to_understand': [
                    'core/config.php - Main configuration',
                    'core/SessionManager.php - Session handling',
                    'core/auth_helper.php - Authentication',
                    'pages/main.php - Main dashboard',
                    'api/personil.php - Personnel API',
                    'login.php - Login system'
                ],
                'development_patterns': [
                    'Use AuthHelper::validateSession() for authentication',
                    'Use SessionManager for session operations',
                    'Use PDO with prepared statements for database',
                    'Follow the established error handling patterns',
                    'Maintain the existing API structure'
                ],
                'common_tasks': [
                    'Debug PHP syntax and logic errors',
                    'Optimize database queries',
                    'Enhance responsive design',
                    'Implement new features following existing patterns',
                    'Fix security vulnerabilities'
                ],
                'testing_approaches': [
                    'Use Puppeteer for end-to-end testing',
                    'Test API endpoints with curl',
                    'Verify responsive design on multiple viewports',
                    'Test authentication flows',
                    'Validate database operations'
                ]
            }
        }
        
        return report
    
    def update_windsurf_configuration(self):
        """Update .windsurf configuration for AI understanding"""
        print("🔄 Updating .windsurf Configuration...")
        
        # Update settings.json
        settings_file = self.base_path / '.windsurf' / 'settings.json'
        try:
            with open(settings_file, 'r', encoding='utf-8') as f:
                settings = json.load(f)
            
            # Add AI-specific settings
            settings['ai_assistance'] = {
                'application_context': {
                    'name': 'SPRIN - Police Management System',
                    'primary_language': 'PHP',
                    'framework': 'Custom MVC-like with Bootstrap',
                    'database': 'MySQL with PDO',
                    'authentication': 'Session-based with AuthHelper',
                    'api_style': 'RESTful with JSON responses'
                },
                'development_focus': [
                    'Code quality and standards',
                    'Security best practices',
                    'Performance optimization',
                    'User experience enhancement',
                    'Error debugging and resolution'
                ],
                'key_concepts': [
                    'SessionManager for session handling',
                    'AuthHelper for authentication',
                    'PDO for database operations',
                    'Bootstrap for responsive design',
                    'API endpoints for data operations'
                ],
                'forbidden_patterns': [
                    'Hardcoded database credentials',
                    'Direct SQL without prepared statements',
                    'Missing authentication checks',
                    'Unvalidated user input',
                    'Error information exposure in production'
                ]
            }
            
            with open(settings_file, 'w', encoding='utf-8') as f:
                json.dump(settings, f, indent=2)
                
            print("✅ Updated settings.json with AI assistance context")
            
        except Exception as e:
            print(f"Error updating settings.json: {e}")
        
        # Update tasks.json
        tasks_file = self.base_path / '.windsurf' / 'tasks.json'
        try:
            with open(tasks_file, 'r', encoding='utf-8') as f:
                tasks = json.load(f)
            
            # Add application-specific tasks
            tasks['application_tasks'] = {
                'maintenance': [
                    'Monitor and optimize database performance',
                    'Update security patches and dependencies',
                    'Backup and restore data integrity',
                    'Log analysis and error monitoring'
                ],
                'development': [
                    'Implement new features following existing patterns',
                    'Debug and fix reported issues',
                    'Optimize application performance',
                    'Enhance user interface and experience',
                    'Test and validate functionality'
                ],
                'security': [
                    'Review and patch security vulnerabilities',
                    'Update authentication mechanisms',
                    'Validate input sanitization',
                    'Audit access control systems'
                ],
                'api_management': [
                    'Maintain API endpoint consistency',
                    'Optimize API response times',
                    'Document API interfaces',
                    'Test API functionality'
                ]
            }
            
            with open(tasks_file, 'w', encoding='utf-8') as f:
                json.dump(tasks, f, indent=2)
                
            print("✅ Updated tasks.json with application-specific tasks")
            
        except Exception as e:
            print(f"Error updating tasks.json: {e}")
        
        # Create new AI guidance file
        ai_guidance_file = self.base_path / '.windsurf' / 'ai_guidance.json'
        guidance = {
            'application_purpose': 'SPRIN is a comprehensive police personnel and organizational management system for POLRES Samosir',
            'core_functionality': {
                'personnel_management': 'Manage police personnel data, assignments, and career progression',
                'organizational_structure': 'Manage police units, departments, and hierarchical structure',
                'calendar_system': 'Schedule and manage police duties, events, and activities',
                'reporting': 'Generate reports and analytics for management decisions'
            },
            'technical_architecture': {
                'authentication': 'Session-based authentication using AuthHelper class',
                'database': 'MySQL database accessed via PDO with prepared statements',
                'api': 'RESTful API endpoints returning JSON responses',
                'frontend': 'Bootstrap 5 responsive design with custom CSS',
                'error_handling': 'Comprehensive error reporting system with development/production modes'
            },
            'development_guidelines': {
                'code_standards': 'Follow PSR-12 coding standards, use strict typing, implement proper error handling',
                'security': 'Always validate user input, use prepared statements, implement proper authentication checks',
                'performance': 'Optimize database queries, implement caching where appropriate, minimize API calls',
                'testing': 'Use Puppeteer for E2E testing, test API endpoints, validate responsive design'
            },
            'critical_files': {
                'core/config.php': 'Main application configuration and constants',
                'core/SessionManager.php': 'Session management and security',
                'core/auth_helper.php': 'Authentication validation and user management',
                'pages/main.php': 'Main dashboard and navigation',
                'api/personil.php': 'Personnel data CRUD operations',
                'login.php': 'User authentication interface'
            },
            'common_issues': [
                'PHP syntax errors with declare(strict_types=1)',
                'Database connection issues',
                'Session management problems',
                'API endpoint routing',
                'Responsive design breakpoints'
            ],
            'ai_assistance_focus': [
                'Debug PHP syntax and runtime errors',
                'Optimize database queries and connections',
                'Enhance responsive design and user experience',
                'Implement security best practices',
                'Maintain code quality and standards'
            ]
        }
        
        with open(ai_guidance_file, 'w', encoding='utf-8') as f:
            json.dump(guidance, f, indent=2)
            
        print("✅ Created AI guidance configuration")
    
    def run_comprehensive_analysis(self):
        """Run complete analysis and update .windsurf"""
        print("🚀 Starting Comprehensive Application Analysis...")
        
        # Run all analysis steps
        self.analyze_application_structure()
        self.analyze_code_patterns()
        self.analyze_dependencies()
        self.analyze_api_endpoints()
        self.analyze_security_features()
        self.analyze_user_workflows()
        
        # Generate comprehensive report
        report = self.generate_comprehensive_report()
        
        # Save analysis report
        report_file = self.base_path / 'comprehensive_analysis_report.json'
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, default=str)
        
        # Update .windsurf configuration
        self.update_windsurf_configuration()
        
        # Print summary
        print(f"\n📊 Comprehensive Analysis Summary:")
        print(f"Application: {report['application_overview']['name']}")
        print(f"Technology Stack: {', '.join(report['application_overview']['technology_stack'].values())}")
        print(f"PHP Files: {len(self.application_structure.get('php_files', []))}")
        print(f"API Endpoints: {len(self.api_endpoints.get('rest_apis', {}))}")
        print(f"Security Features: {len([k for k, v in self.security_features.items() if any(v.values())])}")
        print(f"Report saved to: {report_file}")
        print(f".windsurf configuration updated")
        
        return report

def main():
    """Main execution"""
    analyzer = ComprehensiveApplicationAnalyzer()
    report = analyzer.run_comprehensive_analysis()
    
    print(f"\n🎉 Comprehensive analysis completed!")
    print(f"📚 Application structure and patterns analyzed")
    print(f"🤖 .windsurf configuration updated for AI understanding")
    print(f"📊 Detailed report saved for future reference")
    
    return report

if __name__ == "__main__":
    main()
