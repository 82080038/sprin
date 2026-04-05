# Guidelines Status Penugasan POLRI

## 📋 **DEFINISI DAN PENGGUNAAN STATUS PENUGASAN**

### **🏛️ 1. DEFINITIF**
```sql
-- Kriteria:
- Pejabat yang telah ditetapkan secara resmi
- Memiliki SK pengangkatan tetap
- Kewenangan penuh sesuai jabatan

-- Contoh:
- KAPOLRES SAMOSIR
- WAKAPOLRES
- KABAG OPS
- KASAT RESKRIM

-- Field values:
status_penugasan = 'definitif'
alasan_penugasan = NULL
tanggal_mulai_penugasan = NULL
tanggal_selesai_penugasan = NULL
```

### **🔄 2. PS (Pejabat Sementara)**
```sql
-- Kriteria:
- Jabatan kosong karena pejabat definitif:
  * Sedang dalam proses mutasi
  * Mengikuti pendidikan
  * Dalam proses pemeriksaan
  * Meninggal dunia (sebelum pengganti tetap)
- Masa penugasan: 6 bulan - 1 tahun
- Kewenangan terbatas operasional

-- Contoh yang BENAR:
- PS. KASUBBAGBEKPAL (Kasubbag Bekpal sedang DIKJUR)
- PS. KASIHUMAS (Kasihumas sedang dalam proses mutasi)
- PS. KASAT POLAIRUD (Kasat Polairud sedang SEKPA)

-- Contoh yang SALAH:
- PS. KANIT RESKRIM (Seharusnya langsung KANIT RESKRIM)
- PS. BINTARA POLSEK (Level terlalu rendah untuk PS)

-- Field values:
status_penugasan = 'ps'
alasan_penugasan = 'Jabatan kosong dalam proses seleksi'
tanggal_mulai_penugasan = 'YYYY-MM-DD'
tanggal_selesai_penugasan = 'YYYY-MM-DD' (perkiraan)
```

### **🔧 3. Plt (Pelaksana Tugas)**
```sql
-- Kriteria:
- Pejabat definitif yang berhalangan tetap:
  * Sakit (lebih dari 30 hari)
  * Cuti besar/panjang
  * Tugas belajar (DIKJUR, SEKPA, SESPIM)
  * Tugas khusus (Diklat LN, dll)
- Pejabat pengganti dari level yang sama atau satu level di bawah
- Kewenangan penuh sementara

-- Contoh yang BENAR:
- Plt. KASUBBAGBEKPAL (Kasubbag Bekpal sedang sakit)
- Plt. KASIHUMAS (Kasihumas sedang DIKJUR)
- Plt. KASAT RESKRIM (Kasat Reskrim sedang SESPIM)

-- Field values:
status_penugasan = 'plt'
alasan_penugasan = 'Pejabat definitif berhalangan tetap'
tanggal_mulai_penugasan = 'YYYY-MM-DD'
tanggal_selesai_penugasan = 'YYYY-MM-DD' (perkiraan)
```

### **🎯 4. Pjs (Pejabat Sementara)**
```sql
-- Kriteria:
- Level eselon II dan III
- Jabatan kosong karena:
  * Pejabat definitif pensiun
  * Pejabat definitif mutasi
  * Pejabat definitif diberhentikan
- Masa penugasan: 3-6 bulan
- Kewenangan terbatas untuk kebijakan strategis

-- Contoh yang BENAR:
- Pjs. KAPOLRES (Kapolres definitif pensiun)
- Pjs. DIRRESKRIM (Direktur Reskrim mutasi)
- Pjs. KABID PROPAM (Kabid Propam cuti panjang)

-- Field values:
status_penugasan = 'pjs'
alasan_penugasan = 'Jabatan kosong level tinggi'
tanggal_mulai_penugasan = 'YYYY-MM-DD'
tanggal_selesai_penugasan = 'YYYY-MM-DD' (perkiraan)
```

### **📋 5. Plh (Pelaksana Harian)**
```sql
-- Kriteria:
- Kekosongan sangat singkat (1-7 hari)
- Pejabat definitif sedang:
  * Dinas luar kota
  * Cuti tahunan
  * Tugas mendadak
- Tidak memiliki kewenangan keputusan penting

-- Contoh yang BENAR:
- Plh. KASAT LANTAS (Kasat Lantas dinas luar 3 hari)
- Plh. KABAG OPS (Kabag Ops cuti tahunan 1 minggu)

-- Field values:
status_penugasan = 'plh'
alasan_penugasan = 'Pelaksana harian sementara'
tanggal_mulai_penugasan = 'YYYY-MM-DD'
tanggal_selesai_penugasan = 'YYYY-MM-DD'
```

### **🏛️ 6. Pj (Penjabat)**
```sql
-- Kriteria:
- Jabatan struktural yang kosong permanen
- Unit kerja baru dibentuk
- Pejabat definitif akan diangkat melalui proses seleksi
- Masa penugasan: 6 bulan - 1 tahun
- Kewenangan penuh operasional

-- Contoh yang BENAR:
- Pj. KAPOLRES (Polres baru dibentuk)
- Pj. KASAT LANTAS (Satuan Lantas baru dibentuk)

-- Field values:
status_penugasan = 'pj'
alasan_penugasan = 'Jabatan struktural kosong'
tanggal_mulai_penugasan = 'YYYY-MM-DD'
tanggal_selesai_penugasan = 'YYYY-MM-DD' (perkiraan)
```

---

## 📊 **GUIDELINES PENGGUNAAN**

### **🎯 Level Jabatan yang Boleh Menggunakan Status Penugasan**

#### **✅ BOLEH PS/Pjs:**
- Level Eselon III ke atas
- KABAG, KASAT, KAPOLSEK
- Jabatan struktural penting

#### **❌ TIDAK BOLEH PS/Pjs:**
- Level KANIT ke bawah
- Jabatan fungsional
- Bintara dan Tamtama

#### **✅ BOLEH Plt:**
- Semua level jabatan definitif
- Asalkan pejabat definitif berhalangan

#### **✅ BOLEH Plh:**
- Semua level jabatan
- Untuk kekosongan sangat singkat

### **📋 Prosedur Penugasan**

#### **Step 1: Identifikasi Kebutuhan**
```sql
-- Cek jabatan kosong
SELECT j.nama_jabatan, 'KOSONG' as status
FROM jabatan j
LEFT JOIN personil p ON j.id = p.id_jabatan
WHERE p.id IS NULL
AND j.nama_jabatan LIKE '%KABAG%' OR j.nama_jabatan LIKE '%KASAT%';
```

#### **Step 2: Tentukan Jenis Penugasan**
```sql
-- Berdasarkan alasan:
- Proses mutasi → PS
- Sakit/cuti → Plt
- Pensiun → Pjs
- Baru dibentuk → Pj
- Dinas luar → Plh
```

#### **Step 3: Update Status**
```sql
-- Update jabatan
UPDATE jabatan SET
    status_penugasan = 'ps',
    alasan_penugasan = 'Proses mutasi pejabat definitif',
    tanggal_mulai_penugasan = CURDATE(),
    tanggal_selesai_penugasan = DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
WHERE id = [jabatan_id];
```

#### **Step 4: Assign Personil**
```sql
-- Update personil
UPDATE personil SET
    id_jabatan = [jabatan_id],
    status_penugasan = 'ps',
    alasan_penugasan = 'Melaksanakan tugas sebagai PS',
    tanggal_mulai_penugasan = CURDATE(),
    tanggal_selesai_penugasan = DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
WHERE id = [personil_id];
```

---

## 🔍 **VALIDATION RULES**

### **✅ Rule 1: Maximum PS Percentage**
- PS tidak boleh lebih dari 15% dari total jabatan struktural
- PS hanya untuk level Eselon III ke atas

### **✅ Rule 2: Duration Limits**
- PS: Maksimal 1 tahun
- Plt: Sesuai durasi berhalangan
- Pjs: Maksimal 6 bulan
- Plh: Maksimal 7 hari
- Pj: Maksimal 1 tahun

### **✅ Rule 3: Authority Levels**
- Definitif: Kewenangan penuh
- PS/Pjs: Kewenangan operasional terbatas
- Plt: Kewenangan penuh sementara
- Plh: Kewenangan terbatas harian
- Pj: Kewenangan operasional penuh

### **✅ Rule 4: Documentation**
- Semua penugasan harus ada alasan jelas
- Tanggal mulai dan selesai harus terdefinisi
- SK penugasan harus ada (untuk PS/Pjs/Pj)

---

## 🚨 **COMMON MISTAKES TO AVOID**

### **❌ Mistake 1: Overuse PS**
```sql
-- SALAH: Terlalu banyak PS
PS. KANIT RESKRIM
PS. KANIT INTELKAM
PS. BINTARA POLSEK

-- BENAR: Hanya untuk jabatan penting
PS. KASUBBAGBEKPAL
PS. KASIHUMAS
```

### **❌ Mistake 2: Wrong Status**
```sql
-- SALAH: PS untuk jabatan level bawah
PS. KANIT 1

-- BENAR: Definitif untuk jabatan level bawah
KANIT RESKRIM
```

### **❌ Mistake 3: No Documentation**
```sql
-- SALAH: Tidak ada alasan dan tanggal
UPDATE jabatan SET status_penugasan = 'ps';

-- BENAR: Lengkap dokumentasi
UPDATE jabatan SET 
    status_penugasan = 'ps',
    alasan_penugasan = 'Pejabat definitif sedang DIKJUR',
    tanggal_mulai_penugasan = '2024-01-01',
    tanggal_selesai_penugasan = '2024-06-30';
```

---

## 📊 **MONITORING & REPORTING**

### **🔍 Monitoring Query**
```sql
-- Cek status penugasan saat ini
SELECT 
    status_penugasan,
    COUNT(*) as total,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM jabatan), 2) as percentage
FROM jabatan
GROUP BY status_penugasan
ORDER BY total DESC;
```

### **📋 Reporting Template**
```sql
-- Laporan bulanan status penugasan
SELECT 
    j.nama_jabatan,
    j.status_penugasan,
    j.alasan_penugasan,
    j.tanggal_mulai_penugasan,
    j.tanggal_selesai_penugasan,
    p.nama as personil_nama,
    CASE 
        WHEN j.tanggal_selesai_penugasan < CURDATE() THEN 'EXPIRED'
        WHEN j.tanggal_selesai_penugasan BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'EXPIRING SOON'
        ELSE 'ACTIVE'
    END as status
FROM jabatan j
LEFT JOIN personil p ON j.id = p.id_jabatan
WHERE j.status_penugasan != 'definitif'
ORDER BY j.tanggal_selesai_penugasan;
```

---

## 🎯 **BEST PRACTICES**

### **✅ Do:**
- Gunakan status penugasan sesuai kebutuhan
- Dokumentasikan alasan dan durasi
- Monitor expired assignments
- Update status segera setelah ada pejabat definitif

### **❌ Don't:**
- Gunakan PS untuk jabatan level bawah
- Biarkan status penugasan expired
- Lupakan update personil assignment
- Gunakan status tanpa alasan jelas

---

## 🔄 **AUTOMATION TRIGGERS**

### **Trigger untuk Update Status**
```sql
-- Trigger untuk auto-update status expired
DELIMITER //
CREATE TRIGGER check_expired_appointments
AFTER INSERT ON personil
FOR EACH ROW
BEGIN
    -- Logic untuk check dan update status expired
END //
DELIMITER ;
```

### **Scheduled Job untuk Monitoring**
```bash
# Cron job untuk check expired assignments
0 0 * * * /usr/bin/mysql -u root -proot bagops < /path/to/check_expired.sql
```

---

## 📞 **SUPPORT CONTACT**

Untuk bantuan terkait status penugasan:
1. **Database Admin**: Untuk technical issues
2. **HR/SDM**: Untuk kebijakan penugasan
3. **Pimpinan**: Untuk approval penugasan
4. **System Admin**: Untuk application support
