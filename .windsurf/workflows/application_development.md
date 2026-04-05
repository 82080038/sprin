---
description: Complete application development and maintenance workflow
---

# SPRIN Application Development Workflow

## 🎯 Application Overview

**SPRIN** adalah Sistem Manajemen POLRES Samosir - aplikasi web berbasis PHP untuk mengelola personel, unit organisasi, dan operasional kepolisian.

### Teknologi Stack
- **Backend**: PHP 8.2 dengan PDO dan session management
- **Database**: MySQL dengan prepared statements
- **Frontend**: Bootstrap 5 + JavaScript + CSS3
- **Server**: Apache (XAMPP)
- **Testing**: Puppeteer untuk E2E testing

## 🏗️ Arsitektur Aplikasi

### Struktur Direktori Utama
```
/core/           - Core system (SessionManager, AuthHelper, config)
/pages/          - Halaman aplikasi (main, personil, bagian, unsur)
/api/            - API endpoints untuk CRUD operations
/includes/       - Komponen UI (header, footer, navigation)
/public/         - Assets (CSS, JS, images)
/security/       - Security features
```

### Alur Utama Aplikasi
1. **Authentication**: login.php → SessionManager → AuthHelper
2. **Main Dashboard**: pages/main.php dengan navigasi ke modul
3. **Personnel Management**: pages/personil.php ↔ api/personil.php
4. **Unit Management**: pages/bagian.php ↔ api/bagian.php
5. **Element Management**: pages/unsur.php ↔ api/unsur.php
6. **Calendar**: pages/calendar_dashboard.php

## 🔧 Development Workflow

### 1. Environment Setup
```bash
# Start XAMPP services
sudo /opt/lampp/lampp start

# Verify PHP error reporting
curl http://localhost/sprint/test_file.php

# Run comprehensive tests
npm test
```

### 2. Code Quality Standards
- Gunakan `declare(strict_types=1);` di baris kedua setiap file PHP
- Implement error handling dengan try-catch
- Gunakan prepared statements untuk database
- Validasi input dengan `filter_input()` atau `htmlspecialchars()`
- Ikuti PSR-12 coding standards

### 3. Security Implementation
- **Authentication**: Selalu gunakan `AuthHelper::validateSession()`
- **Database**: Gunakan PDO dengan prepared statements
- **Input Validation**: Sanitasi semua user input
- **Session Management**: Gunakan `SessionManager` class
- **Error Handling**: Production mode tidak menampilkan error details

### 4. API Development Pattern
```php
<?php
declare(strict_types=1);

// Standard API pattern
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Authentication check
if (!AuthHelper::validateSession()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Database connection
$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';unix_socket=' . DB_SOCKET,
    DB_USER, 
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        // Handle GET request
        break;
    case 'POST':
        // Handle POST request
        break;
}
?>
```

### 5. Frontend Development Pattern
```php
<?php
declare(strict_types=1);

// Standard page pattern
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Authentication check
if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}

$page_title = 'Page Title';
include __DIR__ . '/../includes/components/header.php';
?>

<!-- Page content -->
<div class="container mt-4">
    <!-- Content here -->
</div>

<?php include __DIR__ . '/../includes/components/footer.php'; ?>
```

## 🐛 Debugging Workflow

### 1. PHP Errors
```bash
# Check PHP error reporting
curl http://localhost/sprint/problematic_file.php

# Check logs
tail -f /opt/lampp/logs/php_errors.log
tail -f /opt/lampp/logs/error_log
```

### 2. Database Issues
```bash
# Test database connection
php -r "
require_once 'core/config.php';
try {
    \$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    echo 'Database connection successful';
} catch (PDOException \$e) {
    echo 'Database error: ' . \$e->getMessage();
}
"
```

### 3. API Testing
```bash
# Test API endpoints
curl -X GET http://localhost/sprint/api/personil.php
curl -X POST http://localhost/sprint/api/personil.php -d "name=test"
```

### 4. Frontend Issues
- Gunakan browser developer tools
- Check console untuk JavaScript errors
- Verify network requests
- Test responsive design dengan berbagai viewport

## 🧪 Testing Workflow

### 1. Automated Testing
```bash
# Run comprehensive Puppeteer tests
npm test

# Check test results
cat comprehensive-test-report.json
```

### 2. Manual Testing Checklist
- [ ] Login functionality (valid/invalid credentials)
- [ ] Main dashboard loading
- [ ] Navigation to all modules
- [ ] CRUD operations for each module
- [ ] API endpoint responses
- [ ] Responsive design on mobile/tablet/desktop
- [ ] Error handling and validation

### 3. Performance Testing
```bash
# Check page load times
curl -w "@curl-format.txt" http://localhost/sprint/pages/main.php

# Monitor database performance
mysql -u root -p -e "SHOW PROCESSLIST;"
```

## 🚀 Deployment Workflow

### 1. Pre-deployment Checklist
- [ ] All tests passing
- [ ] Error reporting set to production mode
- [ ] Security review completed
- [ ] Database backup created
- [ ] Configuration verified

### 2. Production Configuration
```php
// Set development mode to false
define('DEVELOPMENT_MODE', false);

// Disable error display
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
```

### 3. Post-deployment
- [ ] Verify application functionality
- [ ] Monitor error logs
- [ ] Check performance metrics
- [ ] Test user workflows

## 📝 Common Issues & Solutions

### PHP Syntax Errors
- **Issue**: `declare(strict_types=1)` positioning
- **Solution**: Must be second line after `<?php`
- **Prevention**: Use code templates and linters

### Database Connection Issues
- **Issue**: PDO connection failures
- **Solution**: Check DB_HOST, DB_NAME, credentials
- **Prevention**: Use connection retry logic

### Authentication Issues
- **Issue**: Session validation failures
- **Solution**: Check SessionManager and AuthHelper
- **Prevention**: Implement session timeout handling

### API Issues
- **Issue**: 404 errors or malformed responses
- **Solution**: Verify .htaccess routing and JSON format
- **Prevention**: Use API documentation and testing

### Responsive Design Issues
- **Issue**: Mobile layout problems
- **Solution**: Check CSS media queries and viewport meta
- **Prevention**: Test on multiple devices

## 🔄 Maintenance Workflow

### Daily Tasks
- Monitor error logs
- Check application performance
- Verify backup integrity

### Weekly Tasks
- Update security patches
- Review user feedback
- Optimize database queries

### Monthly Tasks
- Security audit
- Performance analysis
- Feature evaluation

## 📊 Monitoring & Analytics

### Key Metrics
- Page load times
- Error rates
- User engagement
- Database performance
- API response times

### Monitoring Tools
- PHP error logs
- Apache access logs
- Database slow query log
- Custom analytics dashboard

---

## 🎯 AI Assistance Guidelines

### When to Ask for AI Help
- Complex debugging scenarios
- Code optimization suggestions
- Security vulnerability assessment
- Performance improvement recommendations
- Best practices implementation

### What to Provide AI
- Complete error messages
- Relevant code snippets
- Expected vs actual behavior
- Environment details
- Steps already taken

### Expected AI Output
- Root cause analysis
- Code solutions with explanations
- Best practices recommendations
- Testing suggestions
- Prevention strategies

---

*This workflow should be followed for all SPRIN application development and maintenance tasks.*
