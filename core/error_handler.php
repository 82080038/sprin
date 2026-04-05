<?php
/**
 * core/error_handler.php
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

declare(strict_types=1);

// Development Error Reporting
if (!defined('DEVELOPMENT_MODE')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

/**
 * Global Error Handler & Exception Handler
 */
class ErrorHandler {
    const LOG_FILE = __DIR__ . '/../logs/error.log';

    /**
     * Initialize error handlers
     */
    public static function init(): void {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Handle PHP errors
     */
    public static function handleError($severity, $message, $file, $line): bool {
        $errorType = self::getErrorType($severity);
        $errorMessage = "[$errorType] $message in $file on line $line";

        // Log error
        self::logError($errorMessage);

        // In development, show error details
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            return false; // Let PHP handle it normally
        }

        // In production, hide error but log it
        return true;
    }

    /**
     * Handle uncaught exceptions
     */
    public static function handleException($exception): void {
        $errorMessage = sprintf(
            "[EXCEPTION] %s in %s on line %d\nStack trace:\n%s",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        self::logError($errorMessage);

        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            // Show detailed error in development
            echo "<h1>Error Occurred</h1>";
            echo "<pre>" . htmlspecialchars($errorMessage) . "</pre>";
        } else {
            // Show user-friendly error in production
            if (!headers_sent()) {
                header('HTTP/1.1 500 Internal Server Error');
            }
            echo "<h1>Oops! Something went wrong</h1>";
            echo "<p>We're sorry, but an error occurred. Please try again later.</p>";
            echo "<p><a href='/sprint/pages/main.php'>Go back to Dashboard</a></p>";
        }
    }

    /**
     * Handle fatal errors on shutdown
     */
    public static function handleShutdown(): void {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $errorMessage = sprintf(
                "[FATAL] %s in %s on line %d",
                $error['message'],
                $error['file'],
                $error['line']
            );

            self::logError($errorMessage);

            if (!headers_sent()) {
                header('HTTP/1.1 500 Internal Server Error');
            }

            echo "<h1>System Error</h1>";
            echo "<p>A critical error occurred. Please contact the administrator.</p>";
        }
    }

    /**
     * Get human-readable error type
     */
    private static function getErrorType($severity): string {
        $types = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED'
        ];

        return $types[$severity] ?? 'UNKNOWN';
    }

    /**
     * Log error to file
     */
    private static function logError(string $message): void {
        $logDir = dirname(self::LOG_FILE);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message\n";

        error_log($logEntry, 3, self::LOG_FILE);
    }

    /**
     * Create custom error pages
     */
    public static function createErrorPages(): void {
        $errorPagesDir = __DIR__ . '/../error_pages';

        if (!is_dir($errorPagesDir)) {
            mkdir($errorPagesDir, 0755, true);
        }

        // 404 Page
        $content404 = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a237e 0%, #3949ab 50%, #ffd700 100%);
            min-height: 100vh;
        }
        .error-container {
            background: white;
            border-radius: 20px;
            padding: 60px;
            text-align: center;
            margin-top: 100px;
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #1a237e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="error-container">
                    <div class="error-code">404</div>
                    <h2>Page Not Found</h2>
                    <p>The page you're looking for doesn't exist.</p>
                    <a href="/sprint/pages/main.php" class="btn btn-primary">Go to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

        file_put_contents($errorPagesDir . '/404.php', $content404);

        // 500 Page
        $content500 = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a237e 0%, #3949ab 50%, #ffd700 100%);
            min-height: 100vh;
        }
        .error-container {
            background: white;
            border-radius: 20px;
            padding: 60px;
            text-align: center;
            margin-top: 100px;
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="error-container">
                    <div class="error-code">500</div>
                    <h2>Server Error</h2>
                    <p>Something went wrong on our end. Please try again later.</p>
                    <a href="/sprint/pages/main.php" class="btn btn-primary">Go to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

        file_put_contents($errorPagesDir . '/500.php', $content500);
    }
}
?>
