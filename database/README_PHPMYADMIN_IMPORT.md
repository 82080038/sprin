# Cara Update Database Menggunakan phpMyAdmin

## ⚠️ IMPORTANT: DEVELOPMENT DATABASE
**This database is for development and testing purposes only. NOT for production use.**

## File SQL yang Tersedia

### 📄 Main Database
📁 `bagops.sql` - Database lengkap dengan sample data (175 KB)
- **Purpose**: Development testing
- **Data**: Sample POLRES Samosir data
- **Records**: 256 personil, 98 jabatan, 57 pangkat, 29 bagian, 6 unsur
- **Status**: Development data only

### 📄 Migration Files
📁 `database/migrations/` - Database structure updates
- `create_users_table.sql` - Multi-user system (testing)
- `create_backup_tables.sql` - Backup management (testing)
- `add_urutan_to_bagian.sql` - Ordering system (development)

## 🗃️ File SQL yang Dibuat
📁 `database/update_bagops_phpmyadmin.sql`

File ini berisi semua tabel baru yang perlu ditambahkan:
- **users** - Tabel user management (development)
- **user_sessions** - Sesi login user (testing)
- **user_activity_log** - Log aktivitas user (development)
- **password_reset_tokens** - Token reset password (testing)
- **backups** - Data backup database
- **backup_schedule** - Jadwal backup otomatis

---

## Langkah Import di phpMyAdmin

### 1. Buka phpMyAdmin
- Akses: http://localhost/phpmyadmin
- Login dengan user: `root` (tanpa password default XAMPP)

### 2. Pilih Database
- Klik database **`bagops`** di sidebar kiri
- Pastikan database sudah terpilih (warna biru)

### 3. Import File SQL
1. Klik tab **"Import"** di menu atas
2. Klik tombol **"Choose File"**
3. Pilih file: `database/update_bagops_phpmyadmin.sql`
4. Pastikan format adalah **SQL**
5. Biarkan setting lain default
6. Klik tombol **"Go"** (di bagian bawah)

### 4. Verifikasi
- Jika berhasil, akan muncul pesan hijau: "Import has been successfully finished"
- Di sidebar kiri, pastikan tabel baru muncul:
  - `users` ✅
  - `user_sessions` ✅
  - `user_activity_log` ✅
  - `password_reset_tokens` ✅
  - `backups` ✅
  - `backup_schedule` ✅

---

## Cek Hasil Import

### Via phpMyAdmin
1. Klik tabel `users` di sidebar
2. Klik tab **"Structure"**
3. Pastikan kolom sesuai:
   - id, username, password_hash, email, full_name, role, is_active, dll.

### Via Aplikasi
1. Buka aplikasi: http://localhost/sprint
2. Login dengan: **bagops / admin123**
3. Cek menu baru:
   - **Pengaturan > Manajemen User**
   - **Pengaturan > Manajemen Backup**
   - **Laporan**

---

## Jika Import Gagal

### Error: "Table already exists"
- Artinya: Tabel sudah dibuat sebelumnya
- Solusi: Tidak masalah, lanjutkan saja

### Error: "Cannot connect to database"
- Pastikan MySQL/XAMPP sudah running
- Cek XAMPP Control Panel > MySQL status = Running

### Error: "Access denied"
- Pastikan login phpMyAdmin dengan user `root`
- Atau user yang punya privilege ke database `bagops`

---

## Alternatif: Import via Command Line

Jika phpMyAdmin bermasalah, bisa gunakan terminal:

```bash
cd /opt/lampp/htdocs/sprint
cat database/update_bagops_phpmyadmin.sql | /opt/lampp/bin/mysql -u root bagops
```

---

## Setelah Import Berhasil

### 1. Buat Backup Directory
```bash
mkdir -p /opt/lampp/htdocs/sprint/backups
chmod 755 /opt/lampp/htdocs/sprint/backups
```

### 2. Setup Cron Job (Opsional)
Untuk backup otomatis, edit crontab:
```bash
crontab -e
```

Tambahkan baris:
```
* * * * * /opt/lampp/bin/php /opt/lampp/htdocs/sprint/cron/backup_cron.php >> /opt/lampp/htdocs/sprint/logs/backup_cron.log 2>&1
```

### 3. Test Fitur Baru
1. Login ke aplikasi
2. Buka **Manajemen User** → Tambah user baru
3. Buka **Manajemen Backup** → Buat backup
4. Buka **Laporan** → Generate laporan

---

## Butuh Bantuan?

Jika mengalami masalah:
1. Cek error log: `logs/error.log`
2. Jalankan check database: `php cron/check_database.php`
3. Jalankan integration check: `php cron/check_integration.php`
