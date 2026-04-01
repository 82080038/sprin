---
description: Skill untuk membuat CRUD API endpoint baru
---

# CRUD API Generator Skill

## Overview

Skill ini digunakan untuk membuat API endpoint CRUD (Create, Read, Update, Delete) baru dengan mengikuti pola yang sudah ada di aplikasi SPRIN.

## Pattern yang Digunakan

### File Structure
```
api/
├── {entity}_crud.php       # Endpoint utama CRUD
├── {entity}_list.php       # List dengan pagination
├── {entity}_detail.php     # Get single record
└── {entity}_delete.php     # Soft delete endpoint
```

### Code Template

#### 1. CRUD Endpoint ({entity}_crud.php)
```php
<?php
/**
 * {Entity} CRUD API
 * Base URL: /api/{entity}_crud.php
 */

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';
require_once __DIR__ . '/../core/Database.php';

// Check authentication
if (!AuthHelper::validateSession()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = Database::getInstance();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            // Validate required fields
            $required = ['field1', 'field2'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field {$field} wajib diisi");
                }
            }
            
            $data = [
                'field1' => $_POST['field1'],
                'field2' => $_POST['field2'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $id = $db->insert('{table}', $data);
            
            echo json_encode([
                'success' => true,
                'message' => '{Entity} berhasil ditambahkan',
                'data' => ['id' => $id]
            ]);
            break;
            
        case 'read':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID wajib disertakan');
            }
            
            $sql = "SELECT * FROM {table} WHERE id = :id AND is_deleted = FALSE";
            $record = $db->fetchOne($sql, ['id' => $id]);
            
            if (!$record) {
                throw new Exception('{Entity} tidak ditemukan');
            }
            
            echo json_encode([
                'success' => true,
                'data' => $record
            ]);
            break;
            
        case 'update':
            $id = $_POST['id'] ?? null;
            if (!$id) {
                throw new Exception('ID wajib disertakan');
            }
            
            $data = [
                'field1' => $_POST['field1'] ?? null,
                'field2' => $_POST['field2'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Remove null values
            $data = array_filter($data, function($v) { return $v !== null; });
            
            $db->update('{table}', $data, "id = :id AND is_deleted = FALSE", ['id' => $id]);
            
            echo json_encode([
                'success' => true,
                'message' => '{Entity} berhasil diupdate'
            ]);
            break;
            
        default:
            throw new Exception('Action tidak valid');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
```

#### 2. List Endpoint ({entity}_list.php)
```php
<?php
/**
 * {Entity} List API with Pagination
 */

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/auth_helper.php';

if (!AuthHelper::validateSession()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = Database::getInstance();

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = min(100, intval($_GET['per_page'] ?? 20));
$offset = ($page - 1) * $perPage;

// Search
$params = [];
$where = "is_deleted = FALSE";

if (!empty($_GET['search'])) {
    $where .= " AND (field1 LIKE :search OR field2 LIKE :search)";
    $params['search'] = '%' . $_GET['search'] . '%';
}

// Filters
if (!empty($_GET['filter_field'])) {
    $where .= " AND filter_field = :filter";
    $params['filter'] = $_GET['filter_field'];
}

// Sort
$allowedSort = ['id', 'field1', 'field2', 'created_at'];
$sortField = in_array($_GET['sort'] ?? '', $allowedSort) ? $_GET['sort'] : 'id';
$sortDir = strtoupper($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

// Count total
$countSql = "SELECT COUNT(*) as total FROM {table} WHERE {$where}";
$total = $db->fetchOne($countSql, $params)['total'] ?? 0;

// Get data
$sql = "SELECT * FROM {table} WHERE {$where} ORDER BY {$sortField} {$sortDir} LIMIT :limit OFFSET :offset";
$params['limit'] = $perPage;
$params['offset'] = $offset;

$data = $db->fetchAll($sql, $params);

echo json_encode([
    'success' => true,
    'data' => $data,
    'pagination' => [
        'current_page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => ceil($total / $perPage),
        'has_next' => $page < ceil($total / $perPage),
        'has_prev' => $page > 1
    ]
]);
```

#### 3. Detail Endpoint ({entity}_detail.php)
```php
<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/auth_helper.php';

if (!AuthHelper::validateSession()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = Database::getInstance();
$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID wajib disertakan']);
    exit;
}

$sql = "SELECT * FROM {table} WHERE id = :id AND is_deleted = FALSE";
$record = $db->fetchOne($sql, ['id' => $id]);

if (!$record) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => '{Entity} tidak ditemukan']);
    exit;
}

echo json_encode(['success' => true, 'data' => $record]);
```

#### 4. Delete Endpoint ({entity}_delete.php)
```php
<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/auth_helper.php';

if (!AuthHelper::validateSession()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = Database::getInstance();
$id = $_POST['id'] ?? $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID wajib disertakan']);
    exit;
}

// Soft delete
$db->softDelete('{table}', "id = :id", ['id' => $id]);

echo json_encode([
    'success' => true,
    'message' => '{Entity} berhasil dihapus'
]);
```

## Usage Examples

### Create API untuk Tabel "proyek"

1. **Create proyek_crud.php:**
```php
<?php
/**
 * Proyek CRUD API
 */
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/auth_helper.php';
require_once __DIR__ . '/../core/Database.php';

if (!AuthHelper::validateSession()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = Database::getInstance();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $required = ['nama_proyek', 'tanggal_mulai'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field {$field} wajib diisi");
                }
            }
            
            $data = [
                'nama_proyek' => $_POST['nama_proyek'],
                'deskripsi' => $_POST['deskripsi'] ?? '',
                'tanggal_mulai' => $_POST['tanggal_mulai'],
                'tanggal_selesai' => $_POST['tanggal_selesai'] ?? null,
                'status' => $_POST['status'] ?? 'planning',
                'is_deleted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $id = $db->insert('proyek', $data);
            
            echo json_encode([
                'success' => true,
                'message' => 'Proyek berhasil ditambahkan',
                'data' => ['id' => $id]
            ]);
            break;
            
        case 'update':
            $id = $_POST['id'] ?? null;
            if (!$id) throw new Exception('ID wajib disertakan');
            
            $data = [
                'nama_proyek' => $_POST['nama_proyek'] ?? null,
                'deskripsi' => $_POST['deskripsi'] ?? null,
                'status' => $_POST['status'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $data = array_filter($data, fn($v) => $v !== null);
            
            $db->update('proyek', $data, "id = :id AND is_deleted = FALSE", ['id' => $id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Proyek berhasil diupdate'
            ]);
            break;
            
        default:
            throw new Exception('Action tidak valid');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
```

2. **Add to Database:**
```sql
CREATE TABLE proyek (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama_proyek VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    tanggal_mulai DATE,
    tanggal_selesai DATE,
    status ENUM('planning', 'ongoing', 'completed', 'cancelled') DEFAULT 'planning',
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

3. **Test API:**
```bash
curl -X POST http://localhost/sprint/api/proyek_crud.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=create&nama_proyek=Operasi A&tanggal_mulai=2026-04-01"
```

## Checklist

Sebelum deploy API baru:
- [ ] Semua field required divalidasi
- [ ] SQL injection prevention (prepared statements)
- [ ] Authentication check (AuthHelper::validateSession)
- [ ] Error handling lengkap
- [ ] Response format JSON konsisten
- [ ] Soft delete pattern (jika ada delete)
- [ ] Pagination untuk list endpoint
- [ ] Search/filter functionality
- [ ] Timestamp auto-update
