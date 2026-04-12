-- Add dasar_hukum column to unsur table
-- This column stores the legal basis/reference for each unsur

ALTER TABLE `unsur` 
ADD COLUMN `dasar_hukum` VARCHAR(255) DEFAULT NULL AFTER `deskripsi`;

-- Update existing data with legal basis references
UPDATE `unsur` SET 
    `deskripsi` = 'Kapolres dan Wakapolres',
    `dasar_hukum` = 'PERKAP No. 23 Tahun 2010 Pasal 4' 
WHERE `kode_unsur` = 'UNSUR_PIMPINAN';

UPDATE `unsur` SET 
    `deskripsi` = 'Kepala Bagian (KABAG), Kepala Satuan (KASAT), Kepala Polsek (KAPOLSEK)',
    `dasar_hukum` = 'PERKAP No. 23 Tahun 2010 Pasal 5' 
WHERE `kode_unsur` = 'UNSUR_PEMBANTU_PIMPINAN';

UPDATE `unsur` SET 
    `deskripsi` = 'Satuan Tugas Pokok di tingkat POLRES',
    `dasar_hukum` = 'PERKAP No. 23 Tahun 2010 Pasal 6' 
WHERE `kode_unsur` = 'UNSUR_PELAKSANA_TUGAS_POKOK';

UPDATE `unsur` SET 
    `deskripsi` = 'Kepolisian Sektor (POLSEK) jajaran POLRES',
    `dasar_hukum` = 'PERKAP No. 23 Tahun 2010 Pasal 7' 
WHERE `kode_unsur` = 'UNSUR_PELAKSANA_KEWILAYAHAN';

UPDATE `unsur` SET 
    `deskripsi` = 'Unit pendukung operasional dan administrasi',
    `dasar_hukum` = 'PERKAP No. 23 Tahun 2010 Pasal 8' 
WHERE `kode_unsur` = 'UNSUR_PENDUKUNG';

UPDATE `unsur` SET 
    `deskripsi` = 'Unit khusus dan penugasan khusus',
    `dasar_hukum` = 'PERKAP No. 23 Tahun 2010' 
WHERE `kode_unsur` = 'UNSUR_LAINNYA';
