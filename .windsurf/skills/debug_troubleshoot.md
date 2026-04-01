---
description: Skill untuk debugging dan troubleshooting
---

# Debug & Troubleshoot Skill

## Debug Mode

Aplikasi sudah memiliki DEBUG_MODE aktif di development environment:

```php
// core/config.php
define('DEBUG_MODE', true);
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}
```

## Common Issues & Solutions

### 1. Database Connection Error

**Error:** `Database connection failed`

**Check:**
```bash
# 1. MySQL running?
sudo /opt/lampp/lampp status

# 2. Database exists?
/opt/lampp/bin/mysql -u root -p -e "SHOW DATABASES;"

# 3. User privileges?
/opt/lampp/bin/mysql -u root -p -e "SHOW GRANTS FOR 'root'@'localhost';"

# 4. Socket path correct?
# Check Database.php: unix_socket=/opt/lampp/var/mysql/mysql.sock
```

**Fix:**
```php
// Test koneksi sederhana
try {
    $pdo = new PDO('mysql:host=localhost;dbname=bagops', 'root', 'root');
    echo "Connection OK";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
```

### 2. Session/Login Issues

**Error:** Selalu redirect ke login page

**Check:**
```php
// 1. Session started?
session_start();

// 2. Check session data
var_dump($_SESSION);

// 3. Check cookie
var_dump($_COOKIE);

// 4. Session path writable?
echo session_save_path();
```

**Fix:**
```php
// Pastikan session_start() dipanggil sebelum output
<?php
session_start();
require_once __DIR__ . '/../core/config.php';
```

### 3. 500 Internal Server Error

**Check error log:**
```bash
# XAMPP error log
tail -f /opt/lampp/logs/error_log

# PHP error log
tail -f /opt/lampp/htdocs/sprint/logs/error.log

# Check error log location
php -i | grep error_log
```

**Common causes:**
- Syntax error (missing semicolon, bracket)
- Undefined function/variable
- Memory limit exceeded
- File permission issues

**Fix syntax error:**
```bash
# Check PHP syntax
php -l /opt/lampp/htdocs/sprint/api/personil_crud.php

# Check all PHP files
find /opt/lampp/htdocs/sprint -name "*.php" -exec php -l {} \;
```

### 4. API Returns HTML Instead of JSON

**Cause:** Error sebelum JSON header

**Debug:**
```php
<?php
// Add at top of API file
ob_start();

try {
    // ... API code
} catch (Exception $e) {
    ob_clean(); // Clear any output
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
```

**Fix:**
```php
<?php
// Start with clean output buffer
if (ob_get_level() === 0) {
    ob_start();
}

header('Content-Type: application/json; charset=UTF-8');

// Your code here

ob_end_flush();
```

### 5. White Screen (Blank Page)

**Check:**
```php
<?php
// Add at top of page
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check for fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error) {
        var_dump($error);
    }
});
```

**Common causes:**
- Out of memory
- Fatal error in include/require
- Infinite loop

### 6. AJAX Request Failed

**Debug:**
```javascript
// In browser console
fetch('/sprint/api/personil_list.php')
  .then(r => r.text())  // Get raw response first
  .then(text => {
    console.log('Raw:', text);  // Check if HTML error
    try {
      return JSON.parse(text);
    } catch (e) {
      console.error('Not JSON:', text);
    }
  });
```

## Debugging Tools

### 1. Error Handler Class

```php
require_once __DIR__ . '/../core/error_handler.php';
ErrorHandler::init();

// Now all errors will be caught and logged
```

### 2. Database Query Logger

```php
// Add to Database.php temporarily
public function query($sql, $params = []) {
    error_log("[SQL] $sql");
    error_log("[PARAMS] " . json_encode($params));
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
```

### 3. Request/Response Logger

```php
// Add at top of API file
error_log("[REQUEST] " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);
error_log("[BODY] " . file_get_contents('php://input'));
```

### 4. Variable Dumper

```php
function dd($var) {
    header('Content-Type: text/html; charset=UTF-8');
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    exit;
}

// Usage
dd($_POST);
dd($personilData);
```

## Troubleshooting Steps

### Step 1: Identify the Problem
```bash
# Check Apache error log
tail -50 /opt/lampp/logs/error_log

# Check application error log
ls -la /opt/lampp/htdocs/sprint/logs/
cat /opt/lampp/htdocs/sprint/logs/error.log
```

### Step 2: Enable Verbose Error Reporting
```php
// In config.php or at top of file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

### Step 3: Isolate the Issue
```php
// Create minimal test case
<?php
require_once __DIR__ . '/core/config.php';

// Test 1: Database
try {
    $db = Database::getInstance();
    echo "DB OK\n";
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}

// Test 2: Session
session_start();
echo "Session: " . (session_id() ?: "No session") . "\n";

// Test 3: Auth
echo "Logged in: " . (AuthHelper::validateSession() ? "Yes" : "No") . "\n";
```

### Step 4: Check Permissions
```bash
# File permissions
ls -la /opt/lampp/htdocs/sprint/

# Fix permissions
chmod 755 /opt/lampp/htdocs/sprint/logs
chmod 644 /opt/lampp/htdocs/sprint/*.php

# Check ownership
chown -R daemon:daemon /opt/lampp/htdocs/sprint/
```

## Performance Debugging

### Slow Query Detection
```php
// Add timing to Database.php
public function query($sql, $params = []) {
    $start = microtime(true);
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    $time = microtime(true) - $start;
    
    if ($time > 1.0) { // More than 1 second
        error_log("[SLOW QUERY] {$time}s: {$sql}");
    }
    
    return $stmt;
}
```

### Memory Usage
```php
function logMemory($label = '') {
    $usage = memory_get_usage(true) / 1024 / 1024;
    error_log("[MEMORY {$label}] {$usage} MB");
}

logMemory('start');
// ... code ...
logMemory('after_query');
```

## Common Fix Snippets

### Reset Database Connection
```php
// If connection lost
Database::resetInstance();
$db = Database::getInstance();
```

### Clear Cache
```php
// APCu cache (if used)
apcu_clear_cache();

// Session
session_destroy();

// Output buffer
while (ob_get_level()) {
    ob_end_clean();
}
```

### Fix CORS Issues
```php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
```

## Testing After Fix

```bash
# 1. Syntax check
php -l file.php

# 2. Run tests
npm test  # Jika ada tests

# 3. Check functionality
curl http://localhost/sprint/api/personil_list.php

# 4. Check logs
tail -f /opt/lampp/logs/error_log
```
