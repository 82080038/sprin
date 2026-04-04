# Validation Rules untuk Struktur Organisasi POLRES

## 📋 **VALIDATION RULES UNSUR**

### ✅ **Rule 1: Jumlah Unsur Standar**
- **Harus ada minimal 6 unsur** sesuai PERKAP No. 23/2010
- **Boleh tambah unsur pembinaan** sesuai PERKAP No. 2/2021
- **Urutan unsur harus konsisten**: 1-6 (standar), 7-8 (tambahan)

### ✅ **Rule 2: Nama Unsur Standar**
```sql
-- Nama unsur WAJIB sesuai regulasi:
1. UNSUR PIMPINAN
2. UNSUR PEMBANTU PIMPINAN & STAFF
3. UNSUR PELAKSANA TUGAS POKOK
4. UNSUR PELAKSANA KEWILAYAHAN
5. UNSUR PENDUKUNG
6. UNSUR LAINNYA
7. UNSUR PEMBINAAN DAN PENGAWASAN (opsional)
8. UNSUR PENGAWASAN UMUM (opsional)
```

### ✅ **Rule 3: Kode Unsur Unik**
- Kode unsur harus **UNIQUE** dan **UPPERCASE**
- Format: `UNSUR_[NAMA_DESKRIPTIF]`
- Contoh: `UNSUR_PIMPINAN`, `UNSUR_PELAKSANA_TUGAS_POKOK`

---

## 🏢 **VALIDATION RULES BAGIAN**

### ✅ **Rule 1: Relasi Bagian-Unsur**
- Setiap bagian **harus memiliki unsur** (id_unsur NOT NULL)
- Bagian harus **dikelompokkan** sesuai unsurnya
- Urutan bagian harus **sequential per unsur**

### ✅ **Rule 2: Bagian Wajib per Unsur**
```sql
-- UNSUR PIMPINAN: 1 bagian
- PIMPINAN

-- UNSUR PEMBANTU PIMPINAN: Minimal 4 bagian
- BAG OPS, BAG SDM, BAG REN, BAG LOG, (+ BAG PROVOS)

-- UNSUR PELAKSANA TUGAS POKOK: Minimal 1 bagian
- SPKT + SATUAN FUNGSI (Reskrim, Intel, Lantas, dll)

-- UNSUR PELAKSANA KEWILAYAHAN: Minimal 1 bagian
- POLSEK (sesuai kebutuhan geografis)

-- UNSUR PENDUKUNG: Minimal 5 bagian
- SIKEU, SIKUM, SIHUMAS, SIUM, SITIK, (+ SIWAS, SIDOKKES)
```

### ✅ **Rule 3: Kode Bagian Unik**
- Kode bagian harus **UNIQUE** dan **UPPERCASE**
- Format: `[TIPE]_[NAMA]`
- Contoh: `BAG_OPS`, `SAT_RESKRIM`, `SIKEU`

---

## 👮 **VALIDATION RULES JABATAN**

### ✅ **Rule 1: Relasi Jabatan-Unsur**
- Setiap jabatan **harus memiliki unsur** (id_unsur NOT NULL)
- Jabatan harus **sesuai dengan unsurnya**
- Tidak boleh ada jabatan tanpa unsur

### ✅ **Rule 2: Pangkat Minimum per Jabatan**
```sql
-- Level 1: KAPOLRES
- KAPOLRES: AKBP minimum

-- Level 2: WAKAPOLRES & KABAG
- WAKAPOLRES: KOMPOL minimum
- KABAG: AKP minimum

-- Level 3: KASAT & KASUBBAG
- KASAT: AKP minimum
- KASUBBAG: IPTU minimum

-- Level 4: KANIT & KAPOLSEK
- KANIT: IPTU minimum
- KAPOLSEK: IPDA minimum

-- Level 5: PS. Jabatan
- PS. KANIT: IPDA minimum
- PS. KASUBBAG: AIPDA minimum
```

### ✅ **Rule 3: Jenjang Karir**
- Tidak boleh ada **lompatan jenjang** yang tidak wajar
- Personil harus melalui jenjang yang sesuai
- Kenaikan pangkat harus sesuai masa kerja

### ✅ **Rule 4: Jabatan Struktural Wajib**
```sql
-- Jabatan yang WAJIB ada:
1. KAPOLRES
2. WAKAPOLRES
3. KABAG OPS
4. KABAG SDM (SUMDA)
5. KABAG REN
6. KABAG LOG
7. KASAT RESKRIM
8. KASAT INTELKAM
9. KASAT LANTAS
10. KASAT SAMAPTA
11. KASAT RESNARKOBA
12. KASAT PAMOBVIT
13. KASAT POLAIRUD
14. KASAT BINMAS
15. KASAT TAHTI
16. KAPOLSEK (per kecamatan)
```

---

## 👥 **VALIDATION RULES PERSONIL**

### ✅ **Rule 1: Data Lengkap**
- Setiap personil **harus memiliki jabatan** (id_jabatan NOT NULL)
- Setiap personil **harus memiliki pangkat** (id_pangkat NOT NULL)
- Setiap personil **harus memiliki unsur** (melalui jabatan)

### ✅ **Rule 2: Kompatibilitas Jabatan-Pangkat**
- Pangkat personil **tidak boleh lebih rendah** dari pangkat minimum jabatan
- Pangkat personil **tidak boleh terlalu tinggi** untuk jabatan junior
- Exception: ADC, Supir (bisa junior)

### ✅ **Rule 3: Unik NRP**
- NRP harus **UNIQUE** untuk setiap personil
- Format NRP harus sesuai standar POLRI
- Tidak boleh ada duplikasi NRP

---

## 🔍 **VALIDATION CHECKLIST**

### **Sebelum Implementasi:**
- [ ] Backup semua tabel terkait
- [ ] Cek jumlah personil aktif
- [ ] Validasi relasi existing
- [ ] Siapkan rollback plan

### **Saat Implementasi:**
- [ ] Update unsur terlebih dahulu
- [ ] Update bagian berdasarkan unsur baru
- [ ] Update jabatan berdasarkan unsur baru
- [ ] Validasi relasi personil

### **Setelah Implementasi:**
- [ ] Cek jumlah total per tabel
- [ ] Validasi relasi unsur-bagian-jabatan
- [ ] Cek jabatan kosong critical
- [ ] Test aplikasi SPRIN

---

## 🚨 **COMMON ERRORS & SOLUTIONS**

### **Error 1: Foreign Key Constraint**
```sql
-- Problem: Tidak bisa update karena relasi
-- Solution: Disable foreign key check sementara
SET FOREIGN_KEY_CHECKS = 0;
-- Lakukan update
SET FOREIGN_KEY_CHECKS = 1;
```

### **Error 2: Duplicate Entry**
```sql
-- Problem: Kode atau nama duplikat
-- Solution: Cek duplikasi dulu
SELECT nama_unsur, COUNT(*) as count 
FROM unsur 
GROUP BY nama_unsur 
HAVING COUNT(*) > 1;
```

### **Error 3: Data Loss**
```sql
-- Problem: Personil tanpa jabatan setelah update
-- Solution: Assign jabatan default
UPDATE personil SET id_jabatan = 
    (SELECT id FROM jabatan WHERE nama_jabatan = 'BINTARA POLSEK' LIMIT 1)
WHERE id_jabatan IS NULL;
```

---

## 📊 **VALIDATION QUERIES**

### **Query 1: Cek Struktur Lengkap**
```sql
SELECT 
    u.nama_unsur,
    COUNT(DISTINCT b.id) as total_bagian,
    COUNT(DISTINCT j.id) as total_jabatan,
    COUNT(DISTINCT p.id) as total_personil
FROM unsur u
LEFT JOIN bagian b ON u.id = b.id_unsur
LEFT JOIN jabatan j ON u.id = j.id_unsur
LEFT JOIN personil p ON j.id = p.id_jabatan
GROUP BY u.id, u.nama_unsur
ORDER BY u.urutan;
```

### **Query 2: Cek Jabatan Kosong Critical**
```sql
SELECT 
    j.nama_jabatan,
    u.nama_unsur,
    'KOSONG' as status
FROM jabatan j
JOIN unsur u ON j.id_unsur = u.id
LEFT JOIN personil p ON j.id = p.id_jabatan
WHERE p.id IS NULL
AND (j.nama_jabatan LIKE '%KABAG%' 
     OR j.nama_jabatan LIKE '%KASAT%' 
     OR j.nama_jabatan LIKE '%KAPOLSEK%');
```

### **Query 3: Cek Mismatch Pangkat-Jabatan**
```sql
SELECT 
    p.nama,
    p.nrp,
    j.nama_jabatan,
    pg.nama_pangkat,
    pg_min.nama_pangkat as pangkat_minimum,
    CASE 
        WHEN pg.urutan < pg_min.urutan THEN 'TOO LOW'
        WHEN pg.urutan > pg_min.urutan + 2 THEN 'TOO HIGH'
        ELSE 'OK'
    END as status
FROM personil p
JOIN jabatan j ON p.id_jabatan = j.id
JOIN pangkat pg ON p.id_pangkat = pg.id
LEFT JOIN pangkat pg_min ON j.id_pangkat_min = pg_min.id
WHERE j.id_pangkat_min IS NOT NULL
AND pg.urutan < pg_min.urutan;
```

---

## 🎯 **SUCCESS CRITERIA**

### **Structural Compliance:**
- ✅ Semua unsur sesuai PERKAP
- ✅ Semua bagian memiliki unsur
- ✅ Semua jabatan memiliki unsur
- ✅ Hierarki benar

### **Data Integrity:**
- ✅ Tidak ada personil tanpa jabatan
- ✅ Tidak ada jabatan tanpa unsur
- ✅ Pangkat sesuai jenjang jabatan
- ✅ NRP unik untuk semua personil

### **Functional Requirements:**
- ✅ Organizational chart bisa dibuat
- ✅ Career path tracking valid
- ✅ Position assignment valid
- ✅ Reporting accurate
