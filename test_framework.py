#!/usr/bin/env python3
"""
Comprehensive Testing Framework for SPRIN Application
Using Python for API testing and browser automation
"""

import requests
import mysql.connector
import json
import time
import logging
from datetime import datetime
import sys
import os

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('test_results.log'),
        logging.StreamHandler(sys.stdout)
    ]
)

class SPRINTestFramework:
    def __init__(self):
        self.base_url = "http://localhost/sprin"
        self.api_base_url = "http://localhost/sprin/api"
        self.db_config = {
            'host': 'localhost',
            'user': 'root',
            'password': 'root',
            'database': 'bagops'
        }
        self.test_results = []
        self.session = requests.Session()
        
    def setup_database_connection(self):
        """Setup database connection"""
        try:
            self.conn = mysql.connector.connect(**self.db_config)
            self.cursor = self.conn.cursor(dictionary=True)
            logging.info("Database connection established")
            return True
        except Exception as e:
            logging.error(f"Database connection failed: {e}")
            return False
    
    def create_test_data(self):
        """Create comprehensive test data in database"""
        logging.info("Creating test data...")
        
        try:
            # Test personil data
            personil_data = [
                ('198401012015031001', 'Ahmad Rizki', 'AKP', 'Komisaris Polisi', 'Bagops'),
                ('198502022015031002', 'Budi Santoso', 'IPTU', 'Inspektur Polisi Satu', 'Bagops'),
                ('198603032015031003', 'Cahaya Putra', 'IPDA', 'Inspektur Polisi Dua', 'Bagintel'),
                ('198704042015031004', 'Dedi Kurniawan', 'AKP', 'Komisaris Polisi', 'Bagreskrim'),
                ('198805052015031005', 'Eko Prasetyo', 'IPTU', 'Inspektur Polisi Satu', 'Bagsumda'),
            ]
            
            # Insert test personil
            for nrp, nama, pangkat, jabatan, bagian in personil_data:
                self.cursor.execute("""
                    INSERT IGNORE INTO personil (nrp, nama, id_pangkat, id_jabatan, id_bagian, is_active, is_deleted)
                    VALUES (%s, %s, 1, 1, 1, 1, 0)
                """, (nrp, nama))
            
            # Test operation data
            operation_data = [
                ('OPS-2026-0001', 'Operasi Kewilayahan Rutin', 'rutin', '2026-04-12 08:00:00', 'Kota Samosir', '198401012015031001'),
                ('OPS-2026-0002', 'Operasi Penegakan Hukum', 'khusus', '2026-04-13 10:00:00', 'Pekanbaru', '198502022015031002'),
                ('OPS-2026-0003', 'Operasi Kamtibmas Terpadu', 'terpadu', '2026-04-14 06:00:00', 'Medan', '198603032015031003'),
            ]
            
            # Insert test operations
            for kode, nama, jenis, tanggal, lokasi, komandan in operation_data:
                self.cursor.execute("""
                    INSERT IGNORE INTO operasi_kepolisian 
                    (kode_operasi, nama_operasi, jenis_operasi, tanggal_mulai, lokasi_operasi, komandan_ops, status, created_by)
                    VALUES (%s, %s, %s, %s, %s, %s, 'rencana', 'admin')
                """, (kode, nama, jenis, tanggal, lokasi, komandan))
            
            # Test bagops structure
            bagops_structure = [
                ('Kepala Bagian Operasional', 'AKBP', 'III.a', 'Kapolres', '{"Sub Bag Bin Ops": "Kasubbag Bin Ops"}', 'Unsur pimpinan pembantu'),
                ('Kepala Sub Bag Bin Ops', 'Kompol', 'IV.a', 'Kabag Ops', '[]', 'Pembinaan operasi'),
                ('Kepala Sub Bag Dal Ops', 'Kompol', 'IV.a', 'Kabag Ops', '[]', 'Pengendalian operasi'),
            ]
            
            # Insert bagops structure
            for jabatan, pangkat, eselon, atasan, bawahan, deskripsi in bagops_structure:
                self.cursor.execute("""
                    INSERT IGNORE INTO bagops_structure 
                    (jabatan, pangkat, eselon, atasan, bawahan, deskripsi)
                    VALUES (%s, %s, %s, %s, %s, %s)
                """, (jabatan, pangkat, eselon, atasan, bawahan, deskripsi))
            
            self.conn.commit()
            logging.info("Test data created successfully")
            return True
            
        except Exception as e:
            logging.error(f"Failed to create test data: {e}")
            return False
    
    def test_api_endpoints(self):
        """Test all API endpoints"""
        logging.info("Testing API endpoints...")
        
        # Test unified API gateway
        api_tests = [
            {
                'name': 'Get Personil List',
                'url': f'{self.api_base_url}/unified-api.php',
                'params': {'resource': 'personil', 'action': 'get_all'},
                'method': 'GET'
            },
            {
                'name': 'Get Operations List',
                'url': f'{self.api_base_url}/unified-api.php',
                'params': {'resource': 'operasional', 'action': 'get_operasi_list'},
                'method': 'GET'
            },
            {
                'name': 'Get BAGOPS Structure',
                'url': f'{self.api_base_url}/unified-api.php',
                'params': {'resource': 'bagops_structure', 'action': 'get_structure'},
                'method': 'GET'
            },
            {
                'name': 'Get Bagian List',
                'url': f'{self.api_base_url}/unified-api.php',
                'params': {'resource': 'bagian', 'action': 'get_all'},
                'method': 'GET'
            },
            {
                'name': 'Get Jabatan List',
                'url': f'{self.api_base_url}/unified-api.php',
                'params': {'resource': 'jabatan', 'action': 'get_all'},
                'method': 'GET'
            },
            {
                'name': 'Get Analytics Dashboard',
                'url': f'{self.api_base_url}/unified-api.php',
                'params': {'resource': 'analytics', 'action': 'get_dashboard'},
                'method': 'GET'
            }
        ]
        
        for test in api_tests:
            try:
                response = self.session.request(
                    test['method'], 
                    test['url'], 
                    params=test['params'],
                    timeout=10
                )
                
                result = {
                    'test_name': test['name'],
                    'status_code': response.status_code,
                    'response_time': response.elapsed.total_seconds(),
                    'success': response.status_code == 200,
                    'data': response.json() if response.headers.get('content-type', '').startswith('application/json') else None
                }
                
                self.test_results.append(result)
                
                if result['success']:
                    logging.info(f"PASS: {test['name']} - {response.status_code}")
                else:
                    logging.error(f"FAIL: {test['name']} - {response.status_code}")
                    
            except Exception as e:
                result = {
                    'test_name': test['name'],
                    'status_code': 0,
                    'response_time': 0,
                    'success': False,
                    'error': str(e)
                }
                self.test_results.append(result)
                logging.error(f"ERROR: {test['name']} - {e}")
    
    def test_database_queries(self):
        """Test database queries and data integrity"""
        logging.info("Testing database queries...")
        
        db_tests = [
            {
                'name': 'Test Personil Count',
                'query': 'SELECT COUNT(*) as count FROM personil WHERE is_deleted = 0',
                'expected_min': 1
            },
            {
                'name': 'Test Operations Count',
                'query': 'SELECT COUNT(*) as count FROM operasi_kepolisian',
                'expected_min': 1
            },
            {
                'name': 'Test BAGOPS Structure Count',
                'query': 'SELECT COUNT(*) as count FROM bagops_structure',
                'expected_min': 1
            },
            {
                'name': 'Test Personil-Operations Relation',
                'query': '''
                    SELECT p.nama, o.nama_operasi 
                    FROM personil p 
                    LEFT JOIN operasi_kepolisian o ON p.nrp = o.komandan_ops 
                    WHERE p.is_deleted = 0
                    LIMIT 5
                ''',
                'expected_min': 1
            }
        ]
        
        for test in db_tests:
            try:
                self.cursor.execute(test['query'])
                result = self.cursor.fetchall()
                
                success = len(result) >= test['expected_min']
                
                db_result = {
                    'test_name': test['name'],
                    'success': success,
                    'data_count': len(result),
                    'data': result[:3]  # First 3 rows for inspection
                }
                
                self.test_results.append(db_result)
                
                if success:
                    logging.info(f"PASS: {test['name']} - {len(result)} records")
                else:
                    logging.error(f"FAIL: {test['name']} - Expected >= {test['expected_min']}, got {len(result)}")
                    
            except Exception as e:
                db_result = {
                    'test_name': test['name'],
                    'success': False,
                    'error': str(e)
                }
                self.test_results.append(db_result)
                logging.error(f"ERROR: {test['name']} - {e}")
    
    def test_file_system(self):
        """Test file system and uploads"""
        logging.info("Testing file system...")
        
        file_tests = [
            {
                'name': 'Check Upload Directory',
                'path': '/opt/lampp/htdocs/sprin/uploads',
                'type': 'directory'
            },
            {
                'name': 'Check Documentation Directory',
                'path': '/opt/lampp/htdocs/sprin/uploads/dokumentasi',
                'type': 'directory'
            },
            {
                'name': 'Check Sprint Directory',
                'path': '/opt/lampp/htdocs/sprin/uploads/sprint',
                'type': 'directory'
            },
            {
                'name': 'Check API Directory',
                'path': '/opt/lampp/htdocs/sprin/api',
                'type': 'directory'
            },
            {
                'name': 'Check Pages Directory',
                'path': '/opt/lampp/htdocs/sprin/pages',
                'type': 'directory'
            }
        ]
        
        for test in file_tests:
            try:
                exists = os.path.exists(test['path'])
                
                file_result = {
                    'test_name': test['name'],
                    'success': exists,
                    'path': test['path'],
                    'exists': exists
                }
                
                self.test_results.append(file_result)
                
                if exists:
                    logging.info(f"PASS: {test['name']} - Exists")
                else:
                    logging.error(f"FAIL: {test['name']} - Not found")
                    
            except Exception as e:
                file_result = {
                    'test_name': test['name'],
                    'success': False,
                    'error': str(e)
                }
                self.test_results.append(file_result)
                logging.error(f"ERROR: {test['name']} - {e}")
    
    def test_php_syntax(self):
        """Test PHP file syntax"""
        logging.info("Testing PHP syntax...")
        
        php_files = [
            '/opt/lampp/htdocs/sprin/api/unified-api.php',
            '/opt/lampp/htdocs/sprin/api/operasional_api.php',
            '/opt/lampp/htdocs/sprin/api/bagops_structure_api.php',
            '/opt/lampp/htdocs/sprin/pages/operasional_management.php',
            '/opt/lampp/htdocs/sprin/pages/personil.php',
            '/opt/lampp/htdocs/sprin/pages/main.php'
        ]
        
        for php_file in php_files:
            try:
                result = os.system(f'php -l {php_file} > /dev/null 2>&1')
                success = result == 0
                
                syntax_result = {
                    'test_name': f'PHP Syntax: {os.path.basename(php_file)}',
                    'success': success,
                    'file': php_file
                }
                
                self.test_results.append(syntax_result)
                
                if success:
                    logging.info(f"PASS: PHP Syntax - {os.path.basename(php_file)}")
                else:
                    logging.error(f"FAIL: PHP Syntax - {os.path.basename(php_file)}")
                    
            except Exception as e:
                syntax_result = {
                    'test_name': f'PHP Syntax: {os.path.basename(php_file)}',
                    'success': False,
                    'error': str(e)
                }
                self.test_results.append(syntax_result)
                logging.error(f"ERROR: PHP Syntax - {os.path.basename(php_file)} - {e}")
    
    def generate_test_report(self):
        """Generate comprehensive test report"""
        logging.info("Generating test report...")
        
        total_tests = len(self.test_results)
        passed_tests = sum(1 for result in self.test_results if result['success'])
        failed_tests = total_tests - passed_tests
        
        report = f"""
# SPRIN Application Test Report
Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}

## Test Summary
- Total Tests: {total_tests}
- Passed: {passed_tests}
- Failed: {failed_tests}
- Success Rate: {(passed_tests/total_tests*100):.1f}%

## Detailed Results

"""
        
        for result in self.test_results:
            status = "PASS" if result['success'] else "FAIL"
            report += f"### {result['test_name']}\n"
            report += f"- Status: {status}\n"
            
            if 'status_code' in result:
                report += f"- Status Code: {result['status_code']}\n"
                report += f"- Response Time: {result['response_time']}s\n"
            
            if 'data_count' in result:
                report += f"- Records: {result['data_count']}\n"
            
            if 'error' in result:
                report += f"- Error: {result['error']}\n"
            
            report += "\n"
        
        # Save report
        with open('comprehensive_test_report.md', 'w') as f:
            f.write(report)
        
        logging.info(f"Test report saved to comprehensive_test_report.md")
        logging.info(f"Test Summary: {passed_tests}/{total_tests} passed ({passed_tests/total_tests*100:.1f}%)")
        
        return report
    
    def run_comprehensive_tests(self):
        """Run all comprehensive tests"""
        logging.info("Starting comprehensive testing...")
        
        # Setup database
        if not self.setup_database_connection():
            return False
        
        # Create test data
        if not self.create_test_data():
            return False
        
        # Run all tests
        self.test_api_endpoints()
        self.test_database_queries()
        self.test_file_system()
        self.test_php_syntax()
        
        # Generate report
        self.generate_test_report()
        
        # Cleanup
        self.cursor.close()
        self.conn.close()
        
        return True

if __name__ == "__main__":
    # Run comprehensive tests
    tester = SPRINTestFramework()
    success = tester.run_comprehensive_tests()
    
    if success:
        print("\nComprehensive testing completed successfully!")
        print("Check 'comprehensive_test_report.md' for detailed results.")
    else:
        print("\nTesting failed. Check logs for details.")
        sys.exit(1)
