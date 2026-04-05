# URL Helper Developer Training

## Training Objectives
After this training, developers will be able to:
1. Understand the URL helper system
2. Use URL helper functions correctly
3. Migrate existing code to use URL helpers
4. Debug URL-related issues
5. Follow best practices for URL management

## Training Modules

### Module 1: Introduction to URL Helpers (15 minutes)

#### What are URL Helpers?
URL helpers are functions that generate consistent URLs throughout the application.

#### Why Use URL Helpers?
- **Consistency**: All URLs follow the same format
- **Maintainability**: Easy to update base URLs
- **Security**: Built-in validation and safe redirects
- **Readability**: Clear intent in code

#### Available Functions
- `base_url()` - Application base URL
- `page_url()` - Application pages
- `api_url()` - API endpoints
- `asset_url()` - Static assets
- `safe_redirect()` - Safe redirects

### Module 2: Basic Usage (20 minutes)

#### Including URL Helper
```php
<?php
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/url_helper.php';
```

#### Basic Examples
```php
// Base URL
echo base_url();                    // http://localhost/sprint

// Page URLs
echo page_url('main.php');         // http://localhost/sprint/pages/main.php
echo page_url('personil.php');     // http://localhost/sprint/pages/personil.php

// API URLs
echo api_url('personil');          // http://localhost/sprint/api/personil
echo api_url('bagian');            // http://localhost/sprint/api/bagian

// Asset URLs
echo asset_url('css/style.css');   // http://localhost/sprint/public/assets/css/style.css
echo asset_url('js/script.js');    // http://localhost/sprint/public/assets/js/script.js
```

### Module 3: Advanced Usage (25 minutes)

#### Safe Redirects
```php
// Instead of:
header('Location: pages/main.php');

// Use:
safe_redirect('main.php');
```

#### URL Validation
```php
$url = 'http://localhost/sprint/pages/main.php';
if (is_valid_url($url)) {
    echo "Valid URL";
} else {
    echo "Invalid URL";
}
```

#### URL Normalization
```php
$urls = [
    'pages/main.php',
    '/pages/main.php',
    '../pages/main.php'
];

foreach ($urls as $url) {
    echo normalize_url($url) . "\\n";
}
// All output: http://localhost/sprint/pages/main.php
```

## Practical Exercises

### Exercise 1: Basic URL Generation
Create a navigation menu using URL helpers.

### Exercise 2: API Integration
Create an API call using URL helpers.

### Exercise 3: Form Handling
Create a form with validation and redirect using URL helpers.

### Exercise 4: Migration
Migrate an existing file to use URL helpers.

## Best Practices

### DO's
✅ Use URL helpers for all URLs
✅ Include url_helper.php after config.php
✅ Use safe_redirect() instead of header()
✅ Validate URLs with is_valid_url()
✅ Test all URLs after changes

### DON'Ts
❌ Hardcode URLs
❌ Use relative paths
❌ Skip URL validation
❌ Use header() for redirects
❌ Mix URL types

## Code Review Checklist
- [ ] URL helpers are used
- [ ] Correct helper function is used
- [ ] URL validation is implemented
- [ ] Safe redirects are used
- [ ] All URLs are tested

## Troubleshooting

### Common Issues

**Issue**: URL not working
**Solution**: Check if url_helper.php is included

**Issue**: Wrong URL format
**Solution**: Use the correct helper function

**Issue**: Redirect not working
**Solution**: Use safe_redirect() instead of header()

**Issue**: API URL not working
**Solution**: Use api_url() instead of page_url()

### Debugging Techniques
```php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test URL generation
var_dump(base_url('test'));
var_dump(page_url('test.php'));
var_dump(api_url('test'));
var_dump(asset_url('test.css'));
```

## Assessment

### Quiz Questions
1. What function should you use for page URLs?
2. How do you include the URL helper?
3. What's the difference between page_url() and api_url()?
4. When should you use safe_redirect()?
5. How do you validate a URL?

### Practical Assessment
1. Create a page with navigation using URL helpers
2. Implement an API call using URL helpers
3. Create a form with validation and redirect
4. Migrate a sample file to use URL helpers
5. Debug URL-related issues

## Resources

### Documentation
- URL Helper Documentation
- API Reference
- Code Examples

### Tools
- Navigation Test Script
- URL Validator
- Migration Helper

### Support
- Code Review Guidelines
- Best Practices Guide
- Troubleshooting Guide

## Follow-up

### Code Review
All code should be reviewed for proper URL helper usage.

### Continuous Learning
- Stay updated with new URL helper features
- Share best practices with team
- Contribute to URL helper improvements

### Feedback
- Provide feedback on training
- Suggest improvements
- Report issues

---

## Training Schedule

### Duration: 2 hours
- Module 1: 15 minutes
- Module 2: 20 minutes
- Module 3: 25 minutes
- Exercises: 30 minutes
- Assessment: 20 minutes

### Prerequisites
- Basic PHP knowledge
- Understanding of web applications
- Familiarity with the SPRIN application

### Materials
- This training guide
- URL helper documentation
- Code examples
- Exercise files

### Follow-up Actions
1. Implement URL helpers in all new code
2. Migrate existing code
3. Participate in code reviews
4. Provide feedback on the system

---

*This training should be updated as the URL helper system evolves.*
