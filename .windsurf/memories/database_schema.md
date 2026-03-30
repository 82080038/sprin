---
description: Skema database bagops - 16 tabel utama
---

# Database Schema - bagops

## Overview

Database `bagops` terdiri dari 16 tabel utama untuk sistem manajemen personil POLRES Samosir.

## Database Connection

Aplikasi menggunakan Database singleton pattern dengan konfigurasi:
- **Primary**: Unix socket `/opt/lampp/var/mysql/mysql.sock` (XAMPP)
- **Fallback**: TCP connection `mysql:host=localhost;dbname=bagops`
- **PDO Settings**: ERRMODE_EXCEPTION, FETCH_ASSOC, EMULATE_PREPARES=false
- **Timezone**: `+07:00` (Asia/Jakarta)
- **SQL Mode**: `STRICT_ALL_TABLES`

## Entity Relationship Summary

```
unsur (1) ---> bagian (N) ---> personil (N)
                           |
                           |-> jabatan (N)
                           |-> pangkat (N)
                           
personil (1) ---> personil_kontak (N)
personil (1) ---> personil_pendidikan (N)
personil (1) ---> personil_medsos (N)
personil (1) ---> schedules (N)
personil (1) ---> assignments (N)

operations (1) ---> assignments (N)
operations (1) ---> schedules (N)
```

**Standardized Foreign Key References:**
- `bagian.id_unsur` -> `unsur.id`
- `jabatan.id_bagian` -> `bagian.id`
- `personil.id_pangkat` -> `pangkat.id`
- `personil.id_jabatan` -> `jabatan.id`
- `personil.id_bagian` -> `bagian.id`
- `personil.id_unsur` -> `unsur.id`
- `personil.id_jenis_pegawai` -> `master_jenis_pegawai.id`

## Daftar Tabel

### 1. unsur
Struktur organisasi tingkat tinggi POLRI.

| Field | Type | Description |
|-------|------|-------------|
| id | int(11) PK | Auto increment |
| kode_unsur | varchar(50) | Kode unik unsur |
| nama_unsur | varchar(100) | Nama unsur |
| deskripsi | text | Keterangan |
| is_active | tinyint(1) | Status aktif (default 1) |

**Data Unsur:**
1. PIMPINAN (Kapolres, Wakapolres)
2. BAG (Bagian: OPS, REN, SDM, LOG)
3. SAT (Satuan: Intelkam, Reskrim, Resnarkoba, Lantas, Samapta, Pamobvit, Polairud, Tahti, Binmas)
4. POLSEK (Polsek: Harian Boho, Palipi, Simanindo, Onan Runggu, Pangururan)
5. SPKT (SPKT, SIUM, SIKEU, SIDOKKES, SIWAS, SITIK, SIKUM, SIPROPAM, SIHUMAS)
6. BKO (Bantuan Kendali Operasional)

### 2. bagian
Unit kerja di bawah unsur.

| Field | Type | Description |
|-------|------|-------------|
| id | int(11) PK | Auto increment |
| kode_bagian | varchar(50) | Kode unik |
| nama_bagian | varchar(100) | Nama bagian |
| id_unsur | int(11) FK | Reference -> unsur.id |
| deskripsi | text | Keterangan |
| is_active | tinyint(1) | Status aktif |
| created_at | timestamp | Auto |
| updated_at | timestamp | Auto update |

**Total: 29 bagian** termasuk Pimpinan, BAG OPS, SAT RESKRIM, POLSEK, dll.

### 3. pangkat
Daftar pangkat POLRI/ASN.

| Field | Type | Description |
|-------|------|-------------|
| id | int(11) PK | Auto increment |
| kode_pangkat | varchar(20) | Kode pangkat |
| nama_pangkat | varchar(50) | Nama lengkap |
| golongan | varchar(20) | Golongan (III/a, etc) |
| jenis | enum | polri/asn |
| tingkat | int | Level hierarki |

**Contoh:** AIPTU, IPTU, AKP, KOMPOL, AKBP, KOMBES (untuk POLRI)

### 4. jabatan
Daftar jabatan.

| Field | Type | Description |
|-------|------|-------------|
| id | int(11) PK | Auto increment |
| kode_jabatan | varchar(50) | Kode unik |
| nama_jabatan | varchar(100) | Nama jabatan |
| id_bagian | int(11) FK | Reference -> bagian.id |
| level | int | Level dalam struktur |
| is_struktural | tinyint(1) | Jabatan struktural? |
| is_active | tinyint(1) | Status aktif |

### 5. personil
Tabel utama data personil.

| Field | Type | Description |
|-------|------|-------------|
| id | int(11) PK | Auto increment |
| nrk | varchar(20) | Nomor Registrasi Kepolisian |
| nrp | varchar(20) | Nomor Registrasi Pokok |
| nama_lengkap | varchar(100) | Nama lengkap |
| gelar_depan | varchar(20) | Gelar depan (Dr., Ir., dll) |
| gelar_belakang | varchar(20) | Gelar belakang (S.H., M.M., dll) |
| id_pangkat | int(11) FK | Reference -> pangkat.id |
| id_jabatan | int(11) FK | Reference -> jabatan.id |
| id_bagian | int(11) FK | Reference -> bagian.id |
| jenis_kelamin | enum | L/P |
| tempat_lahir | varchar(50) | - |
| tanggal_lahir | date | - |
| agama | varchar(20) | - |
| status_perkawinan | enum | belum_menikah/menikah/cerai |
| alamat | text | Alamat lengkap |
| no_telepon | varchar(20) | - |
| email | varchar(100) | - |
| status_pegawai | enum | aktif/cuti/pensiun/dinas_luar |
| jenis_pegawai | enum | polri/asn/p3k/bukan_pegawai |
| foto | varchar(255) | Path file foto |
| is_deleted | tinyint(1) | Soft delete flag |
| created_at | timestamp | Auto |
| updated_at | timestamp | Auto update |

### 6. personil_kontak
Data kontak tambahan personil.

| Field | Type | Description |
|-------|------|-------------|
| id | int(11) PK | - |
| personil_id | int(11) FK | Reference -> personil.id |
| jenis_kontak | enum | telepon/email/emergency/alamat |
| nilai | varchar(255) | Nilai kontak |
| is_primary | tinyint(1) | Kontak utama? |
| keterangan | varchar(255) | - |

### 7. personil_pendidikan
Riwayat pendidikan personil.

| Field | Type | Description |
|-------|------|-------------|
| id | int(11) PK | - |
| personil_id | int(11) FK | Reference -> personil.id |
| jenjang | enum | sd/smp/sma/d1/d2/d3/d4/s1/s2/s3 |
| nama_sekolah | varchar(100) | - |
| jurusan | varchar(100) | - |
| tahun_lulus | year | - |
| is_akreditasi | tinyint(1) | Status akreditasi |
| gelar | varchar(20) | Gelar yang didapat |
| is_verified | tinyint(1) | Terverifikasi? |

### 8. personil_medsos
Media sosial personil.

| Field | Type | Description |
|-------|------|-------------|
| id | int(11) PK | - |
| personil_id | int(11) FK | Reference -> personil.id |
| platform | enum | instagram/facebook/twitter/whatsapp |
| username | varchar(100) | - |
| url | varchar(255) | Link profil |
| is_public | tinyint(1) | Publik/privat |

### 9. schedules
Jadwal piket dan tugas.

| Field | Type | Description |
|-------|------|-------------|
| id | int(11) PK | - |
| personil_id | varchar(20) FK | Reference -> personil.id |
| personil_name | varchar(255) | Redundancy untuk performa |
| title | varchar(255) | Judul jadwal |
| description | text | Keterangan |
| start_time | datetime | Waktu mulai |
| end_time | datetime | Waktu selesai |
| location | varchar(255) | Lokasi |
| event_type | varchar(50) | Jenis: piket, operasi, rapat |
| is_all_day | tinyint(1) | Sehari penuh? |
| status | enum | scheduled/in_progress/completed/cancelled |
| google_event_id | varchar(255) | Sync dengan Google Calendar |
| created_at | timestamp | - |
| updated_at | timestamp | - |

### 10. assignments
Penugasan personil ke operasi.

| Field | Type | Description |
|-------|------|-------------|
| id | int(11) PK | - |
| operation_id | int(11) FK | Reference -> operations.id |
| personil_id | varchar(20) FK | Reference -> personil.id |
| personil_name | varchar(255) | Redundancy |
| role | varchar(100) | Peran dalam operasi |
| assigned_at | timestamp | Waktu penugasan |

### 11. operations
Data operasi kepolisian.

| Field | Type | Description |
|-------|------|-------------|
| id | int(11) PK | - |
| operation_name | varchar(255) | Nama operasi |
| operation_type | varchar(100) | Jenis operasi |
| description | text | Keterangan |
| start_date | datetime | - |
| end_date | datetime | - |
| location | varchar(255) | Lokasi |
| status | enum | planned/ongoing/completed/cancelled |
| created_by | int(11) | User pembuat |
| created_at | timestamp | - |
| updated_at | timestamp | - |

### 12. calendar_tokens
Token untuk Google Calendar integration.

| Field | Type | Description |
|-------|------|-------------|
| id | int(11) PK | - |
| user_id | int(11) | Reference -> user |
| access_token | text | OAuth access token |
| refresh_token | text | OAuth refresh token |
| token_expiry | datetime | Kadaluarsa token |
| scope | varchar(255) | Scope permissions |
| created_at | timestamp | - |
| updated_at | timestamp | - |

### 13. bagian_pimpinan
Mapping pimpinan ke bagian.

| Field | Type | Description |
|-------|------|-------------|
| id | int(11) PK | - |
| bagian_id | int(11) FK | Reference -> bagian.id |
| personil_id | int(11) FK | Reference -> personil.id |
| peran | enum | koordinator/anggota |
| periode_mulai | date | - |
| periode_selesai | date | - |
| is_aktif | tinyint(1) | Status keaktifan |
| created_at | timestamp | - |

### 14. master_jenis_pegawai
Master data jenis pegawai.

| Field | Type | Description |
|-------|------|-------------|
| id | int(11) PK | - |
| kode_jenis | varchar(20) | Kode unik |
| nama_jenis | varchar(50) | Nama lengkap |
| deskripsi | text | - |
| is_active | tinyint(1) | - |

### 15. master_pendidikan
Master data jenjang pendidikan.

| Field | Type | Description |
|-------|------|-------------|
| id | int(11) PK | - |
| kode_pendidikan | varchar(20) | - |
| nama_pendidikan | varchar(50) | - |
| level | int | Tingkatan |
| is_active | tinyint(1) | - |

### 16. personil_backup
Backup data personil (audit trail).

| Field | Type | Description |
|-------|------|-------------|
| id | int(11) PK | - |
| personil_id | int(11) | ID personil yang di-backup |
| backup_data | longtext | JSON data lengkap |
| backup_type | enum | manual/scheduled/pre_update |
| created_by | varchar(100) | User yang membuat backup |
| created_at | timestamp | - |

## Indexes & Constraints

### Primary Keys
Semua tabel menggunakan `id` INT AUTO_INCREMENT sebagai primary key.

### Foreign Keys
- `bagian.id_unsur` -> `unsur.id`
- `jabatan.id_bagian` -> `bagian.id`
- `personil.id_pangkat` -> `pangkat.id`
- `personil.id_jabatan` -> `jabatan.id`
- `personil.id_bagian` -> `bagian.id`
- `personil_kontak.personil_id` -> `personil.id`
- `personil_pendidikan.personil_id` -> `personil.id`
- `personil_medsos.personil_id` -> `personil.id`
- `schedules.personil_id` -> `personil.id`
- `assignments.operation_id` -> `operations.id`
- `assignments.personil_id` -> `personil.id`
- `bagian_pimpinan.bagian_id` -> `bagian.id`
- `bagian_pimpinan.personil_id` -> `personil.id`

### Soft Delete Pattern
Tabel utama menggunakan kolom `is_deleted` tinyint(1) untuk soft delete:
- `personil.is_deleted`
- Default value: 0 (aktif)
- Set 1 untuk menandai terhapus

## Sample Data

### Unsur (6 record)
1. PIMPINAN - Unit Pimpinan POLRES
2. BAG - Bagian (OPS, REN, SDM, LOG)
3. SAT - Satuan (Intelkam, Reskrim, dll)
4. POLSEK - Polsek (Harian Boho, Palipi, dll)
5. SPKT - Sentra Pelayanan Kepolisian Terpadu
6. BKO - Bantuan Kendali Operasional

### Bagian (29 record)
Pimpinan, BAG OPS, BAG REN, BAG SDM, BAG LOG, SAT Intelkam, SAT Reskrim, SAT Resnarkoba, SAT Lantas, SAT Samapta, SAT Pamobvit, SAT Polairud, SAT Tahti, SAT Binmas, POLSEK Harian Boho, POLSEK Palipi, POLSEK Simanindo, POLSEK Onan Runggu, POLSEK Pangururan, SPKT, SIUM, SIKEU, SIDOKKES, SIWAS, SITIK, SIKUM, SIPROPAM, SIHUMAS, BKO

### Pangkat (POLRI)
- Perwira: KOMBES, AKBP, KOMPOL, AKP
- Bintara: IPTU, IPDA, AIPTU, AIPDA
- Tamtama: BRIPKA, BRIGPOL, BRIPTU, BRIPDA

## Migration Notes

Ketika menambah fitur baru:
1. Buat backup database terlebih dahulu
2. Gunakan migrations untuk perubahan schema
3. Update model classes jika ada perubahan field
4. Update API endpoints untuk field baru
5. Update UI forms untuk menampilkan field baru
