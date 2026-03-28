-- =====================================================
-- COMPLETE DATABASE SCHEMA - POLRES SAMOSIR PERSONIL SYSTEM
-- Created: 29 Maret 2026
-- Purpose: Complete relational database with unsur, bagian, jabatan, personil
-- =====================================================

-- =====================================================
-- 1. TABEL UNSUR (Master Unsur Organisasi POLRI)
-- =====================================================
CREATE TABLE IF NOT EXISTS unsur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_unsur VARCHAR(50) NOT NULL UNIQUE,
    nama_unsur VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    tingkat VARCHAR(50) DEFAULT 'POLRES',
    urutan INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- 2. TABEL BAGIAN (Master Bagian/Satuan)
-- =====================================================
CREATE TABLE IF NOT EXISTS bagian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_bagian VARCHAR(50) NOT NULL UNIQUE,
    nama_bagian VARCHAR(100) NOT NULL,
    id_unsur INT,
    deskripsi TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_unsur) REFERENCES unsur(id) ON DELETE SET NULL
);

-- =====================================================
-- 3. TABEL JABATAN (Master Jabatan)
-- =====================================================
CREATE TABLE IF NOT EXISTS jabatan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_jabatan VARCHAR(50) NOT NULL UNIQUE,
    nama_jabatan VARCHAR(100) NOT NULL,
    id_unsur INT,
    tingkat_jabatan VARCHAR(50),
    eselon VARCHAR(20),
    golongan VARCHAR(20),
    is_pimpinan BOOLEAN DEFAULT FALSE,
    is_pembantu_pimpinan BOOLEAN DEFAULT FALSE,
    is_kepala_unit BOOLEAN DEFAULT FALSE,
    deskripsi TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_unsur) REFERENCES unsur(id) ON DELETE SET NULL
);

-- =====================================================
-- 4. TABEL PANGKAT (Master Pangkat)
-- =====================================================
CREATE TABLE IF NOT EXISTS pangkat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_pangkat VARCHAR(20) NOT NULL UNIQUE,
    nama_pangkat VARCHAR(100) NOT NULL,
    tingkat VARCHAR(50),
    golongan VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- 5. TABEL PERSONIL (Main Personil Table)
-- =====================================================
CREATE TABLE IF NOT EXISTS personil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nrp VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(255) NOT NULL,
    pangkat_id INT,
    jabatan_id INT,
    bagian_id INT,
    status_ket VARCHAR(20),
    status_kepegawaian VARCHAR(20),
    tanggal_lahir DATE,
    tanggal_masuk DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign keys to master tables
    id_unsur INT,
    id_bagian INT,
    id_jabatan INT,
    
    FOREIGN KEY (pangkat_id) REFERENCES pangkat(id) ON DELETE SET NULL,
    FOREIGN KEY (jabatan_id) REFERENCES jabatan(id) ON DELETE SET NULL,
    FOREIGN KEY (bagian_id) REFERENCES bagian(id) ON DELETE SET NULL,
    FOREIGN KEY (id_unsur) REFERENCES unsur(id) ON DELETE SET NULL,
    FOREIGN KEY (id_bagian) REFERENCES bagian(id) ON DELETE SET NULL,
    FOREIGN KEY (id_jabatan) REFERENCES jabatan(id) ON DELETE SET NULL
);

-- =====================================================
-- 6. INDEXES for Performance
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_personil_unsur ON personil(id_unsur);
CREATE INDEX IF NOT EXISTS idx_personil_bagian ON personil(id_bagian);
CREATE INDEX IF NOT EXISTS idx_personil_jabatan ON personil(id_jabatan);
CREATE INDEX IF NOT EXISTS idx_personil_pangkat ON personil(pangkat_id);
CREATE INDEX IF NOT EXISTS idx_personil_nrp ON personil(nrp);
CREATE INDEX IF NOT EXISTS idx_personil_nama ON personil(nama);

CREATE INDEX IF NOT EXISTS idx_bagian_unsur ON bagian(id_unsur);
CREATE INDEX IF NOT EXISTS idx_bagian_kode ON bagian(kode_bagian);

CREATE INDEX IF NOT EXISTS idx_jabatan_unsur ON jabatan(id_unsur);
CREATE INDEX IF NOT EXISTS idx_jabatan_kode ON jabatan(kode_jabatan);
CREATE INDEX IF NOT EXISTS idx_jabatan_tingkat ON jabatan(tingkat_jabatan);

CREATE INDEX IF NOT EXISTS idx_unsur_kode ON unsur(kode_unsur);
CREATE INDEX IF NOT EXISTS idx_unsur_urutan ON unsur(urutan);

-- =====================================================
-- 7. SAMPLE QUERIES
-- =====================================================

-- Query 1: Complete Personil Data with All References
SELECT 
    p.id,
    p.nrp,
    p.nama,
    pa.nama_pangkat,
    j.nama_jabatan,
    b.nama_bagian,
    u.nama_unsur,
    p.status_ket,
    p.status_kepegawaian
FROM personil p
LEFT JOIN pangkat pa ON p.pangkat_id = pa.id
LEFT JOIN jabatan j ON p.id_jabatan = j.id
LEFT JOIN bagian b ON p.id_bagian = b.id
LEFT JOIN unsur u ON p.id_unsur = u.id
ORDER BY u.urutan, b.nama_bagian, p.nama;

-- Query 2: Personil by Unsur
SELECT 
    u.nama_unsur,
    COUNT(p.id) as total_personil,
    GROUP_CONCAT(DISTINCT b.nama_bagian) as bagian_list
FROM unsur u
LEFT JOIN personil p ON u.id = p.id_unsur
LEFT JOIN bagian b ON p.id_bagian = b.id
GROUP BY u.id, u.nama_unsur
ORDER BY u.urutan;

-- Query 3: Pimpinan and Pembantu Pimpinan
SELECT 
    p.nama,
    p.nrp,
    j.nama_jabatan,
    b.nama_bagian,
    u.nama_unsur,
    CASE 
        WHEN j.is_pimpinan THEN 'PIMPINAN'
        WHEN j.is_pembantu_pimpinan THEN 'PEMBANTU PIMPINAN'
        ELSE 'STAFF'
    END as kategori
FROM personil p
JOIN jabatan j ON p.id_jabatan = j.id
JOIN bagian b ON p.id_bagian = b.id
JOIN unsur u ON p.id_unsur = u.id
WHERE j.is_pimpinan = TRUE OR j.is_pembantu_pimpinan = TRUE
ORDER BY u.urutan, j.is_pimpinan DESC, p.nama;

-- Query 4: Statistics Dashboard
SELECT 
    'Total Personil' as metric,
    COUNT(*) as value
FROM personil
UNION ALL
SELECT 
    'Unsur Pimpinan',
    COUNT(*)
FROM personil p
JOIN unsur u ON p.id_unsur = u.id
WHERE u.kode_unsur = 'UNSUR_PIMPINAN'
UNION ALL
SELECT 
    'Pembantu Pimpinan',
    COUNT(*)
FROM personil p
JOIN unsur u ON p.id_unsur = u.id
WHERE u.kode_unsur = 'UNSUR_PEMBANTU_PIMPINAN'
UNION ALL
SELECT 
    'Pelaksana Tugas Pokok',
    COUNT(*)
FROM personil p
JOIN unsur u ON p.id_unsur = u.id
WHERE u.kode_unsur = 'UNSUR_PELAKSANA_TUGAS_POKOK'
UNION ALL
SELECT 
    'Pelaksana Kewilayahan',
    COUNT(*)
FROM personil p
JOIN unsur u ON p.id_unsur = u.id
WHERE u.kode_unsur = 'UNSUR_PELAKSANA_KEWILAYAHAN';

-- Query 5: Jabatan Distribution by Unsur
SELECT 
    u.nama_unsur,
    j.tingkat_jabatan,
    COUNT(DISTINCT j.id) as jabatan_count,
    COUNT(p.id) as personil_count
FROM unsur u
LEFT JOIN jabatan j ON u.id = j.id_unsur
LEFT JOIN personil p ON j.id = p.id_jabatan
GROUP BY u.id, u.nama_unsur, j.tingkat_jabatan
ORDER BY u.urutan, j.tingkat_jabatan;

-- =====================================================
-- 8. VIEWS for Easy Access
-- =====================================================

-- View 1: Complete Personil View
CREATE OR REPLACE VIEW v_personil_complete AS
SELECT 
    p.id,
    p.nrp,
    p.nama,
    pa.nama_pangkat,
    j.nama_jabatan,
    b.nama_bagian,
    u.nama_unsur,
    p.status_ket,
    p.status_kepegawaian,
    p.tanggal_lahir,
    p.tanggal_masuk,
    j.is_pimpinan,
    j.is_pembantu_pimpinan,
    j.is_kepala_unit,
    j.tingkat_jabatan
FROM personil p
LEFT JOIN pangkat pa ON p.pangkat_id = pa.id
LEFT JOIN jabatan j ON p.id_jabatan = j.id
LEFT JOIN bagian b ON p.id_bagian = b.id
LEFT JOIN unsur u ON p.id_unsur = u.id;

-- View 2: Unsur Statistics View
CREATE OR REPLACE VIEW v_unsur_statistics AS
SELECT 
    u.id,
    u.kode_unsur,
    u.nama_unsur,
    u.deskripsi,
    u.urutan,
    COUNT(DISTINCT b.id) as bagian_count,
    COUNT(DISTINCT j.id) as jabatan_count,
    COUNT(p.id) as personil_count,
    ROUND(COUNT(p.id) * 100.0 / (SELECT COUNT(*) FROM personil), 2) as percentage
FROM unsur u
LEFT JOIN bagian b ON u.id = b.id_unsur
LEFT JOIN jabatan j ON u.id = j.id_unsur
LEFT JOIN personil p ON u.id = p.id_unsur
GROUP BY u.id, u.kode_unsur, u.nama_unsur, u.deskripsi, u.urutan
ORDER BY u.urutan;

-- =====================================================
-- END OF SCHEMA
-- =====================================================
