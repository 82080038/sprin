---
description: Dokumentasi integrasi Frontend-to-Backend (F2E) dan End-to-End (E2E)
---

# Integration Documentation - SPRIN

## Overview

Dokumentasi ini menjelaskan standar integrasi antara Frontend dan Backend (F2E) serta alur End-to-End (E2E) untuk aplikasi SPRIN.

## Frontend-to-Backend Integration (F2E)

### 1. Library Standardization

**CSS Framework:**
- Bootstrap 5.3.0 (all pages)
```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
```

**Icon Library:**
- Font Awesome 6.4.2 (all pages)
```html
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
```

**JavaScript:**
- jQuery 3.6.0 (for compatibility)
- Vanilla JS with Fetch API (preferred for new code)

### 2. CSS Variable Standardization

**Root Variables (all pages):**
```css
:root {
    --primary-color: #1a237e;
    --secondary-color: #3949ab;
    --accent-color: #ffd700;
    --text-color: #333;
    --bg-color: #f5f5f5;
    --card-bg: #ffffff;
    --border-color: #dee2e6;
}
```

### 3. API Client Standardization

**JavaScript API Client (`public/assets/js/api-client.js`):**
```javascript
class ApiClient {
    constructor(baseUrl = null) {
        this.baseUrl = baseUrl || (window.ApiConfig ? window.ApiConfig.baseUrl : '/api');
        this.token = localStorage.getItem('api_token') || null;
    }
    
    async handleResponse(response) {
        const text = await response.text();
        
        // Check if response is HTML (error page)
        if (text.trim().startsWith('<!DOCTYPE') || text.includes('<html')) {
            throw new Error('Server returned HTML error page instead of JSON');
        }
        
        try {
            const data = JSON.parse(text);
            if (!response.ok) {
                throw new Error(data.message || data.error?.message || 'Request failed');
            }
            return data;
        } catch (error) {
            if (error instanceof SyntaxError) {
                console.error('Invalid JSON response:', text.substring(0, 200));
                throw new Error('Invalid JSON response from server');
            }
            throw error;
        }
    }
}
```

### 4. Backend API Standardization

**Required Headers:**
```php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
```

**Standardized Response Format:**
```php
// Success
[
    'success' => true,
    'message' => 'Operation successful',
    'data' => $data,
    'timestamp' => date('c')
]

// Error (Development)
[
    'success' => false,
    'message' => 'Database error: ' . $e->getMessage(),
    'timestamp' => date('c')
]

// Error (Production)
[
    'success' => false,
    'message' => 'Failed to process request',
    'timestamp' => date('c')
]
```

**Database Connection:**
```php
require_once __DIR__ . '/../core/Database.php';
$db = Database::getInstance();
$pdo = $db->getConnection();
```

### 5. Authentication Integration

**Frontend:**
- Login via `login.php`
- Session automatically managed by browser cookies
- Logout via `core/logout.php` menggunakan `AuthHelper::logout()`

**Backend:**
```php
require_once __DIR__ . '/../core/auth_helper.php';

if (!AuthHelper::validateSession()) {
    header('Location: ' . url('login.php'));
    exit;
}
```

**Session Security Settings:**
```php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 untuk HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');
```

## End-to-End Workflows (E2E)

### 1. Login Flow

```
User -> login.php (form submit)
  -> POST to login.php (process)
  -> AuthHelper::login($username, $password)
  -> Validate credentials (Argon2id hash)
  -> Set session: $_SESSION['logged_in'] = true
  -> Redirect to pages/main.php
```

**Session Data:**
- `$_SESSION['logged_in']` - Boolean login status
- `$_SESSION['username']` - Username
- `$_SESSION['login_time']` - ISO8601 timestamp
- `$_SESSION['session_token']` - Random token

### 2. Dashboard Statistics Flow

```
User -> main.php
  -> Load header.php (includes CSS/JS)
  -> JavaScript: loadStatistics()
  -> AJAX: GET /api/personil_simple.php
  -> Database: SELECT statistics
  -> Return JSON: {success, message, data, timestamp}
  -> JavaScript: Update DOM elements
  -> Display: totalPersonil, polriCount, etc.
```

**API Chain:**
1. `personil_simple.php` - Basic statistics
2. `unsur_stats.php` - Detailed statistics
3. `calendar_api.php` - Schedule statistics

### 3. Personil Management Flow

```
User -> personil.php
  -> Load page dengan Bootstrap 5.3.0 + Font Awesome 6.4.2
  -> JavaScript: loadPersonil()
  -> AJAX: GET /api/personil_list.php
  -> Database: Query dengan filters
  -> Return: Grouped data by unsur/bagian
  -> Render: HTML tables dengan action buttons

User -> Click "Tambah"
  -> Open modal (Bootstrap)
  -> Load dropdowns: /api/personil_crud.php (action=get_dropdown_data)
  -> Populate: Unsur, Bagian, Jabatan, Pangkat
  -> Cascading: Unsur -> Bagian -> Jabatan

User -> Submit form
  -> POST /api/personil_crud.php (action=create_personil)
  -> Validate input
  -> Database: INSERT
  -> Return: {success, message, data, timestamp}
  -> Refresh: loadPersonil()
```

### 4. Schedule Management Flow

```
User -> calendar_dashboard.php
  -> Load FullCalendar / custom calendar
  -> AJAX: GET /api/calendar_api.php?action=getEvents
  -> Database: Query schedules
  -> Return: Events array
  -> Render: Calendar view

User -> Create event
  -> POST /api/calendar_api.php?action=createEvent
  -> Validate: personil_id, date, shift
  -> Database: INSERT INTO jadwal
  -> Return: {success, message, data, timestamp}
  -> Refresh: Calendar events
```

## Error Handling Integration

### Frontend Error Handling

```javascript
fetch('/api/personil_list.php')
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('text/html')) {
            throw new Error('Server returned HTML error page');
        }
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message);
        }
        // Process data
    })
    .catch(error => {
        console.error('API Error:', error);
        // Show user-friendly message
        UIHelper.showAlert('danger', error.message);
    });
```

### Backend Error Handling

```php
try {
    $db = Database::getInstance();
    // ... operations
} catch(Exception $e) {
    error_log("Error: " . $e->getMessage());
    
    if (ENVIRONMENT === 'development') {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'timestamp' => date('c')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Operation failed',
            'timestamp' => date('c')
        ]);
    }
}
```

## Data Flow Standards

### Request Format

**GET Request:**
```
/api/personil_list.php?search=john&unsur=BAG&status=aktif
```

**POST Request:**
```javascript
fetch('/api/personil_crud.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
        action: 'create_personil',
        nama: 'John Doe',
        nrp: '12345678'
    })
});
```

### Response Format

**Success:**
```json
{
    "success": true,
    "message": "Retrieved 150 personil records",
    "data": {
        "personil": [...],
        "statistics": {...}
    },
    "timestamp": "2026-03-31T12:34:56+07:00"
}
```

**Error:**
```json
{
    "success": false,
    "message": "Database connection failed",
    "timestamp": "2026-03-31T12:34:56+07:00"
}
```

## Testing Integration

### Manual Testing Checklist

**Authentication:**
- [ ] Login dengan credentials valid
- [ ] Login dengan credentials invalid
- [ ] Session timeout setelah 1 jam
- [ ] Logout menghapus session

**API Integration:**
- [ ] All APIs return JSON (not HTML)
- [ ] Error responses include timestamp
- [ ] CORS headers present
- [ ] Database connection using singleton

**UI Integration:**
- [ ] Bootstrap 5.3.0 loaded correctly
- [ ] Font Awesome 6.4.2 icons display
- [ ] Responsive design on mobile
- [ ] JavaScript API client handles errors

### API Testing Commands

```bash
# Test personil list
curl http://localhost/sprint/api/personil_list.php | jq

# Test with search
curl "http://localhost/sprint/api/personil_list.php?search=john" | jq

# Test statistics
curl http://localhost/sprint/api/personil_simple.php | jq

# Test CRUD (POST)
curl -X POST http://localhost/sprint/api/personil_crud.php \
  -d "action=get_dropdown_data" | jq
```

## Version History

### v1.0.0 (Current)
- Standardized all APIs with Database singleton
- Standardized response format: {success, message, data, timestamp}
- Standardized library versions: Bootstrap 5.3.0, Font Awesome 6.4.2
- Implemented AuthHelper::validateSession() across all pages
- Added session security settings
- Updated error handling dengan environment-based messages
