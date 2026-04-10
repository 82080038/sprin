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

## API Endpoints
| Action | Method | Fungsi |
|--------|--------|--------|
| get_siklus | GET | Fase siklus per bagian |
| get_personil_all | GET | Semua personil aktif |
| get_anggota | GET | Anggota tim |
| create_tim | POST | Buat tim |
| update_tim | POST | Edit tim |
| delete_tim | POST | Hapus tim |
| save_siklus | POST | Simpan fase siklus |
| geser_fase | POST | Pindah tim ke fase lain |
| save_anggota | POST | Simpan anggota |
| generate_jadwal_tim | POST | Generate jadwal berulang |

## Troubleshooting
| Masalah | Solusi |
|---------|--------|
| Bagian tidak muncul | Cek login + XAMPP running |
| Fase tidak muncul di form | Buat siklus dulu |
| API "Unauthorized" | Session expired, login ulang |
| Tabel tidak ada | Jalankan migration |
