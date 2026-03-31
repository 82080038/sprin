---
description: Setup dan konfigurasi aplikasi SPRIN
---

# SPRIN Setup Workflow

Workflow untuk setup dan konfigurasi aplikasi SPRIN (POLRES Samosir Personil Management System).

## Prerequisites

- XAMPP terinstall di `/opt/lampp`
- Repository SPRIN di `/opt/lampp/htdocs/sprint`
- MySQL password: `root`

## Setup Steps

### 1. Sync Repository
```bash
cd /opt/lampp/htdocs/sprint
git fetch origin
git reset --hard origin/master
git clean -fd
```

### 2. Start XAMPP Services
```bash
sudo /opt/lampp/lampp start
```

### 3. Setup Database
// turbo
```bash
/opt/lampp/bin/mysql -u root -proot -e "DROP DATABASE IF EXISTS bagops; CREATE DATABASE bagops CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
/opt/lampp/bin/mysql -u root -proot bagops < /opt/lampp/htdocs/sprint/database/bagops.sql
```

### 4. Verify Database
// turbo
```bash
/opt/lampp/bin/mysql -u root -proot bagops -e "SHOW TABLES; SELECT COUNT(*) as total_personil FROM personil;"
```

### 5. Access Application
- URL: http://localhost/sprint
- Login: http://localhost/sprint/login.php
- Username: `bagops`
- Password: `admin123`

## Database Credentials

- Host: localhost
- Database: bagops
- User: root
- Password: root

## File Structure

```
/opt/lampp/htdocs/sprint/
├── api/           # API endpoints (17 files)
├── core/          # Core classes & config
├── database/      # SQL files
├── pages/         # Application pages
├── includes/      # Components (header, footer)
├── public/        # Assets (CSS, JS)
├── docs/          # Documentation
└── .windsurf/     # AI context & workflows
```

## Troubleshooting

### MySQL tidak start
```bash
sudo /opt/lampp/lampp stop
sudo /opt/lampp/lampp start
```

### Permission error
```bash
sudo chmod -R 755 /opt/lampp/htdocs/sprint
sudo chown -R daemon:daemon /opt/lampp/htdocs/sprint
```

### Database error
Ulangi step 3 setup database

### Port conflict
```bash
# Check port 80
sudo netstat -tlnp | grep :80
# Kill process if needed
sudo kill -9 <PID>
```

## Post-Setup Verification

### 1. Check All Services
```bash
sudo /opt/lampp/lampp status
```

### 2. Test Database Connection
```bash
/opt/lampp/bin/mysql -u root -proot bagops -e "SELECT 1"
```

### 3. Test API
```bash
curl http://localhost/sprint/api/personil_list.php?page=1&per_page=1
```

### 4. Check Logs
```bash
# Apache logs
tail -20 /opt/lampp/logs/error_log

# PHP logs
tail -20 /opt/lampp/htdocs/sprint/logs/error.log
```

## Development Notes

- DEBUG_MODE aktif di development
- Error reporting: E_ALL
- Session lifetime: 3600 detik
- Base URL: http://localhost/sprint

## Next Steps

1. Buka browser: http://localhost/sprint
2. Login dengan credentials default
3. Explore menu dan fitur
4. Lihat `.windsurf/workflows/sprin-dev.md` untuk development tasks
