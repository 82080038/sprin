<?php
declare(strict_types=1);
/**
 * Simple pangkat update API
 */

require_once 'core/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Get POST data
$action = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? '';
$nrp = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'nrp', FILTER_SANITIZE_STRING) ?? '';
$id_pangkat = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'id_pangkat', FILTER_SANITIZE_STRING) ?? '';

if ($action === 'update_pangkat' && $nrp && $id_pangkat) {
    // Update pangkat
    $sql = "UPDATE personil SET id_pangkat = ? WHERE nrp = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "is", $id_pangkat, $nrp);
    
    if (mysqli_stmt_execute($stmt)) {
        $affected = mysqli_stmt_affected_rows($stmt);
        if ($affected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Pangkat updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No rows affected - NRP not found'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Update failed: ' . mysqli_error($koneksi)
        ]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid parameters'
    ]);
}

mysqli_close($koneksi);
?>
