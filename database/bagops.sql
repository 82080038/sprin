-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 28 Mar 2026 pada 23.04
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
-- Struktur dari tabel `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `operation_id` int(11) NOT NULL,
  `personil_id` varchar(20) NOT NULL,
  `personil_name` varchar(255) NOT NULL,
  `role` varchar(100) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `bagian`
--

CREATE TABLE `bagian` (
  `id` int(11) NOT NULL,
  `kode_bagian` varchar(50) NOT NULL,
  `nama_bagian` varchar(100) NOT NULL,
  `id_unsur` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bagian`
--

INSERT INTO `bagian` (`id`, `kode_bagian`, `nama_bagian`, `id_unsur`, `deskripsi`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'PIMPINAN', 'PIMPINAN', 1, 'Unit Pimpinan POLRES', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(2, 'BAG_OPS', 'BAG OPS', 2, 'Bagian Operasional', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(3, 'BAG_REN', 'BAG REN', 2, 'Bagian Perencanaan', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(4, 'BAG_SDM', 'BAG SDM', 2, 'Bagian Sumber Daya Manusia', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(5, 'BAG_LOG', 'BAG LOG', 2, 'Bagian Logistik', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(6, 'SAT_INTELKAM', 'SAT INTELKAM', 3, 'Satuan Intelijen dan Keamanan', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(7, 'SAT_RESKRIM', 'SAT RESKRIM', 3, 'Satuan Reserse Kriminal', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(8, 'SAT_RESNARKOBA', 'SAT RESNARKOBA', 3, 'Satuan Reserse Narkoba', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(9, 'SAT_LANTAS', 'SAT LANTAS', 3, 'Satuan Lalu Lintas', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(10, 'SAT_SAMAPTA', 'SAT SAMAPTA', 3, 'Satuan Pengamanan', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(11, 'SAT_PAMOBVIT', 'SAT PAMOBVIT', 3, 'Satuan Pengamanan Objek Vital', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(12, 'SAT_POLAIRUD', 'SAT POLAIRUD', 3, 'Satuan Polisi Air dan Udara', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(13, 'SAT_TAHTI', 'SAT TAHTI', 3, 'Satuan Tata Usaha', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(14, 'SAT_BINMAS', 'SAT BINMAS', 3, 'Satuan Pembinaan Masyarakat', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(15, 'POLSEK_HARIAN_BOHO', 'POLSEK HARIAN BOHO', 4, 'Polsek Harian Boho', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(16, 'POLSEK_PALIPI', 'POLSEK PALIPI', 4, 'Polsek Palipi', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(17, 'POLSEK_SIMANINDO', 'POLSEK SIMANINDO', 4, 'Polsek Simanindo', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(18, 'POLSEK_ONAN_RUNGGU', 'POLSEK ONAN RUNGGU', 4, 'Polsek Onan Runggu', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(19, 'POLSEK_PANGURURAN', 'POLSEK PANGURURAN', 4, 'Polsek Pangururan', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(20, 'SPKT', 'SPKT', 5, 'Sentra Pelayanan Kepolisian Terpadu', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(21, 'SIUM', 'SIUM', 5, 'Satuan Intelijen Umum', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(22, 'SIKEU', 'SIKEU', 5, 'Satuan Keuangan', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(23, 'SIDOKKES', 'SIDOKKES', 5, 'Satuan Dokter Kesehatan', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(24, 'SIWAS', 'SIWAS', 5, 'Satuan Pengawasan Internal', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(25, 'SITIK', 'SITIK', 5, 'Satuan Identifikasi dan Teknologi Forensik', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(26, 'SIKUM', 'SIKUM', 5, 'Satuan Komunikasi', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(27, 'SIPROPAM', 'SIPROPAM', 5, 'Satuan Profesi dan Pengamanan', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(28, 'SIHUMAS', 'SIHUMAS', 5, 'Satuan Humas', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(29, 'BKO', 'BKO', 6, 'Bantuan Kendali Operasional', 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57');

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

-- --------------------------------------------------------

--
-- Struktur dari tabel `calendar_tokens`
--

CREATE TABLE `calendar_tokens` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `access_token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `calendar_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jabatan`
--

CREATE TABLE `jabatan` (
  `id` int(11) NOT NULL,
  `kode_jabatan` varchar(50) NOT NULL,
  `nama_jabatan` varchar(100) NOT NULL,
  `id_unsur` int(11) DEFAULT NULL,
  `tingkat_jabatan` varchar(50) DEFAULT NULL,
  `eselon` varchar(20) DEFAULT NULL,
  `golongan` varchar(20) DEFAULT NULL,
  `is_pimpinan` tinyint(1) DEFAULT 0,
  `is_pembantu_pimpinan` tinyint(1) DEFAULT 0,
  `is_kepala_unit` tinyint(1) DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jabatan`
--

INSERT INTO `jabatan` (`id`, `kode_jabatan`, `nama_jabatan`, `id_unsur`, `tingkat_jabatan`, `eselon`, `golongan`, `is_pimpinan`, `is_pembantu_pimpinan`, `is_kepala_unit`, `deskripsi`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'KAPOLRES_SAMOSIR', 'KAPOLRES SAMOSIR', 1, 'PIMPINAN', NULL, NULL, 1, 0, 0, 'Jabatan KAPOLRES SAMOSIR di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(2, 'WAKAPOLRES', 'WAKAPOLRES', 1, 'PIMPINAN', NULL, NULL, 1, 0, 0, 'Jabatan WAKAPOLRES di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(3, 'KABAG_OPS', 'KABAG OPS', 2, 'PEMBANTU PIMPINAN', NULL, NULL, 0, 1, 1, 'Jabatan KABAG OPS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(4, 'PS._PAUR_SUBBAGBINOPS', 'PS. PAUR SUBBAGBINOPS', 2, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan PS. PAUR SUBBAGBINOPS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(5, 'BA_MIN_BAG_OPS', 'BA MIN BAG OPS', 2, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BA MIN BAG OPS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(6, 'ASN_BAG_OPS', 'ASN BAG OPS', 2, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan ASN BAG OPS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(7, 'KA_SPKT', 'KA SPKT', 5, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan KA SPKT di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(8, 'PAMAPTA_1', 'PAMAPTA 1', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan PAMAPTA 1 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(9, 'PAMAPTA_2', 'PAMAPTA 2', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan PAMAPTA 2 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(10, 'PAMAPTA_3', 'PAMAPTA 3', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan PAMAPTA 3 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(11, 'BAMIN_PAMAPTA_2', 'BAMIN PAMAPTA 2', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BAMIN PAMAPTA 2 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(12, 'BAMIN_PAMAPTA_3', 'BAMIN PAMAPTA 3', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BAMIN PAMAPTA 3 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(13, 'BAMIN_PAMAPTA_1', 'BAMIN PAMAPTA 1', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BAMIN PAMAPTA 1 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(14, 'PAURSUBBAGPROGAR', 'PAURSUBBAGPROGAR', 2, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan PAURSUBBAGPROGAR di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(15, 'BA_MIN_BAG_REN', 'BA MIN BAG REN', 2, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BA MIN BAG REN di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(16, 'PS._KABAG_SDM', 'PS. KABAG SDM', 2, 'PEMBANTU PIMPINAN', NULL, NULL, 0, 1, 1, 'Jabatan PS. KABAG SDM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(17, 'PAURSUBBAGBINKAR', 'PAURSUBBAGBINKAR', 2, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan PAURSUBBAGBINKAR di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(18, 'BA_MIN_BAG_SDM', 'BA MIN BAG SDM', 2, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BA MIN BAG SDM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(19, 'BA_POLRES_SAMOSIR', 'BA POLRES SAMOSIR', 2, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BA POLRES SAMOSIR di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(20, 'ADC_KAPOLRES', 'ADC KAPOLRES', 2, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan ADC KAPOLRES di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(21, 'BINTARA_SATLANTAS', 'BINTARA SATLANTAS', 3, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SATLANTAS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(22, 'PLT._KASUBBAGBEKPAL', 'Plt. KASUBBAGBEKPAL', 2, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan Plt. KASUBBAGBEKPAL di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(23, 'BA_MIN_BAG_LOG', 'BA MIN BAG LOG', 2, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BA MIN BAG LOG di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(24, 'PS._KASIUM', 'PS. KASIUM', 5, 'STAF', NULL, NULL, 0, 0, 1, 'Jabatan PS. KASIUM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(25, 'BINTARA_SIUM', 'BINTARA SIUM', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SIUM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(26, 'PS._KASIKEU', 'PS. KASIKEU', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan PS. KASIKEU di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(27, 'BINTARA_SIKEU', 'BINTARA SIKEU', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SIKEU di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(28, 'KASIDOKKES', 'KASIDOKKES', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan KASIDOKKES di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(29, 'BA_SIDOKKES', 'BA SIDOKKES', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BA SIDOKKES di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(30, 'PLT._KASIWAS', 'Plt. KASIWAS', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan Plt. KASIWAS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(31, 'BINTARA_SIWAS', 'BINTARA SIWAS', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SIWAS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(32, 'BINTARA_SITIK', 'BINTARA SITIK', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SITIK di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(33, 'KASUBSIBANKUM', 'KASUBSIBANKUM', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan KASUBSIBANKUM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(34, 'BINTARA_SIKUM', 'BINTARA SIKUM', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SIKUM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(35, 'PS._KASIPROPAM', 'PS. KASIPROPAM', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan PS. KASIPROPAM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(36, 'PS._KANIT_PROPOS', 'PS. KANIT PROPOS', 5, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANIT PROPOS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(37, 'PS._KANIT_PAMINAL', 'PS. KANIT PAMINAL', 5, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANIT PAMINAL di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(38, 'BINTARA_SIPROPAM', 'BINTARA SIPROPAM', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SIPROPAM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(39, 'BINTARA_SIHUMAS', 'BINTARA SIHUMAS', 5, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SIHUMAS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(40, 'KAURBINOPS', 'KAURBINOPS', 3, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan KAURBINOPS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(41, 'BINTARA_SAT_BINMAS', 'BINTARA SAT BINMAS', 3, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SAT BINMAS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(42, 'PS._KASAT_INTELKAM', 'PS. KASAT INTELKAM', 3, 'PEMBANTU PIMPINAN', NULL, NULL, 0, 1, 1, 'Jabatan PS. KASAT INTELKAM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(43, 'PS._KAURMINTU', 'PS. KAURMINTU', 3, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan PS. KAURMINTU di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(44, 'PS._KANIT_3', 'PS. KANIT 3', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANIT 3 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(45, 'PS._KANIT_1', 'PS. KANIT 1', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANIT 1 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(46, 'PS._KANIT_2', 'PS. KANIT 2', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANIT 2 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(47, 'BINTARA_SAT_INTELKAM', 'BINTARA SAT INTELKAM', 3, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SAT INTELKAM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(48, 'BINTARA_SATINTELKAM', 'BINTARA SATINTELKAM', 3, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SATINTELKAM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(49, 'KASAT_RESKRIM', 'KASAT RESKRIM', 3, 'PEMBANTU PIMPINAN', NULL, NULL, 0, 1, 1, 'Jabatan KASAT RESKRIM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(50, 'KANITIDIK_3', 'KANITIDIK 3', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan KANITIDIK 3 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(51, 'KANITIDIK_4', 'KANITIDIK 4', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan KANITIDIK 4 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(52, 'KANITIDIK_1', 'KANITIDIK 1', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan KANITIDIK 1 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(53, 'KANITIDIK_5', 'KANITIDIK 5', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan KANITIDIK 5 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(54, 'PS._KANITIDIK_2', 'PS. KANITIDIK 2', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANITIDIK 2 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(55, 'PS._KANIT_IDENTIFIKASI', 'PS. KANIT IDENTIFIKASI', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANIT IDENTIFIKASI di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(56, 'BINTARA_SAT_RESKRIM', 'BINTARA SAT RESKRIM', 3, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SAT RESKRIM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(57, 'KASATRESNARKOBA', 'KASATRESNARKOBA', 3, 'PEMBANTU PIMPINAN', NULL, NULL, 0, 1, 1, 'Jabatan KASATRESNARKOBA di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(58, 'PS.KANIT_IDIK_1', 'PS.KANIT IDIK 1', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS.KANIT IDIK 1 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(59, 'BINTARA_SATRESNARKOBA', 'BINTARA SATRESNARKOBA', 3, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SATRESNARKOBA di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(60, 'KASAT_SAMAPTA', 'KASAT SAMAPTA', 3, 'PEMBANTU PIMPINAN', NULL, NULL, 0, 1, 1, 'Jabatan KASAT SAMAPTA di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(61, 'PS._KAURBINOPS', 'PS. KAURBINOPS', 3, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan PS. KAURBINOPS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(62, 'PS._KANIT_DALMAS_2', 'PS. KANIT DALMAS 2', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANIT DALMAS 2 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(63, 'PS._KANIT_TURJAWALI', 'PS. KANIT TURJAWALI', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANIT TURJAWALI di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(64, 'BINTARA_SAT_SAMAPTA', 'BINTARA SAT SAMAPTA', 3, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SAT SAMAPTA di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(65, 'KASAT_PAMOBVIT', 'KASAT PAMOBVIT', 3, 'PEMBANTU PIMPINAN', NULL, NULL, 0, 1, 1, 'Jabatan KASAT PAMOBVIT di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(66, 'PS._KANITPAMWASTER', 'PS. KANITPAMWASTER', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANITPAMWASTER di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(67, 'PS._KANITPAMWISATA', 'PS. KANITPAMWISATA', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANITPAMWISATA di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(68, 'PS._PANIT_PAMWASTER', 'PS. PANIT PAMWASTER', 3, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan PS. PANIT PAMWASTER di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(69, 'BINTARA_SAT_PAMOBVIT', 'BINTARA SAT PAMOBVIT', 3, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SAT PAMOBVIT di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(70, 'KASAT_LANTAS', 'KASAT LANTAS', 3, 'PEMBANTU PIMPINAN', NULL, NULL, 0, 1, 1, 'Jabatan KASAT LANTAS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(71, 'KANITREGIDENT_LANTAS', 'KANITREGIDENT LANTAS', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan KANITREGIDENT LANTAS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(72, 'PS._KANITGAKKUM', 'PS. KANITGAKKUM', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANITGAKKUM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(73, 'PS._KANITTURJAWALI', 'PS. KANITTURJAWALI', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANITTURJAWALI di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(74, 'PS._KANITKAMSEL', 'PS. KANITKAMSEL', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANITKAMSEL di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(75, 'BINTARA_SAT_LANTAS', 'BINTARA SAT LANTAS', 3, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SAT LANTAS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(76, 'KASAT_POLAIRUD', 'KASAT POLAIRUD', 3, 'PEMBANTU PIMPINAN', NULL, NULL, 0, 1, 1, 'Jabatan KASAT POLAIRUD di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(77, 'PS._KANITPATROLI', 'PS. KANITPATROLI', 3, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANITPATROLI di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(78, 'BINTARA_SATPOLAIRUD', 'BINTARA SATPOLAIRUD', 3, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SATPOLAIRUD di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(79, 'PS._KASAT_TAHTI', 'PS. KASAT TAHTI', 3, 'PEMBANTU PIMPINAN', NULL, NULL, 0, 1, 1, 'Jabatan PS. KASAT TAHTI di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(80, 'BINTARA_SAT_TAHTI', 'BINTARA SAT TAHTI', 3, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA SAT TAHTI di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(81, 'PS._KAPOLSEK_HARIAN_BOHO', 'PS. KAPOLSEK HARIAN BOHO', 4, 'PEMBANTU PIMPINAN', NULL, NULL, 0, 1, 1, 'Jabatan PS. KAPOLSEK HARIAN BOHO di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(82, 'PS._KANIT_INTELKAM', 'PS. KANIT INTELKAM', 4, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANIT INTELKAM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(83, 'PS._KANIT_BINMAS', 'PS. KANIT BINMAS', 4, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANIT BINMAS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(84, 'PS._KANIT_RESKRIM', 'PS. KANIT RESKRIM', 4, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANIT RESKRIM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(85, 'PS.KANIT_SAMAPTA', 'PS.KANIT SAMAPTA', 4, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS.KANIT SAMAPTA di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(86, 'BINTARA_POLSEK', 'BINTARA POLSEK', 4, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA POLSEK di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(87, 'KAPOLSEK_PALIPI', 'KAPOLSEK PALIPI', 4, 'PEMBANTU PIMPINAN', NULL, NULL, 0, 1, 1, 'Jabatan KAPOLSEK PALIPI di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(88, 'PS._KA_SPKT_1', 'PS. KA SPKT 1', 4, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KA SPKT 1 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(89, 'PS._KANIT_SAMAPTA', 'PS. KANIT SAMAPTA', 4, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANIT SAMAPTA di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(90, 'PS._KA_SPKT_2', 'PS. KA SPKT 2', 4, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KA SPKT 2 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(91, 'BINTARA__POLSEK', 'BINTARA  POLSEK', 4, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan BINTARA  POLSEK di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(92, 'PS._KAPOLSEK_SIMANINDO', 'PS. KAPOLSEK SIMANINDO', 4, 'PEMBANTU PIMPINAN', NULL, NULL, 0, 1, 1, 'Jabatan PS. KAPOLSEK SIMANINDO di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(93, 'KANIT_RESKRIM', 'KANIT RESKRIM', 4, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan KANIT RESKRIM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(94, 'PS._KANITPROPAM', 'PS. KANITPROPAM', 4, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KANITPROPAM di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(95, 'PS._KA_SPKT_3', 'PS. KA SPKT 3', 4, 'KEPALA SEKSI', NULL, NULL, 0, 0, 1, 'Jabatan PS. KA SPKT 3 di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(96, 'KASIHUMAS', 'KASIHUMAS', 4, 'STAF', NULL, NULL, 0, 0, 0, 'Jabatan KASIHUMAS di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11'),
(97, 'KAPOLSEK_PANGURURAN', 'KAPOLSEK PANGURURAN', 4, 'PEMBANTU PIMPINAN', NULL, NULL, 0, 1, 1, 'Jabatan KAPOLSEK PANGURURAN di POLRES Samosir', 1, '2026-03-28 18:19:11', '2026-03-28 18:19:11');

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
(2, 'POLRI_PENSIUN', 'POLRI Pensiun', 'Anggota POLRI yang sudah pensiun', 'POLRI', 2, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(3, 'POLRI_DIK', 'POLRI Dalam Pendidikan', 'Anggota POLRI yang sedang menjalani pendidikan', 'POLRI', 3, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(4, 'ASN', 'Aparatur Sipil Negara', 'Pegawai negeri sipil', 'ASN', 10, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(5, 'ASN_HONORARIUM', 'ASN Honorarium', 'ASN dengan status honorarium', 'ASN', 11, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(6, 'ASN_KONTRAK', 'ASN Kontrak', 'ASN dengan status kontrak', 'ASN', 12, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(7, 'P3K', 'Pegawai Pemerintah dengan Perjanjian Kerja', 'P3K sesuai PP No. 49 Tahun 2018', 'P3K', 20, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(8, 'P3K_TAHUNAN', 'P3K Tahunan', 'P3K dengan kontrak tahunan', 'P3K', 21, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(9, 'P3K_BULANAN', 'P3K Bulanan', 'P3K dengan kontrak bulanan', 'P3K', 22, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(10, 'HONORARIUM', 'Tenaga Honorarium', 'Tenaga ahli dengan status honorarium', 'HONORARIUM', 30, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(11, 'KONTRAK', 'Tenaga Kontrak', 'Tenaga dengan status kontrak', 'KONTRAK', 31, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11'),
(12, 'LAINNYA', 'Magang', 'Tenaga magang/internship', 'LAINNYA', 40, 1, '2026-03-28 18:56:11', '2026-03-28 18:56:11');

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
-- Struktur dari tabel `operations`
--

CREATE TABLE `operations` (
  `id` int(11) NOT NULL,
  `operation_name` varchar(255) NOT NULL,
  `operation_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `required_personnel` int(11) DEFAULT 0,
  `status` enum('planned','active','completed','cancelled') DEFAULT 'planned',
  `google_event_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pangkat`
--

CREATE TABLE `pangkat` (
  `id` int(11) NOT NULL,
  `nama_pangkat` varchar(100) NOT NULL,
  `singkatan` varchar(20) DEFAULT NULL,
  `level_pangkat` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pangkat`
--

INSERT INTO `pangkat` (`id`, `nama_pangkat`, `singkatan`, `level_pangkat`, `created_at`, `updated_at`) VALUES
(15, 'Jenderal Polisi', 'JENDRAL', 1, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(16, 'Komisaris Jenderal Polisi', 'KOMJEN', 2, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(17, 'Inspektur Jenderal Polisi', 'IRJEN', 3, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(18, 'Brigadir Jenderal Polisi', 'BRIGJEN', 4, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(19, 'Komisaris Besar Polisi', 'KOMBES', 5, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(20, 'Ajun Komisaris Besar Polisi', 'AKBP', 6, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(21, 'Komisaris Polisi', 'KOMPOL', 7, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(22, 'Ajun Komisaris Polisi', 'AKP', 8, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(23, 'Inspektur Polisi Satu', 'IPTU', 9, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(24, 'Inspektur Polisi Dua', 'IPDA', 10, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(25, 'Ajun Inspektur Polisi Satu', 'AIPTU', 11, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(26, 'Ajun Inspektur Polisi Dua', 'AIPDA', 12, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(27, 'Brigadir Polisi Kepala', 'BRIPKA', 13, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(28, 'Brigadir Polisi', 'BRIGPOL', 14, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(29, 'Brigadir Polisi Satu', 'BRIPTU', 15, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(30, 'Brigadir Polisi Dua', 'BRIPDA', 16, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(31, 'Ajun Brigadir Polisi', 'ABRIPOL', 17, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(32, 'Ajun Brigadir Polisi Satu', 'ABRIPTU', 18, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(33, 'Ajun Brigadir Polisi Dua', 'ABRIPDA', 19, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(34, 'Bhayangkara Kepala', 'BHARAKA', 20, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(35, 'Bhayangkara Satu', 'BHARATU', 21, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(36, 'Bhayangkara Dua', 'BHARADA', 22, '2026-03-28 15:44:37', '2026-03-28 15:51:58'),
(37, 'Pembina Utama', 'PEBINA', 23, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(38, 'Pembina Utama Madya', 'PEBINA MADYA', 24, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(39, 'Pembina Utama Muda', 'PEBINA MUDA', 25, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(40, 'Pembina Tingkat I', 'PEBINA TK I', 26, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(41, 'Pembina', 'PEBINA', 27, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(42, 'Penata Tingkat I', 'PENDA', 28, '2026-03-28 15:44:37', '2026-03-28 18:23:21'),
(43, 'Penata', 'PENATA', 29, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(44, 'Penata Muda Tingkat I', 'PENATA MUDA TK I', 30, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(45, 'Penata Muda', 'PENATA MUDA', 31, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(46, 'Pengatur Tingkat I', 'PENGATUR TK I', 32, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(47, 'Pengatur', 'PENGATUR', 33, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(48, 'Pengatur Muda Tingkat I', 'PENGATUR MUDA TK I', 34, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(49, 'Pengatur Muda', 'PENGATUR MUDA', 35, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(50, 'Juru Tingkat I', 'JURU TK I', 36, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(51, 'Juru', 'JURU', 37, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(52, 'Juru Muda Tingkat I', 'JURU MUDA TK I', 38, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(53, 'Juru Muda', 'JURU MUDA', 39, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(54, 'Honorer', 'HONORER', 40, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(55, 'Tenaga Harian Lepas', 'THL', 41, '2026-03-28 15:44:37', '2026-03-28 15:52:06'),
(56, 'Kontrak', 'KONTRAK', 42, '2026-03-28 15:44:37', '2026-03-28 15:52:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `personil`
--

CREATE TABLE `personil` (
  `id` int(11) NOT NULL,
  `nrp` varchar(20) NOT NULL,
  `nip` varchar(18) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `gelar_pendidikan` text DEFAULT NULL,
  `id_pangkat` int(11) DEFAULT NULL,
  `id_jabatan` int(11) DEFAULT NULL,
  `id_bagian` int(11) DEFAULT NULL,
  `id_unsur` int(11) DEFAULT NULL,
  `status_ket` varchar(20) DEFAULT 'aktif',
  `id_jenis_pegawai` int(11) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL COMMENT 'Tanggal lahir personil',
  `JK` enum('L','P') DEFAULT NULL COMMENT 'JK = Jenis Kelamin: L = Laki-laki, P = Perempuan',
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_pensiun` date DEFAULT NULL,
  `no_karpeg` varchar(20) DEFAULT NULL,
  `status_nikah` varchar(20) DEFAULT NULL,
  `jabatan_struktural` varchar(100) DEFAULT NULL,
  `jabatan_fungsional` varchar(100) DEFAULT NULL,
  `golongan` varchar(20) DEFAULT NULL,
  `eselon` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `personil`
--

INSERT INTO `personil` (`id`, `nrp`, `nip`, `nama`, `gelar_pendidikan`, `id_pangkat`, `id_jabatan`, `id_bagian`, `id_unsur`, `status_ket`, `id_jenis_pegawai`, `tempat_lahir`, `tanggal_lahir`, `JK`, `tanggal_masuk`, `tanggal_pensiun`, `no_karpeg`, `status_nikah`, `jabatan_struktural`, `jabatan_fungsional`, `golongan`, `eselon`, `is_active`, `is_deleted`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(256, '84031648', NULL, 'RINA SRY NIRWANA TARIGAN, S.I.K., M.H.', 'S.I.K., M.H.', 20, 1, 1, 1, 'aktif', 1, NULL, '1984-03-01', 'P', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(257, '83081648', NULL, 'BRISTON AGUS MUNTECARLO, S.T., S.I.K.', 'S.T., S.I.K.', 21, 2, 1, 1, 'aktif', 1, NULL, '1983-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(258, '68100259', NULL, 'EDUAR, S.H.', 'S.H.', 21, 3, 2, 2, 'aktif', 1, NULL, '1968-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(259, '82080038', NULL, 'PATRI SIHALOHO, S.H.', 'S.H.', 26, 4, 2, 2, 'aktif', 1, NULL, '1982-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:41'),
(260, '02120141', NULL, 'AGUNG NUGRAHA NADAP-DAP', NULL, 30, 5, 2, 2, 'aktif', 1, NULL, '2002-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:18'),
(261, '03010386', NULL, 'ALDI PRANATA GINTING', NULL, 30, 5, 2, 2, 'aktif', 1, NULL, '2003-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(262, '02040489', NULL, 'HENDRIKSON SILALAHI', NULL, 30, 5, 2, 2, 'aktif', 1, NULL, '2002-04-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(263, '02071119', NULL, 'TOHONAN SITOHANG', NULL, 30, 5, 2, 2, 'aktif', 1, NULL, '2002-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(264, '03101364', NULL, 'GILANG SUTOYO', NULL, 30, 5, 2, 2, 'aktif', 1, NULL, '2003-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(265, '76030248', NULL, 'HENDRI SIAGIAN, S.H.', 'S.H.', 24, 7, 20, 5, 'aktif', 1, NULL, '1976-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(266, '87070134', NULL, 'DENI MUSTIKA SUKMANA, S.E.', 'S.E.', 24, 8, 20, 5, 'aktif', 1, NULL, '1987-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:41'),
(267, '85081770', NULL, 'JAMIL MUNTHE, S.H., M.H.', 'S.H.', 24, 9, 20, 5, 'aktif', 1, NULL, '1985-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(268, '87030020', NULL, 'BULET MARS SWANTO LBN. BATU, S.H.', 'S.H.', 24, 10, 20, 5, 'aktif', 1, NULL, '1987-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:41'),
(269, '96010872', NULL, 'RAMADHAN PUTRA, S.H.', 'S.H.', 29, 11, 20, 5, 'aktif', 1, NULL, '1996-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(270, '98090415', NULL, 'ABEDNEGO TARIGAN', NULL, 29, 12, 20, 5, 'aktif', 1, NULL, '1998-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(271, '00010166', NULL, 'EDY SUSANTO PARDEDE', NULL, 29, 13, 20, 5, 'aktif', 1, NULL, '2000-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:41'),
(272, '98010470', NULL, 'BOBBY ANGGARA PUTRA SIREGAR', NULL, 30, 13, 20, 5, 'aktif', 1, NULL, '1998-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(273, '01070820', NULL, 'GABRIEL PAULIMA NADEAK', NULL, 30, 13, 20, 5, 'aktif', 1, NULL, '2001-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(274, '02091526', NULL, 'ANDRE OWEN PURBA', NULL, 30, 11, 20, 5, 'aktif', 1, NULL, '2002-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(275, '04070159', NULL, 'EDWARD FERDINAND SIDABUTAR', NULL, 30, 11, 20, 5, 'aktif', 1, NULL, '2004-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:37'),
(276, '03060873', NULL, 'BIMA SANTO HUTAGAOL', NULL, 30, 12, 20, 5, 'aktif', 1, NULL, '2003-06-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(277, '03121291', NULL, 'KRISTIAN M. H. NABABAN', NULL, 30, 12, 20, 5, 'aktif', 1, NULL, '2003-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(278, '72100484', NULL, 'SURUNG SAGALA', NULL, 24, 14, 3, 2, 'aktif', 1, NULL, '1972-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(279, '96090857', NULL, 'ZAKHARIA S. I. SIMANJUNTAK, S.H.', 'S.H.', 29, 15, 3, 2, 'aktif', 1, NULL, '1996-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(280, '03080202', NULL, 'GRENIEL WIARTO SIHITE', NULL, 30, 15, 3, 2, 'aktif', 1, NULL, '2003-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(281, '73010107', NULL, 'TARMIZI LUBIS, S.H.', 'S.H.', 22, 16, 4, 2, 'aktif', 1, NULL, '1973-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(282, '198111252014122004', NULL, 'REYMESTA AMBARITA, S.Kom.', 'S.Kom.', 42, 17, 4, 2, 'aktif', 4, NULL, '1981-11-25', 'P', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 22:01:30'),
(283, '97090248', NULL, 'LAMTIO SINAGA, S.H.', 'S.H.', 28, 18, 4, 2, 'aktif', 1, NULL, '1997-09-01', 'P', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:46:22'),
(284, '97120490', NULL, 'DODI KURNIADI', NULL, 29, 18, 4, 2, 'aktif', 1, NULL, '1997-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(285, '05070285', NULL, 'EFRANTA SAPUTRA SITEPU', NULL, 30, 18, 4, 2, 'aktif', 1, NULL, '2005-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(286, '86070985', NULL, 'RADOS. S. TOGATOROP,S.H.', NULL, 26, 19, 4, 2, 'aktif', 3, NULL, '1986-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:25:54'),
(287, '00080579', NULL, 'REYSON YOHANNES SIMBOLON', NULL, 30, 20, 4, 2, 'aktif', 1, NULL, '2000-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(288, '02090891', NULL, 'ANDRE TARUNA SIMBOLON', NULL, 30, 21, 9, 3, 'aktif', 1, NULL, '2002-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(289, '03081525', NULL, 'YOLANDA NAULIVIA ARITONANG', NULL, 30, 20, 4, 2, 'aktif', 1, NULL, '2003-08-01', 'P', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(290, '95080918', NULL, 'SYAUQI LUTFI LUBIS, S.H., M.H.', 'S.H.', 28, 19, 29, 6, 'aktif', 1, NULL, '1995-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(291, '97050575', NULL, 'DANIEL BRANDO SIDABUKKE', NULL, 28, 19, 29, 6, 'aktif', 1, NULL, '1997-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(292, '98010119', NULL, 'SUTRISNO BUTAR-BUTAR, S.H.', 'S.H.', 29, 19, 29, 6, 'aktif', 1, NULL, '1998-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(293, '81110363', NULL, 'LEONARDO SINAGA', NULL, 26, 19, 4, 2, 'aktif', 1, NULL, '1981-11-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:37'),
(294, '76040221', NULL, 'AWALUDDIN', NULL, 24, 22, 5, 2, 'aktif', 1, NULL, '1976-04-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(295, '97050588', NULL, 'EFRON SARWEDY SINAGA, S.H.', 'S.H.', 29, 23, 5, 2, 'aktif', 1, NULL, '1997-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:37'),
(296, '00010095', NULL, 'PRIADI MAROJAHAN HUTABARAT', NULL, 29, 23, 5, 2, 'aktif', 1, NULL, '2000-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:53'),
(297, '03070263', NULL, 'CHRIST JERICHO SAPUTRA TAMPUBOLON', NULL, 30, 23, 5, 2, 'aktif', 1, NULL, '2003-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(298, '86100287', NULL, 'EFRI PANDI', NULL, 26, 24, 21, 5, 'aktif', 1, NULL, '1986-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(299, '04010804', NULL, 'YOGI ADE PRATAMA SITOHANG', NULL, 30, 25, 21, 5, 'aktif', 1, NULL, '2004-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:53'),
(300, '93100676', NULL, 'PENGEJAPEN, S.H.', 'S.H.', 28, 26, 22, 5, 'aktif', 1, NULL, '1993-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(301, '97050876', NULL, 'MUHARRAM SYAHRI, S.H.', 'S.H.', 29, 27, 22, 5, 'aktif', 1, NULL, '1997-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(302, '97100685', NULL, 'M.FATHUR RAHMAN, S.H.', 'S.H.', 29, 27, 22, 5, 'aktif', 1, NULL, '1997-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(303, '03070010', NULL, 'HESKIEL WANDANA MELIALA', NULL, 30, 27, 22, 5, 'aktif', 1, NULL, '2003-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(304, '03040138', NULL, 'DANIEL RICARDO SARAGIH', NULL, 30, 27, 22, 5, 'aktif', 1, NULL, '2003-04-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(305, '197008291993032002', NULL, 'NENENG GUSNIARTI', NULL, 43, 28, 23, 5, 'aktif', 4, NULL, '1970-08-29', 'P', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:20:08'),
(306, '84040532', NULL, 'EDDY SURANTA SARAGIH', NULL, 27, 29, 23, 5, 'aktif', 1, NULL, '1984-04-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(307, '75060617', NULL, 'BILMAR SITUMORANG', NULL, 25, 30, 24, 5, 'aktif', 1, NULL, '1975-06-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(308, '94080815', NULL, 'YOHANES EDI SUPRIATNO, S.H., M.H.', 'S.H.', 28, 31, 24, 5, 'aktif', 1, NULL, '1994-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:10'),
(309, '94080892', NULL, 'AGUSTIAWAN SINAGA', NULL, 28, 31, 24, 5, 'aktif', 1, NULL, '1994-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:37'),
(310, '93060444', NULL, 'LISTER BROUN SITORUS', NULL, 28, 32, 25, 5, 'aktif', 1, NULL, '1993-06-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(311, '00070791', NULL, 'ANDREAS D. S. SITANGGANG', NULL, 30, 32, 25, 5, 'aktif', 1, NULL, '2000-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(312, '01101139', NULL, 'JACKSON SIDABUTAR', NULL, 30, 32, 25, 5, 'aktif', 1, NULL, '2001-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(313, '73050261', NULL, 'PARIMPUNAN SIREGAR', NULL, 24, 33, 26, 5, 'aktif', 1, NULL, '1973-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(314, '95030599', NULL, 'DANIEL E. LUMBANTORUAN, S.H.', 'S.H.', 28, 34, 26, 5, 'aktif', 1, NULL, '1995-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(315, '76120670', NULL, 'DENNI BOYKE H. SIREGAR, S.H.', 'S.H.', 24, 35, 27, 5, 'aktif', 1, NULL, '1976-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(316, '81010202', NULL, 'BENNI ARDINAL, S.H., M.H.', 'S.H.', 26, 36, 27, 5, 'aktif', 1, NULL, '1981-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:37'),
(317, '85081088', NULL, 'AGUSTINUS SINAGA', NULL, 26, 37, 27, 5, 'aktif', 1, NULL, '1985-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:37'),
(318, '86081359', NULL, 'RAMBO CISLER NADEAK', NULL, 27, 38, 27, 5, 'aktif', 1, NULL, '1986-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(319, '95030796', NULL, 'PERY RAPEN YONES PARDOSI, S.H.', 'S.H.', 28, 38, 27, 5, 'aktif', 1, NULL, '1995-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(320, '97070014', NULL, 'DWI HETRIANDY, S.H.', 'S.H.', 28, 38, 27, 5, 'aktif', 1, NULL, '1997-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(321, '97120554', NULL, 'TRY WIBOWO', NULL, 29, 38, 27, 5, 'aktif', 1, NULL, '1997-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(322, '00080343', NULL, 'SIMON TIGRIS SIAGIAN', NULL, 29, 38, 27, 5, 'aktif', 1, NULL, '2000-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(323, '01080575', NULL, 'FIRIAN JOSUA SITORUS', NULL, 30, 38, 27, 5, 'aktif', 1, NULL, '2001-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(324, '93030551', NULL, 'GUNAWAN SITUMORANG', NULL, 28, 39, 28, 5, 'aktif', 1, NULL, '1993-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(325, '98091488', NULL, 'DANIEL BAHTERA SINAGA', NULL, 29, 39, 28, 5, 'aktif', 1, NULL, '1998-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:37'),
(326, '75120560', NULL, 'HORAS LARIUS SITUMORANG', NULL, 24, 40, 14, 3, 'aktif', 1, NULL, '1975-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(327, '95090650', NULL, 'JEFTA OCTAVIANUS NICO SIANTURI', NULL, 28, 41, 14, 3, 'aktif', 1, NULL, '1995-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(328, '94091146', NULL, 'SAHAT MARULI TUA SINAGA, S.H.', 'S.H.', 28, 41, 14, 3, 'aktif', 1, NULL, '1994-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:53'),
(329, '04020118', NULL, 'RONAL PARTOGI SITUMORANG', NULL, 30, 41, 14, 3, 'aktif', 1, NULL, '2004-02-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(330, '82070670', NULL, 'DONAL P. SITANGGANG, S.H., M.H.', 'S.H.', 23, 42, 6, 3, 'aktif', 1, NULL, '1982-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(331, '85050489', NULL, 'MUHAMMAD YUNUS LUBIS, S.H.', 'S.H.', 24, 40, 6, 3, 'aktif', 1, NULL, '1985-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(332, '80070348', NULL, 'MARBETA S. SIANIPAR, S.H.', 'S.H.', 26, 43, 6, 3, 'aktif', 1, NULL, '1980-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(333, '87080112', NULL, 'SITARDA AKABRI SIBUEA', NULL, 26, 44, 6, 3, 'aktif', 1, NULL, '1987-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(334, '87051430', NULL, 'CINTER ROKHY SINAGA', NULL, 27, 45, 6, 3, 'aktif', 1, NULL, '1987-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:37'),
(335, '90080088', NULL, 'VANDU P. MARPAUNG', NULL, 27, 46, 6, 3, 'aktif', 1, NULL, '1990-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(336, '93080556', NULL, 'ALFONSIUS GULTOM, S.H.', 'S.H.', 28, 47, 6, 3, 'aktif', 1, NULL, '1993-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(337, '97040848', NULL, 'TRIFIKO P. NAINGGOLAN, S.H.', 'S.H.', 29, 48, 6, 3, 'aktif', 1, NULL, '1997-04-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(338, '98110618', NULL, 'ANDRI AFRIJAL SIMARMATA', NULL, 29, 48, 6, 3, 'aktif', 1, NULL, '1998-11-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(339, '02030032', NULL, 'DIEN VAROSCY I. SITUMORANG', NULL, 30, 48, 6, 3, 'aktif', 1, NULL, '2002-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(340, '02120339', NULL, 'ARDY TRIANO MALAU', NULL, 30, 48, 6, 3, 'aktif', 1, NULL, '2002-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(341, '02040459', NULL, 'JUNEDI SAGALA', NULL, 30, 48, 6, 3, 'aktif', 1, NULL, '2002-04-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(342, '02101010', NULL, 'GABRIEL SEBASTIAN SIREGAR', NULL, 30, 48, 6, 3, 'aktif', 1, NULL, '2002-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(343, '04020209', NULL, 'RIO F. T ERENST PANJAITAN', NULL, 30, 48, 6, 3, 'aktif', 1, NULL, '2004-02-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(344, '04080118', NULL, 'AGHEO HARMANA JOUSTRA SINURAYA', NULL, 30, 47, 6, 3, 'aktif', 1, NULL, '2004-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(345, '04010932', NULL, 'SAMUEL RINALDI PAKPAHAN', NULL, 30, 47, 6, 3, 'aktif', 1, NULL, '2004-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:53'),
(346, '04040520', NULL, 'RAYMONTIUS HAROMUNTE', NULL, 30, 47, 6, 3, 'aktif', 1, NULL, '2004-04-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(347, '79120994', NULL, 'EDWARD SIDAURUK, S.E., M.M.', 'S.E.', 22, 49, 7, 3, 'aktif', 1, NULL, '1979-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(348, '76020196', NULL, 'DARMONO SAMOSIR, S.H.', 'S.H.', 24, 50, 7, 3, 'aktif', 1, NULL, '1976-02-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(349, '83010825', NULL, 'ROYANTO PURBA, S.H.', 'S.H.', 24, 51, 7, 3, 'aktif', 1, NULL, '1983-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(350, '83120602', NULL, 'SUHADIYANTO, S.H.', 'S.H.', 24, 52, 7, 3, 'aktif', 1, NULL, '1983-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(351, '88060535', NULL, 'KUICAN SIMANJUNTAK', NULL, 27, 53, 7, 3, 'aktif', 1, NULL, '1988-06-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(352, '79030434', NULL, 'MARTIN HABENSONY ARITONANG', NULL, 25, 54, 7, 3, 'aktif', 1, NULL, '1979-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(353, '83060084', NULL, 'HENRY SIPAKKAR', NULL, 25, 55, 7, 3, 'aktif', 1, NULL, '1983-06-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(354, '87011165', NULL, 'CHANDRA HUTAPEA', NULL, 27, 43, 7, 3, 'aktif', 1, NULL, '1987-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(355, '89030401', NULL, 'CHANDRA BARIMBING', NULL, 27, 56, 7, 3, 'aktif', 1, NULL, '1989-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(356, '87041596', NULL, 'DEDY SAOLOAN SIGALINGGING', NULL, 27, 56, 7, 3, 'aktif', 1, NULL, '1987-04-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(357, '82050798', NULL, 'ISWAN LUKITO', NULL, 27, 56, 7, 3, 'aktif', 1, NULL, '1982-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(358, '95030238', NULL, 'RONI HANSVERI BANJARNAHOR', NULL, 28, 56, 7, 3, 'aktif', 1, NULL, '1995-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(359, '94020506', NULL, 'RODEN SUANDI TURNIP', NULL, 28, 56, 7, 3, 'aktif', 1, NULL, '1994-02-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(360, '94121145', NULL, 'SAPUTRA, S.H.', 'S.H.', 28, 56, 7, 3, 'aktif', 1, NULL, '1994-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(361, '95100554', NULL, 'DIAN LESTARI GULTOM, S.H.', 'S.H.', 28, 56, 7, 3, 'aktif', 1, NULL, '1995-10-01', 'P', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:46:22'),
(362, '95110886', NULL, 'ARGIO SIMBOLON', NULL, 28, 56, 7, 3, 'aktif', 1, NULL, '1995-11-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(363, '97070616', NULL, 'EKO DAHANA PARDEDE, S.H.', 'S.H.', 28, 56, 7, 3, 'aktif', 1, NULL, '1997-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(364, '97040728', NULL, 'GIDEON AFRIADI LUMBAN RAJA', NULL, 29, 56, 7, 3, 'aktif', 1, NULL, '1997-04-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(365, '98090397', NULL, 'FACHRUL REZA SILALAHI', NULL, 29, 56, 7, 3, 'aktif', 1, NULL, '1998-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(366, '00030346', NULL, 'RIDHOTUA F. SITANGGANG', NULL, 29, 56, 7, 3, 'aktif', 1, NULL, '2000-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(367, '00110362', NULL, 'NICHO FERNANDO SARAGIH', NULL, 29, 56, 7, 3, 'aktif', 1, NULL, '2000-11-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(368, '00090499', NULL, 'ADI P.S. MARBUN', NULL, 29, 56, 7, 3, 'aktif', 1, NULL, '2000-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(369, '01120358', NULL, 'PRIYATAMA ABDILLAH HARAHAP', NULL, 30, 56, 7, 3, 'aktif', 1, NULL, '2001-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(370, '01070839', NULL, 'RIZKI AFRIZAL SIMANJUNTAK', NULL, 30, 56, 7, 3, 'aktif', 1, NULL, '2001-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(371, '01060553', NULL, 'MIDUK YUDIANTO SINAGA', NULL, 30, 56, 7, 3, 'aktif', 1, NULL, '2001-06-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:53'),
(372, '02110342', NULL, 'FRAN\'S ALEXANDER SIANIPAR', NULL, 30, 56, 7, 3, 'aktif', 1, NULL, '2002-11-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(373, '01110817', NULL, 'RAFFLES SIJABAT', NULL, 30, 56, 7, 3, 'aktif', 1, NULL, '2001-11-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(374, '01091201', NULL, 'HERIANTA TARIGAN', NULL, 30, 56, 7, 3, 'aktif', 1, NULL, '2001-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(375, '03030809', NULL, 'RICKY AGATHA GINTING', NULL, 30, 56, 7, 3, 'aktif', 1, NULL, '2003-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(376, '03020368', NULL, 'CHRISTIAN PROSPEROUS SIMANUNGKALIT', NULL, 30, 56, 7, 3, 'aktif', 1, NULL, '2003-02-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(377, '04020196', NULL, 'PINIEL RAJAGUKGUK', NULL, 30, 56, 7, 3, 'aktif', 1, NULL, '2004-02-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(378, '03090568', NULL, 'REZA SIREGAR', NULL, 30, 56, 7, 3, 'aktif', 1, NULL, '2003-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(379, '04031206', NULL, 'RAYMOND VAN HEZEKIEL SIAHAAN', NULL, 30, 56, 7, 3, 'aktif', 1, NULL, '2004-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(380, '05080602', NULL, 'M. ALAMSYAH PRAYOGA TAMBUNAN', NULL, 30, 56, 7, 3, 'aktif', 1, NULL, '2005-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(381, '04090567', NULL, 'IRVAN SYAPUTRA MALAU', NULL, 30, 56, 7, 3, 'aktif', 1, NULL, '2004-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(382, '79060034', NULL, 'FERRY ARIANDY, S.H., M.H', 'S.H.', 22, 57, 8, 3, 'aktif', 1, NULL, '1979-06-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(383, '88100591', NULL, 'ALVIUS KRISTIAN GINTING, S.H.', 'S.H.', 24, 40, 8, 3, 'aktif', 1, NULL, '1988-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(384, '89010155', NULL, 'BENNY SITUMORANG, S.H.', 'S.H.', 27, 58, 8, 3, 'aktif', 1, NULL, '1989-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(385, '93050797', NULL, 'EKO PUTRA DAMANIK, S.H.', 'S.H.', 28, 59, 8, 3, 'aktif', 1, NULL, '1993-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(386, '91050361', NULL, 'MAY FRANSISCO SIAGIAN, S.H.', 'S.H.', 28, 59, 8, 3, 'aktif', 1, NULL, '1991-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(387, '94090839', NULL, 'ROBERTO MANALU', NULL, 29, 59, 8, 3, 'aktif', 1, NULL, '1994-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(388, '98110378', NULL, 'M. RONALD FAHROZI HARAHAP, S.H.', 'S.H.', 29, 59, 8, 3, 'aktif', 1, NULL, '1998-11-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(389, '97020694', NULL, 'HERIANTO EFENDI, S.H.', 'S.H.', 29, 59, 8, 3, 'aktif', 1, NULL, '1997-02-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(390, '02120224', NULL, 'TEDDI PARNASIPAN TOGATOROP', NULL, 30, 59, 8, 3, 'aktif', 1, NULL, '2002-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(391, '02090838', NULL, 'ONDIHON SIMBOLON', NULL, 30, 59, 8, 3, 'aktif', 1, NULL, '2002-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(392, '05080131', NULL, 'IVAN SIGOP SIHOMBING', NULL, 30, 59, 8, 3, 'aktif', 1, NULL, '2005-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(393, '80080676', NULL, 'NANDI BUTAR-BUTAR, S.H.', 'S.H.', 22, 60, 10, 3, 'aktif', 1, NULL, '1980-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(394, '80050867', NULL, 'BARTO ANTONIUS SIMALANGO', NULL, 25, 61, 10, 3, 'aktif', 1, NULL, '1980-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(395, '73040390', NULL, 'HASUDUNGAN SILITONGA', NULL, 26, 62, 10, 3, 'aktif', 1, NULL, '1973-04-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(396, '85090954', NULL, 'JHONNY LEONARDO SILALAHI', NULL, 27, 63, 10, 3, 'aktif', 1, NULL, '1985-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(397, '83081051', NULL, 'ASRIL', NULL, 27, 64, 10, 3, 'aktif', 1, NULL, '1983-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:37'),
(398, '94110350', NULL, 'INDIRWAN FRIDERICK, S.H.', 'S.H.', 28, 64, 10, 3, 'aktif', 1, NULL, '1994-11-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(399, '93100793', NULL, 'EGIDIUM BRAUN SILITONGA', NULL, 28, 64, 10, 3, 'aktif', 1, NULL, '1993-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(400, '97100701', NULL, 'DINAMIKA JAYA NEGARA SITANGGANG', NULL, 30, 64, 10, 3, 'aktif', 1, NULL, '1997-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:37'),
(401, '05051087', NULL, 'WIRA HARZITA', NULL, 30, 64, 10, 3, 'aktif', 1, NULL, '2005-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(402, '06100189', NULL, 'RAHMAT ANDRIAN TAMBUNAN', NULL, 30, 64, 10, 3, 'aktif', 1, NULL, '2006-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(403, '07080045', NULL, 'JONATAN DWI SAPUTRA PARAPAT', NULL, 30, 64, 10, 3, 'aktif', 1, NULL, '2007-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(404, '04051595', NULL, 'PERDANA NIKOLA SEMBIRING', NULL, 30, 64, 10, 3, 'aktif', 1, NULL, '2004-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(405, '04081205', NULL, 'PETRUS SURIA HUGALUNG', NULL, 30, 64, 10, 3, 'aktif', 1, NULL, '2004-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(406, '06010414', NULL, 'RAFAEL ARSANLILO SINULINGGA', NULL, 30, 64, 10, 3, 'aktif', 1, NULL, '2006-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(407, '06090021', NULL, 'RAJASPER SIRINGORINGO', NULL, 30, 64, 10, 3, 'aktif', 1, NULL, '2006-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(408, '72100604', NULL, 'TANGIO HAOJAHAN SITANGGANG, S.H.', 'S.H.', 23, 65, 11, 3, 'aktif', 1, NULL, '1972-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(409, '80100836', NULL, 'MARUBA NAINGGOLAN', NULL, 25, 66, 11, 3, 'aktif', 1, NULL, '1980-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(410, '85030645', NULL, 'ROY HARIS ST. SIMAREMARE', NULL, 26, 67, 11, 3, 'aktif', 1, NULL, '1985-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(411, '80050898', NULL, 'M. DENY WAHYU', NULL, 26, 68, 11, 3, 'aktif', 1, NULL, '1980-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(412, '83050202', NULL, 'HENRI F. SIANIPAR', NULL, 25, 69, 11, 3, 'aktif', 1, NULL, '1983-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(413, '85121325', NULL, 'BUYUNG ANDRYANTO', NULL, 27, 43, 11, 3, 'aktif', 1, NULL, '1985-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(414, '91110130', NULL, 'RIANTO SITANGGANG', NULL, 28, 69, 11, 3, 'aktif', 1, NULL, '1991-11-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(415, '94090948', NULL, 'ROY NANDA SEMBIRING KEMBAREN', NULL, 28, 69, 11, 3, 'aktif', 1, NULL, '1994-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(416, '96031057', NULL, 'CANDRA SILALAHI, S.H.', 'S.H.', 28, 69, 11, 3, 'aktif', 1, NULL, '1996-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(417, '02100599', NULL, 'YUNUS SAMDIO SIDABUTAR', NULL, 30, 69, 11, 3, 'aktif', 1, NULL, '2002-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(418, '03010565', NULL, 'RAINHEART SITANGGANG', NULL, 30, 69, 11, 3, 'aktif', 1, NULL, '2003-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(419, '02011312', NULL, 'BONIFASIUS NAINGGOLAN', NULL, 30, 69, 11, 3, 'aktif', 1, NULL, '2002-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(420, '00080816', NULL, 'RAY YONDO SIAHAAN', NULL, 30, 69, 11, 3, 'aktif', 1, NULL, '2000-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(421, '03040947', NULL, 'REDY EZRA JONATHAN', NULL, 30, 69, 11, 3, 'aktif', 1, NULL, '2003-04-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(422, '04100485', NULL, 'CHARLY H. ARITONANG', NULL, 30, 69, 11, 3, 'aktif', 1, NULL, '2004-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(423, '79120800', NULL, 'NATANAIL SURBAKTI, S.H', NULL, 22, 70, 9, 3, 'aktif', 1, NULL, '1979-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(424, '75080942', NULL, 'JUSUP KETAREN', NULL, 24, 71, 9, 3, 'aktif', 1, NULL, '1975-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(425, '80070492', NULL, 'ARON PERANGIN-ANGIN', NULL, 25, 72, 9, 3, 'aktif', 1, NULL, '1980-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(426, '79060704', NULL, 'HERON GINTING', NULL, 27, 73, 9, 3, 'aktif', 1, NULL, '1979-06-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(427, '86030733', NULL, 'JEFRI KHADAFI SIREGAR, S.H.', 'S.H.', 27, 74, 9, 3, 'aktif', 1, NULL, '1986-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(428, '89070031', NULL, 'HERIANTO TURNIP', NULL, 27, 75, 9, 3, 'aktif', 1, NULL, '1989-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(429, '87030647', NULL, 'DION MAR\'YANSEN SILITONGA', NULL, 28, 75, 9, 3, 'aktif', 1, NULL, '1987-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(430, '93020749', NULL, 'ROY GRIMSLAY, S.H.', 'S.H.', 28, 75, 9, 3, 'aktif', 1, NULL, '1993-02-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(431, '93090673', NULL, 'BAGUS DWI PRAKOSO, S.H.', 'S.H.', 28, 75, 9, 3, 'aktif', 1, NULL, '1993-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(432, '97040353', NULL, 'ICASANDRI MONANZA BR GINTING', NULL, 28, 75, 9, 3, 'aktif', 1, NULL, '1997-04-01', 'P', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:47:17'),
(433, '95021078', NULL, 'DIKI FEBRIAN SITORUS', NULL, 29, 75, 9, 3, 'aktif', 1, NULL, '1995-02-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(434, '96031061', NULL, 'MARCHLANDA SITOHANG', NULL, 29, 75, 9, 3, 'aktif', 1, NULL, '1996-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(435, '01080438', NULL, 'JULIVER SIDABUTAR', NULL, 29, 75, 9, 3, 'aktif', 1, NULL, '2001-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(436, '01120281', NULL, 'FATHURROZI TINDAON', NULL, 30, 75, 9, 3, 'aktif', 1, NULL, '2001-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(437, '02111012', NULL, 'BENY BOY CHRISTIAN SIAHAAN', NULL, 30, 21, 9, 3, 'aktif', 1, NULL, '2002-11-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(438, '02111051', NULL, 'RADOT NOVALDO PANDAPOTAN PURBA', NULL, 30, 21, 9, 3, 'aktif', 1, NULL, '2002-11-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(439, '05030251', NULL, 'MUHAMMAD ZIDHAN RIFALDI', NULL, 30, 21, 9, 3, 'aktif', 1, NULL, '2005-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(440, '04050615', NULL, 'DANI INDRA PERMANA SINAGA', NULL, 30, 21, 9, 3, 'aktif', 1, NULL, '2004-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:37'),
(441, '05010048', NULL, 'HEZKIEL CAPRI SITINDAON', NULL, 30, 21, 9, 3, 'aktif', 1, NULL, '2005-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(442, '04030824', NULL, 'BONARIS TSUYOKO DITASANI SINAGA', NULL, 30, 21, 9, 3, 'aktif', 1, NULL, '2004-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:37'),
(443, '05010014', NULL, 'ARY ANJAS SARAGIH', NULL, 30, 21, 9, 3, 'aktif', 1, NULL, '2005-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(444, '04030805', NULL, 'GABRIEL VERY JUNIOR SITOHANG', NULL, 30, 21, 9, 3, 'aktif', 1, NULL, '2004-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(445, '02121477', NULL, 'FIRMAN BAHTERA', NULL, 30, 21, 9, 3, 'aktif', 1, NULL, '2002-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(446, '68120522', NULL, 'SULAIMAN PANGARIBUAN, S.H', NULL, 22, 76, 12, 3, 'aktif', 1, NULL, '1968-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(447, '83080822', NULL, 'EFENDI M.  SIREGAR', NULL, 26, 77, 12, 3, 'aktif', 1, NULL, '1983-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(448, '73120275', NULL, 'ROMEL LINDUNG SIAHAAN', NULL, 26, 43, 12, 3, 'aktif', 1, NULL, '1973-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(449, '90060273', NULL, 'FRANS HOTMAN MANURUNG, S.H.', 'S.H.', 27, 78, 12, 3, 'aktif', 1, NULL, '1990-06-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(450, '77070919', NULL, 'ANTONIUS SIPAYUNG', NULL, 28, 78, 12, 3, 'aktif', 1, NULL, '1977-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(451, '82051018', NULL, 'SAUT H. SIAHAAN', NULL, 26, 79, 13, 3, 'aktif', 1, NULL, '1982-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(452, '98050496', NULL, 'FERNANDO SIMBOLON', NULL, 29, 80, 13, 3, 'aktif', 1, NULL, '1998-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(453, '98030531', NULL, 'KURNIA PERMANA', NULL, 29, 80, 13, 3, 'aktif', 1, NULL, '1998-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(454, '05090232', NULL, 'STEVEN IMANUEL SITUMEANG', NULL, 30, 80, 13, 3, 'aktif', 1, NULL, '2005-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(455, '69090552', NULL, 'RAHMAT KURNIAWAN', NULL, 23, 81, 15, 4, 'aktif', 1, NULL, '1969-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(456, '79090296', NULL, 'MARUKKIL J.M. PASARIBU', NULL, 25, 82, 15, 4, 'aktif', 1, NULL, '1979-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:53'),
(457, '82070930', NULL, 'LANTRO LANDELINUS SAGALA', NULL, 26, 83, 15, 4, 'aktif', 1, NULL, '1982-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(458, '87120701', NULL, 'ANDY DEDY SIHOMBING, S.H.', 'S.H.', 27, 84, 15, 4, 'aktif', 1, NULL, '1987-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(459, '86021428', NULL, 'RANGGA HATTA', NULL, 27, 85, 15, 4, 'aktif', 1, NULL, '1986-02-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(460, '80120573', NULL, 'ARDIANSYAH BUTAR-BUTAR', NULL, 27, 86, 15, 4, 'aktif', 1, NULL, '1980-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(461, '96120123', NULL, 'ADRYANTO SINAGA, S.H.', 'S.H.', 28, 86, 15, 4, 'aktif', 1, NULL, '1996-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:53'),
(462, '94040538', NULL, 'BROLIN ADFRIALDI HALOHO', NULL, 28, 86, 15, 4, 'aktif', 1, NULL, '1994-04-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(463, '95110806', NULL, 'SUGIANTO ERIK SIBORO', NULL, 28, 86, 15, 4, 'aktif', 1, NULL, '1995-11-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(464, '01020739', NULL, 'RISKO SIMBOLON', NULL, 30, 86, 15, 4, 'aktif', 1, NULL, '2001-02-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(465, '70050412', NULL, 'MAXON NAINGGOLAN', NULL, 22, 87, 16, 4, 'aktif', 1, NULL, '1970-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(466, '78040213', NULL, 'H. SWANDI SINAGA', NULL, 25, 88, 16, 4, 'aktif', 1, NULL, '1978-04-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:37'),
(467, '77030463', NULL, 'HARATUA GULTOM', NULL, 25, 24, 16, 4, 'aktif', 1, NULL, '1977-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:37'),
(468, '76120606', NULL, 'ASA MELKI HUTABARAT', NULL, 26, 89, 16, 4, 'aktif', 1, NULL, '1976-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:53'),
(469, '78100741', NULL, 'JARIAHMAN SARAGIH', NULL, 26, 83, 16, 4, 'aktif', 1, NULL, '1978-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(470, '87041134', NULL, 'MUHAMMAD SYAFEI RAMADHAN', NULL, 26, 84, 16, 4, 'aktif', 1, NULL, '1987-04-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(471, '86121371', NULL, 'RIJALUL FIKRI SINAGA', NULL, 27, 82, 16, 4, 'aktif', 1, NULL, '1986-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:53');
INSERT INTO `personil` (`id`, `nrp`, `nip`, `nama`, `gelar_pendidikan`, `id_pangkat`, `id_jabatan`, `id_bagian`, `id_unsur`, `status_ket`, `id_jenis_pegawai`, `tempat_lahir`, `tanggal_lahir`, `JK`, `tanggal_masuk`, `tanggal_pensiun`, `no_karpeg`, `status_nikah`, `jabatan_struktural`, `jabatan_fungsional`, `golongan`, `eselon`, `is_active`, `is_deleted`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(472, '85071450', NULL, 'TEGUH SYAHPUTRA', NULL, 27, 90, 16, 4, 'aktif', 1, NULL, '1985-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(473, '85041500', NULL, 'RUDYANTO LUMBANRAJA', NULL, 27, 91, 16, 4, 'aktif', 1, NULL, '1985-04-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(474, '96031075', NULL, 'ZULPAN SYAHPUTRA DAMANIK', NULL, 29, 91, 16, 4, 'aktif', 1, NULL, '1996-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(475, '83061022', NULL, 'RAMADAN SIREGAR, S.H.', 'S.H.', 23, 92, 17, 4, 'aktif', 1, NULL, '1983-06-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(476, '86071792', NULL, 'WIDODO KABAN, S.H.', 'S.H.', 24, 93, 17, 4, 'aktif', 1, NULL, '1986-07-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(477, '75120864', NULL, 'GUNTAR TAMBUNAN', NULL, 25, 88, 17, 4, 'aktif', 1, NULL, '1975-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(478, '82040124', NULL, 'JEFRI RICARDO SAMOSIR', NULL, 25, 94, 17, 4, 'aktif', 1, NULL, '1982-04-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(479, '84020306', NULL, 'JUITO SUPANOTO PERANGIN-ANGIN', NULL, 26, 83, 17, 4, 'aktif', 1, NULL, '1984-02-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(480, '83080042', NULL, 'YOPPHY RHODEAR MUNTHE', NULL, 26, 95, 17, 4, 'aktif', 1, NULL, '1983-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(481, '86010311', NULL, 'TUMBUR SITOHANG', NULL, 26, 82, 17, 4, 'aktif', 1, NULL, '1986-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(482, '84110202', NULL, 'DONI SURIANTO PURBA, S.H.', 'S.H.', 27, 24, 17, 4, 'aktif', 1, NULL, '1984-11-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(483, '89020409', NULL, 'PATAR F. ANRI SIAHAAN', NULL, 27, 89, 17, 4, 'aktif', 1, NULL, '1989-02-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(484, '94090490', NULL, 'KURNIAWAN, S.H.', 'S.H.', 28, 86, 17, 4, 'aktif', 1, NULL, '1994-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(485, '95060432', NULL, 'ASHARI BUTAR-BUTAR, S.H.', 'S.H.', 28, 86, 17, 4, 'aktif', 1, NULL, '1995-06-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(486, '96061331', NULL, 'DIDI HOT BAGAS SITORUS', NULL, 30, 86, 17, 4, 'aktif', 1, NULL, '1996-06-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(487, '01060884', NULL, 'HORAS J.M. ARITONANG', NULL, 30, 86, 17, 4, 'aktif', 1, NULL, '2001-06-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(488, '04060050', NULL, 'ANDRE YEHEZKIEL HUTABARAT', NULL, 30, 86, 17, 4, 'aktif', 1, NULL, '2004-06-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:53'),
(489, '89080105', NULL, 'CLAUDIUS HARIS PARDEDE', NULL, 28, 86, 17, 4, 'aktif', 1, NULL, '1989-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(490, '02051553', NULL, 'ZULKIFLI NASUTION', NULL, 30, 86, 17, 4, 'aktif', 1, NULL, '2002-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(491, '70010290', NULL, 'RADIAMAN SIMARMATA', NULL, 22, 96, 18, 4, 'aktif', 1, NULL, '1970-01-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(492, '82050839', NULL, 'HERMAWADI', NULL, 26, 84, 18, 4, 'aktif', 1, NULL, '1982-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(493, '84091124', NULL, 'BISSAR LUMBANTUNGKUP', NULL, 26, 83, 18, 4, 'aktif', 1, NULL, '1984-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(494, '70090340', NULL, 'BONAR JUBEL SIBARANI', NULL, 27, 89, 18, 4, 'aktif', 1, NULL, '1970-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(495, '77020642', NULL, 'RAMLES SITANGGANG', NULL, 27, 82, 18, 4, 'aktif', 1, NULL, '1977-02-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(496, '83031377', NULL, 'LUHUT SIRINGO-RINGO', NULL, 28, 86, 18, 4, 'aktif', 1, NULL, '1983-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(497, '03100001', NULL, 'ANRIAN SIGALINGGING', NULL, 30, 86, 18, 4, 'aktif', 1, NULL, '2003-10-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(498, '99110755', NULL, 'BONATUA LUMBANTUNGKUP', NULL, 30, 86, 18, 4, 'aktif', 1, NULL, '1999-11-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(499, '03050116', NULL, 'ANDRE SUGIARTO MARPAUNG', NULL, 30, 86, 18, 4, 'aktif', 1, NULL, '2003-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(500, '04030125', NULL, 'ERWIN KEVIN GULTOM', NULL, 30, 86, 18, 4, 'aktif', 1, NULL, '2004-03-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(501, '70020298', NULL, 'BANGUN TUA DALIMUNTHE', NULL, 22, 97, 19, 4, 'aktif', 1, NULL, '1970-02-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(502, '81050713', NULL, 'LANCASTER ARIANTO CANDY PASARIBU, S.H.', 'S.H.', 25, 84, 19, 4, 'aktif', 1, NULL, '1981-05-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:35:37'),
(503, '80090905', NULL, 'RUDY SETYAWAN', NULL, 25, 82, 19, 4, 'aktif', 1, NULL, '1980-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(504, '80080892', NULL, 'MANGATUR TUA TINDAON', NULL, 26, 83, 19, 4, 'aktif', 1, NULL, '1980-08-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(505, '87110154', NULL, 'RENO HOTMARULI TUA MANIK, S.H.', 'S.H.', 27, 89, 19, 4, 'aktif', 1, NULL, '1987-11-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(506, '79020443', NULL, 'HERBINTUPA SITANGGANG', NULL, 28, 86, 19, 4, 'aktif', 1, NULL, '1979-02-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(507, '85121751', NULL, 'IBRAHIM TARIGAN', NULL, 28, 86, 19, 4, 'aktif', 1, NULL, '1985-12-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(508, '98090406', NULL, 'AGUNG NUGRAHA HARIANJA, S.H.', 'S.H.', 29, 86, 19, 4, 'aktif', 1, NULL, '1998-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(509, '98091274', NULL, 'DANI PUTRA RUMAHORBO', NULL, 29, 86, 19, 4, 'aktif', 1, NULL, '1998-09-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:12:11'),
(510, '01060198', NULL, 'KRISMAN JULU GULTOM', NULL, 30, 86, 19, 4, 'aktif', 1, NULL, '2001-06-01', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 19:09:54'),
(511, '198112262024211002', NULL, 'FERNANDO SILALAHI, A.Md.', NULL, NULL, 6, 2, 2, 'aktif', 7, NULL, '1981-12-26', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:26:34', '2026-03-28 19:21:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `personil_backup`
--

CREATE TABLE `personil_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `nrp` varchar(20) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `gelar_pendidikan` text DEFAULT NULL,
  `id_pangkat` int(11) DEFAULT NULL,
  `id_jabatan` int(11) DEFAULT NULL,
  `id_bagian` int(11) DEFAULT NULL,
  `id_unsur` int(11) DEFAULT NULL,
  `status_ket` varchar(20) DEFAULT 'aktif',
  `id_jenis_pegawai` int(11) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL COMMENT 'Tanggal lahir personil',
  `JK` enum('L','P') DEFAULT NULL COMMENT 'JK = Jenis Kelamin: L = Laki-laki, P = Perempuan',
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_pensiun` date DEFAULT NULL,
  `no_karpeg` varchar(20) DEFAULT NULL,
  `status_nikah` varchar(20) DEFAULT NULL,
  `jabatan_struktural` varchar(100) DEFAULT NULL,
  `jabatan_fungsional` varchar(100) DEFAULT NULL,
  `golongan` varchar(20) DEFAULT NULL,
  `eselon` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `personil_backup`
--

INSERT INTO `personil_backup` (`id`, `nrp`, `nama`, `gelar_pendidikan`, `id_pangkat`, `id_jabatan`, `id_bagian`, `id_unsur`, `status_ket`, `id_jenis_pegawai`, `tempat_lahir`, `tanggal_lahir`, `JK`, `tanggal_masuk`, `tanggal_pensiun`, `no_karpeg`, `status_nikah`, `jabatan_struktural`, `jabatan_fungsional`, `golongan`, `eselon`, `is_active`, `is_deleted`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(256, '84031648', 'RINA SRY NIRWANA TARIGAN, S.I.K., M.H.', NULL, 20, 1, 1, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(257, '83081648', 'BRISTON AGUS MUNTECARLO, S.T., S.I.K.', NULL, 21, 2, 1, 1, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(258, '68100259', 'EDUAR, S.H.', NULL, 21, 3, 2, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(259, '82080038', 'PATRI SIHALOHO', NULL, 26, 4, 2, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(260, '02120141', 'AGUNG NUGRAHA NADAP-DAP', NULL, 30, 5, 2, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(261, '03010386', 'ALDI PRANATA GINTING', NULL, 30, 5, 2, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(262, '02040489', 'HENDRIKSON SILALAHI', NULL, 30, 5, 2, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(263, '02071119', 'TOHONAN SITOHANG', NULL, 30, 5, 2, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(264, '03101364', 'GILANG SUTOYO', NULL, 30, 5, 2, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(265, '76030248', 'HENDRI SIAGIAN, S.H.', NULL, 24, 7, 20, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(266, '87070134', 'DENI MUSTIKA SUKMANA, S.E.', NULL, 24, 8, 20, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(267, '85081770', 'JAMIL MUNTHE, S.H., M.H.', NULL, 24, 9, 20, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(268, '87030020', 'BULET MARS SWANTO LBN. BATU, S.H.', NULL, 24, 10, 20, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(269, '96010872', 'RAMADHAN PUTRA, S.H.', NULL, 29, 11, 20, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(270, '98090415', 'ABEDNEGO TARIGAN', NULL, 29, 12, 20, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(271, '00010166', 'EDY SUSANTO PARDEDE', NULL, 29, 13, 20, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(272, '98010470', 'BOBBY ANGGARA PUTRA SIREGAR', NULL, 30, 13, 20, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(273, '01070820', 'GABRIEL PAULIMA NADEAK', NULL, 30, 13, 20, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(274, '02091526', 'ANDRE OWEN PURBA', NULL, 30, 11, 20, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(275, '04070159', 'EDWARD FERDINAND SIDABUTAR', NULL, 30, 11, 20, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(276, '03060873', 'BIMA SANTO HUTAGAOL', NULL, 30, 12, 20, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(277, '03121291', 'KRISTIAN M. H. NABABAN', NULL, 30, 12, 20, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(278, '72100484', 'SURUNG SAGALA', NULL, 24, 14, 3, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(279, '96090857', 'ZAKHARIA S. I. SIMANJUNTAK, S.H.', NULL, 29, 15, 3, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(280, '03080202', 'GRENIEL WIARTO SIHITE', NULL, 30, 15, 3, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(281, '73010107', 'TARMIZI LUBIS, S.H.', NULL, 22, 16, 4, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(282, '`198111252014122004', 'REYMESTA AMBARITA, S.Kom.', NULL, 42, 17, 4, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(283, '97090248', 'LAMTIO SINAGA, S.H.', NULL, 28, 18, 4, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(284, '97120490', 'DODI KURNIADI', NULL, 29, 18, 4, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(285, '05070285', 'EFRANTA SAPUTRA SITEPU', NULL, 30, 18, 4, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(286, '86070985', 'RADOS. S. TOGATOROP,S.H.', NULL, 26, 19, 4, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(287, '00080579', 'REYSON YOHANNES SIMBOLON', NULL, 30, 20, 4, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(288, '02090891', 'ANDRE TARUNA SIMBOLON', NULL, 30, 21, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(289, '03081525', 'YOLANDA NAULIVIA ARITONANG', NULL, 30, 20, 4, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(290, '95080918', 'SYAUQI LUTFI LUBIS, S.H., M.H.', NULL, 28, 19, 29, 6, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(291, '97050575', 'DANIEL BRANDO SIDABUKKE', NULL, 28, 19, 29, 6, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(292, '98010119', 'SUTRISNO BUTAR-BUTAR, S.H.', NULL, 29, 19, 29, 6, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(293, '81110363', 'LEONARDO SINAGA', NULL, 26, 19, 4, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(294, '76040221', 'AWALUDDIN', NULL, 24, 22, 5, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(295, '97050588', 'EFRON SARWEDY SINAGA, S.H.', NULL, 29, 23, 5, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(296, '00010095', 'PRIADI MAROJAHAN HUTABARAT', NULL, 29, 23, 5, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(297, '03070263', 'CHRIST JERICHO SAPUTRA TAMPUBOLON', NULL, 30, 23, 5, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(298, '86100287', 'EFRI PANDI', NULL, 26, 24, 21, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(299, '04010804', 'YOGI ADE PRATAMA SITOHANG', NULL, 30, 25, 21, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(300, '93100676', 'PENGEJAPEN, S.H.', NULL, 28, 26, 22, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(301, '97050876', 'MUHARRAM SYAHRI, S.H.', NULL, 29, 27, 22, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(302, '97100685', 'M.FATHUR RAHMAN, S.H.', NULL, 29, 27, 22, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(303, '03070010', 'HESKIEL WANDANA MELIALA', NULL, 30, 27, 22, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(304, '03040138', 'DANIEL RICARDO SARAGIH', NULL, 30, 27, 22, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(305, '197008291993032002', 'NENENG GUSNIARTI', NULL, 43, 28, 23, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(306, '84040532', 'EDDY SURANTA SARAGIH', NULL, 27, 29, 23, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(307, '75060617', 'BILMAR SITUMORANG', NULL, 25, 30, 24, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(308, '94080815', 'YOHANES EDI SUPRIATNO, S.H., M.H.', NULL, 28, 31, 24, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(309, '94080892', 'AGUSTIAWAN SINAGA', NULL, 28, 31, 24, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(310, '93060444', 'LISTER BROUN SITORUS', NULL, 28, 32, 25, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(311, '00070791', 'ANDREAS D. S. SITANGGANG', NULL, 30, 32, 25, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(312, '01101139', 'JACKSON SIDABUTAR', NULL, 30, 32, 25, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(313, '73050261', 'PARIMPUNAN SIREGAR', NULL, 24, 33, 26, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(314, '95030599', 'DANIEL E. LUMBANTORUAN, S.H.', NULL, 28, 34, 26, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(315, '76120670', 'DENNI BOYKE H. SIREGAR, S.H.', NULL, 24, 35, 27, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(316, '81010202', 'BENNI ARDINAL, S.H., M.H.', NULL, 26, 36, 27, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(317, '85081088', 'AGUSTINUS SINAGA', NULL, 26, 37, 27, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(318, '86081359', 'RAMBO CISLER NADEAK', NULL, 27, 38, 27, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(319, '95030796', 'PERY RAPEN YONES PARDOSI, S.H.', NULL, 28, 38, 27, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(320, '97070014', 'DWI HETRIANDY, S.H.', NULL, 28, 38, 27, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(321, '97120554', 'TRY WIBOWO', NULL, 29, 38, 27, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(322, '00080343', 'SIMON TIGRIS SIAGIAN', NULL, 29, 38, 27, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(323, '01080575', 'FIRIAN JOSUA SITORUS', NULL, 30, 38, 27, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(324, '93030551', 'GUNAWAN SITUMORANG', NULL, 28, 39, 28, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(325, '98091488', 'DANIEL BAHTERA SINAGA', NULL, 29, 39, 28, 5, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(326, '75120560', 'HORAS LARIUS SITUMORANG', NULL, 24, 40, 14, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(327, '95090650', 'JEFTA OCTAVIANUS NICO SIANTURI', NULL, 28, 41, 14, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(328, '94091146', 'SAHAT MARULI TUA SINAGA, S.H.', NULL, 28, 41, 14, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(329, '04020118', 'RONAL PARTOGI SITUMORANG', NULL, 30, 41, 14, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(330, '82070670', 'DONAL P. SITANGGANG, S.H., M.H.', NULL, 23, 42, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(331, '85050489', 'MUHAMMAD YUNUS LUBIS, S.H.', NULL, 24, 40, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(332, '80070348', 'MARBETA S. SIANIPAR, S.H.', NULL, 26, 43, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(333, '87080112', 'SITARDA AKABRI SIBUEA', NULL, 26, 44, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(334, '87051430', 'CINTER ROKHY SINAGA', NULL, 27, 45, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(335, '90080088', 'VANDU P. MARPAUNG', NULL, 27, 46, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(336, '93080556', 'ALFONSIUS GULTOM, S.H.', NULL, 28, 47, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(337, '97040848', 'TRIFIKO P. NAINGGOLAN, S.H.', NULL, 29, 48, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(338, '98110618', 'ANDRI AFRIJAL SIMARMATA', NULL, 29, 48, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(339, '02030032', 'DIEN VAROSCY I. SITUMORANG', NULL, 30, 48, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(340, '02120339', 'ARDY TRIANO MALAU', NULL, 30, 48, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(341, '02040459', 'JUNEDI SAGALA', NULL, 30, 48, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(342, '02101010', 'GABRIEL SEBASTIAN SIREGAR', NULL, 30, 48, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(343, '04020209', 'RIO F. T ERENST PANJAITAN', NULL, 30, 48, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(344, '04080118', 'AGHEO HARMANA JOUSTRA SINURAYA', NULL, 30, 47, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(345, '04010932', 'SAMUEL RINALDI PAKPAHAN', NULL, 30, 47, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(346, '04040520', 'RAYMONTIUS HAROMUNTE', NULL, 30, 47, 6, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(347, '79120994', 'EDWARD SIDAURUK, S.E., M.M.', NULL, 22, 49, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(348, '76020196', 'DARMONO SAMOSIR, S.H.', NULL, 24, 50, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(349, '83010825', 'ROYANTO PURBA, S.H.', NULL, 24, 51, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(350, '83120602', 'SUHADIYANTO, S.H.', NULL, 24, 52, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(351, '88060535', 'KUICAN SIMANJUNTAK', NULL, 27, 53, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(352, '79030434', 'MARTIN HABENSONY ARITONANG', NULL, 25, 54, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(353, '83060084', 'HENRY SIPAKKAR', NULL, 25, 55, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(354, '87011165', 'CHANDRA HUTAPEA', NULL, 27, 43, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(355, '89030401', 'CHANDRA BARIMBING', NULL, 27, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(356, '87041596', 'DEDY SAOLOAN SIGALINGGING', NULL, 27, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(357, '82050798', 'ISWAN LUKITO', NULL, 27, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(358, '95030238', 'RONI HANSVERI BANJARNAHOR', NULL, 28, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(359, '94020506', 'RODEN SUANDI TURNIP', NULL, 28, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(360, '94121145', 'SAPUTRA, S.H.', NULL, 28, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(361, '95100554', 'DIAN LESTARI GULTOM, S.H.', NULL, 28, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(362, '95110886', 'ARGIO SIMBOLON', NULL, 28, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(363, '97070616', 'EKO DAHANA PARDEDE, S.H.', NULL, 28, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(364, '97040728', 'GIDEON AFRIADI LUMBAN RAJA', NULL, 29, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(365, '98090397', 'FACHRUL REZA SILALAHI', NULL, 29, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(366, '00030346', 'RIDHOTUA F. SITANGGANG', NULL, 29, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(367, '00110362', 'NICHO FERNANDO SARAGIH', NULL, 29, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(368, '00090499', 'ADI P.S. MARBUN', NULL, 29, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(369, '01120358', 'PRIYATAMA ABDILLAH HARAHAP', NULL, 30, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(370, '01070839', 'RIZKI AFRIZAL SIMANJUNTAK', NULL, 30, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(371, '01060553', 'MIDUK YUDIANTO SINAGA', NULL, 30, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(372, '02110342', 'FRAN\'S ALEXANDER SIANIPAR', NULL, 30, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(373, '01110817', 'RAFFLES SIJABAT', NULL, 30, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(374, '01091201', 'HERIANTA TARIGAN', NULL, 30, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(375, '03030809', 'RICKY AGATHA GINTING', NULL, 30, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(376, '03020368', 'CHRISTIAN PROSPEROUS SIMANUNGKALIT', NULL, 30, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(377, '04020196', 'PINIEL RAJAGUKGUK', NULL, 30, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(378, '03090568', 'REZA SIREGAR', NULL, 30, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(379, '04031206', 'RAYMOND VAN HEZEKIEL SIAHAAN', NULL, 30, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(380, '05080602', 'M. ALAMSYAH PRAYOGA TAMBUNAN', NULL, 30, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(381, '04090567', 'IRVAN SYAPUTRA MALAU', NULL, 30, 56, 7, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(382, '79060034', 'FERRY ARIANDY, S.H., M.H', NULL, 22, 57, 8, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(383, '88100591', 'ALVIUS KRISTIAN GINTING, S.H.', NULL, 24, 40, 8, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(384, '89010155', 'BENNY SITUMORANG, S.H.', NULL, 27, 58, 8, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(385, '93050797', 'EKO PUTRA DAMANIK, S.H.', NULL, 28, 59, 8, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(386, '91050361', 'MAY FRANSISCO SIAGIAN, S.H.', NULL, 28, 59, 8, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(387, '94090839', 'ROBERTO MANALU', NULL, 29, 59, 8, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(388, '98110378', 'M. RONALD FAHROZI HARAHAP, S.H.', NULL, 29, 59, 8, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(389, '97020694', 'HERIANTO EFENDI, S.H.', NULL, 29, 59, 8, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(390, '02120224', 'TEDDI PARNASIPAN TOGATOROP', NULL, 30, 59, 8, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(391, '02090838', 'ONDIHON SIMBOLON', NULL, 30, 59, 8, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(392, '05080131', 'IVAN SIGOP SIHOMBING', NULL, 30, 59, 8, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(393, '80080676', 'NANDI BUTAR-BUTAR, S.H.', NULL, 22, 60, 10, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(394, '80050867', 'BARTO ANTONIUS SIMALANGO', NULL, 25, 61, 10, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(395, '73040390', 'HASUDUNGAN SILITONGA', NULL, 26, 62, 10, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(396, '85090954', 'JHONNY LEONARDO SILALAHI', NULL, 27, 63, 10, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(397, '83081051', 'ASRIL', NULL, 27, 64, 10, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(398, '94110350', 'INDIRWAN FRIDERICK, S.H.', NULL, 28, 64, 10, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(399, '93100793', 'EGIDIUM BRAUN SILITONGA', NULL, 28, 64, 10, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(400, '97100701', 'DINAMIKA JAYA NEGARA SITANGGANG', NULL, 30, 64, 10, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(401, '05051087', 'WIRA HARZITA', NULL, 30, 64, 10, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(402, '06100189', 'RAHMAT ANDRIAN TAMBUNAN', NULL, 30, 64, 10, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(403, '07080045', 'JONATAN DWI SAPUTRA PARAPAT', NULL, 30, 64, 10, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(404, '04051595', 'PERDANA NIKOLA SEMBIRING', NULL, 30, 64, 10, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(405, '04081205', 'PETRUS SURIA HUGALUNG', NULL, 30, 64, 10, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(406, '06010414', 'RAFAEL ARSANLILO SINULINGGA', NULL, 30, 64, 10, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(407, '06090021', 'RAJASPER SIRINGORINGO', NULL, 30, 64, 10, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(408, '72100604', 'TANGIO HAOJAHAN SITANGGANG, S.H.', NULL, 23, 65, 11, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(409, '80100836', 'MARUBA NAINGGOLAN', NULL, 25, 66, 11, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(410, '85030645', 'ROY HARIS ST. SIMAREMARE', NULL, 26, 67, 11, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(411, '80050898', 'M. DENY WAHYU', NULL, 26, 68, 11, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(412, '83050202', 'HENRI F. SIANIPAR', NULL, 25, 69, 11, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(413, '85121325', 'BUYUNG ANDRYANTO', NULL, 27, 43, 11, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(414, '91110130', 'RIANTO SITANGGANG', NULL, 28, 69, 11, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(415, '94090948', 'ROY NANDA SEMBIRING KEMBAREN', NULL, 28, 69, 11, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(416, '96031057', 'CANDRA SILALAHI, S.H.', NULL, 28, 69, 11, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(417, '02100599', 'YUNUS SAMDIO SIDABUTAR', NULL, 30, 69, 11, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(418, '03010565', 'RAINHEART SITANGGANG', NULL, 30, 69, 11, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(419, '02011312', 'BONIFASIUS NAINGGOLAN', NULL, 30, 69, 11, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(420, '00080816', 'RAY YONDO SIAHAAN', NULL, 30, 69, 11, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(421, '03040947', 'REDY EZRA JONATHAN', NULL, 30, 69, 11, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(422, '04100485', 'CHARLY H. ARITONANG', NULL, 30, 69, 11, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(423, '79120800', 'NATANAIL SURBAKTI, S.H', NULL, 22, 70, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(424, '75080942', 'JUSUP KETAREN', NULL, 24, 71, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(425, '80070492', 'ARON PERANGIN-ANGIN', NULL, 25, 72, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(426, '79060704', 'HERON GINTING', NULL, 27, 73, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(427, '86030733', 'JEFRI KHADAFI SIREGAR, S.H.', NULL, 27, 74, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(428, '89070031', 'HERIANTO TURNIP', NULL, 27, 75, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(429, '87030647', 'DION MAR\'YANSEN SILITONGA', NULL, 28, 75, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(430, '93020749', 'ROY GRIMSLAY, S.H.', NULL, 28, 75, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(431, '93090673', 'BAGUS DWI PRAKOSO, S.H.', NULL, 28, 75, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(432, '97040353', 'ICASANDRI MONANZA BR GINTING', NULL, 28, 75, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(433, '95021078', 'DIKI FEBRIAN SITORUS', NULL, 29, 75, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(434, '96031061', 'MARCHLANDA SITOHANG', NULL, 29, 75, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(435, '01080438', 'JULIVER SIDABUTAR', NULL, 29, 75, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(436, '01120281', 'FATHURROZI TINDAON', NULL, 30, 75, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(437, '02111012', 'BENY BOY CHRISTIAN SIAHAAN', NULL, 30, 21, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(438, '02111051', 'RADOT NOVALDO PANDAPOTAN PURBA', NULL, 30, 21, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(439, '05030251', 'MUHAMMAD ZIDHAN RIFALDI', NULL, 30, 21, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(440, '04050615', 'DANI INDRA PERMANA SINAGA', NULL, 30, 21, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(441, '05010048', 'HEZKIEL CAPRI SITINDAON', NULL, 30, 21, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(442, '04030824', 'BONARIS TSUYOKO DITASANI SINAGA', NULL, 30, 21, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(443, '05010014', 'ARY ANJAS SARAGIH', NULL, 30, 21, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(444, '04030805', 'GABRIEL VERY JUNIOR SITOHANG', NULL, 30, 21, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(445, '02121477', 'FIRMAN BAHTERA', NULL, 30, 21, 9, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(446, '68120522', 'SULAIMAN PANGARIBUAN, S.H', NULL, 22, 76, 12, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(447, '83080822', 'EFENDI M.  SIREGAR', NULL, 26, 77, 12, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(448, '73120275', 'ROMEL LINDUNG SIAHAAN', NULL, 26, 43, 12, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(449, '90060273', 'FRANS HOTMAN MANURUNG, S.H.', NULL, 27, 78, 12, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(450, '77070919', 'ANTONIUS SIPAYUNG', NULL, 28, 78, 12, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(451, '82051018', 'SAUT H. SIAHAAN', NULL, 26, 79, 13, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(452, '98050496', 'FERNANDO SIMBOLON', NULL, 29, 80, 13, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(453, '98030531', 'KURNIA PERMANA', NULL, 29, 80, 13, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(454, '05090232', 'STEVEN IMANUEL SITUMEANG', NULL, 30, 80, 13, 3, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(455, '69090552', 'RAHMAT KURNIAWAN', NULL, 23, 81, 15, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(456, '79090296', 'MARUKKIL J.M. PASARIBU', NULL, 25, 82, 15, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(457, '82070930', 'LANTRO LANDELINUS SAGALA', NULL, 26, 83, 15, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(458, '87120701', 'ANDY DEDY SIHOMBING, S.H.', NULL, 27, 84, 15, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(459, '86021428', 'RANGGA HATTA', NULL, 27, 85, 15, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(460, '80120573', 'ARDIANSYAH BUTAR-BUTAR', NULL, 27, 86, 15, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(461, '96120123', 'ADRYANTO SINAGA, S.H.', NULL, 28, 86, 15, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(462, '94040538', 'BROLIN ADFRIALDI HALOHO', NULL, 28, 86, 15, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(463, '95110806', 'SUGIANTO ERIK SIBORO', NULL, 28, 86, 15, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(464, '01020739', 'RISKO SIMBOLON', NULL, 30, 86, 15, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(465, '70050412', 'MAXON NAINGGOLAN', NULL, 22, 87, 16, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(466, '78040213', 'H. SWANDI SINAGA', NULL, 25, 88, 16, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(467, '77030463', 'HARATUA GULTOM', NULL, 25, 24, 16, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(468, '76120606', 'ASA MELKI HUTABARAT', NULL, 26, 89, 16, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(469, '78100741', 'JARIAHMAN SARAGIH', NULL, 26, 83, 16, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(470, '87041134', 'MUHAMMAD SYAFEI RAMADHAN', NULL, 26, 84, 16, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(471, '86121371', 'RIJALUL FIKRI SINAGA', NULL, 27, 82, 16, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(472, '85071450', 'TEGUH SYAHPUTRA', NULL, 27, 90, 16, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(473, '85041500', 'RUDYANTO LUMBANRAJA', NULL, 27, 91, 16, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(474, '96031075', 'ZULPAN SYAHPUTRA DAMANIK', NULL, 29, 91, 16, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(475, '83061022', 'RAMADAN SIREGAR, S.H.', NULL, 23, 92, 17, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(476, '86071792', 'WIDODO KABAN, S.H.', NULL, 24, 93, 17, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(477, '75120864', 'GUNTAR TAMBUNAN', NULL, 25, 88, 17, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(478, '82040124', 'JEFRI RICARDO SAMOSIR', NULL, 25, 94, 17, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(479, '84020306', 'JUITO SUPANOTO PERANGIN-ANGIN', NULL, 26, 83, 17, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(480, '83080042', 'YOPPHY RHODEAR MUNTHE', NULL, 26, 95, 17, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(481, '86010311', 'TUMBUR SITOHANG', NULL, 26, 82, 17, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53');
INSERT INTO `personil_backup` (`id`, `nrp`, `nama`, `gelar_pendidikan`, `id_pangkat`, `id_jabatan`, `id_bagian`, `id_unsur`, `status_ket`, `id_jenis_pegawai`, `tempat_lahir`, `tanggal_lahir`, `JK`, `tanggal_masuk`, `tanggal_pensiun`, `no_karpeg`, `status_nikah`, `jabatan_struktural`, `jabatan_fungsional`, `golongan`, `eselon`, `is_active`, `is_deleted`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(482, '84110202', 'DONI SURIANTO PURBA, S.H.', NULL, 27, 24, 17, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(483, '89020409', 'PATAR F. ANRI SIAHAAN', NULL, 27, 89, 17, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(484, '94090490', 'KURNIAWAN, S.H.', NULL, 28, 86, 17, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(485, '95060432', 'ASHARI BUTAR-BUTAR, S.H.', NULL, 28, 86, 17, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(486, '96061331', 'DIDI HOT BAGAS SITORUS', NULL, 30, 86, 17, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(487, '01060884', 'HORAS J.M. ARITONANG', NULL, 30, 86, 17, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(488, '04060050', 'ANDRE YEHEZKIEL HUTABARAT', NULL, 30, 86, 17, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(489, '89080105', 'CLAUDIUS HARIS PARDEDE', NULL, 28, 86, 17, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(490, '02051553', 'ZULKIFLI NASUTION', NULL, 30, 86, 17, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(491, '70010290', 'RADIAMAN SIMARMATA', NULL, 22, 96, 18, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(492, '82050839', 'HERMAWADI', NULL, 26, 84, 18, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(493, '84091124', 'BISSAR LUMBANTUNGKUP', NULL, 26, 83, 18, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(494, '70090340', 'BONAR JUBEL SIBARANI', NULL, 27, 89, 18, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(495, '77020642', 'RAMLES SITANGGANG', NULL, 27, 82, 18, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(496, '83031377', 'LUHUT SIRINGO-RINGO', NULL, 28, 86, 18, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(497, '03100001', 'ANRIAN SIGALINGGING', NULL, 30, 86, 18, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(498, '99110755', 'BONATUA LUMBANTUNGKUP', NULL, 30, 86, 18, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(499, '03050116', 'ANDRE SUGIARTO MARPAUNG', NULL, 30, 86, 18, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(500, '04030125', 'ERWIN KEVIN GULTOM', NULL, 30, 86, 18, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(501, '70020298', 'BANGUN TUA DALIMUNTHE', NULL, 22, 97, 19, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(502, '81050713', 'LANCASTER ARIANTO CANDY PASARIBU, S.H.', NULL, 25, 84, 19, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(503, '80090905', 'RUDY SETYAWAN', NULL, 25, 82, 19, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(504, '80080892', 'MANGATUR TUA TINDAON', NULL, 26, 83, 19, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(505, '87110154', 'RENO HOTMARULI TUA MANIK, S.H.', NULL, 27, 89, 19, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(506, '79020443', 'HERBINTUPA SITANGGANG', NULL, 28, 86, 19, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(507, '85121751', 'IBRAHIM TARIGAN', NULL, 28, 86, 19, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(508, '98090406', 'AGUNG NUGRAHA HARIANJA, S.H.', NULL, 29, 86, 19, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(509, '98091274', 'DANI PUTRA RUMAHORBO', NULL, 29, 86, 19, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(510, '01060198', 'KRISMAN JULU GULTOM', NULL, 30, 86, 19, 4, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:25:53', '2026-03-28 18:25:53'),
(511, '198112262024211002', 'FERNANDO SILALAHI, A.Md.', NULL, NULL, 6, 2, 2, 'aktif', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'SYSTEM_IMPORT', NULL, '2026-03-28 18:26:34', '2026-03-28 18:26:34');

-- --------------------------------------------------------

--
-- Struktur dari tabel `personil_kontak`
--

CREATE TABLE `personil_kontak` (
  `id` int(11) NOT NULL,
  `id_personil` int(11) NOT NULL,
  `jenis_kontak` enum('TELEPON','EMAIL','WHATSAPP','FAX','LAINNYA') DEFAULT NULL,
  `nilai_kontak` varchar(255) NOT NULL,
  `is_utama` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `personil_medsos`
--

CREATE TABLE `personil_medsos` (
  `id` int(11) NOT NULL,
  `id_personil` int(11) NOT NULL,
  `platform_medsos` enum('INSTAGRAM','FACEBOOK','TWITTER','LINKEDIN','TIKTOK','YOUTUBE','LAINNYA') DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `url_profile` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `personil_pendidikan`
--

CREATE TABLE `personil_pendidikan` (
  `id` int(11) NOT NULL,
  `id_personil` int(11) NOT NULL,
  `id_pendidikan` int(11) NOT NULL,
  `nama_institusi` varchar(200) DEFAULT NULL,
  `jurusan` varchar(150) DEFAULT NULL,
  `tahun_lulus` varchar(10) DEFAULT NULL,
  `ipk` decimal(3,2) DEFAULT NULL,
  `is_pendidikan_terakhir` tinyint(1) DEFAULT 0,
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
-- Struktur dari tabel `unsur`
--

CREATE TABLE `unsur` (
  `id` int(11) NOT NULL,
  `kode_unsur` varchar(50) NOT NULL,
  `nama_unsur` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tingkat` varchar(50) DEFAULT 'POLRES',
  `urutan` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `unsur`
--

INSERT INTO `unsur` (`id`, `kode_unsur`, `nama_unsur`, `deskripsi`, `tingkat`, `urutan`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'UNSUR_PIMPINAN', 'UNSUR PIMPINAN', 'Kapolres dan Wakapolres sesuai PERKAP No. 23 Tahun 2010', 'POLRES', 1, 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(2, 'UNSUR_PEMBANTU_PIMPINAN', 'UNSUR PEMBANTU PIMPINAN', 'Kepala Bagian (KABAG), Kepala Satuan (KASAT), Kepala Polsek (KAPOLSEK)', 'POLRES', 2, 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(3, 'UNSUR_PELAKSANA_TUGAS_POKOK', 'UNSUR PELAKSANA TUGAS POKOK', 'Satuan Tugas Pokok di tingkat POLRES', 'POLRES', 3, 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(4, 'UNSUR_PELAKSANA_KEWILAYAHAN', 'UNSUR PELAKSANA KEWILAYAHAN', 'Kepolisian Sektor (POLSEK) jajaran POLRES', 'POLRES', 4, 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(5, 'UNSUR_PENDUKUNG', 'UNSUR PENDUKUNG', 'Unit pendukung operasional dan administrasi', 'POLRES', 5, 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57'),
(6, 'UNSUR_LAINNYA', 'UNSUR LAINNYA', 'Unit khusus dan penugasan khusus', 'POLRES', 6, 1, '2026-03-28 18:16:57', '2026-03-28 18:16:57');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_operation` (`operation_id`),
  ADD KEY `idx_personil` (`personil_id`);

--
-- Indeks untuk tabel `bagian`
--
ALTER TABLE `bagian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_bagian` (`kode_bagian`),
  ADD KEY `id_unsur` (`id_unsur`);

--
-- Indeks untuk tabel `bagian_pimpinan`
--
ALTER TABLE `bagian_pimpinan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_active_assignment` (`bagian_id`,`personil_id`,`tanggal_mulai`),
  ADD KEY `personil_id` (`personil_id`);

--
-- Indeks untuk tabel `calendar_tokens`
--
ALTER TABLE `calendar_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `jabatan`
--
ALTER TABLE `jabatan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_jabatan` (`kode_jabatan`),
  ADD KEY `id_unsur` (`id_unsur`);

--
-- Indeks untuk tabel `master_jenis_pegawai`
--
ALTER TABLE `master_jenis_pegawai`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_jenis` (`kode_jenis`),
  ADD KEY `idx_kode` (`kode_jenis`),
  ADD KEY `idx_kategori` (`kategori`);

--
-- Indeks untuk tabel `master_pendidikan`
--
ALTER TABLE `master_pendidikan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_pendidikan` (`kode_pendidikan`),
  ADD KEY `idx_tingkat` (`tingkat_pendidikan`),
  ADD KEY `idx_kode` (`kode_pendidikan`);

--
-- Indeks untuk tabel `operations`
--
ALTER TABLE `operations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date` (`operation_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `pangkat`
--
ALTER TABLE `pangkat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_pangkat` (`nama_pangkat`);

--
-- Indeks untuk tabel `personil`
--
ALTER TABLE `personil`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nrp` (`nrp`),
  ADD KEY `idx_nrp` (`nrp`),
  ADD KEY `idx_nama` (`nama`),
  ADD KEY `idx_pangkat` (`id_pangkat`),
  ADD KEY `idx_jabatan` (`id_jabatan`),
  ADD KEY `idx_bagian` (`id_bagian`),
  ADD KEY `idx_unsur` (`id_unsur`),
  ADD KEY `idx_status` (`status_ket`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_deleted` (`is_deleted`),
  ADD KEY `fk_personil_jenis_pegawai` (`id_jenis_pegawai`);

--
-- Indeks untuk tabel `personil_kontak`
--
ALTER TABLE `personil_kontak`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_personil` (`id_personil`),
  ADD KEY `idx_jenis` (`jenis_kontak`);

--
-- Indeks untuk tabel `personil_medsos`
--
ALTER TABLE `personil_medsos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_personil` (`id_personil`),
  ADD KEY `idx_platform` (`platform_medsos`);

--
-- Indeks untuk tabel `personil_pendidikan`
--
ALTER TABLE `personil_pendidikan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_personil` (`id_personil`),
  ADD KEY `idx_pendidikan` (`id_pendidikan`);

--
-- Indeks untuk tabel `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_personil` (`personil_id`),
  ADD KEY `idx_date` (`shift_date`),
  ADD KEY `idx_bagian` (`bagian`);

--
-- Indeks untuk tabel `unsur`
--
ALTER TABLE `unsur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_unsur` (`kode_unsur`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `bagian`
--
ALTER TABLE `bagian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `bagian_pimpinan`
--
ALTER TABLE `bagian_pimpinan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `calendar_tokens`
--
ALTER TABLE `calendar_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jabatan`
--
ALTER TABLE `jabatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT untuk tabel `master_jenis_pegawai`
--
ALTER TABLE `master_jenis_pegawai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `master_pendidikan`
--
ALTER TABLE `master_pendidikan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `operations`
--
ALTER TABLE `operations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pangkat`
--
ALTER TABLE `pangkat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT untuk tabel `personil`
--
ALTER TABLE `personil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=512;

--
-- AUTO_INCREMENT untuk tabel `personil_kontak`
--
ALTER TABLE `personil_kontak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `personil_medsos`
--
ALTER TABLE `personil_medsos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `personil_pendidikan`
--
ALTER TABLE `personil_pendidikan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `unsur`
--
ALTER TABLE `unsur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bagian`
--
ALTER TABLE `bagian`
  ADD CONSTRAINT `bagian_ibfk_1` FOREIGN KEY (`id_unsur`) REFERENCES `unsur` (`id`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `jabatan_ibfk_1` FOREIGN KEY (`id_unsur`) REFERENCES `unsur` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `personil`
--
ALTER TABLE `personil`
  ADD CONSTRAINT `fk_personil_jenis_pegawai` FOREIGN KEY (`id_jenis_pegawai`) REFERENCES `master_jenis_pegawai` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `personil_ibfk_1` FOREIGN KEY (`id_pangkat`) REFERENCES `pangkat` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `personil_ibfk_2` FOREIGN KEY (`id_jabatan`) REFERENCES `jabatan` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `personil_ibfk_3` FOREIGN KEY (`id_bagian`) REFERENCES `bagian` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `personil_ibfk_4` FOREIGN KEY (`id_unsur`) REFERENCES `unsur` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `personil_kontak`
--
ALTER TABLE `personil_kontak`
  ADD CONSTRAINT `personil_kontak_ibfk_1` FOREIGN KEY (`id_personil`) REFERENCES `personil` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `personil_medsos`
--
ALTER TABLE `personil_medsos`
  ADD CONSTRAINT `personil_medsos_ibfk_1` FOREIGN KEY (`id_personil`) REFERENCES `personil` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `personil_pendidikan`
--
ALTER TABLE `personil_pendidikan`
  ADD CONSTRAINT `personil_pendidikan_ibfk_1` FOREIGN KEY (`id_personil`) REFERENCES `personil` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `personil_pendidikan_ibfk_2` FOREIGN KEY (`id_pendidikan`) REFERENCES `master_pendidikan` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
