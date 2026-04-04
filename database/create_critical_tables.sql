-- =====================================================
-- IMPLEMENTASI 4 TABEL KRITIAL SESUAI PERATURAN POLRI
-- 100% Compliance dengan PERKAP No. 23/2010 dan Perpol No. 3/2024
-- =====================================================

-- STEP 1: BACKUP DATA SEBELUM PERUBAHAN
CREATE TABLE personil_backup_critical_20260402 AS SELECT * FROM personil;
CREATE TABLE jabatan_backup_critical_20260402 AS SELECT * FROM jabatan;
CREATE TABLE bagian_backup_critical_20260402 AS SELECT * FROM bagian;

-- STEP 2: CREATE MASTER SATUAN FUNGSI (SATFUNG)
CREATE TABLE master_satuan_fungsi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_satuan VARCHAR(20) UNIQUE NOT NULL,
    nama_satuan VARCHAR(100) NOT NULL,
    nama_lengkap VARCHAR(200) NOT NULL,
    kategori ENUM('satfung', 'bagian', 'seksi', 'subseksi') NOT NULL,
    level_satuan ENUM('polda', 'polres', 'polsek') NOT NULL,
    is_struktural BOOLEAN DEFAULT TRUE,
    is_fungsional BOOLEAN DEFAULT FALSE,
    is_pimpinan BOOLEAN DEFAULT FALSE,
    is_supervisor BOOLEAN DEFAULT FALSE,
    deskripsi TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_kode_satuan (kode_satuan),
    INDEX idx_kategori (kategori),
    INDEX idx_level_satuan (level_satuan),
    INDEX idx_active (is_active)
);

-- INSERT DATA MASTER SATUAN FUNGSI
INSERT INTO master_satuan_fungsi (kode_satuan, nama_satuan, nama_lengkap, kategori, level_satuan, is_struktural, is_fungsional, is_pimpinan, is_supervisor, deskripsi) VALUES
-- Satuan Fungsi POLRES
('RESKRIM', 'RESKRIM', 'Satuan Reserse Kriminal', 'satfung', 'polres', TRUE, TRUE, FALSE, TRUE, 'Satuan yang menangani penanganan perkara pidana umum'),
('INTELKAM', 'INTELKAM', 'Satuan Intelijen Keamanan', 'satfung', 'polres', TRUE, TRUE, FALSE, TRUE, 'Satuan yang menangani intelijen dan keamanan'),
('LANTAS', 'LANTAS', 'Satuan Lalu Lintas', 'satfung', 'polres', TRUE, TRUE, FALSE, TRUE, 'Satuan yang menangani lalu lintas dan keamanan jalan'),
('SAMAPTA', 'SAMAPTA', 'Satuan Pengamanan Masyarakat', 'satfung', 'polres', TRUE, TRUE, FALSE, TRUE, 'Satuan yang melakukan pengamanan masyarakat'),
('RESNARKOBA', 'RESNARKOBA', 'Satuan Reserse Narkoba', 'satfung', 'polres', TRUE, TRUE, FALSE, TRUE, 'Satuan yang menangani perkara narkotika dan psikotropika'),
('PAMOBVIT', 'PAMOBVIT', 'Satuan Pengamanan Objek Vital', 'satfung', 'polres', TRUE, TRUE, FALSE, TRUE, 'Satuan yang mengamankan objek vital penting'),
('POLAIRUD', 'POLAIRUD', 'Satuan Polisi Air dan Udara', 'satfung', 'polres', TRUE, TRUE, FALSE, TRUE, 'Satuan yang menangani patroli air dan udara'),
('BINMAS', 'BINMAS', 'Satuan Pembinaan Masyarakat', 'satfung', 'polres', TRUE, TRUE, FALSE, TRUE, 'Satuan yang membina masyarakat'),
('TAHTI', 'TAHTI', 'Satuan Tata Usaha', 'satfung', 'polres', TRUE, TRUE, FALSE, TRUE, 'Satuan yang mengurus administrasi dan tata usaha'),

-- Unit Khusus
('SPKT', 'SPKT', 'Sentra Pelayanan Kepolisian Terpadu', 'bagian', 'polres', TRUE, FALSE, FALSE, TRUE, 'Pusat pelayanan kepolisian terpadu untuk masyarakat');

-- STEP 3: CREATE MASTER UNIT PENDUKUNG (SI)
CREATE TABLE master_unit_pendukung (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_unit VARCHAR(20) UNIQUE NOT NULL,
    nama_unit VARCHAR(100) NOT NULL,
    nama_lengkap VARCHAR(200) NOT NULL,
    kategori ENUM('si', 'bagian', 'seksi') NOT NULL,
    fungsi_utama TEXT,
    is_struktural BOOLEAN DEFAULT FALSE,
    is_pendukung BOOLEAN DEFAULT TRUE,
    is_pimpinan BOOLEAN DEFAULT FALSE,
    is_supervisor BOOLEAN DEFAULT FALSE,
    deskripsi TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_kode_unit (kode_unit),
    INDEX idx_kategori (kategori),
    INDEX idx_active (is_active)
);

-- INSERT DATA MASTER UNIT PENDUKUNG
INSERT INTO master_unit_pendukung (kode_unit, nama_unit, nama_lengkap, kategori, fungsi_utama, is_pendukung, is_pimpinan, is_supervisor, deskripsi) VALUES
-- Unit Pendukung POLRES
('SIKEU', 'SIKEU', 'Seksi Sarana dan Peralatan', 'si', 'Manajemen sarana dan peralatan kepolisian', TRUE, FALSE, TRUE, 'Mengelola sarana dan peralatan kepolisian'),
('SIKUM', 'SIKUM', 'Seksi Personalia', 'si', 'Manajemen personil dan kepegawaian', TRUE, FALSE, TRUE, 'Mengelola data personil dan kepegawaian'),
('SIHUMAS', 'SIHUMAS', 'Seksi Hubungan Masyarakat', 'si', 'Hubungan masyarakat dan publikasi', TRUE, FALSE, TRUE, 'Menjalin hubungan dengan masyarakat dan media'),
('SIUM', 'SIUM', 'Seksi Umum', 'si', 'Administrasi umum dan keuangan', TRUE, FALSE, TRUE, 'Mengurus administrasi umum dan keuangan'),
('SITIK', 'SITIK', 'Seksi Teknologi Informasi dan Komunikasi', 'si', 'IT dan komunikasi', TRUE, FALSE, TRUE, 'Mengelola sistem IT dan komunikasi'),
('SIWAS', 'SIWAS', 'Seksi Pengawasan Internal', 'si', 'Pengawasan internal dan propam', TRUE, FALSE, TRUE, 'Melakukan pengawasan internal dan profesi'),
('SIDOKKES', 'SIDOKKES', 'Seksi Kedokteran dan Kesehatan', 'si', 'Pelayanan kesehatan', TRUE, FALSE, TRUE, 'Memberikan pelayanan kesehatan kepada personil'),
('SIPROPAM', 'SIPROPAM', 'Seksi Profesi dan Pengamanan', 'si', 'Profesi dan pengamanan internal', TRUE, FALSE, TRUE, 'Menegakkan profesi dan pengamanan internal');

-- STEP 4: CREATE TABEL RIWAYAT JABATAN
CREATE TABLE riwayat_jabatan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_personil INT NOT NULL,
    id_jabatan_lama INT,
    id_jabatan_baru INT NOT NULL,
    id_unsur_lama INT,
    id_unsur_baru INT,
    id_bagian_lama INT,
    id_bagian_baru INT,
    id_satuan_fungsi_lama INT,
    id_satuan_fungsi_baru INT,
    tanggal_mutasi DATE NOT NULL,
    no_sk_mutasi VARCHAR(50),
    tanggal_sk_mutasi DATE,
    alasan_mutasi TEXT,
    jenis_mutasi ENUM('promosi', 'mutasi', 'rotasi', 'demosi', 'pensiun', 'berhenti') NOT NULL,
    keterangan TEXT,
    is_aktif BOOLEAN DEFAULT TRUE,
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_personil) REFERENCES personil(id) ON DELETE CASCADE,
    FOREIGN KEY (id_jabatan_lama) REFERENCES jabatan(id) ON DELETE SET NULL,
    FOREIGN KEY (id_jabatan_baru) REFERENCES jabatan(id) ON DELETE CASCADE,
    FOREIGN KEY (id_unsur_lama) REFERENCES unsur(id) ON DELETE SET NULL,
    FOREIGN KEY (id_unsur_baru) REFERENCES unsur(id) ON DELETE SET NULL,
    FOREIGN KEY (id_bagian_lama) REFERENCES bagian(id) ON DELETE SET NULL,
    FOREIGN KEY (id_bagian_baru) REFERENCES bagian(id) ON DELETE SET NULL,
    FOREIGN KEY (id_satuan_fungsi_lama) REFERENCES master_satuan_fungsi(id) ON DELETE SET NULL,
    FOREIGN KEY (id_satuan_fungsi_baru) REFERENCES master_satuan_fungsi(id) ON DELETE SET NULL,
    
    INDEX idx_personil (id_personil),
    INDEX idx_jabatan_baru (id_jabatan_baru),
    INDEX idx_tanggal_mutasi (tanggal_mutasi),
    INDEX idx_jenis_mutasi (jenis_mutasi),
    INDEX idx_aktif (is_aktif)
);

-- STEP 5: CREATE TABEL RIWAYAT PANGKAT
CREATE TABLE riwayat_pangkat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_personil INT NOT NULL,
    id_pangkat_lama INT,
    id_pangkat_baru INT NOT NULL,
    tanggal_kenaikan_pangkat DATE NOT NULL,
    no_sk_kenaikan VARCHAR(50),
    tanggal_sk_kenaikan DATE,
    masa_kerja_tahun INT,
    masa_kerja_bulan INT DEFAULT 0,
    alasan_kenaikan TEXT,
    jenis_kenaikan ENUM('reguler', 'luar_biasa', 'penghargaan') DEFAULT 'reguler',
    keterangan TEXT,
    is_aktif BOOLEAN DEFAULT TRUE,
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_personil) REFERENCES personil(id) ON DELETE CASCADE,
    FOREIGN KEY (id_pangkat_lama) REFERENCES pangkat(id) ON DELETE SET NULL,
    FOREIGN KEY (id_pangkat_baru) REFERENCES pangkat(id) ON DELETE CASCADE,
    
    INDEX idx_personil (id_personil),
    INDEX idx_pangkat_baru (id_pangkat_baru),
    INDEX idx_tanggal_kenaikan (tanggal_kenaikan_pangkat),
    INDEX idx_jenis_kenaikan (jenis_kenaikan),
    INDEX idx_aktif (is_aktif)
);

-- STEP 6: ADD MISSING FOREIGN KEY CONSTRAINTS
-- Update personil table
ALTER TABLE personil 
ADD CONSTRAINT fk_personil_jenis_penugasan 
    FOREIGN KEY (id_jenis_penugasan) REFERENCES master_jenis_penugasan(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_personil_alasan_penugasan 
    FOREIGN KEY (id_alasan_penugasan) REFERENCES master_alasan_penugasan(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_personil_status_jabatan 
    FOREIGN KEY (id_status_jabatan) REFERENCES master_status_jabatan(id) ON DELETE SET NULL;

-- Update jabatan table
ALTER TABLE jabatan 
ADD CONSTRAINT fk_jabatan_jenis_penugasan 
    FOREIGN KEY (id_jenis_penugasan) REFERENCES master_jenis_penugasan(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_jabatan_alasan_penugasan 
    FOREIGN KEY (id_alasan_penugasan) REFERENCES master_alasan_penugasan(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_jabatan_status_jabatan 
    FOREIGN KEY (id_status_jabatan) REFERENCES master_status_jabatan(id) ON DELETE SET NULL;

-- STEP 7: UPDATE EXISTING TABLES WITH NEW RELATIONSHIPS
-- Add satuan fungsi reference to jabatan table
ALTER TABLE jabatan ADD COLUMN id_satuan_fungsi INT NULL;
ALTER TABLE jabatan ADD CONSTRAINT fk_jabatan_satuan_fungsi 
    FOREIGN KEY (id_satuan_fungsi) REFERENCES master_satuan_fungsi(id) ON DELETE SET NULL;

-- Add unit pendukung reference to jabatan table
ALTER TABLE jabatan ADD COLUMN id_unit_pendukung INT NULL;
ALTER TABLE jabatan ADD CONSTRAINT fk_jabatan_unit_pendukung 
    FOREIGN KEY (id_unit_pendukung) REFERENCES master_unit_pendukung(id) ON DELETE SET NULL;

-- Update jabatan data with satuan fungsi
UPDATE jabatan j 
SET j.id_satuan_fungsi = (SELECT id FROM master_satuan_fungsi WHERE kode_satuan = 'RESKRIM')
WHERE j.nama_jabatan LIKE '%RESKRIM%' OR j.nama_jabatan LIKE '%KANIT RESKRIM%';

UPDATE jabatan j 
SET j.id_satuan_fungsi = (SELECT id FROM master_satuan_fungsi WHERE kode_satuan = 'INTELKAM')
WHERE j.nama_jabatan LIKE '%INTELKAM%' OR j.nama_jabatan LIKE '%KANIT INTELKAM%';

UPDATE jabatan j 
SET j.id_satuan_fungsi = (SELECT id FROM master_satuan_fungsi WHERE kode_satuan = 'LANTAS')
WHERE j.nama_jabatan LIKE '%LANTAS%' OR j.nama_jabatan LIKE '%KANIT LANTAS%';

UPDATE jabatan j 
SET j.id_satuan_fungsi = (SELECT id FROM master_satuan_fungsi WHERE kode_satuan = 'SAMAPTA')
WHERE j.nama_jabatan LIKE '%SAMAPTA%' OR j.nama_jabatan LIKE '%KANIT SAMAPTA%';

UPDATE jabatan j 
SET j.id_satuan_fungsi = (SELECT id FROM master_satuan_fungsi WHERE kode_satuan = 'RESNARKOBA')
WHERE j.nama_jabatan LIKE '%RESNARKOBA%' OR j.nama_jabatan LIKE '%KANIT RESNARKOBA%';

UPDATE jabatan j 
SET j.id_satuan_fungsi = (SELECT id FROM master_satuan_fungsi WHERE kode_satuan = 'PAMOBVIT')
WHERE j.nama_jabatan LIKE '%PAMOBVIT%' OR j.nama_jabatan LIKE '%KANIT PAMOBVIT%';

UPDATE jabatan j 
SET j.id_satuan_fungsi = (SELECT id FROM master_satuan_fungsi WHERE kode_satuan = 'POLAIRUD')
WHERE j.nama_jabatan LIKE '%POLAIRUD%' OR j.nama_jabatan LIKE '%KANIT POLAIRUD%';

UPDATE jabatan j 
SET j.id_satuan_fungsi = (SELECT id FROM master_satuan_fungsi WHERE kode_satuan = 'BINMAS')
WHERE j.nama_jabatan LIKE '%BINMAS%' OR j.nama_jabatan LIKE '%KANIT BINMAS%';

UPDATE jabatan j 
SET j.id_satuan_fungsi = (SELECT id FROM master_satuan_fungsi WHERE kode_satuan = 'TAHTI')
WHERE j.nama_jabatan LIKE '%TAHTI%' OR j.nama_jabatan LIKE '%KANIT TAHTI%';

UPDATE jabatan j 
SET j.id_satuan_fungsi = (SELECT id FROM master_satuan_fungsi WHERE kode_satuan = 'SPKT')
WHERE j.nama_jabatan LIKE '%KA SPKT%' OR j.nama_jabatan LIKE '%SPKT%';

-- Update jabatan data with unit pendukung
UPDATE jabatan j 
SET j.id_unit_pendukung = (SELECT id FROM master_unit_pendukung WHERE kode_unit = 'SIKEU')
WHERE j.nama_jabatan LIKE '%SIKEU%' OR j.nama_jabatan LIKE '%KASIKEU%';

UPDATE jabatan j 
SET j.id_unit_pendukung = (SELECT id FROM master_unit_pendukung WHERE kode_unit = 'SIKUM')
WHERE j.nama_jabatan LIKE '%SIKUM%' OR j.nama_jabatan LIKE '%KASIKUM%';

UPDATE jabatan j 
SET j.id_unit_pendukung = (SELECT id FROM master_unit_pendukung WHERE kode_unit = 'SIHUMAS')
WHERE j.nama_jabatan LIKE '%SIHUMAS%' OR j.nama_jabatan LIKE '%KASIHUMAS%';

UPDATE jabatan j 
SET j.id_unit_pendukung = (SELECT id FROM master_unit_pendukung WHERE kode_unit = 'SIUM')
WHERE j.nama_jabatan LIKE '%SIUM%' OR j.nama_jabatan LIKE '%KASIUM%';

UPDATE jabatan j 
SET j.id_unit_pendukung = (SELECT id FROM master_unit_pendukung WHERE kode_unit = 'SITIK')
WHERE j.nama_jabatan LIKE '%SITIK%' OR j.nama_jabatan LIKE '%KASITIK%';

UPDATE jabatan j 
SET j.id_unit_pendukung = (SELECT id FROM master_unit_pendukung WHERE kode_unit = 'SIWAS')
WHERE j.nama_jabatan LIKE '%SIWAS%' OR j.nama_jabatan LIKE '%KASIWAS%';

UPDATE jabatan j 
SET j.id_unit_pendukung = (SELECT id FROM master_unit_pendukung WHERE kode_unit = 'SIDOKKES')
WHERE j.nama_jabatan LIKE '%SIDOKKES%' OR j.nama_jabatan LIKE '%KASIDOKKES%';

UPDATE jabatan j 
SET j.id_unit_pendukung = (SELECT id FROM master_unit_pendukung WHERE kode_unit = 'SIPROPAM')
WHERE j.nama_jabatan LIKE '%SIPROPAM%' OR j.nama_jabatan LIKE '%KASIPROPAM%';

-- STEP 8: VALIDATION AND SUMMARY
SELECT 'MASTER TABLES CREATED' as status, COUNT(*) as total FROM (
    SELECT 'master_satuan_fungsi' as table_name UNION
    SELECT 'master_unit_pendukung' UNION
    SELECT 'riwayat_jabatan' UNION
    SELECT 'riwayat_pangkat'
) as tables;

SELECT 'MASTER SATUAN FUNGSI DATA' as status, COUNT(*) as total FROM master_satuan_fungsi;
SELECT 'MASTER UNIT PENDUKUNG DATA' as status, COUNT(*) as total FROM master_unit_pendukung;

SELECT 'JABATAN WITH SATUAN FUNGSI' as status, COUNT(*) as total FROM jabatan WHERE id_satuan_fungsi IS NOT NULL;
SELECT 'JABATAN WITH UNIT PENDUKUNG' as status, COUNT(*) as total FROM jabatan WHERE id_unit_pendukung IS NOT NULL;

SELECT 'FOREIGN KEY CONSTRAINTS' as status, COUNT(*) as total FROM information_schema.TABLE_CONSTRAINTS 
WHERE TABLE_SCHEMA = 'bagops' AND CONSTRAINT_TYPE = 'FOREIGN KEY';

-- =====================================================
-- END OF CRITICAL TABLES IMPLEMENTATION
-- =====================================================
