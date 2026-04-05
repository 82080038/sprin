<?php

declare(strict_types=1);

/**
 * improved_error_handler.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

session_start();

/**
 * Improved Error Handler for SPRIN Application
 * Based on PHP best practices from phpdelusions.net and official PHP documentation
 */

// Set proper error reporting based on environment
error_reporting(E_ALL);

// Universal exception handler based on phpdelusions.net recommendations
function myExceptionHandler($e) {
    // Log the error
    error_log($e);

    // Set proper HTTP status code
    http_response_code(500);

    // Check if we're in development mode
    $displayErrors = filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN);

    if ($displayErrors) {
        // Development: Show full error details
        echo '<div style="background: #ffebee; border: 2px solid #f44336; padding: 20px; margin: 20px; border-radius: 8px; font-family: monospace;">';
        echo '<h2 style="color: #d32f2f;">🚨 Application Error</h2>';
        echo '<strong>Error:</strong>' . htmlspecialchars($e->getMessage()) . '<br>';
        echo '<strong>File:</strong>' . htmlspecialchars($e->getFile()) . '<br>';
        echo '<strong>Line:</strong>' . $e->getLine() . '<br>';
        echo '<strong>Trace:</strong><br><pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        echo '</div>';
    } else {
        // Production: Show generic error page
        echo '<!DOCTYPE html>
<html>
<head>
    <title>System Error</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .error-container { max-width: 500px; margin: 0 auto; }
        .error-icon { font-size: 48px; color: #f44336; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>System Maintenance</h1>
        <p>We\'re experiencing technical difficulties. Please try again later.</p>
        <p><small>Error ID: ' . date('Y-m-d H:i:s') . '</small></p>
    </div>
</body>
</html>';
    }

    exit;
}

// Set exception handler
set_exception_handler('myExceptionHandler');

// Convert all PHP errors to exceptions
set_error_handler(function ($level, $message, $file = '', $line = 0) {
    // Don't throw exception for @ suppressed errors
    if (!(error_reporting() & $level)) {
        return false;
    }

    throw new ErrorException($message, 0, $level, $file, $line);
});

// Handle fatal errors that can't be caught by regular error handler
register_shutdown_function(function () {
    $error = error_get_last();

    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        myExceptionHandler(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
    }
});

/**
 * Safe error logging function
 */
function logError($message, $context = []) {
    $logMessage = date('Y-m-d H:i:s') . ' - ' . $message;

    if (!empty($context)) {
        $logMessage .= ' - Context: ' . json_encode($context);
    }

    error_log($logMessage);
}

/**
 * Custom error display for development
 */
function displayError($title, $message, $details = []) {
    if (filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN)) {
        echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 15px; border-radius: 4px;">';
        echo '<h4 style="color: #856404;">' . htmlspecialchars($title) . '</h4>';
        echo '<p>' . htmlspecialchars($message) . '</p>';

        if (!empty($details)) {
            echo '<pre>' . htmlspecialchars(print_r($details, true)) . '</pre>';
        }

        echo '</div>';
    }
}
?>
