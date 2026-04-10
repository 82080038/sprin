# TODO — SPRIN v1.4.x Development
**Diperbarui**: 2026-04-10 | **Branch**: kantor | **Versi**: 1.4.0-dev

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

- [ ] **Multi-Level User**
  - Role: Admin / Operator (input+absensi) / Viewer
  - Guard akses per role

- [ ] **Training Management**
  - Jadwal pelatihan per satuan
  - Rekap pelatihan per personil

---

## 📁 File Utama

| File | Fungsi |
|------|--------|
| `pages/tim_piket.php` | Tim + Papan Siklus Piket |
| `pages/calendar_dashboard.php` | Kalender FullCalendar 6.1.15 |
| `pages/operasi.php` | Daftar & manajemen operasi |
| `pages/jadwal_piket.php` | *(TODO)* Jadwal per tim |
| `pages/laporan_piket.php` | Rekap absensi piket (DONE) |
| `api/tim_piket_api.php` | API tim, siklus, generate jadwal |
| `api/calendar_api_public.php` | API jadwal & operasi |
| `cron/migrate_tim_piket.php` | Migration DB (sudah dijalankan) |

---

## 🗃️ Status Database

| Tabel | Status |
|-------|--------|
| `tim_piket` | ✅ Lengkap (15 kolom) |
| `tim_piket_anggota` | ✅ Lengkap |
| `siklus_piket_fase` | ✅ Lengkap |
| `schedules` | ✅ + recurrence + tim_id |
| `operations` | ✅ + tingkat/jenis + recurrence |
| `piket_absensi` | ⏳ TODO — Fase 2 |

---

*Update file ini setiap selesai mengerjakan item.*
