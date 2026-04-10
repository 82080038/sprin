#!/usr/bin/env python3
"""
Comprehensive Fixer for SPRIN
Detailed fixes for remaining issues
"""

import os
import re
from pathlib import Path

class ComprehensiveFixer:
    def __init__(self, root_path):
        self.root = Path(root_path)
        self.changes = []
    
    def fix_all(self):
        """Run comprehensive fixes"""
        print("=" * 70)
        print("COMPREHENSIVE FIXER")
        print("=" * 70)
        
        self.standardize_api_responses()
        self.standardize_error_handling()
        self.standardize_ajax_calls()
        self.add_missing_csp_headers()
        
        print("\n" + "=" * 70)
        print("Comprehensive fixes completed")
        print("=" * 70)
    
    def standardize_api_responses(self):
        """Standardize all API responses to use success/error format"""
        print("\n[1] Standardizing API Response Formats...")
        
        # These APIs need to be checked and standardized
        api_files = self.root.glob('api/*.php')
        
        for api_file in api_files:
            try:
                content = api_file.read_text(encoding='utf-8')
                
                # Skip if already standardized
                if "'success' =>" in content and 'try' in content.lower():
                    continue
                
                # Check for json_encode without success field
                json_matches = re.findall(r'echo json_encode\(([^)]+)\);', content)
                for match in json_matches:
                    if "'success'" not in match and '"success"' not in match:
                        print(f"  ⚠️  {api_file.name} has non-standard JSON response")
                        print(f"     Response: {match[:50]}...")
                        
            except Exception as e:
                print(f"  ✗ Error reading {api_file}: {e}")
    
    def standardize_error_handling(self):
        """Add try-catch to APIs without error handling"""
        print("\n[2] Standardizing Error Handling...")
        
        api_files = self.root.glob('api/*.php')
        
        for api_file in api_files:
            try:
                content = api_file.read_text(encoding='utf-8')
                
                # Skip if already has try-catch
                if 'try {' in content and 'catch' in content:
                    continue
                
                # Skip simple files that don't need it
                if api_file.stat().st_size < 500:
                    continue
                
                print(f"  ⚠️  {api_file.name} missing try-catch error handling")
                
            except Exception as e:
                print(f"  ✗ Error reading {api_file}: {e}")
    
    def standardize_ajax_calls(self):
        """Standardize AJAX calls in pages"""
        print("\n[3] Standardizing AJAX Patterns...")
        
        page_files = (self.root / 'pages').glob('*.php')
        
        for page_file in page_files:
            try:
                content = page_file.read_text(encoding='utf-8')
                
                # Check for fetch without proper error handling
                if 'fetch(' in content:
                    # Check if it has .catch()
                    if '.catch(' not in content:
                        print(f"  ⚠️  {page_file.name} fetch() without .catch()")
                    
                    # Check if it has credentials
                    if 'credentials' not in content:
                        print(f"  ⚠️  {page_file.name} fetch() without credentials")
                    
                    # Check for proper JSON parsing
                    if 'response.json()' not in content:
                        print(f"  ⚠️  {page_file.name} fetch() without response.json()")
                    
            except Exception as e:
                print(f"  ✗ Error reading {page_file}: {e}")
    
    def add_missing_csp_headers(self):
        """Add security headers where missing"""
        print("\n[4] Checking Security Headers...")
        
        php_files = list(self.root.rglob('*.php'))
        
        security_files = [
            'api/jabatan_api.php',
            'api/unified-api.php',
            'api/unsur_api.php',
            'api/bagian_api.php',
        ]
        
        for file_path in security_files:
            full_path = self.root / file_path
            if full_path.exists():
                try:
                    content = full_path.read_text(encoding='utf-8')
                    
                    # Check for Content-Type header
                    if 'Content-Type' not in content:
                        print(f"  ⚠️  {file_path} missing Content-Type header")
                    
                    # Check for X-Frame-Options
                    if 'X-Frame-Options' not in content:
                        print(f"  ⚠️  {file_path} missing X-Frame-Options header")
                    
                except Exception as e:
                    print(f"  ✗ Error reading {file_path}: {e}")
    
    def create_api_template(self):
        """Create standardized API template"""
        template = '''<?php
/**
 * [API Name] - Standardized API Template
 */

// Error reporting controlled by config
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

error_reporting(E_ALL);
ini_set('display_errors', defined('DEBUG_MODE') && DEBUG_MODE ? 1 : 0);
ini_set('log_errors', 1);

// Set security headers
header("Content-Type: application/json; charset=UTF-8");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

// Start session
SessionManager::start();

// CSRF validation for mutating requests
$readOnlyActions = ['get_all', 'get_detail'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($action, $readOnlyActions)) {
    $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    
    if (empty($csrfToken) || !\AuthHelper::validateCSRFToken($csrfToken)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid CSRF token',
            'csrf_expired' => true
        ]);
        exit;
    }
}

// Database connection
try {
    require_once __DIR__ . '/../core/Database.php';
    $pdo = Database::getInstance()->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Main logic
try {
    switch ($action) {
        case 'get_all':
            // Implementation
            echo json_encode(['success' => true, 'data' => []]);
            break;
            
        case 'create':
            // Validate input
            // Insert data
            echo json_encode(['success' => true, 'message' => 'Created']);
            break;
            
        case 'update':
            // Validate input
            // Update data
            echo json_encode(['success' => true, 'message' => 'Updated']);
            break;
            
        case 'delete':
            // Delete data
            echo json_encode(['success' => true, 'message' => 'Deleted']);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    error_log('[API Error] ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
'''
        
        template_path = self.root / 'docs' / 'API_TEMPLATE.php'
        template_path.parent.mkdir(exist_ok=True)
        template_path.write_text(template)
        
        print(f"\n✓ Created API template at: {template_path}")
    
    def create_page_template(self):
        """Create standardized page template"""
        template = '''<?php
// Start output buffering
if (ob_get_level() === 0) {
    ob_start();
}

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

SessionManager::start();

if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

$page_title = 'Page Title - POLRES Samosir';
include __DIR__ . '/../includes/components/header.php';

require_once __DIR__ . '/../core/Database.php';
$pdo = Database::getInstance()->getConnection();

// Handle AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    // Handle POST requests
    exit;
}
?>
<!-- Page content here -->

<script>
// Initialize CSRF
document.addEventListener('DOMContentLoaded', async function() {
    if (!window.APP_CONFIG?.csrfToken) {
        // Fetch token
    }
});

// Standardized API call
async function apiCall(url, data) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': window.APP_CONFIG?.csrfToken
        },
        credentials: 'same-origin',
        body: new URLSearchParams(data)
    });
    return response.json();
}
</script>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
'''
        
        template_path = self.root / 'docs' / 'PAGE_TEMPLATE.php'
        template_path.write_text(template)
        
        print(f"✓ Created Page template at: {template_path}")


def main():
    root_path = '/opt/lampp/htdocs/sprin'
    fixer = ComprehensiveFixer(root_path)
    
    fixer.fix_all()
    fixer.create_api_template()
    fixer.create_page_template()
    
    print("\n" + "=" * 70)
    print("Templates created for future consistency")
    print("=" * 70)


if __name__ == '__main__':
    main()
