# Rencana Pengembangan Selanjutnya — SPRIN v1.3.x
**Dibuat**: 2026-04-10  
**Branch**: kantor  
**Status**: Aktif dikerjakan

---

## ✅ Sudah Selesai (Sesi Ini)

### Modul Operasi Kepolisian
- [x] Halaman `operasi.php` — daftar operasi dengan filter, stat cards, tabel lengkap
- [x] Modal View detail operasi (semua field + badge)
- [x] Modal Edit operasi (form lengkap pre-filled)
- [x] Modal Tambah Operasi dipindah dari `calendar_dashboard.php` ke `operasi.php`
- [x] Tombol Hapus operasi dengan konfirmasi
- [x] API: `create_operation`, `update_operation`, `delete_operation`
- [x] Auto-detect status dari tanggal (Selesai / Sedang Berlangsung / Agenda)
- [x] Terbilang (angka → kata Indonesia) untuk field Dukgra
- [x] Field `tingkat_operasi`, `jenis_operasi`, `operation_date_end` di DB dan form
- [x] Navbar dropdown "Operasional" (Schedule / Daftar Operasi / Tim Piket)

### Kalender
- [x] Upgrade FullCalendar 5.11.3 → 6.1.15
- [x] Tambah view Tahunan (`multiMonthYear`) dan Agenda Tahun (`listYear`)
- [x] Tombol navigasi: Tahun | Bulan | Minggu | Hari | Agenda Tahun

### Tim Piket & Jadwal Berulang
- [x] Migration DB: tabel `tim_piket`, `tim_piket_anggota`
- [x] Kolom recurrence di `schedules` dan `operations`
- [x] Halaman `tim_piket.php` — CRUD tim per bagian/unsur
- [x] Kelola anggota tim (dual-list panel)
- [x] Generate jadwal dari tim dengan pola berulang (harian/mingguan/bulanan/tahunan)
- [x] API `tim_piket_api.php` lengkap

---

## 🔲 Prioritas Tinggi — Kerjakan Selanjutnya

### 1. Update `openScheduleModal` dengan Tim & Recurrence
**File**: `pages/calendar_dashboard.php`  
**Estimasi**: 2–3 jam  

Tambahkan ke modal jadwal:
- [ ] Section "Dari Tim Piket" — dropdown pilih tim → auto-fill personil
- [ ] Section "Pengulangan" — pilih jenis + interval + hari (weekly)
- [ ] Preview teks pengulangan real-time
- [ ] Kirim ke API dengan field recurrence

```
Kolom baru yang dikirim ke API:
- tim_id (opsional)
- recurrence_type
- recurrence_interval
- recurrence_days
- recurrence_end
```

---

### 2. Update Modal Operasi dengan Recurrence
**File**: `pages/operasi.php` (modal Tambah & Edit)  
**Estimasi**: 1–2 jam  

Tambahkan ke kedua modal:
- [ ] Section "Pengulangan Operasi" (operasi rutin mingguan/bulanan)
- [ ] Kirim field recurrence ke API `create_operation` / `update_operation`

---

### 3. Kalender — Badge 🔁 untuk Event Berulang
**File**: `pages/calendar_dashboard.php`  
**Estimasi**: 1 jam  

- [ ] Tampilkan ikon 🔁 pada event di kalender yang punya `recurrence_type != 'none'`
- [ ] Warna berbeda untuk event dari tim piket vs jadwal manual
- [ ] Tooltip: "Berulang setiap X hari/minggu/bulan"

---

### 4. Halaman Jadwal Piket — View per Tim
**File baru**: `pages/jadwal_piket.php`  
**Estimasi**: 3–4 jam  

- [ ] Tabel jadwal per tim, filter bulan/tahun
- [ ] Tampilkan semua jadwal yang dihasilkan dari tim tertentu
- [ ] Tombol "Hapus Series" — hapus semua jadwal dari 1 batch generate
- [ ] Print / export PDF jadwal piket

---

## 🔲 Prioritas Sedang

### 5. Manajemen Shift Rotasi Otomatis
**Konsep**: Tim dengan `shift_default = ROTASI` otomatis berganti shift per periode  
- [ ] Logic rotasi: hitung posisi shift berdasarkan nomor minggu/bulan
- [ ] Tampilkan shift aktif tim saat ini di halaman tim_piket
- [ ] Notifikasi / alert jika shift akan berganti

---

### 6. Dashboard Piket Hari Ini
**File**: update `index.php` atau buat `pages/dashboard_piket.php`  
- [ ] Widget: siapa piket hari ini per fungsi
- [ ] Tabel: nama, pangkat, bagian, shift, jam
- [ ] Status: hadir / belum hadir / ijin

---

### 7. Absensi / Konfirmasi Kehadiran Piket
**Tabel baru**: `piket_absensi`  
- [ ] Personil piket bisa konfirmasi kehadiran
- [ ] Admin bisa input status kehadiran (hadir/tidak hadir/sakit/ijin)
- [ ] Laporan rekap absensi piket per bulan

---

### 8. Laporan Operasi
**File baru**: `pages/laporan_operasi.php`  
- [ ] Rekap operasi per bulan/tahun
- [ ] Total dukgra per jenis operasi
- [ ] Export Excel/PDF
- [ ] Grafik: operasi per tingkat, per jenis

---

## 🔲 Prioritas Rendah / Future

### 9. Notifikasi & Pengingat
- [ ] Notifikasi H-1 sebelum jadwal piket
- [ ] Pengingat operasi yang akan dimulai
- [ ] Sistem notifikasi in-app (badge di navbar)

### 10. Laporan Lengkap Personil
- [ ] Rekap kehadiran personil
- [ ] Riwayat penugasan operasi per personil
- [ ] Kartu Tanda Tugas digital

### 11. Multi-Level User
- [ ] Role: Admin, Operator, Viewer
- [ ] Operator hanya bisa input, tidak bisa hapus
- [ ] Viewer hanya bisa lihat

---

## 📁 Struktur File Baru (Sesi Ini)

```
sprin/
├── pages/
│   ├── operasi.php          ← BARU: daftar & manajemen operasi
│   └── tim_piket.php        ← BARU: manajemen tim piket
├── api/
│   └── tim_piket_api.php    ← BARU: API tim piket
└── cron/
    └── migrate_tim_piket.php ← BARU: migration DB
```

---

## 🗃️ Perubahan Database (Sesi Ini)

```sql
-- Tabel baru
CREATE TABLE tim_piket (...)
CREATE TABLE tim_piket_anggota (...)

-- Kolom baru di schedules
ALTER TABLE schedules ADD COLUMN tim_id INT NULL;
ALTER TABLE schedules ADD COLUMN recurrence_type ENUM('none','daily','weekly','monthly','yearly');
ALTER TABLE schedules ADD COLUMN recurrence_interval INT DEFAULT 1;
ALTER TABLE schedules ADD COLUMN recurrence_days VARCHAR(20);
ALTER TABLE schedules ADD COLUMN recurrence_end DATE;
ALTER TABLE schedules ADD COLUMN recurrence_parent_id INT NULL;

-- Kolom baru di operations
ALTER TABLE operations ADD COLUMN tingkat_operasi ENUM(...);
ALTER TABLE operations ADD COLUMN jenis_operasi ENUM(...);
ALTER TABLE operations ADD COLUMN operation_date_end DATE;
ALTER TABLE operations ADD COLUMN recurrence_type ENUM(...);
ALTER TABLE operations ADD COLUMN recurrence_interval INT DEFAULT 1;
ALTER TABLE operations ADD COLUMN recurrence_days VARCHAR(20);
ALTER TABLE operations ADD COLUMN recurrence_end DATE;
ALTER TABLE operations ADD COLUMN recurrence_parent_id INT NULL;
```

---

## 🔧 Catatan Teknis

- **FullCalendar**: v6.1.15 (upgrade dari 5.11.3) — view tahunan `multiMonthYear` 
- **PHP**: FullCalendar locale `id` sudah bundled dalam `index.global.min.js`
- **API base**: semua API di `/api/`, auth via `$_SESSION['user_id']`
- **Status auto-detect**: logika ada di JS (frontend) DAN PHP API (backend safety net)
- **Recurrence limit**: max 365 hari ke depan per generate (safety limit di API)
- **Terbilang**: fungsi JS di `operasi.php`, konversi angka → kata Bahasa Indonesia hingga triliun

---

*File ini diupdate otomatis setiap sesi pengembangan.*
