---
description: API development and management standards
---

# API Development Standards - SPRIN

## Overview

Standar pengembangan API untuk aplikasi SPRIN v1.2.0 dengan fokus pada consistency, security, dan performance.

## API Architecture

### Current API Structure (v1.2.0)
```
api/
├── personil_crud.php          # CRUD operations
├── personil_list.php          # List dengan pagination
├── personil_detail.php        # Get single record
├── jabatan_crud.php           # Jabatan management
├── unsur_crud.php             # Unsur management
├── bagian_crud.php            # Bagian management
├── user_management.php        # User management
├── backup_api.php             # Backup operations
├── calendar_api.php          # Calendar operations
├── export_personil.php        # Export functionality
├── bulk_update_personil.php   # Bulk operations
├── report_api.php             # Reporting system
├── advanced_search.php       # Advanced search
├── unsur_stats.php            # Statistics
├── health_check.php           # Health monitoring
└── test_connection.php        # Connection test
```

## API Standards

### 1. Request/Response Format

#### Standard Response Structure
```json
{
    "success": true|false,
    "message": "Human readable message",
    "data": {
        // Response data
    },
    "meta": {
        "total": 100,
        "page": 1,
        "per_page": 10,
        "total_pages": 10
    },
    "errors": [
        {
            "field": "field_name",
            "message": "Error message"
        }
    ]
}
```

#### HTTP Status Codes
- `200 OK` - Request successful
- `201 Created` - Resource created
- `400 Bad Request` - Invalid input
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Access denied
- `404 Not Found` - Resource not found
- `500 Internal Server Error` - Server error

### 2. Authentication & Security

#### Session Validation
```php
<?php
require_once __DIR__ . '/../core/auth_helper.php';

// Check authentication
if (!AuthHelper::validateSession()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// CSRF protection
if (!AuthHelper::validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}
```

#### Input Validation
```php
<?php
// Sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Validate required fields
$requiredFields = ['nama', 'nrp'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode([
            'success' => false,
            'message' => "Field {$field} is required"
        ]);
        exit;
    }
}
```

### 3. Database Operations

#### Standard CRUD Pattern
```php
<?php
class Database {
    private $pdo;
    
    public function __construct() {
        $this->pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    }
    
    public function create($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->pdo->lastInsertId();
    }
    
    public function read($table, $id = null, $filters = []) {
        $sql = "SELECT * FROM {$table}";
        $params = [];
        
        if ($id) {
            $sql .= " WHERE id = ?";
            $params[] = $id;
        } elseif (!empty($filters)) {
            $conditions = [];
            foreach ($filters as $key => $value) {
                $conditions[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $id ? $stmt->fetch() : $stmt->fetchAll();
    }
    
    public function update($table, $id, $data) {
        $setClause = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $setClause[] = "{$key} = ?";
            $params[] = $value;
        }
        
        $params[] = $id;
        $sql = "UPDATE {$table} SET " . implode(', ', $setClause) . " WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($table, $id, $soft = true) {
        if ($soft) {
            $sql = "UPDATE {$table} SET deleted_at = NOW() WHERE id = ?";
        } else {
            $sql = "DELETE FROM {$table} WHERE id = ?";
        }
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
}
```

### 4. Pagination Implementation

#### Standard Pagination
```php
<?php
function getPaginatedResults($table, $page = 1, $perPage = 10, $filters = []) {
    $offset = ($page - 1) * $perPage;
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM {$table}";
    if (!empty($filters)) {
        $conditions = [];
        foreach ($filters as $key => $value) {
            $conditions[] = "{$key} = ?";
        }
        $countSql .= " WHERE " . implode(' AND ', $conditions);
    }
    
    $stmt = $pdo->prepare($countSql);
    $stmt->execute(array_values($filters));
    $total = $stmt->fetch()['total'];
    
    // Get paginated data
    $sql = "SELECT * FROM {$table}";
    if (!empty($filters)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    $sql .= " LIMIT {$perPage} OFFSET {$offset}";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($filters));
    $data = $stmt->fetchAll();
    
    return [
        'data' => $data,
        'meta' => [
            'total' => (int)$total,
            'page' => (int)$page,
            'per_page' => (int)$perPage,
            'total_pages' => ceil($total / $perPage)
        ]
    ];
}
```

### 5. Error Handling

#### Comprehensive Error Handling
```php
<?php
try {
    // Database operations
    $result = $db->create('personil', $data);
    
    echo json_encode([
        'success' => true,
        'message' => 'Personil created successfully',
        'data' => ['id' => $result]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'errors' => [
            ['field' => 'database', 'message' => $e->getMessage()]
        ]
    ]);
    
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'errors' => [
            ['field' => 'general', 'message' => $e->getMessage()]
        ]
    ]);
}
```

### 6. API Documentation

#### Endpoint Documentation Template
```php
<?php
/**
 * Personil CRUD API
 * 
 * Endpoints:
 * - POST /api/personil_crud.php - Create/Update/Delete personil
 * 
 * Parameters:
 * - action: create|update|delete
 * - id: (required for update/delete)
 * - nama: (required for create/update)
 * - nrp: (required for create/update)
 * - bagian_id: (optional)
 * 
 * Responses:
 * - 200: Success with data
 * - 400: Bad request
 * - 401: Unauthorized
 * - 404: Not found
 * - 500: Server error
 * 
 * Examples:
 * curl -X POST "http://localhost/sprint/api/personil_crud.php" \
 *   -d "action=create&nama=Test&nrp=12345"
 */
```

## API Testing Standards

### 1. Unit Testing
```php
<?php
// Test API endpoints
function testPersonilCRUD() {
    // Test create
    $response = curl_post('/api/personil_crud.php', [
        'action' => 'create',
        'nama' => 'Test Personil',
        'nrp' => 'TEST001'
    ]);
    
    assert($response['success'] === true);
    assert(isset($response['data']['id']));
    
    // Test read
    $response = curl_get('/api/personil_detail.php?id=' . $response['data']['id']);
    assert($response['success'] === true);
    assert($response['data']['nama'] === 'Test Personil');
    
    // Test update
    $response = curl_post('/api/personil_crud.php', [
        'action' => 'update',
        'id' => $response['data']['id'],
        'nama' => 'Updated Personil'
    ]);
    
    assert($response['success'] === true);
    
    // Test delete
    $response = curl_post('/api/personil_crud.php', [
        'action' => 'delete',
        'id' => $response['data']['id']
    ]);
    
    assert($response['success'] === true);
}
```

### 2. Integration Testing
```bash
# Test API health
curl -s "http://localhost/sprint/api/health_check.php" | python3 -m json.tool

# Test authentication
curl -s -X POST "http://localhost/sprint/api/personil_crud.php" \
  -d "action=create" | python3 -m json.tool

# Test pagination
curl -s "http://localhost/sprint/api/personil_list.php?page=1&per_page=5" | python3 -m json.tool
```

## Performance Standards

### 1. Response Time Targets
- Database queries: < 100ms
- API endpoints: < 500ms
- File uploads: < 2s
- Export operations: < 30s

### 2. Database Optimization
```php
<?php
// Use indexes for frequently queried columns
$sql = "SELECT * FROM personil WHERE bagian_id = ? AND deleted_at IS NULL";

// Use prepared statements
$stmt = $pdo->prepare($sql);
$stmt->execute([$bagianId]);

// Limit results for pagination
$sql .= " LIMIT ? OFFSET ?";
```

### 3. Caching Strategy
```php
<?php
// Simple caching for read operations
$cacheKey = "personil_list_{$page}_{$perPage}";
$cachedData = apcu_fetch($cacheKey);

if ($cachedData === false) {
    $data = getPersonilList($page, $perPage);
    apcu_store($cacheKey, $data, 300); // Cache for 5 minutes
} else {
    $data = $cachedData;
}
```

## Security Standards

### 1. Input Validation
- Validate all input data
- Sanitize user input
- Use prepared statements
- Implement rate limiting

### 2. Authentication
- Session-based authentication
- CSRF token validation
- Role-based access control
- Session timeout management

### 3. Data Protection
- Encrypt sensitive data
- Use HTTPS in production
- Implement data masking
- Regular security audits

## API Versioning

### 1. Version Strategy
- URL-based versioning: `/api/v1/personil`
- Backward compatibility
- Deprecation notices
- Migration support

### 2. Version Implementation
```php
<?php
// Version detection
$version = $_SERVER['HTTP_X_API_VERSION'] ?? 'v1';

// Route to appropriate version
switch ($version) {
    case 'v1':
        require_once 'v1/personil_crud.php';
        break;
    case 'v2':
        require_once 'v2/personil_crud.php';
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unsupported API version']);
}
```

## Monitoring & Logging

### 1. API Logging
```php
<?php
// Log API requests
function logAPIRequest($endpoint, $method, $params, $response, $duration) {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'endpoint' => $endpoint,
        'method' => $method,
        'params' => $params,
        'response_code' => http_response_code(),
        'duration_ms' => $duration,
        'user_id' => $_SESSION['user_id'] ?? null
    ];
    
    file_put_contents(
        __DIR__ . '/../logs/api_activity.log',
        json_encode($logData) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}
```

### 2. Performance Monitoring
```bash
# Monitor API response times
curl -w "@curl-format.txt" -o /dev/null -s "http://localhost/sprint/api/personil_list.php"

# Check API health
curl -s "http://localhost/sprint/api/health_check.php" | python3 -m json.tool
```

## Best Practices

### 1. Code Organization
- Single responsibility principle
- Consistent naming conventions
- Proper error handling
- Comprehensive documentation

### 2. Performance
- Minimize database queries
- Use appropriate indexes
- Implement caching
- Optimize response sizes

### 3. Security
- Validate all inputs
- Use prepared statements
- Implement authentication
- Regular security updates

### 4. Maintainability
- Code comments and documentation
- Unit test coverage
- Code review process
- Regular refactoring
