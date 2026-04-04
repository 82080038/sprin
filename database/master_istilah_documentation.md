# Master Tabel Istilah Kepegawaian POLRI

## 📋 **OVERVIEW**

Master tabel istilah kepegawaian POLRI dibuat untuk konsistensi data, validasi otomatis, dan kemudahan manajemen status penugasan dalam sistem SPRIN.

---

## 🏗️ **DATABASE SCHEMA**

### **1. master_jenis_penugasan**
```sql
CREATE TABLE master_jenis_penugasan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode VARCHAR(10) UNIQUE NOT NULL,           -- DEF, PS, PLT, PJS, PLH, PJ
    nama VARCHAR(50) NOT NULL,                  -- Definitif, PS, Plt, Pjs, Plh, Pj
    nama_lengkap VARCHAR(100) NOT NULL,          -- Nama lengkap deskriptif
    deskripsi TEXT,                             -- Penjelasan detail
    kategori ENUM('sementara', 'definitif', 'berhalangan'),
    level_minimal ENUM('eselon_2', 'eselon_3', 'eselon_4', 'eselon_5', 'semua_level'),
    durasi_maximal_bulan INT DEFAULT 12,        -- Maksimal durasi dalam bulan
    kewenangan ENUM('penuh', 'operasional', 'terbatas', 'harian'),
    persentase_maximal DECIMAL(5,2) DEFAULT 15.00, -- Maksimal persentase
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### **2. master_alasan_penugasan**
```sql
CREATE TABLE master_alasan_penugasan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode VARCHAR(20) UNIQUE NOT NULL,           -- MUTASI, DIKJUR, SAKIT, dll
    nama VARCHAR(100) NOT NULL,                 -- Nama alasan
    kategori ENUM('proses_mutasi', 'pendidikan', 'berhalangan', 'jabatan_kosong', 'tugas_khusus', 'lainnya'),
    deskripsi TEXT,                             -- Penjelasan detail
    durasi_rekomendasi_bulan INT,              -- Rekomendasi durasi
    requires_sk BOOLEAN DEFAULT FALSE,          -- Apakah perlu SK
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### **3. master_status_jabatan**
```sql
CREATE TABLE master_status_jabatan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode VARCHAR(10) UNIQUE NOT NULL,           -- KAPOLRES, KABAG, KASAT, dll
    nama VARCHAR(50) NOT NULL,                  -- Nama singkat
    nama_lengkap VARCHAR(100) NOT NULL,          -- Nama lengkap
    deskripsi TEXT,                             -- Penjelasan detail
    kategori ENUM('struktural', 'fungsional', 'pelaksana', 'pendukung'),
    level_eselon ENUM('eselon_2', 'eselon_3', 'eselon_4', 'eselon_5', 'non_eselon'),
    is_definitif BOOLEAN DEFAULT TRUE,          -- Apakah jabatan definitif
    is_managerial BOOLEAN DEFAULT FALSE,        -- Apakah jabatan manajerial
    is_supervisor BOOLEAN DEFAULT FALSE,        -- Apakah jabatan supervisi
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### **4. master_pangkat_minimum_jabatan**
```sql
CREATE TABLE master_pangkat_minimum_jabatan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_jabatan INT NOT NULL,                    -- Foreign key ke tabel jabatan
    id_pangkat_minimal INT NOT NULL,           -- Pangkat minimum
    id_pangkat_maksimal INT,                    -- Pangkat maksimal (opsional)
    is_strict BOOLEAN DEFAULT TRUE,             -- Strict validation
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 📊 **DATA MASTER**

### **Jenis Penugasan (6 Records)**
| Kode | Nama | Kategori | Level Minimal | Durasi Max | Kewenangan | % Max |
|------|------|----------|----------------|------------|------------|-------|
| DEF | Definitif | Definitif | Semua Level | - | Penuh | 100% |
| PS | PS | Sementara | Eselon 3 | 12 bulan | Operasional | 15% |
| PLT | Plt | Berhalangan | Semua Level | 24 bulan | Penuh | - |
| PJS | Pjs | Sementara | Eselon 2 | 6 bulan | Terbatas | 5% |
| PLH | Plh | Berhalangan | Semua Level | 1 bulan | Harian | - |
| PJ | Pj | Sementara | Eselon 3 | 12 bulan | Operasional | 10% |

### **Alasan Penugasan (23 Records)**
| Kategori | Kode | Nama | Durasi Rekomendasi | Requires SK |
|----------|------|------|-------------------|-------------|
| Proses Mutasi | MUTASI | Proses Mutasi | 6 bulan | ✓ |
| | SELEKSI | Proses Seleksi | 12 bulan | ✓ |
| | PROMOSI | Proses Promosi | 3 bulan | ✓ |
| Pendidikan | DIKJUR | DIKJUR | 6 bulan | ✓ |
| | SEKPA | SEKPA | 9 bulan | ✓ |
| | SESPIM | SESPIM | 6 bulan | ✓ |
| Berhalangan | SAKIT | Sakit | 3 bulan | ✗ |
| | CUTI | Cuti | 2 bulan | ✗ |
| | CUTI_BESAR | Cuti Besar | 12 bulan | ✗ |
| | TUGAS_KHUSUS | Tugas Khusus | 6 bulan | ✓ |
| | DINAS_LUAR | Dinas Luar | 1 bulan | ✗ |
| Jabatan Kosong | PENSIUN | Pensiun | 12 bulan | ✓ |
| | BERHENTIKAN | Diberhentikan | 6 bulan | ✓ |
| | MENINGGAL | Meninggal Dunia | 3 bulan | ✗ |
| | UNIT_BARU | Unit Baru | 12 bulan | ✓ |
| Tugas Khusus | OPERASI | Operasi Khusus | 3 bulan | ✓ |
| | PENGAMANAN | Pengamanan Khusus | 2 bulan | ✓ |
| | INVESTIGASI | Investigasi | 6 bulan | ✓ |
| Lainnya | REORGANISASI | Reorganisasi | 6 bulan | ✓ |
| | AUDIT | Audit Internal | 3 bulan | ✓ |
| | LAINNYA | Lainnya | 1 bulan | ✗ |

### **Status Jabatan (45 Records)**
| Kategori | Kode | Nama | Level Eselon | Definitif | Managerial | Supervisor |
|----------|------|------|--------------|-----------|-----------|------------|
| Struktural | KAPOLRES | KAPOLRES | Eselon 2 | ✓ | ✓ | ✓ |
| | WAKAPOLRES | WAKAPOLRES | Eselon 2 | ✓ | ✓ | ✓ |
| | KABAG | KABAG | Eselon 3 | ✓ | ✓ | ✓ |
| | KASAT | KASAT | Eselon 3 | ✓ | ✓ | ✓ |
| | KAPOLSEK | KAPOLSEK | Eselon 3 | ✓ | ✓ | ✓ |
| | KASUBBAG | KASUBBAG | Eselon 4 | ✓ | ✓ | ✗ |
| | KANIT | KANIT | Eselon 5 | ✓ | ✓ | ✗ |
| | KAUR | KAUR | Eselon 5 | ✓ | ✗ | ✗ |
| Fungsional | RESKRIM | RESKRIM | Non-Eselon | ✓ | ✗ | ✗ |
| | INTELKAM | INTELKAM | Non-Eselon | ✓ | ✗ | ✗ |
| | LANTAS | LANTAS | Non-Eselon | ✓ | ✗ | ✗ |
| | BINMAS | BINMAS | Non-Eselon | ✓ | ✗ | ✗ |
| | POLAIRUD | POLAIRUD | Non-Eselon | ✓ | ✗ | ✗ |
| | TAHTI | TAHTI | Non-Eselon | ✓ | ✗ | ✗ |
| Pendukung | SIKEU | SIKEU | Non-Eselon | ✓ | ✗ | ✗ |
| | SIKUM | SIKUM | Non-Eselon | ✓ | ✗ | ✗ |
| | SIHUMAS | SIHUMAS | Non-Eselon | ✓ | ✗ | ✗ |
| | SIUM | SIUM | Non-Eselon | ✓ | ✗ | ✗ |
| | SITIK | SITIK | Non-Eselon | ✓ | ✗ | ✗ |
| | SIWAS | SIWAS | Non-Eselon | ✓ | ✗ | ✗ |
| | SIDOKKES | SIDOKKES | Non-Eselon | ✓ | ✗ | ✗ |
| | SIPROPAM | SIPROPAM | Non-Eselon | ✓ | ✗ | ✗ |
| Pelaksana | BINTARA | BINTARA | Non-Eselon | ✓ | ✗ | ✗ |
| | TAMTAMA | TAMTAMA | Non-Eselon | ✓ | ✗ | ✗ |
| | PNS | PNS | Non-Eselon | ✓ | ✗ | ✗ |

---

## 🔧 **IMPLEMENTATION**

### **Step 1: Setup Master Tables**
```bash
# Execute master table setup
mysql -u root -proot bagops < database/master_istilah_kepegawaian.sql

# Expected results:
# - 4 master tables created
# - 6 jenis penugasan records
# - 23 alasan penugasan records  
# - 45 status jabatan records
```

### **Step 2: Update Existing Tables**
```bash
# Update existing tables to use master data
mysql -u root -proot bagops < database/update_tables_use_master.sql

# Expected results:
# - jabatan table updated with foreign keys
# - personil table updated with foreign keys
# - All existing data mapped to master data
```

### **Step 3: Test API Integration**
```bash
# Test API endpoints
curl -X POST "http://localhost/sprint/api/master_kepegawaian_crud.php" \
     -d "action=get_jenis_penugasan"

curl -X POST "http://localhost/sprint/api/master_kepegawaian_crud.php" \
     -d "action=get_alasan_penugasan"

curl -X POST "http://localhost/sprint/api/master_kepegawaian_crud.php" \
     -d "action=get_status_jabatan"

curl -X POST "http://localhost/sprint/api/master_kepegawaian_crud.php" \
     -d "action=get_jabatan_with_master"
```

---

## 🎯 **VALIDATION RULES**

### **Rule 1: Level Validation**
```sql
-- PS hanya untuk Eselon 3 ke atas
SELECT j.nama_jabatan, 'INVALID PS LEVEL' as error
FROM jabatan j
JOIN master_jenis_penugasan jp ON j.id_jenis_penugasan = jp.id
JOIN master_status_jabatan sj ON j.id_status_jabatan = sj.id
WHERE jp.kode = 'PS' AND sj.level_eselon NOT IN ('eselon_2', 'eselon_3');
```

### **Rule 2: Percentage Validation**
```sql
-- PS tidak boleh lebih dari 15%
SELECT 
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM jabatan), 2) as ps_percentage,
    CASE 
        WHEN COUNT(*) * 100.0 / (SELECT COUNT(*) FROM jabatan) > 15 THEN 'EXCEEDED'
        ELSE 'OK'
    END as status
FROM jabatan j
JOIN master_jenis_penugasan jp ON j.id_jenis_penugasan = jp.id
WHERE jp.kode = 'PS';
```

### **Rule 3: Duration Validation**
```sql
-- Cek durasi penugasan yang melebihi batas
SELECT j.nama_jabatan, jp.nama as jenis_penugasan, 
       jp.durasi_maximal_bulan, j.tanggal_mulai_penugasan, j.tanggal_selesai_penugasan,
       DATEDIFF(j.tanggal_selesai_penugasan, j.tanggal_mulai_penugasan) / 30 as duration_months,
       CASE 
           WHEN DATEDIFF(j.tanggal_selesai_penugasan, j.tanggal_mulai_penugasan) / 30 > jp.durasi_maximal_bulan THEN 'EXCEEDED'
           ELSE 'OK'
       END as status
FROM jabatan j
JOIN master_jenis_penugasan jp ON j.id_jenis_penugasan = jp.id
WHERE jp.durasi_maximal_bulan IS NOT NULL;
```

---

## 📊 **BENEFITS**

### **✅ Consistency**
- Single source of truth untuk istilah kepegawaian
- Tidak ada duplikasi atau inkonsistensi penamaan
- Standardized format untuk semua data

### **✅ Validation**
- Automatic validation untuk level jabatan
- Percentage limit enforcement
- Duration limit enforcement
- SK requirement checking

### **✅ Reporting**
- Easy statistics generation
- Consistent reporting format
- Better data analysis
- Trend monitoring

### **✅ Management**
- Easy updates to master data
- Centralized control
- History tracking
- Audit trail

---

## 🔄 **MAINTENANCE**

### **Regular Tasks**
1. **Monthly:** Check expired penugasan
2. **Quarterly:** Review percentage limits
3. **Semi-annually:** Update master data if needed
4. **Annually:** Full validation and cleanup

### **Data Quality**
1. **Daily:** Monitor new assignments
2. **Weekly:** Check data consistency
3. **Monthly:** Validate against regulations
4. **Quarterly:** Review and update master data

---

## 📞 **SUPPORT**

### **Technical Support**
- Database issues: Contact DB Admin
- API issues: Contact System Admin
- Data issues: Contact HR/SDM

### **Business Support**
- Policy questions: Contact Pimpinan
- Regulation updates: Contact Legal/HR
- Process changes: Change Management Team

---

## 🎯 **SUCCESS METRICS**

### **Data Quality**
- [ ] 0 jabatan tanpa master data mapping
- [ ] 0 personil tanpa master data mapping
- [ ] 100% consistent naming
- [ ] 0 validation errors

### **System Performance**
- [ ] API response time < 500ms
- [ ] Query optimization working
- [ ] No deadlocks or timeouts
- [ ] 99.9% uptime

### **Business Value**
- [ ] Reduced manual validation time by 80%
- [ ] 100% compliance with regulations
- [ ] Improved reporting accuracy
- [ ] Better decision making

---

## 🚀 **FUTURE ENHANCEMENTS**

### **Phase 2 Features**
1. **Workflow Automation**
   - Automatic expiration notifications
   - Approval workflows
   - Document management

2. **Advanced Analytics**
   - Trend analysis
   - Predictive analytics
   - Performance metrics

3. **Integration**
   - HR system integration
   - Document management system
   - Notification system

### **Phase 3 Features**
1. **Mobile Application**
   - Mobile-friendly interface
   - Push notifications
   - Offline capabilities

2. **AI/ML Integration**
   - Smart recommendations
   - Anomaly detection
   - Predictive analytics

---

## 📝 **CONCLUSION**

Master tabel istilah kepegawaian POLRI menyediakan foundation yang solid untuk:
- **Consistent data management**
- **Automated validation**
- **Better reporting**
- **Regulatory compliance**
- **Future scalability**

Dengan implementasi ini, sistem SPRIN akan memiliki data kepegawaian yang konsisten, valid, dan mudah dikelola sesuai regulasi POLRI.
