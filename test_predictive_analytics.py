#!/usr/bin/env python3
"""
Comprehensive Testing for Predictive Analytics Features
Tests all predictive analytics APIs and ML models
"""

import requests
import mysql.connector
import json
import time
from datetime import datetime

class PredictiveAnalyticsTester:
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
            print("Database connection established")
            return True
        except Exception as e:
            print(f"Database connection failed: {e}")
            return False
    
    def test_predictive_analytics_apis(self):
        """Test all predictive analytics API endpoints"""
        print("\n=== Testing Predictive Analytics APIs ===")
        
        api_tests = [
            {
                'name': 'Staffing Demand Prediction',
                'url': f'{self.api_base_url}/unified-api.php',
                'params': {'resource': 'predictive_analytics', 'action': 'staffing_demand_prediction', 'days_ahead': 7},
                'method': 'GET'
            },
            {
                'name': 'Fatigue Risk Analysis',
                'url': f'{self.api_base_url}/unified-api.php',
                'params': {'resource': 'predictive_analytics', 'action': 'fatigue_risk_analysis', 'days_ahead': 14},
                'method': 'GET'
            },
            {
                'name': 'Absence Pattern Prediction',
                'url': f'{self.api_base_url}/unified-api.php',
                'params': {'resource': 'predictive_analytics', 'action': 'absence_pattern_prediction', 'period': 30},
                'method': 'GET'
            },
            {
                'name': 'Operational Success Probability',
                'url': f'{self.api_base_url}/unified-api.php',
                'params': {'resource': 'predictive_analytics', 'action': 'operational_success_probability', 'personnel_count': 15, 'operation_type': 'khusus'},
                'method': 'GET'
            },
            {
                'name': 'Resource Allocation Forecast',
                'url': f'{self.api_base_url}/unified-api.php',
                'params': {'resource': 'predictive_analytics', 'action': 'resource_allocation_forecast', 'period': 30},
                'method': 'GET'
            },
            {
                'name': 'Predictive Dashboard',
                'url': f'{self.api_base_url}/unified-api.php',
                'params': {'resource': 'predictive_analytics', 'action': 'predictive_dashboard'},
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
                    print(f"PASS: {test['name']} - {response.status_code}")
                    # Validate response structure
                    self.validate_api_response(test['name'], result['data'])
                else:
                    print(f"FAIL: {test['name']} - {response.status_code}")
                    
            except Exception as e:
                result = {
                    'test_name': test['name'],
                    'status_code': 0,
                    'response_time': 0,
                    'success': False,
                    'error': str(e)
                }
                self.test_results.append(result)
                print(f"ERROR: {test['name']} - {e}")
    
    def validate_api_response(self, test_name, data):
        """Validate API response structure"""
        if not data or not data.get('success'):
            return
        
        validations = {
            'Staffing Demand Prediction': ['predictions', 'historical_data_points', 'confidence_level'],
            'Fatigue Risk Analysis': ['fatigue_risks', 'risk_summary'],
            'Absence Pattern Prediction': ['predictions', 'high_risk_days'],
            'Operational Success Probability': ['success_probability', 'confidence'],
            'Resource Allocation Forecast': ['forecast', 'overall_efficiency'],
            'Predictive Dashboard': ['staffing_predictions', 'fatigue_alerts', 'key_insights']
        }
        
        if test_name in validations:
            required_fields = validations[test_name]
            missing_fields = []
            
            for field in required_fields:
                if field not in data.get('data', {}):
                    missing_fields.append(field)
            
            if missing_fields:
                print(f"  WARNING: Missing fields in {test_name}: {missing_fields}")
            else:
                print(f"  OK: Response structure valid")
    
    def test_ml_models(self):
        """Test machine learning models"""
        print("\n=== Testing Machine Learning Models ===")
        
        try:
            # Import and test ML models
            import sys
            sys.path.append('/opt/lampp/htdocs/sprin')
            from machine_learning_models import SPRINPredictiveModels
            
            sprin_ml = SPRINPredictiveModels(self.db_config)
            
            # Test model training
            print("Testing model training...")
            training_results = sprin_ml.train_all_models()
            
            if 'error' in training_results:
                print(f"FAIL: Model training - {training_results['error']}")
            else:
                print("PASS: Model training completed")
                
                # Test predictions
                print("Testing staffing demand prediction...")
                staffing_pred = sprin_ml.predict_staffing_demand(days_ahead=7)
                if 'error' in staffing_pred:
                    print(f"FAIL: Staffing prediction - {staffing_pred['error']}")
                else:
                    print(f"PASS: Staffing prediction - {len(staffing_pred['predictions'])} days")
                
                print("Testing success probability prediction...")
                success_pred = sprin_ml.predict_success_probability({
                    'personnel_count': 15,
                    'operation_type': 'khusus'
                })
                if 'error' in success_pred:
                    print(f"FAIL: Success probability - {success_pred['error']}")
                else:
                    print(f"PASS: Success probability - {success_pred['success_probability']}%")
                
                # Test fatigue risk prediction
                print("Testing fatigue risk prediction...")
                fatigue_pred = sprin_ml.predict_fatigue_risk([
                    {'nrp': '198401012015031001', 'nama': 'Test Person', 'scheduled_shifts': 5, 'night_shifts': 2}
                ])
                if 'error' in fatigue_pred:
                    print(f"FAIL: Fatigue risk - {fatigue_pred['error']}")
                else:
                    print(f"PASS: Fatigue risk prediction - {len(fatigue_pred['predictions'])} personnel")
            
            result = {
                'test_name': 'Machine Learning Models',
                'success': 'error' not in training_results,
                'data': training_results
            }
            self.test_results.append(result)
            
        except Exception as e:
            result = {
                'test_name': 'Machine Learning Models',
                'success': False,
                'error': str(e)
            }
            self.test_results.append(result)
            print(f"ERROR: ML Models - {e}")
    
    def test_database_integration(self):
        """Test database integration for predictive features"""
        print("\n=== Testing Database Integration ===")
        
        db_tests = [
            {
                'name': 'Test Operations Data Availability',
                'query': 'SELECT COUNT(*) as count FROM operasi_kepolisian WHERE tanggal_mulai >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)',
                'expected_min': 1
            },
            {
                'name': 'Test Personnel Data Availability',
                'query': 'SELECT COUNT(*) as count FROM personil WHERE is_active = 1 AND is_deleted = 0',
                'expected_min': 1
            },
            {
                'name': 'Test Schedule Data Availability',
                'query': 'SELECT COUNT(*) as count FROM schedules WHERE shift_date >= CURDATE()',
                'expected_min': 0  # Can be 0 for testing
            },
            {
                'name': 'Test Equipment Data Availability',
                'query': 'SELECT COUNT(*) as count FROM equipment',
                'expected_min': 1
            }
        ]
        
        for test in db_tests:
            try:
                self.cursor.execute(test['query'])
                result = self.cursor.fetchone()
                
                success = result['count'] >= test['expected_min']
                
                db_result = {
                    'test_name': test['name'],
                    'success': success,
                    'data_count': result['count'],
                    'expected_min': test['expected_min']
                }
                
                self.test_results.append(db_result)
                
                if success:
                    print(f"PASS: {test['name']} - {result['count']} records")
                else:
                    print(f"FAIL: {test['name']} - Expected >= {test['expected_min']}, got {result['count']}")
                    
            except Exception as e:
                db_result = {
                    'test_name': test['name'],
                    'success': False,
                    'error': str(e)
                }
                self.test_results.append(db_result)
                print(f"ERROR: {test['name']} - {e}")
    
    def test_predictive_dashboard_page(self):
        """Test predictive analytics dashboard page"""
        print("\n=== Testing Predictive Dashboard Page ===")
        
        try:
            response = self.session.get(f"{self.base_url}/pages/predictive_analytics_dashboard.php", timeout=10)
            
            result = {
                'test_name': 'Predictive Dashboard Page',
                'status_code': response.status_code,
                'response_time': response.elapsed.total_seconds(),
                'success': response.status_code == 200,
                'content_length': len(response.content)
            }
            
            self.test_results.append(result)
            
            if result['success']:
                print(f"PASS: Dashboard page - {response.status_code}")
                
                # Check for key elements
                content = response.text.lower()
                elements = ['predictive analytics', 'staffing demand', 'fatigue risk', 'success probability']
                missing_elements = []
                
                for element in elements:
                    if element not in content:
                        missing_elements.append(element)
                
                if missing_elements:
                    print(f"  WARNING: Missing elements: {missing_elements}")
                else:
                    print(f"  OK: All key elements found")
            else:
                print(f"FAIL: Dashboard page - {response.status_code}")
                
        except Exception as e:
            result = {
                'test_name': 'Predictive Dashboard Page',
                'success': False,
                'error': str(e)
            }
            self.test_results.append(result)
            print(f"ERROR: Dashboard page - {e}")
    
    def test_prediction_accuracy(self):
        """Test prediction accuracy and validation"""
        print("\n=== Testing Prediction Accuracy ===")
        
        accuracy_tests = [
            {
                'name': 'Staffing Prediction Range Validation',
                'test': lambda: self.validate_staffing_prediction_range()
            },
            {
                'name': 'Success Probability Range Validation',
                'test': lambda: self.validate_success_probability_range()
            },
            {
                'name': 'Fatigue Risk Level Validation',
                'test': lambda: self.validate_fatigue_risk_levels()
            }
        ]
        
        for test in accuracy_tests:
            try:
                result = test['test']()
                result['test_name'] = test['name']
                self.test_results.append(result)
                
                if result['success']:
                    print(f"PASS: {test['name']}")
                else:
                    print(f"FAIL: {test['name']} - {result.get('error', 'Validation failed')}")
                    
            except Exception as e:
                error_result = {
                    'test_name': test['name'],
                    'success': False,
                    'error': str(e)
                }
                self.test_results.append(error_result)
                print(f"ERROR: {test['name']} - {e}")
    
    def validate_staffing_prediction_range(self):
        """Validate staffing prediction ranges"""
        try:
            response = self.session.get(
                f'{self.api_base_url}/unified-api.php',
                params={'resource': 'predictive_analytics', 'action': 'staffing_demand_prediction', 'days_ahead': 7},
                timeout=10
            )
            
            if response.status_code != 200:
                return {'success': False, 'error': f'API returned {response.status_code}'}
            
            data = response.json()
            if not data.get('success'):
                return {'success': False, 'error': 'API response not successful'}
            
            predictions = data.get('data', {}).get('predictions', [])
            if not predictions:
                return {'success': False, 'error': 'No predictions found'}
            
            # Validate prediction ranges
            for pred in predictions:
                demand = pred.get('predicted_demand', 0)
                if demand < 1 or demand > 100:
                    return {'success': False, 'error': f'Invalid demand prediction: {demand}'}
            
            return {'success': True, 'predictions_validated': len(predictions)}
            
        except Exception as e:
            return {'success': False, 'error': str(e)}
    
    def validate_success_probability_range(self):
        """Validate success probability ranges"""
        try:
            response = self.session.get(
                f'{self.api_base_url}/unified-api.php',
                params={'resource': 'predictive_analytics', 'action': 'operational_success_probability', 'personnel_count': 15},
                timeout=10
            )
            
            if response.status_code != 200:
                return {'success': False, 'error': f'API returned {response.status_code}'}
            
            data = response.json()
            if not data.get('success'):
                return {'success': False, 'error': 'API response not successful'}
            
            probability = data.get('data', {}).get('success_probability', 0)
            if not (0 <= probability <= 100):
                return {'success': False, 'error': f'Invalid probability: {probability}'}
            
            return {'success': True, 'probability_validated': probability}
            
        except Exception as e:
            return {'success': False, 'error': str(e)}
    
    def validate_fatigue_risk_levels(self):
        """Validate fatigue risk levels"""
        try:
            response = self.session.get(
                f'{self.api_base_url}/unified-api.php',
                params={'resource': 'predictive_analytics', 'action': 'fatigue_risk_analysis'},
                timeout=10
            )
            
            if response.status_code != 200:
                return {'success': False, 'error': f'API returned {response.status_code}'}
            
            data = response.json()
            if not data.get('success'):
                return {'success': False, 'error': 'API response not successful'}
            
            risks = data.get('data', {}).get('fatigue_risks', [])
            valid_levels = ['critical', 'high', 'medium', 'low']
            
            for risk in risks:
                level = risk.get('risk_level', '')
                if level not in valid_levels:
                    return {'success': False, 'error': f'Invalid risk level: {level}'}
                
                score = risk.get('risk_score', 0)
                if not (0 <= score <= 100):
                    return {'success': False, 'error': f'Invalid risk score: {score}'}
            
            return {'success': True, 'risks_validated': len(risks)}
            
        except Exception as e:
            return {'success': False, 'error': str(e)}
    
    def generate_test_report(self):
        """Generate comprehensive test report"""
        print("\n=== Generating Test Report ===")
        
        total_tests = len(self.test_results)
        passed_tests = sum(1 for result in self.test_results if result['success'])
        failed_tests = total_tests - passed_tests
        
        report = f"""
# Predictive Analytics Test Report
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
        with open('predictive_analytics_test_report.md', 'w') as f:
            f.write(report)
        
        print(f"Test report saved to predictive_analytics_test_report.md")
        print(f"Test Summary: {passed_tests}/{total_tests} passed ({passed_tests/total_tests*100:.1f}%)")
        
        return report
    
    def run_comprehensive_tests(self):
        """Run all predictive analytics tests"""
        print("Starting comprehensive predictive analytics testing...")
        
        # Setup database
        if not self.setup_database_connection():
            return False
        
        # Run all tests
        self.test_predictive_analytics_apis()
        self.test_ml_models()
        self.test_database_integration()
        self.test_predictive_dashboard_page()
        self.test_prediction_accuracy()
        
        # Generate report
        self.generate_test_report()
        
        # Cleanup
        self.cursor.close()
        self.conn.close()
        
        return True

if __name__ == "__main__":
    # Run comprehensive tests
    tester = PredictiveAnalyticsTester()
    success = tester.run_comprehensive_tests()
    
    if success:
        print("\nPredictive analytics testing completed successfully!")
        print("Check 'predictive_analytics_test_report.md' for detailed results.")
    else:
        print("\nTesting failed. Check logs for details.")
