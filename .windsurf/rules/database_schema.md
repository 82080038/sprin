---
description: Aturan dan panduan penggunaan database
---

# Database Rules - SPRIN

## Connection

**ALWAYS** gunakan singleton Database class:
```php
$db = Database::getInstance();
$connection = $db->getConnection();
```

JANGAN buat koneksi PDO manual - gunakan class yang sudah ada.

## Query Standards

### 1. Prepared Statements (WAJIB)
```php
// ✅ BENAR
$sql = "SELECT * FROM personil WHERE id = :id AND is_deleted = FALSE";
$stmt = $db->query($sql, ['id' => $id]);

// ❌ SALAH - SQL Injection risk
$sql = "SELECT * FROM personil WHERE id = $id";
```

### 2. Soft Delete Pattern
Semua tabel utama menggunakan soft delete:
```sql
-- SELECT selalu filter is_deleted
SELECT * FROM personil WHERE is_deleted = FALSE;

-- DELETE menggunakan method softDelete
UPDATE personil SET is_deleted = TRUE, updated_at = NOW() WHERE id = :id;
```

### 3. Timestamp Management
Gunakan auto timestamps:
- `created_at` - DEFAULT CURRENT_TIMESTAMP
- `updated_at` - ON UPDATE CURRENT_TIMESTAMP

### 4. Foreign Key Validation
Selalu validate foreign key exists sebelum insert:
```php
// Check bagian exists
$bagian = $db->fetchOne("SELECT id FROM bagian WHERE id = :id", ['id' => $bagianId]);
if (!$bagian) {
    throw new Exception('Bagian tidak ditemukan');
}
```

## Transaction Management

**Gunakan untuk operasi multi-step:**
```php
try {
    $db->beginTransaction();
    
    // Step 1: Insert personil
    $personilId = $db->insert('personil', $personilData);
    
    // Step 2: Insert kontak
    foreach ($kontakData as $kontak) {
        $kontak['personil_id'] = $personilId;
        $db->insert('personil_kontak', $kontak);
    }
    
    // Step 3: Insert pendidikan
    foreach ($pendidikanData as $pendidikan) {
        $pendidikan['personil_id'] = $personilId;
        $db->insert('personil_pendidikan', $pendidikan);
    }
    
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}
```

## Pagination Standards

**Gunakan parameter: limit dan offset**
```php
$page = $_GET['page'] ?? 1;
$perPage = min($_GET['per_page'] ?? 20, 100); // Max 100
$offset = ($page - 1) * $perPage;

$sql = "SELECT * FROM personil 
        WHERE is_deleted = FALSE 
        LIMIT :limit OFFSET :offset";
$results = $db->fetchAll($sql, [
    'limit' => $perPage,
    'offset' => $offset
]);
```

## Search & Filtering

### Text Search (LIKE)
```php
$search = $_GET['search'] ?? '';
if ($search) {
    $sql .= " AND (nama_lengkap LIKE :search OR nrk LIKE :search OR nrp LIKE :search)";
    $params['search'] = '%' . $search . '%';
}
```

### Multiple Values (IN clause)
```php
$bagianIds = $_GET['bagian'] ?? [];
if (!empty($bagianIds)) {
    $placeholders = implode(',', array_fill(0, count($bagianIds), '?'));
    $sql .= " AND id_bagian IN ($placeholders)";
    $params = array_merge($params, $bagianIds);
}
```

### Range Filter
```php
$tanggalDari = $_GET['dari'] ?? null;
$tanggalSampai = $_GET['sampai'] ?? null;

if ($tanggalDari && $tanggalSampai) {
    $sql .= " AND tanggal_lahir BETWEEN :dari AND :sampai";
    $params['dari'] = $tanggalDari;
    $params['sampai'] = $tanggalSampai;
}
```

## Sorting

**Whitelist allowed columns:**
```php
$allowedSort = ['nama_lengkap', 'nrk', 'tanggal_lahir', 'created_at'];
$sortField = $_GET['sort'] ?? 'nama_lengkap';
$sortDir = strtoupper($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

if (in_array($sortField, $allowedSort)) {
    $sql .= " ORDER BY {$sortField} {$sortDir}";
}
```

## Index Optimization

**Kolom yang harus di-index:**
- Primary keys (auto by MySQL)
- Foreign keys
- Kolom yang sering di-search (nama_lengkap, nrk, nrp)
- Kolom yang sering di-filter (id_bagian, id_pangkat, status_pegawai)
- Kolom yang sering di-sort (created_at, nama_lengkap)

```sql
CREATE INDEX idx_personil_nama ON personil(nama_lengkap);
CREATE INDEX idx_personil_nrk ON personil(nrk);
CREATE INDEX idx_personil_bagian ON personil(id_bagian);
CREATE INDEX idx_personil_status ON personil(status_pegawai, is_deleted);
```

## Backup & Restore

**Gunakan BackupManager class:**
```php
require_once __DIR__ . '/../core/BackupManager.php';

// Backup
$backupManager = new BackupManager();
$backupFile = $backupManager->createBackup();

// Restore
$backupManager->restoreBackup($backupFile);
```

## Data Migration

**Gunakan DatabaseOptimizer untuk maintenance:**
```php
require_once __DIR__ . '/../core/DatabaseOptimizer.php';

$optimizer = new DatabaseOptimizer();
$optimizer->optimizeTables();
$optimizer->updateStatistics();
```

## Forbidden Patterns

❌ **JANGAN:**
- DROP table tanpa backup
- TRUNCATE table production tanpa approval
- Update data tanpa WHERE clause
- Delete tanpa soft delete (kecuali memang hard delete)
- SELECT * untuk data besar (gunakan pagination)
- Nested queries tanpa limit
- UNION tanpa parentheses

✅ **LAKUKAN:**
- Backup sebelum migration besar
- Gunakan transaction untuk multi-table operations
- Test query dengan EXPLAIN untuk performance
- Gunakan LIMIT untuk testing
- Log semua DDL operations

## Common Queries Reference

### Get Personil dengan Relasi
```php
$sql = "
    SELECT p.*, 
           pk.nama_pangkat, pk.golongan,
           j.nama_jabatan,
           b.nama_bagian, u.nama_unsur
    FROM personil p
    LEFT JOIN pangkat pk ON p.id_pangkat = pk.id
    LEFT JOIN jabatan j ON p.id_jabatan = j.id
    LEFT JOIN bagian b ON p.id_bagian = b.id
    LEFT JOIN unsur u ON b.id_unsur = u.id
    WHERE p.is_deleted = FALSE
";
```

### Count by Unsur
```php
$sql = "
    SELECT u.nama_unsur, COUNT(p.id) as total
    FROM personil p
    JOIN bagian b ON p.id_bagian = b.id
    JOIN unsur u ON b.id_unsur = u.id
    WHERE p.is_deleted = FALSE
    GROUP BY u.id, u.nama_unsur
";
```

### Get Personil dengan Kontak & Pendidikan
```php
// Gunakan separate queries untuk avoid N+1 problem
$personil = $db->fetchOne("SELECT * FROM personil WHERE id = :id", ['id' => $id]);
$kontak = $db->fetchAll("SELECT * FROM personil_kontak WHERE personil_id = :id", ['id' => $id]);
$pendidikan = $db->fetchAll("SELECT * FROM personil_pendidikan WHERE personil_id = :id", ['id' => $id]);
```

## Validation Rules

**Personil:**
- NRK: required, unique, max 20 chars
- NRP: required, unique, max 20 chars  
- Nama Lengkap: required, max 100 chars
- ID Pangkat: required, exists in pangkat table
- ID Bagian: required, exists in bagian table
- Jenis Kelamin: required, enum(L, P)
- Jenis Pegawai: required, enum(polri, asn, p3k)

**Bagian:**
- Kode Bagian: required, unique, max 50 chars
- Nama Bagian: required, max 100 chars
- ID Unsur: required, exists in unsur table

## Error Messages

**Gunakan pesan error yang user-friendly:**
```php
// Database errors (log detail, show simple message)
try {
    $db->query($sql, $params);
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());  // Log detail
    throw new Exception("Gagal menyimpan data. Silakan coba lagi.");  // User message
}

// Validation errors
if (empty($nrk)) {
    throw new Exception('NRK wajib diisi');
}
if (strlen($nrk) > 20) {
    throw new Exception('NRK maksimal 20 karakter');
}
```
