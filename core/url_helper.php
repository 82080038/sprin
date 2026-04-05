<?php
/**
 * core/url_helper.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

declare(strict_types=1);

/**
 * URL Helper Functions
 * Provides consistent URL generation and validation
 */

/**
 * Generate base URL
 */
function base_url(string $path = ''): string {
    $base_url = BASE_URL ?? 'http://localhost/sprint';
    return rtrim($base_url, '/') . '/' . ltrim($path, '/');
}

/**
 * Generate URL for pages
 */
function page_url(string $page = ''): string {
    return base_url('pages/' . ltrim($page, '/'));
}

/**
 * Generate API URL
 */
function api_url(string $endpoint = ''): string {
    return base_url('api/' . ltrim($endpoint, '/'));
}

/**
 * Generate asset URL
 */
function asset_url(string $asset = ''): string {
    return base_url('public/assets/' . ltrim($asset, '/'));
}

/**
 * Validate and normalize URL
 */
function normalize_url(string $url): string {
    // Remove double slashes
    $url = preg_replace('/([^:])\/\//', '$1/', $url);

    // Remove spaces
    $url = str_replace(' ', '', $url);

    // Ensure proper format
    if (!str_starts_with($url, 'http') && !str_starts_with($url, '/')) {
        $url = base_url($url);
    }

    return $url;
}

/**
 * Check if URL is valid
 */
function is_valid_url(string $url): bool {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Safe redirect with URL validation
 */
function safe_redirect(string $path, int $status_code = 302): void {
    $url = base_url($path);

    // Validate URL
    if (!is_valid_url($url)) {
        throw new InvalidArgumentException("Invalid redirect URL: {$url}");
    }

    // Prevent open redirects
    if (str_contains($url, '://') && !str_starts_with($url, base_url())) {
        throw new InvalidArgumentException("External redirect not allowed: {$url}");
    }

    // Perform redirect
    header("Location: {$url}", true, $status_code);
    exit;
}

/**
 * Get current URL with query parameters
 */
function current_url(array $params = []): string {
    $url = getCurrentUrl();

    if (!empty($params)) {
        $query = http_build_query($params);
        $separator = str_contains($url, '?') ? '&' : '?';
        $url .= $separator . $query;
    }

    return $url;
}

/**
 * Generate URL with query string
 */
function url_with_params(string $path, array $params = []): string {
    $url = base_url($path);

    if (!empty($params)) {
        $query = http_build_query($params);
        $url .= '?' . $query;
    }

    return $url;
}

/**
 * Check if URL is external
 */
function is_external_url(string $url): bool {
    $base_host = parse_url(BASE_URL, PHP_URL_HOST);
    $url_host = parse_url($url, PHP_URL_HOST);

    return $url_host && $url_host !== $base_host;
}

/**
 * Generate secure URL (HTTPS)
 */
function secure_url(string $path = ''): string {
    $base_url = str_replace('http://', 'https://', BASE_URL);
    return rtrim($base_url, '/') . '/' . ltrim($path, '/');
}

/**
 * Get URL scheme
 */
function get_url_scheme(): string {
    return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
}

/**
 * Get URL host
 */
function get_url_host(): string {
    return $_SERVER['HTTP_HOST'] ?? 'localhost';
}

/**
 * Get URL port
 */
function get_url_port(): int {
    return (int) ($_SERVER['SERVER_PORT'] ?? 80);
}

/**
 * Get URL path
 */
function get_url_path(): string {
    return $_SERVER['REQUEST_URI'] ?? '/';
}

/**
 * Generate back URL (referer or default)
 */
function get_back_url(string $default = ''): string {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    if (empty($referer) || is_external_url($referer)) {
        return base_url($default);
    }

    return $referer;
}

/**
 * Generate pagination URL
 */
function pagination_url(string $base_path, int $page, array $params = []): string {
    $params['page'] = $page;
    return url_with_params($base_path, $params);
}

/**
 * Generate sort URL
 */
function sort_url(string $base_path, string $field, string $direction = 'asc', array $params = []): string {
    $params['sort'] = $field;
    $params['order'] = $direction;
    return url_with_params($base_path, $params);
}

/**
 * Generate filter URL
 */
function filter_url(string $base_path, array $filters = []): string {
    return url_with_params($base_path, $filters);
}

/**
 * Generate download URL
 */
function download_url(string $file_path): string {
    return base_url('download.php?file=' . urlencode($file_path));
}

/**
 * Generate upload URL
 */
function upload_url(string $upload_type = ''): string {
    $path = 'upload.php';
    if ($upload_type) {
        $path .= '?type=' . urlencode($upload_type);
    }
    return base_url($path);
}

/**
 * Generate API endpoint URL with version
 */
function api_endpoint_url(string $endpoint, string $version = 'v1'): string {
    return base_url("api/{$version}/" . ltrim($endpoint, '/'));
}

/**
 * Generate webhook URL
 */
function webhook_url(string $webhook_name): string {
    return base_url("webhooks/{$webhook_name}");
}

/**
 * Generate callback URL
 */
function callback_url(string $callback_name): string {
    return base_url("callbacks/{$callback_name}");
}

/**
 * Generate public URL (for external access)
 */
function public_url(string $path = ''): string {
    $base_url = BASE_URL;

    // Replace localhost with actual domain if needed
    if (str_contains($base_url, 'localhost')) {
        $base_url = str_replace('localhost', $_SERVER['HTTP_HOST'] ?? 'localhost', $base_url);
    }

    return rtrim($base_url, '/') . '/' . ltrim($path, '/');
}

/**
 * Generate admin URL
 */
function admin_url(string $path = ''): string {
    return base_url('admin/' . ltrim($path, '/'));
}

/**
 * Generate API documentation URL
 */
function api_docs_url(string $path = ''): string {
    return base_url('api-docs/' . ltrim($path, '/'));
}

/**
 * Generate assets URL (alias for asset_url)
 */
function assets_url(string $asset = ''): string {
    return asset_url($asset);
}

/**
 * Generate images URL
 */
function images_url(string $image = ''): string {
    return asset_url('images/' . ltrim($image, '/'));
}

/**
 * Generate CSS URL
 */
function css_url(string $css_file = ''): string {
    return asset_url('css/' . ltrim($css_file, '/'));
}

/**
 * Generate JS URL
 */
function js_url(string $js_file = ''): string {
    return asset_url('js/' . ltrim($js_file, '/'));
}

/**
 * Generate fonts URL
 */
function fonts_url(string $font_file = ''): string {
    return asset_url('fonts/' . ltrim($font_file, '/'));
}

/**
 * Generate vendor URL
 */
function vendor_url(string $vendor_path = ''): string {
    return asset_url('vendor/' . ltrim($vendor_path, '/'));
}

// Backward compatibility aliases
if (!function_exists('url')) {
    function url(string $path = ''): string {
        return base_url($path);
    }
}

if (!function_exists('site_url')) {
    function site_url(string $path = ''): string {
        return base_url($path);
    }
}
?>
