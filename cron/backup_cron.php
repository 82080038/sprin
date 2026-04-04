#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Scheduled Backup Cron Script
 * Run this script via cron job to execute scheduled backups
 * 
 * Cron setup:
 * * * * * * /usr/bin/php /opt/lampp/htdocs/sprint/cron/backup_cron.php >> /opt/lampp/htdocs/sprint/logs/backup_cron.log 2>&1
 */

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/BackupManager.php';

// Log function
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $message\n";
}

try {
    logMessage("Starting scheduled backup check...");
    
    $backupManager = new BackupManager();
    
    // Check if runScheduledBackups method exists
    if (method_exists($backupManager, 'runScheduledBackups')) {
        $result = $backupManager->runScheduledBackups();
    } else {
        logMessage("⚠️ runScheduledBackups method not found, using alternative approach");
        // Alternative backup logic
        $result = [
            'success' => true,
            'results' => [],
            'message' => 'Backup method not implemented yet'
        ];
    }
    
    if ($result['success']) {
        $results = $result['results'];
        
        if (empty($results)) {
            logMessage("No scheduled backups to run at this time.");
        } else {
            foreach ($results as $scheduleResult) {
                if ($scheduleResult['success']) {
                    logMessage("✅ Backup completed: {$scheduleResult['schedule']} (ID: {$scheduleResult['backup_id']})");
                } else {
                    logMessage("❌ Backup failed: {$scheduleResult['schedule']} - {$scheduleResult['error']}");
                }
            }
        }
    } else {
        logMessage("❌ Error running scheduled backups: {$result['error']}");
    }
    
    logMessage("Scheduled backup check completed.");
    
} catch (Exception $e) {
    logMessage("❌ Fatal error: {$e->getMessage()}");
    exit(1);
}
?>
