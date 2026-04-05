#!/usr/bin/env python3
"""
Optimized PHP Configuration based on Internet Best Practices
Following recommendations from phpdelusions.net and official PHP documentation
"""

import os
import re
import json
import subprocess
from pathlib import Path

class OptimizedPHPConfig:
    def __init__(self, base_path: str = "/opt/lampp/htdocs/sprint"):
        self.base_path = Path(base_path)
        self.changes_made = []
        
    def find_correct_php_ini(self):
        """Find the actual php.ini file being used by Apache"""
        try:
            # Create a phpinfo file to find loaded configuration
            phpinfo_file = self.base_path / 'find_php_ini.php'
            phpinfo_content = '''<?php
phpinfo();
?>'''
            
            with open(phpinfo_file, 'w', encoding='utf-8') as f:
                f.write(phpinfo_content)
            
            # Run phpinfo to find the loaded configuration file
            result = subprocess.run(
                ['php', str(phpinfo_file)],
                capture_output=True,
                text=True,
                cwd=str(self.base_path)
            )
            
            # Extract the loaded configuration file path
            loaded_config = None
            for line in result.stdout.split('\n'):
                if 'Loaded Configuration File' in line:
                    # Extract the path from the line
                    config_match = re.search(r'=>\s*(/[^<\s]+)', line)
                    if config_match:
                        loaded_config = config_match.group(1)
                        break
            
            # Clean up
            phpinfo_file.unlink(missing_ok=True)
            
            if loaded_config and Path(loaded_config).exists():
                print(f"✅ Found loaded PHP configuration: {loaded_config}")
                return loaded_config
            else:
                # Fallback to common XAMPP locations
                common_locations = [
                    "/opt/lampp/etc/php.ini",
                    "/opt/lampp/bin/php.ini",
                    "/etc/php/8.2/apache2/php.ini",
                    "/etc/php/8.2/cli/php.ini"
                ]
                
                for location in common_locations:
                    if Path(location).exists():
                        print(f"✅ Using fallback PHP configuration: {location}")
                        return location
                
                return None
                
        except Exception as e:
            print(f"Error finding php.ini: {e}")
            return None

    def optimize_php_ini_based_on_best_practices(self):
        """Optimize php.ini based on internet best practices"""
        php_ini = self.find_correct_php_ini()
        
        if not php_ini:
            print("❌ Could not find php.ini file")
            return False
        
        try:
            with open(php_ini, 'r', encoding='utf-8') as f:
                content = f.read()
            
            original_content = content
            
            # Apply best practices from phpdelusions.net
            best_practices_settings = {
                # Development settings (from phpdelusions.net)
                'error_reporting': 'E_ALL',
                'display_errors': 'On',
                'display_startup_errors': 'On',
                'log_errors': 'On',
                'track_errors': 'On',
                'html_errors': 'On',
                'error_prepend_string': '<div style="color: red; border: 1px solid red; padding: 10px; margin: 10px;">',
                'error_append_string': '</div>',
                
                # Performance settings
                'max_execution_time': '300',
                'memory_limit': '512M',
                'post_max_size': '100M',
                'upload_max_filesize': '100M',
                
                # Security settings (development friendly)
                'expose_php': 'On',  # Show PHP version in development
                'allow_url_fopen': 'On',
                'allow_url_include': 'Off',
                
                # Session settings
                'session.save_path': '/opt/lampp/tmp',
                'session.use_strict_mode': '1',
                'session.cookie_httponly': '1',
                'session.cookie_samesite': 'Lax',
                
                # Error logging
                'error_log': '/opt/lampp/logs/php_errors.log'
            }
            
            # Apply settings
            for setting, value in best_practices_settings.items():
                pattern = rf'^{re.escape(setting)}\s*=\s*[^\n\r]*'
                replacement = f'{setting} = {value}'
                content = re.sub(pattern, replacement, content, flags=re.MULTILINE | re.IGNORECASE)
            
            # Add development section if not present
            if '; Development Settings' not in content:
                dev_section = '''

; ===== DEVELOPMENT SETTINGS =====
; Based on phpdelusions.net best practices
; https://phpdelusions.net/articles/error_reporting

[Development]
; Error reporting for development
error_reporting = E_ALL
display_errors = On
display_startup_errors = On
log_errors = On
track_errors = On
html_errors = On

; Error formatting
error_prepend_string = '<div style="color: #d32f2f; background: #ffebee; border: 2px solid #d32f2f; padding: 15px; margin: 10px; border-radius: 8px; font-family: monospace;">'
error_append_string = '</div>'

; Error logging
error_log = /opt/lampp/logs/php_errors.log

; Development-friendly settings
expose_php = On
max_execution_time = 300
memory_limit = 512M
'''
                content += dev_section
            
            # Write back if changed
            if content != original_content:
                with open(php_ini, 'w', encoding='utf-8') as f:
                    f.write(content)
                
                self.changes_made.append({
                    'type': 'php_ini_optimized',
                    'file': php_ini,
                    'source': 'phpdelusions.net best practices',
                    'changes': 'Applied development error reporting best practices'
                })
                
                print(f"✅ Optimized PHP configuration based on best practices")
                return True
            else:
                print("ℹ️ PHP configuration already follows best practices")
                return True
                
        except Exception as e:
            print(f"Error optimizing PHP configuration: {e}")
            return False

    def update_htaccess_with_best_practices(self):
        """Update .htaccess with best practices from internet research"""
        htaccess_file = self.base_path / '.htaccess'
        
        try:
            with open(htaccess_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            original_content = content
            
            # Remove existing PHP error settings
            content = re.sub(
                r'# PHP Error Reporting.*?</IfModule>',
                '',
                content,
                flags=re.MULTILINE | re.DOTALL
            )
            
            # Add best practices from Stack Overflow and PHP documentation
            php_settings = '''
# PHP Error Reporting - Best Practices
# Based on: https://phpdelusions.net/articles/error_reporting
# https://stackoverflow.com/questions/1053424/how-do-i-get-php-errors-to-display

<IfModule mod_php8.c>
    # Development settings - show all errors
    php_flag display_errors On
    php_flag display_startup_errors On
    php_value error_reporting E_ALL
    php_flag log_errors On
    php_flag track_errors On
    php_flag html_errors On
    php_value error_log /opt/lampp/logs/php_errors.log
    
    # Performance settings for development
    php_value max_execution_time 300
    php_value memory_limit 512M
    php_value post_max_size 100M
    php_value upload_max_filesize 100M
</IfModule>

<IfModule mod_php7.c>
    # Development settings - show all errors
    php_flag display_errors On
    php_flag display_startup_errors On
    php_value error_reporting E_ALL
    php_flag log_errors On
    php_flag track_errors On
    php_flag html_errors On
    php_value error_log /opt/lampp/logs/php_errors.log
    
    # Performance settings for development
    php_value max_execution_time 300
    php_value memory_limit 512M
    php_value post_max_size 100M
    php_value upload_max_filesize 100M
</IfModule>

# Ensure PHP errors are logged even if display fails
ErrorLog /opt/lampp/logs/apache_php_errors.log
'''
            
            content += php_settings
            
            with open(htaccess_file, 'w', encoding='utf-8') as f:
                f.write(content)
            
            self.changes_made.append({
                'type': 'htaccess_best_practices',
                'file': str(htaccess_file),
                'source': 'Stack Overflow & PHP documentation',
                'changes': 'Applied PHP error reporting best practices'
            })
            
            print(f"✅ Updated .htaccess with internet best practices")
            return True
            
        except Exception as e:
            print(f"Error updating .htaccess: {e}")
            return False

    def integrate_improved_error_handler(self):
        """Integrate the improved error handler based on phpdelusions.net"""
        config_file = self.base_path / 'core' / 'config.php'
        
        try:
            with open(config_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            original_content = content
            
            # Add improved error handler inclusion
            if 'improved_error_handler.php' not in content:
                error_handler_include = '''
// Include improved error handler based on phpdelusions.net best practices
require_once __DIR__ . '/../improved_error_handler.php';

'''
                
                # Insert after opening PHP tag and declare
                if content.startswith('<?php'):
                    if 'declare(strict_types=1)' in content:
                        # Insert after declare statement
                        lines = content.split('\n')
                        declare_line = -1
                        for i, line in enumerate(lines):
                            if 'declare(strict_types=1)' in line:
                                declare_line = i
                                break
                        
                        if declare_line != -1:
                            lines.insert(declare_line + 1, error_handler_include)
                            content = '\n'.join(lines)
                    else:
                        content = content[:5] + error_handler_include + content[5:]
            
            # Write back if changed
            if content != original_content:
                with open(config_file, 'w', encoding='utf-8') as f:
                    f.write(content)
                
                self.changes_made.append({
                    'type': 'improved_error_handler',
                    'file': str(config_file),
                    'source': 'phpdelusions.net universal error handler',
                    'changes': 'Integrated improved error handling system'
                })
                
                print(f"✅ Integrated improved error handler in config.php")
                return True
            else:
                print("ℹ️ Improved error handler already integrated")
                return True
                
        except Exception as e:
            print(f"Error integrating improved error handler: {e}")
            return False

    def create_development_environment_detector(self):
        """Create environment detection based on best practices"""
        env_detector_file = self.base_path / 'core' / 'environment_detector.php'
        
        env_detector_content = '''<?php
/**
 * Environment Detector based on best practices
 * Automatically detects development vs production environment
 */

class EnvironmentDetector {
    private static $environment = null;
    
    public static function getEnvironment() {
        if (self::$environment !== null) {
            return self::$environment;
        }
        
        // Check for common development indicators
        $isDevelopment = (
            $_SERVER['SERVER_NAME'] === 'localhost' ||
            $_SERVER['SERVER_NAME'] === '127.0.0.1' ||
            $_SERVER['SERVER_ADDR'] === '127.0.0.1' ||
            $_SERVER['SERVER_ADDR'] === '::1' ||
            strpos($_SERVER['SERVER_NAME'], '.local') !== false ||
            strpos($_SERVER['SERVER_NAME'], '.dev') !== false ||
            strpos($_SERVER['SERVER_NAME'], '.test') !== false ||
            isset($_ENV['DEV']) ||
            isset($_ENV['DEVELOPMENT']) ||
            isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development'
        );
        
        self::$environment = $isDevelopment ? 'development' : 'production';
        return self::$environment;
    }
    
    public static function isDevelopment() {
        return self::getEnvironment() === 'development';
    }
    
    public static function isProduction() {
        return self::getEnvironment() === 'production';
    }
    
    public static function configureErrorReporting() {
        if (self::isDevelopment()) {
            // Development: Show all errors
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            ini_set('log_errors', 1);
            ini_set('track_errors', 1);
            ini_set('html_errors', 1);
        } else {
            // Production: Log errors but don't display them
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            ini_set('log_errors', 1);
            ini_set('track_errors', 1);
            ini_set('html_errors', 0);
        }
    }
}

// Auto-configure error reporting
EnvironmentDetector::configureErrorReporting();

// Define constants for backward compatibility
define('DEVELOPMENT_MODE', EnvironmentDetector::isDevelopment());
define('PRODUCTION_MODE', EnvironmentDetector::isProduction());

?>'''
        
        try:
            with open(env_detector_file, 'w', encoding='utf-8') as f:
                f.write(env_detector_content)
            
            self.changes_made.append({
                'type': 'environment_detector',
                'file': str(env_detector_file),
                'source': 'Best practices for environment detection',
                'changes': 'Created automatic environment detection system'
            })
            
            print(f"✅ Created environment detector with best practices")
            return True
            
        except Exception as e:
            print(f"Error creating environment detector: {e}")
            return False

    def restart_apache_for_changes(self):
        """Restart Apache to apply all configuration changes"""
        try:
            print("🔄 Restarting Apache to apply optimized configuration...")
            result = subprocess.run(
                ['sudo', '/opt/lampp/lampp', 'reload'],
                capture_output=True,
                text=True,
                timeout=30
            )
            
            if result.returncode == 0:
                print("✅ Apache restarted successfully with optimized configuration")
                return True
            else:
                print(f"⚠️ Apache restart warning: {result.stderr}")
                return False
                
        except Exception as e:
            print(f"Error restarting Apache: {e}")
            return False

    def run_optimization(self):
        """Run complete optimization based on internet best practices"""
        print("🌐 Starting PHP Configuration Optimization based on Internet Best Practices...")
        print("📚 Sources: phpdelusions.net, Stack Overflow, official PHP documentation")
        
        optimizations = [
            self.optimize_php_ini_based_on_best_practices(),
            self.update_htaccess_with_best_practices(),
            self.integrate_improved_error_handler(),
            self.create_development_environment_detector(),
            self.restart_apache_for_changes()
        ]
        
        successful_optimizations = sum(1 for opt in optimizations if opt)
        
        # Generate report
        report = {
            'timestamp': subprocess.check_output(['date'], text=True).strip(),
            'optimizations_applied': successful_optimizations,
            'total_optimizations': len(optimizations),
            'changes_made': self.changes_made,
            'sources_used': [
                'https://phpdelusions.net/articles/error_reporting',
                'https://www.php.net/manual/en/control-structures.declare.php',
                'https://stackoverflow.com/questions/1053424/how-do-i-get-php-errors-to-display'
            ],
            'success_rate': f"{(successful_optimizations/len(optimizations)*100):.1f}%"
        }
        
        report_file = self.base_path / 'optimized_php_config_report.json'
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, default=str)
        
        print(f"\n📊 Optimization Summary:")
        print(f"Optimizations Applied: {successful_optimizations}/{len(optimizations)}")
        print(f"Success Rate: {report['success_rate']}")
        print(f"Sources Used: {len(report['sources_used'])}")
        print(f"Report saved to: {report_file}")
        
        return successful_optimizations

def main():
    """Main execution"""
    optimizer = OptimizedPHPConfig()
    success = optimizer.run_optimization()
    
    if success > 0:
        print(f"\n🎉 Successfully optimized PHP configuration with {success} improvements!")
        print("📚 Based on best practices from trusted internet sources")
        print("🔍 Error reporting now follows industry standards")
    else:
        print("\n❌ No optimizations were applied")
    
    return success > 0

if __name__ == "__main__":
    main()
