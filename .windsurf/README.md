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
- **Version**: 1.3.0-dev
- **Last Updated**: 2026-04-10
- **Branch**: kantor

## Access URLs
- **Main Application**: http://localhost/sprin
- **PHPMyAdmin**: http://localhost/phpmyadmin
- **Daftar Operasi**: http://localhost/sprin/pages/operasi.php
- **Tim Piket**: http://localhost/sprin/pages/tim_piket.php
- **Calendar**: http://localhost/sprin/pages/calendar_dashboard.php

## Development Notes
- Use the provided credentials when prompted for passwords
- Database operations should use the XAMPP MySQL socket
- Always backup database before major updates
- Migration script: http://localhost/sprin/cron/migrate_tim_piket.php (jalankan sekali saja)

## Key Pages
| URL | Deskripsi |
|-----|-----------|
| `/pages/calendar_dashboard.php` | Kalender jadwal (FullCalendar 6) |
| `/pages/operasi.php` | Daftar & manajemen operasi kepolisian |
| `/pages/tim_piket.php` | Manajemen tim/regu piket per fungsi |
| `/api/calendar_api_public.php` | API jadwal & operasi |
| `/api/tim_piket_api.php` | API tim piket & generate jadwal |
| `/cron/migrate_tim_piket.php` | Migration DB tim piket & recurrence |
