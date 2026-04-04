<?php
declare(strict_types=1);
/**
 * SPRIN Security Headers Implementation
 * PHP 8.2+ compatible security headers
 */

class SecurityHeaders {
    private array $headers = [];
    
    public function __construct() {
        $this->setDefaultHeaders();
    }
    
    private function setDefaultHeaders(): void {
        $this->headers = [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => $this->buildCSP(),
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Permissions-Policy' => $this->buildPermissionsPolicy()
        ];
    }
    
    private function buildCSP(): string {
        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
            "img-src 'self' data: https:",
            "font-src 'self' https://cdn.jsdelivr.net",
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'"
        ];
        
        return implode('; ', $directives);
    }
    
    private function buildPermissionsPolicy(): string {
        $policies = [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'accelerometer=()'
        ];
        
        return implode(', ', $policies);
    }
    
    public function addHeader(string $name, string $value): self {
        $this->headers[$name] = $value;
        return $this;
    }
    
    public function removeHeader(string $name): self {
        unset($this->headers[$name]);
        return $this;
    }
    
    public function sendHeaders(): void {
        if (!headers_sent()) {
            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }
        }
    }
    
    public function getHeaders(): array {
        return $this->headers;
    }
    
    // Static method for quick implementation
    public static function apply(): void {
        $security = new self();
        $security->sendHeaders();
    }
}

// Usage example:
// SecurityHeaders::apply();

// Or with custom headers:
// $headers = new SecurityHeaders();
// $headers->addHeader('Custom-Security', 'value');
// $headers->sendHeaders();
?>
