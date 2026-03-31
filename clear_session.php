<?php
// Clear session and cookies script
require_once 'core/SessionManager.php';

// Clear all session data
SessionManager::clear();
SessionManager::destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Clear all application cookies
foreach ($_COOKIE as $name => $value) {
    setcookie($name, '', time() - 3600, '/');
}

echo "✅ Session and cookies cleared!\n";
echo "🔄 Please restart your browser and try login again.\n";
?>
