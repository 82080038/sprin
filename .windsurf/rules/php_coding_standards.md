---
description: Standar kode PHP untuk aplikasi SPRIN
---

# PHP Coding Standards - SPRIN

## General Rules

### 1. File Structure
- Gunakan `<?php` tag di awal file (tanpa closing tag `?>` untuk pure PHP files)
- Setiap file harus memiliki docblock dengan deskripsi
- Path menggunakan absolute path via `__DIR__`

```php
<?php
/**
 * Description of this file
 */

require_once __DIR__ . '/../core/config.php';
```

### 2. Naming Conventions

**Classes:** PascalCase
```php
class Database { }
class PersonilController extends Controller { }
```

**Methods:** camelCase
```php
public function getPersonilById($id) { }
protected function sanitizeInput($data) { }
```

**Variables:** camelCase atau snake_case (konsisten)
```php
$personilData = [];
$personil_data = [];  // Prefer camelCase untuk consistency dengan codebase
```

**Constants:** UPPER_CASE with underscores
```php
define('DB_HOST', 'localhost');
define('SESSION_LIFETIME', 3600);
```

**Database Tables:** snake_case, plural
```sql
personil, bagian_pimpinan, master_jenis_pegawai
```

**File Names:** snake_case untuk consistency
```
personil_crud.php, auth_helper.php, error_handler.php
```

### 3. Indentation & Formatting
- Indentasi: 4 spaces (NO tabs)
- Line length: Max 120 characters
- Brace style: K&R style (brace on same line)

```php
class Example {
    public function method() {
        if ($condition) {
            // code
        } else {
            // code
        }
    }
}
```

### 4. Documentation

**Class Docblock:**
```php
/**
 * Base Model Class - Part of MVC Architecture
 * All models should extend this class
 */
abstract class Model {
```

**Method Docblock:**
```php
/**
 * Find record by ID
 * @param int $id The record ID
 * @return array|null The record data or null
 */
public function find($id) {
```

### 5. Security Best Practices

**Input Sanitization:**
```php
protected function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map([$this, 'sanitizeInput'], $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
```

**SQL Injection Prevention:**
- SELALU gunakan prepared statements
- JANGAN PERNAH concatenate user input ke SQL

```php
// ✅ BENAR
$sql = "SELECT * FROM personil WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);

// ❌ SALAH - JANGAN
$sql = "SELECT * FROM personil WHERE id = $id";  // SQL Injection risk!
```

**Password Hashing:**
```php
public static function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
}
```

**Output Escaping:**
```php
// HTML output
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// JSON output
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
```

### 6. Error Handling

**Try-Catch Pattern:**
```php
try {
    $db = Database::getInstance();
    $result = $db->query($sql, $params);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    throw new Exception("Database operation failed");
}
```

**Global Error Handler:**
Aplikasi sudah memiliki error handler di `core/error_handler.php`. Gunakan:
```php
require_once __DIR__ . '/error_handler.php';
ErrorHandler::init();
```

### 7. MVC Pattern

**Model:** Business logic & database operations
```php
class Personil extends Model {
    protected $table = 'personil';
    protected $fillable = ['nrk', 'nama_lengkap', 'id_pangkat'];
}
```

**Controller:** Handle requests & responses
```php
class PersonilController extends Controller {
    public function index() {
        $personil = $this->db->fetchAll("SELECT * FROM personil");
        $this->success($personil);
    }
}
```

**View:** Presentation layer (pages/)
```php
// Di file pages/personil.php
include __DIR__ . '/../includes/components/header.php';
// ... content
include __DIR__ . '/../includes/components/footer.php';
```

### 8. Database Operations

**Singleton Pattern (Required):**
```php
require_once __DIR__ . '/../core/Database.php';

// Get database instance
$db = Database::getInstance();
$pdo = $db->getConnection();

// Use for queries
$stmt = $pdo->prepare("SELECT * FROM personil WHERE id = ?");
$stmt->execute([$id]);
$result = $stmt->fetchAll();
```

**JANGAN gunakan direct PDO connection:**
```php
// ❌ SALAH - JANGAN
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
$pdo = new PDO($dsn, DB_USER, DB_PASS);  // Bypass singleton!
```

**Database Connection Features:**
- Primary: Unix socket `/opt/lampp/var/mysql/mysql.sock` (XAMPP)
- Fallback: TCP connection jika socket gagal
- PDO Settings: ERRMODE_EXCEPTION, FETCH_ASSOC, EMULATE_PREPARES=false
- Timezone: `+07:00` (Asia/Jakarta)
- SQL Mode: `STRICT_ALL_TABLES`

**CRUD Operations via Model:**
```php
$personil = new Personil();

// Create
$id = $personil->create($data);

// Read
$row = $personil->find($id);
$all = $personil->all();

// Update
$personil->update($id, $data);

// Soft Delete
$personil->delete($id);
```

### 9. API Response Format

**Standardized Response Structure:**
```php
// Success Response
header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'success' => true,
    'message' => 'Operation successful',
    'data' => $data,
    'timestamp' => date('c')
]);

// Error Response
header('Content-Type: application/json; charset=UTF-8');
if (ENVIRONMENT === 'development') {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'timestamp' => date('c')
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to process request',
        'timestamp' => date('c')
    ]);
}
```

**Required Headers:**
```php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
```

**Error Handling Pattern:**
```php
// Production: Hide detailed errors
if (ENVIRONMENT !== 'development') {
    error_reporting(0);
    ini_set('display_errors', 0);
}
```

### 10. Session Management

**Check Authentication:**
```php
if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}
```

**Start Session:**
```php
session_start();
// HARUS dipanggil sebelum output apa pun
```

### 11. Configuration

**Access Config:**
```php
require_once __DIR__ . '/../core/config.php';

// Gunakan konstanta yang sudah didefinisikan
echo DB_HOST;
echo BASE_URL;
```

### 12. URL Generation

**Gunakan Helper Functions:**
```php
url('pages/personil.php');           // http://localhost/sprint/pages/personil.php
asset_url('css/style.css');          // http://localhost/sprint/assets/css/style.css
api_url('personil_list.php');        // http://localhost/sprint/api/v1/personil_list.php
```

### 13. File Uploads

**Security Checklist:**
- Validate file type (whitelist)
- Validate file size
- Rename file (jangan gunakan nama asli)
- Store outside web root jika sensitive

```php
$allowedTypes = ['image/jpeg', 'image/png'];
$maxSize = 2 * 1024 * 1024; // 2MB

if (!in_array($_FILES['foto']['type'], $allowedTypes)) {
    throw new Exception('Invalid file type');
}

$newName = uniqid() . '_' . basename($_FILES['foto']['name']);
$uploadPath = __DIR__ . '/../uploads/' . $newName;
```

### 14. Logging

**Gunakan error_log:**
```php
error_log("[DEBUG] User action: " . $action);
error_log("[ERROR] Database failed: " . $e->getMessage());
```

**Audit Trail:**
Gunakan `AuditTrail` class untuk operasi penting:
```php
require_once __DIR__ . '/../core/AuditTrail.php';
AuditTrail::log('CREATE_PERSONIL', "Created personil ID: $id", $userId);
```

### 15. PHP 8+ Features

**Match Expression:**
```php
$error_type = match($severity) {
    E_ERROR => 'ERROR',
    E_WARNING => 'WARNING',
    E_NOTICE => 'NOTICE',
    default => 'UNKNOWN'
};
```

**Nullsafe Operator:**
```php
$personil?->nama_lengkap ?? 'Unknown';
```

**Named Arguments:**
```php
$this->json(data: $result, statusCode: 201);
```

## Forbidden Patterns

❌ **JANGAN:**
- Gunakan `eval()` atau `exec()` dengan user input
- Simpan password dalam plaintext
- Tampilkan error detail ke user (production)
- Gunakan `mysql_*` functions (deprecated)
- Akses `$_GET`/`$_POST` langsung tanpa sanitasi
- Gunakan `==` untuk perbandingan (gunakan `===`)
- Biarkan session ID di URL

✅ **LAKUKAN:**
- Gunakan prepared statements
- Hash password dengan Argon2id
- Log errors, tampilkan pesan user-friendly
- Gunakan PDO untuk database
- Sanitize semua input
- Gunakan strict comparison (`===`)
- Regenerate session ID setelah login

## Code Review Checklist

Sebelum commit, pastikan:
- [ ] Tidak ada hardcoded credentials
- [ ] Semua SQL menggunakan prepared statements
- [ ] Output di-escape (XSS prevention)
- [ ] Error handling lengkap
- [ ] Docblocks untuk classes dan methods
- [ ] Consistent naming conventions
- [ ] No debug code (var_dump, echo debug)
- [ ] Input validation lengkap
