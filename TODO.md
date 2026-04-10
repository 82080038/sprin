# TODO — SPRIN Development Roadmap
**Diperbarui**: 2026-04-10 | **Branch**: kantor | **Versi**: 1.7.0-dev

> **Konteks Aplikasi**: SPRIN adalah sistem informasi operasional untuk **BAGOPS Polres Samosir**.
> BAGOPS (Bagian Operasional) bertugas merencanakan & mengendalikan operasi kepolisian,
> administrasi surat perintah, laporan pelaksanaan tugas, manajemen penugasan, dan pengamanan markas.
> Lihat analisis lengkap: `.windsurf/BAGOPS_ANALYSIS.md`

---

## ✅ SELESAI

### v1.3.x
- [x] Modul Operasi Kepolisian — CRUD lengkap + auto-detect status
- [x] Kalender FullCalendar v6.1.15 — view Tahun/Bulan/Minggu/Hari/Agenda
- [x] Tim Piket — CRUD tim + anggota + generate jadwal berulang
- [x] DB recurrence di `schedules` & `operations`

### v1.4.0-dev (sesi terakhir)
- [x] DB: tabel `siklus_piket_fase`, kolom `fase_siklus_id` + `jam_mulai_aktif` + `durasi_jam` di `tim_piket`
- [x] Filter cerdas bagian piket: Unsur 3+4 + SPKT (id=20)
- [x] Form Tambah Tim baru: Unsur→Bagian→Nama→Jenis→Fase→Jam+Durasi
- [x] Papan Siklus Kanban per satuan + Drag & Drop geser fase
- [x] Modal Atur Siklus: fase, durasi, jam, mode Auto/Manual
- [x] API: `get_siklus`, `save_siklus`, `geser_fase`
- [x] `piket.md` workflow ditulis lengkap
- [x] Update semua MD dokumentasi

---

## 🔴 FASE 1 — Segera (Langsung Terasa Manfaatnya)

- [x] **Dashboard Piket Hari Ini** — widget di `index.php` atau `pages/dashboard_piket.php`
  - Tabel per satuan: Nama | Pangkat | Fase | Jam Mulai | Jam Selesai | Status
  - Sumber: `JOIN schedules + personil WHERE shift_date = CURDATE()`
  - Estimasi: 1 hari

- [x] **Halaman Jadwal Piket per Tim** — `pages/jadwal_piket.php`
  - Pilih tim → tabel jadwal bulan/tahun
  - Filter: bulan, tahun, shift
  - Tombol Hapus Series + Cetak/Print
  - Estimasi: 1 hari

- [x] **Kalender — Modal Jadwal + Tim Piket**
  - Tab "Dari Tim Piket": dropdown tim → auto-fill anggota
  - Section Pengulangan: type + interval + hari + tanggal akhir
  - Kirim `tim_id`, `recurrence_*` ke API

- [x] **Kalender — Badge Event Berulang**
  - Icon 🔁 pada event berulang
  - Warna berbeda: tim piket (biru tua) vs jadwal manual

---

- [x] **Recurrence di Modal Operasi** — `pages/operasi.php`
  - Section Pengulangan di modal Tambah & Edit
  - Preview badge dinamis

---

## 🟠 FASE 2 — Piket Jadi Sistem

- [x] **Absensi & Konfirmasi Kehadiran** — tabel `piket_absensi`
  - DB: schedule_id, personil_id, status (hadir/tidak_hadir/sakit/ijin/terlambat), jam_hadir, catatan
  - UI: per shift, centang kehadiran per personil
  - Rekap per bulan
  - Estimasi: 2-3 hari

- [x] **Cover Management — Substitusi Personil**
  - Jika personil absen → tampilkan pengganti dari satuan yang sama
  - Update schedule dengan personil pengganti
  - Log: siapa menggantikan siapa
  - Estimasi: 2 hari

- [x] **Recurrence di Modal Operasi** — `pages/operasi.php`
  - Section "Pengulangan" di modal Tambah & Edit
  - Preview badge dinamis + fill saat edit

---

## 🟡 FASE 3 — Laporan & Akuntabilitas

- [x] **Rekap Absensi Piket** — `pages/laporan_piket.php`
  - Rekap per personil per bulan: hadir/absen/ijin/sakit
  - Rekap per satuan: persentase kehadiran
  - Export Excel/PDF
  - Estimasi: 2 hari

- [x] **Laporan Operasi** — `pages/laporan_operasi.php`
  - Rekap per bulan/tahun + grafik
  - Total dukgra per jenis operasi
  - Export Excel/PDF
  - Estimasi: 2 hari

- [x] **Cetak Surat Perintah Tugas (ST)**
  - Generate dokumen ST dari data tim + jadwal + operasi
  - Format standar Polri
  - Estimasi: 3 hari

---

## 🟢 FASE 4 — Sistem Cerdas (Jangka Panjang)

- [x] **Deteksi Konflik Jadwal**
  - Warning: personil dijadwalkan 2 tempat di hari sama
  - Warning: tim kekurangan anggota minimum
  - Warning: personil belum giliran terlalu lama (fairness)

- [x] **Rotasi Shift Otomatis**
  - Tim ROTASI berganti fase siklus otomatis tiap X hari
  - Tampilkan shift aktif di papan siklus

- [x] **Notifikasi In-App**
  - Badge navbar jumlah piket hari ini

- [x] **Multi-Level User Role** 🔴 PRIORITAS TINGGI
  - Role: `admin` (Kabagops/IT) / `operator` (Staf input) / `viewer` (Kapolres/Waka)
  - `AuthHelper::requireRole()`, `canEdit()`, `canDelete()`, `isAdmin()`
  - Guard diterapkan di `user_management.php`, `pengaturan.php`
  - Badge role di navbar, `SPRIN_USER_ROLE` JS variable
  - User sampel: bagops/admin, operator/operator123, viewer/viewer123

- [x] **Training Management** — Pelatihan Praoperasi 🟠
  - CRUD: `pages/pelatihan.php` + `api/pelatihan_api.php`
  - 6 jenis: menembak, bela diri, SAR, ketahanan, teknis, lainnya
  - Stat cards + filter + role-based aksi

---

## � FASE 5 — Tupoksi BAGOPS yang Belum Ada (Berdasarkan Analisis)

> Lihat analisis lengkap: `.windsurf/BAGOPS_ANALYSIS.md`

- [x] **LHPT — Laporan Hasil Pelaksanaan Tugas** 🔴 KRITIS
  - CRUD lengkap: `pages/lhpt.php` + `api/lhpt_api.php`
  - Nomor LHPT auto-generate: `LHPT / [urut] / [bulan-romawi] / [tahun] / OPS`
  - Print format standar Polri (window.open)
  - Tabel: `lhpt` (FK ke operations)
  - Navigasi: di menu Laporan

- [x] **Nomor Sprint Otomatis** 🔴 KRITIS
  - Format: `Sprin / [urut] / [bulan-romawi] / [tahun] / OPS`
  - Urutan otomatis per tahun, auto-generate saat create
  - Kolom: `nomor_sprint` di tabel `operations`
  - Tampil di tabel, detail modal, dan Cetak ST

- [x] **Ekspedisi Surat Keluar/Masuk** 🟠
  - CRUD lengkap: `pages/ekspedisi.php` + `api/ekspedisi_api.php`
  - Nomor agenda auto: `SM/0001/2026` (masuk), `SK/0001/2026` (keluar)
  - Field: nomor, tanggal, perihal, pengirim/tujuan, kategori, status, disposisi
  - Tabel: `surat_ekspedisi`
  - Role-based UI: edit hanya admin+operator, hapus hanya admin

- [x] **Dashboard Komandan (Real-time)** 🟠
  - Greeting role-based + 4 summary cards (ops aktif, rencana, LHPT draft, surat diproses)
  - Piket hari ini widget + stats personil (8 cards)
  - Sidebar: quick actions, rekap operasional, info sistem (admin)
  - Target pengguna: semua role dengan widget yang sesuai

- [ ] **WhatsApp Notification** 🟡
  - Notif H-1 jadwal piket via WA Gateway (Fonnte/Wablas API)
  - Notif saat Sprint diterbitkan ke personil ybs
  - Butuh: API key WA Gateway + konfigurasi nomor HP di data personil
  - Estimasi: 2 hari

- [x] **Apel Nominal Digital** 🟡
  - CRUD: `pages/apel_nominal.php` + `api/apel_api.php`
  - Input absensi pagi/sore per unsur/bagian
  - 7 status: hadir, tidak hadir, sakit, ijin, cuti, dinas luar, tugas belajar
  - Rekap bulanan per personil + persentase kehadiran
  - Tabel: `apel_nominal` (UNIQUE per tanggal+jenis+personil)

---

## 📁 File Utama (v1.5.0)

| File | Fungsi | Status |
|------|--------|--------|
| `pages/main.php` | Dashboard + widget piket hari ini | ✅ |
| `pages/tim_piket.php` | Papan Siklus + Rotasi Fase | ✅ |
| `pages/jadwal_piket.php` | Jadwal tim + Absensi + Cover | ✅ |
| `pages/calendar_dashboard.php` | Kalender + recurrence + tim picker | ✅ |
| `pages/operasi.php` | Operasi + Cetak ST | ✅ |
| `pages/laporan_piket.php` | Rekap absensi per bulan | ✅ |
| `pages/laporan_operasi.php` | Laporan operasi + grafik + CSV | ✅ |
| `pages/lhpt.php` | LHPT pasca operasi | ✅ |
| `pages/ekspedisi.php` | Surat keluar/masuk | ✅ |
| `pages/pelatihan.php` | Training Management | ✅ |
| `pages/apel_nominal.php` | Apel Nominal Digital | ✅ |
| `api/tim_piket_api.php` | get_all_tim, cover, rotasi | ✅ |
| `api/calendar_api_public.php` | schedules + recurrence + konflik | ✅ |

---

## 🗃️ Status Database

| Tabel | Status | Keterangan |
|-------|--------|------------|
| `personil` | ✅ | 256 record aktif |
| `tim_piket` | ✅ | 15 kolom (fase, jam, durasi) |
| `tim_piket_anggota` | ✅ | |
| `siklus_piket_fase` | ✅ | Definisi fase per bagian |
| `piket_absensi` | ✅ | Absensi harian + cover |
| `schedules` | ✅ | + recurrence + tim_id |
| `operations` | ✅ | + tingkat/jenis + recurrence |
| `lhpt` | ✅ | LHPT — FK ke operations |
| `surat_ekspedisi` | ✅ | Surat masuk/keluar + agenda otomatis |
| `pelatihan` | ✅ | 6 jenis + stats |
| `apel_nominal` | ✅ | Absensi apel pagi/sore |
| `users` / `user_roles` | ✅ | 3 role: admin/operator/viewer + guard middleware |

---

*Update file ini setiap selesai mengerjakan item.*
