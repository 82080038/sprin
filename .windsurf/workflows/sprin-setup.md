---
description: Setup and configure SPRIN application environment
---

# SPRIN Setup Workflow

Workflow untuk setup dan konfigurasi aplikasi SPRIN (POLRES Samosir Personil Management System).

## Prerequisites
- XAMPP terinstall di `/opt/lampp`
- Repository SPRIN di `/opt/lampp/htdocs/sprint`

## Setup Steps

1. **Sync Repository**
   ```bash
   cd /opt/lampp/htdocs/sprint
   git fetch origin
   git reset --hard origin/master
   git clean -fd
   ```

2. **Start XAMPP Services**
   ```bash
   sudo /opt/lampp/lampp start
   ```

3. **Setup Database**
   // turbo
   ```bash
   /opt/lampp/bin/mysql -u root -proot -e "DROP DATABASE IF EXISTS bagops; CREATE DATABASE bagops CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
   /opt/lampp/bin/mysql -u root -proot bagops < /opt/lampp/htdocs/sprint/database/bagops.sql
   ```

4. **Verify Database**
   // turbo
   ```bash
   /opt/lampp/bin/mysql -u root -proot bagops -e "SHOW TABLES; SELECT COUNT(*) as total_personil FROM personil;"
   ```

5. **Access Application**
   - URL: http://localhost/sprint
   - Login: http://localhost/sprint/login.php
   - Username: `bagops`
   - Password: `admin123`

## Database Credentials
- Host: localhost
- Database: bagops
- User: root
- Password: root

## Troubleshooting
- Jika MySQL tidak start: `sudo /opt/lampp/lampp stop` kemudian `sudo /opt/lampp/lampp start`
- Jika permission error: `sudo chmod -R 755 /opt/lampp/htdocs/sprint`
- Jika database error: Ulangi step 3 setup database
