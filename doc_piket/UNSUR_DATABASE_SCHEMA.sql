-- =====================================================
-- DATABASE MASTER UNSUR POLRES SAMOSIR
-- Created: 29 Maret 2026
-- Purpose: Tabel master unsur organisasi POLRI
-- =====================================================

-- 1. TABEL UNSUR (Master Unsur Organisasi)
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

-- Insert data unsur
INSERT INTO unsur (kode_unsur, nama_unsur, deskripsi, tingkat, urutan) VALUES
('UNSUR_PIMPINAN', 'UNSUR PIMPINAN', 'Kapolres dan Wakapolres sesuai PERKAP No. 23 Tahun 2010', 'POLRES', 1),
('UNSUR_PEMBANTU_PIMPINAN', 'UNSUR PEMBANTU PIMPINAN', 'Kepala Bagian (KABAG), Kepala Satuan (KASAT), Kepala Polsek (KAPOLSEK)', 'POLRES', 2),
('UNSUR_PELAKSANA_TUGAS_POKOK', 'UNSUR PELAKSANA TUGAS POKOK', 'Satuan Tugas Pokok di tingkat POLRES', 'POLRES', 3),
('UNSUR_PELAKSANA_KEWILAYAHAN', 'UNSUR PELAKSANA KEWILAYAHAN', 'Kepolisian Sektor (POLSEK) jajaran POLRES', 'POLRES', 4),
('UNSUR_PENDUKUNG', 'UNSUR PENDUKUNG', 'Unit pendukung operasional dan administrasi', 'POLRES', 5),
('UNSUR_LAINNYA', 'UNSUR LAINNYA', 'Unit khusus dan penugasan khusus', 'POLRES', 6);

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

-- Insert data bagian
INSERT INTO bagian (kode_bagian, nama_bagian, id_unsur, deskripsi) VALUES
-- UNSUR PIMPINAN
('PIMPINAN', 'PIMPINAN', 1, 'Unit Pimpinan POLRES'),

-- UNSUR PEMBANTU PIMPINAN
('BAG_OPS', 'BAG OPS', 2, 'Bagian Operasional'),
('BAG_REN', 'BAG REN', 2, 'Bagian Perencanaan'),
('BAG_SDM', 'BAG SDM', 2, 'Bagian Sumber Daya Manusia'),
('BAG_LOG', 'BAG LOG', 2, 'Bagian Logistik'),

-- UNSUR PELAKSANA TUGAS POKOK
('SAT_INTELKAM', 'SAT INTELKAM', 3, 'Satuan Intelijen dan Keamanan'),
('SAT_RESKRIM', 'SAT RESKRIM', 3, 'Satuan Reserse Kriminal'),
('SAT_RESNARKOBA', 'SAT RESNARKOBA', 3, 'Satuan Reserse Narkoba'),
('SAT_LANTAS', 'SAT LANTAS', 3, 'Satuan Lalu Lintas'),
('SAT_SAMAPTA', 'SAT SAMAPTA', 3, 'Satuan Pengamanan'),
('SAT_PAMOBVIT', 'SAT PAMOBVIT', 3, 'Satuan Pengamanan Objek Vital'),
('SAT_POLAIRUD', 'SAT_POLAIRUD', 3, 'Satuan Polisi Air dan Udara'),
('SAT_TAHTI', 'SAT TAHTI', 3, 'Satuan Tata Usaha'),
('SAT_BINMAS', 'SAT BINMAS', 3, 'Satuan Pembinaan Masyarakat'),

-- UNSUR PELAKSANA KEWILAYAHAN
('POLSEK_HARIAN_BOHO', 'POLSEK HARIAN BOHO', 4, 'Polsek Harian Boho'),
('POLSEK_PALIPI', 'POLSEK PALIPI', 4, 'Polsek Palipi'),
('POLSEK_SIMANINDO', 'POLSEK SIMANINDO', 4, 'Polsek Simanindo'),
('POLSEK_ONAN_RUNGGU', 'POLSEK_ONAN RUNGGU', 4, 'Polsek Onan Runggu'),
('POLSEK_PANGURURAN', 'POLSEK_PANGURURAN', 4, 'Polsek Pangururan'),

-- UNSUR PENDUKUNG
('SPKT', 'SPKT', 5, 'Sentra Pelayanan Kepolisian Terpadu'),
('SIUM', 'SIUM', 5, 'Satuan Intelijen Umum'),
('SIKEU', 'SIKEU', 5, 'Satuan Keuangan'),
('SIDOKKES', 'SIDOKKES', 5, 'Satuan Dokter Kesehatan'),
('SIWAS', 'SIWAS', 5, 'Satuan Pengawasan Internal'),
('SITIK', 'SITIK', 5, 'Satuan Identifikasi dan Teknologi Forensik'),
('SIKUM', 'SIKUM', 5, 'Satuan Komunikasi'),
('SIPROPAM', 'SIPROPAM', 5, 'Satuan Profesi dan Pengamanan'),
('SIHUMAS', 'SIHUMAS', 5, 'Satuan Humas'),

-- UNSUR LAINNYA
('BKO', 'BKO', 6, 'Bantuan Kendali Operasional');

-- =====================================================
-- 3. UPDATE TABEL PERSONIL (Add foreign keys)
-- =====================================================
-- Add foreign key columns to personil table
ALTER TABLE personil 
ADD COLUMN IF NOT EXISTS id_unsur INT,
ADD COLUMN IF NOT EXISTS id_bagian INT,
ADD FOREIGN KEY (id_unsur) REFERENCES unsur(id) ON DELETE SET NULL,
ADD FOREIGN KEY (id_bagian) REFERENCES bagian(id) ON DELETE SET NULL;

-- =====================================================
-- 4. SAMPLE QUERIES
-- =====================================================

-- Query 1: Get all personil by unsur
SELECT 
    u.nama_unsur,
    COUNT(p.id) as total_personil,
    GROUP_CONCAT(DISTINCT b.nama_bagian) as bagian_list
FROM unsur u
LEFT JOIN bagian b ON u.id = b.id_unsur
LEFT JOIN personil p ON b.id = p.id_bagian
GROUP BY u.id, u.nama_unsur
ORDER BY u.urutan;

-- Query 2: Get personil by specific unsur
SELECT 
    p.nama,
    p.nrp,
    p.jabatan,
    b.nama_bagian,
    u.nama_unsur
FROM personil p
JOIN bagian b ON p.id_bagian = b.id
JOIN unsur u ON b.id_unsur = u.id
WHERE u.kode_unsur = 'UNSUR_PIMPINAN'
ORDER BY p.nama;

-- Query 3: Get pimpinan statistics
SELECT 
    u.nama_unsur,
    COUNT(p.id) as count,
    (COUNT(p.id) * 100.0 / (SELECT COUNT(*) FROM personil WHERE id_unsur IS NOT NULL)) as percentage
FROM unsur u
LEFT JOIN bagian b ON u.id = b.id_unsur
LEFT JOIN personil p ON b.id = p.id_bagian
GROUP BY u.id, u.nama_unsur
ORDER BY count DESC;

-- Query 4: Get bagian distribution by unsur
SELECT 
    u.nama_unsur,
    b.nama_bagian,
    COUNT(p.id) as personil_count
FROM unsur u
JOIN bagian b ON u.id = b.id_unsur
LEFT JOIN personil p ON b.id = p.id_bagian
GROUP BY u.id, b.id, u.nama_unsur, b.nama_bagian
ORDER BY u.urutan, personil_count DESC;

-- =====================================================
-- 5. INDEXES for performance
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_personil_unsur ON personil(id_unsur);
CREATE INDEX IF NOT EXISTS idx_personil_bagian ON personil(id_bagian);
CREATE INDEX IF NOT EXISTS idx_bagian_unsur ON bagian(id_unsur);
CREATE INDEX IF NOT EXISTS idx_unsur_kode ON unsur(kode_unsur);
CREATE INDEX IF NOT EXISTS idx_bagian_kode ON bagian(kode_bagian);

-- =====================================================
-- END OF SCRIPT
-- =====================================================
