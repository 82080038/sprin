# SPRIN API Documentation

## Overview
The SPRIN application provides RESTful API endpoints for managing police personnel, units, and organizational elements.

## Base URL
```
http://localhost/sprint/api/
```

## Authentication
All API endpoints require authentication. Include session cookie in requests.

## Endpoints

### Personnel Management

#### Get All Personnel
```http
GET /api/personil.php
```

Response:
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "nama": "John Doe",
            "nrp": "123456789",
            "pangkat": "Inspector",
            "bagian": "Intelligence"
        }
    ]
}
```

#### Add Personnel
```http
POST /api/personil.php
Content-Type: application/json

{
    "nama": "John Doe",
    "nrp": "123456789",
    "pangkat": "Inspector",
    "bagian": "Intelligence"
}
```

#### Update Personnel
```http
PUT /api/personil.php?id=1
Content-Type: application/json

{
    "nama": "John Doe Updated",
    "pangkat": "Senior Inspector"
}
```

#### Delete Personnel
```http
DELETE /api/personil.php?id=1
```

### Unit Management

#### Get All Units
```http
GET /api/bagian.php
```

#### Add Unit
```http
POST /api/bagian.php
Content-Type: application/json

{
    "nama": "Intelligence Unit",
    "deskripsi": "Handles intelligence operations"
}
```

### Element Management

#### Get All Elements
```http
GET /api/unsur.php
```

#### Add Element
```http
POST /api/unsur.php
Content-Type: application/json

{
    "nama": "Investigation",
    "deskripsi": "Investigation element"
}
```

## Error Handling

All endpoints return consistent error responses:

```json
{
    "status": "error",
    "message": "Error description",
    "code": 400
}
```

## Status Codes
- 200: Success
- 400: Bad Request
- 401: Unauthorized
- 404: Not Found
- 500: Internal Server Error

## Rate Limiting
API endpoints are rate-limited to prevent abuse.

## Examples

### JavaScript Example
```javascript
// Get all personnel
fetch('/api/personil.php')
    .then(response => response.json())
    .then(data => console.log(data));
```

### PHP Example
```php
// Add personnel
$data = [
    'nama' => 'John Doe',
    'nrp' => '123456789',
    'pangkat' => 'Inspector'
];

$ch = curl_init('http://localhost/sprint/api/personil.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
echo $response;
```

## Testing
Use the provided test script to verify API functionality:

```bash
curl http://localhost/sprint/api/personil.php
```

---

*This documentation is updated automatically with code changes.*
