# 🚔 Analisis Tupoksi BAGOPS & Roadmap Pengembangan SPRIN

> **Dokumen ini WAJIB dibaca** oleh siapapun yang akan melanjutkan pengembangan SPRIN,
> agar memahami konteks institusional, tujuan aplikasi, dan arah pengembangan yang tepat.

---

## 1. Apa itu BAGOPS?

**BAGOPS** (Bagian Operasional) adalah unsur pengawas dan pembantu pimpinan
di tingkat **Polres** yang berada langsung di bawah **Kapolres**.

> Referensi: Perpol No. 2 Tahun 2021 tentang Sotk Polres; Perkap No. 9 Tahun 2011.

---

## 2. Tupoksi BAGOPS (Sumber Resmi)

BAGOPS bertugas **merencanakan dan mengendalikan** administrasi operasi kepolisian.

### Fungsi-fungsi utama:

| # | Fungsi | Keterangan |
|---|--------|------------|
| 1 | **Perencanaan Operasi** | Menyusun rencana operasi (Renops), jadwal, dan sasaran |
| 2 | **Pengendalian Operasi** | Memantau pelaksanaan operasi, evaluasi, dan tindak lanjut |
| 3 | **Administrasi Surat Perintah** | Menerbitkan Sprint/ST untuk personil yang ditugaskan |
| 4 | **Pelaporan** | Laporan Hasil Pelaksanaan Tugas (LHPT) setelah operasi selesai |
| 5 | **Pengamanan Kegiatan** | Pamgiat kegiatan masyarakat dan instansi pemerintah |
| 6 | **Pengendalian Pengamanan Markas** | Jadwal piket harian, absensi, shift jaga |
| 7 | **Penyajian Data & Dokumentasi** | Rekap operasi, statistik, arsip kegiatan |
| 8 | **Pelatihan Praoperasi** | Koordinasi jadwal latihan personil sebelum operasi |
| 9 | **Manajemen Siklus Penugasan** | Rotasi personil, tim piket, jadwal berulang |
| 10 | **Ekspedisi Surat** | Penomoran dan pengarsipan surat keluar/masuk |

---

## 3. Hubungan Tupoksi → Fitur Aplikasi SPRIN

### ✅ Sudah Diimplementasi

| Tupoksi BAGOPS | Fitur SPRIN |
|----------------|-------------|
| Pengendalian pengamanan markas | `jadwal_piket.php` — jadwal piket harian + absensi |
| Manajemen siklus penugasan | `tim_piket.php` — papan siklus + rotasi fase |
| Perencanaan operasi | `operasi.php` — CRUD operasi, tingkat, jenis |
| Administrasi Sprint/ST | `operasi.php` → Cetak ST (print-ready) |
| Kalender operasional | `calendar_dashboard.php` — FullCalendar + recurrence |
| Penyajian data | `laporan_operasi.php` — rekap + grafik |
| Rekap absensi personil | `laporan_piket.php` — per bulan + export CSV |
| Manajemen personil | `personil` CRUD — 256 personil aktif |
| Cover/substitusi | `jadwal_piket.php` → modal Ganti Personil |

### ❌ Belum Diimplementasi (Prioritas Pengembangan Selanjutnya)

| Tupoksi BAGOPS | Fitur yang Dibutuhkan | Prioritas |
|----------------|-----------------------|-----------|
| Pelaporan pasca-operasi | **LHPT** — Laporan Hasil Pelaksanaan Tugas | 🔴 Tinggi |
| Administrasi Sprint | **Nomor Sprint otomatis** + penomoran berurut | 🔴 Tinggi |
| Ekspedisi surat | **Manajemen Surat Keluar/Masuk** (no. agenda, tanggal, perihal) | 🟠 Sedang |
| Pelatihan praoperasi | **Training Management** — jadwal latihan per satuan | 🟠 Sedang |
| Keamanan data | **Multi-Level User** — Admin / Operator / Viewer | 🔴 Tinggi |
| Pengendalian operasi | **Dashboard Komandan** — ringkasan operasi aktif real-time | 🟠 Sedang |
| Rencana operasi lengkap | **Renops** — template rencana sebelum operasi | 🟡 Rendah |
| Gelar pasukan digital | **Apel Nominal** — absensi gelar pasukan/apel pagi | 🟡 Rendah |

---

## 4. Saran Pengembangan (Terurut Prioritas)

### 🔴 Prioritas 1 — Fondasi Sistem (Harus Ada)

#### A. Multi-Level User Role
- **Mengapa kritis**: Saat ini siapapun bisa akses semua fitur.
  BAGOPS punya hierarki: Kabagops → Kasi → Operator → Viewer.
- **Role yang dibutuhkan**:
  - `admin` — akses penuh (Kabagops / IT Admin)
  - `operator` — input data, absensi, operasi (Staf BAGOPS)
  - `viewer` — hanya lihat laporan (Kapolres, Wakapolres)
- **Implementasi**: tabel `users` (sudah ada) + middleware guard per halaman

#### B. LHPT — Laporan Hasil Pelaksanaan Tugas
- **Mengapa kritis**: Setiap operasi yang selesai WAJIB ada LHPT.
  Ini dokumen pertanggungjawaban ke atasan.
- **Field**: tanggal, nomor_lhpt, operasi_id (FK), isi_laporan, kendala, hasil, rekomendasi
- **UI**: Form input LHPT di `operasi.php` saat status = `completed`
- **Output**: Print LHPT format standar Polri

#### C. Nomor Sprint Otomatis
- Saat ini nomor ST diisi manual di template cetak.
- Butuh: tabel `sprint_nomor` atau kolom `nomor_sprint` di `operations`
- Format: `Sprin / [urut] / [bulan-romawi] / [tahun] / [jenis]`
- Urutan otomatis per bulan, reset tiap tahun

---

### 🟠 Prioritas 2 — Kelengkapan Operasional

#### D. Manajemen Surat Keluar/Masuk (Ekspedisi)
- **Mengapa penting**: BAGOPS adalah pusat surat-menyurat operasional Polres.
- **Field**: nomor, tanggal, perihal, pengirim/tujuan, kategori (keluar/masuk), status, lampiran
- **Halaman**: `pages/ekspedisi.php`

#### E. Training Management — Pelatihan Praoperasi
- Jadwal latihan per satuan / per tim
- Jenis pelatihan: menembak, bela diri, SAR, lalu lintas, dsb.
- Rekap jam latihan per personil per tahun

#### F. Dashboard Komandan (Real-time)
- Ringkasan: operasi aktif, piket hari ini, personil bertugas
- Alert: jadwal jatuh tempo, LHPT belum dikerjakan
- Akses cepat untuk Kapolres/Wakapolres

---

### 🟡 Prioritas 3 — Fitur Pendukung

#### G. Rencana Operasi (Renops)
- Template rencana sebelum operasi dimulai
- Konversi Renops → Sprint → LHPT (alur kerja terintegrasi)

#### H. Apel Nominal Digital
- Absensi apel pagi/sore via aplikasi
- Berbeda dengan absensi piket (scope lebih luas: semua personil)

#### I. WhatsApp Notification
- Notif H-1 jadwal piket via WA Gateway (Fonnte / Wablas)
- Notif Sprint diterbitkan ke personil ybs

---

## 5. Alur Kerja BAGOPS yang Idealnya Terpadu di SPRIN

```
RENCANA OPERASI (Renops)
        │
        ▼
SURAT PERINTAH (Sprint) ──── nomor otomatis
        │
        ▼
PELAKSANAAN OPERASI ──── kalender_dashboard + operasi.php
        │
        ├── Jadwal Piket ──────── jadwal_piket.php + absensi
        ├── Cover Personil ───── substitusi otomatis
        └── Rekap Real-time ──── laporan_operasi.php
        │
        ▼
LAPORAN HASIL PELAKSANAAN TUGAS (LHPT) ◄── BELUM ADA
        │
        ▼
ARSIP & EKSPEDISI SURAT ◄── BELUM ADA
```

---

## 6. Catatan untuk Developer Berikutnya

### Context Aplikasi
- **Nama**: SPRIN (Sistem Pengelolaan Informasi)
- **Institusi**: BAGOPS Polres Samosir, Sumatera Utara
- **Stack**: PHP 8.0, MySQL (bagops), Bootstrap 5, FullCalendar 6, Chart.js
- **Path**: `/opt/lampp/htdocs/sprin` | DB: `bagops` | Branch: `kantor`

### Prinsip Pengembangan
1. **Ikuti hierarki Polri** — fitur harus mencerminkan alur kerja BAGOPS nyata
2. **Print-ready** — hampir semua dokumen kepolisian butuh dicetak
3. **Offline-first** — XAMPP lokal, tidak boleh bergantung internet saat operasional
4. **Sederhana** — operator bukan IT literate; UI harus sangat intuitif
5. **Audit trail** — semua perubahan data penting harus tercatat (siapa, kapan)

### Yang BELUM Diimplementasi (Butuh Arsitektur Besar)
- **Multi-Level User Role** — auth guard per halaman + tabel roles
- **Training Management** — tabel pelatihan, jadwal, rekap jam latihan
- **LHPT** — tabel + form + print template
- **Ekspedisi Surat** — tabel surat_keluar + surat_masuk + nomor agenda

---

*Dokumen ini dibuat 2026-04-10. Update setiap kali ada penambahan fitur besar.*
