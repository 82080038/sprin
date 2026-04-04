---
description: Overview aplikasi SPRIN (Sistem Manajemen Personil POLRES Samosir)
---

# SPRIN - Sistem Manajemen Personil POLRES Samosir

## Informasi Umum

- **Nama Aplikasi**: POLRES Samosir Management System (SPRIN)
- **Versi**: 1.0.0
- **Environment**: Development (DEBUG_MODE aktif)
- **Base URL**: http://localhost/sprint
- **Database**: bagops (MySQL/MariaDB)

## Arsitektur Aplikasi

Aplikasi menggunakan arsitektur MVC sederhana dengan komponen:

### Core (core/)
- **config.php** - Konfigurasi aplikasi, database, security, session settings
- **Database.php** - Singleton pattern untuk koneksi PDO dengan TCP/socket fallback
- **Model.php** - Base model class dengan CRUD operations
- **Controller.php** - Base controller dengan response helpers
- **auth_helper.php** - Authentication & authorization utilities dengan Argon2id hashing
- **auth_check.php** - Standardized authentication check menggunakan AuthHelper
- **error_handler.php** - Global error handling dengan environment-based display

### API (api/)
17 endpoint API dengan standardized response format:
- `personil_crud.php`, `personil_list.php`, `personil_detail.php`, `personil_simple.php`
- `unsur_stats.php` - Statistik distribusi personil dengan standardized format
- `calendar_api.php`, `google_calendar_api.php`
- `simple.php` - Bagian data API dengan standardized format
- `export_personil.php`, `advanced_search.php`
- `jabatan_crud.php`

**Standardized API Response Format:**
```json
{
  "success": true|false,
  "message": "string",
  "data": object|array,
  "timestamp": "ISO8601 datetime"
}
```

### Pages (pages/)
- `main.php` - Dashboard dengan statistik real-time
- `personil.php` - Manajemen data personil lengkap
- `bagian.php` - Manajemen bagian/unit
- `jabatan.php` - Manajemen jabatan
- `unsur.php` - Manajemen unsur (PIMPINAN, BAG, SAT, POLSEK, SPKT, BKO)
- `calendar_dashboard.php` - Kalender jadwal dengan Google Calendar integration

### Database (database/)
- `bagops.sql` - Full database schema dengan sample data

## Autentikasi

- **Login URL**: /login.php
- **Default Username**: bagops
- **Default Password**: admin123
- **Hashing**: Argon2id dengan memory_cost 65536
- **Session Management**: AuthHelper::validateSession() dengan 3600 detik (1 jam) lifetime
- **Session Security**:
  - `session.cookie_httponly = 1`
  - `session.cookie_samesite = 'Lax'`
  - `session.use_strict_mode = 1`
  - `session.cookie_secure = 0` (set to 1 untuk HTTPS)

## Fitur Utama

1. **Manajemen Personil**
   - CRUD data personil POLRI, ASN, P3K
   - Filter berdasarkan unsur, bagian, jabatan
   - Export PDF, Excel, CSV
   - Advanced search dengan multiple criteria

2. **Manajemen Bagian & Unsur**
   - 6 Unsur: PIMPINAN, BAG, SAT, POLSEK, SPKT, BKO
   - 29 Bagian termasuk Pimpinan, BAG OPS, SAT RESKRIM, dll
   - Struktur organisasi hierarki

3. **Schedule Management**
   - Kalender interaktif FullCalendar
   - Integrasi Google Calendar API
   - Manajemen shift otomatis
   - Jadwal piket dan operasi

4. **Dashboard & Statistik**
   - Statistik real-time via API
   - Distribusi personil by unsur, jenis kelamin, pendidikan
   - Chart dan visualisasi data

## Teknologi Stack

- **Backend**: PHP 8.2+ dengan PDO, Database Singleton Pattern
- **Frontend**: Bootstrap 5.3.0, Font Awesome 6.4.2 (standardized across all pages)
- **Database**: MariaDB 10.4+ (MySQL) dengan socket/TCP fallback connection
- **JavaScript**: Vanilla JS dengan Fetch API, jQuery 3.6.0 untuk kompatibilitas
- **Styling**: Custom CSS dengan variabel theming, responsive.css untuk mobile-first design

### Frontend Integration (F2E)
- **CSS Variables**: Consistent theming dengan `--primary-color: #1a237e`, `--secondary-color: #3949ab`, `--accent-color: #ffd700`
- **JavaScript API Client**: Standardized error handling dengan HTML response detection
- **Font Awesome**: Version 6.4.2 dengan integrity hash untuk security
- **Bootstrap**: Version 5.3.0 untuk semua components

### Backend Architecture
- **Database Connection**: Singleton pattern dengan TCP fallback (XAMPP socket → TCP)
- **Authentication**: AuthHelper dengan Argon2id hashing dan session security
- **API Standardization**: Consistent response format `{success, message, data, timestamp}`
- **Error Handling**: Environment-based (detailed in dev, generic in production)

## Keamanan

- Password hashing dengan Argon2id
- Prepared statements untuk semua query
- Session management dengan token
- Input sanitization & validation
- Error handling terpusat
- Soft delete pattern untuk data retention

## Struktur Database

Lihat file [database_schema.md](database_schema.md) untuk detail lengkap 16 tabel utama termasuk:
- personil, bagian, unsur, jabatan
- schedules, assignments, operations
- calendar_tokens, personil_kontak, personil_pendidikan

## API Documentation

Lihat file [api_reference.md](api_reference.md) untuk dokumentasi lengkap 17+ API endpoint.
