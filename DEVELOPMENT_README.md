# 🚀 SPRIN v1.7.0-dev — Development README
**Last Updated**: 2026-04-10 | **Branch**: kantor | **Status**: Active Development

---

## 📋 Versi & Status

| Item | Detail |
|------|--------|
| Versi | **v1.7.0-dev** |
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
- **Nomor Sprint Otomatis**: `Sprin / [urut] / [bulan-romawi] / [tahun] / OPS` ← BARU v1.6.0
- Cetak ST menggunakan nomor sprint yang ter-generate

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

### 📄 LHPT — Laporan Hasil Pelaksanaan Tugas (`/pages/lhpt.php`) — **BARU v1.6.0**
- CRUD lengkap: isi laporan, hasil, kendala, rekomendasi
- Nomor LHPT auto-generate: `LHPT / [urut] / [bulan-romawi] / [tahun] / OPS`
- Cetak format standar Polri (print window)
- Terkait langsung ke operasi (FK)

### ✉️ Ekspedisi Surat (`/pages/ekspedisi.php`) — **BARU v1.6.0**
- Buku agenda surat masuk & keluar
- Nomor agenda auto: `SM/0001/2026` (masuk), `SK/0001/2026` (keluar)
- Kategori: Biasa, Penting, Rahasia, Segera
- Status: Diterima, Diproses, Selesai, Diarsipkan
- Disposisi & keterangan, role-based aksi

### � Multi-Level User Role — **BARU v1.6.0**
- 3 role: `admin` / `operator` / `viewer`
- Guard middleware: `AuthHelper::requireRole()`, `canEdit()`, `canDelete()`
- Menu Pengaturan hanya untuk admin
- Badge role di navbar + JS variable `SPRIN_USER_ROLE`
- User default: bagops (admin), operator (operator123), viewer (viewer123)

### 🏠 Dashboard Komandan (`/pages/main.php`) — **BARU v1.7.0**
- Greeting role-based + waktu real-time
- 4 summary cards: operasi aktif, rencana, LHPT draft, surat diproses
- Piket hari ini widget + statistik personil (8 cards)
- Sidebar: aksi cepat, rekap operasional, info sistem (admin only)

### 🏳️ Apel Nominal Digital (`/pages/apel_nominal.php`) — **BARU v1.7.0**
- Absensi apel pagi/sore untuk seluruh personil
- Filter per unsur/bagian, 7 status kehadiran
- Rekap bulanan per personil + persentase
- Bulk "Semua Hadir" button

### 🏋️ Training Management (`/pages/pelatihan.php`) — **BARU v1.7.0**
- CRUD pelatihan praoperasi
- 6 jenis: menembak, bela diri, SAR, ketahanan, teknis, lainnya
- Stat cards: total, selesai, rencana, berlangsung, jam, peserta
- Role-based aksi (edit: admin+operator, delete: admin)

---

## �🗃️ Status Database

| Tabel | Status | Keterangan |
|-------|--------|------------|
| `personil` | ✅ | 256 record |
| `tim_piket` | ✅ | 15 kolom (fase, jam, durasi) |
| `tim_piket_anggota` | ✅ | |
| `siklus_piket_fase` | ✅ | Definisi fase per bagian |
| `piket_absensi` | ✅ | absensi harian + cover |
| `schedules` | ✅ | + recurrence + tim_id |
| `operations` | ✅ | + tingkat/jenis + recurrence + nomor_sprint |
| `lhpt` | ✅ | Laporan Hasil Pelaksanaan Tugas |
| `surat_ekspedisi` | ✅ | Surat masuk/keluar + agenda otomatis |
| `apel_nominal` | ✅ | Absensi apel pagi/sore |
| `pelatihan` | ✅ | Training management |

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
├── laporan_operasi.php    # Laporan operasi + grafik
├── lhpt.php               # LHPT pasca operasi        ← BARU v1.6.0
├── ekspedisi.php          # Surat masuk/keluar         ← BARU v1.6.0
├── struktur_organisasi.php # Org chart
├── pengaturan.php         # System settings (admin)
├── apel_nominal.php       # Apel nominal digital       ← BARU v1.7.0
├── pelatihan.php          # Training management        ← BARU v1.7.0
api/
├── tim_piket_api.php      # get_all_tim, get_cover_candidates, save_cover, rotasi_fase_semua
├── calendar_api_public.php# create_schedule (recurrence series), get_schedules + tim_id
├── lhpt_api.php           # CRUD LHPT                  ← BARU v1.6.0
├── ekspedisi_api.php      # CRUD surat ekspedisi       ← BARU v1.6.0
├── apel_api.php           # Apel nominal               ← BARU v1.7.0
├── pelatihan_api.php      # Training management        ← BARU v1.7.0
```

---

## ✅ SEMUA FASE SELESAI

| Fase | Status | Fitur |
|------|--------|-------|
| Fase 1 | ✅ | Dashboard piket, Jadwal piket, Absensi, Kalender recurrence + tim |
| Fase 2 | ✅ | Cover Management, Rekap Absensi |
| Fase 3 | ✅ | Laporan Operasi + grafik + CSV, Cetak ST |
| Fase 4 | ✅ | Deteksi konflik jadwal, Badge notifikasi navbar, Rotasi fase |
| Fase 5 | ✅ | Nomor Sprint, LHPT, Ekspedisi Surat, Multi-Level User Role |
| Fase 6 | ✅ | Dashboard Komandan, Apel Nominal, Training Management |

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
