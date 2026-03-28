# 📁 STRUKTUR FOLDER APLIKASI POLRES SAMOSIR

## 🏗️ Arsitektur Folder Profesional

### 📁 Root Directory (File yang harus di root)
```
📄 index.php          - Entry point utama aplikasi
📄 login.php          - Halaman login
📄 .htaccess          - Konfigurasi Apache
📄 .gitignore         - Konfigurasi Git
```

### 📁 core/ - File Sistem Inti
```
📁 core/
├── 📄 config.php           - Konfigurasi database & aplikasi
├── 📄 auth_check.php       - Validasi autentikasi
├── 📄 logout.php           - Proses logout
└── 📄 calendar_config.php  - Konfigurasi kalender
```

### 📁 pages/ - Halaman Aplikasi
```
📁 pages/
├── 📄 main.php                - Dashboard utama
├── 📄 personil.php            - Data personil POLRES
├── 📄 bagian.php              - Data bagian/satuan
├── 📄 calendar_dashboard.php - Dashboard kalender
├── 📄 schedule_manager.php    - Manajemen jadwal
└── 📄 jabatan_rangkap_detail.php - Detail jabatan rangkap
```

### 📁 api/ - API Endpoints
```
📁 api/
├── 📄 personil_api.php       - API data personil
├── 📄 bagian_api.php         - API data bagian
├── 📄 google_calendar_api.php - API Google Calendar
├── 📄 personil_simple.php    - API sederhana personil
├── 📄 simple.php             - API sederhana
├── 📄 bulk_update_personil.php - API bulk update
├── 📄 personil.php           - API personil (alternatif)
├── 📄 test.php               - API testing
└── 📁 v1/
    └── 📄 index.php          - API v1 router
```

### 📁 includes/ - Komponen Reusable
```
📁 includes/
└── 📁 components/
    ├── 📄 header.php          - Header HTML
    ├── 📄 footer.php          - Footer HTML
    └── 📄 header_backup_*.php - Backup header
```

### 📁 public/ - Assets Publik
```
📁 public/
└── 📁 assets/
    ├── 📁 css/
    │   └── 📄 personil.css    - CSS untuk halaman personil
    └── 📁 js/
        ├── 📄 api-client.js          - JavaScript API client
        ├── 📄 jquery-api-client.js   - jQuery API client
        └── 📄 config.php             - Konfigurasi JavaScript
```

### 📁 docs/ - Dokumentasi
```
📁 docs/
├── 📄 README.md               - Dokumentasi aplikasi
└── 📄 ANALISIS_UNSUR_POLRI.md - Analisis lengkap struktur POLRI
```

### 📁 doc_piket/ - Dokumentasi Piket (Dipertahankan sesuai permintaan)
```
📁 doc_piket/
├── 📄 DATA PERS FEBRUARI 2026 NEW.csv
├── 📄 DATA PERS FEBRUARI 2026 NEW.xlsx
├── 📄 personil.csv
└── 📄 WhatsApp Image 2026-03-27 at 13.12.0*.jpeg
```

### 📁 error_pages/ - Halaman Error
```
📁 error_pages/
└── 📄 500.php                 - Halaman error 500
```

### 📁 Folder Reserved (Dipersiapkan untuk pengembangan)
```
📁 config/    - Konfigurasi tambahan
📁 models/    - Model database (MVC)
📁 views/     - View templates (MVC)
📁 controllers/ - Controller logic (MVC)
```

---

## 🔄 Alur Akses Aplikasi

### 🌐 URL Routing (melalui .htaccess):
```
/                    → index.php → pages/main.php
/login               → login.php
/personil            → pages/personil.php
/bagian              → pages/bagian.php
/calendar            → pages/calendar_dashboard.php
/schedule            → pages/schedule_manager.php

/api/personil        → api/personil_api.php
/api/bagian          → api/bagian_api.php
/api/calendar        → api/google_calendar_api.php
```

### 🔐 Alur Autentikasi:
```
1. User mengakses / → index.php
2. Cek session → core/auth_check.php
3. Jika belum login → redirect login.php
4. Login berhasil → redirect pages/main.php
5. Setiap halaman → cek session → include ../core/auth_check.php
```

---

## 🎯 Keuntungan Struktur Ini:

### ✅ **Organisasi yang Jelas:**
- File yang terkait dikelompokkan bersama
- Pemisahan antara logic, presentation, dan assets
- Mudah untuk maintenance dan development

### ✅ **Keamanan:**
- Konfigurasi sensitif di folder terpisah
- Assets publik terpisah dari file PHP
- .htaccess melindungi file-file penting

### ✅ **Scalability:**
- Folder models, views, controllers siap untuk MVC
- API terorganisir dengan versioning
- Mudah menambahkan fitur baru

### ✅ **Professional:**
- Mengikuti best practice struktur aplikasi web
- Mudah untuk deployment dan version control
- Dokumentasi terintegrasi

---

## 📝 Catatan Penting:

### 🔧 **File yang TIDAK BOLEH Dipindahkan:**
- `index.php` - Entry point aplikasi
- `login.php` - Halaman login utama
- `.htaccess` - Konfigurasi web server
- `.gitignore` - Konfigurasi Git

### 📁 **Folder yang Dipertahankan:**
- `doc_piket/` - Sesuai permintaan user
- `docs/` - Dokumentasi penting

### 🔄 **Path Relatif:**
- Semua file di `pages/` menggunakan path relatif `../`
- File di `api/` menggunakan path relatif `../`
- Assets diakses melalui `../public/assets/`

---

**🎉 Struktur folder ini membuat aplikasi lebih profesional, mudah dikelola, dan siap untuk pengembangan lebih lanjut.**
