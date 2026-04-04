-- =====================================================
-- MASTER TABEL ISTILAH KEPEGAWAIAN POLRI
-- Untuk konsistensi dan validasi data kepegawaian
-- =====================================================

-- STEP 1: BACKUP DATA EXISTING
CREATE TABLE jabatan_backup_master_istilah_20260402 AS SELECT * FROM jabatan;
CREATE TABLE personil_backup_master_istilah_20260402 AS SELECT * FROM personil;

-- STEP 2: MASTER TABEL JENIS PENUGASAN
CREATE TABLE master_jenis_penugasan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode VARCHAR(10) UNIQUE NOT NULL,
    nama VARCHAR(50) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    kategori ENUM('sementara', 'definitif', 'berhalangan') NOT NULL,
    level_minimal ENUM('eselon_2', 'eselon_3', 'eselon_4', 'eselon_5', 'semua_level') NOT NULL,
    durasi_maximal_bulan INT DEFAULT 12,
    kewenangan ENUM('penuh', 'operasional', 'terbatas', 'harian') NOT NULL,
    persentase_maximal DECIMAL(5,2) DEFAULT 15.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_kode (kode),
    INDEX idx_kategori (kategori),
    INDEX idx_active (is_active)
);

-- STEP 3: MASTER TABEL ALASAN PENUGASAN
CREATE TABLE master_alasan_penugasan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    kategori ENUM('proses_mutasi', 'pendidikan', 'berhalangan', 'jabatan_kosong', 'tugas_khusus', 'lainnya') NOT NULL,
    deskripsi TEXT,
    durasi_rekomendasi_bulan INT,
    requires_sk BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_kode (kode),
    INDEX idx_kategori (kategori),
    INDEX idx_active (is_active)
);

-- STEP 4: MASTER TABEL STATUS JABATAN
CREATE TABLE master_status_jabatan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(50) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    kategori ENUM('struktural', 'fungsional', 'pelaksana', 'pendukung') NOT NULL,
    level_eselon ENUM('eselon_2', 'eselon_3', 'eselon_4', 'eselon_5', 'non_eselon') NOT NULL,
    is_definitif BOOLEAN DEFAULT TRUE,
    is_managerial BOOLEAN DEFAULT FALSE,
    is_supervisor BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_kode (kode),
    INDEX idx_kategori (kategori),
    INDEX idx_eselon (level_eselon),
    INDEX idx_active (is_active)
);

-- STEP 5: MASTER TABEL PANGKAT MINIMUM JABATAN
CREATE TABLE master_pangkat_minimum_jabatan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_jabatan INT NOT NULL,
    id_pangkat_minimal INT NOT NULL,
    id_pangkat_maksimal INT,
    is_strict BOOLEAN DEFAULT TRUE, -- Jika TRUE, tidak boleh kurang dari minimal
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_jabatan) REFERENCES jabatan(id),
    FOREIGN KEY (id_pangkat_minimal) REFERENCES pangkat(id),
    FOREIGN KEY (id_pangkat_maksimal) REFERENCES pangkat(id),
    
    UNIQUE KEY unique_jabatan_pangkat (id_jabatan, id_pangkat_minimal),
    INDEX idx_jabatan (id_jabatan),
    INDEX idx_pangkat_minimal (id_pangkat_minimal)
);

-- STEP 6: INSERT DATA MASTER JENIS PENUGASAN
INSERT INTO master_jenis_penugasan (kode, nama, nama_lengkap, deskripsi, kategori, level_minimal, durasi_maximal_bulan, kewenangan, persentase_maximal) VALUES
('DEF', 'Definitif', 'Pejabat Definitif', 'Pejabat yang telah ditetapkan secara resmi dengan SK pengangkatan tetap', 'definitif', 'semua_level', NULL, 'penuh', 100.00),
('PS', 'PS', 'Pejabat Sementara', 'Pejabat yang mengisi jabatan kosong sementara karena pejabat definitif sedang dalam proses seleksi', 'sementara', 'eselon_3', 12, 'operasional', 15.00),
('PLT', 'Plt', 'Pelaksana Tugas', 'Pejabat definitif yang berhalangan tetap dan digantikan sementara', 'berhalangan', 'semua_level', 24, 'penuh', NULL),
('PJS', 'Pjs', 'Pejabat Sementara', 'Pejabat sementara untuk jabatan level tinggi yang kosong', 'sementara', 'eselon_2', 6, 'terbatas', 5.00),
('PLH', 'Plh', 'Pelaksana Harian', 'Pelaksana harian untuk kekosongan sangat singkat', 'berhalangan', 'semua_level', 1, 'harian', NULL),
('PJ', 'Pj', 'Penjabat', 'Penjabat untuk jabatan struktural yang kosong permanen', 'sementara', 'eselon_3', 12, 'operasional', 10.00);

-- STEP 7: INSERT DATA MASTER ALASAN PENUGASAN
INSERT INTO master_alasan_penugasan (kode, nama, kategori, deskripsi, durasi_rekomendasi_bulan, requires_sk) VALUES
-- Proses Mutasi
('MUTASI', 'Proses Mutasi', 'proses_mutasi', 'Pejabat sedang dalam proses mutasi ke jabatan lain', 6, TRUE),
('SELEKSI', 'Proses Seleksi', 'proses_mutasi', 'Jabatan sedang dalam proses seleksi pengganti', 12, TRUE),
('PROMOSI', 'Proses Promosi', 'proses_mutasi', 'Pejabat sedang dalam proses promosi jabatan', 3, TRUE),

-- Pendidikan
('DIKJUR', 'DIKJUR', 'pendidikan', 'Pendidikan Jurusan', 6, TRUE),
('SEKPA', 'SEKPA', 'pendidikan', 'Sekolah Polisi Negara', 9, TRUE),
('SESPIM', 'SESPIM', 'pendidikan', 'Sekolah Staf dan Pimpinan', 6, TRUE),
('DIKLAG', 'DIKLAG', 'pendidikan', 'Pendidikan Guru', 3, TRUE),
('DIKLUAR', 'Diklat Luar Negeri', 'pendidikan', 'Pendidikan di luar negeri', 12, TRUE),

-- Berhalangan
('SAKIT', 'Sakit', 'berhalangan', 'Pejabat sedang sakit', 3, FALSE),
('CUTI', 'Cuti', 'berhalangan', 'Pejabat sedang cuti', 2, FALSE),
('CUTI_BESAR', 'Cuti Besar', 'berhalangan', 'Pejabat sedang cuti besar', 12, FALSE),
('TUGAS_KHUSUS', 'Tugas Khusus', 'berhalangan', 'Pejabat sedang tugas khusus', 6, TRUE),
('DINAS_LUAR', 'Dinas Luar', 'berhalangan', 'Pejabat sedang dinas luar kota', 1, FALSE),

-- Jabatan Kosong
('PENSIUN', 'Pensiun', 'jabatan_kosong', 'Pejabat definitif telah pensiun', 12, TRUE),
('BERHENTIKAN', 'Diberhentikan', 'jabatan_kosong', 'Pejabat definitif diberhentikan', 6, TRUE),
('MENINGGAL', 'Meninggal Dunia', 'jabatan_kosong', 'Pejabat definitif meninggal dunia', 3, FALSE),
('UNIT_BARU', 'Unit Baru', 'jabatan_kosong', 'Unit kerja baru dibentuk', 12, TRUE),

-- Tugas Khusus
('OPERASI', 'Operasi Khusus', 'tugas_khusus', 'Tugas operasi khusus', 3, TRUE),
('PENGAMANAN', 'Pengamanan Khusus', 'tugas_khusus', 'Tugas pengamanan khusus', 2, TRUE),
('INVESTIGASI', 'Investigasi', 'tugas_khusus', 'Tugas investigasi khusus', 6, TRUE),

-- Lainnya
('REORGANISASI', 'Reorganisasi', 'lainnya', 'Proses reorganisasi struktur', 6, TRUE),
('AUDIT', 'Audit Internal', 'lainnya', 'Proses audit internal', 3, TRUE),
('LAINNYA', 'Lainnya', 'lainnya', 'Alasan lainnya', 1, FALSE);

-- STEP 8: INSERT DATA MASTER STATUS JABATAN
INSERT INTO master_status_jabatan (kode, nama, nama_lengkap, deskripsi, kategori, level_eselon, is_definitif, is_managerial, is_supervisor) VALUES
-- Level Eselon II
('KAPOLRES', 'KAPOLRES', 'Kepala Kepolisian Resort', 'Pimpinan tertinggi di tingkat Polres', 'struktural', 'eselon_2', TRUE, TRUE, TRUE),
('WAKAPOLRES', 'WAKAPOLRES', 'Wakil Kepala Kepolisian Resort', 'Wakil pimpinan tertinggi di tingkat Polres', 'struktural', 'eselon_2', TRUE, TRUE, TRUE),

-- Level Eselon III
('KABAG', 'KABAG', 'Kepala Bagian', 'Pimpinan bagian di unsur pembantu pimpinan', 'struktural', 'eselon_3', TRUE, TRUE, TRUE),
('KASAT', 'KASAT', 'Kepala Satuan', 'Pimpinan satuan fungsi', 'struktural', 'eselon_3', TRUE, TRUE, TRUE),
('KAPOLSEK', 'KAPOLSEK', 'Kepala Kepolisian Sektor', 'Pimpinan di tingkat Polsek', 'struktural', 'eselon_3', TRUE, TRUE, TRUE),

-- Level Eselon IV
('KASUBBAG', 'KASUBBAG', 'Kepala Sub Bagian', 'Pimpinan sub bagian', 'struktural', 'eselon_4', TRUE, TRUE, FALSE),
('KASUBSAT', 'KASUBSAT', 'Kepala Sub Satuan', 'Pimpinan sub satuan', 'struktural', 'eselon_4', TRUE, TRUE, FALSE),
('PS_KAPOLSEK', 'PS. KAPOLSEK', 'Pejabat Sementara Kapolsek', 'Pejabat sementara kepala polsek', 'struktural', 'eselon_4', FALSE, TRUE, TRUE),
('KANIT', 'KANIT', 'Kepala Unit', 'Pimpinan unit', 'struktural', 'eselon_5', TRUE, TRUE, FALSE),
('KAUR', 'KAUR', 'Kepala Urusan', 'Pimpinan urusan', 'struktural', 'eselon_5', TRUE, FALSE, FALSE),

-- Level Non-Eselon
('KA_SPKT', 'KA SPKT', 'Kepala Sentra Pelayanan Kepolisian Terpadu', 'Pimpinan SPKT', 'struktural', 'non_eselon', TRUE, TRUE, FALSE),
('PS_KANIT', 'PS. KANIT', 'Pejabat Sementara Kepala Unit', 'Pejabat sementara kepala unit', 'struktural', 'non_eselon', FALSE, TRUE, FALSE),

-- Fungsional
('PAMAPTA', 'PAMAPTA', 'Pengamanan Masyarakat', 'Personel pengamanan masyarakat', 'fungsional', 'non_eselon', TRUE, FALSE, FALSE),
('RESKRIM', 'RESKRIM', 'Reserse Kriminal', 'Personel reserse kriminal', 'fungsional', 'non_eselon', TRUE, FALSE, FALSE),
('INTELKAM', 'INTELKAM', 'Intelijen Keamanan', 'Personel intelijen keamanan', 'fungsional', 'non_eselon', TRUE, FALSE, FALSE),
('LANTAS', 'LANTAS', 'Lalu Lintas', 'Personel lalu lintas', 'fungsional', 'non_eselon', TRUE, FALSE, FALSE),
('BINMAS', 'BINMAS', 'Pembinaan Masyarakat', 'Personel pembinaan masyarakat', 'fungsional', 'non_eselon', TRUE, FALSE, FALSE),
('POLAIRUD', 'POLAIRUD', 'Polisi Air dan Udara', 'Personel polisi air dan udara', 'fungsional', 'non_eselon', TRUE, FALSE, FALSE),
('TAHTI', 'TAHTI', 'Tata Usaha dan Administrasi', 'Personel tata usaha', 'fungsional', 'non_eselon', TRUE, FALSE, FALSE),

-- Pendukung
('SIKEU', 'SIKEU', 'Sarana dan Peralatan', 'Staf sarana dan peralatan', 'pendukung', 'non_eselon', TRUE, FALSE, FALSE),
('SIKUM', 'SIKUM', 'Personalia', 'Staf personnelia', 'pendukung', 'non_eselon', TRUE, FALSE, FALSE),
('SIHUMAS', 'SIHUMAS', 'Hubungan Masyarakat', 'Staf hubungan masyarakat', 'pendukung', 'non_eselon', TRUE, FALSE, FALSE),
('SIUM', 'SIUM', 'Umum', 'Staf umum', 'pendukung', 'non_eselon', TRUE, FALSE, FALSE),
('SITIK', 'SITIK', 'Teknologi Informasi dan Komunikasi', 'Staf TIK', 'pendukung', 'non_eselon', TRUE, FALSE, FALSE),
('SIWAS', 'SIWAS', 'Pengawasan Internal', 'Staf pengawasan internal', 'pendukung', 'non_eselon', TRUE, FALSE, FALSE),
('SIDOKKES', 'SIDOKKES', 'Kedokteran dan Kesehatan', 'Staf kesehatan', 'pendukung', 'non_eselon', TRUE, FALSE, FALSE),
('SIPROPAM', 'SIPROPAM', 'Profesi dan Pengamanan', 'Staf profesi dan pengamanan', 'pendukung', 'non_eselon', TRUE, FALSE, FALSE),

-- Pelaksana
('BINTARA', 'BINTARA', 'Bintara', 'Personil level bintara', 'pelaksana', 'non_eselon', TRUE, FALSE, FALSE),
('TAMTAMA', 'TAMTAMA', 'Tamtama', 'Personil level tamtama', 'pelaksana', 'non_eselon', TRUE, FALSE, FALSE),
('PNS', 'PNS', 'Pegawai Negeri Sipil', 'Pegawai negeri sipil', 'pelaksana', 'non_eselon', TRUE, FALSE, FALSE);

-- STEP 9: VALIDASI DATA MASTER
SELECT 'VALIDASI MASTER JENIS PENUGASAN' as status, COUNT(*) as total FROM master_jenis_penugasan;
SELECT 'VALIDASI MASTER ALASAN PENUGASAN' as status, COUNT(*) as total FROM master_alasan_penugasan;
SELECT 'VALIDASI MASTER STATUS JABATAN' as status, COUNT(*) as total FROM master_status_jabatan;

-- STEP 10: SUMMARY REPORT
SELECT 'MASTER DATA SETUP COMPLETE' as status,
       (SELECT COUNT(*) FROM master_jenis_penugasan) as jenis_penugasan,
       (SELECT COUNT(*) FROM master_alasan_penugasan) as alasan_penugasan,
       (SELECT COUNT(*) FROM master_status_jabatan) as status_jabatan;

-- =====================================================
-- END OF MASTER TABEL ISTILAH KEPEGAWAIAN SETUP
-- =====================================================
