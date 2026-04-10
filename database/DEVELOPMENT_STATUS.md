# 🗄️ Database Status — SPRIN v1.4.1-dev

## 📊 Informasi Database

- **Nama**: `bagops`
- **Host**: localhost (XAMPP MySQL)
- **User**: root / root
- **File Backup**: `database/bagops.sql`
- **Records**: 256 personil, 15 satuan piket, 6 unsur, 29 bagian

## ✅ Tabel Aktif

| Tabel | Kolom Penting | Status |
|-------|--------------|--------|
| `personil` | nrp, nama, id_pangkat, id_jabatan, id_bagian | ✅ |
| `bagian` | id, nama_bagian, id_unsur, urutan, is_active | ✅ |
| `unsur` | id, nama_unsur | ✅ |
| `pangkat` | id, nama_pangkat | ✅ |
| `jabatan` | id, nama_jabatan | ✅ |
| `schedules` | shift_date, personil_id, tim_id, recurrence_* | ✅ |
| `operations` | nama_operasi, tingkat, jenis, recurrence_* | ✅ |
| `tim_piket` | nama_tim, id_bagian, fase_siklus_id, jam_mulai_aktif, durasi_jam | ✅ |
| `tim_piket_anggota` | tim_id, personil_id, peran | ✅ |
| `siklus_piket_fase` | id_bagian, nama_fase, urutan, durasi_jam, jam_mulai_default | ✅ |
| `piket_absensi` | schedule_id, personil_id, status, jam_hadir, catatan | ✅ BARU |
| `users` | username, password, role | ✅ |
| `backups` | filename, size | ✅ |

## 🔄 Migration

Jalankan migration jika tabel belum ada:
```
http://localhost/sprin/cron/migrate_tim_piket.php
```

File migration otomatis membuat semua tabel di atas.

## 📝 Catatan

- Data adalah **sample development** — bukan data produksi
- Selalu backup sebelum perubahan besar: `mysqldump -u root -proot bagops > backup.sql`
