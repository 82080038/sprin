---
description: Skill untuk melakukan migrasi database
---

# Database Migration Skill

## Overview

Skill ini untuk melakukan perubahan schema database dengan aman menggunakan backup dan rollback mechanism.

## Migration Workflow

### 1. Backup Database (WAJIB)

```php
require_once __DIR__ . '/core/BackupManager.php';

$backupManager = new BackupManager();
$backupFile = $backupManager->createBackup('pre_migration_{description}');
echo "Backup created: $backupFile\n";
```

Atau via command line:
```bash
mysqldump -u root -p bagops > /opt/lampp/htdocs/sprint/backups/bagops_backup_$(date +%Y%m%d_%H%M%S).sql
```

### 2. Create Migration File

Format nama: `YYYY_MM_DD_description.sql`

```sql
-- Migration: 2026_03_31_add_proyek_table
-- Description: Add proyek table for project management

-- Step 1: Create new table
CREATE TABLE IF NOT EXISTS proyek (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama_proyek VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    tanggal_mulai DATE,
    tanggal_selesai DATE,
    status ENUM('planning', 'ongoing', 'completed', 'cancelled') DEFAULT 'planning',
    created_by INT(11),
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Step 2: Add indexes
CREATE INDEX idx_proyek_status ON proyek(status);
CREATE INDEX idx_proyek_tanggal ON proyek(tanggal_mulai, tanggal_selesai);

-- Step 3: Add foreign key (jika ada relasi)
-- ALTER TABLE proyek ADD CONSTRAINT fk_proyek_created_by 
--     FOREIGN KEY (created_by) REFERENCES personil(id);

-- Log migration
INSERT INTO migrations (filename, executed_at, status) 
VALUES ('2026_03_31_add_proyek_table.sql', NOW(), 'success');
```

### 3. Execute Migration

**Via PHP Script:**
```php
<?php
// migrate.php
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/Database.php';

$migrationFile = $argv[1] ?? null;

if (!$migrationFile) {
    echo "Usage: php migrate.php <migration_file>\n";
    exit(1);
}

if (!file_exists($migrationFile)) {
    echo "Migration file not found: $migrationFile\n";
    exit(1);
}

$db = Database::getInstance();

try {
    $db->beginTransaction();
    
    $sql = file_get_contents($migrationFile);
    $db->query($sql);
    
    $db->commit();
    echo "Migration executed successfully!\n";
} catch (Exception $e) {
    $db->rollback();
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
```

**Via MySQL CLI:**
```bash
/opt/lampp/bin/mysql -u root -p bagops < 2026_03_31_add_proyek_table.sql
```

### 4. Create Rollback Script

```sql
-- Rollback: 2026_03_31_add_proyek_table
-- Description: Remove proyek table

-- Step 1: Drop foreign keys (jika ada)
-- ALTER TABLE proyek DROP FOREIGN KEY fk_proyek_created_by;

-- Step 2: Drop table
DROP TABLE IF EXISTS proyek;

-- Step 3: Update migration log
UPDATE migrations SET rolled_back_at = NOW(), status = 'rolled_back' 
WHERE filename = '2026_03_31_add_proyek_table.sql';
```

## Common Migration Patterns

### Add Column
```sql
-- Add column
ALTER TABLE personil ADD COLUMN alamat_domisili TEXT AFTER alamat;

-- Add with default
ALTER TABLE personil ADD COLUMN status_kesehatan 
    ENUM('sehat', 'sakit_ringan', 'sakit_berat') DEFAULT 'sehat';
```

### Modify Column
```sql
-- Change column type
ALTER TABLE personil MODIFY COLUMN no_telepon VARCHAR(20);

-- Rename column
ALTER TABLE personil CHANGE COLUMN alamat alamat_lengkap TEXT;
```

### Create Index
```sql
-- Single column index
CREATE INDEX idx_personil_nama ON personil(nama_lengkap);

-- Composite index
CREATE INDEX idx_personil_bagian_status ON personil(id_bagian, status_pegawai);

-- Fulltext index (untuk search)
CREATE FULLTEXT INDEX idx_personil_search ON personil(nama_lengkap, nrk, nrp);
```

### Add Foreign Key
```sql
ALTER TABLE {child_table} 
ADD CONSTRAINT fk_{child}_{parent} 
FOREIGN KEY ({child_column}) 
REFERENCES {parent_table}({parent_column})
ON DELETE CASCADE
ON UPDATE CASCADE;
```

### Create Junction Table (Many-to-Many)
```sql
CREATE TABLE personil_proyek (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    personil_id INT(11) NOT NULL,
    proyek_id INT(11) NOT NULL,
    peran VARCHAR(100),
    tanggal_mulai DATE,
    tanggal_selesai DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (personil_id) REFERENCES personil(id),
    FOREIGN KEY (proyek_id) REFERENCES proyek(id),
    UNIQUE KEY unique_personil_proyek (personil_id, proyek_id)
);
```

## Migration Table

Track migrations dengan tabel:

```sql
CREATE TABLE migrations (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    description TEXT,
    executed_at TIMESTAMP,
    rolled_back_at TIMESTAMP NULL,
    status ENUM('success', 'failed', 'rolled_back') DEFAULT 'success',
    executed_by VARCHAR(100)
);
```

## Batch Migration Script

```php
<?php
// run_migrations.php
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/Database.php';

$migrationsDir = __DIR__ . '/database/migrations';
$db = Database::getInstance();

// Get executed migrations
$executed = $db->fetchAll("SELECT filename FROM migrations WHERE status = 'success'");
$executedFiles = array_column($executed, 'filename');

// Get all migration files
$files = glob($migrationsDir . '/*.sql');
sort($files);

foreach ($files as $file) {
    $filename = basename($file);
    
    if (in_array($filename, $executedFiles)) {
        echo "[SKIP] Already executed: $filename\n";
        continue;
    }
    
    try {
        $db->beginTransaction();
        
        echo "[EXEC] $filename... ";
        $sql = file_get_contents($file);
        $db->query($sql);
        
        // Log migration
        $db->query(
            "INSERT INTO migrations (filename, executed_at, status) VALUES (?, NOW(), 'success')",
            [$filename]
        );
        
        $db->commit();
        echo "OK\n";
    } catch (Exception $e) {
        $db->rollback();
        echo "FAILED: " . $e->getMessage() . "\n";
        
        // Log failed migration
        $db->query(
            "INSERT INTO migrations (filename, executed_at, status) VALUES (?, NOW(), 'failed')",
            [$filename]
        );
        
        exit(1);
    }
}

echo "\nAll migrations executed successfully!\n";
```

## Data Migration (Seeding)

### Insert Master Data
```sql
-- Seed: master_status_proyek.sql
INSERT INTO master_status (kode, nama, deskripsi) VALUES
('PLANNING', 'Planning', 'Tahap perencanaan'),
('ONGOING', 'On Going', 'Sedang berjalan'),
('COMPLETED', 'Completed', 'Selesai'),
('CANCELLED', 'Cancelled', 'Dibatalkan');
```

### Migrate Data from Old Table
```sql
-- Migrate data from old_column to new structure
INSERT INTO new_table (field1, field2, field3)
SELECT 
    old_field1,
    old_field2,
    CONCAT(old_field3, ' ', old_field4) as field3
FROM old_table
WHERE is_deleted = FALSE;
```

## Checklist

Sebelum menjalankan migration:
- [ ] Backup database terbaru tersedia
- [ ] Test migration di development environment
- [ ] Rollback script siap
- [ ] Migration file di-review
- [ ] No destructive operations tanpa backup
- [ ] Indexes sudah dipertimbangkan untuk performance
- [ ] Foreign key constraints valid

Setelah migration:
- [ ] Verifikasi struktur table baru
- [ ] Test CRUD operations
- [ ] Check performance dengan EXPLAIN
- [ ] Update aplikasi code untuk menggunakan schema baru
