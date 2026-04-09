<?php
/**
 * JWT Authentication Manager
 * Part of Security & Authentication System
 */

class JWTAuth {
    
    private static $secretKey;
    private static $algorithm = 'HS256';
    private static $tokenLifetime = 86400; // 24 hours
    
    /**
     * Initialize with secret key from config
     */
    public static function init() {
        if (!defined('JWT_SECRET')) {
            throw new RuntimeException('JWT_SECRET is not defined. Load core/config.php first.');
        }
        self::$secretKey = JWT_SECRET;
    }
    
    /**
     * Generate JWT token
     */
    public static function generateToken($payload) {
        self::init();
        
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => self::$algorithm
        ]);
        
        $time = time();
        $tokenPayload = array_merge($payload, [
            'iat' => $time, // Issued at
            'exp' => $time + self::$tokenLifetime, // Expiration
            'iss' => 'sprin-system', // Issuer
            'aud' => 'sprin-api' // Audience
        ]);
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($tokenPayload)));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, self::$secretKey, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    /**
     * Verify and decode JWT token
     */
    public static function verifyToken($token) {
        self::init();
        
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return ['valid' => false, 'error' => 'Invalid token format'];
        }
        
        list($base64Header, $base64Payload, $base64Signature) = $parts;
        
        // Verify signature
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, self::$secretKey, true);
        $base64ComputedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        if (!hash_equals($base64Signature, $base64ComputedSignature)) {
            return ['valid' => false, 'error' => 'Invalid signature'];
        }
        
        // Decode payload
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Payload)), true);
        
        if (!$payload) {
            return ['valid' => false, 'error' => 'Invalid payload'];
        }
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return ['valid' => false, 'error' => 'Token expired'];
        }
        
        return ['valid' => true, 'payload' => $payload];
    }
    
    /**
     * Get token from request headers
     */
    public static function getTokenFromHeaders() {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (strpos($authHeader, 'Bearer ') === 0) {
                return substr($authHeader, 7);
            }
        }
        
        // Check query parameter as fallback
        if (isset($_GET['token'])) {
            return $_GET['token'];
        }
        
        return null;
    }
    
    /**
     * Authenticate API request
     */
    public static function authenticate() {
        $token = self::getTokenFromHeaders();
        
        if (!$token) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required',
                'timestamp' => date('c')
            ]);
            exit;
        }
        
        $result = self::verifyToken($token);
        
        if (!$result['valid']) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $result['error'],
                'timestamp' => date('c')
            ]);
            exit;
        }
        
        return $result['payload'];
    }
    
    /**
     * Refresh token
     */
    public static function refreshToken($token) {
        $result = self::verifyToken($token);
        
        if (!$result['valid']) {
            return ['success' => false, 'error' => $result['error']];
        }
        
        // Remove exp and iat from old payload
        $payload = $result['payload'];
        unset($payload['iat']);
        unset($payload['exp']);
        unset($payload['iss']);
        unset($payload['aud']);
        
        // Generate new token
        $newToken = self::generateToken($payload);
        
        return [
            'success' => true,
            'token' => $newToken,
            'expires_in' => self::$tokenLifetime
        ];
    }
    
    /**
     * Set token lifetime
     */
    public static function setTokenLifetime($seconds) {
        self::$tokenLifetime = $seconds;
    }
    
    /**
     * Get token lifetime
     */
    public static function getTokenLifetime() {
        return self::$tokenLifetime;
    }
}

// Auto-initialize
JWTAuth::init();
