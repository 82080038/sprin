---
description: Referensi API endpoints aplikasi SPRIN
---

# API Reference - SPRIN

## Base URL
```
http://localhost/sprint/api
```

## Response Format
Semua API mengembalikan JSON dengan format standar:
```json
{
  "success": true|false,
  "message": "string",
  "data": object|array,
  "timestamp": "ISO8601 datetime"
}
```

## Authentication
- Session-based authentication menggunakan PHP sessions via `AuthHelper::validateSession()`
- Login via /login.php
- Session lifetime: 3600 detik (1 jam) dengan security settings:
  - `session.cookie_httponly = 1`
  - `session.cookie_samesite = 'Lax'`
  - `session.use_strict_mode = 1`
- Token tidak diperlukan untuk API call karena menggunakan session cookies

## API Implementation Standards

### Database Connection
Semua API menggunakan Database singleton pattern:
```php
require_once __DIR__ . '/../core/Database.php';
$db = Database::getInstance();
$pdo = $db->getConnection();
```

### Error Handling
```php
// Production: Hide detailed errors
if (ENVIRONMENT !== 'development') {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Standardized error response
catch(Exception $e) {
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
}
```

### CORS Headers
```php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
```

## API Endpoints

### Personil API

#### 1. List Personil
```
GET /api/personil_list.php
```
**Query Parameters:**
- `page` (int) - Halaman, default 1
- `per_page` (int) - Item per halaman, default 20
- `search` (string) - Pencarian nama/NRK/NRP
- `unsur` (string) - Filter by unsur code
- `bagian` (int) - Filter by bagian ID
- `jenis_pegawai` (string) - Filter: polri/asn/p3k
- `status` (string) - Filter status pegawai

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [...],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 100,
      "total_pages": 5,
      "has_next": true,
      "has_prev": false
    }
  }
}
```

#### 2. Get Personil Detail
```
GET /api/personil_detail.php?id={id}
```

#### 3. Create Personil
```
POST /api/personil_crud.php
Content-Type: application/json

{
  "action": "create",
  "nrk": "123456",
  "nrp": "12345678",
  "nama_lengkap": "John Doe",
  "id_pangkat": 1,
  "id_jabatan": 1,
  "id_bagian": 1,
  "jenis_kelamin": "L",
  "jenis_pegawai": "polri"
}
```

#### 4. Update Personil
```
POST /api/personil_crud.php
Content-Type: application/json

{
  "action": "update",
  "id": 1,
  "nama_lengkap": "John Updated"
}
```

#### 5. Delete Personil
```
POST /api/personil_delete.php
Content-Type: application/json

{
  "id": 1
}
```

#### 6. Simple Personil Stats
```
GET /api/personil_simple.php
```
**Response:**
```json
{
  "success": true,
  "data": {
    "statistics": {
      "total_personil": 150,
      "polri_count": 100,
      "unsur_distribution": {...}
    }
  }
}
```

### Bagian & Unsur API

#### 7. Jabatan CRUD
```
POST /api/jabatan_crud.php
```
**Actions:** create, read, update, delete

#### 8. Search Personil (Advanced)
```
POST /api/advanced_search.php
```
**Body:**
```json
{
  "filters": {
    "nama_lengkap": "search term",
    "id_bagian": [1, 2, 3],
    "id_pangkat": [1, 2],
    "jenis_kelamin": "L",
    "jenis_pegawai": "polri",
    "status_pegawai": "aktif"
  },
  "sort": {
    "field": "nama_lengkap",
    "direction": "asc"
  },
  "pagination": {
    "page": 1,
    "per_page": 20
  }
}
```

#### 9. Unsur Statistics
```
GET /api/unsur_stats.php
GET /api/unsur_stats.php?unsur=BAG
```
**Response:**
```json
{
  "success": true,
  "data": {
    "unsur": "BAG",
    "unsur_name": "Bagian",
    "total_personil": 45,
    "by_bagian": [...],
    "by_pangkat": [...],
    "by_jk": {"L": 30, "P": 15},
    "overall_statistics": {...}
  }
}
```

### Export API

#### 10. Export Personil
```
GET /api/export_personil.php?format=pdf|excel|csv
```
**Query Parameters:**
- `format` - pdf, excel, csv
- `ids` - (optional) Comma-separated IDs untuk export terpilih
- Semua filter dari personil_list juga berlaku

### Calendar API

#### 11. Calendar Events
```
GET /api/calendar_api.php?action=getEvents&start=2026-03-01&end=2026-03-31
```
**Actions:**
- `getEvents` - List semua events dalam range
- `getStats` - Statistik jadwal (today, week, month)
- `createEvent` - Buat event baru
- `updateEvent` - Update event
- `deleteEvent` - Hapus event

**Response getEvents:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Piket Pagi",
      "start": "2026-03-31T08:00:00",
      "end": "2026-03-31T16:00:00",
      "personil_name": "John Doe"
    }
  ]
}
```

#### 12. Google Calendar API
```
POST /api/google_calendar_api.php
```
**Actions:**
- `sync` - Sync event ke Google Calendar
- `auth` - OAuth authorization
- `callback` - OAuth callback handler

### Bulk Operations

#### 13. Bulk Update Personil
```
POST /api/bulk_update_personil.php
Content-Type: application/json

{
  "ids": [1, 2, 3, 4, 5],
  "field": "id_bagian",
  "value": 2
}
```

### API Versioning

#### v1 API (Legacy Compatibility)
```
/api/v1/index.php
```
Endpoint ini untuk backward compatibility dengan aplikasi lama.

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "Missing required fields: nama_lengkap, nrk",
  "data": {
    "missing_fields": ["nama_lengkap", "nrk"]
  }
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthorized. Please login first."
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Personil not found"
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Database connection failed"
}
```

## Pagination

Semua API list menggunakan pagination standar:

| Parameter | Default | Max |
|-----------|---------|-----|
| page | 1 | - |
| per_page | 20 | 100 |

**Response Pagination Object:**
```json
{
  "current_page": 1,
  "per_page": 20,
  "total": 150,
  "total_pages": 8,
  "has_next": true,
  "has_prev": false
}
```

## Filtering

### Text Search
Field yang bisa di-search:
- nama_lengkap (LIKE %search%)
- nrk (LIKE %search%)
- nrp (LIKE %search%)

### Range Filter
```json
{
  "filters": {
    "tanggal_lahir_from": "1990-01-01",
    "tanggal_lahir_to": "2000-12-31"
  }
}
```

### Multiple Values (IN)
```json
{
  "filters": {
    "id_bagian": [1, 2, 3, 4]
  }
}
```

## Sorting

```json
{
  "sort": {
    "field": "nama_lengkap",
    "direction": "asc"  // atau "desc"
  }
}
```

Field yang bisa di-sort:
- nama_lengkap
- nrk, nrp
- tanggal_lahir
- created_at
- id_pangkat

## Rate Limiting

- **Limit**: 100 requests per hour per IP
- **Status**: Belum diimplementasikan fully (configured in config.php)

## CORS

API mendukung CORS untuk development:
```php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
```

## Testing

Untuk testing API, gunakan curl atau Postman:

```bash
# Get personil list
curl http://localhost/sprint/api/personil_list.php?page=1

# Create personil
curl -X POST http://localhost/sprint/api/personil_crud.php \
  -H "Content-Type: application/json" \
  -d '{"action":"create","nrk":"123","nama_lengkap":"Test"}'

# Export PDF
curl "http://localhost/sprint/api/export_personil.php?format=pdf" \
  --output personil.pdf
```
