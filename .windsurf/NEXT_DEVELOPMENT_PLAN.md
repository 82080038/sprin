# Rencana Pengembangan SPRIN v1.5.x → v2.0
**Diperbarui**: 2026-04-10  
**Branch**: kantor  
**Status**: v1.5.0 selesai — Lanjut ke Fase 5 (BAGOPS Lengkap)

> ⭐ **KONTEKS PENTING**: Aplikasi ini dibuat untuk **BAGOPS Polres Samosir**.
> BAGOPS = Bagian Operasional, tupoksi: perencanaan & pengendalian operasi kepolisian,
> administrasi Sprint/ST, LHPT, piket/penugasan, ekspedisi surat.
> Baca `.windsurf/BAGOPS_ANALYSIS.md` untuk analisis lengkap & saran fitur.

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

## ✅ SELESAI — v1.4.1-dev
- [x] `piket_absensi` DB + API absensi + widget piket hari ini

## ✅ SELESAI — v1.4.2-dev
- [x] Kalender: modal tim piket + recurrence + badge 🔁 + konflik deteksi
- [x] Operasi: recurrence modal Tambah/Edit + Cetak ST

## ✅ SELESAI — v1.5.0-dev (Semua Fase)
- [x] `laporan_piket.php` — rekap absensi per bulan + export CSV
- [x] `laporan_operasi.php` — rekap operasi + grafik Chart.js + export CSV
- [x] Cover Management — substitusi personil absen + log otomatis
- [x] Rotasi fase piket per satuan (1 klik)
- [x] Badge notifikasi navbar — count piket hari ini
- [x] Laporan Operasi → Cetak ST template print-ready

---

## 🔴 FASE 5 — Prioritas Berikutnya (v2.0)

> Berdasarkan analisis tupoksi BAGOPS. Detail: `.windsurf/BAGOPS_ANALYSIS.md`

### 1. 🔴 Multi-Level User Role
- Role: `admin` / `operator` / `viewer`
- Guard middleware per halaman
- Tabel: `users` (ada) + tambah kolom `role`
- **Mengapa penting**: Data operasional Polri tidak boleh terbuka ke semua orang

### 2. � LHPT — Laporan Hasil Pelaksanaan Tugas
- Setiap operasi selesai WAJIB ada LHPT (pertanggungjawaban ke atasan)
- Tabel baru: `lhpt` (operasi_id FK, nomor, isi, kendala, hasil, rekomendasi)
- Print format standar Polri
- File baru: `pages/lhpt.php`

### 3. 🔴 Nomor Sprint Otomatis
- Format: `Sprin / 001 / IV / 2026 / OPS`
- Auto-increment per bulan, reset tiap tahun
- Kolom baru: `nomor_sprint` di `operations`

### 4. 🟠 Ekspedisi Surat Keluar/Masuk
- Penomoran agenda surat operasional BAGOPS
- File baru: `pages/ekspedisi.php`, tabel: `surat_ekspedisi`

### 5. 🟠 Training Management
- Jadwal pelatihan praoperasi per satuan
- Rekap jam latihan per personil
- File baru: `pages/pelatihan.php`

### 6. 🟠 Dashboard Komandan
- Widget role-based: Kapolres lihat ringkasan, operator lihat tugas harian
- Alert LHPT pending, operasi jatuh tempo

### 7. 🟡 WhatsApp Notification
- Notif H-1 piket + Sprint diterbitkan via Fonnte/Wablas

---

## 📁 Struktur File v1.5.0

```
sprin/
├── TODO.md                      ← Roadmap lengkap
├── .windsurf/BAGOPS_ANALYSIS.md ← Analisis tupoksi + saran fitur ⭐
├── pages/
│   ├── main.php                 ← Dashboard + piket hari ini
│   ├── tim_piket.php            ← Papan Siklus + Rotasi Fase
│   ├── jadwal_piket.php         ← Jadwal + Absensi + Cover
│   ├── calendar_dashboard.php   ← Kalender recurrence + tim
│   ├── operasi.php              ← Operasi + Cetak ST
│   ├── laporan_piket.php        ← Rekap absensi ✅
│   └── laporan_operasi.php      ← Laporan operasi ✅
├── api/
│   ├── tim_piket_api.php        ← get_all_tim, cover, rotasi
│   └── calendar_api_public.php  ← schedules + recurrence
└── cron/
    └── migrate_tim_piket.php    ← Migration DB (jalankan 1x)
```

---

## � Info Teknis untuk Developer Baru

| Item | Detail |
|------|--------|
| **Institusi** | BAGOPS Polres Samosir, Sumatera Utara |
| **Stack** | PHP 8.0, MySQL, Bootstrap 5, FullCalendar 6, Chart.js |
| **DB** | host=localhost, name=`bagops`, user=root, pass=root |
| **Path** | `/opt/lampp/htdocs/sprin` |
| **Branch** | `kantor` |
| **Filter Piket** | Unsur id=3+4 + bagian id=20 (SPKT) → 15 satuan |
| **Auth** | Semua API cek `$_SESSION['user_id']` |
| **Migration** | Jalankan `cron/migrate_tim_piket.php` sekali di browser |
| **Offline** | Tidak bergantung internet — XAMPP lokal |

---

*Diupdate: 2026-04-10 — v1.5.0-dev*
