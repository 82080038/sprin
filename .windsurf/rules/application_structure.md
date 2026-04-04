# Application Structure Rules

## 📁 Directory Structure Guidelines

### Core Directories
```
sprint/
├── api/                    # API endpoints (46 files)
│   ├── *.php              # All API files
│   ├── auth_helper.php    # Authentication helper
│   ├── health_check.php   # System health monitoring
│   └── security_middleware.php # Security features
├── core/                   # Core system files (23 files)
│   ├── config.php         # Main configuration
│   ├── Database.php       # Database connection
│   ├── AuthHelper.php     # Authentication
│   └── *.php              # Core utilities
├── pages/                  # Application pages (12 files)
│   ├── *.php              # Main application pages
│   └── login.php          # Authentication page
├── public/                 # Static assets
│   ├── assets/            # CSS, JS, images
│   └── api-docs/          # API documentation
├── database/              # Database files (7 files)
│   ├── *.sql              # Database schema
│   └── migrations/        # Migration files
├── tests/                  # Test suite (25 files)
│   ├── jest/              # Unit tests
│   ├── playwright/        # E2E tests
│   └── utils/             # Test utilities
├── security/              # Security utilities (3 files)
│   ├── create_default_user.php
│   └── *.php              # Security tools
├── config/                # Configuration files
│   └── package*.json      # NPM configuration
├── docs/documentation/    # All documentation (13 files)
│   └── *.md               # Documentation files
├── logs/                  # Log files
├── backups/               # Database backups
└── error_pages/           # Custom error pages
```

## 📝 File Naming Conventions

### PHP Files
- **API endpoints**: `snake_case.php` (e.g., `personil_list.php`)
- **Core files**: `PascalCase.php` (e.g., `Database.php`)
- **Pages**: `lowercase.php` (e.g., `personil.php`)
- **Classes**: `PascalCase.php` (e.g., `AuthHelper.php`)

### JavaScript Files
- **Client libraries**: `kebab-case.js` (e.g., `f2e-client.js`)
- **Test files**: `kebab-case.spec.js` (e.g., `login.spec.js`)
- **Utilities**: `kebab-case.js` (e.g., `url-helper.js`)

### Documentation Files
- **Main docs**: `PascalCase.md` (e.g., `DEVELOPMENT_README.md`)
- **Workflows**: `snake_case.md` (e.g., `application_health_check.md`)
- **Rules**: `snake_case.md` (e.g., `application_structure.md`)

## 🔧 Code Organization Rules

### API Structure
```php
<?php
/**
 * File Description
 * Purpose and functionality
 */

// Headers first
header('Content-Type: application/json');

// Dependencies
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/auth_helper.php';

// Initialize
$auth = APIAuth::getInstance();

// Business logic
// ...
```

### Class Structure
```php
<?php
/**
 * Class Description
 * Purpose and usage
 */

class ClassName {
    private $property;
    
    public function __construct() {
        // Constructor
    }
    
    public function methodName() {
        // Method implementation
    }
}
```

### Page Structure
```php
<?php
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/includes/components/header.php';
?>
<!-- HTML content -->
<?php
require_once __DIR__ . '/includes/components/footer.php';
?>
```

## 🚫 Files to Exclude

### Development Files
- `test_*.php` - Test files
- `debug_*.php` - Debug files
- `*_test.php` - Test scripts
- `*_DRAFT.md` - Draft documentation

### Temporary Files
- `*.tmp` - Temporary files
- `*.cache` - Cache files
- `*.log` - Log files (in production)
- `*.swp` - Editor swap files

### Build Artifacts
- `node_modules/` - NPM dependencies
- `test-results/` - Test results
- `playwright-report/` - E2E test reports
- `coverage/` - Code coverage reports

## 📋 File Size Guidelines

### Maximum File Sizes
- **PHP files**: < 50KB (split if larger)
- **JavaScript files**: < 100KB (use modules if larger)
- **CSS files**: < 50KB (use components if larger)
- **Documentation**: < 100KB (split if larger)

### Image Optimization
- **Icons**: < 10KB
- **Logos**: < 50KB
- **Photos**: < 500KB (compress if larger)

## 🔍 Quality Standards

### Code Quality
1. **No syntax errors**: All files must pass `php -l`
2. **No deprecated functions**: Use modern PHP features
3. **Proper error handling**: Use try-catch blocks
4. **Input validation**: Sanitize all user inputs
5. **Security**: Follow security best practices

### Documentation Standards
1. **File headers**: Describe purpose and usage
2. **Function comments**: Explain parameters and return values
3. **Class documentation**: Describe purpose and methods
4. **API documentation**: Include examples and error codes

### Testing Standards
1. **Unit tests**: Test individual functions
2. **Integration tests**: Test API endpoints
3. **E2E tests**: Test user workflows
4. **Coverage**: Aim for >80% code coverage

## 🔄 Maintenance Rules

### Weekly Tasks
- Review file sizes
- Check for duplicate files
- Update documentation
- Run test suite

### Monthly Tasks
- Archive old logs
- Update dependencies
- Security audit
- Performance review

### Quarterly Tasks
- Code review
- Structure optimization
- Documentation update
- Backup verification

## 🎯 Best Practices

1. **Consistent naming**: Follow naming conventions
2. **Logical grouping**: Organize files by purpose
3. **Clear documentation**: Document all components
4. **Regular cleanup**: Remove unused files
5. **Version control**: Track all changes
6. **Testing**: Test all components
7. **Security**: Follow security guidelines
8. **Performance**: Optimize for speed

## 📊 Metrics to Track

### File Metrics
- Total file count by type
- Average file size
- Largest files
- Most recently modified

### Code Metrics
- Lines of code
- Cyclomatic complexity
- Test coverage
- Code duplication

### Performance Metrics
- Page load times
- API response times
- Database query times
- Memory usage

## 🚨 Red Flags

### Structure Issues
- Files in wrong directories
- Inconsistent naming
- Missing documentation
- Duplicate functionality

### Performance Issues
- Large file sizes
- Slow loading times
- High memory usage
- Database bottlenecks

### Security Issues
- Hardcoded credentials
- Input validation missing
- Outdated dependencies
- Insecure permissions

## 📞 Support

For structure-related issues:
1. Check this documentation first
2. Review file organization guidelines
3. Consult team lead for major changes
4. Update documentation after changes
