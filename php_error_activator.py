#!/usr/bin/env python3
"""
PHP Error Activator for SPRIN Development
Enable comprehensive PHP error reporting for development environment
"""

import os
import re
import json
import subprocess
from pathlib import Path

class PHPErrorActivator:
    def __init__(self, base_path: str = "/opt/lampp/htdocs/sprint"):
        self.base_path = Path(base_path)
        self.changes_made = []
        
    def activate_php_ini_errors(self):
        """Activate PHP errors in php.ini configuration"""
        php_ini_paths = [
            "/opt/lampp/etc/php.ini",
            "/opt/lampp/bin/php.ini", 
            "/etc/php/8.2/apache2/php.ini",
            "/etc/php/8.2/cli/php.ini"
        ]
        
        php_ini_updated = False
        
        for php_ini_path in php_ini_paths:
            if not Path(php_ini_path).exists():
                continue
                
            try:
                with open(php_ini_path, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                original_content = content
                
                # Enable all error reporting settings
                error_settings = {
                    'display_errors': 'On',
                    'display_startup_errors': 'On', 
                    'error_reporting': 'E_ALL',
                    'log_errors': 'On',
                    'track_errors': 'On',
                    'html_errors': 'On',
                    'xmlrpc_errors': '0',
                    'error_prepend_string': '<div style="color: red; border: 1px solid red; padding: 10px; margin: 10px;">',
                    'error_append_string': '</div>',
                    'error_log': '/opt/lampp/logs/php_errors.log'
                }
                
                for setting, value in error_settings.items():
                    pattern = rf'^{setting}\s*=\s*[^\n\r]*'
                    replacement = f'{setting} = {value}'
                    content = re.sub(pattern, replacement, content, flags=re.MULTILINE | re.IGNORECASE)
                
                # Ensure error_reporting is E_ALL
                if 'error_reporting = E_ALL' not in content:
                    content += '\n; Development Error Settings\nerror_reporting = E_ALL\ndisplay_errors = On\n'
                
                # Write back if changed
                if content != original_content:
                    with open(php_ini_path, 'w', encoding='utf-8') as f:
                        f.write(content)
                    
                    self.changes_made.append({
                        'type': 'php_ini',
                        'file': php_ini_path,
                        'changes': 'Enabled comprehensive error reporting'
                    })
                    
                    php_ini_updated = True
                    print(f"✅ Updated PHP error settings in {php_ini_path}")
                
            except Exception as e:
                print(f"Error updating {php_ini_path}: {e}")
        
        return php_ini_updated

    def activate_htaccess_errors(self):
        """Enable PHP errors in .htaccess"""
        htaccess_file = self.base_path / '.htaccess'
        
        try:
            with open(htaccess_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            original_content = content
            
            # Add PHP error settings to .htaccess
            php_error_settings = '''
# PHP Error Reporting for Development
<IfModule mod_php8.c>
    php_flag display_errors On
    php_flag display_startup_errors On
    php_value error_reporting E_ALL
    php_flag log_errors On
    php_flag track_errors On
    php_flag html_errors On
    php_value error_log /opt/lampp/logs/php_errors.log
</IfModule>

<IfModule mod_php7.c>
    php_flag display_errors On
    php_flag display_startup_errors On
    php_value error_reporting E_ALL
    php_flag log_errors On
    php_flag track_errors On
    php_flag html_errors On
    php_value error_log /opt/lampp/logs/php_errors.log
</IfModule>
'''
            
            # Remove existing PHP error settings
            content = re.sub(
                r'# PHP Error Reporting.*?</IfModule>',
                '',
                content,
                flags=re.MULTILINE | re.DOTALL
            )
            
            # Add new settings at the end
            content += php_error_settings
            
            with open(htaccess_file, 'w', encoding='utf-8') as f:
                f.write(content)
            
            self.changes_made.append({
                'type': 'htaccess',
                'file': str(htaccess_file),
                'changes': 'Added PHP error reporting settings'
            })
            
            print(f"✅ Added PHP error settings to .htaccess")
            return True
            
        except Exception as e:
            print(f"Error updating .htaccess: {e}")
            return False

    def create_development_config(self):
        """Create development configuration file"""
        dev_config_file = self.base_path / 'core' / 'config_dev.php'
        
        dev_config_content = '''<?php
/**
 * Development Configuration for SPRIN Application
 * Enhanced error reporting and debugging settings
 */

// Force enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('track_errors', 1);
ini_set('html_errors', 1);
ini_set('error_prepend_string', '<div style="color: #d32f2f; background: #ffebee; border: 2px solid #d32f2f; padding: 15px; margin: 10px; border-radius: 5px; font-family: monospace;">');
ini_set('error_append_string', '</div>');

// Custom error handler for development
function development_error_handler($severity, $message, $file, $line) {
    $error_types = array(
        E_ERROR => 'Fatal Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated'
    );
    
    $error_type = isset($error_types[$severity]) ? $error_types[$severity] : 'Unknown Error';
    
    echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 10px; border-radius: 5px;">';
    echo "<strong>{$error_type}:</strong> {$message}<br>";
    echo "<em>File:</em> {$file}<br>";
    echo "<em>Line:</em> {$line}<br>";
    echo '</div>';
    
    // Log to file as well
    error_log("{$error_type}: {$message} in {$file} on line {$line}");
    
    return true;
}

// Set custom error handler
set_error_handler('development_error_handler');

// Development debug function
function debug_var($var, $label = 'DEBUG') {
    echo '<div style="background: #e3f2fd; border: 1px solid #2196f3; padding: 10px; margin: 10px; border-radius: 5px;">';
    echo "<strong>{$label}:</strong><br>";
    echo '<pre style="background: #f5f5f5; padding: 10px; border-radius: 3px; overflow: auto;">';
    print_r($var);
    echo '</pre>';
    echo '</div>';
}

// Development query debugger
function debug_query($sql, $params = []) {
    echo '<div style="background: #f3e5f5; border: 1px solid #9c27b0; padding: 10px; margin: 10px; border-radius: 5px;">';
    echo '<strong>SQL Query:</strong><br>';
    echo '<code style="background: #f5f5f5; padding: 5px; display: block;">' . htmlspecialchars($sql) . '</code>';
    
    if (!empty($params)) {
        echo '<strong>Parameters:</strong><br>';
        echo '<pre style="background: #f5f5f5; padding: 5px;">';
        print_r($params);
        echo '</pre>';
    }
    echo '</div>';
}

// Development session debugger
function debug_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo '<div style="background: #e8f5e8; border: 1px solid #4caf50; padding: 10px; margin: 10px; border-radius: 5px;">';
        echo '<strong>Session Data:</strong><br>';
        echo '<pre style="background: #f5f5f5; padding: 5px;">';
        print_r($_SESSION);
        echo '</pre>';
        echo '</div>';
    }
}

// Development POST debugger
function debug_post() {
    if (!empty($_POST)) {
        echo '<div style="background: #fff3e0; border: 1px solid #ff9800; padding: 10px; margin: 10px; border-radius: 5px;">';
        echo '<strong>POST Data:</strong><br>';
        echo '<pre style="background: #f5f5f5; padding: 5px;">';
        print_r($_POST);
        echo '</pre>';
        echo '</div>';
    }
}

// Development GET debugger  
function debug_get() {
    if (!empty($_GET)) {
        echo '<div style="background: #fce4ec; border: 1px solid #e91e63; padding: 10px; margin: 10px; border-radius: 5px;">';
        echo '<strong>GET Data:</strong><br>';
        echo '<pre style="background: #f5f5f5; padding: 5px;">';
        print_r($_GET);
        echo '</pre>';
        echo '</div>';
    }
}

// Auto-load development helpers on every page
function auto_debug_development() {
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
        debug_session();
        debug_post();
        debug_get();
    }
}

// Development mode constant
define('DEVELOPMENT_MODE', true);

echo '<!-- Development Mode Active - PHP Errors Enabled -->';

?>'''
        
        try:
            with open(dev_config_file, 'w', encoding='utf-8') as f:
                f.write(dev_config_content)
            
            self.changes_made.append({
                'type': 'development_config',
                'file': str(dev_config_file),
                'changes': 'Created comprehensive development configuration'
            })
            
            print(f"✅ Created development configuration file")
            return True
            
        except Exception as e:
            print(f"Error creating development config: {e}")
            return False

    def update_main_config(self):
        """Update main config to include development settings"""
        config_file = self.base_path / 'core' / 'config.php'
        
        try:
            with open(config_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            original_content = content
            
            # Add development mode detection
            dev_detection = '''
// Development Mode Detection
if (!defined('DEVELOPMENT_MODE')) {
    define('DEVELOPMENT_MODE', true);
}

// Enable comprehensive error reporting in development
if (DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('track_errors', 1);
    ini_set('html_errors', 1);
}

'''
            
            # Insert after opening PHP tag
            if content.startswith('<?php'):
                content = content[:5] + dev_detection + content[5:]
            
            # Write back if changed
            if content != original_content:
                with open(config_file, 'w', encoding='utf-8') as f:
                    f.write(content)
                
                self.changes_made.append({
                    'type': 'main_config',
                    'file': str(config_file),
                    'changes': 'Added development mode and error settings'
                })
                
                print(f"✅ Updated main config with development settings")
                return True
            else:
                print("ℹ️ Main config already has development settings")
                return True
        
        except Exception as e:
            print(f"Error updating main config: {e}")
            return False

    def update_all_pages_for_errors(self):
        """Update all PHP pages to include development error reporting"""
        php_files = list(self.base_path.rglob("*.php"))
        pages_updated = 0
        
        for php_file in php_files:
            # Skip certain files
            if any(skip in str(php_file) for skip in ['vendor', 'node_modules', '.git']):
                continue
                
            try:
                with open(php_file, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                original_content = content
                
                # Add development error reporting at the beginning
                if content.startswith('<?php') and 'DEVELOPMENT_MODE' not in content:
                    dev_header = '''

// Development Error Reporting
if (!defined('DEVELOPMENT_MODE')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}
'''
                    content = content[:5] + dev_header + content[5:]
                
                # Write back if changed
                if content != original_content:
                    with open(php_file, 'w', encoding='utf-8') as f:
                        f.write(content)
                    
                    pages_updated += 1
                    
                    if pages_updated <= 5:  # Show first few updates
                        print(f"✅ Updated {php_file.relative_to(self.base_path)}")
                    elif pages_updated == 6:
                        print(f"✅ Updated {len(php_files)-5} more files...")
                
            except Exception as e:
                print(f"Error updating {php_file}: {e}")
        
        if pages_updated > 0:
            self.changes_made.append({
                'type': 'pages_updated',
                'count': pages_updated,
                'changes': 'Added development error reporting to PHP files'
            })
        
        return pages_updated > 0

    def create_error_log_file(self):
        """Create and configure error log file"""
        error_log_file = Path("/opt/lampp/logs/php_errors.log")
        
        try:
            # Create log directory if it doesn't exist
            error_log_file.parent.mkdir(parents=True, exist_ok=True)
            
            # Create/truncate error log file
            with open(error_log_file, 'w', encoding='utf-8') as f:
                f.write(f"# PHP Error Log - SPRIN Development\n# Started: {subprocess.check_output(['date'], text=True).strip()}\n\n")
            
            # Set proper permissions
            os.chmod(error_log_file, 0o666)
            
            self.changes_made.append({
                'type': 'error_log',
                'file': str(error_log_file),
                'changes': 'Created PHP error log file'
            })
            
            print(f"✅ Created PHP error log file: {error_log_file}")
            return True
            
        except Exception as e:
            print(f"Error creating error log file: {e}")
            return False

    def restart_services(self):
        """Restart Apache to apply PHP configuration changes"""
        try:
            print("🔄 Restarting Apache to apply PHP configuration...")
            result = subprocess.run(
                ['sudo', '/opt/lampp/lampp', 'reload'],
                capture_output=True,
                text=True,
                timeout=30
            )
            
            if result.returncode == 0:
                print("✅ Apache restarted successfully")
                return True
            else:
                print(f"⚠️ Apache restart warning: {result.stderr}")
                return False
                
        except Exception as e:
            print(f"Error restarting services: {e}")
            return False

    def run_error_activation(self):
        """Run comprehensive PHP error activation"""
        print("🚀 Starting PHP Error Activation for Development...")
        
        activations = [
            self.activate_php_ini_errors(),
            self.activate_htaccess_errors(),
            self.create_development_config(),
            self.update_main_config(),
            self.update_all_pages_for_errors(),
            self.create_error_log_file(),
            self.restart_services()
        ]
        
        successful_activations = sum(1 for act in activations if act)
        
        # Generate report
        report = {
            'timestamp': subprocess.check_output(['date'], text=True).strip(),
            'error_activations': successful_activations,
            'total_activations': len(activations),
            'changes_made': self.changes_made,
            'success_rate': f"{(successful_activations/len(activations)*100):.1f}%"
        }
        
        report_file = self.base_path / 'php_error_activation_report.json'
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, default=str)
        
        print(f"\n📊 PHP Error Activation Summary:")
        print(f"Activations Applied: {successful_activations}/{len(activations)}")
        print(f"Success Rate: {report['success_rate']}")
        print(f"Report saved to: {report_file}")
        
        return successful_activations

def main():
    """Main execution"""
    activator = PHPErrorActivator()
    success = activator.run_error_activation()
    
    if success > 0:
        print(f"\n🎉 Successfully activated PHP errors in {success} areas!")
        print("📝 All PHP errors, warnings, and notices will now be displayed")
        print("🔍 Development mode is fully enabled")
    else:
        print("\n❌ No PHP error activations were applied")
    
    return success > 0

if __name__ == "__main__":
    main()
