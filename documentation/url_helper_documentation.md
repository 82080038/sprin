# URL Helper Documentation

## Overview
The SPRIN application now includes a comprehensive URL helper system to ensure consistent URL generation and management throughout the application.

## URL Helper Functions

### base_url(string $path = ''): string
Generates the base URL for the application.

```php
// Examples
echo base_url();                    // http://localhost/sprint
echo base_url('pages/main.php');   // http://localhost/sprint/pages/main.php
echo base_url('api/personil');     // http://localhost/sprint/api/personil
```

### page_url(string $page): string
Generates URL for application pages.

```php
// Examples
echo page_url('main.php');         // http://localhost/sprint/pages/main.php
echo page_url('personil.php');     // http://localhost/sprint/pages/personil.php
echo page_url('login.php');        // http://localhost/sprint/pages/login.php
```

### api_url(string $endpoint): string
Generates URL for API endpoints.

```php
// Examples
echo api_url('personil');          // http://localhost/sprint/api/personil
echo api_url('bagian');            // http://localhost/sprint/api/bagian
echo api_url('unsur');             // http://localhost/sprint/api/unsur
```

### asset_url(string $asset): string
Generates URL for static assets.

```php
// Examples
echo asset_url('css/style.css');   // http://localhost/sprint/public/assets/css/style.css
echo asset_url('js/script.js');    // http://localhost/sprint/public/assets/js/script.js
echo asset_url('images/logo.png'); // http://localhost/sprint/public/assets/images/logo.png
```

### safe_redirect(string $url, int $status_code = 302): void
Performs safe redirect with URL validation.

```php
// Examples
safe_redirect('pages/main.php');                    // Redirects to main page
safe_redirect(api_url('personil'), 301);           // 301 redirect to API
safe_redirect(page_url('login.php'), 302);         // 302 redirect to login
```

## Usage Guidelines

### 1. Always Use URL Helper Functions
Instead of hardcoding URLs, use the appropriate helper function:

```php
// ❌ Wrong
echo 'http://localhost/sprint/pages/main.php';
header('Location: http://localhost/sprint/login.php');

// ✅ Correct
echo page_url('main.php');
safe_redirect('login.php');
```

### 2. Include URL Helper
Always include the URL helper after the config:

```php
<?php
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/url_helper.php';
```

### 3. Use in Templates
Use URL helpers in HTML templates:

```php
<link href="<?php echo asset_url('css/style.css'); ?>" rel="stylesheet">
<a href="<?php echo page_url('personil.php'); ?>">Personnel</a>
<script src="<?php echo asset_url('js/script.js'); ?>"></script>
```

### 4. Use in API Responses
Use URL helpers in API responses:

```php
echo json_encode([
    'personnel_url' => page_url('personil.php'),
    'api_url' => api_url('personil'),
    'asset_url' => asset_url('css/style.css')
]);
```

### 5. Use in JavaScript
Pass URLs to JavaScript:

```php
<script>
const apiUrl = '<?php echo api_url('personil'); ?>';
const pageUrl = '<?php echo page_url('main.php'); ?>';
</script>
```

## Migration Guide

### From Hardcoded URLs
Replace hardcoded URLs with helper functions:

```php
// Before
echo 'http://localhost/sprint/pages/main.php';
echo '/sprin/api/personil.php';
echo 'public/assets/css/style.css';

// After
echo page_url('main.php');
echo api_url('personil');
echo asset_url('css/style.css');
```

### From Relative Paths
Replace relative paths with absolute URLs:

```php
// Before
echo '../pages/personil.php';
echo '../../api/bagian.php';
echo './assets/js/script.js';

// After
echo page_url('personil.php');
echo api_url('bagian');
echo asset_url('js/script.js');
```

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

## Troubleshooting

### Common Issues

1. **URL Not Working**: Check if the helper function is included
2. **Wrong URL Type**: Use the correct helper function (page_url vs api_url)
3. **Relative Path Issues**: Use absolute URLs with helpers
4. **Redirect Not Working**: Use safe_redirect() instead of header()

### Debugging

Enable error reporting to see URL-related issues:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test URL generation
echo base_url('test');
echo page_url('test.php');
echo api_url('test');
echo asset_url('test.css');
```

## Integration Examples

### Navigation Menu
```php
<nav>
    <a href="<?php echo base_url(); ?>">Home</a>
    <a href="<?php echo page_url('main.php'); ?>">Dashboard</a>
    <a href="<?php echo page_url('personil.php'); ?>">Personnel</a>
    <a href="<?php echo page_url('bagian.php'); ?>">Units</a>
</nav>
```

### API Integration
```php
<script>
$.ajax({
    url: '<?php echo api_url('personil'); ?>',
    method: 'GET',
    success: function(data) {
        window.location.href = '<?php echo page_url('main.php'); ?>';
    }
});
</script>
```

### Asset Loading
```php
<link href="<?php echo asset_url('css/bootstrap.min.css'); ?>" rel="stylesheet">
<script src="<?php echo asset_url('js/jquery.min.js'); ?>"></script>
<img src="<?php echo asset_url('images/logo.png'); ?>" alt="Logo">
```

## Security Considerations

1. **URL Validation**: Always validate URLs with `is_valid_url()`
2. **Safe Redirects**: Use `safe_redirect()` to prevent open redirects
3. **Input Sanitization**: Sanitize URL parameters
4. **HTTPS**: Use HTTPS in production

## Performance Considerations

1. **Caching**: Cache frequently used URLs
2. **Minimization**: Minimize URL generation calls
3. **Optimization**: Use appropriate URL types

## Future Enhancements

1. **CDN Integration**: Add CDN support
2. **Versioning**: Add URL versioning
3. **Caching**: Implement URL caching
4. **Analytics**: Add URL tracking

---

*This documentation should be updated as new features are added to the URL helper system.*
