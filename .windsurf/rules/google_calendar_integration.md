---
description: Panduan integrasi Google Calendar
---

# Google Calendar Integration

## Setup

### 1. Google Cloud Console
1. Buat project di Google Cloud Console
2. Enable Google Calendar API
3. Buat OAuth 2.0 credentials
4. Download client_secrets.json

### 2. Configuration
Update `core/calendar_config.php`:
```php
define('GOOGLE_CLIENT_ID', 'your-client-id');
define('GOOGLE_CLIENT_SECRET', 'your-client-secret');
define('GOOGLE_REDIRECT_URI', BASE_URL . '/api/google_calendar_api.php?action=callback');
define('GOOGLE_CALENDAR_ID', 'primary'); // atau calendar ID spesifik
```

### 3. Database
Pastikan table `calendar_tokens` sudah ada untuk menyimpan OAuth tokens.

## OAuth Flow

### Authorization
```
GET /api/google_calendar_api.php?action=auth
```
Redirect user ke Google OAuth consent screen.

### Callback Handler
```
GET /api/google_calendar_api.php?action=callback&code=xxx
```
Exchange code untuk access_token dan refresh_token, simpan ke database.

## API Endpoints

### Sync Event to Google Calendar
```
POST /api/google_calendar_api.php
Content-Type: application/json

{
  "action": "sync",
  "schedule_id": 123
}
```

### Get Google Calendar Events
```
GET /api/google_calendar_api.php?action=list&start=2026-03-01&end=2026-03-31
```

## Event Format

### Mapping SPRIN Schedule ke Google Calendar Event
```php
$googleEvent = [
    'summary' => $schedule['title'],
    'description' => $schedule['description'] . "\n\nPersonil: " . $schedule['personil_name'],
    'start' => [
        'dateTime' => $schedule['start_time'],
        'timeZone' => 'Asia/Jakarta'
    ],
    'end' => [
        'dateTime' => $schedule['end_time'],
        'timeZone' => 'Asia/Jakarta'
    ],
    'location' => $schedule['location'],
    'attendees' => [
        ['email' => $personilEmail] // jika ada
    ]
];
```

## Token Management

### Store Tokens
```php
$sql = "
    INSERT INTO calendar_tokens 
    (user_id, access_token, refresh_token, token_expiry, scope, updated_at)
    VALUES (:user_id, :access_token, :refresh_token, :expiry, :scope, NOW())
    ON DUPLICATE KEY UPDATE
    access_token = :access_token,
    refresh_token = :refresh_token,
    token_expiry = :expiry,
    updated_at = NOW()
";
```

### Refresh Access Token
```php
if (strtotime($token['token_expiry']) <= time()) {
    // Token expired, refresh
    $newToken = $googleClient->refreshToken($token['refresh_token']);
    // Update database dengan token baru
}
```

## Error Handling

### Common Errors

**Token Expired:**
```php
try {
    $events = $service->events->listEvents($calendarId);
} catch (Google_Service_Exception $e) {
    if ($e->getCode() == 401) {
        // Token expired, refresh and retry
        $this->refreshToken();
        $events = $service->events->listEvents($calendarId);
    }
}
```

**Calendar Not Found:**
```php
if ($e->getCode() == 404) {
    throw new Exception('Kalender tidak ditemukan atau akses ditolak');
}
```

## Testing

### Local Testing
Google OAuth requires HTTPS untuk production. Untuk local development:
1. Tambahkan `http://localhost` ke authorized redirect URIs
2. Gunakan "Testing" mode untuk OAuth consent screen

### Test Sync
```bash
curl -X POST http://localhost/sprint/api/google_calendar_api.php \
  -H "Content-Type: application/json" \
  -d '{"action":"sync","schedule_id":1}'
```

## Security Considerations

1. **Store tokens securely** - Encrypt sebelum simpan ke database
2. **Use refresh tokens** - Access tokens expire, refresh tokens tidak
3. **Limit scope** - Hanya minta permission yang diperlukan
4. **Revoke access** - Sediakan fitur disconnect dari Google Calendar

## Scope Permissions

Minimal scope yang diperlukan:
```
https://www.googleapis.com/auth/calendar.events
```

Untuk full calendar access:
```
https://www.googleapis.com/auth/calendar
```
