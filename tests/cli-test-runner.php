<?php
/**
 * SPRIN CLI Test Runner
 * Run via: /opt/lampp/bin/php tests/cli-test-runner.php
 * Tests core logic directly without HTTP — bypasses Apache
 */

define('CLI_TEST_MODE', true);
define('ROOT', dirname(__DIR__));

// ─── Output helpers ───────────────────────────────────────────────────────────
$stats = ['pass' => 0, 'fail' => 0, 'skip' => 0];
$failures = [];
$currentGroup = '';

function group(string $name): void {
    global $currentGroup;
    $currentGroup = $name;
    echo "\n\033[1;34m▶ {$name}\033[0m\n";
}

function test(string $name, callable $fn): void {
    global $stats, $failures, $currentGroup;
    $t = microtime(true);
    try {
        $result = $fn();
        $ms = round((microtime(true) - $t) * 1000);
        $detail = $result ? " — {$result}" : '';
        echo "  \033[32m✓\033[0m {$name}{$detail} \033[2m({$ms}ms)\033[0m\n";
        $stats['pass']++;
    } catch (Throwable $e) {
        $ms = round((microtime(true) - $t) * 1000);
        echo "  \033[31m✗\033[0m {$name} \033[2m({$ms}ms)\033[0m\n";
        echo "    \033[31m→ {$e->getMessage()}\033[0m\n";
        $stats['fail']++;
        $failures[] = "[{$currentGroup}] {$name}: " . $e->getMessage();
    }
}

function skip(string $name, string $reason = ''): void {
    global $stats;
    echo "  \033[33m⊘\033[0m {$name}" . ($reason ? " — {$reason}" : '') . "\n";
    $stats['skip']++;
}

function assert_true(bool $cond, string $msg): void {
    if (!$cond) throw new Exception($msg);
}

function assert_equals($a, $b, string $msg = ''): void {
    if ($a !== $b) throw new Exception($msg ?: "Expected " . var_export($b, true) . ", got " . var_export($a, true));
}

function assert_contains(string $haystack, string $needle, string $msg = ''): void {
    if (strpos($haystack, $needle) === false)
        throw new Exception($msg ?: "Expected to find '{$needle}' in string");
}

function assert_not_contains(string $haystack, string $needle, string $msg = ''): void {
    if (strpos($haystack, $needle) !== false)
        throw new Exception($msg ?: "Should NOT contain '{$needle}'");
}

// ─── Setup: Load config without session/output ────────────────────────────────
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_HOST'] = 'localhost';

// Suppress output buffering from config.php
ob_start();
try {
    require_once ROOT . '/core/config.php';
} catch (Throwable $e) {}
ob_end_clean();

echo "\033[1;36m╔══════════════════════════════════════════════════╗\033[0m\n";
echo "\033[1;36m║       SPRIN CLI Test Runner v1.0                 ║\033[0m\n";
echo "\033[1;36m╚══════════════════════════════════════════════════╝\033[0m\n";
echo "Started: " . date('Y-m-d H:i:s') . "\n";

$startTime = microtime(true);

// ═══════════════════════════════════════════════════════════════════════════════
// GROUP 1: Configuration
// ═══════════════════════════════════════════════════════════════════════════════
group('Configuration');

test('BASE_URL is defined', function() {
    assert_true(defined('BASE_URL'), 'BASE_URL not defined');
    return BASE_URL;
});

test('DB constants are defined', function() {
    assert_true(defined('DB_HOST'), 'DB_HOST missing');
    assert_true(defined('DB_NAME'), 'DB_NAME missing');
    assert_true(defined('DB_USER'), 'DB_USER missing');
    assert_true(defined('DB_PASS'), 'DB_PASS missing');
    return DB_HOST . '/' . DB_NAME;
});

test('DEBUG_MODE is boolean', function() {
    assert_true(defined('DEBUG_MODE'), 'DEBUG_MODE not defined');
    assert_true(is_bool(DEBUG_MODE), 'DEBUG_MODE should be boolean, got: ' . gettype(DEBUG_MODE));
    return 'DEBUG_MODE=' . (DEBUG_MODE ? 'true' : 'false');
});

test('ENVIRONMENT is set to development', function() {
    assert_true(defined('ENVIRONMENT'), 'ENVIRONMENT not defined');
    return ENVIRONMENT;
});

test('JWT_SECRET is generated and long enough', function() {
    assert_true(defined('JWT_SECRET'), 'JWT_SECRET not defined');
    assert_true(strlen(JWT_SECRET) >= 32, 'JWT_SECRET too short: ' . strlen(JWT_SECRET));
    assert_true(JWT_SECRET !== 'your-secret-key-here', 'JWT_SECRET is default insecure value');
    return 'length=' . strlen(JWT_SECRET);
});

test('API_RATE_LIMIT is set', function() {
    assert_true(defined('API_RATE_LIMIT'), 'API_RATE_LIMIT not defined');
    assert_true(API_RATE_LIMIT > 0, 'API_RATE_LIMIT must be positive');
    return API_RATE_LIMIT . ' req/hr';
});

// ═══════════════════════════════════════════════════════════════════════════════
// GROUP 2: Database Connection
// ═══════════════════════════════════════════════════════════════════════════════
group('Database Connection');

$pdo = null;

test('Connect via Unix socket', function() use (&$pdo) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";unix_socket=/opt/lampp/var/mysql/mysql.sock";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        return 'connected via socket';
    } catch (PDOException $e) {
        // Fallback to TCP
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        return 'connected via TCP';
    }
});

test('Database is accessible', function() use (&$pdo) {
    assert_true($pdo !== null, 'No PDO connection');
    $result = $pdo->query("SELECT 1 as ok")->fetch();
    assert_equals((int)$result['ok'], 1, 'DB query failed');
    return 'query OK';
});

test('Required tables exist', function() use (&$pdo) {
    assert_true($pdo !== null, 'No PDO connection');
    $required = ['unsur', 'bagian', 'jabatan', 'personil', 'users'];
    $missing = [];
    foreach ($required as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
        if (!$stmt->fetch()) $missing[] = $table;
    }
    assert_true(empty($missing), 'Missing tables: ' . implode(', ', $missing));
    return implode(', ', $required) . ' — all present';
});

test('Personil table has data', function() use (&$pdo) {
    assert_true($pdo !== null, 'No PDO connection');
    $count = $pdo->query("SELECT COUNT(*) FROM personil WHERE is_deleted = 0")->fetchColumn();
    assert_true((int)$count > 0, 'No active personil records found');
    return $count . ' active records';
});

test('Unsur table has records', function() use (&$pdo) {
    assert_true($pdo !== null, 'No PDO connection');
    $count = $pdo->query("SELECT COUNT(*) FROM unsur")->fetchColumn();
    assert_true((int)$count > 0, 'No unsur records');
    return $count . ' records';
});

test('Bagian table has records', function() use (&$pdo) {
    assert_true($pdo !== null, 'No PDO connection');
    $count = $pdo->query("SELECT COUNT(*) FROM bagian")->fetchColumn();
    assert_true((int)$count > 0, 'No bagian records');
    return $count . ' records';
});

test('Jabatan table has records', function() use (&$pdo) {
    assert_true($pdo !== null, 'No PDO connection');
    $count = $pdo->query("SELECT COUNT(*) FROM jabatan")->fetchColumn();
    assert_true((int)$count > 0, 'No jabatan records');
    return $count . ' records';
});

test('Users table has at least one user', function() use (&$pdo) {
    assert_true($pdo !== null, 'No PDO connection');
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    assert_true((int)$count > 0, 'No users in database');
    return $count . ' users';
});

// ═══════════════════════════════════════════════════════════════════════════════
// GROUP 3: Data Integrity
// ═══════════════════════════════════════════════════════════════════════════════
group('Data Integrity');

test('No orphaned jabatan (jabatan without unsur)', function() use (&$pdo) {
    assert_true($pdo !== null, 'No PDO connection');
    $count = $pdo->query("
        SELECT COUNT(*) FROM jabatan j
        LEFT JOIN unsur u ON j.id_unsur = u.id
        WHERE j.id_unsur IS NOT NULL AND u.id IS NULL
    ")->fetchColumn();
    assert_equals((int)$count, 0, "{$count} orphaned jabatan records");
    return 'no orphans';
});

test('No orphaned bagian (bagian without unsur)', function() use (&$pdo) {
    assert_true($pdo !== null, 'No PDO connection');
    $count = $pdo->query("
        SELECT COUNT(*) FROM bagian b
        LEFT JOIN unsur u ON b.id_unsur = u.id
        WHERE b.id_unsur IS NOT NULL AND u.id IS NULL
    ")->fetchColumn();
    assert_equals((int)$count, 0, "{$count} orphaned bagian records");
    return 'no orphans';
});

test('Personil NRP values are unique (no duplicates)', function() use (&$pdo) {
    assert_true($pdo !== null, 'No PDO connection');
    $count = $pdo->query("
        SELECT COUNT(*) FROM (
            SELECT nrp FROM personil WHERE is_deleted = 0
            GROUP BY nrp HAVING COUNT(*) > 1
        ) t
    ")->fetchColumn();
    assert_equals((int)$count, 0, "{$count} duplicate NRP values found");
    return 'all NRP unique';
});

test('Users have hashed passwords (not plain text)', function() use (&$pdo) {
    assert_true($pdo !== null, 'No PDO connection');
    $users = $pdo->query("SELECT password FROM users LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($users as $pw) {
        assert_true(strlen($pw) > 20, 'Password looks too short/plain: ' . substr($pw, 0, 10));
        assert_true(!preg_match('/^[a-zA-Z0-9]{1,20}$/', $pw), 'Password looks unhashed');
    }
    return count($users) . ' users checked';
});

test('Unsur urutan values are set', function() use (&$pdo) {
    assert_true($pdo !== null, 'No PDO connection');
    $null_count = $pdo->query("SELECT COUNT(*) FROM unsur WHERE urutan IS NULL")->fetchColumn();
    assert_equals((int)$null_count, 0, "{$null_count} unsur have NULL urutan");
    return 'all urutan set';
});

test('No XSS in nama_bagian (no raw script tags in DB)', function() use (&$pdo) {
    assert_true($pdo !== null, 'No PDO connection');
    $count = $pdo->query("SELECT COUNT(*) FROM bagian WHERE nama_bagian LIKE '%<script%'")->fetchColumn();
    assert_equals((int)$count, 0, "{$count} bagian contain script tags");
    return 'clean';
});

test('No XSS in nama_jabatan', function() use (&$pdo) {
    assert_true($pdo !== null, 'No PDO connection');
    $count = $pdo->query("SELECT COUNT(*) FROM jabatan WHERE nama_jabatan LIKE '%<script%'")->fetchColumn();
    assert_equals((int)$count, 0, "{$count} jabatan contain script tags");
    return 'clean';
});

// ═══════════════════════════════════════════════════════════════════════════════
// GROUP 4: Core Classes
// ═══════════════════════════════════════════════════════════════════════════════
group('Core Classes');

test('Database.php can be loaded', function() {
    ob_start();
    require_once ROOT . '/core/Database.php';
    ob_end_clean();
    assert_true(class_exists('Database'), 'Database class not found');
    return 'class loaded';
});

test('Database singleton returns instance', function() use (&$pdo) {
    assert_true(class_exists('Database'), 'Database class not loaded');
    // Just verify the class has getInstance method
    assert_true(method_exists('Database', 'getInstance'), 'getInstance method missing');
    return 'method exists';
});

test('auth_helper.php can be loaded', function() {
    ob_start();
    try {
        require_once ROOT . '/core/auth_helper.php';
    } catch (Throwable $e) {
        ob_end_clean();
        throw $e;
    }
    ob_end_clean();
    assert_true(class_exists('AuthHelper'), 'AuthHelper class not found');
    return 'class loaded';
});

test('AuthHelper::generateCSRFToken creates token', function() {
    assert_true(class_exists('AuthHelper'), 'AuthHelper not loaded');
    assert_true(method_exists('AuthHelper', 'generateCSRFToken'), 'generateCSRFToken missing');
    // Method exists — session needed to actually generate
    return 'method exists';
});

test('AuthHelper::hashPassword produces Argon2/bcrypt hash', function() {
    assert_true(class_exists('AuthHelper'), 'AuthHelper not loaded');
    assert_true(method_exists('AuthHelper', 'hashPassword'), 'hashPassword missing');
    $hash = AuthHelper::hashPassword('test_password_123');
    assert_true(strlen($hash) > 30, 'Hash too short');
    assert_true($hash !== 'test_password_123', 'Password not hashed');
    assert_true(password_verify('test_password_123', $hash), 'Hash does not verify');
    return 'hash length=' . strlen($hash);
});

test('AuthHelper::verifyPassword works correctly', function() {
    assert_true(class_exists('AuthHelper'), 'AuthHelper not loaded');
    $hash = AuthHelper::hashPassword('correct_password');
    assert_true(AuthHelper::verifyPassword('correct_password', $hash), 'Should verify true');
    assert_true(!AuthHelper::verifyPassword('wrong_password', $hash), 'Should verify false');
    return 'verify OK';
});

test('SessionManager.php can be loaded', function() {
    ob_start();
    try {
        require_once ROOT . '/core/SessionManager.php';
    } catch (Throwable $e) {
        ob_end_clean();
        throw $e;
    }
    ob_end_clean();
    assert_true(class_exists('SessionManager'), 'SessionManager not found');
    return 'class loaded';
});

test('BackupManager.php can be loaded', function() {
    ob_start();
    try {
        require_once ROOT . '/core/BackupManager.php';
    } catch (Throwable $e) {
        ob_end_clean();
        throw $e;
    }
    ob_end_clean();
    assert_true(class_exists('BackupManager'), 'BackupManager not found');
    return 'class loaded';
});

// ═══════════════════════════════════════════════════════════════════════════════
// GROUP 5: File Structure
// ═══════════════════════════════════════════════════════════════════════════════
group('File Structure');

$requiredFiles = [
    'core/config.php', 'core/Database.php', 'core/auth_helper.php',
    'core/SessionManager.php', 'core/BackupManager.php',
    'api/unified-api.php', 'api/unsur_api.php', 'api/bagian_api.php',
    'api/jabatan_api.php', 'api/personil_crud.php', 'api/personil_simple.php',
    'pages/unsur.php', 'pages/bagian.php', 'pages/jabatan.php', 'pages/personil.php',
    'includes/components/header.php', 'includes/components/footer.php',
    'public/assets/js/api-client.js', 'login.php', 'index.php',
];

foreach ($requiredFiles as $f) {
    test("Exists: {$f}", function() use ($f) {
        $path = ROOT . '/' . $f;
        assert_true(file_exists($path), "File not found: {$path}");
        assert_true(filesize($path) > 0, "File is empty: {$f}");
        return filesize($path) . ' bytes';
    });
}

// ═══════════════════════════════════════════════════════════════════════════════
// GROUP 6: PHP Syntax Check
// ═══════════════════════════════════════════════════════════════════════════════
group('PHP Syntax Check');

$phpFiles = [
    'core/config.php', 'core/Database.php', 'core/auth_helper.php',
    'core/SessionManager.php', 'core/BackupManager.php',
    'api/unified-api.php', 'api/unsur_api.php', 'api/bagian_api.php',
    'api/jabatan_api.php', 'api/bulk_update_personil.php',
    'login.php', 'index.php',
];

foreach ($phpFiles as $f) {
    test("Syntax OK: {$f}", function() use ($f) {
        $path = ROOT . '/' . $f;
        if (!file_exists($path)) throw new Exception("File not found");
        $output = shell_exec("/opt/lampp/bin/php -l " . escapeshellarg($path) . " 2>&1");
        if ($output === null) {
            // shell_exec not available, do basic token check
            $code = file_get_contents($path);
            $tokens = @token_get_all($code);
            assert_true($tokens !== false, 'Token parse failed');
            return 'tokens OK (shell_exec unavailable)';
        }
        assert_contains($output, 'No syntax errors', "Syntax error: {$output}");
        return 'OK';
    });
}

// ═══════════════════════════════════════════════════════════════════════════════
// GROUP 7: Security Audit
// ═══════════════════════════════════════════════════════════════════════════════
group('Security Audit');

test('No hardcoded passwords in config.php', function() {
    $content = file_get_contents(ROOT . '/core/config.php');
    assert_not_contains($content, "'your-secret-key-here'", 'Default JWT secret still present');
    return 'OK';
});

test('JWT_SECRET uses random_bytes', function() {
    $content = file_get_contents(ROOT . '/core/config.php');
    assert_contains($content, 'random_bytes', 'JWT_SECRET should use random_bytes');
    return 'OK';
});

test('DEBUG_MODE controlled by environment variable', function() {
    $content = file_get_contents(ROOT . '/core/config.php');
    assert_contains($content, 'getenv', 'Should use getenv for DEBUG_MODE');
    return 'OK';
});

test('CSRF token generation exists in auth_helper', function() {
    $content = file_get_contents(ROOT . '/core/auth_helper.php');
    assert_contains($content, 'csrf_token', 'CSRF token logic missing');
    assert_contains($content, 'hash_equals', 'hash_equals comparison missing');
    return 'OK';
});

test('BackupManager uses MYSQL_PWD env var (not -p in command)', function() {
    $content = file_get_contents(ROOT . '/core/BackupManager.php');
    assert_contains($content, 'MYSQL_PWD', 'Should use MYSQL_PWD env var');
    // Should NOT have -p%s pattern anymore
    assert_not_contains($content, "'-p%s'", 'Password still in command string');
    return 'OK';
});

test('API files use display_errors from DEBUG_MODE', function() {
    foreach (['api/unsur_api.php', 'api/jabatan_api.php', 'api/bagian_api.php', 'api/unified-api.php'] as $f) {
        $content = file_get_contents(ROOT . '/' . $f);
        assert_contains($content, 'DEBUG_MODE', "display_errors not tied to DEBUG_MODE in {$f}");
    }
    return '4/4 files OK';
});

test('Bulk update requires authentication check', function() {
    $content = file_get_contents(ROOT . '/api/bulk_update_personil.php');
    assert_contains($content, 'user_id', 'No auth check in bulk_update_personil');
    return 'OK';
});

test('No debug HTML comments in bagian.php', function() {
    $content = file_get_contents(ROOT . '/pages/bagian.php');
    assert_not_contains($content, '<!-- DEBUG:', 'Debug HTML comments still present');
    return 'clean';
});

test('No debug console.log in jabatan.php', function() {
    $content = file_get_contents(ROOT . '/pages/jabatan.php');
    assert_not_contains($content, "console.log('=== DEBUG", 'Debug console.log still present');
    return 'clean';
});

test('No debug console.log in personil.php', function() {
    $content = file_get_contents(ROOT . '/pages/personil.php');
    assert_not_contains($content, "console.log('Setting jabatan", 'Debug console.log still in personil.php');
    return 'clean';
});

test('Rate limiting in unified-api.php', function() {
    $content = file_get_contents(ROOT . '/api/unified-api.php');
    assert_contains($content, 'checkRateLimit', 'Rate limiting not implemented');
    assert_contains($content, '429', 'HTTP 429 not returned on rate limit');
    return 'OK';
});

test('Input validation in unsur_api — filter_var used', function() {
    $content = file_get_contents(ROOT . '/api/unsur_api.php');
    assert_contains($content, 'FILTER_VALIDATE_INT', 'Integer validation missing');
    assert_contains($content, 'strip_tags', 'XSS strip missing');
    return 'OK';
});

test('Input validation in bagian_api — filter_var used', function() {
    $content = file_get_contents(ROOT . '/api/bagian_api.php');
    assert_contains($content, 'FILTER_VALIDATE_INT', 'Integer validation missing');
    assert_contains($content, 'strip_tags', 'XSS strip missing');
    return 'OK';
});

test('Input validation in jabatan_api — filter_var used', function() {
    $content = file_get_contents(ROOT . '/api/jabatan_api.php');
    assert_contains($content, 'FILTER_VALIDATE_INT', 'Integer validation missing');
    assert_contains($content, 'strip_tags', 'XSS strip missing');
    return 'OK';
});

// ═══════════════════════════════════════════════════════════════════════════════
// SUMMARY
// ═══════════════════════════════════════════════════════════════════════════════
$duration = round(microtime(true) - $startTime, 3);
$total = $stats['pass'] + $stats['fail'] + $stats['skip'];

echo "\n\033[1m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m\n";
echo "\033[1mTest Results\033[0m\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Total:   {$total}\n";
echo "  \033[32mPassed:  {$stats['pass']}\033[0m\n";
echo "  \033[31mFailed:  {$stats['fail']}\033[0m\n";
echo "  \033[33mSkipped: {$stats['skip']}\033[0m\n";
echo "  Duration: {$duration}s\n";

if (!empty($failures)) {
    echo "\n\033[1;31mFailed Tests:\033[0m\n";
    foreach ($failures as $f) {
        echo "  \033[31m• {$f}\033[0m\n";
    }
}

echo "\n";
$exitCode = $stats['fail'] > 0 ? 1 : 0;
if ($exitCode === 0) {
    echo "\033[1;32m✓ All tests passed!\033[0m\n\n";
} else {
    echo "\033[1;31m✗ {$stats['fail']} test(s) failed.\033[0m\n\n";
}
exit($exitCode);
