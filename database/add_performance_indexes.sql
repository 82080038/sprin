-- Performance Indexes for SPRIN Application
-- Run this once to improve query performance
-- Date: 2026-04-09

-- ============================================
-- Indexes for personil table (most queried)
-- ============================================

-- Index for soft delete filter (used in almost every query)
ALTER TABLE personil ADD INDEX IF NOT EXISTS idx_is_deleted (is_deleted);

-- Index for jabatan lookup (JOIN with jabatan)
ALTER TABLE personil ADD INDEX IF NOT EXISTS idx_id_jabatan (id_jabatan);

-- Index for bagian lookup (JOIN with bagian)
ALTER TABLE personil ADD INDEX IF NOT EXISTS idx_id_bagian (id_bagian);

-- Index for NRP search (frequently used for lookup)
ALTER TABLE personil ADD INDEX IF NOT EXISTS idx_nrp (nrp);

-- Index for nama search
ALTER TABLE personil ADD INDEX IF NOT EXISTS idx_nama (nama);

-- Index for pangkat filter
ALTER TABLE personil ADD INDEX IF NOT EXISTS idx_id_pangkat (id_pangkat);

-- Composite index for active personil by jabatan (common JOIN pattern)
ALTER TABLE personil ADD INDEX IF NOT EXISTS idx_jabatan_active (id_jabatan, is_deleted);

-- Composite index for active personil by bagian
ALTER TABLE personil ADD INDEX IF NOT EXISTS idx_bagian_active (id_bagian, is_deleted);

-- ============================================
-- Indexes for jabatan table
-- ============================================

-- Index for unsur lookup
ALTER TABLE jabatan ADD INDEX IF NOT EXISTS idx_id_unsur (id_unsur);

-- Index for bagian lookup
ALTER TABLE jabatan ADD INDEX IF NOT EXISTS idx_id_bagian_jabatan (id_bagian);

-- Index for ordering
ALTER TABLE jabatan ADD INDEX IF NOT EXISTS idx_urutan_jabatan (urutan);

-- ============================================
-- Indexes for bagian table
-- ============================================

-- Index for unsur lookup
ALTER TABLE bagian ADD INDEX IF NOT EXISTS idx_id_unsur_bagian (id_unsur);

-- Index for ordering
ALTER TABLE bagian ADD INDEX IF NOT EXISTS idx_urutan_bagian (urutan);

-- ============================================
-- Indexes for unsur table
-- ============================================

-- Index for ordering
ALTER TABLE unsur ADD INDEX IF NOT EXISTS idx_urutan_unsur (urutan);

-- ============================================
-- Verify indexes
-- ============================================
-- Run: SHOW INDEX FROM personil;
-- Run: SHOW INDEX FROM jabatan;
-- Run: SHOW INDEX FROM bagian;
-- Run: SHOW INDEX FROM unsur;
