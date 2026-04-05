# Database Schema

SPRIN uses MySQL with tables: personil, bagian, unsur, jabatan, pangkat, users, backups, schedules. Key relationships: personil belongs to bagian and jabatan, jabatan belongs to bagian, unsur is top-level organizational unit. All tables have id, created_at, updated_at, is_deleted, is_active columns.

**Tags**: database, schema, relationships
