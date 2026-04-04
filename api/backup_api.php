<?php
declare(strict_types=1);
/**
 * Backup API
 * CRUD operations for backup management
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/BackupManager.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Check authentication
if (!AuthHelper::validateSession()) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'timestamp' => date('c')
    ]);
    exit;
}

$action = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'action', FILTER_SANITIZE_STRING) ?? '';

try {
    $backupManager = new BackupManager();
    
    switch ($action) {
        case 'list':
            $result = $backupManager->getBackups(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'limit', FILTER_SANITIZE_STRING) ?? 20);
            echo json_encode([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Backups retrieved' : $result['error'],
                'data' => ['backups' => $result['backups'] ?? []],
                'timestamp' => date('c')
            ]);
            break;
            
        case 'create':
            $type = filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'type', FILTER_SANITIZE_STRING) ?? 'full';
            $tables = !empty(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tables', FILTER_SANITIZE_STRING)) ? explode(',', filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'tables', FILTER_SANITIZE_STRING)) : [];
            
            $result = $backupManager->createBackup($type, $tables, $_SESSION['user_id'] ?? null);
            
            echo json_encode([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Backup created successfully' : $result['error'],
                'data' => $result['success'] ? [
                    'backup_id' => $result['backup_id'],
                    'filename' => $result['filename'],
                    'file_size' => $result['file_size'],
                    'checksum' => $result['checksum']
                ] : null,
                'timestamp' => date('c')
            ]);
            break;
            
        case 'restore':
            $backupId = intval(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'backup_id', FILTER_SANITIZE_STRING) ?? 0);
            if (!$backupId) {
                throw new Exception('Backup ID required');
            }
            
            $result = $backupManager->restoreBackup($backupId);
            
            echo json_encode([
                'success' => $result['success'],
                'message' => $result['success'] ? $result['message'] : $result['error'],
                'timestamp' => date('c')
            ]);
            break;
            
        case 'delete':
            $backupId = intval(filter_input($_POST === \$_GET ? INPUT_GET : ($_POST === \$_POST ? INPUT_POST : INPUT_REQUEST), 'backup_id', FILTER_SANITIZE_STRING) ?? 0);
            if (!$backupId) {
                throw new Exception('Backup ID required');
            }
            
            $result = $backupManager->deleteBackup($backupId);
            
            echo json_encode([
                'success' => $result['success'],
                'message' => $result['success'] ? $result['message'] : $result['error'],
                'timestamp' => date('c')
            ]);
            break;
            
        case 'download':
            $backupId = intval(filter_input($_GET === \$_GET ? INPUT_GET : ($_GET === \$_POST ? INPUT_POST : INPUT_REQUEST), 'backup_id', FILTER_SANITIZE_STRING) ?? 0);
            if (!$backupId) {
                throw new Exception('Backup ID required');
            }
            
            // Get backup info
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM backups WHERE id = ?");
            $stmt->execute([$backupId]);
            $backup = $stmt->fetch();
            
            if (!$backup || !file_exists($backup['file_path'])) {
                throw new Exception('Backup file not found');
            }
            
            // Send file
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $backup['filename'] . '"');
            header('Content-Length: ' . filesize($backup['file_path']));
            readfile($backup['file_path']);
            exit;
            
        case 'run_scheduled':
            // Admin only
            $result = $backupManager->runScheduledBackups();
            
            echo json_encode([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Scheduled backups completed' : $result['error'],
                'data' => ['results' => $result['results'] ?? []],
                'timestamp' => date('c')
            ]);
            break;
            
        case 'stats':
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            // Get backup stats
            $stmt = $pdo->query("SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(file_size) as total_size
                FROM backups");
            $stats = $stmt->fetch();
            
            // Get latest backup
            $stmt = $pdo->query("SELECT * FROM backups WHERE status = 'completed' ORDER BY created_at DESC LIMIT 1");
            $latest = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'message' => 'Backup statistics retrieved',
                'data' => [
                    'stats' => $stats,
                    'latest_backup' => $latest
                ],
                'timestamp' => date('c')
            ]);
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
}

?>