---
description: Workflow manajemen Tim Piket dan Siklus Piket SPRIN — setup awal, tambah tim, atur siklus, geser fase, generate jadwal
---

# Workflow: Manajemen Tim Piket & Siklus

## Overview
Sistem Tim Piket mengelola regu piket per satuan.
Filter: Unsur Tugas Pokok (SAT-) + Kewilayahan (POLSEK) + SPKT.

## 1. Migration DB (Jalankan 1x)
```
http://localhost/sprin/cron/migrate_tim_piket.php
```
Verifikasi: `SHOW TABLES LIKE '%piket%';` → harus ada 3 tabel.

## 2. Buka Halaman Tim Piket
```
http://localhost/sprin/pages/tim_piket.php
```
Tampil Papan Siklus per Satuan (14 satuan).

## 3. Setup Siklus per Satuan (Lakukan Dulu Sebelum Tambah Tim)
1. Klik tombol **Siklus** di card satuan
2. Klik **Tambah Fase** → isi nama, durasi, jam mulai, mode (Auto/Manual), wajib/opsional
3. **Mode Auto**: jam mulai = jam selesai fase sebelumnya (propagasi otomatis)
4. Klik **Simpan Siklus**

Contoh SAT SAMAPTA:
- Fase 1: Piket Fungsi, 8 jam, 07:00, Manual, Wajib
- Fase 2: Lepas Piket, 8 jam, 15:00, Auto, Wajib
- Fase 3: Piket Cadangan, 4 jam, 23:00, Auto, Opsional

## 4. Tambah Tim
1. Klik tombol **+ Tim** di card satuan
2. Isi form: Unsur → Bagian → Nama Tim → Jenis → Fase → Jam+Durasi
3. Fase dropdown otomatis muncul setelah pilih bagian
4. Jam Selesai dihitung otomatis dari Jam Mulai + Durasi
5. Klik **Simpan Tim**

## 5. Kelola Anggota
- Klik ⋮ → **Anggota** di kartu tim
- Dual-list: personil tersedia (kiri) ↔ anggota tim (kanan)
- Tombol » tambah, « keluarkan

## 6. Geser Fase
- **Drag & drop** kartu tim ke kolom fase tujuan
- Atau Edit tim → ubah Posisi Fase

## 7. Generate Jadwal
1. Klik ⋮ → **Buat Jadwal** di kartu tim
2. Pilih shift, tanggal mulai-selesai, pola pengulangan
3. Klik **Generate Jadwal** → redirect ke kalender

## 8. Fatigue Check
1. Buka tab **Dashboard Hari Ini**
2. Klik **Fatigue Check** → modal terbuka
3. Pilih personil, tanggal, dan minimum jeda istirahat (jam)
4. Klik **Cek Sekarang** → warning jika jeda kurang dari minimum

## 9. Swap Shift (Tukar Jadwal)
1. Buka tab **Dashboard Hari Ini**
2. Klik **Swap Shift** → modal terbuka dengan daftar jadwal hari ini
3. Pilih 2 jadwal yang akan ditukar (klik tombol "1" dan "2")
4. Klik **Tukar Jadwal** → personil saling ditukar

## 10. Export Statistik CSV
1. Buka tab **Statistik**
2. Pilih bulan dan tahun
3. Klik tombol **Export** → file CSV ter-download

## 11. Notifikasi Rotasi
- Saat rotasi otomatis/manual dijalankan, notifikasi muncul di tab Dashboard
- Klik X untuk menandai sudah dibaca
- Cron auto_rotasi otomatis buat notifikasi

## API Endpoints
| Action | Method | Fungsi |
|--------|--------|--------|
| get_siklus | GET | Fase siklus per bagian |
| get_personil_all | GET | Semua personil aktif |
| get_anggota | GET | Anggota tim |
| dashboard_hari_ini | GET | Dashboard piket hari ini |
| statistik_personil | GET | Statistik jam piket per personil |
| calendar_data | GET | Data kalender per bulan |
| get_rotasi_log | GET | Riwayat rotasi |
| fatigue_check | GET | Cek jeda istirahat personil |
| cetak_sprin_data | GET | Data untuk cetak SPRIN |
| get_notifikasi_piket | GET | Notifikasi rotasi (unread) |
| create_tim | POST | Buat tim |
| update_tim | POST | Edit tim |
| delete_tim | POST | Hapus tim |
| save_siklus | POST | Simpan fase siklus |
| geser_fase | POST | Pindah tim ke fase lain |
| save_anggota_peran | POST | Simpan anggota + peran |
| generate_jadwal_tim | POST | Generate jadwal berulang |
| swap_shift | POST | Tukar jadwal 2 personil |
| rotasi_bagian | POST | Rotasi manual per bagian |
| read_notifikasi | POST | Tandai notifikasi sudah dibaca |

## Troubleshooting
| Masalah | Solusi |
|---------|--------|
| Bagian tidak muncul | Cek login + XAMPP running |
| Fase tidak muncul di form | Buat siklus dulu |
| API "Unauthorized" | Session expired, login ulang |
| Tabel tidak ada | Jalankan migration |
