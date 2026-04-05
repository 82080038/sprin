# Documentation Update Procedures

## Overview

Prosedur standar untuk mengupdate dokumentasi Windsurf (`.windsurf/`) agar selalu sinkron dengan kode aplikasi.

---

## 1. When to Update Documentation

### IMMEDIATE UPDATE Required:
- [ ] Menambah/mengubah API endpoint
- [ ] Mengubah response format API
- [ ] Menambah tabel database baru
- [ ] Mengubah foreign key relationships
- [ ] Menambah library/dependency baru
- [ ] Mengubah authentication flow
- [ ] Menambah fitur baru

### WEEKLY UPDATE (Batch):
- [ ] Minor code refactoring tanpa functional change
- [ ] Bug fixes yang tidak mengubah interface
- [ ] Performance improvements
- [ ] Code cleanup

---

## 2. Update Procedures by File Type

### A. API Changes → Update `api_reference.md`

**Step-by-Step:**
1. Buka file yang dimodifikasi (contoh: `api/personil_crud.php`)
2. Identify perubahan:
   - Endpoint baru?
   - Parameter baru?
   - Response format berubah?
3. Buka `.windsurf/memories/api_reference.md`
4. Update bagian yang relevan:
   - Tambah endpoint baru di daftar
   - Update parameter list
   - Update contoh response
   - Update error response jika berubah
5. Simpan dengan format yang konsisten

**Template Update API:**
```markdown
#### X. Nama Endpoint Baru
```
METHOD /api/file.php?action=nama_action
```
**Parameters:**
- `param1` (type) - Description
- `param2` (type) - Description

**Response:**
```json
{
  "success": true,
  "message": "Description",
  "data": {...},
  "timestamp": "ISO8601"
}
```
```

---

### B. Database Changes → Update `database_schema.md`

**Step-by-Step:**
1. Identify perubahan database:
   - Tabel baru?
   - Kolom baru/terhapus?
   - Foreign key baru?
   - Index baru?
2. Buka `.windsurf/memories/database_schema.md`
3. Update:
   - Entity relationship diagram (jika berubah)
   - Daftar tabel (tambah/hapus)
   - Schema tabel yang berubah
   - Foreign key references
   - Indexes
4. Update migration notes jika diperlukan

---

### C. Frontend/Library Changes → Update `app_overview.md`

**Step-by-Step:**
1. Identify perubahan:
   - Library version berubah?
   - CSS framework berubah?
   - JavaScript approach berubah?
2. Buka `.windsurf/memories/app_overview.md`
3. Update bagian:
   - Technology Stack
   - Frontend Integration (F2E)
   - Backend Architecture
4. Pastikan versi library sesuai dengan actual usage

---

### D. Workflow/Integration Changes → Update `integration_guide.md`

**Step-by-Step:**
1. Identify perubahan flow:
   - User journey berubah?
   - API call sequence berubah?
   - Authentication flow berubah?
2. Buka `.windsurf/memories/integration_guide.md`
3. Update:
   - E2E workflow diagrams
   - Data flow descriptions
   - Error handling patterns
   - Testing procedures
4. Update version history di akhir file

---

### E. Coding Standards Changes → Update `php_coding_standards.md`

**Step-by-Step:**
1. Identify pattern baru yang perlu distandarisasi
2. Buka `.windsurf/rules/php_coding_standards.md`
3. Tambah section baru atau update existing:
   - Database Operations
   - API Response Format
   - Security Best Practices
   - Error Handling
4. Tambah ke Forbidden Patterns jika ada anti-pattern baru

---

## 3. Sync Verification Process

### Before Starting Work:
```bash
# 1. Read all documentation
cat .windsurf/memories/api_reference.md
cat .windsurf/memories/app_overview.md
cat .windsurf/memories/database_schema.md
cat .windsurf/memories/integration_guide.md
cat .windsurf/rules/php_coding_standards.md

# 2. Compare with actual code
grep -r "Database::getInstance" api/
grep -r "font-awesome" pages/
grep -r "bootstrap" pages/
grep -r "AuthHelper::validateSession" core/ pages/
```

### After Making Changes:
```bash
# 1. Verify consistency
diff <(cat .windsurf/memories/api_reference.md | grep -A5 "Response Format") <(grep -A5 "json_encode" api/personil_crud.php)

# 2. Check for outdated info
grep "5.15.4" .windsurf/memories/*.md  # Should find nothing (already updated to 6.4.2)
grep "5.1.3" .windsurf/memories/*.md   # Should find nothing (already updated to 5.3.0)
```

---

## 4. Documentation Structure Standards

### File Organization:
```
.windsurf/
├── memories/           # Factual information about the project
│   ├── api_reference.md      # API documentation
│   ├── app_overview.md       # Architecture & tech stack
│   ├── database_schema.md    # Database structure
│   └── integration_guide.md  # F2E/E2E integration
│
├── rules/              # Coding standards and constraints
│   └── php_coding_standards.md
│
├── skills/             # How-to guides for specific tasks
│   ├── crud_api_generator.md
│   ├── database_migration.md
│   └── debug_troubleshoot.md
│
└── workflows/          # Step-by-step procedures
    ├── development_sync_checklist.md
    ├── add_new_feature.md
    ├── app_setup.md
    └── bug_fix.md
```

### Content Format:
- Use Markdown format
- Include frontmatter with description
- Use code blocks for examples
- Include tables for structured data
- Add version history at the end

---

## 5. Quick Sync Commands

### Verify Library Versions:
```bash
# Check Font Awesome version
grep -r "font-awesome" pages/ | grep -v "6.4.2" && echo "INCONSISTENCY FOUND"

# Check Bootstrap version
grep -r "bootstrap" pages/ | grep "5.3.0" | wc -l
```

### Verify API Standardization:
```bash
# Check for timestamp in responses
grep -l "timestamp.*date('c')" api/*.php | wc -l

# Check for Database singleton
grep -l "Database::getInstance" api/*.php | wc -l

# Check for AuthHelper
grep -l "AuthHelper::validateSession" api/*.php | wc -l
```

### Verify Documentation Matches Code:
```bash
# Check if api_reference mentions all API files
for file in api/*.php; do
    basename=$(basename $file)
    grep -q "$basename" .windsurf/memories/api_reference.md || echo "Missing: $basename"
done
```

---

## 6. Version History Tracking

Setiap file dokumentasi harus memiliki version history di akhir:

```markdown
## Version History

### v1.0.1 (YYYY-MM-DD)
- Updated: [specific changes]
- Added: [new content]
- Fixed: [corrections]

### v1.0.0 (YYYY-MM-DD)
- Initial documentation
```

---

## 7. Emergency Sync Protocol

### When Inconsistency Detected:

1. **STOP** - Jangan lanjutkan development
2. **IDENTIFY** - Cari sumber ketidakcocokan
3. **DECIDE** - Mana yang menjadi source of truth?
4. **UPDATE** - Sync keduanya
5. **VERIFY** - Pastikan sudah konsisten
6. **RESUME** - Lanjutkan development

### Common Inconsistency Patterns:

| Issue | Solution |
|-------|----------|
| Library version mismatch | Update code OR update docs |
| API endpoint missing in docs | Add to api_reference.md |
| Database schema outdated | Update database_schema.md |
| Response format different | Update both code and docs |
| Missing foreign key | Update database_schema.md |

---

## 8. Monthly Maintenance Tasks

- [ ] Review all documentation files
- [ ] Check for broken links/references
- [ ] Update version numbers
- [ ] Verify all API endpoints documented
- [ ] Check for deprecated patterns in docs
- [ ] Update integration guide jika workflow berubah
- [ ] Review and update skills if needed
