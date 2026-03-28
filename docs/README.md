# 🏛️ SISTEM INFORMASI POLRES SAMOSIR

## 📋 Deskripsi
Aplikasi sistem informasi manajemen personil dan jadwal kepolisian untuk POLRES Samosir dengan struktur organisasi yang sesuai regulasi POLRI.

## 📁 Struktur Aplikasi

### 🏗️ Arsitektur Folder Profesional

#### 📄 Root Directory (File yang harus di root)
- `index.php` - Entry point utama aplikasi
- `login.php` - Halaman login
- `.htaccess` - Konfigurasi Apache & URL routing
- `.gitignore` - Konfigurasi Git

#### 🔧 core/ - File Sistem Inti
- `config.php` - Konfigurasi database & aplikasi
- `auth_check.php` - Validasi autentikasi
- `logout.php` - Proses logout
- `calendar_config.php` - Konfigurasi kalender

#### 📄 pages/ - Halaman Aplikasi
- `main.php` - Dashboard utama
- `personil.php` - Data personil POLRES
- `bagian.php` - Data bagian/satuan
- `calendar_dashboard.php` - Dashboard kalender
- `schedule_manager.php` - Manajemen jadwal
- `jabatan_rangkap_detail.php` - Detail jabatan rangkap

#### 🌐 api/ - API Endpoints
- `personil_api.php` - API data personil
- `bagian_api.php` - API data bagian
- `google_calendar_api.php` - API Google Calendar
- `personil_simple.php` - API sederhana personil
- `simple.php` - API sederhana
- `bulk_update_personil.php` - API bulk update

#### 🧩 includes/ - Komponen Reusable
- `components/header.php` - Header HTML
- `components/footer.php` - Footer HTML

#### 🎨 public/ - Assets Publik
- `assets/css/personil.css` - CSS untuk halaman personil
- `assets/js/api-client.js` - JavaScript API client
- `assets/js/jquery-api-client.js` - jQuery API client
- `assets/js/config.php` - Konfigurasi JavaScript

#### � docs/ - Dokumentasi
- `README.md` - Dokumentasi aplikasi (ini)
- `ANALISIS_UNSUR_POLRI.md` - Analisis lengkap struktur POLRI
- `STRUKTUR_FOLDER.md` - Dokumentasi struktur folder

#### 📄 doc_piket/ - Dokumentasi Piket (Dipertahankan)
- File CSV, Excel, dan gambar terkait data piket

#### 🚫 error_pages/ - Halaman Error
- `500.php` - Halaman error 500

#### 📁 Folder Reserved (Dipersiapkan untuk pengembangan)
- `config/` - Konfigurasi tambahan
- `models/` - Model database (MVC)
- `views/` - View templates (MVC)
- `controllers/` - Controller logic (MVC)

## 🌐 URL Routing

### 🔗 Akses Langsung:
- `/` → Dashboard utama
- `/login` → Halaman login
- `/personil` → Data personil
- `/bagian` → Data bagian
- `/calendar` → Dashboard kalender
- `/schedule` → Manajemen jadwal

### 🌐 API Endpoints:
- `/api/personil` - API data personil
- `/api/bagian` - API data bagian
- `/api/calendar` - API Google Calendar

## 🏛️ Struktur Organisasi POLRES Samosir

Berdasarkan **PERKAP No. 23 Tahun 2010**, POLRES Samosir memiliki:

### 📊 4 Unsur Utama:
1. **Unsur Pimpinan** - Kapolres & Wakapolres
2. **Unsur Pembantu** - 4 Kepala Bagian (Ops, Ren, SDM, Log)
3. **Unsur Pelaksana** - 9 Satuan Fungsional (Intelkam, Reskrim, Lantas, dll)
4. **Unsur Kewilayahan** - 5 Polsek Jajaran

### 🎖️ Hierarki Pangkat:
- **Perwira Tinggi**: Kombes Polisi (Kapolres)
- **Perwira Menengah**: AKBP (Wakapolres)
- **Perwira Pertama**: Kompol, AKP (Kabag, Kasat)
- **Perwira Awal**: Iptu, Ipda (Kasat, Kapolsek)
- **Bintara Tinggi**: Aiptu, Aipda (Kanit, Personil)
- **Bintara Reguler**: Bripka, Brigpol, Briptu, Bripda (Personil Pelaksana)

## 🔧 Instalasi

### 📋 Persyaratan:
- PHP 7.4+
- MySQL 5.7+
- Web Server (Apache dengan mod_rewrite)

### 🛠️ Setup:
1. Clone/download repository
2. Import database
3. Konfigurasi `core/config.php`
4. Setup folder permissions
5. Akses aplikasi via browser

### 🔐 Login:
- Username: `bagops`
- Password: `admin123`

## 🎯 Fitur Utama

### 📊 Manajemen Personil:
- Data personil real-time dari database
- Struktur organisasi sesuai regulasi
- Hierarki pangkat lengkap
- Filter dan pencarian

### 📅 Manajemen Jadwal:
- Dashboard kalender interaktif
- Integrasi Google Calendar
- Manajemen jadwal piket
- Notifikasi otomatis

### 📈 Dashboard:
- Statistik personil lengkap
- Grafik kehadiran
- Quick access menu
- Real-time updates

## 📞 Kontak

Untuk informasi lebih lanjut, hubungi:
- POLRES Samosir
- Email: info@polressamosir.id

---

**🎉 Aplikasi dengan struktur folder profesional dan implementasi lengkap struktur POLRES.**
