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

| Page | URL |
|------|-----|
| Landing | http://localhost/sprint |
| Login | http://localhost/sprint/login.php |
| Dashboard | http://localhost/sprint/pages/main.php |
| Personil | http://localhost/sprint/pages/personil.php |
| Bagian | http://localhost/sprint/pages/bagian.php |
| Unsur | http://localhost/sprint/pages/unsur.php |
| Jabatan | http://localhost/sprint/pages/jabatan.php |
| Calendar | http://localhost/sprint/pages/calendar_dashboard.php |

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| /api/personil_list.php | GET | List dengan pagination |
| /api/personil_crud.php | POST | Create/Update/Delete |
| /api/personil_detail.php | GET | Get detail by ID |
| /api/calendar_api.php | GET/POST | Calendar operations |
| /api/unsur_stats.php | GET | Statistics per unsur |
| /api/advanced_search.php | POST | Advanced search dengan filters |
| /api/export_personil.php | GET | Export PDF/Excel/CSV |

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
# Get personil list
curl "http://localhost/sprint/api/personil_list.php?page=1&per_page=5"

# Create personil
curl -X POST http://localhost/sprint/api/personil_crud.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=create&nrk=TEST123&nama_lengkap=Test&jenis_pegawai=polri"

# Export PDF
curl "http://localhost/sprint/api/export_personil.php?format=pdf" --output test.pdf
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
