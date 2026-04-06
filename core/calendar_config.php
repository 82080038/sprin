<?php
// Google Calendar API Configuration
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
}
if (!defined('GOOGLE_REDIRECT_URI')) {
    define('GOOGLE_REDIRECT_URI', 'http://localhost/sprint/oauth_callback.php');
}

// Required Google API scopes for Calendar
if (!defined('GOOGLE_SCOPES')) {
    define('GOOGLE_SCOPES', implode(' ', [
        'https://www.googleapis.com/auth/calendar.readonly',
        'https://www.googleapis.com/auth/calendar.events'
    ]));
}

// Database configuration for schedules
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'bagops');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', 'root');
}

// Schedule types
if (!defined('SHIFT_TYPES')) {
    define('SHIFT_TYPES', [
        'PAGI' => '06:00-14:00',
        'SIANG' => '14:00-22:00', 
        'MALAM' => '22:00-06:00',
        'FULL_DAY' => '00:00-23:59'
    ]);
}

// Event colors for calendar
if (!defined('EVENT_COLORS')) {
    define('EVENT_COLORS', [
        'PAGI' => '#4285F4',
        'SIANG' => '#EA4335', 
        'MALAM' => '#FBBC04',
        'FULL_DAY' => '#34A853',
        'CUTI' => '#FF6F00',
        'LEMBUR' => '#9E9E9E'
    ]);
}
?>
