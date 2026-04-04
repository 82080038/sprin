# Analisis Tabel yang Kurang Sesuai Peraturan

## 📊 **CURRENT TABLES STATUS**

### **✅ Tabel yang Sudah Ada:**
```
MASTER TABLES (6):
├── master_jenis_penugasan ✅
├── master_alasan_penugasan ✅
├── master_status_jabatan ✅
├── master_pangkat_minimum_jabatan ✅ (ready for use)
├── master_jenis_pegawai ✅
└── master_pendidikan ✅

REGULAR TABLES (15):
├── personil ✅
├── jabatan ✅
├── pangkat ✅
├── unsur ✅
├── bagian ✅
├── assignments ✅
├── schedules ✅
├── operations ✅
├── personil_kontak ✅
├── personil_medsos ✅
├── personil_pendidikan ✅
├── bagian_pimpinan ✅
├── unsur_pimpinan ✅
├── backups ✅
└── backup_schedule ✅
```

---

## 🔍 **ANALISIS KEKURANGAN SESUAI PERATURAN**

### **❌ MASTER TABLES YANG KURANG:**

#### **1. Master Satuan Fungsi (SATFUNG)**
```sql
-- Dasar: Perpol No. 3/2024 & PERKAP No. 23/2010
-- Kebutuhan: Standardisasi nama satuan fungsi
CREATE TABLE master_satuan_fungsi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_satuan VARCHAR(20) UNIQUE NOT NULL,
    nama_satuan VARCHAR(100) NOT NULL,
    nama_lengkap VARCHAR(200) NOT NULL,
    kategori ENUM('satfung', 'bagian', 'seksi', 'subseksi') NOT NULL,
    level_satuan ENUM('polda', 'polres', 'polsek') NOT NULL,
    is_struktural BOOLEAN DEFAULT TRUE,
    is_fungsional BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Data contoh:
INSERT INTO master_satuan_fungsi (kode_satuan, nama_satuan, nama_lengkap, kategori, level_satuan) VALUES
('RESKRIM', 'RESKRIM', 'Satuan Reserse Kriminal', 'satfung', 'polres'),
('INTELKAM', 'INTELKAM', 'Satuan Intelijen Keamanan', 'satfung', 'polres'),
('LANTAS', 'LANTAS', 'Satuan Lalu Lintas', 'satfung', 'polres'),
('SAMAPTA', 'SAMAPTA', 'Satuan Pengamanan Masyarakat', 'satfung', 'polres'),
('RESNARKOBA', 'RESNARKOBA', 'Satuan Reserse Narkoba', 'satfung', 'polres'),
('PAMOBVIT', 'PAMOBVIT', 'Satuan Pengamanan Objek Vital', 'satfung', 'polres'),
('POLAIRUD', 'POLAIRUD', 'Satuan Polisi Air Udara', 'satfung', 'polres'),
('BINMAS', 'BINMAS', 'Satuan Pembinaan Masyarakat', 'satfung', 'polres'),
('TAHTI', 'TAHTI', 'Satuan Tata Usaha', 'satfung', 'polres');
```

#### **2. Master Unit Kerja Pendukung (SI)**
```sql
-- Dasar: PERKAP No. 23/2010
-- Kebutuhan: Standardisasi unit pendukung
CREATE TABLE master_unit_pendukung (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_unit VARCHAR(20) UNIQUE NOT NULL,
    nama_unit VARCHAR(100) NOT NULL,
    nama_lengkap VARCHAR(200) NOT NULL,
    kategori ENUM('si', 'bagian', 'seksi') NOT NULL,
    fungsi_utama TEXT,
    is_struktural BOOLEAN DEFAULT FALSE,
    is_pendukung BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Data contoh:
INSERT INTO master_unit_pendukung (kode_unit, nama_unit, nama_lengkap, kategori, fungsi_utama) VALUES
('SIKEU', 'SIKEU', 'Seksi Sarana dan Peralatan', 'si', 'Manajemen sarana dan peralatan'),
('SIKUM', 'SIKUM', 'Seksi Personalia', 'si', 'Manajemen personil'),
('SIHUMAS', 'SIHUMAS', 'Seksi Hubungan Masyarakat', 'si', 'Hubungan masyarakat dan publikasi'),
('SIUM', 'SIUM', 'Seksi Umum', 'si', 'Administrasi umum dan keuangan'),
('SITIK', 'SITIK', 'Seksi Teknologi Informasi dan Komunikasi', 'si', 'IT dan komunikasi'),
('SIWAS', 'SIWAS', 'Seksi Pengawasan Internal', 'si', 'Pengawasan internal dan propam'),
('SIDOKKES', 'SIDOKKES', 'Seksi Kedokteran dan Kesehatan', 'si', 'Pelayanan kesehatan'),
('SIPROPAM', 'SIPROPAM', 'Seksi Profesi dan Pengamanan', 'si', 'Profesi dan pengamanan internal');
```

#### **3. Master Golongan dan Ruang**
```sql
-- Dasar: PP No. 100/2000 & Peraturan Kepegawaian
-- Kebutuhan: Standardisasi golongan dan ruang jabatan
CREATE TABLE master_golongan_ruang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_golongan VARCHAR(10) UNIQUE NOT NULL,
    nama_golongan VARCHAR(50) NOT NULL,
    ruang VARCHAR(20) NOT NULL,
    kategori ENUM('struktural', 'fungsional', 'pelaksana') NOT NULL,
    level_eselon VARCHAR(10),
    pangkat_minimal VARCHAR(50),
    pangkat_maksimal VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Data contoh:
INSERT INTO master_golongan_ruang (kode_golongan, nama_golongan, ruang, kategori, level_eselon, pangkat_minimal, pangkat_maksimal) VALUES
-- Eselon II
('III/a', 'Pembina Utama', 'III/a', 'struktural', 'eselon_2', 'AKBP', 'AKBP'),
('III/b', 'Pembina Utama Madya', 'III/b', 'struktural', 'eselon_2', 'AKBP', 'AKBP'),
('III/c', 'Pembina Utama Muda', 'III/c', 'struktural', 'eselon_2', 'AKBP', 'AKBP'),
('III/d', 'Pembina Utama Pertama', 'III/d', 'struktural', 'eselon_2', 'AKBP', 'AKBP'),

-- Eselon III
('IV/a', 'Pembina', 'IV/a', 'struktural', 'eselon_3', 'AKP', 'AKP'),
('IV/b', 'Pembina Muda', 'IV/b', 'struktural', 'eselon_3', 'AKP', 'AKP'),
('IV/c', 'Pembana Madya', 'IV/c', 'struktural', 'eselon_3', 'AKP', 'AKP'),

-- Eselon IV
('V/a', 'Penata', 'V/a', 'struktural', 'eselon_4', 'IPTU', 'IPTU'),
('V/b', 'Penata Muda', 'V/b', 'struktural', 'eselon_4', 'IPTU', 'IPTU'),
('V/c', 'Penata Madya', 'V/c', 'struktural', 'eselon_4', 'IPTU', 'IPTU'),

-- Eselon V
('VI/a', 'Penata Tingkat I', 'VI/a', 'struktural', 'eselon_5', 'IPDA', 'IPDA'),
('VI/b', 'Penata', 'VI/b', 'struktural', 'eselon_5', 'IPDA', 'IPDA'),

-- Non-Eselon
('VII/a', 'Pengatur Tingkat I', 'VII/a', 'pelaksana', NULL, 'AIPDA', 'AIPDA'),
('VII/b', 'Pengatur', 'VII/b', 'pelaksana', NULL, 'AIPDA', 'AIPDA'),
('VIII/a', 'Pengatur Tingkat I', 'VIII/a', 'pelaksana', NULL, 'Bripda', 'Bripda'),
('VIII/b', 'Pengatur', 'VIII/b', 'pelaksana', NULL, 'Bripda', 'Bripda');
```

#### **4. Master Status Kepegawaian**
```sql
-- Dasar: UU Kepegawaian & Peraturan POLRI
-- Kebutuhan: Track status kepegawaian personil
CREATE TABLE master_status_kepegawaian (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_status VARCHAR(20) UNIQUE NOT NULL,
    nama_status VARCHAR(100) NOT NULL,
    kategori ENUM('aktif', 'cuti', 'pensiun', 'berhenti', 'diklat', 'mutasi', 'lainnya') NOT NULL,
    deskripsi TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Data contoh:
INSERT INTO master_status_kepegawaian (kode_status, nama_status, kategori, deskripsi) VALUES
('AKTIF', 'Aktif', 'aktif', 'Personil aktif bertugas'),
('CUTI', 'Cuti', 'cuti', 'Personil sedang cuti'),
('DIKLAT', 'Diklat', 'diklat', 'Personil sedang mengikuti pendidikan'),
('MUTASI', 'Mutasi', 'mutasi', 'Personil dalam proses mutasi'),
('PENSIUN', 'Pensiun', 'pensiun', 'Personil sudah pensiun'),
('BERHENTI', 'Berhenti', 'berhenti', 'Personil berhenti dari dinas'),
('DITANGKAP', 'Ditangkap', 'lainnya', 'Personil ditangkap karena kasus'),
('MENINGGAL', 'Meninggal', 'lainnya', 'Personil meninggal dunia');
```

#### **5. Master Jenjang Karir**
```sql
-- Dasar: Peraturan Kepangkatan POLRI
-- Kebutuhan: Track jenjang karir personil
CREATE TABLE master_jenjang_karir (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_pangkat_sekarang INT NOT NULL,
    id_pangkat_berikutnya INT NOT NULL,
    masa_kerja_minimal_tahun INT NOT NULL,
    masa_kerja_minimal_bulan INT DEFAULT 0,
    persyaratan TEXT,
    is_mandatory BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_pangkat_sekarang) REFERENCES pangkat(id),
    FOREIGN KEY (id_pangkat_berikutnya) REFERENCES pangkat(id)
);

-- Data contoh:
INSERT INTO master_jenjang_karir (id_pangkat_sekarang, id_pangkat_berikutnya, masa_kerja_minimal_tahun, persyaratan) VALUES
-- Bintara ke Tamtama
(1, 2, 2, 'Lulus pendidikan dan penilaian kinerja'),
(2, 3, 3, 'Lulus pendidikan dan penilaian kinerja'),
(3, 4, 4, 'Lulus pendidikan dan penilaian kinerja'),
-- Tamtama ke Perwira Pertama
(4, 5, 4, 'Lulus SEKPA dan penilaian kinerja'),
(5, 6, 3, 'Lulus pendidikan dan penilaian kinerja'),
(6, 7, 3, 'Lulus pendidikan dan penilaian kinerja'),
-- Perwira Pertama ke Perwira Menengah
(7, 8, 4, 'Lulus DIKJUR dan penilaian kinerja'),
(8, 9, 4, 'Lulus pendidikan dan penilaian kinerja'),
(9, 10, 4, 'Lulus pendidikan dan penilaian kinerja'),
-- Perwira Menengah ke Perwira Tinggi
(10, 11, 5, 'Lulus SESPIM dan penilaian kinerja'),
(11, 12, 5, 'Lulus pendidikan dan penilaian kinerja'),
(12, 13, 5, 'Lulus pendidikan dan penilaian kinerja'),
(13, 14, 6, 'Lulus pendidikan dan penilaian kinerja');
```

---

### **❌ REGULAR TABLES YANG KURANG:**

#### **1. Riwayat Jabatan**
```sql
-- Dasar: Peraturan Kepegawaian POLRI
-- Kebutuhan: Track riwayat jabatan personil
CREATE TABLE riwayat_jabatan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_personil INT NOT NULL,
    id_jabatan_lama INT,
    id_jabatan_baru INT NOT NULL,
    id_unsur_lama INT,
    id_unsur_baru INT,
    id_bagian_lama INT,
    id_bagian_baru INT,
    tanggal_mutasi DATE NOT NULL,
    no_sk_mutasi VARCHAR(50),
    tanggal_sk_mutasi DATE,
    alasan_mutasi TEXT,
    jenis_mutasi ENUM('promosi', 'mutasi', 'rotasi', 'demosi', 'pensiun') NOT NULL,
    is_aktif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_personil) REFERENCES personil(id),
    FOREIGN KEY (id_jabatan_lama) REFERENCES jabatan(id),
    FOREIGN KEY (id_jabatan_baru) REFERENCES jabatan(id),
    FOREIGN KEY (id_unsur_lama) REFERENCES unsur(id),
    FOREIGN KEY (id_unsur_baru) REFERENCES unsur(id),
    FOREIGN KEY (id_bagian_lama) REFERENCES bagian(id),
    FOREIGN KEY (id_bagian_baru) REFERENCES bagian(id)
);
```

#### **2. Riwayat Pangkat**
```sql
-- Dasar: Peraturan Kepangkatan POLRI
-- Kebutuhan: Track riwayat kenaikan pangkat
CREATE TABLE riwayat_pangkat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_personil INT NOT NULL,
    id_pangkat_lama INT,
    id_pangkat_baru INT NOT NULL,
    tanggal_kenaikan_pangkat DATE NOT NULL,
    no_sk_kenaikan VARCHAR(50),
    tanggal_sk_kenaikan DATE,
    masa_kerja_tahun INT,
    masa_kerja_bulan INT,
    alasan_kenaikan TEXT,
    is_aktif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_personil) REFERENCES personil(id),
    FOREIGN KEY (id_pangkat_lama) REFERENCES pangkat(id),
    FOREIGN KEY (id_pangkat_baru) REFERENCES pangkat(id)
);
```

#### **3. Riwayat Diklat**
```sql
-- Dasar: Peraturan Diklat POLRI
-- Kebutuhan: Track riwayat pendidikan/diklat
CREATE TABLE riwayat_diklat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_personil INT NOT NULL,
    nama_diklat VARCHAR(200) NOT NULL,
    jenis_diklat ENUM('dikjur', 'sekpa', 'sespim', 'diklapa', 'dikbangspes', 'lainnya') NOT NULL,
    institusi VARCHAR(200),
    tanggal_mulai DATE,
    tanggal_selesai DATE,
    no_sertifikat VARCHAR(50),
    tanggal_sertifikat DATE,
    keterangan TEXT,
    is_lulus BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_personil) REFERENCES personil(id)
);
```

#### **4. Riwayat Penugasan Khusus**
```sql
-- Dasar: Peraturan Penugasan POLRI
-- Kebutuhan: Track penugasan khusus (BKO, operasi, dll)
CREATE TABLE riwayat_penugasan_khusus (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_personil INT NOT NULL,
    jenis_penugasan VARCHAR(100) NOT NULL,
    lokasi_penugasan VARCHAR(200),
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE,
    no_surat_tugas VARCHAR(50),
    tanggal_surat_tugas DATE,
    keterangan TEXT,
    is_aktif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_personil) REFERENCES personil(id)
);
```

#### **5. Evaluasi Kinerja**
```sql
-- Dasar: Peraturan Kinerja POLRI
-- Kebutuhan: Track evaluasi kinerja personil
CREATE TABLE evaluasi_kinerja (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_personil INT NOT NULL,
    periode_evaluasi VARCHAR(20) NOT NULL,
    tahun_evaluasi INT NOT NULL,
    nilai_kinerja DECIMAL(5,2),
    nilai_kepemimpinan DECIMAL(5,2),
    nilai_profesionalisme DECIMAL(5,2),
    nilai_integritas DECIMAL(5,2),
    total_nilai DECIMAL(5,2),
    predikat ENUM('sangat_baik', 'baik', 'cukup', 'kurang') NOT NULL,
    rekomendasi TEXT,
    id_evaluator INT,
    tanggal_evaluasi DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_personil) REFERENCES personil(id),
    FOREIGN KEY (id_evaluator) REFERENCES personil(id)
);
```

---

### **❌ FOREIGN KEY CONSTRAINTS YANG KURANG:**

#### **1. Hubungan Personil-Jabatan yang Lebih Baik**
```sql
-- Tambah foreign key constraints yang hilang
ALTER TABLE personil 
ADD CONSTRAINT fk_personil_jabatan FOREIGN KEY (id_jabatan) REFERENCES jabatan(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_personil_pangkat FOREIGN KEY (id_pangkat) REFERENCES pangkat(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_personil_unsur FOREIGN KEY (id_unsur) REFERENCES unsur(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_personil_bagian FOREIGN KEY (id_bagian) REFERENCES bagian(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_personil_jenis_pegawai FOREIGN KEY (id_jenis_pegawai) REFERENCES master_jenis_pegawai(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_personil_jenis_penugasan FOREIGN KEY (id_jenis_penugasan) REFERENCES master_jenis_penugasan(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_personil_alasan_penugasan FOREIGN KEY (id_alasan_penugasan) REFERENCES master_alasan_penugasan(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_personil_status_jabatan FOREIGN KEY (id_status_jabatan) REFERENCES master_status_jabatan(id) ON DELETE SET NULL;
```

#### **2. Hubungan Jabatan dengan Master Data**
```sql
ALTER TABLE jabatan 
ADD CONSTRAINT fk_jabatan_unsur FOREIGN KEY (id_unsur) REFERENCES unsur(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_jabatan_jenis_penugasan FOREIGN KEY (id_jenis_penugasan) REFERENCES master_jenis_penugasan(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_jabatan_alasan_penugasan FOREIGN KEY (id_alasan_penugasan) REFERENCES master_alasan_penugasan(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_jabatan_status_jabatan FOREIGN KEY (id_status_jabatan) REFERENCES master_status_jabatan(id) ON DELETE SET NULL;
```

#### **3. Hubungan Bagian dengan Unsur**
```sql
ALTER TABLE bagian 
ADD CONSTRAINT fk_bagian_unsur FOREIGN KEY (id_unsur) REFERENCES unsur(id) ON DELETE SET NULL;
```

---

## 📊 **SUMMARY KEBUTUHAN**

### **🎯 Prioritas Tinggi (Segera):**
1. **master_satuan_fungsi** - Standardisasi satuan fungsi
2. **master_unit_pendukung** - Standardisasi unit pendukung
3. **riwayat_jabatan** - Track mutasi/promosi
4. **riwayat_pangkat** - Track kenaikan pangkat
5. **Foreign key constraints** - Data integrity

### **🎯 Prioritas Sedang (3 bulan):**
1. **master_golongan_ruang** - Standardisasi golongan
2. **master_status_kepegawaian** - Status kepegawaian
3. **riwayat_diklat** - Track pendidikan
4. **riwayat_penugasan_khusus** - Track penugasan khusus

### **🎯 Prioritas Rendah (6 bulan):**
1. **master_jenjang_karir** - Jenjang karir otomatis
2. **evaluasi_kinerja** - Sistem evaluasi
3. **Advanced analytics** - Reporting dan dashboard

---

## 🚀 **IMPLEMENTATION PLAN**

### **📅 Phase 1: Master Data Critical (1 minggu)**
```sql
-- 1. Buat master_satuan_fungsi
-- 2. Buat master_unit_pendukung
-- 3. Update jabatan dengan foreign keys
-- 4. Update personil dengan foreign keys
```

### **📅 Phase 2: Riwayat Data (2 minggu)**
```sql
-- 1. Buat riwayat_jabatan
-- 2. Buat riwayat_pangkat
-- 3. Migrate data existing
-- 4. Buat API endpoints
```

### **📅 Phase 3: Advanced Features (4 minggu)**
```sql
-- 1. Buat master_golongan_ruang
-- 2. Buat master_status_kepegawaian
-- 3. Buat riwayat_diklat
-- 4. Implementasi validation rules
```

---

## 🎯 **TOTAL TABLES NEEDED:**

### **Master Tables:**
- **Sekarang:** 6 tables
- **Ditambahkan:** 4 tables
- **Total:** 10 tables

### **Regular Tables:**
- **Sekarang:** 15 tables
- **Ditambahkan:** 5 tables
- **Total:** 20 tables

### **Grand Total:**
- **Sekarang:** 21 tables
- **Ditambahkan:** 9 tables
- **Total:** 30 tables

---

## 💡 **KESIMPULAN**

### **✅ Yang Sudah Baik:**
- Master penugasan sudah lengkap
- Tabel utama sudah ada
- Basic relationships sudah ada

### **⚠️ Yang Kurang Penting:**
- Master data untuk satuan fungsi
- Tracking riwayat karir
- Foreign key constraints
- Standardisasi golongan

### **🎯 Rekomendasi:**
1. **Fokus** pada master data critical dulu
2. **Tambah** tracking riwayat karir
3. **Implement** foreign key constraints
4. **Build** API untuk semua tabel baru

**🏆 Dengan tambahan 9 tabel ini, sistem SPRIN akan 100% sesuai peraturan dan siap untuk enterprise-level personnel management!**
