#!/usr/bin/env python3
"""
URL Monitoring System for SPRIN Application
Regular automated link validation and monitoring
"""

import requests
import json
from datetime import datetime

class URLMonitor:
    def __init__(self, base_url="http://localhost/sprint"):
        self.base_url = base_url
        self.results = []
        
    def check_url(self, url, timeout=10):
        """Check if URL is accessible"""
        try:
            response = requests.get(url, timeout=timeout)
            return {
                'url': url,
                'status_code': response.status_code,
                'accessible': response.status_code < 400,
                'timestamp': datetime.now().isoformat()
            }
        except Exception as e:
            return {
                'url': url,
                'status_code': 0,
                'accessible': False,
                'error': str(e),
                'timestamp': datetime.now().isoformat()
            }
    
    def monitor_endpoints(self):
        """Monitor all application endpoints"""
        endpoints = {
            'main_pages': [
                f"{self.base_url}/",
                f"{self.base_url}/login.php",
                f"{self.base_url}/pages/main.php",
                f"{self.base_url}/pages/personil.php"
            ],
            'api_endpoints': [
                f"{self.base_url}/api/personil.php",
                f"{self.base_url}/api/bagian.php",
                f"{self.base_url}/api/unsur.php",
                f"{self.base_url}/api/health_check_new.php"
            ]
        }
        
        for category, urls in endpoints.items():
            for url in urls:
                result = self.check_url(url)
                result['category'] = category
                self.results.append(result)
    
    def generate_report(self):
        """Generate monitoring report"""
        total = len(self.results)
        accessible = len([r for r in self.results if r['accessible']])
        failed = total - accessible
        
        report = {
            'timestamp': datetime.now().isoformat(),
            'summary': {
                'total_urls': total,
                'accessible': accessible,
                'failed': failed,
                'success_rate': f"{(accessible/total)*100:.1f}%" if total > 0 else "0%"
            },
            'results': self.results
        }
        
        # Save report
        with open('url_monitoring_report.json', 'w') as f:
            json.dump(report, f, indent=2)
        
        return report
    
    def run_monitoring(self):
        """Run complete monitoring process"""
        print("🔍 Starting URL Monitoring...")
        
        self.monitor_endpoints()
        report = self.generate_report()
        
        print(f"📊 Monitoring Results:")
        print(f"Total URLs: {report['summary']['total_urls']}")
        print(f"Accessible: {report['summary']['accessible']}")
        print(f"Failed: {report['summary']['failed']}")
        print(f"Success Rate: {report['summary']['success_rate']}")
        
        return report

if __name__ == "__main__":
    monitor = URLMonitor()
    report = monitor.run_monitoring()
