#!/usr/bin/env python3
"""
SPRIN Application Analyzer
Comprehensive analysis tool for the SPRIN police management system
"""

import os
import re
import json
import sqlite3
import subprocess
from pathlib import Path
from collections import defaultdict, Counter
from datetime import datetime
import ast

class SPRINAnalyzer:
    def __init__(self, base_path="/opt/lampp/htdocs/sprin"):
        self.base_path = Path(base_path)
        self.analysis_results = {
            "structure": {},
            "code_quality": {},
            "security_issues": [],
            "performance_issues": [],
            "database_analysis": {},
            "api_analysis": {},
            "frontend_analysis": {},
            "testing_coverage": {},
            "dependencies": {},
            "errors": [],
            "warnings": []
        }
        
    def analyze_structure(self):
        """Analyze project structure and file organization"""
        print("🔍 Analyzing project structure...")
        
        structure = {
            "directories": [],
            "file_types": defaultdict(int),
            "total_files": 0,
            "size_analysis": {}
        }
        
        for root, dirs, files in os.walk(self.base_path):
            # Skip hidden directories and common non-source dirs
            dirs[:] = [d for d in dirs if not d.startswith('.') and d not in ['node_modules', 'vendor']]
            
            rel_path = os.path.relpath(root, self.base_path)
            if rel_path != '.':
                structure["directories"].append(rel_path)
            
            for file in files:
                if not file.startswith('.'):
                    ext = os.path.splitext(file)[1].lower()
                    structure["file_types"][ext] += 1
                    structure["total_files"] += 1
                    
                    # File size analysis
                    file_path = os.path.join(root, file)
                    try:
                        size = os.path.getsize(file_path)
                        if ext not in structure["size_analysis"]:
                            structure["size_analysis"][ext] = []
                        structure["size_analysis"][ext].append(size)
                    except OSError:
                        pass
        
        self.analysis_results["structure"] = structure
        print(f"✅ Found {structure['total_files']} files across {len(structure['directories'])} directories")
        
    def analyze_php_code(self):
        """Analyze PHP code quality and issues"""
        print("🔍 Analyzing PHP code quality...")
        
        php_files = list(self.base_path.rglob("*.php"))
        code_quality = {
            "total_php_files": len(php_files),
            "security_issues": [],
            "code_smells": [],
            "complexity_analysis": {},
            "dependency_analysis": defaultdict(int)
        }
        
        security_patterns = {
            "SQL Injection": [r"mysql_query", r"mysqli_query", r"\$_GET\['", r"\$_POST\['"],
            "XSS": [r"echo\s+\$_", r"print\s+\$_", r"htmlspecialchars.*false"],
            "File Inclusion": [r"include\s+\$", r"require\s+\$", r"include_once\s+\$"],
            "Eval Usage": [r"eval\s*\(", r"create_function\s*\("],
            "Weak Crypto": [r"md5\s*\(", r"sha1\s*\("]
        }
        
        for php_file in php_files:
            try:
                with open(php_file, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                    
                    # Security analysis
                    for issue, patterns in security_patterns.items():
                        for pattern in patterns:
                            if re.search(pattern, content, re.IGNORECASE):
                                code_quality["security_issues"].append({
                                    "file": str(php_file.relative_to(self.base_path)),
                                    "issue": issue,
                                    "pattern": pattern
                                })
                    
                    # Code smells
                    if content.count('function') > 20:
                        code_quality["code_smells"].append({
                            "file": str(php_file.relative_to(self.base_path)),
                            "issue": "Too many functions in single file",
                            "count": content.count('function')
                        })
                    
                    # Dependency analysis
                    includes = re.findall(r'(?:include|require|include_once|require_once)\s*[\'"]([^\'"]+)[\'"]', content, re.IGNORECASE)
                    for inc in includes:
                        code_quality["dependency_analysis"][inc] += 1
                        
            except Exception as e:
                code_quality["errors"] = code_quality.get("errors", [])
                code_quality["errors"].append(f"Error analyzing {php_file}: {str(e)}")
        
        self.analysis_results["code_quality"] = code_quality
        print(f"✅ Analyzed {len(php_files)} PHP files")
        
    def analyze_database(self):
        """Analyze database structure and queries"""
        print("🔍 Analyzing database structure...")
        
        db_analysis = {
            "sql_files": [],
            "table_analysis": {},
            "query_patterns": defaultdict(int),
            "index_analysis": {}
        }
        
        # Find SQL files
        sql_files = list(self.base_path.rglob("*.sql"))
        db_analysis["sql_files"] = [str(f.relative_to(self.base_path)) for f in sql_files]
        
        # Analyze PHP files for database queries
        php_files = list(self.base_path.rglob("*.php"))
        query_patterns = {
            "SELECT": r"SELECT\s+.*\s+FROM\s+(\w+)",
            "INSERT": r"INSERT\s+INTO\s+(\w+)",
            "UPDATE": r"UPDATE\s+(\w+)",
            "DELETE": r"DELETE\s+FROM\s+(\w+)"
        }
        
        for php_file in php_files:
            try:
                with open(php_file, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                    
                    for operation, pattern in query_patterns.items():
                        matches = re.findall(pattern, content, re.IGNORECASE)
                        for table in matches:
                            db_analysis["query_patterns"][f"{operation}_{table}"] += 1
                            
            except Exception as e:
                db_analysis["errors"] = db_analysis.get("errors", [])
                db_analysis["errors"].append(f"Error analyzing {php_file}: {str(e)}")
        
        self.analysis_results["database_analysis"] = db_analysis
        print(f"✅ Found {len(sql_files)} SQL files and analyzed database queries")
        
    def analyze_api(self):
        """Analyze API endpoints and structure"""
        print("🔍 Analyzing API structure...")
        
        api_dir = self.base_path / "api"
        if not api_dir.exists():
            self.analysis_results["api_analysis"] = {"error": "API directory not found"}
            return
            
        api_analysis = {
            "endpoints": [],
            "methods": Counter(),
            "security_headers": [],
            "validation_analysis": {},
            "response_formats": []
        }
        
        # Find API files
        api_files = list(api_dir.rglob("*.php"))
        
        for api_file in api_files:
            try:
                with open(api_file, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                    
                    # Find HTTP methods
                    methods = re.findall(r"\$_(GET|POST|PUT|DELETE|PATCH)", content, re.IGNORECASE)
                    for method in methods:
                        api_analysis["methods"][method.upper()] += 1
                    
                    # Check for security headers
                    if "header(" in content:
                        headers = re.findall(r'header\s*\(\s*[\'"]([^\'"]+)[\'"]', content)
                        api_analysis["security_headers"].extend(headers)
                    
                    # Check for input validation
                    if "filter_var" in content or "preg_match" in content:
                        api_analysis["validation_analysis"][str(api_file.relative_to(self.base_path))] = True
                    
                    api_analysis["endpoints"].append(str(api_file.relative_to(self.base_path)))
                    
            except Exception as e:
                api_analysis["errors"] = api_analysis.get("errors", [])
                api_analysis["errors"].append(f"Error analyzing {api_file}: {str(e)}")
        
        self.analysis_results["api_analysis"] = api_analysis
        print(f"✅ Analyzed {len(api_files)} API files")
        
    def analyze_frontend(self):
        """Analyze frontend assets and structure"""
        print("🔍 Analyzing frontend structure...")
        
        frontend_analysis = {
            "css_files": [],
            "js_files": [],
            "image_files": [],
            "framework_usage": {},
            "security_issues": []
        }
        
        # CSS files
        css_files = list(self.base_path.rglob("*.css"))
        frontend_analysis["css_files"] = [str(f.relative_to(self.base_path)) for f in css_files]
        
        # JavaScript files
        js_files = list(self.base_path.rglob("*.js"))
        frontend_analysis["js_files"] = [str(f.relative_to(self.base_path)) for f in js_files]
        
        # Images
        img_extensions = ['.png', '.jpg', '.jpeg', '.gif', '.svg', '.webp']
        for ext in img_extensions:
            img_files = list(self.base_path.rglob(f"*{ext}"))
            frontend_analysis["image_files"].extend([str(f.relative_to(self.base_path)) for f in img_files])
        
        # Analyze JS files for frameworks and security issues
        for js_file in js_files:
            try:
                with open(js_file, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                    
                    # Framework detection
                    frameworks = {
                        "jQuery": r"jQuery|\$\(",
                        "Bootstrap": r"bootstrap|\.modal\(",
                        "Chart.js": r"Chart\(",
                        "DataTables": r"DataTables|\.dataTable\("
                    }
                    
                    for framework, pattern in frameworks.items():
                        if re.search(pattern, content):
                            frontend_analysis["framework_usage"][framework] = frontend_analysis["framework_usage"].get(framework, 0) + 1
                    
                    # Security issues
                    if "eval(" in content or "innerHTML" in content:
                        frontend_analysis["security_issues"].append({
                            "file": str(js_file.relative_to(self.base_path)),
                            "issue": "Potential XSS vulnerability"
                        })
                        
            except Exception as e:
                frontend_analysis["errors"] = frontend_analysis.get("errors", [])
                frontend_analysis["errors"].append(f"Error analyzing {js_file}: {str(e)}")
        
        self.analysis_results["frontend_analysis"] = frontend_analysis
        print(f"✅ Analyzed {len(css_files)} CSS, {len(js_files)} JS files")
        
    def analyze_testing(self):
        """Analyze testing setup and coverage"""
        print("🔍 Analyzing testing setup...")
        
        testing_analysis = {
            "test_files": [],
            "test_types": defaultdict(int),
            "coverage_analysis": {},
            "test_configs": []
        }
        
        # Find test files
        test_patterns = ["*test*.php", "*test*.js", "test_*", "*_test.*"]
        test_files = []
        
        for pattern in test_patterns:
            test_files.extend(self.base_path.rglob(pattern))
        
        # Categorize tests
        for test_file in test_files:
            rel_path = str(test_file.relative_to(self.base_path))
            testing_analysis["test_files"].append(rel_path)
            
            if "phpunit" in rel_path.lower():
                testing_analysis["test_types"]["PHPUnit"] += 1
            elif "jest" in rel_path.lower():
                testing_analysis["test_types"]["Jest"] += 1
            elif "playwright" in rel_path.lower():
                testing_analysis["test_types"]["Playwright"] += 1
            else:
                testing_analysis["test_types"]["Other"] += 1
        
        # Find test configuration files
        config_files = ["phpunit.xml", "jest.config.js", "playwright.config.js"]
        for config in config_files:
            if (self.base_path / config).exists():
                testing_analysis["test_configs"].append(config)
        
        self.analysis_results["testing_coverage"] = testing_analysis
        print(f"✅ Found {len(test_files)} test files")
        
    def check_dependencies(self):
        """Check project dependencies and package management"""
        print("🔍 Analyzing dependencies...")
        
        dependency_analysis = {
            "package_managers": {},
            "dependencies": {},
            "security_issues": []
        }
        
        # Check for package.json
        package_json = self.base_path / "package.json"
        if package_json.exists():
            try:
                with open(package_json, 'r') as f:
                    package_data = json.load(f)
                    dependency_analysis["package_managers"]["npm"] = package_data
                    dependency_analysis["dependencies"]["npm"] = {
                        "dependencies": package_data.get("dependencies", {}),
                        "devDependencies": package_data.get("devDependencies", {})
                    }
            except Exception as e:
                dependency_analysis["security_issues"].append(f"Error reading package.json: {str(e)}")
        
        # Check for composer.json
        composer_json = self.base_path / "composer.json"
        if composer_json.exists():
            try:
                with open(composer_json, 'r') as f:
                    composer_data = json.load(f)
                    dependency_analysis["package_managers"]["composer"] = composer_data
                    dependency_analysis["dependencies"]["composer"] = {
                        "require": composer_data.get("require", {}),
                        "require-dev": composer_data.get("require-dev", {})
                    }
            except Exception as e:
                dependency_analysis["security_issues"].append(f"Error reading composer.json: {str(e)}")
        
        self.analysis_results["dependencies"] = dependency_analysis
        print("✅ Dependency analysis complete")
        
    def run_comprehensive_analysis(self):
        """Run all analysis modules"""
        print("🚀 Starting comprehensive SPRIN application analysis...")
        start_time = datetime.now()
        
        try:
            self.analyze_structure()
            self.analyze_php_code()
            self.analyze_database()
            self.analyze_api()
            self.analyze_frontend()
            self.analyze_testing()
            self.check_dependencies()
            
            duration = datetime.now() - start_time
            print(f"✅ Analysis completed in {duration.total_seconds():.2f} seconds")
            
        except Exception as e:
            print(f"❌ Analysis failed: {str(e)}")
            self.analysis_results["errors"].append(f"Analysis failed: {str(e)}")
        
    def generate_report(self):
        """Generate comprehensive analysis report"""
        print("📊 Generating analysis report...")
        
        report = {
            "metadata": {
                "analysis_date": datetime.now().isoformat(),
                "analyzer": "SPRIN Analyzer v1.0",
                "base_path": str(self.base_path)
            },
            "summary": {
                "total_files": self.analysis_results["structure"].get("total_files", 0),
                "security_issues": len(self.analysis_results["code_quality"].get("security_issues", [])),
                "code_smells": len(self.analysis_results["code_quality"].get("code_smells", [])),
                "test_files": len(self.analysis_results["testing_coverage"].get("test_files", [])),
                "api_endpoints": len(self.analysis_results["api_analysis"].get("endpoints", []))
            },
            "detailed_analysis": self.analysis_results
        }
        
        # Save report
        report_file = self.base_path / "analysis_report.json"
        try:
            with open(report_file, 'w') as f:
                json.dump(report, f, indent=2, default=str)
            print(f"📄 Report saved to {report_file}")
        except Exception as e:
            print(f"❌ Failed to save report: {str(e)}")
        
        return report

if __name__ == "__main__":
    analyzer = SPRINAnalyzer()
    analyzer.run_comprehensive_analysis()
    report = analyzer.generate_report()
    
    # Print summary
    print("\n" + "="*50)
    print("ANALYSIS SUMMARY")
    print("="*50)
    print(f"Total Files: {report['summary']['total_files']}")
    print(f"Security Issues: {report['summary']['security_issues']}")
    print(f"Code Smells: {report['summary']['code_smells']}")
    print(f"Test Files: {report['summary']['test_files']}")
    print(f"API Endpoints: {report['summary']['api_endpoints']}")
    print("="*50)
