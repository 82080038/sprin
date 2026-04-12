-- Add keterangan column to bagian table
ALTER TABLE `bagian` ADD COLUMN `keterangan` TEXT DEFAULT NULL AFTER `deskripsi`;

-- Add keterangan column to jabatan table
ALTER TABLE `jabatan` ADD COLUMN `keterangan` TEXT DEFAULT NULL AFTER `deskripsi`;
