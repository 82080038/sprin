-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 05 Apr 2026 pada 21.15
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bagops`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `access_logs`
--

CREATE TABLE `access_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `resource` varchar(200) NOT NULL,
  `method` varchar(10) NOT NULL,
  `status_code` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `request_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_data`)),
  `response_size` int(11) DEFAULT NULL,
  `execution_time` decimal(10,4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `record_id` int(11) NOT NULL,
  `action` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `backups`
--

CREATE TABLE `backups` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) NOT NULL DEFAULT 0,
  `backup_type` enum('full','partial','scheduled') NOT NULL DEFAULT 'full',
  `tables_included` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `status` enum('pending','running','completed','failed') NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `is_auto` tinyint(1) NOT NULL DEFAULT 0,
  `checksum` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `backup_history`
--

CREATE TABLE `backup_history` (
  `id` int(11) NOT NULL,
  `backup_schedule_id` int(11) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `type` enum('full','incremental','differential') NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `file_hash` varchar(64) DEFAULT NULL,
  `compression` tinyint(1) DEFAULT 0,
  `encryption` tinyint(1) DEFAULT 0,
  `status` enum('running','completed','failed','cancelled') DEFAULT 'running',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `backup_schedule`
--

CREATE TABLE `backup_schedule` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `type` enum('full','incremental','differential') NOT NULL,
  `schedule_type` enum('daily','weekly','monthly') NOT NULL,
  `schedule_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`schedule_config`)),
  `backup_path` varchar(500) DEFAULT NULL,
  `retention_days` int(11) DEFAULT 30,
  `compression` tinyint(1) DEFAULT 1,
  `encryption` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `last_run_at` timestamp NULL DEFAULT NULL,
  `next_run_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `bagian`
--

CREATE TABLE `bagian` (
  `id` int(11) NOT NULL,
  `kode_bagian` varchar(20) NOT NULL,
  `nama_bagian` varchar(100) NOT NULL,
  `nama_lengkap` varchar(200) DEFAULT NULL,
  `id_unsur` int(11) NOT NULL,
  `parent_bagian_id` int(11) DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `kategori` enum('bagian','seksi','subseksi','unit') NOT NULL,
  `level_bagian` enum('level_1','level_2','level_3','level_4') NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bagian`
--

INSERT INTO `bagian` (`id`, `kode_bagian`, `nama_bagian`, `nama_lengkap`, `id_unsur`, `parent_bagian_id`, `urutan`, `kategori`, `level_bagian`, `deskripsi`, `is_active`, `is_deleted`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'BAG1', 'PIMPINAN', 'PIMPINAN', 61, NULL, 2, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-03 23:09:55'),
(2, 'BAG2', 'BAG OPS', 'BAG OPS', 62, NULL, 1, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 18:07:20'),
(3, 'BAG3', 'BAG REN', 'BAG REN', 62, NULL, 3, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 18:04:06'),
(4, 'BAG4', 'BAG SDM', 'BAG SDM', 62, NULL, 2, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 18:07:20'),
(5, 'BAG5', 'BAG LOG ', 'BAG LOG ', 62, NULL, 4, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-02 16:46:44'),
(6, 'BAG6', 'SAT INTELKAM', 'SAT INTELKAM', 63, NULL, 2, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 02:32:03'),
(7, 'BAG7', 'SAT RESKRIM', 'SAT RESKRIM', 63, NULL, 1, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 02:32:03'),
(8, 'BAG8', 'SAT RESNARKOBA', 'SAT RESNARKOBA', 63, NULL, 3, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 02:32:03'),
(9, 'BAG9', 'SAT LANTAS', 'SAT LANTAS', 63, NULL, 4, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 02:32:03'),
(10, 'BAG10', 'SAT SAMAPTA', 'SAT SAMAPTA', 63, NULL, 6, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 02:32:03'),
(11, 'BAG11', 'SAT PAMOBVIT', 'SAT PAMOBVIT', 63, NULL, 5, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 02:32:03'),
(12, 'BAG12', 'SAT POLAIRUD', 'SAT POLAIRUD', 63, NULL, 7, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 02:32:03'),
(13, 'BAG13', 'SAT TAHTI', 'SAT TAHTI', 63, NULL, 9, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 02:32:03'),
(14, 'BAG14', 'SAT BINMAS', 'SAT BINMAS', 63, NULL, 8, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 02:32:03'),
(15, 'BAG15', 'POLSEK HARIAN BOHO', 'POLSEK HARIAN BOHO', 64, NULL, 5, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 02:32:03'),
(16, 'BAG16', 'POLSEK PALIPI', 'POLSEK PALIPI', 64, NULL, 6, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 02:32:03'),
(17, 'BAG17', 'POLSEK SIMANINDO', 'POLSEK SIMANINDO', 64, NULL, 7, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 02:32:03'),
(18, 'BAG18', 'POLSEK NAINGGOLAN', 'POLSEK NAINGGOLAN', 64, NULL, 3, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 02:32:03'),
(19, 'BAG19', 'POLSEK PANGURURAN', 'POLSEK PANGURURAN', 64, NULL, 4, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 02:32:03'),
(20, 'BAG20', 'SPKT', 'SPKT', 63, NULL, 10, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-04 02:32:03'),
(21, 'BAG21', 'SIUM', 'SIUM', 65, NULL, 6, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-03 23:09:55'),
(22, 'BAG22', 'SIKEU', 'SIKEU', 65, NULL, 2, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-03 23:09:55'),
(23, 'BAG23', 'SIDOKKES', 'SIDOKKES', 65, NULL, 8, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-03 23:09:55'),
(24, 'BAG24', 'SIWAS', 'SIWAS', 65, NULL, 5, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-03 23:09:55'),
(25, 'BAG25', 'SITIK', 'SITIK', 65, NULL, 7, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-03 23:09:55'),
(26, 'BAG26', 'SIKUM', 'SIKUM', 65, NULL, 3, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-03 23:09:55'),
(27, 'BAG27', 'SIPROPAM', 'SIPROPAM', 65, NULL, 1, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-03 23:09:55'),
(28, 'BAG28', 'SIHUMAS', 'SIHUMAS', 65, NULL, 4, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-03 23:09:55'),
(29, 'BAG29', 'BKO', 'BKO', 66, NULL, 1, 'bagian', 'level_2', NULL, 1, 0, NULL, NULL, '2026-04-02 16:46:44', '2026-04-03 23:09:55'),
(31, 'POLSEK_ONANRUNGGU', 'POLSEK ONANRUNGGU', NULL, 64, NULL, 2, 'unit', 'level_3', NULL, 1, 0, NULL, NULL, '2026-04-04 01:30:32', '2026-04-04 02:32:03'),
(32, 'PERS_MUTASI', 'PERS MUTASI', NULL, 66, NULL, 99, 'unit', 'level_3', NULL, 1, 0, NULL, NULL, '2026-04-04 01:30:32', '2026-04-04 01:30:32'),
(33, 'POL-HARIAN-BOHO', 'HARIAN BOHO', NULL, 64, NULL, 1, '', 'level_4', NULL, 1, 0, NULL, NULL, '2026-04-04 01:48:36', '2026-04-04 02:32:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `bagian_pimpinan`
--

CREATE TABLE `bagian_pimpinan` (
  `id` int(11) NOT NULL,
  `bagian_id` int(11) NOT NULL,
  `personil_id` int(11) NOT NULL,
  `tanggal_mulai` date DEFAULT curdate(),
  `tanggal_selesai` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bagian_pimpinan`
--

INSERT INTO `bagian_pimpinan` (`id`, `bagian_id`, `personil_id`, `tanggal_mulai`, `tanggal_selesai`, `created_at`, `updated_at`) VALUES
(3, 20, 265, '2026-04-01', NULL, '2026-04-01 04:25:39', '2026-04-01 04:25:39'),
(5, 14, 326, '2026-04-01', NULL, '2026-04-01 04:39:14', '2026-04-01 04:39:14'),
(6, 6, 330, '2026-04-01', NULL, '2026-04-01 04:40:52', '2026-04-01 04:40:52'),
(7, 1, 256, '2026-04-01', NULL, '2026-04-01 04:43:48', '2026-04-01 04:43:48'),
(8, 5, 294, '2026-04-01', NULL, '2026-04-01 04:43:55', '2026-04-01 04:43:55'),
(9, 2, 258, '2026-04-01', NULL, '2026-04-01 04:44:04', '2026-04-01 04:44:04'),
(10, 3, 278, '2026-04-01', NULL, '2026-04-01 04:49:25', '2026-04-01 04:49:25'),
(11, 4, 281, '2026-04-01', NULL, '2026-04-01 04:57:08', '2026-04-01 04:57:08'),
(12, 24, 307, '2026-04-01', NULL, '2026-04-01 05:00:32', '2026-04-01 05:00:32'),
(13, 9, 423, '2026-04-01', NULL, '2026-04-01 05:00:43', '2026-04-01 05:00:43'),
(14, 11, 408, '2026-04-01', NULL, '2026-04-01 05:00:56', '2026-04-01 05:00:56'),
(15, 12, 446, '2026-04-01', NULL, '2026-04-01 05:01:23', '2026-04-01 05:01:23'),
(16, 7, 347, '2026-04-01', NULL, '2026-04-01 05:01:35', '2026-04-01 05:01:35'),
(17, 8, 382, '2026-04-01', NULL, '2026-04-01 05:01:53', '2026-04-01 05:01:53'),
(18, 10, 393, '2026-04-01', NULL, '2026-04-01 05:02:03', '2026-04-01 05:02:03'),
(19, 13, 451, '2026-04-01', NULL, '2026-04-01 05:02:14', '2026-04-01 05:02:14'),
(20, 21, 298, '2026-04-01', NULL, '2026-04-01 05:04:47', '2026-04-01 05:04:47'),
(21, 15, 455, '2026-04-01', NULL, '2026-04-01 05:07:14', '2026-04-01 05:07:14'),
(22, 18, 491, '2026-04-01', NULL, '2026-04-01 05:12:39', '2026-04-01 05:12:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `error_logs`
--

CREATE TABLE `error_logs` (
  `id` int(11) NOT NULL,
  `level` enum('DEBUG','INFO','WARNING','ERROR','CRITICAL') NOT NULL,
  `message` text NOT NULL,
  `file` varchar(500) DEFAULT NULL,
  `line` int(11) DEFAULT NULL,
  `function_name` varchar(100) DEFAULT NULL,
  `stack_trace` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `request_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jabatan`
--

CREATE TABLE `jabatan` (
  `id` int(11) NOT NULL,
  `kode_jabatan` varchar(50) NOT NULL,
  `nama_jabatan` varchar(100) NOT NULL,
  `nama_lengkap` varchar(200) DEFAULT NULL,
  `id_unsur` int(11) DEFAULT NULL,
  `id_bagian` int(11) DEFAULT NULL,
  `id_satuan_fungsi` int(11) DEFAULT NULL,
  `id_unit_pendukung` int(11) DEFAULT NULL,
  `id_status_jabatan` int(11) NOT NULL,
  `tingkat_jabatan` enum('struktural','fungsional','pelaksana','pendukung') NOT NULL,
  `level_eselon` enum('eselon_2','eselon_3','eselon_4','eselon_5','non_eselon') DEFAULT NULL,
  `is_pimpinan` tinyint(1) DEFAULT 0,
  `is_pembantu_pimpinan` tinyint(1) DEFAULT 0,
  `is_kepala_unit` tinyint(1) DEFAULT 0,
  `is_supervisor` tinyint(1) DEFAULT 0,
  `is_managerial` tinyint(1) DEFAULT 0,
  `is_operasional` tinyint(1) DEFAULT 0,
  `id_pangkat_minimal` int(11) DEFAULT NULL,
  `id_pangkat_maksimal` int(11) DEFAULT NULL,
  `masa_kerja_minimal_tahun` int(11) DEFAULT 0,
  `pendidikan_minimal` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status_penugasan` enum('definitif','ps','plt','pjs','plh','pj') DEFAULT 'definitif',
  `alasan_penugasan` text DEFAULT NULL,
  `tanggal_mulai_penugasan` date DEFAULT NULL,
  `tanggal_selesai_penugasan` date DEFAULT NULL,
  `pejabat_definitif_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jabatan`
--

INSERT INTO `jabatan` (`id`, `kode_jabatan`, `nama_jabatan`, `nama_lengkap`, `id_unsur`, `id_bagian`, `id_satuan_fungsi`, `id_unit_pendukung`, `id_status_jabatan`, `tingkat_jabatan`, `level_eselon`, `is_pimpinan`, `is_pembantu_pimpinan`, `is_kepala_unit`, `is_supervisor`, `is_managerial`, `is_operasional`, `id_pangkat_minimal`, `id_pangkat_maksimal`, `masa_kerja_minimal_tahun`, `pendidikan_minimal`, `deskripsi`, `is_active`, `is_deleted`, `created_by`, `updated_by`, `created_at`, `updated_at`, `status_penugasan`, `alasan_penugasan`, `tanggal_mulai_penugasan`, `tanggal_selesai_penugasan`, `pejabat_definitif_id`) VALUES
(763, 'KAPOLRES_SAMOSIR', 'KAPOLRES SAMOSIR', NULL, 61, 1, NULL, NULL, 1, 'struktural', NULL, 1, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(764, 'WAKAPOLRES', 'WAKAPOLRES', NULL, 61, 1, NULL, NULL, 1, 'struktural', NULL, 1, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(765, 'KABAG_OPS', 'KABAG OPS', NULL, 62, 2, NULL, NULL, 1, 'struktural', NULL, 0, 1, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(766, 'PS._PAUR_SUBBAGBINOPS', 'PS. PAUR SUBBAGBINOPS', NULL, 62, 2, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(767, 'BA_MIN_BAG_OPS', 'BA MIN BAG OPS', NULL, 62, 2, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(768, 'ASN_BAG_OPS', 'ASN BAG OPS', NULL, 62, 2, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(769, 'KA_SPKT', 'KA SPKT', NULL, 63, 20, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(770, 'PAMAPTA_1', 'PAMAPTA 1', NULL, 63, 20, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:24:28', 'definitif', NULL, NULL, NULL, NULL),
(771, 'PAMAPTA_2', 'PAMAPTA 2', NULL, 63, 20, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:24:28', 'definitif', NULL, NULL, NULL, NULL),
(772, 'PAMAPTA_3', 'PAMAPTA 3', NULL, 63, 20, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:24:28', 'definitif', NULL, NULL, NULL, NULL),
(773, 'BAMIN_PAMAPTA_2', 'BAMIN PAMAPTA 2', NULL, 63, 20, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:24:28', 'definitif', NULL, NULL, NULL, NULL),
(774, 'BAMIN_PAMAPTA_3', 'BAMIN PAMAPTA 3', NULL, 63, 20, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:24:28', 'definitif', NULL, NULL, NULL, NULL),
(775, 'BAMIN_PAMAPTA_1', 'BAMIN PAMAPTA 1', NULL, 63, 20, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:24:28', 'definitif', NULL, NULL, NULL, NULL),
(776, 'PAURSUBBAGPROGAR', 'PAURSUBBAGPROGAR', NULL, 62, 3, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(777, 'BA_MIN_BAG_REN', 'BA MIN BAG REN', NULL, 62, 3, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(778, 'PS._KABAG_SDM', 'PS. KABAG SDM', NULL, 62, 4, NULL, NULL, 1, 'struktural', NULL, 0, 1, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(779, 'PAURSUBBAGBINKAR', 'PAURSUBBAGBINKAR', NULL, 62, 4, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(780, 'BA_MIN_BAG_SDM', 'BA MIN BAG SDM', NULL, 62, 4, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(781, 'BA_POLRES_SAMOSIR', 'BA POLRES SAMOSIR', NULL, 62, 4, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(782, 'ADCKAPOLRES', 'ADC KAPOLRES', NULL, 66, 29, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:32:41', 'definitif', NULL, NULL, NULL, NULL),
(783, 'BINTARASATLANTAS', 'BINTARA SATLANTAS', NULL, 62, 4, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(784, 'PLT._KASUBBAGBEKPAL', 'Plt. KASUBBAGBEKPAL', NULL, 62, 5, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 00:20:02', 'definitif', NULL, NULL, NULL, NULL),
(785, 'BA_MIN_BAG_LOG', 'BA MIN BAG LOG', NULL, 62, 5, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(786, 'PS._KASIUM', 'PS. KASIUM', NULL, 65, 21, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(787, 'BINTARA_SIUM', 'BINTARA SIUM', NULL, 65, 21, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(788, 'PS._KASIKEU', 'PS. KASIKEU', NULL, 65, 22, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(789, 'BINTARA_SIKEU', 'BINTARA SIKEU', NULL, 65, 22, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(790, 'KASIDOKKES', 'KASIDOKKES', NULL, 65, 23, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(791, 'BA_SIDOKKES', 'BA SIDOKKES', NULL, 65, 23, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(792, 'PLT._KASIWAS', 'Plt. KASIWAS', NULL, 65, 24, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(793, 'BINTARA_SIWAS', 'BINTARA SIWAS', NULL, 65, 24, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(794, 'BINTARA_SITIK', 'BINTARA SITIK', NULL, 65, 25, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(795, 'KASUBSIBANKUM', 'KASUBSIBANKUM', NULL, 65, 26, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(796, 'BINTARA_SIKUM', 'BINTARA SIKUM', NULL, 65, 26, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(797, 'PS._KASIPROPAM', 'PS. KASIPROPAM', NULL, 65, 27, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(798, 'PS._KANIT_PROPOS', 'PS. KANIT PROPOS', NULL, 65, 27, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(799, 'PS._KANIT_PAMINAL', 'PS. KANIT PAMINAL', NULL, 65, 27, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(800, 'BINTARA_SIPROPAM', 'BINTARA SIPROPAM', NULL, 65, 27, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(801, 'BINTARA_SIHUMAS', 'BINTARA SIHUMAS', NULL, 65, 28, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(802, 'KAURBINOPS', 'KAURBINOPS', NULL, 63, 14, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(803, 'BINTARA_SAT_BINMAS', 'BINTARA SAT BINMAS', NULL, 63, 14, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(804, 'PS._KASAT_INTELKAM', 'PS. KASAT INTELKAM', NULL, 63, 6, NULL, NULL, 1, 'struktural', NULL, 0, 1, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(805, 'PS._KAURMINTU', 'PS. KAURMINTU', NULL, 63, 6, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(806, 'PS._KANIT_3', 'PS. KANIT 3', NULL, 63, 6, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(807, 'PS._KANIT_1', 'PS. KANIT 1', NULL, 63, 6, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(808, 'PS._KANIT_2', 'PS. KANIT 2', NULL, 63, 6, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(809, 'BINTARA_SAT_INTELKAM', 'BINTARA SAT INTELKAM', NULL, 63, 6, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(810, 'BINTARASATINTELKAM', 'BINTARA SATINTELKAM', NULL, 63, 6, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:17:29', 'definitif', NULL, NULL, NULL, NULL),
(811, 'KASAT_RESKRIM', 'KASAT RESKRIM', NULL, 63, 7, NULL, NULL, 1, 'struktural', NULL, 0, 1, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(812, 'KANITIDIK3', 'KANITIDIK 3', NULL, 63, 7, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:21:06', 'definitif', NULL, NULL, NULL, NULL),
(813, 'KANITIDIK_4', 'KANITIDIK 4', NULL, 63, 7, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(814, 'KANITIDIK1', 'KANITIDIK 1', NULL, 63, 7, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:18:11', 'definitif', NULL, NULL, NULL, NULL),
(815, 'KANITIDIK_5', 'KANITIDIK 5', NULL, 63, 7, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(816, 'PS._KANITIDIK_2', 'PS. KANITIDIK 2', NULL, 63, 7, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(817, 'PS._KANIT_IDENTIFIKASI', 'PS. KANIT IDENTIFIKASI', NULL, 63, 7, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(818, 'BINTARA_SAT_RESKRIM', 'BINTARA SAT RESKRIM', NULL, 63, 7, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(819, 'KASATRESNARKOBA', 'KASATRESNARKOBA', NULL, 63, 8, NULL, NULL, 1, 'struktural', NULL, 0, 1, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:18:29', 'definitif', NULL, NULL, NULL, NULL),
(820, 'PSKANITIDIK1', 'PS.KANIT IDIK 1', NULL, 63, 8, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:19:15', 'definitif', NULL, NULL, NULL, NULL),
(821, 'BINTARASATRESNARKOBA', 'BINTARA SATRESNARKOBA', NULL, 63, 8, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:20:27', 'definitif', NULL, NULL, NULL, NULL),
(822, 'KASAT_SAMAPTA', 'KASAT SAMAPTA', NULL, 63, 10, NULL, NULL, 1, 'struktural', NULL, 0, 1, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(823, 'PS._KAURBINOPS', 'PS. KAURBINOPS', NULL, 63, 10, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(824, 'PS._KANIT_DALMAS_2', 'PS. KANIT DALMAS 2', NULL, 63, 10, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(825, 'PS._KANIT_TURJAWALI', 'PS. KANIT TURJAWALI', NULL, 63, 10, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(826, 'BINTARA_SAT_SAMAPTA', 'BINTARA SAT SAMAPTA', NULL, 63, 10, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(827, 'KASAT_PAMOBVIT', 'KASAT PAMOBVIT', NULL, 63, 11, NULL, NULL, 1, 'struktural', NULL, 0, 1, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(828, 'PS._KANITPAMWASTER', 'PS. KANITPAMWASTER', NULL, 63, 11, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(829, 'PS._KANITPAMWISATA', 'PS. KANITPAMWISATA', NULL, 63, 11, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(830, 'PSPANITPAMWASTER', 'PS. PANIT PAMWASTER', NULL, 63, 11, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:21:40', 'definitif', NULL, NULL, NULL, NULL),
(831, 'BINTARA_SAT_PAMOBVIT', 'BINTARA SAT PAMOBVIT', NULL, 63, 11, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(832, 'KASAT_LANTAS', 'KASAT LANTAS', NULL, 63, 9, NULL, NULL, 1, 'struktural', NULL, 0, 1, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(833, 'KANITREGIDENT_LANTAS', 'KANITREGIDENT LANTAS', NULL, 63, 9, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(834, 'PS._KANITGAKKUM', 'PS. KANITGAKKUM', NULL, 63, 9, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(835, 'PS._KANITTURJAWALI', 'PS. KANITTURJAWALI', NULL, 63, 9, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(836, 'PS._KANITKAMSEL', 'PS. KANITKAMSEL', NULL, 63, 9, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(837, 'BINTARA_SAT_LANTAS', 'BINTARA SAT LANTAS', NULL, 63, 9, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(838, 'KASAT_POLAIRUD', 'KASAT POLAIRUD', NULL, 63, 12, NULL, NULL, 1, 'struktural', NULL, 0, 1, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(839, 'PS._KANITPATROLI', 'PS. KANITPATROLI', NULL, 63, 12, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(840, 'BINTARASATPOLAIRUD', 'BINTARA SATPOLAIRUD', NULL, 63, 12, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:17:57', 'definitif', NULL, NULL, NULL, NULL),
(841, 'PS._KASAT_TAHTI', 'PS. KASAT TAHTI', NULL, 63, 13, NULL, NULL, 1, 'struktural', NULL, 0, 1, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(842, 'BINTARA_SAT_TAHTI', 'BINTARA SAT TAHTI', NULL, 63, 13, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(843, 'PS._KAPOLSEK_HARIAN_BOHO', 'PS. KAPOLSEK HARIAN BOHO', NULL, 64, 33, NULL, NULL, 1, 'struktural', NULL, 0, 1, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:24:28', 'definitif', NULL, NULL, NULL, NULL),
(844, 'PS._KANIT_INTELKAM', 'PS. KANIT INTELKAM', NULL, 64, 33, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(845, 'PS._KANIT_BINMAS', 'PS. KANIT BINMAS', NULL, 64, 33, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(846, 'PS._KANIT_RESKRIM', 'PS. KANIT RESKRIM', NULL, 64, 33, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(847, 'PS.KANIT_SAMAPTA', 'PS.KANIT SAMAPTA', NULL, 64, 33, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(848, 'BINTARAPOLSEK', 'BINTARA POLSEK', NULL, 64, 33, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:24:28', 'definitif', NULL, NULL, NULL, NULL),
(849, 'KAPOLSEK_PALIPI', 'KAPOLSEK PALIPI', NULL, 64, 16, NULL, NULL, 1, 'struktural', NULL, 0, 1, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(850, 'PS._KA_SPKT_1', 'PS. KA SPKT 1', NULL, 64, 16, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(851, 'PS._KANIT_SAMAPTA', 'PS. KANIT SAMAPTA', NULL, 64, 16, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(852, 'PS._KA_SPKT_2', 'PS. KA SPKT 2', NULL, 64, 16, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(853, 'BINTARA__POLSEK', 'BINTARA  POLSEK', NULL, 64, 16, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(854, 'PS._KAPOLSEK_SIMANINDO', 'PS. KAPOLSEK SIMANINDO', NULL, 64, 17, NULL, NULL, 1, 'struktural', NULL, 0, 1, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(855, 'KANIT_RESKRIM', 'KANIT RESKRIM', NULL, 64, 17, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(856, 'PS._KANITPROPAM', 'PS. KANITPROPAM', NULL, 64, 17, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(857, 'PS._KA_SPKT_3', 'PS. KA SPKT 3', NULL, 64, 17, NULL, NULL, 1, 'struktural', NULL, 0, 0, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(858, 'KASIHUMAS', 'KASIHUMAS', NULL, 64, 31, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(859, 'KAPOLSEK_PANGURURAN', 'KAPOLSEK PANGURURAN', NULL, 64, 19, NULL, NULL, 1, 'struktural', NULL, 0, 1, 1, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(860, 'SUPIRWAKAPOLRES', 'SUPIR WAKAPOLRES', NULL, 66, NULL, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-03 23:29:00', 'definitif', NULL, NULL, NULL, NULL),
(861, 'CONTOH1', 'contoh1', NULL, 62, 2, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 1, NULL, NULL, '2026-04-04 17:56:07', '2026-04-04 18:06:32', 'definitif', NULL, NULL, NULL, NULL),
(863, 'BINTARAPOLSEKONANRUN', 'BINTARA POLSEK ONANRUNGGU', NULL, 64, 31, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:05', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(864, 'BINTARAPOLSEKPANGURU', 'BINTARA POLSEK PANGURURAN', NULL, 64, 19, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:05', '2026-04-04 18:27:05', 'definitif', NULL, NULL, NULL, NULL),
(865, 'BINTARAPOLSEKSIMANIN', 'BINTARA POLSEK SIMANINDO', NULL, 64, 17, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:06', '2026-04-04 18:27:06', 'definitif', NULL, NULL, NULL, NULL),
(867, 'BINTARASATLANTASLANT', 'BINTARA SATLANTAS LANTAS', NULL, 63, 9, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(868, 'KAURBINOPSINTELKAM', 'KAURBINOPS INTELKAM', NULL, 63, 6, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(869, 'KAURBINOPSRESNARKOBA', 'KAURBINOPS RESNARKOBA', NULL, 63, 8, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(870, 'PSKASPKT1SIMANINDO', 'PS. KA SPKT 1 SIMANINDO', NULL, 64, 17, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(871, 'PSKANITBINMASONANRUN', 'PS. KANIT BINMAS ONANRUNGGU', NULL, 64, 31, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(872, 'PSKANITBINMASPALIPI', 'PS. KANIT BINMAS PALIPI', NULL, 64, 16, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(873, 'PSKANITBINMASPANGURU', 'PS. KANIT BINMAS PANGURURAN', NULL, 64, 19, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(874, 'PSKANITBINMASSIMANIN', 'PS. KANIT BINMAS SIMANINDO', NULL, 64, 17, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(875, 'PSKANITINTELKAMONANR', 'PS. KANIT INTELKAM ONANRUNGGU', NULL, 64, 31, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(876, 'PSKANITINTELKAMPALIP', 'PS. KANIT INTELKAM PALIPI', NULL, 64, 16, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(877, 'PSKANITINTELKAMPANGU', 'PS. KANIT INTELKAM PANGURURAN', NULL, 64, 19, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(878, 'PSKANITINTELKAMSIMAN', 'PS. KANIT INTELKAM SIMANINDO', NULL, 64, 17, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(879, 'PSKANITRESKRIMONANRU', 'PS. KANIT RESKRIM ONANRUNGGU', NULL, 64, 31, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(880, 'PSKANITRESKRIMPALIPI', 'PS. KANIT RESKRIM PALIPI', NULL, 64, 16, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(881, 'PSKANITRESKRIMPANGUR', 'PS. KANIT RESKRIM PANGURURAN', NULL, 64, 19, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(882, 'PSKANITSAMAPTAONANRU', 'PS. KANIT SAMAPTA ONANRUNGGU', NULL, 64, 31, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:29:56', 'definitif', NULL, NULL, NULL, NULL),
(883, 'PSKANITSAMAPTAPANGUR', 'PS. KANIT SAMAPTA PANGURURAN', NULL, 64, 19, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(884, 'PSKANITSAMAPTASIMANI', 'PS. KANIT SAMAPTA SIMANINDO', NULL, 64, 17, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(885, 'PSKASIUMPALIPI', 'PS. KASIUM PALIPI', NULL, 64, 16, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(886, 'PSKASIUMSIMANINDO', 'PS. KASIUM SIMANINDO', NULL, 64, 17, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(887, 'PSKAURMINTUPAMOBVIT', 'PS. KAURMINTU PAMOBVIT', NULL, 63, 11, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(888, 'PSKAURMINTUPOLAIRUD', 'PS. KAURMINTU POLAIRUD', NULL, 63, 12, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(889, 'PSKAURMINTURESKRIM', 'PS. KAURMINTU RESKRIM', NULL, 63, 7, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:27:56', '2026-04-04 18:27:56', 'definitif', NULL, NULL, NULL, NULL),
(890, 'BAPOLRESSAMOSIRBKO', 'BA POLRES SAMOSIR BKO', NULL, 66, 29, NULL, NULL, 1, 'struktural', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, 1, 0, NULL, NULL, '2026-04-04 18:28:06', '2026-04-04 18:28:06', 'definitif', NULL, NULL, NULL, NULL);

--
-- Trigger `jabatan`
--
DELIMITER $$
CREATE TRIGGER `jabatan_audit_delete` AFTER DELETE ON `jabatan` FOR EACH ROW BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values, user_id, ip_address)
    VALUES ('jabatan', OLD.id, 'DELETE', JSON_OBJECT(
        'nama_jabatan', OLD.nama_jabatan,
        'id_bagian', OLD.id_bagian,
        'level_eselon', OLD.level_eselon
    ), @current_user_id, @client_ip);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `jabatan_audit_insert` AFTER INSERT ON `jabatan` FOR EACH ROW BEGIN
    INSERT INTO audit_log (table_name, record_id, action, new_values, user_id, ip_address)
    VALUES ('jabatan', NEW.id, 'INSERT', JSON_OBJECT(
        'nama_jabatan', NEW.nama_jabatan,
        'id_bagian', NEW.id_bagian,
        'level_eselon', NEW.level_eselon
    ), @current_user_id, @client_ip);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `jabatan_audit_update` AFTER UPDATE ON `jabatan` FOR EACH ROW BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values, new_values, user_id, ip_address)
    VALUES ('jabatan', NEW.id, 'UPDATE', 
        JSON_OBJECT('nama_jabatan', OLD.nama_jabatan, 'id_bagian', OLD.id_bagian, 'level_eselon', OLD.level_eselon),
        JSON_OBJECT('nama_jabatan', NEW.nama_jabatan, 'id_bagian', NEW.id_bagian, 'level_eselon', NEW.level_eselon),
        @current_user_id, @client_ip);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenjang_karir`
--

CREATE TABLE `jenjang_karir` (
  `id` int(11) NOT NULL,
  `id_pangkat_saat_ini` int(11) NOT NULL,
  `id_pangkat_berikutnya` int(11) NOT NULL,
  `masa_kerja_minimal_tahun` int(11) NOT NULL,
  `masa_kerja_minimal_bulan` int(11) DEFAULT 0,
  `persyaratan` text DEFAULT NULL,
  `is_mandatory` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jenjang_karir`
--

INSERT INTO `jenjang_karir` (`id`, `id_pangkat_saat_ini`, `id_pangkat_berikutnya`, `masa_kerja_minimal_tahun`, `masa_kerja_minimal_bulan`, `persyaratan`, `is_mandatory`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 2, 0, 'Lulus pendidikan dan penilaian kinerja', 1, 1, '2026-04-02 16:23:39', '2026-04-02 16:23:39'),
(2, 2, 3, 3, 0, 'Lulus pendidikan dan penilaian kinerja', 1, 1, '2026-04-02 16:23:39', '2026-04-02 16:23:39'),
(3, 3, 4, 4, 0, 'Lulus pendidikan dan penilaian kinerja', 1, 1, '2026-04-02 16:23:39', '2026-04-02 16:23:39'),
(4, 4, 5, 4, 0, 'Lulus SEKPA dan penilaian kinerja', 1, 1, '2026-04-02 16:23:39', '2026-04-02 16:23:39'),
(5, 5, 6, 3, 0, 'Lulus pendidikan dan penilaian kinerja', 1, 1, '2026-04-02 16:23:39', '2026-04-02 16:23:39'),
(6, 6, 7, 3, 0, 'Lulus pendidikan dan penilaian kinerja', 1, 1, '2026-04-02 16:23:39', '2026-04-02 16:23:39'),
(7, 7, 8, 4, 0, 'Lulus DIKJUR dan penilaian kinerja', 1, 1, '2026-04-02 16:23:39', '2026-04-02 16:23:39'),
(8, 8, 9, 4, 0, 'Lulus pendidikan dan penilaian kinerja', 1, 1, '2026-04-02 16:23:39', '2026-04-02 16:23:39'),
(9, 9, 10, 4, 0, 'Lulus pendidikan dan penilaian kinerja', 1, 1, '2026-04-02 16:23:39', '2026-04-02 16:23:39'),
(10, 10, 11, 5, 0, 'Lulus SESPIM dan penilaian kinerja', 1, 1, '2026-04-02 16:23:39', '2026-04-02 16:23:39'),
(11, 11, 12, 5, 0, 'Lulus pendidikan dan penilaian kinerja', 1, 1, '2026-04-02 16:23:39', '2026-04-02 16:23:39'),
(12, 12, 13, 5, 0, 'Lulus pendidikan dan penilaian kinerja', 1, 1, '2026-04-02 16:23:39', '2026-04-02 16:23:39'),
(13, 13, 14, 6, 0, 'Lulus pendidikan dan penilaian kinerja', 1, 1, '2026-04-02 16:23:39', '2026-04-02 16:23:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `master_alasan_penugasan`
--

CREATE TABLE `master_alasan_penugasan` (
  `id` int(11) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kategori` enum('proses_mutasi','pendidikan','berhalangan','jabatan_kosong','tugas_khusus','lainnya') NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `durasi_rekomendasi_bulan` int(11) DEFAULT NULL,
  `requires_sk` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `master_alasan_penugasan`
--

INSERT INTO `master_alasan_penugasan` (`id`, `kode`, `nama`, `kategori`, `deskripsi`, `durasi_rekomendasi_bulan`, `requires_sk`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'MUTASI', 'Proses Mutasi', 'proses_mutasi', 'Pejabat sedang dalam proses mutasi ke jabatan lain', 6, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(2, 'SELEKSI', 'Proses Seleksi', 'proses_mutasi', 'Jabatan sedang dalam proses seleksi pengganti', 12, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(3, 'PROMOSI', 'Proses Promosi', 'proses_mutasi', 'Pejabat sedang dalam proses promosi jabatan', 3, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(4, 'DIKJUR', 'DIKJUR', 'pendidikan', 'Pendidikan Jurusan', 6, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(5, 'SEKPA', 'SEKPA', 'pendidikan', 'Sekolah Polisi Negara', 9, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(6, 'SESPIM', 'SESPIM', 'pendidikan', 'Sekolah Staf dan Pimpinan', 6, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(7, 'DIKLAG', 'DIKLAG', 'pendidikan', 'Pendidikan Guru', 3, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(8, 'DIKLUAR', 'Diklat Luar Negeri', 'pendidikan', 'Pendidikan di luar negeri', 12, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(9, 'SAKIT', 'Sakit', 'berhalangan', 'Pejabat sedang sakit', 3, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(10, 'CUTI', 'Cuti', 'berhalangan', 'Pejabat sedang cuti', 2, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(11, 'CUTI_BESAR', 'Cuti Besar', 'berhalangan', 'Pejabat sedang cuti besar', 12, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(12, 'TUGAS_KHUSUS', 'Tugas Khusus', 'berhalangan', 'Pejabat sedang tugas khusus', 6, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(13, 'DINAS_LUAR', 'Dinas Luar', 'berhalangan', 'Pejabat sedang dinas luar kota', 1, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(14, 'PENSIUN', 'Pensiun', 'jabatan_kosong', 'Pejabat definitif telah pensiun', 12, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(15, 'BERHENTIKAN', 'Diberhentikan', 'jabatan_kosong', 'Pejabat definitif diberhentikan', 6, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(16, 'MENINGGAL', 'Meninggal Dunia', 'jabatan_kosong', 'Pejabat definitif meninggal dunia', 3, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(17, 'UNIT_BARU', 'Unit Baru', 'jabatan_kosong', 'Unit kerja baru dibentuk', 12, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(18, 'OPERASI', 'Operasi Khusus', 'tugas_khusus', 'Tugas operasi khusus', 3, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(19, 'PENGAMANAN', 'Pengamanan Khusus', 'tugas_khusus', 'Tugas pengamanan khusus', 2, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(20, 'INVESTIGASI', 'Investigasi', 'tugas_khusus', 'Tugas investigasi khusus', 6, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(21, 'REORGANISASI', 'Reorganisasi', 'lainnya', 'Proses reorganisasi struktur', 6, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(22, 'AUDIT', 'Audit Internal', 'lainnya', 'Proses audit internal', 3, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(23, 'LAINNYA', 'Lainnya', 'lainnya', 'Alasan lainnya', 1, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `master_jenis_pegawai`
--

CREATE TABLE `master_jenis_pegawai` (
  `id` int(11) NOT NULL,
  `kode_jenis` varchar(20) NOT NULL,
  `nama_jenis` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kategori` enum('POLRI','ASN','P3K','HONORARIUM','KONTRAK','LAINNYA') DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `master_jenis_pegawai`
--

INSERT INTO `master_jenis_pegawai` (`id`, `kode_jenis`, `nama_jenis`, `deskripsi`, `kategori`, `urutan`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'POLRI', 'POLRI Aktif', 'Anggota Polri Republik Indonesia yang aktif', 'POLRI', 1, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(2, 'POLRI_PENSIUN', 'POLRI Pensiun', 'Anggota POLRI yang sudah pensiun', 'POLRI', 2, 0, '2026-03-28 18:56:11', '2026-04-04 17:44:15'),
(3, 'POLRI_DIK', 'POLRI Dalam Pendidikan', 'Anggota POLRI yang sedang menjalani pendidikan', 'POLRI', 3, 0, '2026-03-28 18:56:11', '2026-04-04 17:44:20'),
(4, 'ASN', 'Aparatur Sipil Negara', 'Pegawai negeri sipil', 'ASN', 10, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(5, 'ASN_HONORARIUM', 'ASN Honorarium', 'ASN dengan status honorarium', 'ASN', 11, 0, '2026-03-28 18:56:11', '2026-04-04 18:10:36'),
(6, 'ASN_KONTRAK', 'ASN Kontrak', 'ASN dengan status kontrak', 'ASN', 12, 0, '2026-03-28 18:56:11', '2026-04-04 18:10:34'),
(7, 'P3K', 'Pegawai Pemerintah dengan Perjanjian Kerja', 'P3K sesuai PP No. 49 Tahun 2018', 'P3K', 20, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(8, 'P3K_TAHUNAN', 'P3K Tahunan', 'P3K dengan kontrak tahunan', 'P3K', 21, 0, '2026-03-28 18:56:11', '2026-04-04 18:10:44'),
(9, 'P3K_BULANAN', 'P3K Bulanan', 'P3K dengan kontrak bulanan', 'P3K', 22, 0, '2026-03-28 18:56:11', '2026-04-04 18:10:42'),
(10, 'HONORARIUM', 'Tenaga Honorarium', 'Tenaga ahli dengan status honorarium', 'HONORARIUM', 30, 0, '2026-03-28 18:56:11', '2026-04-04 18:10:50'),
(11, 'KONTRAK', 'Tenaga Kontrak', 'Tenaga dengan status kontrak', 'KONTRAK', 31, 0, '2026-03-28 18:56:11', '2026-04-04 18:10:52'),
(12, 'LAINNYA', 'Magang', 'Tenaga magang/internship', 'LAINNYA', 40, 0, '2026-03-28 18:56:11', '2026-04-04 18:10:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `master_jenis_penugasan`
--

CREATE TABLE `master_jenis_penugasan` (
  `id` int(11) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kategori` enum('sementara','definitif','berhalangan') NOT NULL,
  `level_minimal` enum('eselon_2','eselon_3','eselon_4','eselon_5','semua_level') NOT NULL,
  `durasi_maximal_bulan` int(11) DEFAULT 12,
  `kewenangan` enum('penuh','operasional','terbatas','harian') NOT NULL,
  `persentase_maximal` decimal(5,2) DEFAULT 15.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `master_jenis_penugasan`
--

INSERT INTO `master_jenis_penugasan` (`id`, `kode`, `nama`, `nama_lengkap`, `deskripsi`, `kategori`, `level_minimal`, `durasi_maximal_bulan`, `kewenangan`, `persentase_maximal`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'DEF', 'Definitif', 'Pejabat Definitif', 'Pejabat yang telah ditetapkan secara resmi dengan SK pengangkatan tetap', 'definitif', 'semua_level', NULL, 'penuh', 100.00, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(2, 'PS', 'PS', 'Pejabat Sementara', 'Pejabat yang mengisi jabatan kosong sementara karena pejabat definitif sedang dalam proses seleksi', 'sementara', 'eselon_3', 12, 'operasional', 15.00, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(3, 'PLT', 'Plt', 'Pelaksana Tugas', 'Pejabat definitif yang berhalangan tetap dan digantikan sementara', 'berhalangan', 'semua_level', 24, 'penuh', NULL, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(4, 'PJS', 'Pjs', 'Pejabat Sementara', 'Pejabat sementara untuk jabatan level tinggi yang kosong', 'sementara', 'eselon_2', 6, 'terbatas', 5.00, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(5, 'PLH', 'Plh', 'Pelaksana Harian', 'Pelaksana harian untuk kekosongan sangat singkat', 'berhalangan', 'semua_level', 1, 'harian', NULL, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(6, 'PJ', 'Pj', 'Penjabat', 'Penjabat untuk jabatan struktural yang kosong permanen', 'sementara', 'eselon_3', 12, 'operasional', 10.00, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `master_pangkat_minimum_jabatan`
--

CREATE TABLE `master_pangkat_minimum_jabatan` (
  `id` int(11) NOT NULL,
  `id_jabatan` int(11) NOT NULL,
  `id_pangkat_minimal` int(11) NOT NULL,
  `id_pangkat_maksimal` int(11) DEFAULT NULL,
  `is_strict` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `master_pendidikan`
--

CREATE TABLE `master_pendidikan` (
  `id` int(11) NOT NULL,
  `tingkat_pendidikan` enum('SD','SMP','SMA','D1','D2','D3','D4','S1','S2','S3','LAINNYA') DEFAULT NULL,
  `nama_pendidikan` varchar(100) NOT NULL,
  `kode_pendidikan` varchar(20) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `master_pendidikan`
--

INSERT INTO `master_pendidikan` (`id`, `tingkat_pendidikan`, `nama_pendidikan`, `kode_pendidikan`, `deskripsi`, `urutan`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'SD', 'Sekolah Dasar', 'SD', NULL, 1, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(2, 'SMP', 'Sekolah Menengah Pertama', 'SMP', NULL, 2, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(3, 'SMA', 'Sekolah Menengah Atas', 'SMA', NULL, 3, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(4, 'D1', 'Diploma Satu', 'D1', NULL, 4, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(5, 'D2', 'Diploma Dua', 'D2', NULL, 5, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(6, 'D3', 'Diploma Tiga', 'D3', NULL, 6, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(7, 'D4', 'Diploma Empat', 'D4', NULL, 7, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(8, 'S1', 'Strata Satu', 'S1', NULL, 8, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(9, 'S2', 'Strata Dua', 'S2', NULL, 9, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(10, 'S3', 'Strata Tiga', 'S3', NULL, 10, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(11, 'LAINNYA', 'Lain-lain', 'LAINNYA', NULL, 11, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `master_satuan_fungsi`
--

CREATE TABLE `master_satuan_fungsi` (
  `id` int(11) NOT NULL,
  `kode_satuan` varchar(20) NOT NULL,
  `nama_satuan` varchar(100) NOT NULL,
  `nama_lengkap` varchar(200) NOT NULL,
  `kategori` enum('satfung','bagian','seksi','subseksi') NOT NULL,
  `level_satuan` enum('polda','polres','polsek') NOT NULL,
  `is_struktural` tinyint(1) DEFAULT 1,
  `is_fungsional` tinyint(1) DEFAULT 0,
  `is_pimpinan` tinyint(1) DEFAULT 0,
  `is_supervisor` tinyint(1) DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `master_satuan_fungsi`
--

INSERT INTO `master_satuan_fungsi` (`id`, `kode_satuan`, `nama_satuan`, `nama_lengkap`, `kategori`, `level_satuan`, `is_struktural`, `is_fungsional`, `is_pimpinan`, `is_supervisor`, `deskripsi`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'RESKRIM', 'RESKRIM', 'Satuan Reserse Kriminal', 'satfung', 'polres', 1, 1, 0, 1, 'Satuan yang menangani penanganan perkara pidana umum', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45'),
(2, 'INTELKAM', 'INTELKAM', 'Satuan Intelijen Keamanan', 'satfung', 'polres', 1, 1, 0, 1, 'Satuan yang menangani intelijen dan keamanan', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45'),
(3, 'LANTAS', 'LANTAS', 'Satuan Lalu Lintas', 'satfung', 'polres', 1, 1, 0, 1, 'Satuan yang menangani lalu lintas dan keamanan jalan', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45'),
(4, 'SAMAPTA', 'SAMAPTA', 'Satuan Pengamanan Masyarakat', 'satfung', 'polres', 1, 1, 0, 1, 'Satuan yang melakukan pengamanan masyarakat', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45'),
(5, 'RESNARKOBA', 'RESNARKOBA', 'Satuan Reserse Narkoba', 'satfung', 'polres', 1, 1, 0, 1, 'Satuan yang menangani perkara narkotika dan psikotropika', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45'),
(6, 'PAMOBVIT', 'PAMOBVIT', 'Satuan Pengamanan Objek Vital', 'satfung', 'polres', 1, 1, 0, 1, 'Satuan yang mengamankan objek vital penting', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45'),
(7, 'POLAIRUD', 'POLAIRUD', 'Satuan Polisi Air dan Udara', 'satfung', 'polres', 1, 1, 0, 1, 'Satuan yang menangani patroli air dan udara', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45'),
(8, 'BINMAS', 'BINMAS', 'Satuan Pembinaan Masyarakat', 'satfung', 'polres', 1, 1, 0, 1, 'Satuan yang membina masyarakat', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45'),
(9, 'TAHTI', 'TAHTI', 'Satuan Tata Usaha', 'satfung', 'polres', 1, 1, 0, 1, 'Satuan yang mengurus administrasi dan tata usaha', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45'),
(10, 'SPKT', 'SPKT', 'Sentra Pelayanan Kepolisian Terpadu', 'bagian', 'polres', 1, 0, 0, 1, 'Pusat pelayanan kepolisian terpadu untuk masyarakat', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `master_status_jabatan`
--

CREATE TABLE `master_status_jabatan` (
  `id` int(11) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kategori` enum('struktural','fungsional','pelaksana','pendukung') NOT NULL,
  `level_eselon` enum('eselon_2','eselon_3','eselon_4','eselon_5','non_eselon') NOT NULL,
  `is_definitif` tinyint(1) DEFAULT 1,
  `is_managerial` tinyint(1) DEFAULT 0,
  `is_supervisor` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `master_status_jabatan`
--

INSERT INTO `master_status_jabatan` (`id`, `kode`, `nama`, `nama_lengkap`, `deskripsi`, `kategori`, `level_eselon`, `is_definitif`, `is_managerial`, `is_supervisor`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'KAPOLRES', 'KAPOLRES', 'Kepala Kepolisian Resort', 'Pimpinan tertinggi di tingkat Polres', 'struktural', 'eselon_2', 1, 1, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(2, 'WAKAPOLRES', 'WAKAPOLRES', 'Wakil Kepala Kepolisian Resort', 'Wakil pimpinan tertinggi di tingkat Polres', 'struktural', 'eselon_2', 1, 1, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(3, 'KABAG', 'KABAG', 'Kepala Bagian', 'Pimpinan bagian di unsur pembantu pimpinan', 'struktural', 'eselon_3', 1, 1, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(4, 'KASAT', 'KASAT', 'Kepala Satuan', 'Pimpinan satuan fungsi', 'struktural', 'eselon_3', 1, 1, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(5, 'KAPOLSEK', 'KAPOLSEK', 'Kepala Kepolisian Sektor', 'Pimpinan di tingkat Polsek', 'struktural', 'eselon_3', 1, 1, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(6, 'KASUBBAG', 'KASUBBAG', 'Kepala Sub Bagian', 'Pimpinan sub bagian', 'struktural', 'eselon_4', 1, 1, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(7, 'KASUBSAT', 'KASUBSAT', 'Kepala Sub Satuan', 'Pimpinan sub satuan', 'struktural', 'eselon_4', 1, 1, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(8, 'PS_KAPOLSEK', 'PS. KAPOLSEK', 'Pejabat Sementara Kapolsek', 'Pejabat sementara kepala polsek', 'struktural', 'eselon_4', 0, 1, 1, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(9, 'KANIT', 'KANIT', 'Kepala Unit', 'Pimpinan unit', 'struktural', 'eselon_5', 1, 1, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(10, 'KAUR', 'KAUR', 'Kepala Urusan', 'Pimpinan urusan', 'struktural', 'eselon_5', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(11, 'KA_SPKT', 'KA SPKT', 'Kepala Sentra Pelayanan Kepolisian Terpadu', 'Pimpinan SPKT', 'struktural', 'non_eselon', 1, 1, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(12, 'PS_KANIT', 'PS. KANIT', 'Pejabat Sementara Kepala Unit', 'Pejabat sementara kepala unit', 'struktural', 'non_eselon', 0, 1, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(13, 'PAMAPTA', 'PAMAPTA', 'Pengamanan Masyarakat', 'Personel pengamanan masyarakat', 'fungsional', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(14, 'RESKRIM', 'RESKRIM', 'Reserse Kriminal', 'Personel reserse kriminal', 'fungsional', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(15, 'INTELKAM', 'INTELKAM', 'Intelijen Keamanan', 'Personel intelijen keamanan', 'fungsional', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(16, 'LANTAS', 'LANTAS', 'Lalu Lintas', 'Personel lalu lintas', 'fungsional', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(17, 'BINMAS', 'BINMAS', 'Pembinaan Masyarakat', 'Personel pembinaan masyarakat', 'fungsional', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(18, 'POLAIRUD', 'POLAIRUD', 'Polisi Air dan Udara', 'Personel polisi air dan udara', 'fungsional', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(19, 'TAHTI', 'TAHTI', 'Tata Usaha dan Administrasi', 'Personel tata usaha', 'fungsional', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(20, 'SIKEU', 'SIKEU', 'Sarana dan Peralatan', 'Staf sarana dan peralatan', 'pendukung', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(21, 'SIKUM', 'SIKUM', 'Personalia', 'Staf personnelia', 'pendukung', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(22, 'SIHUMAS', 'SIHUMAS', 'Hubungan Masyarakat', 'Staf hubungan masyarakat', 'pendukung', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(23, 'SIUM', 'SIUM', 'Umum', 'Staf umum', 'pendukung', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(24, 'SITIK', 'SITIK', 'Teknologi Informasi dan Komunikasi', 'Staf TIK', 'pendukung', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(25, 'SIWAS', 'SIWAS', 'Pengawasan Internal', 'Staf pengawasan internal', 'pendukung', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(26, 'SIDOKKES', 'SIDOKKES', 'Kedokteran dan Kesehatan', 'Staf kesehatan', 'pendukung', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(27, 'SIPROPAM', 'SIPROPAM', 'Profesi dan Pengamanan', 'Staf profesi dan pengamanan', 'pendukung', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(28, 'BINTARA', 'BINTARA', 'Bintara', 'Personil level bintara', 'pelaksana', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(29, 'TAMTAMA', 'TAMTAMA', 'Tamtama', 'Personil level tamtama', 'pelaksana', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13'),
(30, 'PNS', 'PNS', 'Pegawai Negeri Sipil', 'Pegawai negeri sipil', 'pelaksana', 'non_eselon', 1, 0, 0, 1, '2026-04-02 16:01:13', '2026-04-02 16:01:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `master_unit_pendukung`
--

CREATE TABLE `master_unit_pendukung` (
  `id` int(11) NOT NULL,
  `kode_unit` varchar(20) NOT NULL,
  `nama_unit` varchar(100) NOT NULL,
  `nama_lengkap` varchar(200) NOT NULL,
  `kategori` enum('si','bagian','seksi') NOT NULL,
  `fungsi_utama` text DEFAULT NULL,
  `is_struktural` tinyint(1) DEFAULT 0,
  `is_pendukung` tinyint(1) DEFAULT 1,
  `is_pimpinan` tinyint(1) DEFAULT 0,
  `is_supervisor` tinyint(1) DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `master_unit_pendukung`
--

INSERT INTO `master_unit_pendukung` (`id`, `kode_unit`, `nama_unit`, `nama_lengkap`, `kategori`, `fungsi_utama`, `is_struktural`, `is_pendukung`, `is_pimpinan`, `is_supervisor`, `deskripsi`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'SIKEU', 'SIKEU', 'Seksi Sarana dan Peralatan', 'si', 'Manajemen sarana dan peralatan kepolisian', 0, 1, 0, 1, 'Mengelola sarana dan peralatan kepolisian', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45'),
(2, 'SIKUM', 'SIKUM', 'Seksi Personalia', 'si', 'Manajemen personil dan kepegawaian', 0, 1, 0, 1, 'Mengelola data personil dan kepegawaian', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45'),
(3, 'SIHUMAS', 'SIHUMAS', 'Seksi Hubungan Masyarakat', 'si', 'Hubungan masyarakat dan publikasi', 0, 1, 0, 1, 'Menjalin hubungan dengan masyarakat dan media', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45'),
(4, 'SIUM', 'SIUM', 'Seksi Umum', 'si', 'Administrasi umum dan keuangan', 0, 1, 0, 1, 'Mengurus administrasi umum dan keuangan', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45'),
(5, 'SITIK', 'SITIK', 'Seksi Teknologi Informasi dan Komunikasi', 'si', 'IT dan komunikasi', 0, 1, 0, 1, 'Mengelola sistem IT dan komunikasi', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45'),
(6, 'SIWAS', 'SIWAS', 'Seksi Pengawasan Internal', 'si', 'Pengawasan internal dan propam', 0, 1, 0, 1, 'Melakukan pengawasan internal dan profesi', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45'),
(7, 'SIDOKKES', 'SIDOKKES', 'Seksi Kedokteran dan Kesehatan', 'si', 'Pelayanan kesehatan', 0, 1, 0, 1, 'Memberikan pelayanan kesehatan kepada personil', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45'),
(8, 'SIPROPAM', 'SIPROPAM', 'Seksi Profesi dan Pengamanan', 'si', 'Profesi dan pengamanan internal', 0, 1, 0, 1, 'Menegakkan profesi dan pengamanan internal', 1, '2026-04-02 16:11:45', '2026-04-02 16:11:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `status` enum('unread','read','archived') DEFAULT 'unread',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title_template` text NOT NULL,
  `message_template` text NOT NULL,
  `data_schema` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data_schema`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `name`, `type`, `title_template`, `message_template`, `data_schema`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'personil_created', 'personil', 'Personil Baru Ditambahkan', 'Personil baru {{nama}} telah ditambahkan dengan NRP {{nrp}}', '{\"nama\": \"string\", \"nrp\": \"string\", \"jabatan\": \"string\", \"bagian\": \"string\"}', 1, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(2, 'personil_updated', 'personil', 'Data Personil Diperbarui', 'Data personil {{nama}} telah diperbarui', '{\"nama\": \"string\", \"nrp\": \"string\", \"changes\": \"array\"}', 1, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(3, 'personil_deleted', 'personil', 'Personil Dihapus', 'Personil {{nama}} telah dihapus dari sistem', '{\"nama\": \"string\", \"nrp\": \"string\", \"deleted_by\": \"string\"}', 1, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(4, 'backup_completed', 'system', 'Backup Selesai', 'Backup {{backup_name}} telah selesai dengan ukuran {{file_size}}', '{\"backup_name\": \"string\", \"file_size\": \"string\", \"type\": \"string\"}', 1, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(5, 'backup_failed', 'system', 'Backup Gagal', 'Backup {{backup_name}} gagal: {{error_message}}', '{\"backup_name\": \"string\", \"error_message\": \"string\"}', 1, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(6, 'report_generated', 'report', 'Laporan Tersedia', 'Laporan {{report_name}} telah selesai dibuat', '{\"report_name\": \"string\", \"type\": \"string\", \"generated_by\": \"string\"}', 1, '2026-04-04 20:26:25', '2026-04-04 20:26:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pangkat`
--

CREATE TABLE `pangkat` (
  `id` int(11) NOT NULL,
  `nama_pangkat` varchar(100) NOT NULL,
  `singkatan` varchar(20) DEFAULT NULL,
  `level_pangkat` int(11) NOT NULL,
  `kategori` enum('POLRI','ASN','LAINNYA') DEFAULT 'LAINNYA',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pangkat`
--

INSERT INTO `pangkat` (`id`, `nama_pangkat`, `singkatan`, `level_pangkat`, `kategori`, `created_at`, `updated_at`) VALUES
(15, 'Jenderal Polisi', 'JENDRAL', 1, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(16, 'Komisaris Jenderal Polisi', 'KOMJEN', 2, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(17, 'Inspektur Jenderal Polisi', 'IRJEN', 3, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(18, 'Brigadir Jenderal Polisi', 'BRIGJEN', 4, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(19, 'Komisaris Besar Polisi', 'KOMBES', 5, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(20, 'Ajun Komisaris Besar Polisi', 'AKBP', 6, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(21, 'Komisaris Polisi', 'KOMPOL', 7, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(22, 'Ajun Komisaris Polisi', 'AKP', 8, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(23, 'Inspektur Polisi Satu', 'IPTU', 9, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(24, 'Inspektur Polisi Dua', 'IPDA', 10, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(25, 'Ajun Inspektur Polisi Satu', 'AIPTU', 11, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(26, 'Ajun Inspektur Polisi Dua', 'AIPDA', 12, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(27, 'Brigadir Polisi Kepala', 'BRIPKA', 13, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(28, 'Brigadir Polisi', 'BRIGPOL', 14, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(29, 'Brigadir Polisi Satu', 'BRIPTU', 15, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(30, 'Brigadir Polisi Dua', 'BRIPDA', 16, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(31, 'Ajun Brigadir Polisi', 'ABRIPOL', 17, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(32, 'Ajun Brigadir Polisi Satu', 'ABRIPTU', 18, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(33, 'Ajun Brigadir Polisi Dua', 'ABRIPDA', 19, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(34, 'Bhayangkara Kepala', 'BHARAKA', 20, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(35, 'Bhayangkara Satu', 'BHARATU', 21, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(36, 'Bhayangkara Dua', 'BHARADA', 22, 'POLRI', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(37, 'Pembina Utama', 'PEBINA', 23, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(38, 'Pembina Utama Madya', 'PEBINA MADYA', 24, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(39, 'Pembina Utama Muda', 'PEBINA MUDA', 25, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(40, 'Pembina Tingkat I', 'PEBINA TK I', 26, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(41, 'Pembina', 'PEBINA', 27, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(42, 'Penata Tingkat I', 'PENDA', 28, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(43, 'Penata', 'PENATA', 29, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(44, 'Penata Muda Tingkat I', 'PENATA MUDA TK I', 30, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(45, 'Penata Muda', 'PENATA MUDA', 31, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(46, 'Pengatur Tingkat I', 'PENGATUR TK I', 32, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(47, 'Pengatur', 'PENGATUR', 33, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(48, 'Pengatur Muda Tingkat I', 'PENGATUR MUDA TK I', 34, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(49, 'Pengatur Muda', 'PENGATUR MUDA', 35, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(50, 'Juru Tingkat I', 'JURU TK I', 36, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(51, 'Juru', 'JURU', 37, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(52, 'Juru Muda Tingkat I', 'JURU MUDA TK I', 38, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(53, 'Juru Muda', 'JURU MUDA', 39, 'ASN', '2026-03-28 15:44:37', '2026-04-04 20:30:26'),
(54, 'Honorer', 'HONORER', 40, 'LAINNYA', '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(55, 'Tenaga Harian Lepas', 'THL', 41, 'LAINNYA', '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(56, 'Kontrak', 'KONTRAK', 42, 'LAINNYA', '2026-03-28 15:44:37', '2026-03-28 15:52:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `personil`
--

CREATE TABLE `personil` (
  `id` int(11) NOT NULL,
  `nrp` varchar(20) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `gelar_depan` varchar(50) DEFAULT NULL,
  `gelar_belakang` varchar(50) DEFAULT NULL,
  `tempat_lahir` varchar(100) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `JK` enum('L','P') NOT NULL,
  `id_pangkat` int(11) NOT NULL,
  `id_jenis_pegawai` int(11) NOT NULL,
  `id_jabatan` int(11) DEFAULT NULL,
  `id_unsur` int(11) DEFAULT NULL,
  `id_bagian` int(11) DEFAULT NULL,
  `id_satuan_fungsi` int(11) DEFAULT NULL,
  `id_unit_pendukung` int(11) DEFAULT NULL,
  `id_status_kepegawaian` int(11) DEFAULT 1,
  `status_ket` varchar(20) DEFAULT 'aktif',
  `alasan_status` text DEFAULT NULL,
  `id_jenis_penugasan` int(11) DEFAULT NULL,
  `id_alasan_penugasan` int(11) DEFAULT NULL,
  `id_status_jabatan` int(11) DEFAULT NULL,
  `tanggal_mulai_penugasan` date DEFAULT NULL,
  `tanggal_selesai_penugasan` date DEFAULT NULL,
  `keterangan_penugasan` text DEFAULT NULL,
  `alamat` text NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `pendidikan_terakhir` varchar(100) DEFAULT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `tahun_lulus` int(11) DEFAULT NULL,
  `status_nikah` varchar(20) DEFAULT NULL,
  `jumlah_anak` int(11) DEFAULT 0,
  `tanggal_masuk` date NOT NULL,
  `tanggal_pensiun` date DEFAULT NULL,
  `no_karpeg` varchar(20) DEFAULT NULL,
  `masa_kerja_tahun` int(11) DEFAULT 0,
  `masa_kerja_bulan` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status_penugasan` enum('definitif','ps','plt','pjs','plh','pj') DEFAULT 'definitif',
  `alasan_penugasan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `personil`
--

INSERT INTO `personil` (`id`, `nrp`, `nama`, `gelar_depan`, `gelar_belakang`, `tempat_lahir`, `tanggal_lahir`, `JK`, `id_pangkat`, `id_jenis_pegawai`, `id_jabatan`, `id_unsur`, `id_bagian`, `id_satuan_fungsi`, `id_unit_pendukung`, `id_status_kepegawaian`, `status_ket`, `alasan_status`, `id_jenis_penugasan`, `id_alasan_penugasan`, `id_status_jabatan`, `tanggal_mulai_penugasan`, `tanggal_selesai_penugasan`, `keterangan_penugasan`, `alamat`, `telepon`, `email`, `pendidikan_terakhir`, `jurusan`, `tahun_lulus`, `status_nikah`, `jumlah_anak`, `tanggal_masuk`, `tanggal_pensiun`, `no_karpeg`, `masa_kerja_tahun`, `masa_kerja_bulan`, `is_active`, `is_deleted`, `created_by`, `updated_by`, `created_at`, `updated_at`, `status_penugasan`, `alasan_penugasan`) VALUES
(32, '84031648', 'RINA SRY NIRWANA TARIGAN, S.I.K., M.H.', 'S.I.K., M.H.', NULL, 'Tidak diketahui', '1984-03-01', 'P', 20, 1, 763, 61, 1, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(33, '83081648', 'BRISTON AGUS MUNTECARLO, S.T., S.I.K.', 'S.T., S.I.K.', NULL, 'Tidak diketahui', '1983-08-01', 'L', 21, 1, 764, 61, 1, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(34, '68100259', 'EDUAR, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1968-10-01', 'L', 21, 1, 765, 62, 2, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(35, '82080038', 'PATRI SIHALOHO', 'S.H.', NULL, 'Tidak diketahui', '1982-08-01', 'L', 26, 1, 766, 62, 2, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(36, '02120141', 'AGUNG NUGRAHA NADAP-DAP', '', NULL, 'Tidak diketahui', '2002-12-01', 'L', 30, 1, 767, 62, 2, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(37, '03010386', 'ALDI PRANATA GINTING', '', NULL, 'Tidak diketahui', '2003-01-01', 'L', 30, 1, 767, 62, 2, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(38, '02040489', 'HENDRIKSON SILALAHI', '', NULL, 'Tidak diketahui', '2002-04-01', 'L', 30, 1, 767, 62, 2, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(39, '02071119', 'TOHONAN SITOHANG', '', NULL, 'Tidak diketahui', '2002-07-01', 'L', 30, 1, 767, 62, 2, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(40, '03101364', 'GILANG SUTOYO', '', NULL, 'Tidak diketahui', '2003-10-01', 'L', 30, 1, 767, 62, 2, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(41, '76030248', 'HENDRI SIAGIAN, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1976-03-01', 'L', 24, 1, 769, 63, 20, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(42, '87070134', 'DENI MUSTIKA SUKMANA, S.E.', 'S.E.', NULL, 'Tidak diketahui', '1987-07-01', 'L', 24, 1, 770, 63, 20, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(43, '85081770', 'JAMIL MUNTHE, S.H., M.H.', 'S.H.', NULL, 'Tidak diketahui', '1985-08-01', 'L', 24, 1, 771, 63, 20, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(44, '87030020', 'BULET MARS SWANTO LBN. BATU, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1987-03-01', 'L', 24, 1, 772, 63, 20, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(45, '96010872', 'RAMADHAN PUTRA, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1996-01-01', 'L', 29, 1, 773, 63, 20, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(46, '98090415', 'ABEDNEGO TARIGAN', '', NULL, 'Tidak diketahui', '1998-09-01', 'L', 29, 1, 774, 63, 20, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(47, '00010166', 'EDY SUSANTO PARDEDE', '', NULL, 'Tidak diketahui', '2000-01-01', 'L', 29, 1, 775, 63, 20, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(48, '98010470', 'BOBBY ANGGARA PUTRA SIREGAR', '', NULL, 'Tidak diketahui', '1998-01-01', 'L', 30, 1, 775, 63, 20, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(49, '01070820', 'GABRIEL PAULIMA NADEAK', '', NULL, 'Tidak diketahui', '2001-07-01', 'L', 30, 1, 775, 63, 20, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(50, '02091526', 'ANDRE OWEN PURBA', '', NULL, 'Tidak diketahui', '2002-09-01', 'L', 30, 1, 773, 63, 20, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(51, '04070159', 'EDWARD FERDINAND SIDABUTAR', '', NULL, 'Tidak diketahui', '2004-07-01', 'L', 30, 1, 773, 63, 20, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(52, '03060873', 'BIMA SANTO HUTAGAOL', '', NULL, 'Tidak diketahui', '2003-06-01', 'L', 30, 1, 774, 63, 20, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(53, '03121291', 'KRISTIAN M. H. NABABAN', '', NULL, 'Tidak diketahui', '2003-12-01', 'L', 30, 1, 774, 63, 20, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(54, '72100484', 'SURUNG SAGALA', '', NULL, 'Tidak diketahui', '1972-10-01', 'L', 24, 1, 776, 62, 3, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(55, '96090857', 'ZAKHARIA S. I. SIMANJUNTAK, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1996-09-01', 'L', 29, 1, 777, 62, 3, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(56, '03080202', 'GRENIEL WIARTO SIHITE', '', NULL, 'Tidak diketahui', '2003-08-01', 'L', 30, 1, 777, 62, 3, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(57, '73010107', 'TARMIZI LUBIS, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1973-01-01', 'L', 22, 1, 778, 62, 4, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(58, '198111252014122004', 'REYMESTA AMBARITA, S.Kom.', 'S.Kom.', NULL, 'Tidak diketahui', '1981-11-25', 'P', 42, 4, 779, 62, 4, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(59, '97090248', 'LAMTIO SINAGA, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1997-09-01', 'P', 28, 1, 780, 62, 4, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(60, '97120490', 'DODI KURNIADI', '', NULL, 'Tidak diketahui', '1997-12-01', 'L', 29, 1, 780, 62, 4, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(61, '05070285', 'EFRANTA SAPUTRA SITEPU', '', NULL, 'Tidak diketahui', '2005-07-01', 'L', 30, 1, 780, 62, 4, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(62, '86070985', 'RADOS. S. TOGATOROP,S.H.', '', NULL, 'Tidak diketahui', '1986-07-01', 'L', 26, 3, 781, 62, 4, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(63, '00080579', 'REYSON YOHANNES SIMBOLON', '', NULL, 'Tidak diketahui', '2000-08-01', 'L', 30, 1, 782, 62, 4, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(64, '02090891', 'ANDRE TARUNA SIMBOLON', '', NULL, 'Tidak diketahui', '2002-09-01', 'L', 30, 1, 783, 62, 4, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(65, '03081525', 'YOLANDA NAULIVIA ARITONANG', '', NULL, 'Tidak diketahui', '2003-08-01', 'P', 30, 1, 782, 62, 4, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(66, '95080918', 'SYAUQI LUTFI LUBIS, S.H., M.H.', 'S.H.', NULL, 'Tidak diketahui', '1995-08-01', 'L', 28, 1, 890, 66, 29, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:28:06', 'definitif', NULL),
(67, '97050575', 'DANIEL BRANDO SIDABUKKE', '', NULL, 'Tidak diketahui', '1997-05-01', 'L', 28, 1, 890, 66, 29, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:28:15', 'definitif', NULL),
(68, '98010119', 'SUTRISNO BUTAR-BUTAR, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1998-01-01', 'L', 29, 1, 890, 66, 29, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:28:15', 'definitif', NULL),
(69, '81110363', 'LEONARDO SINAGA', '', NULL, 'Tidak diketahui', '1981-11-01', 'L', 26, 1, 781, 66, 4, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, 'BELUM MENGHADAP', 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(70, '76040221', 'AWALUDDIN', '', NULL, 'Tidak diketahui', '1976-04-01', 'L', 24, 1, 784, 62, 5, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:50', 'definitif', NULL),
(71, '97050588', 'EFRON SARWEDY SINAGA, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1997-05-01', 'L', 29, 1, 785, 62, 5, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:50', 'definitif', NULL),
(72, '00010095', 'PRIADI MAROJAHAN HUTABARAT', '', NULL, 'Tidak diketahui', '2000-01-01', 'L', 29, 1, 785, 62, 5, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:50', 'definitif', NULL),
(73, '03070263', 'CHRIST JERICHO SAPUTRA TAMPUBOLON', '', NULL, 'Tidak diketahui', '2003-07-01', 'L', 30, 1, 785, 62, 5, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:50', 'definitif', NULL),
(74, '86100287', 'EFRI PANDI', '', NULL, 'Tidak diketahui', '1986-10-01', 'L', 26, 1, 786, 65, 21, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(75, '04010804', 'YOGI ADE PRATAMA SITOHANG', '', NULL, 'Tidak diketahui', '2004-01-01', 'L', 30, 1, 787, 65, 21, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(76, '93100676', 'PENGEJAPEN, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1993-10-01', 'L', 28, 1, 788, 65, 22, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(77, '97050876', 'MUHARRAM SYAHRI, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1997-05-01', 'L', 29, 1, 789, 65, 22, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(78, '97100685', 'M.FATHUR RAHMAN, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1997-10-01', 'L', 29, 1, 789, 65, 22, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(79, '03070010', 'HESKIEL WANDANA MELIALA', '', NULL, 'Tidak diketahui', '2003-07-01', 'L', 30, 1, 789, 65, 22, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(80, '03040138', 'DANIEL RICARDO SARAGIH', '', NULL, 'Tidak diketahui', '2003-04-01', 'L', 30, 1, 789, 65, 22, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(81, '197008291993032002', 'NENENG GUSNIARTI', '', NULL, 'Tidak diketahui', '1970-08-29', 'P', 43, 4, 790, 65, 23, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(82, '84040532', 'EDDY SURANTA SARAGIH', '', NULL, 'Tidak diketahui', '1984-04-01', 'L', 27, 1, 791, 65, 23, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(83, '75060617', 'BILMAR SITUMORANG', '', NULL, 'Tidak diketahui', '1975-06-01', 'L', 25, 1, 792, 65, 24, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(84, '94080815', 'YOHANES EDI SUPRIATNO, S.H., M.H.', 'S.H.', NULL, 'Tidak diketahui', '1994-08-01', 'L', 28, 1, 793, 65, 24, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(85, '94080892', 'AGUSTIAWAN SINAGA', '', NULL, 'Tidak diketahui', '1994-08-01', 'L', 28, 1, 793, 65, 24, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(86, '93060444', 'LISTER BROUN SITORUS', '', NULL, 'Tidak diketahui', '1993-06-01', 'L', 28, 1, 794, 65, 25, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(87, '00070791', 'ANDREAS D. S. SITANGGANG', '', NULL, 'Tidak diketahui', '2000-07-01', 'L', 30, 1, 794, 65, 25, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(88, '01101139', 'JACKSON SIDABUTAR', '', NULL, 'Tidak diketahui', '2001-10-01', 'L', 30, 1, 794, 65, 25, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(89, '73050261', 'PARIMPUNAN SIREGAR', '', NULL, 'Tidak diketahui', '1973-05-01', 'L', 24, 1, 795, 65, 26, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(90, '95030599', 'DANIEL E. LUMBANTORUAN, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1995-03-01', 'L', 28, 1, 796, 65, 26, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(91, '76120670', 'DENNI BOYKE H. SIREGAR, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1976-12-01', 'L', 24, 1, 797, 65, 27, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(92, '81010202', 'BENNI ARDINAL, S.H., M.H.', 'S.H.', NULL, 'Tidak diketahui', '1981-01-01', 'L', 26, 1, 798, 65, 27, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(93, '85081088', 'AGUSTINUS SINAGA', '', NULL, 'Tidak diketahui', '1985-08-01', 'L', 26, 1, 799, 65, 27, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(94, '86081359', 'RAMBO CISLER NADEAK', '', NULL, 'Tidak diketahui', '1986-08-01', 'L', 27, 1, 800, 65, 27, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(95, '95030796', 'PERY RAPEN YONES PARDOSI, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1995-03-01', 'L', 28, 1, 800, 65, 27, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(96, '97070014', 'DWI HETRIANDY, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1997-07-01', 'L', 28, 1, 800, 65, 27, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(97, '97120554', 'TRY WIBOWO', '', NULL, 'Tidak diketahui', '1997-12-01', 'L', 29, 1, 800, 65, 27, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(98, '00080343', 'SIMON TIGRIS SIAGIAN', '', NULL, 'Tidak diketahui', '2000-08-01', 'L', 29, 1, 800, 65, 27, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(99, '01080575', 'FIRIAN JOSUA SITORUS', '', NULL, 'Tidak diketahui', '2001-08-01', 'L', 30, 1, 800, 65, 27, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(100, '93030551', 'GUNAWAN SITUMORANG', '', NULL, 'Tidak diketahui', '1993-03-01', 'L', 28, 1, 801, 65, 28, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(101, '98091488', 'DANIEL BAHTERA SINAGA', '', NULL, 'Tidak diketahui', '1998-09-01', 'L', 29, 1, 801, 65, 28, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(102, '75120560', 'HORAS LARIUS SITUMORANG', '', NULL, 'Tidak diketahui', '1975-12-01', 'L', 24, 1, 802, 63, 14, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(103, '95090650', 'JEFTA OCTAVIANUS NICO SIANTURI', '', NULL, 'Tidak diketahui', '1995-09-01', 'L', 28, 1, 803, 63, 14, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(104, '94091146', 'SAHAT MARULI TUA SINAGA, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1994-09-01', 'L', 28, 1, 803, 63, 14, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(105, '04020118', 'RONAL PARTOGI SITUMORANG', '', NULL, 'Tidak diketahui', '2004-02-01', 'L', 30, 1, 803, 63, 14, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(106, '82070670', 'DONAL P. SITANGGANG, S.H., M.H.', 'S.H.', NULL, 'Tidak diketahui', '1982-07-01', 'L', 23, 1, 804, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(107, '85050489', 'MUHAMMAD YUNUS LUBIS, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1985-05-01', 'L', 24, 1, 868, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(108, '80070348', 'MARBETA S. SIANIPAR, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1980-07-01', 'L', 26, 1, 805, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(109, '87080112', 'SITARDA AKABRI SIBUEA', '', NULL, 'Tidak diketahui', '1987-08-01', 'L', 26, 1, 806, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(110, '87051430', 'CINTER ROKHY SINAGA', '', NULL, 'Tidak diketahui', '1987-05-01', 'L', 27, 1, 807, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(111, '90080088', 'VANDU P. MARPAUNG', '', NULL, 'Tidak diketahui', '1990-08-01', 'L', 27, 1, 808, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(112, '93080556', 'ALFONSIUS GULTOM, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1993-08-01', 'L', 28, 1, 809, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(113, '97040848', 'TRIFIKO P. NAINGGOLAN, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1997-04-01', 'L', 29, 1, 810, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(114, '98110618', 'ANDRI AFRIJAL SIMARMATA', '', NULL, 'Tidak diketahui', '1998-11-01', 'L', 29, 1, 810, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(115, '02030032', 'DIEN VAROSCY I. SITUMORANG', '', NULL, 'Tidak diketahui', '2002-03-01', 'L', 30, 1, 810, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(116, '02120339', 'ARDY TRIANO MALAU', '', NULL, 'Tidak diketahui', '2002-12-01', 'L', 30, 1, 810, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(117, '02040459', 'JUNEDI SAGALA', '', NULL, 'Tidak diketahui', '2002-04-01', 'L', 30, 1, 810, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(118, '02101010', 'GABRIEL SEBASTIAN SIREGAR', '', NULL, 'Tidak diketahui', '2002-10-01', 'L', 30, 1, 810, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(119, '04020209', 'RIO F. T ERENST PANJAITAN', '', NULL, 'Tidak diketahui', '2004-02-01', 'L', 30, 1, 810, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(120, '04080118', 'AGHEO HARMANA JOUSTRA SINURAYA', '', NULL, 'Tidak diketahui', '2004-08-01', 'L', 30, 1, 809, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(121, '04010932', 'SAMUEL RINALDI PAKPAHAN', '', NULL, 'Tidak diketahui', '2004-01-01', 'L', 30, 1, 809, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(122, '04040520', 'RAYMONTIUS HAROMUNTE', '', NULL, 'Tidak diketahui', '2004-04-01', 'L', 30, 1, 809, 63, 6, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(123, '79120994', 'EDWARD SIDAURUK, S.E., M.M.', 'S.E.', NULL, 'Tidak diketahui', '1979-12-01', 'L', 22, 1, 811, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(124, '76020196', 'DARMONO SAMOSIR, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1976-02-01', 'L', 24, 1, 812, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(125, '83010825', 'ROYANTO PURBA, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1983-01-01', 'L', 24, 1, 813, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(126, '83120602', 'SUHADIYANTO, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1983-12-01', 'L', 24, 1, 814, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(127, '88060535', 'KUICAN SIMANJUNTAK', '', NULL, 'Tidak diketahui', '1988-06-01', 'L', 27, 1, 815, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(128, '79030434', 'MARTIN HABENSONY ARITONANG', '', NULL, 'Tidak diketahui', '1979-03-01', 'L', 25, 1, 816, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(129, '83060084', 'HENRY SIPAKKAR', '', NULL, 'Tidak diketahui', '1983-06-01', 'L', 25, 1, 817, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(130, '87011165', 'CHANDRA HUTAPEA', '', NULL, 'Tidak diketahui', '1987-01-01', 'L', 27, 1, 889, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(131, '89030401', 'CHANDRA BARIMBING', '', NULL, 'Tidak diketahui', '1989-03-01', 'L', 27, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(132, '87041596', 'DEDY SAOLOAN SIGALINGGING', '', NULL, 'Tidak diketahui', '1987-04-01', 'L', 27, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(133, '82050798', 'ISWAN LUKITO', '', NULL, 'Tidak diketahui', '1982-05-01', 'L', 27, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(134, '95030238', 'RONI HANSVERI BANJARNAHOR', '', NULL, 'Tidak diketahui', '1995-03-01', 'L', 28, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(135, '94020506', 'RODEN SUANDI TURNIP', '', NULL, 'Tidak diketahui', '1994-02-01', 'L', 28, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(136, '94121145', 'SAPUTRA, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1994-12-01', 'L', 28, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(137, '95100554', 'DIAN LESTARI GULTOM, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1995-10-01', 'P', 28, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(138, '95110886', 'ARGIO SIMBOLON', '', NULL, 'Tidak diketahui', '1995-11-01', 'L', 28, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(139, '97070616', 'EKO DAHANA PARDEDE, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1997-07-01', 'L', 28, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(140, '97040728', 'GIDEON AFRIADI LUMBAN RAJA', '', NULL, 'Tidak diketahui', '1997-04-01', 'L', 29, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(141, '98090397', 'FACHRUL REZA SILALAHI', '', NULL, 'Tidak diketahui', '1998-09-01', 'L', 29, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(142, '00030346', 'RIDHOTUA F. SITANGGANG', '', NULL, 'Tidak diketahui', '2000-03-01', 'L', 29, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(143, '00110362', 'NICHO FERNANDO SARAGIH', '', NULL, 'Tidak diketahui', '2000-11-01', 'L', 29, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(144, '00090499', 'ADI P.S. MARBUN', '', NULL, 'Tidak diketahui', '2000-09-01', 'L', 29, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(145, '01120358', 'PRIYATAMA ABDILLAH HARAHAP', '', NULL, 'Tidak diketahui', '2001-12-01', 'L', 30, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(146, '01070839', 'RIZKI AFRIZAL SIMANJUNTAK', '', NULL, 'Tidak diketahui', '2001-07-01', 'L', 30, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(147, '01060553', 'MIDUK YUDIANTO SINAGA', '', NULL, 'Tidak diketahui', '2001-06-01', 'L', 30, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(148, '02110342', 'FRAN\'S ALEXANDER SIANIPAR', '', NULL, 'Tidak diketahui', '2002-11-01', 'L', 30, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(149, '01110817', 'RAFFLES SIJABAT', '', NULL, 'Tidak diketahui', '2001-11-01', 'L', 30, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(150, '01091201', 'HERIANTA TARIGAN', '', NULL, 'Tidak diketahui', '2001-09-01', 'L', 30, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(151, '03030809', 'RICKY AGATHA GINTING', '', NULL, 'Tidak diketahui', '2003-03-01', 'L', 30, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(152, '03020368', 'CHRISTIAN PROSPEROUS SIMANUNGKALIT', '', NULL, 'Tidak diketahui', '2003-02-01', 'L', 30, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(153, '04020196', 'PINIEL RAJAGUKGUK', '', NULL, 'Tidak diketahui', '2004-02-01', 'L', 30, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(154, '03090568', 'REZA SIREGAR', '', NULL, 'Tidak diketahui', '2003-09-01', 'L', 30, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(155, '04031206', 'RAYMOND VAN HEZEKIEL SIAHAAN', '', NULL, 'Tidak diketahui', '2004-03-01', 'L', 30, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(156, '05080602', 'M. ALAMSYAH PRAYOGA TAMBUNAN', '', NULL, 'Tidak diketahui', '2005-08-01', 'L', 30, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(157, '04090567', 'IRVAN SYAPUTRA MALAU', '', NULL, 'Tidak diketahui', '2004-09-01', 'L', 30, 1, 818, 63, 7, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(158, '79060034', 'FERRY ARIANDY, S.H., M.H', 'S.H.', NULL, 'Tidak diketahui', '1979-06-01', 'L', 22, 1, 819, 63, 8, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(159, '88100591', 'ALVIUS KRISTIAN GINTING, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1988-10-01', 'L', 24, 1, 869, 63, 8, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(160, '89010155', 'BENNY SITUMORANG, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1989-01-01', 'L', 27, 1, 820, 63, 8, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(161, '93050797', 'EKO PUTRA DAMANIK, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1993-05-01', 'L', 28, 1, 821, 63, 8, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(162, '91050361', 'MAY FRANSISCO SIAGIAN, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1991-05-01', 'L', 28, 1, 821, 63, 8, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(163, '94090839', 'ROBERTO MANALU', '', NULL, 'Tidak diketahui', '1994-09-01', 'L', 29, 1, 821, 63, 8, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(164, '98110378', 'M. RONALD FAHROZI HARAHAP, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1998-11-01', 'L', 29, 1, 821, 63, 8, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(165, '97020694', 'HERIANTO EFENDI, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1997-02-01', 'L', 29, 1, 821, 63, 8, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(166, '02120224', 'TEDDI PARNASIPAN TOGATOROP', '', NULL, 'Tidak diketahui', '2002-12-01', 'L', 30, 1, 821, 63, 8, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(167, '02090838', 'ONDIHON SIMBOLON', '', NULL, 'Tidak diketahui', '2002-09-01', 'L', 30, 1, 821, 63, 8, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(168, '05080131', 'IVAN SIGOP SIHOMBING', '', NULL, 'Tidak diketahui', '2005-08-01', 'L', 30, 1, 821, 63, 8, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(169, '80080676', 'NANDI BUTAR-BUTAR, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1980-08-01', 'L', 22, 1, 822, 63, 10, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(170, '80050867', 'BARTO ANTONIUS SIMALANGO', '', NULL, 'Tidak diketahui', '1980-05-01', 'L', 25, 1, 823, 63, 10, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL);
INSERT INTO `personil` (`id`, `nrp`, `nama`, `gelar_depan`, `gelar_belakang`, `tempat_lahir`, `tanggal_lahir`, `JK`, `id_pangkat`, `id_jenis_pegawai`, `id_jabatan`, `id_unsur`, `id_bagian`, `id_satuan_fungsi`, `id_unit_pendukung`, `id_status_kepegawaian`, `status_ket`, `alasan_status`, `id_jenis_penugasan`, `id_alasan_penugasan`, `id_status_jabatan`, `tanggal_mulai_penugasan`, `tanggal_selesai_penugasan`, `keterangan_penugasan`, `alamat`, `telepon`, `email`, `pendidikan_terakhir`, `jurusan`, `tahun_lulus`, `status_nikah`, `jumlah_anak`, `tanggal_masuk`, `tanggal_pensiun`, `no_karpeg`, `masa_kerja_tahun`, `masa_kerja_bulan`, `is_active`, `is_deleted`, `created_by`, `updated_by`, `created_at`, `updated_at`, `status_penugasan`, `alasan_penugasan`) VALUES
(171, '73040390', 'HASUDUNGAN SILITONGA', '', NULL, 'Tidak diketahui', '1973-04-01', 'L', 26, 1, 824, 63, 10, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(172, '85090954', 'JHONNY LEONARDO SILALAHI', '', NULL, 'Tidak diketahui', '1985-09-01', 'L', 27, 1, 825, 63, 10, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(173, '83081051', 'ASRIL', '', NULL, 'Tidak diketahui', '1983-08-01', 'L', 27, 1, 826, 63, 10, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(174, '94110350', 'INDIRWAN FRIDERICK, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1994-11-01', 'L', 28, 1, 826, 63, 10, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(175, '93100793', 'EGIDIUM BRAUN SILITONGA', '', NULL, 'Tidak diketahui', '1993-10-01', 'L', 28, 1, 826, 63, 10, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(176, '97100701', 'DINAMIKA JAYA NEGARA SITANGGANG', '', NULL, 'Tidak diketahui', '1997-10-01', 'L', 30, 1, 826, 63, 10, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(177, '05051087', 'WIRA HARZITA', '', NULL, 'Tidak diketahui', '2005-05-01', 'L', 30, 1, 826, 63, 10, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(178, '06100189', 'RAHMAT ANDRIAN TAMBUNAN', '', NULL, 'Tidak diketahui', '2006-10-01', 'L', 30, 1, 826, 63, 10, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(179, '07080045', 'JONATAN DWI SAPUTRA PARAPAT', '', NULL, 'Tidak diketahui', '2007-08-01', 'L', 30, 1, 826, 63, 10, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(180, '04051595', 'PERDANA NIKOLA SEMBIRING', '', NULL, 'Tidak diketahui', '2004-05-01', 'L', 30, 1, 826, 63, 10, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(181, '04081205', 'PETRUS SURIA HUGALUNG', '', NULL, 'Tidak diketahui', '2004-08-01', 'L', 30, 1, 826, 63, 10, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(182, '06010414', 'RAFAEL ARSANLILO SINULINGGA', '', NULL, 'Tidak diketahui', '2006-01-01', 'L', 30, 1, 826, 63, 10, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(183, '06090021', 'RAJASPER SIRINGORINGO', '', NULL, 'Tidak diketahui', '2006-09-01', 'L', 30, 1, 826, 63, 10, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(184, '72100604', 'TANGIO HAOJAHAN SITANGGANG, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1972-10-01', 'L', 23, 1, 827, 63, 11, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(185, '80100836', 'MARUBA NAINGGOLAN', '', NULL, 'Tidak diketahui', '1980-10-01', 'L', 25, 1, 828, 63, 11, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(186, '85030645', 'ROY HARIS ST. SIMAREMARE', '', NULL, 'Tidak diketahui', '1985-03-01', 'L', 26, 1, 829, 63, 11, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(187, '80050898', 'M. DENY WAHYU', '', NULL, 'Tidak diketahui', '1980-05-01', 'L', 26, 1, 830, 63, 11, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(188, '83050202', 'HENRI F. SIANIPAR', '', NULL, 'Tidak diketahui', '1983-05-01', 'L', 25, 1, 831, 63, 11, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(189, '85121325', 'BUYUNG ANDRYANTO', '', NULL, 'Tidak diketahui', '1985-12-01', 'L', 27, 1, 887, 63, 11, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(190, '91110130', 'RIANTO SITANGGANG', '', NULL, 'Tidak diketahui', '1991-11-01', 'L', 28, 1, 831, 63, 11, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(191, '94090948', 'ROY NANDA SEMBIRING KEMBAREN', '', NULL, 'Tidak diketahui', '1994-09-01', 'L', 28, 1, 831, 63, 11, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(192, '96031057', 'CANDRA SILALAHI, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1996-03-01', 'L', 28, 1, 831, 63, 11, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(193, '02100599', 'YUNUS SAMDIO SIDABUTAR', '', NULL, 'Tidak diketahui', '2002-10-01', 'L', 30, 1, 831, 63, 11, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(194, '03010565', 'RAINHEART SITANGGANG', '', NULL, 'Tidak diketahui', '2003-01-01', 'L', 30, 1, 831, 63, 11, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(195, '02011312', 'BONIFASIUS NAINGGOLAN', '', NULL, 'Tidak diketahui', '2002-01-01', 'L', 30, 1, 831, 63, 11, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(196, '00080816', 'RAY YONDO SIAHAAN', '', NULL, 'Tidak diketahui', '2000-08-01', 'L', 30, 1, 831, 63, 11, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(197, '03040947', 'REDY EZRA JONATHAN', '', NULL, 'Tidak diketahui', '2003-04-01', 'L', 30, 1, 831, 63, 11, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(198, '04100485', 'CHARLY H. ARITONANG', '', NULL, 'Tidak diketahui', '2004-10-01', 'L', 30, 1, 831, 63, 11, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(199, '79120800', 'NATANAIL SURBAKTI, S.H', '', NULL, 'Tidak diketahui', '1979-12-01', 'L', 22, 1, 832, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(200, '75080942', 'JUSUP KETAREN', '', NULL, 'Tidak diketahui', '1975-08-01', 'L', 24, 1, 833, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(201, '80070492', 'ARON PERANGIN-ANGIN', '', NULL, 'Tidak diketahui', '1980-07-01', 'L', 25, 1, 834, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(202, '79060704', 'HERON GINTING', '', NULL, 'Tidak diketahui', '1979-06-01', 'L', 27, 1, 835, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(203, '86030733', 'JEFRI KHADAFI SIREGAR, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1986-03-01', 'L', 27, 1, 836, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(204, '89070031', 'HERIANTO TURNIP', '', NULL, 'Tidak diketahui', '1989-07-01', 'L', 27, 1, 837, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(205, '87030647', 'DION MAR\'YANSEN SILITONGA', '', NULL, 'Tidak diketahui', '1987-03-01', 'L', 28, 1, 837, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(206, '93020749', 'ROY GRIMSLAY, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1993-02-01', 'L', 28, 1, 837, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(207, '93090673', 'BAGUS DWI PRAKOSO, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1993-09-01', 'L', 28, 1, 837, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(208, '97040353', 'ICASANDRI MONANZA BR GINTING', '', NULL, 'Tidak diketahui', '1997-04-01', 'P', 28, 1, 837, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(209, '95021078', 'DIKI FEBRIAN SITORUS', '', NULL, 'Tidak diketahui', '1995-02-01', 'L', 29, 1, 837, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(210, '96031061', 'MARCHLANDA SITOHANG', '', NULL, 'Tidak diketahui', '1996-03-01', 'L', 29, 1, 837, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(211, '01080438', 'JULIVER SIDABUTAR', '', NULL, 'Tidak diketahui', '2001-08-01', 'L', 29, 1, 837, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(212, '01120281', 'FATHURROZI TINDAON', '', NULL, 'Tidak diketahui', '2001-12-01', 'L', 30, 1, 837, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(213, '02111012', 'BENY BOY CHRISTIAN SIAHAAN', '', NULL, 'Tidak diketahui', '2002-11-01', 'L', 30, 1, 867, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(214, '02111051', 'RADOT NOVALDO PANDAPOTAN PURBA', '', NULL, 'Tidak diketahui', '2002-11-01', 'L', 30, 1, 867, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(215, '05030251', 'MUHAMMAD ZIDHAN RIFALDI', '', NULL, 'Tidak diketahui', '2005-03-01', 'L', 30, 1, 867, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(216, '04050615', 'DANI INDRA PERMANA SINAGA', '', NULL, 'Tidak diketahui', '2004-05-01', 'L', 30, 1, 867, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(217, '05010048', 'HEZKIEL CAPRI SITINDAON', '', NULL, 'Tidak diketahui', '2005-01-01', 'L', 30, 1, 867, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(218, '04030824', 'BONARIS TSUYOKO DITASANI SINAGA', '', NULL, 'Tidak diketahui', '2004-03-01', 'L', 30, 1, 867, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(219, '05010014', 'ARY ANJAS SARAGIH', '', NULL, 'Tidak diketahui', '2005-01-01', 'L', 30, 1, 867, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(220, '04030805', 'GABRIEL VERY JUNIOR SITOHANG', '', NULL, 'Tidak diketahui', '2004-03-01', 'L', 30, 1, 867, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(221, '02121477', 'FIRMAN BAHTERA', '', NULL, 'Tidak diketahui', '2002-12-01', 'L', 30, 1, 867, 63, 9, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(222, '68120522', 'SULAIMAN PANGARIBUAN, S.H', '', NULL, 'Tidak diketahui', '1968-12-01', 'L', 22, 1, 838, 63, 12, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(223, '83080822', 'EFENDI M.  SIREGAR', '', NULL, 'Tidak diketahui', '1983-08-01', 'L', 26, 1, 839, 63, 12, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(224, '73120275', 'ROMEL LINDUNG SIAHAAN', '', NULL, 'Tidak diketahui', '1973-12-01', 'L', 26, 1, 888, 63, 12, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(225, '90060273', 'FRANS HOTMAN MANURUNG, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1990-06-01', 'L', 27, 1, 840, 63, 12, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(226, '77070919', 'ANTONIUS SIPAYUNG', '', NULL, 'Tidak diketahui', '1977-07-01', 'L', 28, 1, 840, 63, 12, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 02:27:39', 'definitif', NULL),
(227, '82051018', 'SAUT H. SIAHAAN', '', NULL, 'Tidak diketahui', '1982-05-01', 'L', 26, 1, 841, 63, 13, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(228, '98050496', 'FERNANDO SIMBOLON', '', NULL, 'Tidak diketahui', '1998-05-01', 'L', 29, 1, 842, 63, 13, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(229, '98030531', 'KURNIA PERMANA', '', NULL, 'Tidak diketahui', '1998-03-01', 'L', 29, 1, 842, 63, 13, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(230, '05090232', 'STEVEN IMANUEL SITUMEANG', '', NULL, 'Tidak diketahui', '2005-09-01', 'L', 30, 1, 842, 63, 13, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(231, '69090552', 'RAHMAT KURNIAWAN', '', NULL, 'Tidak diketahui', '1969-09-01', 'L', 23, 1, 843, 64, 33, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:43', 'definitif', NULL),
(232, '79090296', 'MARUKKIL J.M. PASARIBU', '', NULL, 'Tidak diketahui', '1979-09-01', 'L', 25, 1, 844, 64, 33, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:43', 'definitif', NULL),
(233, '82070930', 'LANTRO LANDELINUS SAGALA', '', NULL, 'Tidak diketahui', '1982-07-01', 'L', 26, 1, 845, 64, 33, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:43', 'definitif', NULL),
(234, '87120701', 'ANDY DEDY SIHOMBING, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1987-12-01', 'L', 27, 1, 846, 64, 33, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:43', 'definitif', NULL),
(235, '86021428', 'RANGGA HATTA', '', NULL, 'Tidak diketahui', '1986-02-01', 'L', 27, 1, 847, 64, 33, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:43', 'definitif', NULL),
(236, '80120573', 'ARDIANSYAH BUTAR-BUTAR', '', NULL, 'Tidak diketahui', '1980-12-01', 'L', 27, 1, 848, 64, 33, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:43', 'definitif', NULL),
(237, '96120123', 'ADRYANTO SINAGA, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1996-12-01', 'L', 28, 1, 848, 64, 33, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:43', 'definitif', NULL),
(238, '94040538', 'BROLIN ADFRIALDI HALOHO', '', NULL, 'Tidak diketahui', '1994-04-01', 'L', 28, 1, 848, 64, 33, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:43', 'definitif', NULL),
(239, '95110806', 'SUGIANTO ERIK SIBORO', '', NULL, 'Tidak diketahui', '1995-11-01', 'L', 28, 1, 848, 64, 33, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:43', 'definitif', NULL),
(240, '01020739', 'RISKO SIMBOLON', '', NULL, 'Tidak diketahui', '2001-02-01', 'L', 30, 1, 848, 64, 33, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:43', 'definitif', NULL),
(241, '70050412', 'MAXON NAINGGOLAN', '', NULL, 'Tidak diketahui', '1970-05-01', 'L', 22, 1, 849, 64, 16, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(242, '78040213', 'H. SWANDI SINAGA', '', NULL, 'Tidak diketahui', '1978-04-01', 'L', 25, 1, 850, 64, 16, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(243, '77030463', 'HARATUA GULTOM', '', NULL, 'Tidak diketahui', '1977-03-01', 'L', 25, 1, 885, 64, 16, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(244, '76120606', 'ASA MELKI HUTABARAT', '', NULL, 'Tidak diketahui', '1976-12-01', 'L', 26, 1, 851, 64, 16, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(245, '78100741', 'JARIAHMAN SARAGIH', '', NULL, 'Tidak diketahui', '1978-10-01', 'L', 26, 1, 872, 64, 16, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(246, '87041134', 'MUHAMMAD SYAFEI RAMADHAN', '', NULL, 'Tidak diketahui', '1987-04-01', 'L', 26, 1, 880, 64, 16, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(247, '86121371', 'RIJALUL FIKRI SINAGA', '', NULL, 'Tidak diketahui', '1986-12-01', 'L', 27, 1, 876, 64, 16, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(248, '85071450', 'TEGUH SYAHPUTRA', '', NULL, 'Tidak diketahui', '1985-07-01', 'L', 27, 1, 852, 64, 16, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(249, '85041500', 'RUDYANTO LUMBANRAJA', '', NULL, 'Tidak diketahui', '1985-04-01', 'L', 27, 1, 853, 64, 16, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(250, '96031075', 'ZULPAN SYAHPUTRA DAMANIK', '', NULL, 'Tidak diketahui', '1996-03-01', 'L', 29, 1, 853, 64, 16, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(251, '83061022', 'RAMADAN SIREGAR, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1983-06-01', 'L', 23, 1, 854, 64, 17, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(252, '86071792', 'WIDODO KABAN, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1986-07-01', 'L', 24, 1, 855, 64, 17, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(253, '75120864', 'GUNTAR TAMBUNAN', '', NULL, 'Tidak diketahui', '1975-12-01', 'L', 25, 1, 870, 64, 17, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(254, '82040124', 'JEFRI RICARDO SAMOSIR', '', NULL, 'Tidak diketahui', '1982-04-01', 'L', 25, 1, 856, 64, 17, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(255, '84020306', 'JUITO SUPANOTO PERANGIN-ANGIN', '', NULL, 'Tidak diketahui', '1984-02-01', 'L', 26, 1, 874, 64, 17, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(256, '83080042', 'YOPPHY RHODEAR MUNTHE', '', NULL, 'Tidak diketahui', '1983-08-01', 'L', 26, 1, 857, 64, 17, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(257, '86010311', 'TUMBUR SITOHANG', '', NULL, 'Tidak diketahui', '1986-01-01', 'L', 26, 1, 878, 64, 17, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(258, '84110202', 'DONI SURIANTO PURBA, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1984-11-01', 'L', 27, 1, 886, 64, 17, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(259, '89020409', 'PATAR F. ANRI SIAHAAN', '', NULL, 'Tidak diketahui', '1989-02-01', 'L', 27, 1, 884, 64, 17, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(260, '94090490', 'KURNIAWAN, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1994-09-01', 'L', 28, 1, 865, 64, 17, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:06', 'definitif', NULL),
(261, '95060432', 'ASHARI BUTAR-BUTAR, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1995-06-01', 'L', 28, 1, 865, 64, 17, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:06', 'definitif', NULL),
(262, '96061331', 'DIDI HOT BAGAS SITORUS', '', NULL, 'Tidak diketahui', '1996-06-01', 'L', 30, 1, 865, 64, 17, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:06', 'definitif', NULL),
(263, '01060884', 'HORAS J.M. ARITONANG', '', NULL, 'Tidak diketahui', '2001-06-01', 'L', 30, 1, 865, 64, 17, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:06', 'definitif', NULL),
(264, '04060050', 'ANDRE YEHEZKIEL HUTABARAT', '', NULL, 'Tidak diketahui', '2004-06-01', 'L', 30, 1, 865, 64, 17, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:06', 'definitif', NULL),
(265, '89080105', 'CLAUDIUS HARIS PARDEDE', '', NULL, 'Tidak diketahui', '1989-08-01', 'L', 28, 1, 865, 64, 17, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:06', 'definitif', NULL),
(266, '02051553', 'ZULKIFLI NASUTION', '', NULL, 'Tidak diketahui', '2002-05-01', 'L', 30, 1, 865, 64, 17, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:06', 'definitif', NULL),
(267, '70010290', 'RADIAMAN SIMARMATA', '', NULL, 'Tidak diketahui', '1970-01-01', 'L', 22, 1, 858, 65, 31, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(268, '82050839', 'HERMAWADI', '', NULL, 'Tidak diketahui', '1982-05-01', 'L', 26, 1, 879, 63, 31, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(269, '84091124', 'BISSAR LUMBANTUNGKUP', '', NULL, 'Tidak diketahui', '1984-09-01', 'L', 26, 1, 871, 63, 31, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(270, '70090340', 'BONAR JUBEL SIBARANI', '', NULL, 'Tidak diketahui', '1970-09-01', 'L', 27, 1, 882, 63, 31, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(271, '77020642', 'RAMLES SITANGGANG', '', NULL, 'Tidak diketahui', '1977-02-01', 'L', 27, 1, 875, 63, 31, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(272, '83031377', 'LUHUT SIRINGO-RINGO', '', NULL, 'Tidak diketahui', '1983-03-01', 'L', 28, 1, 863, 66, 31, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:05', 'definitif', NULL),
(273, '03100001', 'ANRIAN SIGALINGGING', '', NULL, 'Tidak diketahui', '2003-10-01', 'L', 30, 1, 863, 66, 31, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:05', 'definitif', NULL),
(274, '99110755', 'BONATUA LUMBANTUNGKUP', '', NULL, 'Tidak diketahui', '1999-11-01', 'L', 30, 1, 863, 66, 31, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:05', 'definitif', NULL),
(275, '03050116', 'ANDRE SUGIARTO MARPAUNG', '', NULL, 'Tidak diketahui', '2003-05-01', 'L', 30, 1, 863, 66, 31, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:05', 'definitif', NULL),
(276, '04030125', 'ERWIN KEVIN GULTOM', '', NULL, 'Tidak diketahui', '2004-03-01', 'L', 30, 1, 863, 66, 31, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:05', 'definitif', NULL),
(277, '70020298', 'BANGUN TUA DALIMUNTHE', '', NULL, 'Tidak diketahui', '1970-02-01', 'L', 22, 1, 859, 64, 19, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL),
(278, '81050713', 'LANCASTER ARIANTO CANDY PASARIBU, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1981-05-01', 'L', 25, 1, 881, 64, 19, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(279, '80090905', 'RUDY SETYAWAN', '', NULL, 'Tidak diketahui', '1980-09-01', 'L', 25, 1, 877, 64, 19, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(280, '80080892', 'MANGATUR TUA TINDAON', '', NULL, 'Tidak diketahui', '1980-08-01', 'L', 26, 1, 873, 64, 19, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(281, '87110154', 'RENO HOTMARULI TUA MANIK, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1987-11-01', 'L', 27, 1, 883, 64, 19, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:56', 'definitif', NULL),
(282, '79020443', 'HERBINTUPA SITANGGANG', '', NULL, 'Tidak diketahui', '1979-02-01', 'L', 28, 1, 864, 64, 19, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:06', 'definitif', NULL),
(283, '85121751', 'IBRAHIM TARIGAN', '', NULL, 'Tidak diketahui', '1985-12-01', 'L', 28, 1, 864, 64, 19, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:06', 'definitif', NULL),
(284, '98090406', 'AGUNG NUGRAHA HARIANJA, S.H.', 'S.H.', NULL, 'Tidak diketahui', '1998-09-01', 'L', 29, 1, 864, 64, 19, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:06', 'definitif', NULL),
(285, '98091274', 'DANI PUTRA RUMAHORBO', '', NULL, 'Tidak diketahui', '1998-09-01', 'L', 29, 1, 864, 64, 19, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:06', 'definitif', NULL),
(286, '01060198', 'KRISMAN JULU GULTOM', '', NULL, 'Tidak diketahui', '2001-06-01', 'L', 30, 1, 864, 64, 19, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:27:06', 'definitif', NULL),
(287, '198112262024211002', 'FERNANDO SILALAHI, A.Md.', '', NULL, 'Tidak diketahui', '1981-12-26', 'L', 1, 7, 768, 62, 2, NULL, NULL, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Alamat belum diisi', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2020-01-01', NULL, NULL, 0, 0, 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 01:48:26', 'definitif', NULL);

--
-- Trigger `personil`
--
DELIMITER $$
CREATE TRIGGER `before_personil_insert` BEFORE INSERT ON `personil` FOR EACH ROW BEGIN
    
    IF NEW.nrp IS NULL OR LENGTH(NEW.nrp) != 8 OR NEW.nrp NOT REGEXP '^[0-9]{8}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'NRP harus 8 digit angka';
    END IF;
    
    
    IF EXISTS (SELECT 1 FROM personil WHERE nrp = NEW.nrp AND is_active = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'NRP sudah terdaftar';
    END IF;
    
    
    IF TIMESTAMPDIFF(YEAR, NEW.tanggal_lahir, CURDATE()) < 18 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Umur minimal 18 tahun';
    END IF;
    
    
    IF NEW.masa_kerja_tahun IS NULL THEN
        SET NEW.masa_kerja_tahun = TIMESTAMPDIFF(YEAR, NEW.tanggal_masuk, CURDATE());
    END IF;
    
    IF NEW.masa_kerja_bulan IS NULL THEN
        SET NEW.masa_kerja_bulan = TIMESTAMPDIFF(MONTH, NEW.tanggal_masuk, CURDATE()) % 12;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_personil_update` BEFORE UPDATE ON `personil` FOR EACH ROW BEGIN
    
    IF NEW.nrp <> OLD.nrp THEN
        IF NEW.nrp IS NULL OR LENGTH(NEW.nrp) != 8 OR NEW.nrp NOT REGEXP '^[0-9]{8}$' THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'NRP harus 8 digit angka';
        END IF;
        
        IF EXISTS (SELECT 1 FROM personil WHERE nrp = NEW.nrp AND is_active = 1 AND id <> NEW.id) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'NRP sudah terdaftar';
        END IF;
    END IF;
    
    
    IF NEW.tanggal_masuk <> OLD.tanggal_masuk THEN
        SET NEW.masa_kerja_tahun = TIMESTAMPDIFF(YEAR, NEW.tanggal_masuk, CURDATE());
        SET NEW.masa_kerja_bulan = TIMESTAMPDIFF(MONTH, NEW.tanggal_masuk, CURDATE()) % 12;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `personil_audit_delete` AFTER DELETE ON `personil` FOR EACH ROW BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values, user_id, ip_address)
    VALUES ('personil', OLD.id, 'DELETE', JSON_OBJECT(
        'nama', OLD.nama,
        'nrp', OLD.nrp,
        'id_jabatan', OLD.id_jabatan,
        'id_bagian', OLD.id_bagian,
        'id_pangkat', OLD.id_pangkat
    ), @current_user_id, @client_ip);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `personil_audit_insert` AFTER INSERT ON `personil` FOR EACH ROW BEGIN
    INSERT INTO audit_log (table_name, record_id, action, new_values, user_id, ip_address)
    VALUES ('personil', NEW.id, 'INSERT', JSON_OBJECT(
        'nama', NEW.nama,
        'nrp', NEW.nrp,
        'id_jabatan', NEW.id_jabatan,
        'id_bagian', NEW.id_bagian,
        'id_pangkat', NEW.id_pangkat
    ), @current_user_id, @client_ip);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `personil_audit_update` AFTER UPDATE ON `personil` FOR EACH ROW BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values, new_values, user_id, ip_address)
    VALUES ('personil', NEW.id, 'UPDATE', 
        JSON_OBJECT('nama', OLD.nama, 'nrp', OLD.nrp, 'id_jabatan', OLD.id_jabatan, 'id_bagian', OLD.id_bagian, 'id_pangkat', OLD.id_pangkat),
        JSON_OBJECT('nama', NEW.nama, 'nrp', NEW.nrp, 'id_jabatan', NEW.id_jabatan, 'id_bagian', NEW.id_bagian, 'id_pangkat', NEW.id_pangkat),
        @current_user_id, @client_ip);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `file_type` varchar(10) DEFAULT NULL,
  `status` enum('generating','completed','failed') DEFAULT 'generating',
  `generated_by` int(11) DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `report_schedules`
--

CREATE TABLE `report_schedules` (
  `id` int(11) NOT NULL,
  `report_template_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `schedule_type` enum('daily','weekly','monthly','yearly') NOT NULL,
  `schedule_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`schedule_config`)),
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `recipients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`recipients`)),
  `is_active` tinyint(1) DEFAULT 1,
  `last_run_at` timestamp NULL DEFAULT NULL,
  `next_run_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `report_templates`
--

CREATE TABLE `report_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `query_template` text NOT NULL,
  `parameters_schema` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters_schema`)),
  `output_format` enum('html','pdf','excel','csv') DEFAULT 'html',
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_jabatan`
--

CREATE TABLE `riwayat_jabatan` (
  `id` int(11) NOT NULL,
  `id_personil` int(11) NOT NULL,
  `id_jabatan_lama` int(11) DEFAULT NULL,
  `id_jabatan_baru` int(11) NOT NULL,
  `id_unsur_lama` int(11) DEFAULT NULL,
  `id_unsur_baru` int(11) DEFAULT NULL,
  `id_bagian_lama` int(11) DEFAULT NULL,
  `id_bagian_baru` int(11) DEFAULT NULL,
  `id_satuan_fungsi_lama` int(11) DEFAULT NULL,
  `id_satuan_fungsi_baru` int(11) DEFAULT NULL,
  `tanggal_mutasi` date NOT NULL,
  `no_sk_mutasi` varchar(50) DEFAULT NULL,
  `tanggal_sk_mutasi` date DEFAULT NULL,
  `alasan_mutasi` text DEFAULT NULL,
  `jenis_mutasi` enum('promosi','mutasi','rotasi','demosi','pensiun','berhenti','meninggal') NOT NULL,
  `keterangan` text DEFAULT NULL,
  `is_aktif` tinyint(1) DEFAULT 1,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_pangkat`
--

CREATE TABLE `riwayat_pangkat` (
  `id` int(11) NOT NULL,
  `id_personil` int(11) NOT NULL,
  `id_pangkat_lama` int(11) DEFAULT NULL,
  `id_pangkat_baru` int(11) NOT NULL,
  `tanggal_kenaikan_pangkat` date NOT NULL,
  `no_sk_kenaikan` varchar(50) DEFAULT NULL,
  `tanggal_sk_kenaikan` date DEFAULT NULL,
  `masa_kerja_tahun` int(11) DEFAULT NULL,
  `masa_kerja_bulan` int(11) DEFAULT 0,
  `alasan_kenaikan` text DEFAULT NULL,
  `jenis_kenaikan` enum('reguler','luar_biasa','penghargaan','prestasi') DEFAULT 'reguler',
  `keterangan` text DEFAULT NULL,
  `is_aktif` tinyint(1) DEFAULT 1,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_penugasan`
--

CREATE TABLE `riwayat_penugasan` (
  `id` int(11) NOT NULL,
  `id_personil` int(11) NOT NULL,
  `id_jabatan` int(11) NOT NULL,
  `id_jenis_penugasan` int(11) NOT NULL,
  `id_alasan_penugasan` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `no_sk_penugasan` varchar(50) DEFAULT NULL,
  `tanggal_sk_penugasan` date DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `is_aktif` tinyint(1) DEFAULT 1,
  `is_expired` tinyint(1) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `personil_id` varchar(20) NOT NULL,
  `personil_name` varchar(255) NOT NULL,
  `bagian` varchar(100) NOT NULL,
  `shift_type` varchar(20) NOT NULL,
  `shift_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `google_event_id` varchar(255) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `system_config`
--

CREATE TABLE `system_config` (
  `id` int(11) NOT NULL,
  `key_name` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `data_type` enum('string','integer','boolean','json') DEFAULT 'string',
  `is_system` tinyint(1) DEFAULT 0,
  `is_public` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `system_config`
--

INSERT INTO `system_config` (`id`, `key_name`, `value`, `description`, `data_type`, `is_system`, `is_public`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'app_name', 'SPRIN - Sistem Manajemen Personil POLRES Samosir', 'Application name', 'string', 1, 1, NULL, NULL, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(2, 'app_version', '1.2.0', 'Application version', 'string', 1, 1, NULL, NULL, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(3, 'max_login_attempts', '5', 'Maximum login attempts before lockout', 'integer', 1, 0, NULL, NULL, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(4, 'session_timeout', '3600', 'Session timeout in seconds', 'integer', 1, 0, NULL, NULL, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(5, 'password_min_length', '8', 'Minimum password length', 'integer', 1, 0, NULL, NULL, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(6, 'backup_retention_days', '30', 'Backup retention period in days', 'integer', 1, 0, NULL, NULL, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(7, 'enable_notifications', 'true', 'Enable notification system', 'boolean', 1, 0, NULL, NULL, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(8, 'enable_audit_log', 'true', 'Enable audit logging', 'boolean', 1, 0, NULL, NULL, '2026-04-04 20:26:25', '2026-04-04 20:26:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `unsur`
--

CREATE TABLE `unsur` (
  `id` int(11) NOT NULL,
  `kode_unsur` varchar(20) NOT NULL,
  `nama_unsur` varchar(100) NOT NULL,
  `nama_lengkap` varchar(200) DEFAULT NULL,
  `kategori` enum('pimpinan','pembantu_pimpinan','pelaksana_tugas_pokok','pelaksana_kewilayahan','pendukung','lainnya') NOT NULL,
  `level_unsur` enum('level_1','level_2','level_3','level_4','level_5','level_6') NOT NULL,
  `parent_unsur_id` int(11) DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `unsur`
--

INSERT INTO `unsur` (`id`, `kode_unsur`, `nama_unsur`, `nama_lengkap`, `kategori`, `level_unsur`, `parent_unsur_id`, `urutan`, `deskripsi`, `is_active`, `is_deleted`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(61, 'UNSUR_PIMPINAN', 'UNSUR PIMPINAN', 'Unsur Pimpinan', 'pimpinan', 'level_1', NULL, 1, 'Pimpinan tertinggi POLRES', 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-02 16:45:35'),
(62, 'UNSUR PEMBANTU PIMPI', 'UNSUR PEMBANTU PIMPINAN & STAFF', 'Unsur Pembantu Pimpinan', 'pembantu_pimpinan', 'level_2', NULL, 2, 'Pembantu pimpinan POLRES', 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 18:59:48'),
(63, 'UNSUR_PELAKSANA', 'UNSUR PELAKSANA', 'Unsur Pelaksana Tugas Pokok', 'pelaksana_tugas_pokok', 'level_3', NULL, 3, 'Satuan fungsi pelaksana', 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-02 16:45:35'),
(64, 'UNSUR KEWILAYAHAN', 'UNSUR KEWILAYAHAN', 'Unsur Pelaksana Kewilayahan', 'pelaksana_kewilayahan', 'level_4', NULL, 4, 'Polsek dan unit kewilayahan', 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 17:17:24'),
(65, 'UNSUR PENDUKUNG', 'UNSUR PENDUKUNG', 'Unsur Pendukung', 'pendukung', 'level_5', NULL, 5, 'Unit pendukung dan administratif', 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 17:17:33'),
(66, 'UNSUR LAINNYA', 'UNSUR LAINNYA', 'Unsur Lainnya', 'lainnya', 'level_6', NULL, 6, 'Unit lainnya dan khusus', 1, 0, NULL, NULL, '2026-04-02 16:45:35', '2026-04-04 17:17:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `unsur_pimpinan`
--

CREATE TABLE `unsur_pimpinan` (
  `id` int(11) NOT NULL,
  `unsur_id` int(11) NOT NULL,
  `personil_id` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL DEFAULT curdate(),
  `tanggal_selesai` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','operator','viewer') NOT NULL DEFAULT 'viewer',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `login_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `email`, `full_name`, `role`, `is_active`, `last_login`, `login_attempts`, `locked_until`, `created_at`, `updated_at`, `created_by`) VALUES
(1, 'bagops', '$2y$10$abcde1234567890abcdef1234567890abcdef1234567890abcdef12345678', NULL, 'Administrator', 'admin', 1, NULL, 0, NULL, '2026-04-05 17:01:50', '2026-04-05 17:20:19', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL,
  `resource_type` varchar(50) DEFAULT NULL,
  `resource_id` int(11) DEFAULT NULL,
  `granted_by` int(11) DEFAULT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`permissions`)),
  `is_system` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user_roles`
--

INSERT INTO `user_roles` (`id`, `name`, `description`, `permissions`, `is_system`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'Super Administrator with all permissions', '[\"*\"]', 1, 1, NULL, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(2, 'admin', 'Administrator with most permissions', '[\"personil.*\", \"jabatan.*\", \"bagian.*\", \"unsur.*\", \"pangkat.*\", \"reports.*\", \"users.view\"]', 1, 1, NULL, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(3, 'manager', 'Manager with limited permissions', '[\"personil.view\", \"personil.create\", \"personil.update\", \"jabatan.view\", \"bagian.view\", \"unsur.view\", \"pangkat.view\", \"reports.view\"]', 1, 1, NULL, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(4, 'user', 'Regular user with basic permissions', '[\"personil.view\", \"jabatan.view\", \"bagian.view\", \"unsur.view\", \"pangkat.view\"]', 1, 1, NULL, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(5, 'viewer', 'Read-only access', '[\"personil.view\", \"jabatan.view\", \"bagian.view\", \"unsur.view\", \"pangkat.view\"]', 1, 1, NULL, '2026-04-04 20:26:25', '2026-04-04 20:26:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_role_assignments`
--

CREATE TABLE `user_role_assignments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `validation_rules`
--

CREATE TABLE `validation_rules` (
  `id` int(11) NOT NULL,
  `nama_rule` varchar(100) NOT NULL,
  `kategori` enum('personil','jabatan','pangkat','penugasan','kepegawaian') NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `rule_sql` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_pangkat_kategori`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_pangkat_kategori` (
`id` int(11)
,`nama_pangkat` varchar(100)
,`singkatan` varchar(20)
,`level_pangkat` int(11)
,`kategori` enum('POLRI','ASN','LAINNYA')
,`created_at` timestamp
,`updated_at` timestamp
,`nama_jenis` varchar(100)
,`kategori_jenis_pegawai` enum('POLRI','ASN','P3K','HONORARIUM','KONTRAK','LAINNYA')
);

-- --------------------------------------------------------

--
-- Struktur untuk view `v_pangkat_kategori`
--
DROP TABLE IF EXISTS `v_pangkat_kategori`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pangkat_kategori`  AS SELECT `p`.`id` AS `id`, `p`.`nama_pangkat` AS `nama_pangkat`, `p`.`singkatan` AS `singkatan`, `p`.`level_pangkat` AS `level_pangkat`, `p`.`kategori` AS `kategori`, `p`.`created_at` AS `created_at`, `p`.`updated_at` AS `updated_at`, `mjp`.`nama_jenis` AS `nama_jenis`, `mjp`.`kategori` AS `kategori_jenis_pegawai` FROM (`pangkat` `p` left join `master_jenis_pegawai` `mjp` on(`p`.`kategori` = `mjp`.`kategori` or `p`.`nama_pangkat` like concat('%',`mjp`.`nama_jenis`,'%') or `p`.`singkatan` like concat('%',`mjp`.`nama_jenis`,'%'))) ORDER BY `p`.`kategori` ASC, `p`.`level_pangkat` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `access_logs`
--
ALTER TABLE `access_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_access_logs_user` (`user_id`),
  ADD KEY `idx_access_logs_action` (`action`),
  ADD KEY `idx_access_logs_created` (`created_at`),
  ADD KEY `idx_access_logs_status` (`status_code`);

--
-- Indeks untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_logs_user` (`user_id`),
  ADD KEY `idx_activity_logs_type` (`activity_type`),
  ADD KEY `idx_activity_logs_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_activity_logs_created` (`created_at`);

--
-- Indeks untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_table_record` (`table_name`,`record_id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_created` (`created_at`);

--
-- Indeks untuk tabel `backups`
--
ALTER TABLE `backups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `backup_type` (`backup_type`);

--
-- Indeks untuk tabel `backup_history`
--
ALTER TABLE `backup_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `backup_schedule_id` (`backup_schedule_id`),
  ADD KEY `idx_backup_history_status` (`status`),
  ADD KEY `idx_backup_history_started` (`started_at`),
  ADD KEY `idx_backup_history_type` (`type`);

--
-- Indeks untuk tabel `backup_schedule`
--
ALTER TABLE `backup_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_backup_schedule_active` (`is_active`),
  ADD KEY `idx_backup_schedule_next_run` (`next_run_at`);

--
-- Indeks untuk tabel `bagian`
--
ALTER TABLE `bagian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_bagian` (`kode_bagian`),
  ADD UNIQUE KEY `unique_bagian_unsur` (`nama_bagian`,`id_unsur`),
  ADD KEY `idx_kode_bagian` (`kode_bagian`),
  ADD KEY `idx_nama_bagian` (`nama_bagian`),
  ADD KEY `idx_unsur` (`id_unsur`),
  ADD KEY `idx_parent` (`parent_bagian_id`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_level_bagian` (`level_bagian`),
  ADD KEY `idx_urutan` (`urutan`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_bagian_nama` (`nama_bagian`),
  ADD KEY `idx_bagian_urutan` (`urutan`),
  ADD KEY `idx_bagian_active` (`is_active`,`is_deleted`);

--
-- Indeks untuk tabel `bagian_pimpinan`
--
ALTER TABLE `bagian_pimpinan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_active_assignment` (`bagian_id`,`personil_id`,`tanggal_mulai`),
  ADD KEY `personil_id` (`personil_id`);

--
-- Indeks untuk tabel `error_logs`
--
ALTER TABLE `error_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_error_logs_level` (`level`),
  ADD KEY `idx_error_logs_created` (`created_at`),
  ADD KEY `idx_error_logs_user` (`user_id`);

--
-- Indeks untuk tabel `jabatan`
--
ALTER TABLE `jabatan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_jabatan` (`kode_jabatan`),
  ADD UNIQUE KEY `unique_jabatan_unsur` (`nama_jabatan`,`id_unsur`),
  ADD KEY `id_unit_pendukung` (`id_unit_pendukung`),
  ADD KEY `id_pangkat_minimal` (`id_pangkat_minimal`),
  ADD KEY `id_pangkat_maksimal` (`id_pangkat_maksimal`),
  ADD KEY `idx_kode_jabatan` (`kode_jabatan`),
  ADD KEY `idx_nama_jabatan` (`nama_jabatan`),
  ADD KEY `idx_unsur` (`id_unsur`),
  ADD KEY `id_satuan_fungsi` (`id_satuan_fungsi`),
  ADD KEY `idx_status_jabatan` (`id_status_jabatan`),
  ADD KEY `idx_level_eselon` (`level_eselon`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_jabatan_unsur_bagian` (`id_unsur`,`id_bagian`,`is_deleted`),
  ADD KEY `idx_jabatan_hierarchy` (`id_unsur`,`id_bagian`,`is_deleted`,`is_active`),
  ADD KEY `idx_jabatan_nama` (`nama_jabatan`),
  ADD KEY `idx_jabatan_bagian` (`id_bagian`),
  ADD KEY `idx_jabatan_eselon` (`level_eselon`);

--
-- Indeks untuk tabel `jenjang_karir`
--
ALTER TABLE `jenjang_karir`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pangkat_saat_ini` (`id_pangkat_saat_ini`),
  ADD KEY `idx_pangkat_berikutnya` (`id_pangkat_berikutnya`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indeks untuk tabel `master_alasan_penugasan`
--
ALTER TABLE `master_alasan_penugasan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`),
  ADD KEY `idx_kode` (`kode`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indeks untuk tabel `master_jenis_pegawai`
--
ALTER TABLE `master_jenis_pegawai`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_jenis` (`kode_jenis`),
  ADD KEY `idx_kode` (`kode_jenis`),
  ADD KEY `idx_kategori` (`kategori`);

--
-- Indeks untuk tabel `master_jenis_penugasan`
--
ALTER TABLE `master_jenis_penugasan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`),
  ADD KEY `idx_kode` (`kode`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indeks untuk tabel `master_pangkat_minimum_jabatan`
--
ALTER TABLE `master_pangkat_minimum_jabatan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_jabatan_pangkat` (`id_jabatan`,`id_pangkat_minimal`),
  ADD KEY `id_pangkat_maksimal` (`id_pangkat_maksimal`),
  ADD KEY `idx_jabatan` (`id_jabatan`),
  ADD KEY `idx_pangkat_minimal` (`id_pangkat_minimal`);

--
-- Indeks untuk tabel `master_pendidikan`
--
ALTER TABLE `master_pendidikan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_pendidikan` (`kode_pendidikan`),
  ADD KEY `idx_tingkat` (`tingkat_pendidikan`),
  ADD KEY `idx_kode` (`kode_pendidikan`);

--
-- Indeks untuk tabel `master_satuan_fungsi`
--
ALTER TABLE `master_satuan_fungsi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_satuan` (`kode_satuan`),
  ADD KEY `idx_kode_satuan` (`kode_satuan`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_level_satuan` (`level_satuan`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indeks untuk tabel `master_status_jabatan`
--
ALTER TABLE `master_status_jabatan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`),
  ADD KEY `idx_kode` (`kode`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_eselon` (`level_eselon`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indeks untuk tabel `master_unit_pendukung`
--
ALTER TABLE `master_unit_pendukung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_unit` (`kode_unit`),
  ADD KEY `idx_kode_unit` (`kode_unit`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`),
  ADD KEY `idx_notifications_status` (`status`),
  ADD KEY `idx_notifications_created` (`created_at`);

--
-- Indeks untuk tabel `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `pangkat`
--
ALTER TABLE `pangkat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_pangkat` (`nama_pangkat`),
  ADD KEY `idx_pangkat_nama` (`nama_pangkat`),
  ADD KEY `idx_pangkat_kategori` (`kategori`);

--
-- Indeks untuk tabel `personil`
--
ALTER TABLE `personil`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nrp` (`nrp`),
  ADD KEY `id_jenis_pegawai` (`id_jenis_pegawai`),
  ADD KEY `id_satuan_fungsi` (`id_satuan_fungsi`),
  ADD KEY `id_unit_pendukung` (`id_unit_pendukung`),
  ADD KEY `id_alasan_penugasan` (`id_alasan_penugasan`),
  ADD KEY `id_status_jabatan` (`id_status_jabatan`),
  ADD KEY `idx_nrp` (`nrp`),
  ADD KEY `idx_nama` (`nama`),
  ADD KEY `idx_pangkat` (`id_pangkat`),
  ADD KEY `idx_jabatan` (`id_jabatan`),
  ADD KEY `idx_unsur` (`id_unsur`),
  ADD KEY `idx_status_kepegawaian` (`id_status_kepegawaian`),
  ADD KEY `idx_jenis_penugasan` (`id_jenis_penugasan`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_personil_jabatan_active` (`id_jabatan`,`is_active`,`is_deleted`),
  ADD KEY `idx_personil_unsur_bagian` (`id_unsur`,`id_bagian`,`is_deleted`),
  ADD KEY `idx_personil_search` (`nama`,`nrp`,`is_active`,`is_deleted`),
  ADD KEY `idx_personil_org_structure` (`id_unsur`,`id_bagian`,`id_jabatan`,`is_deleted`),
  ADD KEY `idx_personil_nama` (`nama`),
  ADD KEY `idx_personil_nrp` (`nrp`),
  ADD KEY `idx_personil_jabatan` (`id_jabatan`),
  ADD KEY `idx_personil_bagian` (`id_bagian`),
  ADD KEY `idx_personil_pangkat` (`id_pangkat`),
  ADD KEY `idx_personil_active` (`is_active`,`is_deleted`);

--
-- Indeks untuk tabel `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reports_type` (`type`),
  ADD KEY `idx_reports_status` (`status`),
  ADD KEY `idx_reports_generated_by` (`generated_by`);

--
-- Indeks untuk tabel `report_schedules`
--
ALTER TABLE `report_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_template_id` (`report_template_id`),
  ADD KEY `idx_schedules_active` (`is_active`),
  ADD KEY `idx_schedules_next_run` (`next_run_at`);

--
-- Indeks untuk tabel `report_templates`
--
ALTER TABLE `report_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `riwayat_jabatan`
--
ALTER TABLE `riwayat_jabatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_jabatan_lama` (`id_jabatan_lama`),
  ADD KEY `id_unsur_lama` (`id_unsur_lama`),
  ADD KEY `id_unsur_baru` (`id_unsur_baru`),
  ADD KEY `id_bagian_lama` (`id_bagian_lama`),
  ADD KEY `id_bagian_baru` (`id_bagian_baru`),
  ADD KEY `id_satuan_fungsi_lama` (`id_satuan_fungsi_lama`),
  ADD KEY `id_satuan_fungsi_baru` (`id_satuan_fungsi_baru`),
  ADD KEY `idx_personil` (`id_personil`),
  ADD KEY `idx_jabatan_baru` (`id_jabatan_baru`),
  ADD KEY `idx_tanggal_mutasi` (`tanggal_mutasi`),
  ADD KEY `idx_jenis_mutasi` (`jenis_mutasi`),
  ADD KEY `idx_aktif` (`is_aktif`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `riwayat_pangkat`
--
ALTER TABLE `riwayat_pangkat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pangkat_lama` (`id_pangkat_lama`),
  ADD KEY `idx_personil` (`id_personil`),
  ADD KEY `idx_pangkat_baru` (`id_pangkat_baru`),
  ADD KEY `idx_tanggal_kenaikan` (`tanggal_kenaikan_pangkat`),
  ADD KEY `idx_jenis_kenaikan` (`jenis_kenaikan`),
  ADD KEY `idx_aktif` (`is_aktif`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `riwayat_penugasan`
--
ALTER TABLE `riwayat_penugasan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_alasan_penugasan` (`id_alasan_penugasan`),
  ADD KEY `idx_personil` (`id_personil`),
  ADD KEY `idx_jabatan` (`id_jabatan`),
  ADD KEY `idx_jenis_penugasan` (`id_jenis_penugasan`),
  ADD KEY `idx_tanggal_mulai` (`tanggal_mulai`),
  ADD KEY `idx_tanggal_selesai` (`tanggal_selesai`),
  ADD KEY `idx_aktif` (`is_aktif`),
  ADD KEY `idx_expired` (`is_expired`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_personil` (`personil_id`),
  ADD KEY `idx_date` (`shift_date`),
  ADD KEY `idx_bagian` (`bagian`);

--
-- Indeks untuk tabel `system_config`
--
ALTER TABLE `system_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_name` (`key_name`),
  ADD KEY `idx_system_config_key` (`key_name`),
  ADD KEY `idx_system_config_public` (`is_public`);

--
-- Indeks untuk tabel `unsur`
--
ALTER TABLE `unsur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_unsur` (`kode_unsur`),
  ADD KEY `idx_kode_unsur` (`kode_unsur`),
  ADD KEY `idx_nama_unsur` (`nama_unsur`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_level_unsur` (`level_unsur`),
  ADD KEY `idx_parent` (`parent_unsur_id`),
  ADD KEY `idx_urutan` (`urutan`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_unsur_order` (`urutan`,`id`),
  ADD KEY `idx_unsur_nama` (`nama_unsur`),
  ADD KEY `idx_unsur_urutan` (`urutan`),
  ADD KEY `idx_unsur_active` (`is_active`,`is_deleted`);

--
-- Indeks untuk tabel `unsur_pimpinan`
--
ALTER TABLE `unsur_pimpinan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_unsur_active` (`unsur_id`,`tanggal_selesai`),
  ADD KEY `idx_personil_active` (`personil_id`,`tanggal_selesai`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role` (`role`),
  ADD KEY `is_active` (`is_active`);

--
-- Indeks untuk tabel `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_permission` (`user_id`,`permission`,`resource_type`,`resource_id`),
  ADD KEY `idx_user_permissions_user` (`user_id`),
  ADD KEY `idx_user_permissions_permission` (`permission`),
  ADD KEY `idx_user_permissions_resource` (`resource_type`,`resource_id`);

--
-- Indeks untuk tabel `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `user_role_assignments`
--
ALTER TABLE `user_role_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_role` (`user_id`,`role_id`),
  ADD KEY `idx_user_role_assignments_user` (`user_id`),
  ADD KEY `idx_user_role_assignments_role` (`role_id`);

--
-- Indeks untuk tabel `validation_rules`
--
ALTER TABLE `validation_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nama_rule` (`nama_rule`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_active` (`is_active`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `access_logs`
--
ALTER TABLE `access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `backups`
--
ALTER TABLE `backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `backup_history`
--
ALTER TABLE `backup_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `backup_schedule`
--
ALTER TABLE `backup_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `bagian`
--
ALTER TABLE `bagian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `bagian_pimpinan`
--
ALTER TABLE `bagian_pimpinan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `error_logs`
--
ALTER TABLE `error_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jabatan`
--
ALTER TABLE `jabatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=891;

--
-- AUTO_INCREMENT untuk tabel `jenjang_karir`
--
ALTER TABLE `jenjang_karir`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `master_alasan_penugasan`
--
ALTER TABLE `master_alasan_penugasan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `master_jenis_pegawai`
--
ALTER TABLE `master_jenis_pegawai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `master_jenis_penugasan`
--
ALTER TABLE `master_jenis_penugasan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `master_pangkat_minimum_jabatan`
--
ALTER TABLE `master_pangkat_minimum_jabatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `master_pendidikan`
--
ALTER TABLE `master_pendidikan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `master_satuan_fungsi`
--
ALTER TABLE `master_satuan_fungsi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `master_status_jabatan`
--
ALTER TABLE `master_status_jabatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `master_unit_pendukung`
--
ALTER TABLE `master_unit_pendukung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `pangkat`
--
ALTER TABLE `pangkat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT untuk tabel `personil`
--
ALTER TABLE `personil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=288;

--
-- AUTO_INCREMENT untuk tabel `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `report_schedules`
--
ALTER TABLE `report_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `report_templates`
--
ALTER TABLE `report_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `riwayat_jabatan`
--
ALTER TABLE `riwayat_jabatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `riwayat_pangkat`
--
ALTER TABLE `riwayat_pangkat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `riwayat_penugasan`
--
ALTER TABLE `riwayat_penugasan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `system_config`
--
ALTER TABLE `system_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT untuk tabel `unsur`
--
ALTER TABLE `unsur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT untuk tabel `unsur_pimpinan`
--
ALTER TABLE `unsur_pimpinan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `user_role_assignments`
--
ALTER TABLE `user_role_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `validation_rules`
--
ALTER TABLE `validation_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `backup_history`
--
ALTER TABLE `backup_history`
  ADD CONSTRAINT `backup_history_ibfk_1` FOREIGN KEY (`backup_schedule_id`) REFERENCES `backup_schedule` (`id`);

--
-- Ketidakleluasaan untuk tabel `bagian`
--
ALTER TABLE `bagian`
  ADD CONSTRAINT `bagian_ibfk_1` FOREIGN KEY (`id_unsur`) REFERENCES `unsur` (`id`),
  ADD CONSTRAINT `bagian_ibfk_2` FOREIGN KEY (`parent_bagian_id`) REFERENCES `bagian` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `bagian_pimpinan`
--
ALTER TABLE `bagian_pimpinan`
  ADD CONSTRAINT `bagian_pimpinan_ibfk_1` FOREIGN KEY (`bagian_id`) REFERENCES `bagian` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bagian_pimpinan_ibfk_2` FOREIGN KEY (`personil_id`) REFERENCES `personil` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `jabatan`
--
ALTER TABLE `jabatan`
  ADD CONSTRAINT `jabatan_ibfk_1` FOREIGN KEY (`id_unsur`) REFERENCES `unsur` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jabatan_ibfk_2` FOREIGN KEY (`id_bagian`) REFERENCES `bagian` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jabatan_ibfk_3` FOREIGN KEY (`id_satuan_fungsi`) REFERENCES `master_satuan_fungsi` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jabatan_ibfk_4` FOREIGN KEY (`id_unit_pendukung`) REFERENCES `master_unit_pendukung` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jabatan_ibfk_5` FOREIGN KEY (`id_status_jabatan`) REFERENCES `master_status_jabatan` (`id`),
  ADD CONSTRAINT `jabatan_ibfk_6` FOREIGN KEY (`id_pangkat_minimal`) REFERENCES `pangkat` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jabatan_ibfk_7` FOREIGN KEY (`id_pangkat_maksimal`) REFERENCES `pangkat` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `jenjang_karir`
--
ALTER TABLE `jenjang_karir`
  ADD CONSTRAINT `jenjang_karir_ibfk_1` FOREIGN KEY (`id_pangkat_saat_ini`) REFERENCES `pangkat` (`id`),
  ADD CONSTRAINT `jenjang_karir_ibfk_2` FOREIGN KEY (`id_pangkat_berikutnya`) REFERENCES `pangkat` (`id`);

--
-- Ketidakleluasaan untuk tabel `master_pangkat_minimum_jabatan`
--
ALTER TABLE `master_pangkat_minimum_jabatan`
  ADD CONSTRAINT `master_pangkat_minimum_jabatan_ibfk_1` FOREIGN KEY (`id_jabatan`) REFERENCES `jabatan` (`id`),
  ADD CONSTRAINT `master_pangkat_minimum_jabatan_ibfk_2` FOREIGN KEY (`id_pangkat_minimal`) REFERENCES `pangkat` (`id`),
  ADD CONSTRAINT `master_pangkat_minimum_jabatan_ibfk_3` FOREIGN KEY (`id_pangkat_maksimal`) REFERENCES `pangkat` (`id`);

--
-- Ketidakleluasaan untuk tabel `personil`
--
ALTER TABLE `personil`
  ADD CONSTRAINT `personil_ibfk_1` FOREIGN KEY (`id_pangkat`) REFERENCES `pangkat` (`id`),
  ADD CONSTRAINT `personil_ibfk_10` FOREIGN KEY (`id_alasan_penugasan`) REFERENCES `master_alasan_penugasan` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `personil_ibfk_11` FOREIGN KEY (`id_status_jabatan`) REFERENCES `master_status_jabatan` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `personil_ibfk_2` FOREIGN KEY (`id_jenis_pegawai`) REFERENCES `master_jenis_pegawai` (`id`),
  ADD CONSTRAINT `personil_ibfk_3` FOREIGN KEY (`id_jabatan`) REFERENCES `jabatan` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `personil_ibfk_4` FOREIGN KEY (`id_unsur`) REFERENCES `unsur` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `personil_ibfk_5` FOREIGN KEY (`id_bagian`) REFERENCES `bagian` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `personil_ibfk_6` FOREIGN KEY (`id_satuan_fungsi`) REFERENCES `master_satuan_fungsi` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `personil_ibfk_7` FOREIGN KEY (`id_unit_pendukung`) REFERENCES `master_unit_pendukung` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `personil_ibfk_8` FOREIGN KEY (`id_status_kepegawaian`) REFERENCES `master_status_kepegawaian` (`id`),
  ADD CONSTRAINT `personil_ibfk_9` FOREIGN KEY (`id_jenis_penugasan`) REFERENCES `master_jenis_penugasan` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `report_schedules`
--
ALTER TABLE `report_schedules`
  ADD CONSTRAINT `report_schedules_ibfk_1` FOREIGN KEY (`report_template_id`) REFERENCES `report_templates` (`id`);

--
-- Ketidakleluasaan untuk tabel `riwayat_jabatan`
--
ALTER TABLE `riwayat_jabatan`
  ADD CONSTRAINT `riwayat_jabatan_ibfk_1` FOREIGN KEY (`id_personil`) REFERENCES `personil` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_jabatan_ibfk_2` FOREIGN KEY (`id_jabatan_lama`) REFERENCES `jabatan` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `riwayat_jabatan_ibfk_3` FOREIGN KEY (`id_jabatan_baru`) REFERENCES `jabatan` (`id`),
  ADD CONSTRAINT `riwayat_jabatan_ibfk_4` FOREIGN KEY (`id_unsur_lama`) REFERENCES `unsur` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `riwayat_jabatan_ibfk_5` FOREIGN KEY (`id_unsur_baru`) REFERENCES `unsur` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `riwayat_jabatan_ibfk_6` FOREIGN KEY (`id_bagian_lama`) REFERENCES `bagian` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `riwayat_jabatan_ibfk_7` FOREIGN KEY (`id_bagian_baru`) REFERENCES `bagian` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `riwayat_jabatan_ibfk_8` FOREIGN KEY (`id_satuan_fungsi_lama`) REFERENCES `master_satuan_fungsi` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `riwayat_jabatan_ibfk_9` FOREIGN KEY (`id_satuan_fungsi_baru`) REFERENCES `master_satuan_fungsi` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `riwayat_pangkat`
--
ALTER TABLE `riwayat_pangkat`
  ADD CONSTRAINT `riwayat_pangkat_ibfk_1` FOREIGN KEY (`id_personil`) REFERENCES `personil` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_pangkat_ibfk_2` FOREIGN KEY (`id_pangkat_lama`) REFERENCES `pangkat` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `riwayat_pangkat_ibfk_3` FOREIGN KEY (`id_pangkat_baru`) REFERENCES `pangkat` (`id`);

--
-- Ketidakleluasaan untuk tabel `riwayat_penugasan`
--
ALTER TABLE `riwayat_penugasan`
  ADD CONSTRAINT `riwayat_penugasan_ibfk_1` FOREIGN KEY (`id_personil`) REFERENCES `personil` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_penugasan_ibfk_2` FOREIGN KEY (`id_jabatan`) REFERENCES `jabatan` (`id`),
  ADD CONSTRAINT `riwayat_penugasan_ibfk_3` FOREIGN KEY (`id_jenis_penugasan`) REFERENCES `master_jenis_penugasan` (`id`),
  ADD CONSTRAINT `riwayat_penugasan_ibfk_4` FOREIGN KEY (`id_alasan_penugasan`) REFERENCES `master_alasan_penugasan` (`id`);

--
-- Ketidakleluasaan untuk tabel `unsur`
--
ALTER TABLE `unsur`
  ADD CONSTRAINT `unsur_ibfk_1` FOREIGN KEY (`parent_unsur_id`) REFERENCES `unsur` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `unsur_pimpinan`
--
ALTER TABLE `unsur_pimpinan`
  ADD CONSTRAINT `unsur_pimpinan_ibfk_1` FOREIGN KEY (`unsur_id`) REFERENCES `unsur` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `unsur_pimpinan_ibfk_2` FOREIGN KEY (`personil_id`) REFERENCES `personil` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `user_role_assignments`
--
ALTER TABLE `user_role_assignments`
  ADD CONSTRAINT `user_role_assignments_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `user_roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
