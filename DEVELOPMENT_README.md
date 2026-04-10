# 🚀 SPRIN v1.5.0-dev — Development README
**Last Updated**: 2026-04-10 | **Branch**: kantor | **Status**: Active Development

---

## 📋 Versi & Status

| Item | Detail |
|------|--------|
| Versi | **v1.5.0-dev** |
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

### 📋 Jadwal Piket (`/pages/jadwal_piket.php`) — **v1.4.1**
- View jadwal per tim per bulan/tahun
- Tabel per tanggal: nama, pangkat, shift, jam, lokasi
- Input absensi (hadir/tidak_hadir/sakit/ijin/terlambat) + jam hadir
- **Cover Management** — ganti personil absen, catat pengganti otomatis
- Hapus jadwal series per bulan · Cetak (print CSS)

### 🏠 Dashboard Piket Hari Ini — **v1.4.1**
- Widget otomatis di `main.php` — tabel personil piket hari ini
- Badge notifikasi di navbar (jumlah piket aktif hari ini)

### 📅 Kalender Jadwal — **v1.4.2**
- Modal sumber jadwal: Personil Manual vs Tim Piket
- Section Pengulangan: type/interval/hari/tanggal akhir + preview badge
- `dateClick` handler — klik tanggal langsung buka modal
- Badge 🔁 di event berulang + warna biru tua untuk event tim
- Detail modal tampilkan info Pengulangan & Tim
- **Deteksi konflik** — warning jika personil double-booked

### 📊 Rekap Absensi (`/pages/laporan_piket.php`) — **BARU v1.5.0**
- Filter: bulan / tahun / satuan
- Card persentase kehadiran per satuan
- Tabel detail per personil: hadir/sakit/ijin/terlambat/tidak_hadir/%
- Export CSV · Print

### 📈 Laporan Operasi (`/pages/laporan_operasi.php`) — **BARU v1.5.0**
- Rekap tahunan atau per bulan
- 6 stat cards (total, aktif, selesai, rencana, personil, dukgra)
- Grafik donut per jenis + bar trend bulanan (Chart.js)
- Tabel daftar semua operasi + Export CSV
- **Cetak ST** — Surat Perintah Tugas dari data operasi (1-klik print)

### 🔄 Rotasi Fase Piket — **BARU v1.5.0**
- Tombol **Rotasi** per satuan di papan siklus
- Geser semua tim ke fase berikutnya dalam 1 klik

---

## 🗃️ Status Database

| Tabel | Status | Keterangan |
|-------|--------|------------|
| `personil` | ✅ | 256 record |
| `tim_piket` | ✅ | 15 kolom (fase, jam, durasi) |
| `tim_piket_anggota` | ✅ | |
| `siklus_piket_fase` | ✅ | Definisi fase per bagian |
| `piket_absensi` | ✅ | absensi harian + cover |
| `schedules` | ✅ | + recurrence + tim_id |
| `operations` | ✅ | + tingkat/jenis + recurrence |

---

## 📁 File Utama

```
pages/
├── main.php               # Dashboard + widget piket hari ini
├── tim_piket.php          # Papan siklus + Rotasi Fase
├── jadwal_piket.php       # Jadwal per tim + absensi + Cover
├── calendar_dashboard.php # Kalender + recurrence + tim picker
├── operasi.php            # Daftar operasi + Cetak ST
├── laporan_piket.php      # Rekap absensi per bulan  ← BARU
├── laporan_operasi.php    # Laporan operasi + grafik  ← BARU
api/
├── tim_piket_api.php      # get_all_tim, get_cover_candidates, save_cover, rotasi_fase_semua
├── calendar_api_public.php# create_schedule (recurrence series), get_schedules + tim_id
```

---

## ✅ SEMUA FASE SELESAI

| Fase | Status | Fitur |
|------|--------|-------|
| Fase 1 | ✅ | Dashboard piket, Jadwal piket, Absensi, Kalender recurrence + tim |
| Fase 2 | ✅ | Cover Management, Rekap Absensi |
| Fase 3 | ✅ | Laporan Operasi + grafik + CSV, Cetak ST |
| Fase 4 | ✅ | Deteksi konflik jadwal, Badge notifikasi navbar, Rotasi fase |

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
