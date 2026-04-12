-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 10 Apr 2026 pada 08.26
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
-- Struktur dari tabel `operations`
--

CREATE TABLE `operations` (
  `id` int(11) NOT NULL,
  `operation_name` varchar(255) NOT NULL,
  `tingkat_operasi` enum('terpusat','kewilayahan_polda','kewilayahan_polres','imbangan') DEFAULT 'kewilayahan_polres' COMMENT 'Tingkat penyelenggara operasi',
  `jenis_operasi` enum('intelijen','pengamanan_kegiatan','pemeliharaan_keamanan','penegakan_hukum','pemulihan_keamanan','kontinjensi','lainnya') DEFAULT 'pemeliharaan_keamanan' COMMENT 'Bentuk/jenis operasi sesuai Perkap',
  `operation_month` varchar(7) DEFAULT NULL COMMENT 'Format YYYY-MM, bulan pelaksanaan sebelum tanggal pasti ditentukan',
  `operation_date` date DEFAULT NULL COMMENT 'Tanggal pasti, boleh kosong jika masih rencana',
  `operation_date_end` date DEFAULT NULL COMMENT 'Tanggal akhir operasi',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `required_personnel` int(11) DEFAULT 0,
  `kuat_personil` int(11) DEFAULT 0 COMMENT 'Kekuatan / jumlah personil',
  `dukgra` decimal(15,2) DEFAULT 0.00 COMMENT 'Dukungan anggaran (Rupiah)',
  `status` enum('planned','active','completed','cancelled') DEFAULT 'planned',
  `google_event_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `recurrence_type` enum('none','daily','weekly','monthly','yearly') NOT NULL DEFAULT 'none',
  `recurrence_interval` int(11) NOT NULL DEFAULT 1,
  `recurrence_days` varchar(20) DEFAULT NULL,
  `recurrence_end` date DEFAULT NULL,
  `recurrence_parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `operations`
--

INSERT INTO `operations` (`id`, `operation_name`, `tingkat_operasi`, `jenis_operasi`, `operation_month`, `operation_date`, `operation_date_end`, `start_time`, `end_time`, `location`, `description`, `required_personnel`, `kuat_personil`, `dukgra`, `status`, `google_event_id`, `created_at`, `updated_at`, `recurrence_type`, `recurrence_interval`, `recurrence_days`, `recurrence_end`, `recurrence_parent_id`) VALUES
(1, 'OPS BINA KESUMA TOBA', 'kewilayahan_polda', 'pemeliharaan_keamanan', '2026-03', NULL, NULL, NULL, NULL, 'POLRES SAMOSIR', 'OPERASI KEPOLISIAN KEWILAYAHAN, DALAM RANGKA PENCEGAHAN TERJADINYA GANGGUAN KAMTIBMAS TERKAIT KENAKALAN REMAJA, PELECEHAN SEKS TERHADAP ANAK, KEKERASAN TERHADAP PEREMPUAN DAN ANAK, SERTA MASALAH TKI', 0, 25, 23750000.00, 'planned', NULL, '2026-04-10 05:50:12', '2026-04-10 06:05:02', 'none', 1, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `operations`
--
ALTER TABLE `operations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date` (`operation_date`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `operations`
--
ALTER TABLE `operations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
