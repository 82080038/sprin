---
description: Common SPRIN development tasks and commands
---

# SPRIN Development Workflow

## Quick Commands

### Database Operations

**View Database Status:**
```bash
/opt/lampp/bin/mysql -u root -proot bagops -e "SHOW TABLES;"
```

**Reset Database:**
```bash
/opt/lampp/bin/mysql -u root -proot -e "DROP DATABASE IF EXISTS bagops; CREATE DATABASE bagops CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
/opt/lampp/bin/mysql -u root -proot bagops < /opt/lampp/htdocs/sprint/database/bagops.sql
```

**Export Database:**
```bash
/opt/lampp/bin/mysqldump -u root -proot bagops > /opt/lampp/htdocs/sprint/database/bagops_backup_$(date +%Y%m%d).sql
```

### XAMPP Control

**Start:** `sudo /opt/lampp/lampp start`
**Stop:** `sudo /opt/lampp/lampp stop`
**Restart:** `sudo /opt/lampp/lampp restart`
**Status:** `sudo /opt/lampp/lampp status`

## Application URLs

| Page | URL | Status |
|------|-----|--------|
| Landing | http://localhost/sprint | ✅ Active |
| Login | http://localhost/sprint/login.php | ✅ Active |
| Dashboard | http://localhost/sprint/pages/main.php | ✅ Active |
| Personil | http://localhost/sprint/pages/personil.php | ✅ Enhanced |
| Bagian | http://localhost/sprint/pages/bagian.php | ✅ Auto-Type |
| Unsur | http://localhost/sprint/pages/unsur.php | ✅ Auto-Order |
| Jabatan | http://localhost/sprint/pages/jabatan.php | ✅ Card-Based |
| Calendar | http://localhost/sprint/pages/calendar_dashboard.php | ✅ Active |
| User Management | http://localhost/sprint/pages/user_management.php | ✅ Enhanced |
| Backup Management | http://localhost/sprint/pages/backup_management.php | ✅ Active |

## API Endpoints

| Endpoint | Method | Description | Status |
|----------|--------|-------------|--------|
| /api/personil_list.php | GET | List dengan pagination | ✅ Enhanced |
| /api/personil_crud.php | POST | Create/Update/Delete | ✅ Optimized |
| /api/personil_detail.php | GET | Get detail by ID | ✅ Active |
| /api/calendar_api.php | GET/POST | Calendar operations | ✅ Enhanced |
| /api/unsur_stats.php | GET | Statistics per unsur | ✅ Active |
| /api/advanced_search.php | POST | Advanced search dengan filters | ✅ Active |
| /api/export_personil.php | GET | Export PDF/Excel/CSV | ✅ Enhanced |
| /api/jabatan_crud.php | POST | Jabatan management | ✅ New |
| /api/user_management.php | POST | User management | ✅ Enhanced |
| /api/backup_api.php | POST/GET | Backup operations | ✅ New |
| /api/bulk_update_personil.php | POST | Bulk operations | ✅ New |
| /api/report_api.php | GET/POST | Reporting system | ✅ New |
| /api/unsur_terminal.php | GET | Unsur terminal view | ✅ New |

## System Architecture

### Current Version: SPRIN v1.2.0

### Core Components
```
/opt/lampp/htdocs/sprint/
├── api/           # 21 API endpoints
├── core/          # 25 core classes & config
├── database/      # SQL schema & migrations
├── pages/         # 10+ application pages
├── includes/      # UI components
├── public/        # CSS, JS, assets
├── docs/          # Documentation
├── logs/          # Application logs
├── cron/          # Scheduled tasks
└── .windsurf/     # AI context & workflows
```

### Database Schema
- **Primary Database**: bagops (MySQL)
- **Character Set**: utf8mb4_general_ci
- **Key Tables**: personil, bagian, unsur, jabatan, users
- **Features**: Soft delete, audit trail, relationships

### Frontend Stack
- **Framework**: Bootstrap 5.3.0
- **Icons**: Font Awesome 6.4.2
- **Custom CSS**: Responsive design, modal overrides
- **JavaScript**: Vanilla JS with AJAX

### Backend Stack
- **Language**: PHP 8.x
- **Database**: MySQL/MariaDB
- **Architecture**: MVC pattern with API layer
- **Security**: Session management, prepared statements

## Recent Enhancements (v1.2.0)

### ✅ UI/UX Improvements
- **Modal Consistency**: Standardized sizes (sm/md/lg)
- **Responsive Design**: Mobile-first approach
- **Card-Based Layout**: Jabatan management restructure
- **Auto-Generation**: Kode uns ur, bagian types, ordering

### ✅ Backend Enhancements
- **API Expansion**: 21 endpoints total
- **Error Handling**: Comprehensive validation
- **Performance**: Optimized queries with COALESCE
- **Security**: Enhanced session management

### ✅ New Features
- **Drag & Drop**: Unsur ordering
- **Bulk Operations**: Personil management
- **Export System**: PDF/Excel/CSV with filters
- **Calendar Integration**: Google Calendar sync
- **Backup System**: Automated database backups

## Development Tasks

### Create New API Endpoint

1. Create file di `api/{name}_crud.php`
2. Follow template dari `.windsurf/skills/crud_api_generator.md`
3. Test dengan curl atau browser
4. Update dokumentasi API

### Add New Database Table

1. Create migration file: `database/migrations/YYYY_MM_DD_description.sql`
2. Run: `.windsurf/skills/database_migration.md`
3. Create model class di `core/` jika perlu
4. Create API endpoints

### Debug Issues

1. Check error logs: `tail -f /opt/lampp/logs/error_log`
2. Enable debug mode di `core/config.php`
3. Gunakan `.windsurf/skills/debug_troubleshoot.md`
4. Test isolasi dengan minimal test case

## Git Workflow

**Sync dengan GitHub (reset hard):**
```bash
cd /opt/lampp/htdocs/sprint
git fetch origin
git reset --hard origin/master
git clean -fd
```

**Lihat status:**
```bash
git status
git log --oneline -5
```

## Testing

### Test API dengan curl

```bash
# Get personil list (enhanced with filters)
curl "http://localhost/sprint/api/personil_list.php?page=1&per_page=5&bagian_id=2"

# Create personil (with validation)
curl -X POST http://localhost/sprint/api/personil_crud.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=create&nrk=TEST123&nama_lengkap=Test&jenis_pegawai=polri&bagian_id=2"

# Export PDF (with filters)
curl "http://localhost/sprint/api/export_personil.php?format=pdf&bagian_id=2" --output test.pdf

# Test new jabatan API
curl -X POST http://localhost/sprint/api/jabatan_crud.php \
  -d "action=create&nama_jabatan=Kasat&bagian_id=2&unsur_id=3"

# Bulk update personil
curl -X POST http://localhost/sprint/api/bulk_update_personil.php \
  -H "Content-Type: application/json" \
  -d '{"action":"update","data":[{"id":1,"bagian_id":3}]}'
```

### Check PHP Syntax

```bash
# Single file
php -l /opt/lampp/htdocs/sprint/api/personil_crud.php

# All files
find /opt/lampp/htdocs/sprint -name "*.php" -exec php -l {} \;
```

## Common Tasks

### Add New Personil

1. Login: http://localhost/sprint/login.php (bagops/admin123)
2. Menu Personil → Tambah Personil
3. Isi form: NRK, NRP, Nama, Pangkat, Bagian, dll
4. Simpan

### Generate Jadwal Piket

1. Buka Calendar Dashboard
2. Pilih rentang tanggal
3. Klik "Generate Jadwal Otomatis"
4. Pilih bagian/unsur yang akan dijadwalkan
5. Konfirmasi generate

### Export Data

1. Buka halaman Personil
2. Filter data yang diinginkan
3. Klik tombol Export
4. Pilih format (Excel/PDF/CSV)
5. Download file

## Code Standards

Lihat `.windsurf/rules/php_coding_standards.md` untuk detail.

**Quick Reference:**
- Classes: PascalCase
- Methods/Variables: camelCase
- Constants: UPPER_CASE
- Always use prepared statements
- Soft delete pattern untuk data deletion
- Docblocks untuk classes dan methods

---

## System Information

**Report Updated**: 2026-04-01
**System Version**: SPRIN v1.2.0
**Architecture**: PHP 8.x + MySQL + Bootstrap 5
**Environment**: Development (DEBUG_MODE: ON)
**Database**: bagops (utf8mb4_general_ci)
**Total APIs**: 21 endpoints
**Core Classes**: 25 files
**Application Pages**: 10+ pages
