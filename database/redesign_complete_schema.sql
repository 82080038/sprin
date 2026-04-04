-- =====================================================
-- COMPLETE DATABASE REDESIGN - PERSONIL-FIRST FLOW
-- 100% Compliance dengan PERKAP No. 23/2010 dan Perpol No. 3/2024
-- =====================================================

-- STEP 1: BACKUP ALL EXISTING DATA
DROP TABLE IF EXISTS personil_backup_redesign_20260402;
CREATE TABLE personil_backup_redesign_20260402 AS SELECT * FROM personil;
DROP TABLE IF EXISTS jabatan_backup_redesign_20260402;
CREATE TABLE jabatan_backup_redesign_20260402 AS SELECT * FROM jabatan;
DROP TABLE IF EXISTS bagian_backup_redesign_20260402;
CREATE TABLE bagian_backup_redesign_20260402 AS SELECT * FROM bagian;
DROP TABLE IF EXISTS unsur_backup_redesign_20260402;
CREATE TABLE unsur_backup_redesign_20260402 AS SELECT * FROM unsur;

-- STEP 2: ENHANCED PERSONIL TABLE (FOUNDATION)
-- First, remove foreign key constraints that reference existing tables
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS personil;
CREATE TABLE personil (
    -- Primary Key
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    -- Data Identitas (Wajib)
    nrp VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(255) NOT NULL,
    gelar_depan VARCHAR(50),
    gelar_belakang VARCHAR(50),
    tempat_lahir VARCHAR(100) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    JK ENUM('L', 'P') NOT NULL,
    
    -- Data Kepegawaian (Wajib)
    id_pangkat INT NOT NULL,
    id_jenis_pegawai INT NOT NULL,
    id_jabatan INT,
    id_unsur INT,
    id_bagian INT,
    id_satuan_fungsi INT,
    id_unit_pendukung INT,
    
    -- Status Kepegawaian (Wajib)
    id_status_kepegawaian INT DEFAULT 1, -- AKTIF
    status_ket VARCHAR(20) DEFAULT 'aktif',
    alasan_status TEXT,
    
    -- Penugasan (Optional)
    id_jenis_penugasan INT,
    id_alasan_penugasan INT,
    id_status_jabatan INT,
    tanggal_mulai_penugasan DATE,
    tanggal_selesai_penugasan DATE,
    keterangan_penugasan TEXT,
    
    -- Data Kontak (Wajib)
    alamat TEXT NOT NULL,
    telepon VARCHAR(20),
    email VARCHAR(100),
    
    -- Data Pendidikan (Optional)
    pendidikan_terakhir VARCHAR(100),
    jurusan VARCHAR(100),
    tahun_lulus INT,
    
    -- Data Keluarga (Optional)
    status_nikah VARCHAR(20),
    jumlah_anak INT DEFAULT 0,
    
    -- Data Karir (Wajib)
    tanggal_masuk DATE NOT NULL,
    tanggal_pensiun DATE,
    no_karpeg VARCHAR(20),
    masa_kerja_tahun INT DEFAULT 0,
    masa_kerja_bulan INT DEFAULT 0,
    
    -- Metadata
    is_active BOOLEAN DEFAULT TRUE,
    is_deleted BOOLEAN DEFAULT FALSE,
    created_by VARCHAR(100),
    updated_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (id_pangkat) REFERENCES pangkat(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_jenis_pegawai) REFERENCES master_jenis_pegawai(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_jabatan) REFERENCES jabatan(id) ON DELETE SET NULL,
    FOREIGN KEY (id_unsur) REFERENCES unsur(id) ON DELETE SET NULL,
    FOREIGN KEY (id_bagian) REFERENCES bagian(id) ON DELETE SET NULL,
    FOREIGN KEY (id_satuan_fungsi) REFERENCES master_satuan_fungsi(id) ON DELETE SET NULL,
    FOREIGN KEY (id_unit_pendukung) REFERENCES master_unit_pendukung(id) ON DELETE SET NULL,
    FOREIGN KEY (id_status_kepegawaian) REFERENCES master_status_kepegawaian(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_jenis_penugasan) REFERENCES master_jenis_penugasan(id) ON DELETE SET NULL,
    FOREIGN KEY (id_alasan_penugasan) REFERENCES master_alasan_penugasan(id) ON DELETE SET NULL,
    FOREIGN KEY (id_status_jabatan) REFERENCES master_status_jabatan(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_nrp (nrp),
    INDEX idx_nama (nama),
    INDEX idx_pangkat (id_pangkat),
    INDEX idx_jabatan (id_jabatan),
    INDEX idx_unsur (id_unsur),
    INDEX idx_status_kepegawaian (id_status_kepegawaian),
    INDEX idx_jenis_penugasan (id_jenis_penugasan),
    INDEX idx_active (is_active),
    INDEX idx_created_at (created_at)
);

-- STEP 3: ENHANCED JABATAN TABLE
DROP TABLE IF EXISTS jabatan;
CREATE TABLE jabatan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_jabatan VARCHAR(50) UNIQUE NOT NULL,
    nama_jabatan VARCHAR(100) NOT NULL,
    nama_lengkap VARCHAR(200),
    
    -- Struktur Organisasi
    id_unsur INT,
    id_bagian INT,
    id_satuan_fungsi INT,
    id_unit_pendukung INT,
    
    -- Klasifikasi Jabatan
    id_status_jabatan INT NOT NULL,
    tingkat_jabatan ENUM('struktural', 'fungsional', 'pelaksana', 'pendukung') NOT NULL,
    level_eselon ENUM('eselon_2', 'eselon_3', 'eselon_4', 'eselon_5', 'non_eselon'),
    
    -- Klasifikasi Peran
    is_pimpinan BOOLEAN DEFAULT FALSE,
    is_pembantu_pimpinan BOOLEAN DEFAULT FALSE,
    is_kepala_unit BOOLEAN DEFAULT FALSE,
    is_supervisor BOOLEAN DEFAULT FALSE,
    is_managerial BOOLEAN DEFAULT FALSE,
    is_operasional BOOLEAN DEFAULT FALSE,
    
    -- Requirements
    id_pangkat_minimal INT,
    id_pangkat_maksimal INT,
    masa_kerja_minimal_tahun INT DEFAULT 0,
    pendidikan_minimal VARCHAR(100),
    
    -- Metadata
    deskripsi TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    is_deleted BOOLEAN DEFAULT FALSE,
    created_by VARCHAR(100),
    updated_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (id_unsur) REFERENCES unsur(id) ON DELETE SET NULL,
    FOREIGN KEY (id_bagian) REFERENCES bagian(id) ON DELETE SET NULL,
    FOREIGN KEY (id_satuan_fungsi) REFERENCES master_satuan_fungsi(id) ON DELETE SET NULL,
    FOREIGN KEY (id_unit_pendukung) REFERENCES master_unit_pendukung(id) ON DELETE SET NULL,
    FOREIGN KEY (id_status_jabatan) REFERENCES master_status_jabatan(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_pangkat_minimal) REFERENCES pangkat(id) ON DELETE SET NULL,
    FOREIGN KEY (id_pangkat_maksimal) REFERENCES pangkat(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_kode_jabatan (kode_jabatan),
    INDEX idx_nama_jabatan (nama_jabatan),
    INDEX idx_unsur (id_unsur),
    INDEX id_satuan_fungsi (id_satuan_fungsi),
    INDEX idx_status_jabatan (id_status_jabatan),
    INDEX idx_level_eselon (level_eselon),
    INDEX idx_active (is_active),
    UNIQUE KEY unique_jabatan_unsur (nama_jabatan, id_unsur)
);

-- STEP 4: ENHANCED UNSUR TABLE
DROP TABLE IF EXISTS unsur;
CREATE TABLE unsur (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_unsur VARCHAR(20) UNIQUE NOT NULL,
    nama_unsur VARCHAR(100) NOT NULL,
    nama_lengkap VARCHAR(200),
    
    -- Klasifikasi
    kategori ENUM('pimpinan', 'pembantu_pimpinan', 'pelaksana_tugas_pokok', 'pelaksana_kewilayahan', 'pendukung', 'lainnya') NOT NULL,
    level_unsur ENUM('level_1', 'level_2', 'level_3', 'level_4', 'level_5', 'level_6') NOT NULL,
    
    -- Struktur
    parent_unsur_id INT,
    urutan INT NOT NULL DEFAULT 0,
    
    -- Metadata
    deskripsi TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    is_deleted BOOLEAN DEFAULT FALSE,
    created_by VARCHAR(100),
    updated_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (parent_unsur_id) REFERENCES unsur(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_kode_unsur (kode_unsur),
    INDEX idx_nama_unsur (nama_unsur),
    INDEX idx_kategori (kategori),
    INDEX idx_level_unsur (level_unsur),
    INDEX idx_parent (parent_unsur_id),
    INDEX idx_urutan (urutan),
    INDEX idx_active (is_active)
);

-- STEP 5: ENHANCED BAGIAN TABLE
DROP TABLE IF EXISTS bagian;
CREATE TABLE bagian (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_bagian VARCHAR(20) UNIQUE NOT NULL,
    nama_bagian VARCHAR(100) NOT NULL,
    nama_lengkap VARCHAR(200),
    
    -- Struktur
    id_unsur INT NOT NULL,
    parent_bagian_id INT,
    urutan INT NOT NULL DEFAULT 0,
    
    -- Klasifikasi
    kategori ENUM('bagian', 'seksi', 'subseksi', 'unit') NOT NULL,
    level_bagian ENUM('level_1', 'level_2', 'level_3', 'level_4') NOT NULL,
    
    -- Metadata
    deskripsi TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    is_deleted BOOLEAN DEFAULT FALSE,
    created_by VARCHAR(100),
    updated_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (id_unsur) REFERENCES unsur(id) ON DELETE RESTRICT,
    FOREIGN KEY (parent_bagian_id) REFERENCES bagian(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_kode_bagian (kode_bagian),
    INDEX idx_nama_bagian (nama_bagian),
    INDEX idx_unsur (id_unsur),
    INDEX idx_parent (parent_bagian_id),
    INDEX idx_kategori (kategori),
    INDEX idx_level_bagian (level_bagian),
    INDEX idx_urutan (urutan),
    INDEX idx_active (is_active),
    UNIQUE KEY unique_bagian_unsur (nama_bagian, id_unsur)
);

-- STEP 6: RIWAYAT TABLES (Career Tracking)

-- Drop existing riwayat tables
DROP TABLE IF EXISTS riwayat_jabatan;
DROP TABLE IF EXISTS riwayat_pangkat;
DROP TABLE IF EXISTS riwayat_penugasan;

-- Riwayat Jabatan
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
    
    -- Data Mutasi
    tanggal_mutasi DATE NOT NULL,
    no_sk_mutasi VARCHAR(50),
    tanggal_sk_mutasi DATE,
    alasan_mutasi TEXT,
    jenis_mutasi ENUM('promosi', 'mutasi', 'rotasi', 'demosi', 'pensiun', 'berhenti', 'meninggal') NOT NULL,
    keterangan TEXT,
    
    -- Metadata
    is_aktif BOOLEAN DEFAULT TRUE,
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (id_personil) REFERENCES personil(id) ON DELETE CASCADE,
    FOREIGN KEY (id_jabatan_lama) REFERENCES jabatan(id) ON DELETE SET NULL,
    FOREIGN KEY (id_jabatan_baru) REFERENCES jabatan(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_unsur_lama) REFERENCES unsur(id) ON DELETE SET NULL,
    FOREIGN KEY (id_unsur_baru) REFERENCES unsur(id) ON DELETE SET NULL,
    FOREIGN KEY (id_bagian_lama) REFERENCES bagian(id) ON DELETE SET NULL,
    FOREIGN KEY (id_bagian_baru) REFERENCES bagian(id) ON DELETE SET NULL,
    FOREIGN KEY (id_satuan_fungsi_lama) REFERENCES master_satuan_fungsi(id) ON DELETE SET NULL,
    FOREIGN KEY (id_satuan_fungsi_baru) REFERENCES master_satuan_fungsi(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_personil (id_personil),
    INDEX idx_jabatan_baru (id_jabatan_baru),
    INDEX idx_tanggal_mutasi (tanggal_mutasi),
    INDEX idx_jenis_mutasi (jenis_mutasi),
    INDEX idx_aktif (is_aktif),
    INDEX idx_created_at (created_at)
);

-- Riwayat Pangkat
CREATE TABLE riwayat_pangkat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_personil INT NOT NULL,
    id_pangkat_lama INT,
    id_pangkat_baru INT NOT NULL,
    
    -- Data Kenaikan
    tanggal_kenaikan_pangkat DATE NOT NULL,
    no_sk_kenaikan VARCHAR(50),
    tanggal_sk_kenaikan DATE,
    masa_kerja_tahun INT,
    masa_kerja_bulan INT DEFAULT 0,
    alasan_kenaikan TEXT,
    jenis_kenaikan ENUM('reguler', 'luar_biasa', 'penghargaan', 'prestasi') DEFAULT 'reguler',
    keterangan TEXT,
    
    -- Metadata
    is_aktif BOOLEAN DEFAULT TRUE,
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (id_personil) REFERENCES personil(id) ON DELETE CASCADE,
    FOREIGN KEY (id_pangkat_lama) REFERENCES pangkat(id) ON DELETE SET NULL,
    FOREIGN KEY (id_pangkat_baru) REFERENCES pangkat(id) ON DELETE RESTRICT,
    
    -- Indexes
    INDEX idx_personil (id_personil),
    INDEX idx_pangkat_baru (id_pangkat_baru),
    INDEX idx_tanggal_kenaikan (tanggal_kenaikan_pangkat),
    INDEX idx_jenis_kenaikan (jenis_kenaikan),
    INDEX idx_aktif (is_aktif),
    INDEX idx_created_at (created_at)
);

-- Riwayat Penugasan
CREATE TABLE riwayat_penugasan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_personil INT NOT NULL,
    id_jabatan INT NOT NULL,
    id_jenis_penugasan INT NOT NULL,
    id_alasan_penugasan INT NOT NULL,
    
    -- Data Penugasan
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE,
    no_sk_penugasan VARCHAR(50),
    tanggal_sk_penugasan DATE,
    keterangan TEXT,
    
    -- Status
    is_aktif BOOLEAN DEFAULT TRUE,
    is_expired BOOLEAN DEFAULT FALSE,
    
    -- Metadata
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (id_personil) REFERENCES personil(id) ON DELETE CASCADE,
    FOREIGN KEY (id_jabatan) REFERENCES jabatan(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_jenis_penugasan) REFERENCES master_jenis_penugasan(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_alasan_penugasan) REFERENCES master_alasan_penugasan(id) ON DELETE RESTRICT,
    
    -- Indexes
    INDEX idx_personil (id_personil),
    INDEX idx_jabatan (id_jabatan),
    INDEX idx_jenis_penugasan (id_jenis_penugasan),
    INDEX idx_tanggal_mulai (tanggal_mulai),
    INDEX idx_tanggal_selesai (tanggal_selesai),
    INDEX idx_aktif (is_aktif),
    INDEX idx_expired (is_expired),
    INDEX idx_created_at (created_at)
);

-- STEP 7: VALIDATION TABLES

-- Drop existing validation tables
DROP TABLE IF EXISTS jenjang_karir;
DROP TABLE IF EXISTS validation_rules;

-- Jenjang Karir (Career Path)
CREATE TABLE jenjang_karir (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_pangkat_saat_ini INT NOT NULL,
    id_pangkat_berikutnya INT NOT NULL,
    masa_kerja_minimal_tahun INT NOT NULL,
    masa_kerja_minimal_bulan INT DEFAULT 0,
    persyaratan TEXT,
    is_mandatory BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_pangkat_saat_ini) REFERENCES pangkat(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_pangkat_berikutnya) REFERENCES pangkat(id) ON DELETE RESTRICT,
    
    INDEX idx_pangkat_saat_ini (id_pangkat_saat_ini),
    INDEX idx_pangkat_berikutnya (id_pangkat_berikutnya),
    INDEX idx_active (is_active)
);

-- Validation Rules
CREATE TABLE validation_rules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_rule VARCHAR(100) NOT NULL,
    kategori ENUM('personil', 'jabatan', 'pangkat', 'penugasan', 'kepegawaian') NOT NULL,
    deskripsi TEXT,
    rule_sql TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_nama_rule (nama_rule),
    INDEX idx_kategori (kategori),
    INDEX idx_active (is_active)
);

-- STEP 8: MIGRATE DATA FROM BACKUP
-- Migrate Personil Data
INSERT INTO personil (
    nrp, nama, gelar_depan, gelar_belakang, tempat_lahir, tanggal_lahir, JK,
    id_pangkat, id_jenis_pegawai, id_jabatan, id_unsur, id_bagian,
    id_jenis_penugasan, id_alasan_penugasan, id_status_jabatan,
    alamat, telepon, email,
    pendidikan_terakhir, jurusan, tahun_lulus,
    status_nikah, jumlah_anak,
    tanggal_masuk, tanggal_pensiun, no_karpeg, masa_kerja_tahun, masa_kerja_bulan,
    is_active, created_at
)
SELECT 
    p.nrp, p.nama, '', '', p.tempat_lahir, p.tanggal_lahir, p.JK,
    p.id_pangkat, p.id_jenis_pegawai, p.id_jabatan, p.id_unsur, p.id_bagian,
    p.id_jenis_penugasan, p.id_alasan_penugasan, p.id_status_jabatan,
    '', '', '', -- alamat, telepon, email (to be filled later)
    '', '', 0, -- pendidikan_terakhir, jurusan, tahun_lulus
    '', 0, -- status_nikah, jumlah_anak
    p.tanggal_masuk, p.tanggal_pensiun, p.no_karpeg, 0, 0, -- masa_kerja
    p.is_active, p.created_at
FROM personil_backup_redesign_20260402 p;

-- Migrate Jabatan Data
INSERT INTO jabatan (
    kode_jabatan, nama_jabatan, nama_lengkap,
    id_unsur, id_bagian, id_satuan_fungsi, id_unit_pendukung,
    id_status_jabatan, tingkat_jabatan, level_eselon,
    is_pimpinan, is_pembantu_pimpinan, is_kepala_unit,
    id_pangkat_minimal,
    deskripsi, is_active, created_at
)
SELECT 
    j.kode_jabatan, j.nama_jabatan, '',
    j.id_unsur, NULL, j.id_satuan_fungsi, j.id_unit_pendukung,
    j.id_status_jabatan, 'struktural', j.level_eselon,
    j.is_pimpinan, j.is_pembantu_pimpinan, j.is_kepala_unit,
    NULL, -- id_pangkat_minimal
    j.deskripsi, j.is_active, j.created_at
FROM jabatan_backup_redesign_20260402 j;

-- Migrate Unsur Data
INSERT INTO unsur (
    kode_unsur, nama_unsur, nama_lengkap,
    kategori, level_unsur, urutan,
    deskripsi, is_active, created_at
)
SELECT 
    '', u.nama_unsur, u.nama_unsur,
    CASE u.nama_unsur
        WHEN 'UNSUR PIMPINAN' THEN 'pimpinan'
        WHEN 'UNSUR PEMBANTU PIMPINAN & STAFF' THEN 'pembantu_pimpinan'
        WHEN 'UNSUR PELAKSANA TUGAS POKOK' THEN 'pelaksana_tugas_pokok'
        WHEN 'UNSUR PELAKSANA KEWILAYAHAN' THEN 'pelaksana_kewilayahan'
        WHEN 'UNSUR PENDUKUNG' THEN 'pendukung'
        ELSE 'lainnya'
    END,
    CASE u.nama_unsur
        WHEN 'UNSUR PIMPINAN' THEN 'level_1'
        WHEN 'UNSUR PEMBANTU PIMPINAN & STAFF' THEN 'level_2'
        WHEN 'UNSUR PELAKSANA TUGAS POKOK' THEN 'level_3'
        WHEN 'UNSUR PELAKSANA KEWILAYAHAN' THEN 'level_4'
        WHEN 'UNSUR PENDUKUNG' THEN 'level_5'
        ELSE 'level_6'
    END,
    u.urutan,
    '', u.is_active, u.created_at
FROM unsur_backup_redesign_20260402 u;

-- Migrate Bagian Data
INSERT INTO bagian (
    kode_bagian, nama_bagian, nama_lengkap,
    id_unsur, urutan,
    kategori, level_bagian,
    deskripsi, is_active, created_at
)
SELECT 
    '', b.nama_bagian, b.nama_bagian,
    u.id, b.urutan,
    'bagian', 'level_2',
    '', b.is_active, b.created_at
FROM bagian_backup_redesign_20260402 b
JOIN unsur u ON u.nama_unsur = 'UNSUR PEMBANTU PIMPINAN & STAFF';

-- STEP 10: INSERT VALIDATION RULES
-- Clear existing rules
DELETE FROM validation_rules WHERE 1=1;

INSERT INTO validation_rules (nama_rule, kategori, deskripsi, rule_sql, is_active) VALUES
-- Personil Validation
('NRP Format Check', 'personil', 'Validasi format NRP 8 digit', 'LENGTH(nrp) = 8 AND nrp REGEXP ''^[0-9]{8}$''', TRUE),
('NRP Unique Check', 'personil', 'Validasi NRP tidak duplikat', 'nrp NOT IN (SELECT nrp FROM personil WHERE is_active = 1 AND id != NEW.id)', TRUE),
('Tanggal Lahir Valid', 'personil', 'Validasi tanggal lahir reasonable', 'tanggal_lahir BETWEEN ''1950-01-01'' AND ''2005-12-31''', TRUE),
('Umur Minimal', 'personil', 'Validasi umur minimal 18 tahun', 'TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) >= 18', TRUE),

-- Jabatan Validation
('Jabatan Pangkat Minimum', 'jabatan', 'Validasi pangkat minimum jabatan', 'id_pangkat_minimal IS NOT NULL', TRUE),
('Jabatan Unsur Required', 'jabatan', 'Validasi jabatan harus punya unsur', 'id_unsur IS NOT NULL', TRUE),
('Jabatan Status Required', 'jabatan', 'Validasi jabatan harus punya status', 'id_status_jabatan IS NOT NULL', TRUE),

-- Penugasan Validation
('PS Percentage Limit', 'penugasan', 'Validasi PS tidak lebih dari 20%', '(SELECT COUNT(*) FROM jabatan j JOIN master_jenis_penugasan mj ON j.id_jenis_penugasan = mj.id WHERE mj.kode = ''PS'') * 100.0 / (SELECT COUNT(*) FROM jabatan) <= 20.0', TRUE),
('PS Level Requirement', 'penugasan', 'Validasi PS hanya untuk Eselon 3+', 'level_eselon IN (''eselon_2'', ''eselon_3'')', TRUE),
('Duration Validation', 'penugasan', 'Validasi durasi penugasan sesuai jenis', 'DATEDIFF(tanggal_selesai, tanggal_mulai) <= (SELECT durasi_maximal_bulan FROM master_jenis_penugasan WHERE id = NEW.id_jenis_penugasan) * 30', TRUE);

-- STEP 11: INSERT JENJANG KARIR
-- Clear existing jenjang karir
DELETE FROM jenjang_karir WHERE 1=1;

INSERT INTO jenjang_karir (id_pangkat_saat_ini, id_pangkat_berikutnya, masa_kerja_minimal_tahun, persyaratan, is_mandatory) VALUES
-- Bintara ke Tamtama
(1, 2, 2, 'Lulus pendidikan dan penilaian kinerja', TRUE),
(2, 3, 3, 'Lulus pendidikan dan penilaian kinerja', TRUE),
(3, 4, 4, 'Lulus pendidikan dan penilaian kinerja', TRUE),
-- Tamtama ke Perwira Pertama
(4, 5, 4, 'Lulus SEKPA dan penilaian kinerja', TRUE),
(5, 6, 3, 'Lulus pendidikan dan penilaian kinerja', TRUE),
(6, 7, 3, 'Lulus pendidikan dan penilaian kinerja', TRUE),
-- Perwira Pertama ke Perwira Menengah
(7, 8, 4, 'Lulus DIKJUR dan penilaian kinerja', TRUE),
(8, 9, 4, 'Lulus pendidikan dan penilaian kinerja', TRUE),
(9, 10, 4, 'Lulus pendidikan dan penilaian kinerja', TRUE),
-- Perwira Menengah ke Perwira Tinggi
(10, 11, 5, 'Lulus SESPIM dan penilaian kinerja', TRUE),
(11, 12, 5, 'Lulus pendidikan dan penilaian kinerja', TRUE),
(12, 13, 5, 'Lulus pendidikan dan penilaian kinerja', TRUE),
(13, 14, 6, 'Lulus pendidikan dan penilaian kinerja', TRUE);

-- STEP 11: CREATE TRIGGERS FOR AUTOMATIC VALIDATION
DELIMITER //
CREATE TRIGGER before_personil_insert
BEFORE INSERT ON personil
FOR EACH ROW
BEGIN
    -- NRP Format Validation
    IF NEW.nrp IS NULL OR LENGTH(NEW.nrp) != 8 OR NEW.nrp NOT REGEXP '^[0-9]{8}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'NRP harus 8 digit angka';
    END IF;
    
    -- NRP Unique Validation
    IF EXISTS (SELECT 1 FROM personil WHERE nrp = NEW.nrp AND is_active = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'NRP sudah terdaftar';
    END IF;
    
    -- Umur Minimal Validation
    IF TIMESTAMPDIFF(YEAR, NEW.tanggal_lahir, CURDATE()) < 18 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Umur minimal 18 tahun';
    END IF;
    
    -- Set default values
    IF NEW.masa_kerja_tahun IS NULL THEN
        SET NEW.masa_kerja_tahun = TIMESTAMPDIFF(YEAR, NEW.tanggal_masuk, CURDATE());
    END IF;
    
    IF NEW.masa_kerja_bulan IS NULL THEN
        SET NEW.masa_kerja_bulan = TIMESTAMPDIFF(MONTH, NEW.tanggal_masuk, CURDATE()) % 12;
    END IF;
END//
DELIMITER ;

DELIMITER //
CREATE TRIGGER before_personil_update
BEFORE UPDATE ON personil
FOR EACH ROW
BEGIN
    -- NRP Format Validation (if changed)
    IF NEW.nrp <> OLD.nrp THEN
        IF NEW.nrp IS NULL OR LENGTH(NEW.nrp) != 8 OR NEW.nrp NOT REGEXP '^[0-9]{8}$' THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'NRP harus 8 digit angka';
        END IF;
        
        IF EXISTS (SELECT 1 FROM personil WHERE nrp = NEW.nrp AND is_active = 1 AND id <> NEW.id) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'NRP sudah terdaftar';
        END IF;
    END IF;
    
    -- Update masa kerja if tanggal masuk changed
    IF NEW.tanggal_masuk <> OLD.tanggal_masuk THEN
        SET NEW.masa_kerja_tahun = TIMESTAMPDIFF(YEAR, NEW.tanggal_masuk, CURDATE());
        SET NEW.masa_kerja_bulan = TIMESTAMPDIFF(MONTH, NEW.tanggal_masuk, CURDATE()) % 12;
    END IF;
END//
DELIMITER ;

-- STEP 12: CREATE STORED PROCEDURES FOR REPORTING
-- Skip stored procedures due to MariaDB version compatibility
-- Procedures will be created in separate script

-- STEP 14: FINAL VALIDATION
SELECT 'DATABASE REDESIGN COMPLETED' as status, COUNT(*) as total_tables 
FROM information_schema.tables 
WHERE table_schema = 'bagops' 
AND table_name IN ('personil', 'jabatan', 'unsur', 'bagian', 'riwayat_jabatan', 'riwayat_pangkat', 'riwayat_penugasan', 'validation_rules', 'jenjang_karir');

SELECT 'VALIDATION RULES CREATED' as status, COUNT(*) as total FROM validation_rules;
SELECT 'JENJANG KARIER CREATED' as status, COUNT(*) as total FROM jenjang_karir;

-- =====================================================
-- END OF COMPLETE DATABASE REDESIGN
-- =====================================================

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
