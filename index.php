<?php
/**
 * SPRIN Application - Index Router
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Get the requested path
 = $_SERVER['REQUEST_URI'];
 = $_SERVER['SCRIPT_NAME'];
 = str_replace(dirname(), '', );
 = trim(, '/');

// Route based on path
switch () {
    case '':
    case 'index':
    case 'home':
        // Redirect to main dashboard if logged in, otherwise to login
        if (isset($_SESSION['user_id'])) {
            header('Location: /pages/main.php');
        } else {
            header('Location: /login.php');
        }
        break;
        
    case 'login':
        header('Location: /login.php');
        break;
        
    case 'logout':
        header('Location: /logout.php');
        break;
        
    default:
        // For other paths, try to serve the file or show 404
         = __DIR__ . '/' . ;
        if (file_exists() && is_file()) {
            // Serve the file directly
            include ;
        } else {
            // Show 404 page
            http_response_code(404);
            echo '<h1>404 - Page Not Found</h1>';
            echo '<p>The requested page could not be found.</p>';
            echo '<p><a href="/">Go to Home</a></p>';
        }
        break;
}

?>
