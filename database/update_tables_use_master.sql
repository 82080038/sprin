-- =====================================================
-- UPDATE TABEL EKSTING UNTUK MENGGUNAKAN MASTER DATA
-- Integrasi master tabel istilah kepegawaan dengan tabel existing
-- =====================================================

-- STEP 1: TAMBAH FOREIGN KEY KE TABEL JABATAN
ALTER TABLE jabatan ADD COLUMN id_jenis_penugasan INT NULL;
ALTER TABLE jabatan ADD COLUMN id_alasan_penugasan INT NULL;
ALTER TABLE jabatan ADD COLUMN id_status_jabatan INT NULL;

-- STEP 2: TAMBAH FOREIGN KEY KE TABEL PERSONIL
ALTER TABLE personil ADD COLUMN id_jenis_penugasan INT NULL;
ALTER TABLE personil ADD COLUMN id_alasan_penugasan INT NULL;
ALTER TABLE personil ADD COLUMN id_status_jabatan INT NULL;

-- STEP 3: UPDATE TABEL JABATAN DENGAN MASTER DATA
-- Update jenis penugasan berdasarkan nama jabatan
UPDATE jabatan j 
SET j.id_jenis_penugasan = (SELECT id FROM master_jenis_penugasan WHERE kode = 'DEF')
WHERE j.nama_jabatan NOT LIKE 'PS.%' 
AND j.nama_jabatan NOT LIKE 'Plt.%' 
AND j.nama_jabatan NOT LIKE 'Pjs.%'
AND j.nama_jabatan NOT LIKE 'Plh.%'
AND j.nama_jabatan NOT LIKE 'Pj.%';

UPDATE jabatan j 
SET j.id_jenis_penugasan = (SELECT id FROM master_jenis_penugasan WHERE kode = 'PS')
WHERE j.nama_jabatan LIKE 'PS.%';

UPDATE jabatan j 
SET j.id_jenis_penugasan = (SELECT id FROM master_jenis_penugasan WHERE kode = 'PLT')
WHERE j.nama_jabatan LIKE 'Plt.%';

UPDATE jabatan j 
SET j.id_jenis_penugasan = (SELECT id FROM master_jenis_penugasan WHERE kode = 'PJS')
WHERE j.nama_jabatan LIKE 'Pjs.%';

UPDATE jabatan j 
SET j.id_jenis_penugasan = (SELECT id FROM master_jenis_penugasan WHERE kode = 'PLH')
WHERE j.nama_jabatan LIKE 'Plh.%';

UPDATE jabatan j 
SET j.id_jenis_penugasan = (SELECT id FROM master_jenis_penugasan WHERE kode = 'PJ')
WHERE j.nama_jabatan LIKE 'Pj.%';

-- Update status jabatan berdasarkan nama jabatan
UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'KAPOLRES')
WHERE j.nama_jabatan LIKE '%KAPOLRES%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'WAKAPOLRES')
WHERE j.nama_jabatan LIKE '%WAKAPOLRES%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'KABAG')
WHERE j.nama_jabatan LIKE '%KABAG%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'KASAT')
WHERE j.nama_jabatan LIKE '%KASAT%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'KAPOLSEK')
WHERE j.nama_jabatan LIKE '%KAPOLSEK%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'KASUBBAG')
WHERE j.nama_jabatan LIKE '%KASUBBAG%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'KANIT')
WHERE j.nama_jabatan LIKE '%KANIT%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'KAUR')
WHERE j.nama_jabatan LIKE '%KAUR%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'KA_SPKT')
WHERE j.nama_jabatan LIKE '%KA SPKT%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'PS_KAPOLSEK')
WHERE j.nama_jabatan LIKE '%PS. KAPOLSEK%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'PS_KANIT')
WHERE j.nama_jabatan LIKE '%PS. KANIT%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'RESKRIM')
WHERE (j.nama_jabatan LIKE '%RESKRIM%' OR j.nama_jabatan LIKE '%KANIT RESKRIM%');

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'INTELKAM')
WHERE (j.nama_jabatan LIKE '%INTELKAM%' OR j.nama_jabatan LIKE '%KANIT INTELKAM%');

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'LANTAS')
WHERE (j.nama_jabatan LIKE '%LANTAS%' OR j.nama_jabatan LIKE '%KANIT LANTAS%');

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'BINMAS')
WHERE (j.nama_jabatan LIKE '%BINMAS%' OR j.nama_jabatan LIKE '%KANIT BINMAS%');

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'POLAIRUD')
WHERE (j.nama_jabatan LIKE '%POLAIRUD%' OR j.nama_jabatan LIKE '%KANIT POLAIRUD%');

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'TAHTI')
WHERE (j.nama_jabatan LIKE '%TAHTI%' OR j.nama_jabatan LIKE '%KANIT TAHTI%');

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'SIKEU')
WHERE j.nama_jabatan LIKE '%SIKEU%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'SIKUM')
WHERE j.nama_jabatan LIKE '%SIKUM%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'SIHUMAS')
WHERE j.nama_jabatan LIKE '%SIHUMAS%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'SIUM')
WHERE j.nama_jabatan LIKE '%SIUM%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'SITIK')
WHERE j.nama_jabatan LIKE '%SITIK%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'SIWAS')
WHERE j.nama_jabatan LIKE '%SIWAS%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'SIDOKKES')
WHERE j.nama_jabatan LIKE '%SIDOKKES%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'SIPROPAM')
WHERE j.nama_jabatan LIKE '%SIPROPAM%';

UPDATE jabatan j 
SET j.id_status_jabatan = (SELECT id FROM master_status_jabatan WHERE kode = 'BINTARA')
WHERE j.nama_jabatan LIKE '%BINTARA%';

-- Update alasan penugasan default
UPDATE jabatan j 
SET j.id_alasan_penugasan = (SELECT id FROM master_alasan_penugasan WHERE kode = 'MUTASI')
WHERE j.id_jenis_penugasan = (SELECT id FROM master_jenis_penugasan WHERE kode = 'PS');

UPDATE jabatan j 
SET j.id_alasan_penugasan = (SELECT id FROM master_alasan_penugasan WHERE kode = 'SAKIT')
WHERE j.id_jenis_penugasan = (SELECT id FROM master_jenis_penugasan WHERE kode = 'PLT');

-- STEP 4: UPDATE TABEL PERSONIL DENGAN MASTER DATA
-- Update personil berdasarkan jabatan
UPDATE personil p 
SET p.id_jenis_penugasan = (SELECT j.id_jenis_penugasan FROM jabatan j WHERE j.id = p.id_jabatan),
    p.id_alasan_penugasan = (SELECT j.id_alasan_penugasan FROM jabatan j WHERE j.id = p.id_jabatan),
    p.id_status_jabatan = (SELECT j.id_status_jabatan FROM jabatan j WHERE j.id = p.id_jabatan);

-- STEP 5: CLEANUP NAMA JABATAN (HILANGKAN PREFIX PENUGASAN)
UPDATE jabatan SET nama_jabatan = TRIM(SUBSTRING(nama_jabatan, 5)) WHERE nama_jabatan LIKE 'PS.%';
UPDATE jabatan SET nama_jabatan = TRIM(SUBSTRING(nama_jabatan, 6)) WHERE nama_jabatan LIKE 'Plt.%';
UPDATE jabatan SET nama_jabatan = TRIM(SUBSTRING(nama_jabatan, 5)) WHERE nama_jabatan LIKE 'Pjs.%';
UPDATE jabatan SET nama_jabatan = TRIM(SUBSTRING(nama_jabatan, 5)) WHERE nama_jabatan LIKE 'Plh.%';
UPDATE jabatan SET nama_jabatan = TRIM(SUBSTRING(nama_jabatan, 4)) WHERE nama_jabatan LIKE 'Pj.%';

-- STEP 6: VALIDASI HASIL UPDATE
SELECT 'VALIDASI JABATAN SETELAH UPDATE' as status,
       j.nama_jabatan,
       jp.nama as jenis_penugasan,
       ap.nama as alasan_penugasan,
       sj.nama as status_jabatan,
       COUNT(p.id) as personil_count
FROM jabatan j
LEFT JOIN master_jenis_penugasan jp ON j.id_jenis_penugasan = jp.id
LEFT JOIN master_alasan_penugasan ap ON j.id_alasan_penugasan = ap.id
LEFT JOIN master_status_jabatan sj ON j.id_status_jabatan = sj.id
LEFT JOIN personil p ON j.id = p.id_jabatan
GROUP BY j.id, j.nama_jabatan, jp.nama, ap.nama, sj.nama
ORDER BY jp.nama, j.nama_jabatan;

-- STEP 7: VALIDASI PERSONIL
SELECT 'VALIDASI PERSONIL SETELAH UPDATE' as status,
       p.nama,
       p.nrp,
       jp.nama as jenis_penugasan,
       ap.nama as alasan_penugasan,
       sj.nama as status_jabatan
FROM personil p
LEFT JOIN master_jenis_penugasan jp ON p.id_jenis_penugasan = jp.id
LEFT JOIN master_alasan_penugasan ap ON p.id_alasan_penugasan = ap.id
LEFT JOIN master_status_jabatan sj ON p.id_status_jabatan = sj.id
ORDER BY jp.nama, p.nama;

-- STEP 8: STATISTICS REPORT
SELECT 'STATISTICS PENUGASAN SETELAH UPDATE' as status,
       jp.nama as jenis_penugasan,
       COUNT(DISTINCT j.id) as jabatan_count,
       COUNT(DISTINCT p.id) as personil_count,
       ROUND(COUNT(DISTINCT j.id) * 100.0 / (SELECT COUNT(*) FROM jabatan), 2) as jabatan_percentage
FROM jabatan j
LEFT JOIN master_jenis_penugasan jp ON j.id_jenis_penugasan = jp.id
LEFT JOIN personil p ON j.id = p.id_jabatan
GROUP BY jp.id, jp.nama
ORDER BY jabatan_count DESC;

-- STEP 9: CHECK DATA CONSISTENCY
SELECT 'DATA CONSISTENCY CHECK' as status,
       'Jabatan tanpa jenis penugasan' as check_type,
       COUNT(*) as count
FROM jabatan 
WHERE id_jenis_penugasan IS NULL

UNION ALL

SELECT 'DATA CONSISTENCY CHECK' as status,
       'Jabatan tanpa status jabatan' as check_type,
       COUNT(*) as count
FROM jabatan 
WHERE id_status_jabatan IS NULL

UNION ALL

SELECT 'DATA CONSISTENCY CHECK' as status,
       'Personil tanpa jenis penugasan' as check_type,
       COUNT(*) as count
FROM personil 
WHERE id_jenis_penugasan IS NULL

UNION ALL

SELECT 'DATA CONSISTENCY CHECK' as status,
       'Personil tanpa status jabatan' as check_type,
       COUNT(*) as count
FROM personil 
WHERE id_status_jabatan IS NULL;

-- STEP 10: FINAL SUMMARY
SELECT 'FINAL SUMMARY' as status,
       (SELECT COUNT(*) FROM jabatan) as total_jabatan,
       (SELECT COUNT(*) FROM jabatan WHERE id_jenis_penugasan IS NOT NULL) as jabatan_with_penugasan,
       (SELECT COUNT(*) FROM personil) as total_personil,
       (SELECT COUNT(*) FROM personil WHERE id_jenis_penugasan IS NOT NULL) as personil_with_penugasan,
       (SELECT COUNT(*) FROM master_jenis_penugasan) as master_jenis_penugasan,
       (SELECT COUNT(*) FROM master_alasan_penugasan) as master_alasan_penugasan,
       (SELECT COUNT(*) FROM master_status_jabatan) as master_status_jabatan;

-- =====================================================
-- END OF TABLE UPDATE WITH MASTER DATA
-- =====================================================
