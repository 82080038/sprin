<?php
/**
 * MySQL to PDO Migration Example
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../core/config.php';

/**
 * MySQL to PDO Migration Example
 */
echo '<h1>MySQL to PDO Migration Example</h1>';

echo '<h2>Before (MySQL functions - DEPRECATED):</h2>';
echo '<pre><code>';
echo '// Old way - DEPRECATED
$connection = // // DEPRECATED: // DEPRECATED: // DEPRECATED: mysql_connect( - Use PDO instead - Use PDO instead - Use PDO insteadDEPRECATED - use PDODB_HOST, DB_USER, DB_PASS);
// // DEPRECATED: // DEPRECATED: // DEPRECATED: mysql_select_db( - Use PDO instead - Use PDO instead - Use PDO insteadDEPRECATED - use PDODB_NAME, $connection);

// Query
$result = // // DEPRECATED: // DEPRECATED: // DEPRECATED: mysql_query( - Use PDO instead - Use PDO instead - Use PDO insteadDEPRECATED - use PDO"SELECT * FROM personil", $connection);

// Fetch data
while ($row = // // DEPRECATED: // DEPRECATED: // DEPRECATED: mysql_fetch_assoc( - Use PDO instead - Use PDO instead - Use PDO insteadDEPRECATED - use PDO::fetch$result)) {
    echo $row["nama_lengkap"] . "\\n";
}

// Close connection
// // DEPRECATED: // DEPRECATED: // DEPRECATED: mysql_close( - Use PDO instead - Use PDO instead - Use PDO insteadDEPRECATED - PDO auto-closes$connection);
</code></pre>';

echo '<h2>After (PDO - MODERN):</h2>';
echo '<pre><code>';
echo '// New way - MODERN
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";unix_socket=" . DB_SOCKET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Query
    $stmt = $pdo->prepare("SELECT * FROM personil");
    $stmt->execute();

    // Fetch data
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row["nama_lengkap"] . "\\n";
    }

    // Connection automatically closed when object is destroyed

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
</code></pre>';

echo '<h2>Benefits of PDO:</h2>';
echo '<ul>';
echo '<li>✅ Better security (prepared statements)</li>';
echo '<li>✅ Object-oriented interface</li>';
echo '<li>✅ Consistent API across databases</li>';
echo '<li>✅ Better error handling</li>';
echo '<li>✅ Support for multiple database types</li>';
echo '</ul>';

echo '<h2>Migration Steps:</h2>';
echo '<ol>';
echo '<li>Replace // // DEPRECATED: // DEPRECATED: // DEPRECATED: mysql_connect( - Use PDO instead - Use PDO instead - Use PDO insteadDEPRECATED - use PDO) with PDO constructor</li>';
echo '<li>Replace // // DEPRECATED: // DEPRECATED: // DEPRECATED: mysql_query( - Use PDO instead - Use PDO instead - Use PDO insteadDEPRECATED - use PDO) with PDO::prepare() and execute()</li>';
echo '<li>Replace mysql_fetch_*() with PDO::fetch()</li>';
echo '<li>Add proper error handling with try-catch</li>';
echo '<li>Use prepared statements for security</li>';
echo '</ol>';
?>
