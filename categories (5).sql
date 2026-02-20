-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 19, 2026 at 04:01 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory_esdm`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `code`, `name`, `parent_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '1.01.03', 'KATEGORI 1.01.03', 57, NULL, 1, '2026-01-23 09:00:00', '2026-02-12 03:19:12'),
(2, '1.01.03.01', 'ALAT TULIS KANTOR', 1, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(3, '1.01.03.02', 'KERTAS DAN COVER', 1, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(4, '1.01.03.04', 'BAHAN KOMPUTER', 1, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(5, '1.01.03.06', 'ALAT LISTRIK', 1, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(6, '1.01.03.01.001', 'Alat Tulis', 2, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(7, '1.01.03.01.002', 'Tinta Tulis, Tinta Stempel', 2, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(8, '1.01.03.01.003', 'Penjepit Kertas', 2, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(9, '1.01.03.01.004', 'Penghapus/Korektor', 2, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(10, '1.01.03.01.005', 'Buku Tulis', 2, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(11, '1.01.03.01.006', 'Ordner Dan Map', 2, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(12, '1.01.03.01.007', 'Penggaris', 2, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(13, '1.01.03.01.008', 'Cutter (Alat Tulis Kantor)', 2, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(14, '1.01.03.01.009', 'Pita Mesin Ketik', 2, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(15, '1.01.03.01.010', 'Alat Perekat', 2, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(16, '1.01.03.01.011', 'Stadler HD', 2, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(17, '1.01.03.01.012', 'Staples', 2, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(18, '1.01.03.01.013', 'Isi Staples', 2, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(19, '1.01.03.01.014', 'Barang Cetakan', 2, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(20, '1.01.03.01.015', 'Seminar Kit', 2, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(21, '1.01.03.01.999', 'Alat Tulis Kantor Lainnya', 2, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(22, '1.01.03.02.001', 'Kertas HVS', 3, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(23, '1.01.03.02.002', 'Berbagai Kertas', 3, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(24, '1.01.03.02.003', 'Kertas Cover', 3, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(25, '1.01.03.02.004', 'Amplop', 3, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(26, '1.01.03.02.005', 'Kop Surat', 3, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(27, '1.01.03.02.999', 'Kertas Dan Cover Lainnya', 3, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(28, '1.01.03.04.001', 'Continuous Form', 4, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(29, '1.01.03.04.002', 'Computer File/Tempat Disket', 4, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(30, '1.01.03.04.003', 'Pita Printer', 4, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(31, '1.01.03.04.004', 'Tinta/Toner Printer', 4, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(32, '1.01.03.04.005', 'Disket', 4, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(33, '1.01.03.04.006', 'USB/Flash Disk', 4, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(34, '1.01.03.04.007', 'Kartu Memori', 4, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(35, '1.01.03.04.008', 'CD/DVD Drive', 4, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(36, '1.01.03.04.009', 'Harddisk Internal', 4, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(37, '1.01.03.04.010', 'Mouse', 4, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(38, '1.01.03.04.011', 'CD/DVD', 4, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(39, '1.01.03.04.999', 'Bahan Komputer Lainnya', 4, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(40, '1.01.03.06.001', 'Kabel Listrik', 5, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(41, '1.01.03.06.002', 'Lampu Listrik', 5, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(42, '1.01.03.06.003', 'Stop Kontak', 5, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(43, '1.01.03.06.004', 'Saklar', 5, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(44, '1.01.03.06.005', 'Stacker', 5, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(45, '1.01.03.06.006', 'Balast', 5, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(46, '1.01.03.06.007', 'Starter', 5, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(47, '1.01.03.06.008', 'Vitting', 5, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(48, '1.01.03.06.009', 'Accu', 5, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(49, '1.01.03.06.010', 'Batu Baterai', 5, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(50, '1.01.03.06.011', 'Stavol', 5, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(51, '1.01.03.06.999', 'Alat Listrik Lainnya', 5, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
(54, '1', 'KATEGORI 1', NULL, NULL, 1, '2026-02-12 03:16:44', '2026-02-12 03:19:12'),
(57, '1.01', 'KATEGORI 1.01', 54, NULL, 1, '2026-02-12 03:19:12', '2026-02-12 03:19:12'),
(60, '1.01.03.14', 'OBAT-OBATAN', 1, NULL, 1, '2026-02-12 03:29:53', '2026-02-12 03:29:53'),
(61, '1.01.03.14.001', 'Obat Cair', 60, NULL, 1, '2026-02-12 03:29:53', '2026-02-12 03:29:53'),
(62, '1.01.03.14.002', 'Obat Padat', 60, NULL, 1, '2026-02-12 03:29:53', '2026-02-12 03:29:53'),
(63, '1.01.03.14.003', 'Obat Gas', 60, NULL, 1, '2026-02-12 03:29:53', '2026-02-12 03:29:53'),
(64, '1.01.03.14.004', 'Obat Serbuk/Tepung', 60, NULL, 1, '2026-02-12 03:29:53', '2026-02-12 03:29:53'),
(65, '1.01.03.14.005', 'Obat Gel/Salep', 60, NULL, 1, '2026-02-12 03:29:53', '2026-02-12 03:29:53'),
(66, '1.01.03.14.006', 'Alat/Obat Kontrasepsi Keluarga Berencana', 60, NULL, 1, '2026-02-12 03:29:53', '2026-02-12 03:29:53'),
(67, '1.01.03.14.007', 'Non Alat/Obat Kontrasepsi Keluarga Berencana', 60, NULL, 1, '2026-02-12 03:29:53', '2026-02-12 03:29:53'),
(69, '1.01.03.14.999', 'Obat Lainnya', 60, NULL, 1, '2026-02-12 03:29:53', '2026-02-12 03:29:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_category_code` (`code`),
  ADD KEY `idx_category_parent` (`parent_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
