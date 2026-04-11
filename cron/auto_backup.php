<?php
/**
 * Cron Job: Automatic Daily Database Backup
 * Runs daily to create database backups with retention management
 * 
 * Usage: Add to crontab: 0 2 * * * php /opt/lampp/htdocs/sprin/cron/auto_backup.php
 * This runs daily at 2 AM
 */

require_once __DIR__ . '/../core/BackupManager.php';

try {
    $backupManager = new BackupManager(__DIR__ . '/../backups/', 30);
    
    echo "=== Automatic Backup Started: " . date('Y-m-d H:i:s') . " ===\n";
    
    // Create backup
    $result = $backupManager->createDatabaseBackup();
    
    if ($result['success']) {
        echo "✅ Backup created: " . $result['filename'] . "\n";
        echo "   Size: " . round($result['size'] / 1024 / 1024, 2) . " MB\n";
    } else {
        echo "❌ Backup failed: " . $result['message'] . "\n";
        exit(1);
    }
    
    // Clean old backups (retention policy)
    $cleanupResult = $backupManager->cleanupOldBackups();
    echo "🧹 Cleanup: " . $cleanupResult['deleted'] . " old backups removed\n";
    
    // Get backup stats
    $stats = $backupManager->getBackupStats();
    echo "📊 Total backups: " . $stats['total'] . "\n";
    echo "   Total size: " . round($stats['total_size'] / 1024 / 1024, 2) . " MB\n";
    
    echo "=== Backup Completed Successfully ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
