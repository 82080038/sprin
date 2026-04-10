# SPRIN Development Environment Configuration

## Database Credentials
- **MySQL User**: root
- **MySQL Password**: root
- **Database Name**: bagops
- **MySQL Socket**: /opt/lampp/var/mysql/mysql.sock

## System Credentials
- **SUDO Password**: 8208
- **XAMPP Path**: /opt/lampp

## Quick Commands

### Database Operations
```bash
# Connect to MySQL
mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock bagops

# Import SQL file
mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock bagops < file.sql

# Export database
mysqldump -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock bagops > backup.sql
```

### XAMPP Operations
```bash
# Start XAMPP
echo "8208" | sudo -S /opt/lampp/lampp start

# Stop XAMPP
echo "8208" | sudo -S /opt/lampp/lampp stop

# Restart XAMPP
echo "8208" | sudo -S /opt/lampp/lampp restart
```

### File Operations
```bash
# Set proper permissions
echo "8208" | sudo -S chown -R www-data:www-data /opt/lampp/htdocs/sprin
echo "8208" | sudo -S chmod -R 755 /opt/lampp/htdocs/sprin
```

## Project Information
- **Project Name**: SPRIN
- **Description**: Sistem Manajemen Personil & Schedule Management POLRES Samosir
- **Version**: 1.4.1-dev
- **Last Updated**: 2026-04-10
- **Branch**: kantor

## Access URLs
- **Main Application**: http://localhost/sprin/pages/main.php
- **PHPMyAdmin**: http://localhost/phpmyadmin
- **Daftar Operasi**: http://localhost/sprin/pages/operasi.php
- **Tim Piket**: http://localhost/sprin/pages/tim_piket.php
- **Jadwal Piket**: http://localhost/sprin/pages/jadwal_piket.php
- **Calendar**: http://localhost/sprin/pages/calendar_dashboard.php

## Development Notes
- Use the provided credentials when prompted for passwords
- Database operations should use the XAMPP MySQL socket
- Always backup database before major updates
- Migration script: http://localhost/sprin/cron/migrate_tim_piket.php (sudah dijalankan, tabel lengkap)
- Filter bagian piket: Unsur Pelaksana Tugas Pokok (id=3) + Kewilayahan (id=4) + SPKT (id=20)
- TODO list lengkap: `/TODO.md` di root project

## Database Status
| Tabel | Status | Keterangan |
|-------|--------|------------|
| `tim_piket` | ✅ Ada | +fase_siklus_id, jam_mulai_aktif, durasi_jam |
| `tim_piket_anggota` | ✅ Ada | relasi tim ↔ personil |
| `siklus_piket_fase` | ✅ Ada | definisi fase siklus per bagian |
| `piket_absensi` | ✅ Ada | absensi harian, status, jam_hadir — BARU |
| `schedules` | ✅ Ada | +recurrence_type/interval/days/end/parent_id, tim_id |
| `operations` | ✅ Ada | +tingkat_operasi, jenis_operasi, recurrence |

## Key Pages
| URL | Deskripsi |
|-----|-----------|
| `/pages/main.php` | Dashboard + widget Piket Hari Ini |
| `/pages/calendar_dashboard.php` | Kalender jadwal (FullCalendar 6.1.15) |
| `/pages/operasi.php` | Daftar & manajemen operasi kepolisian |
| `/pages/tim_piket.php` | Manajemen tim + papan siklus piket |
| `/pages/jadwal_piket.php` | Jadwal per tim + absensi — BARU |
| `/api/calendar_api_public.php` | API jadwal & operasi |
| `/api/tim_piket_api.php` | API tim piket, absensi, jadwal |
| `/cron/migrate_tim_piket.php` | Migration DB (jalankan 1x saja) |
