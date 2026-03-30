---
description: Common SPRIN development tasks and commands
---

# SPRIN Development Workflow

## Database Operations

### View Database Status
```bash
/opt/lampp/bin/mysql -u root -proot bagops -e "SHOW TABLES;"
```

### Reset Database
```bash
/opt/lampp/bin/mysql -u root -proot -e "DROP DATABASE IF EXISTS bagops; CREATE DATABASE bagops CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
/opt/lampp/bin/mysql -u root -proot bagops < /opt/lampp/htdocs/sprint/database/bagops.sql
```

### Export Database
```bash
/opt/lampp/bin/mysqldump -u root -proot bagops > /opt/lampp/htdocs/sprint/database/bagops_backup_$(date +%Y%m%d).sql
```

## XAMPP Control

### Start XAMPP
```bash
sudo /opt/lampp/lampp start
```

### Stop XAMPP
```bash
sudo /opt/lampp/lampp stop
```

### Restart XAMPP
```bash
sudo /opt/lampp/lampp restart
```

## Application URLs

- **Landing Page**: http://localhost/sprint
- **Login**: http://localhost/sprint/login.php
- **Dashboard**: http://localhost/sprint/pages/main.php
- **Personil**: http://localhost/sprint/pages/personil.php
- **Bagian**: http://localhost/sprint/pages/bagian.php
- **Unsur**: http://localhost/sprint/pages/unsur.php
- **Calendar**: http://localhost/sprint/pages/calendar_dashboard.php

## API Endpoints

- `GET/POST /api/personil_api.php` - CRUD Personil
- `GET/POST /api/calendar_api.php` - Calendar Operations
- `GET /api/unsur_stats.php` - Statistics
- `GET /api/search_personil.php` - Search Personil

## Common Tasks

### Add New Personil
1. Login ke aplikasi
2. Navigasi ke menu Personil
3. Klik tombol "Tambah Personil"
4. Isi form dengan data lengkap
5. Simpan

### Generate Jadwal Piket
1. Buka Calendar Dashboard
2. Pilih rentang tanggal
3. Klik "Generate Jadwal Otomatis"
4. Pilih bagian/unsur yang akan dijadwalkan
5. Konfirmasi generate

### Export Data Personil
1. Buka halaman Personil
2. Klik tombol "Export"
3. Pilih format (Excel/PDF)
4. Download file
