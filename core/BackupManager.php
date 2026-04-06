<?php
/**
 * Backup System for SPRIN
 * Automated database and file backup with monitoring
 */

class BackupManager {
    
    private $backupPath;
    private $dbConfig;
    private $retentionDays;
    private $maxBackups;
    
    public function __construct($backupPath = __DIR__ . '/../backups/', $retentionDays = 30) {
        $this->backupPath = $backupPath;
        $this->retentionDays = $retentionDays;
        $this->maxBackups = 50;
        
        // Load database config
        require_once __DIR__ . '/config.php';
        $this->dbConfig = [
            'host' => DB_HOST,
            'database' => DB_NAME,
            'user' => DB_USER,
            'password' => DB_PASS
        ];
        
        // Create backup directory
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }
    
    /**
     * Create full database backup
     */
    public function createDatabaseBackup($filename = null) {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = $filename ?? "db_backup_{$timestamp}.sql";
        $filepath = $this->backupPath . $filename;
        
        // Build mysqldump command
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s --single-transaction --routines --triggers > %s 2>&1',
            escapeshellarg($this->dbConfig['host']),
            escapeshellarg($this->dbConfig['user']),
            escapeshellarg($this->dbConfig['password']),
            escapeshellarg($this->dbConfig['database']),
            escapeshellarg($filepath)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            // Try alternative: PDO backup
            return $this->createPDODatabaseBackup($filepath);
        }
        
        // Compress the backup
        $compressedFile = $filepath . '.gz';
        $compressCommand = "gzip -c " . escapeshellarg($filepath) . " > " . escapeshellarg($compressedFile);
        exec($compressCommand);
        
        // Remove uncompressed file
        if (file_exists($compressedFile)) {
            unlink($filepath);
            $filepath = $compressedFile;
            $filename .= '.gz';
        }
        
        // Log backup
        $this->logBackup('database', $filename, filesize($filepath));
        
        return [
            'success' => true,
            'type' => 'database',
            'filename' => $filename,
            'filepath' => $filepath,
            'size' => $this->formatBytes(filesize($filepath)),
            'created_at' => date('c')
        ];
    }
    
    /**
     * Alternative PDO database backup (when mysqldump not available)
     */
    private function createPDODatabaseBackup($filepath) {
        try {
            $dsn = "mysql:host={$this->dbConfig['host']};dbname={$this->dbConfig['database']}";
            $pdo = new PDO($dsn, $this->dbConfig['user'], $this->dbConfig['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "-- SPRIN Database Backup\n";
            $sql .= "-- Generated: " . date('c') . "\n";
            $sql .= "-- Database: {$this->dbConfig['database']}\n\n";
            
            // Get all tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                // Get create table statement
                $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql .= $create['Create Table'] . ";\n\n";
                
                // Get table data
                $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($rows)) {
                    $columns = array_keys($rows[0]);
                    $columnList = '`' . implode('`, `', $columns) . '`';
                    
                    foreach ($rows as $row) {
                        $values = array_map(function($val) {
                            if ($val === null) return 'NULL';
                            return "'" . addslashes($val) . "'";
                        }, array_values($row));
                        
                        $sql .= "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sql .= "\n";
                }
            }
            
            file_put_contents($filepath, $sql);
            
            // Compress
            $compressedFile = $filepath . '.gz';
            $gz = gzopen($compressedFile, 'w9');
            gzwrite($gz, file_get_contents($filepath));
            gzclose($gz);
            
            if (file_exists($compressedFile)) {
                unlink($filepath);
                $filepath = $compressedFile;
            }
            
            $this->logBackup('database', basename($filepath), filesize($filepath));
            
            return [
                'success' => true,
                'type' => 'database',
                'filename' => basename($filepath),
                'filepath' => $filepath,
                'size' => $this->formatBytes(filesize($filepath)),
                'created_at' => date('c')
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create files backup
     */
    public function createFilesBackup($directories = [], $filename = null) {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = $filename ?? "files_backup_{$timestamp}.zip";
        $filepath = $this->backupPath . $filename;
        
        $defaultDirs = [
            'public/assets/uploads',
            'docs',
            'exports'
        ];
        
        $directories = !empty($directories) ? $directories : $defaultDirs;
        $basePath = __DIR__ . '/../';
        
        $zip = new ZipArchive();
        if ($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return [
                'success' => false,
                'error' => 'Failed to create zip file'
            ];
        }
        
        foreach ($directories as $dir) {
            $fullPath = $basePath . $dir;
            if (is_dir($fullPath)) {
                $this->addDirectoryToZip($zip, $fullPath, $dir);
            }
        }
        
        $zip->close();
        
        $this->logBackup('files', $filename, filesize($filepath));
        
        return [
            'success' => true,
            'type' => 'files',
            'filename' => $filename,
            'filepath' => $filepath,
            'size' => $this->formatBytes(filesize($filepath)),
            'directories' => $directories,
            'created_at' => date('c')
        ];
    }
    
    /**
     * Add directory to zip recursively
     */
    private function addDirectoryToZip($zip, $path, $localPath) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $localPath . '/' . substr($filePath, strlen($path) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    
    /**
     * Create full backup (database + files)
     */
    public function createFullBackup() {
        $timestamp = date('Y-m-d_H-i-s');
        
        // Create database backup
        $dbBackup = $this->createDatabaseBackup("db_{$timestamp}.sql");
        
        // Create files backup
        $filesBackup = $this->createFilesBackup([], "files_{$timestamp}.zip");
        
        // Create manifest
        $manifest = [
            'timestamp' => $timestamp,
            'database' => $dbBackup['success'] ? $dbBackup['filename'] : null,
            'files' => $filesBackup['success'] ? $filesBackup['filename'] : null,
            'created_at' => date('c')
        ];
        
        $manifestPath = $this->backupPath . "manifest_{$timestamp}.json";
        file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT));
        
        return [
            'success' => $dbBackup['success'] || $filesBackup['success'],
            'timestamp' => $timestamp,
            'database' => $dbBackup,
            'files' => $filesBackup,
            'manifest' => $manifestPath
        ];
    }
    
    /**
     * List all backups
     */
    public function listBackups($type = null) {
        $backups = [];
        $files = glob($this->backupPath . '*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                $fileType = $this->getBackupType($filename);
                
                if ($type === null || $fileType === $type) {
                    $backups[] = [
                        'filename' => $filename,
                        'type' => $fileType,
                        'size' => $this->formatBytes(filesize($file)),
                        'size_bytes' => filesize($file),
                        'created_at' => date('c', filemtime($file)),
                        'age_days' => floor((time() - filemtime($file)) / 86400)
                    ];
                }
            }
        }
        
        // Sort by date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $backups;
    }
    
    /**
     * Get backup type from filename
     */
    private function getBackupType($filename) {
        if (strpos($filename, 'db_') === 0) return 'database';
        if (strpos($filename, 'files_') === 0) return 'files';
        if (strpos($filename, 'manifest_') === 0) return 'manifest';
        return 'unknown';
    }
    
    /**
     * Restore database from backup
     */
    public function restoreDatabase($filename) {
        $filepath = $this->backupPath . $filename;
        
        if (!file_exists($filepath)) {
            return [
                'success' => false,
                'error' => 'Backup file not found'
            ];
        }
        
        // Check if compressed
        if (substr($filepath, -3) === '.gz') {
            // Decompress first
            $decompressed = substr($filepath, 0, -3);
            $gz = gzopen($filepath, 'r');
            $content = '';
            while (!gzeof($gz)) {
                $content .= gzread($gz, 4096);
            }
            gzclose($gz);
            file_put_contents($decompressed, $content);
            $filepath = $decompressed;
        }
        
        // Execute SQL
        try {
            $dsn = "mysql:host={$this->dbConfig['host']};dbname={$this->dbConfig['database']}";
            $pdo = new PDO($dsn, $this->dbConfig['user'], $this->dbConfig['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = file_get_contents($filepath);
            $pdo->exec($sql);
            
            return [
                'success' => true,
                'message' => 'Database restored successfully',
                'filename' => $filename
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Clean old backups
     */
    public function cleanOldBackups() {
        $backups = $this->listBackups();
        $deleted = [];
        
        foreach ($backups as $backup) {
            if ($backup['age_days'] > $this->retentionDays) {
                $filepath = $this->backupPath . $backup['filename'];
                if (unlink($filepath)) {
                    $deleted[] = $backup['filename'];
                }
            }
        }
        
        // Also enforce max backup limit
        $totalBackups = count($this->listBackups());
        if ($totalBackups > $this->maxBackups) {
            $allBackups = $this->listBackups();
            $toDelete = array_slice($allBackups, $this->maxBackups);
            
            foreach ($toDelete as $backup) {
                $filepath = $this->backupPath . $backup['filename'];
                if (unlink($filepath)) {
                    $deleted[] = $backup['filename'];
                }
            }
        }
        
        return [
            'success' => true,
            'deleted' => $deleted,
            'count' => count($deleted)
        ];
    }
    
    /**
     * Log backup to database
     */
    private function logBackup($type, $filename, $size) {
        try {
            require_once __DIR__ . '/Database.php';
            $db = Database::getInstance();
            
            // Create backup_logs table if not exists
            $sql = "
                CREATE TABLE IF NOT EXISTS backup_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    type VARCHAR(50) NOT NULL,
                    filename VARCHAR(255) NOT NULL,
                    size_bytes INT,
                    created_by VARCHAR(100),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_type (type),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB
            ";
            $db->query($sql);
            
            // Insert log
            $sql = "
                INSERT INTO backup_logs (type, filename, size_bytes, created_by, created_at)
                VALUES (:type, :filename, :size_bytes, :created_by, NOW())
            ";
            
            $user = $_SESSION['username'] ?? 'system';
            
            $db->query($sql, [
                'type' => $type,
                'filename' => $filename,
                'size_bytes' => $size,
                'created_by' => $user
            ]);
            
        } catch (Exception $e) {
            error_log("Failed to log backup: " . $e->getMessage());
        }
    }
    
    /**
     * Get backup statistics
     */
    public function getBackupStats() {
        $backups = $this->listBackups();
        
        $totalSize = 0;
        $dbCount = 0;
        $filesCount = 0;
        
        foreach ($backups as $backup) {
            $totalSize += $backup['size_bytes'];
            if ($backup['type'] === 'database') $dbCount++;
            if ($backup['type'] === 'files') $filesCount++;
        }
        
        return [
            'total_backups' => count($backups),
            'database_backups' => $dbCount,
            'files_backups' => $filesCount,
            'total_size' => $this->formatBytes($totalSize),
            'total_size_bytes' => $totalSize,
            'retention_days' => $this->retentionDays,
            'max_backups' => $this->maxBackups
        ];
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
    
    /**
     * Schedule automated backups
     */
    public function scheduleBackup($type = 'full', $frequency = 'daily') {
        // This would typically be configured via cron job
        // This method returns the recommended cron command
        
        $cronCommands = [
            'daily' => '0 2 * * *',    // 2 AM daily
            'weekly' => '0 2 * * 0',   // 2 AM Sunday
            'monthly' => '0 2 1 * *'   // 2 AM 1st of month
        ];
        
        $cronTime = $cronCommands[$frequency] ?? $cronCommands['daily'];
        $scriptPath = __DIR__ . '/../scripts/backup.php';
        
        $command = "{$cronTime} cd /opt/lampp/htdocs/sprint && php {$scriptPath} {$type} >> /var/log/sprin_backup.log 2>&1";
        
        return [
            'cron_command' => $command,
            'frequency' => $frequency,
            'type' => $type,
            'script_path' => $scriptPath
        ];
    }
}
