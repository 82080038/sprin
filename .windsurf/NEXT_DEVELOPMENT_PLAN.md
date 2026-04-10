# Rencana Pengembangan SPRIN v1.4.x
**Diperbarui**: 2026-04-10  
**Branch**: kantor  
**Status**: Aktif dikerjakan

---

## ✅ SELESAI — v1.3.x

- [x] Modul Operasi Kepolisian — CRUD + auto-detect status + terbilang dukgra
- [x] Kalender FullCalendar 6.1.15 — view Tahun/Bulan/Minggu/Hari/Agenda
- [x] Tim Piket Foundation — DB tabel, API CRUD, generate jadwal

## ✅ SELESAI — v1.4.0-dev

- [x] DB: tabel `siklus_piket_fase`, kolom baru di `tim_piket`
- [x] Filter cerdas bagian: Unsur 3+4 + SPKT (id=20), **15 satuan**
- [x] Form Tambah Tim baru: Unsur→Bagian→Nama→Jenis→Fase→Jam+Durasi
- [x] Papan Siklus Kanban per satuan + Drag & Drop
- [x] Modal Atur Siklus: fase, durasi, jam, mode Auto/Manual, propagasi
- [x] API: `get_siklus`, `save_siklus`, `geser_fase`
- [x] Dokumentasi MD diperbarui + `piket.md` workflow

## ✅ SELESAI — v1.4.1-dev (Sesi Ini)

- [x] **DB: tabel `piket_absensi`** — absensi harian dengan status + jam hadir
- [x] **Halaman `jadwal_piket.php`** — view jadwal per tim/bulan + input absensi + hapus series
- [x] **Widget Piket Hari Ini** di `main.php` — tabel otomatis dari schedules hari ini
- [x] **Navbar** — link "Jadwal Piket" di dropdown Operasional
- [x] **API**: `get_piket_hari_ini`, `save_absensi`, `delete_jadwal_series`
- [x] `TODO.md` dibuat di root project
- [x] Semua MD files diperbarui
- [x] `cron/migrate_tim_piket.php` diperbarui dengan `piket_absensi`

---

## 🎯 PRIORITAS TINGGI — Kerjakan Selanjutnya

### 1. Kalender — Pilih Tim & Recurrence di Modal Jadwal
**File**: `pages/calendar_dashboard.php`

- [ ] Tab **"Dari Tim Piket"**: dropdown tim → personil auto-fill
- [ ] Section **Pengulangan**: type + interval + hari (weekly) + tanggal akhir
- [ ] Kirim `tim_id`, `recurrence_type`, `recurrence_interval`, `recurrence_days`, `recurrence_end`

### 2. Kalender — Badge Event Berulang
**File**: `pages/calendar_dashboard.php`

- [ ] Icon 🔁 pada event `recurrence_type != 'none'`
- [ ] Warna beda: tim piket vs jadwal manual

### 3. Recurrence di Modal Operasi
**File**: `pages/operasi.php`

- [ ] Section "Pengulangan" di modal Tambah & Edit
- [ ] Kirim ke API operasi

---

## 🔲 PRIORITAS SEDANG

### 4. Cover Management — Substitusi Personil
- [ ] Jika personil absen → tampilkan pengganti dari satuan yang sama
- [ ] Log: siapa menggantikan siapa, tanggal

### 5. Rekap Absensi Piket
**File baru**: `pages/laporan_piket.php`
- [ ] Rekap per personil per bulan
- [ ] Rekap per satuan: % kehadiran
- [ ] Export Excel/PDF

---

## 🔲 PRIORITAS RENDAH / FUTURE

### 6. Laporan Operasi (`pages/laporan_operasi.php`)
- [ ] Rekap per bulan/tahun + grafik + export

### 7. Cetak Surat Perintah Tugas (ST)
- [ ] Generate dokumen ST dari data tim + jadwal

### 8. Rotasi Shift Otomatis
- [ ] Tim `ROTASI` ganti fase siklus otomatis tiap X hari

### 9. Notifikasi In-App
- [ ] Badge navbar jadwal hari ini
- [ ] Pengingat H-1 operasi

### 10. Multi-Level User
- [ ] Role: Admin / Operator / Viewer
- [ ] Guard akses per role

---

## 📁 Struktur File Saat Ini

```
sprin/
├── TODO.md                  ← ⭐ BARU — todo list lengkap semua fase
├── pages/
│   ├── main.php             ← Dashboard + widget Piket Hari Ini ⭐
│   ├── tim_piket.php        ← Tim + Papan Siklus Piket
│   ├── jadwal_piket.php     ← Jadwal per Tim + Absensi ⭐ BARU
│   ├── calendar_dashboard.php
│   ├── operasi.php
│   └── ...
├── api/
│   ├── tim_piket_api.php    ← +get_piket_hari_ini, save_absensi, delete_jadwal_series ⭐
│   └── calendar_api_public.php
└── cron/
    └── migrate_tim_piket.php  ← +piket_absensi table ⭐
```

---

## 🗃️ Status Database

| Tabel | Status |
|-------|--------|
| `tim_piket` | ✅ fase_siklus_id, jam_mulai_aktif, durasi_jam |
| `tim_piket_anggota` | ✅ |
| `siklus_piket_fase` | ✅ |
| `piket_absensi` | ✅ **BARU** — schedule_id, status, jam_hadir |
| `schedules` | ✅ tim_id, recurrence_* |
| `operations` | ✅ tingkat, jenis, recurrence_* |

---

## 🔧 Catatan Teknis

| Komponen | Detail |
|----------|--------|
| **Filter Piket** | Unsur id=3+4 + bagian id=20 (SPKT) |
| **Satuan Piket** | 15 satuan: 9 SAT + 5 POLSEK + SPKT |
| **FullCalendar** | v6.1.15 — locale id bundled |
| **Auth** | Semua API: cek `$_SESSION['user_id']` |
| **Migration** | `cron/migrate_tim_piket.php` (jalankan 1x) |

---

*Diupdate: 2026-04-10 — v1.4.1-dev*
