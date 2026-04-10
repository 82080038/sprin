# 🚀 SPRIN v1.4.1-dev — Development README
**Last Updated**: 2026-04-10 | **Branch**: kantor | **Status**: Active Development

---

## 📋 Versi & Status

| Item | Detail |
|------|--------|
| Versi | **v1.4.1-dev** |
| Branch | `kantor` |
| PHP | 8.0+ (XAMPP) |
| DB | MySQL 5.7+ · Nama: `bagops` |
| Stability | Testing / Development |

---

## ✅ Fitur yang Sudah Berjalan

### 👤 Manajemen Personil
- CRUD personil: NRP, pangkat, jabatan, bagian, unsur
- Filter & pencarian
- **256 personil aktif** terdaftar

### 🎯 Manajemen Operasi (`/pages/operasi.php`)
- CRUD operasi kepolisian lengkap
- Field: tingkat_operasi, jenis_operasi, dukgra (terbilang otomatis)
- Auto-detect status dari tanggal (Selesai/Berlangsung/Agenda)
- Badge visual berdasarkan status & tingkat

### 📅 Kalender Jadwal (`/pages/calendar_dashboard.php`)
- FullCalendar **v6.1.15** (upgrade dari 5.11.3)
- View: Tahun / Bulan / Minggu / Hari / Agenda Tahun
- Locale Indonesia (bundled)
- CRUD jadwal dari kalender

### 🛡️ Manajemen Tim Piket (`/pages/tim_piket.php`)
- Filter cerdas: **Unsur Tugas Pokok + Kewilayahan + SPKT** (15 satuan)
- Form Tambah Tim: Unsur → Bagian → Nama → Jenis → Fase → Jam+Durasi
- **Papan Siklus Kanban** per satuan — visualisasi fase sebagai kolom
- **Drag & drop** kartu tim antar kolom fase
- Modal **Atur Siklus**: definisi fase, durasi, jam mulai auto/manual
- Kelola anggota tim (dual-list: tersedia ↔ anggota)
- Generate jadwal berulang dari tim (harian/mingguan/bulanan/tahunan)

### 📋 Jadwal Piket (`/pages/jadwal_piket.php`) — **BARU v1.4.1**
- View jadwal per tim per bulan/tahun
- Tabel per tanggal: nama, pangkat, shift, jam, lokasi
- Input absensi (hadir/tidak_hadir/sakit/ijin/terlambat) + jam hadir
- Hapus jadwal series per bulan
- Cetak (print CSS)

### 🏠 Dashboard Piket Hari Ini (`/pages/main.php`) — **BARU v1.4.1**
- Widget otomatis tampil jika ada jadwal dari tim piket hari ini
- Tabel: Satuan | Nama | Pangkat | Shift | Jam | Tim
- Link langsung ke Jadwal Lengkap

---

## 🗃️ Status Database

| Tabel | Status | Keterangan |
|-------|--------|------------|
| `personil` | ✅ | 256 record |
| `tim_piket` | ✅ | 15 kolom (fase, jam, durasi) |
| `tim_piket_anggota` | ✅ | |
| `siklus_piket_fase` | ✅ | Definisi fase per bagian |
| `piket_absensi` | ✅ | **BARU v1.4.1** — absensi harian |
| `schedules` | ✅ | + recurrence + tim_id |
| `operations` | ✅ | + tingkat/jenis + recurrence |

---

## 📁 File Utama

```
pages/
├── main.php               # Dashboard + widget piket hari ini
├── tim_piket.php          # Papan siklus + manajemen tim
├── jadwal_piket.php       # Jadwal per tim + absensi   ← BARU
├── calendar_dashboard.php # Kalender FullCalendar 6.1.15
├── operasi.php            # Daftar & manajemen operasi
api/
├── tim_piket_api.php      # get_piket_hari_ini, save_absensi, delete_jadwal_series ← BARU
├── calendar_api_public.php
cron/
└── migrate_tim_piket.php  # Migration DB (termasuk piket_absensi)
```

---

## 🔄 Selanjutnya (Fase 1 sisa)

- [ ] Recurrence di modal jadwal kalender (pilih tim + pengulangan)
- [ ] Badge 🔁 di kalender untuk event berulang
- [ ] Recurrence di modal Tambah/Edit Operasi

## 🟠 Fase 2 — Sistem Piket Lengkap

- [ ] Cover Management — substitusi personil absen
- [ ] Rekap Absensi per bulan per satuan

## 🟡 Fase 3 — Laporan & Cetak

- [ ] Laporan Operasi (rekap bulanan + grafik)
- [ ] Cetak Surat Perintah Tugas (ST)

---

## 🔧 Setup Development

```bash
# Start XAMPP
sudo /opt/lampp/lampp start

# Run Migration (jika tabel belum ada)
# Browser: http://localhost/sprin/cron/migrate_tim_piket.php

# Akses aplikasi
# http://localhost/sprin/pages/main.php
```

**DB**: host=localhost, name=bagops, user=root, pass=root

---
**⚠️ DEVELOPMENT VERSION — SAMPLE DATA ONLY**
