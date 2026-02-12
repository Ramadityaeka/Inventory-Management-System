-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 12, 2026 at 03:08 AM
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
-- Table structure for table `approvals`
--

CREATE TABLE `approvals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `submission_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `action` enum('approved','rejected') NOT NULL,
  `rejection_reason` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `approvals`
--
DELIMITER $$
CREATE TRIGGER `after_insert_approvals` AFTER INSERT ON `approvals` FOR EACH ROW BEGIN
                DECLARE item_id_var BIGINT;
                DECLARE warehouse_id_var BIGINT;
                DECLARE quantity_var INT;
                DECLARE conversion_factor_var INT DEFAULT 1;
                DECLARE total_qty INT;
                DECLARE unit_id_var BIGINT;

                SELECT s.item_id, s.warehouse_id, s.quantity, COALESCE(s.conversion_factor, 1), s.unit_id
                INTO item_id_var, warehouse_id_var, quantity_var, conversion_factor_var, unit_id_var
                FROM submissions s
                WHERE s.id = NEW.submission_id;

                SET total_qty = quantity_var * conversion_factor_var;

                IF NEW.action = "approved" AND item_id_var IS NOT NULL THEN
                    -- Update stocks
                    INSERT INTO stocks (item_id, unit_id, warehouse_id, quantity, last_updated)
                    VALUES (item_id_var, unit_id_var, warehouse_id_var, total_qty, NOW())
                    ON DUPLICATE KEY UPDATE
                        quantity = quantity + total_qty,
                        last_updated = NOW();

                    -- Insert stock movement with unit_id
                    INSERT INTO stock_movements (item_id, unit_id, warehouse_id, quantity, movement_type, reference_type, reference_id, created_by, created_at)
                    VALUES (
                        item_id_var,
                        unit_id_var,
                        warehouse_id_var,
                        total_qty,
                        "in",
                        "submission",
                        NEW.submission_id,
                        NEW.admin_id,
                        NOW()
                    );
                END IF;
            END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key_col` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `cache_key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, '1.01.03', 'ALAT/BAHAN UNTUK KEGIATAN KANTOR', NULL, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50'),
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
(51, '1.01.03.06.999', 'Alat Listrik Lainnya', 5, NULL, 1, '2026-01-23 09:00:00', '2026-01-28 11:10:50');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `unit` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `category_id`, `code`, `name`, `supplier_id`, `unit`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 8, '1.01.03.01.003.001', 'Binder Clip 200', NULL, 'Dus', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(2, 8, '1.01.03.01.003.002', 'Binder Clip 260', NULL, 'Dus', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(3, 22, '1.01.03.02.001.001', 'Kertas HVS Bola Dunia A4 80 Gram', NULL, 'Rim', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(4, 22, '1.01.03.02.001.002', 'Kertas HVS Bola Dunia A4 70 Gram', NULL, 'Rim', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(5, 11, '1.01.03.01.006.001', 'Stopmap 5002 Diamond ( Kuning )', NULL, 'Dus', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(6, 11, '1.01.03.01.006.002', 'Stopmap 5002 Diamond ( Putih )', NULL, 'Dus', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(7, 8, '1.01.03.01.003.003', 'Blinder Clip Joyco 155', NULL, 'Dus', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(8, 11, '1.01.03.01.006.003', 'Clear Sheet 5721 Bindex', NULL, 'Pack', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(9, 6, '1.01.03.01.001.001', 'Ballpoint Kenko K-1', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(10, 37, '1.01.03.04.010.001', 'Mouse USB Logitech Pop Icon Mouse', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(11, 17, '1.01.03.01.012.001', 'Stappler HD-10 Joyko', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(12, 33, '1.01.03.04.006.001', 'Flashdisk 256Gb Sandisk', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(13, 6, '1.01.03.01.001.002', 'Ballpoint Sarasa 0.7 Zebra ( Biru )', NULL, 'Lusin', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(14, 6, '1.01.03.01.001.003', 'Ballpoint Sarasa 0.7 Zebra ( Hitam )', NULL, 'Lusin', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(15, 21, '1.01.03.01.999.001', 'Pelubang Kertas No.40 Kenko', NULL, 'Unit', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(16, 8, '1.01.03.01.003.004', 'Paper Clip Joyco No 3', NULL, 'Pak', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(17, 8, '1.01.03.01.003.005', 'Paper Clip Joyco No 1', NULL, 'Pak', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(18, 13, '1.01.03.01.008.001', 'Pisau cutter A 300', NULL, 'Box', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(19, 13, '1.01.03.01.008.002', 'Gunting', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(20, 31, '1.01.03.04.004.001', 'Toner HP 55 A', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(21, 31, '1.01.03.04.004.002', 'Toner HP 12 A', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(22, 31, '1.01.03.04.004.003', 'Toner HP 49 A', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(23, 31, '1.01.03.04.004.004', 'Toner 49 HP A', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(24, 31, '1.01.03.04.004.005', 'Toner HP 78A', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(25, 31, '1.01.03.04.004.006', 'Toner HP 85A', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(26, 6, '1.01.03.01.001.004', 'Spidol Snawman hitam permanen', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(27, 21, '1.01.03.01.999.002', 'Pembolong Kertas No.40 kenko', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(28, 6, '1.01.03.01.001.005', 'Stabilo Warna Biru', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(29, 6, '1.01.03.01.001.006', 'Ballpoint Snowman v3 Gel.0,5', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(30, 6, '1.01.03.01.001.007', 'Ballpoint Faster', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(31, 6, '1.01.03.01.001.008', 'Ballpoint Standar', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(32, 6, '1.01.03.01.001.009', 'Pensil Faber Castel', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(35, 15, '1.01.03.01.010.003', 'Lakban Hitam 36 mm', NULL, 'Roll', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(36, 15, '1.01.03.01.010.004', 'Lakban Diamaru Bening', NULL, 'Roll', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(37, 15, '1.01.03.01.010.005', 'Lakban 1/2 in x 72 yds', NULL, 'Roll', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(40, 23, '1.01.03.02.002.001', 'Post it Sign Here', NULL, 'Pak', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(41, 23, '1.01.03.02.002.002', 'Post it Notes', NULL, 'Pak', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(42, 23, '1.01.03.02.002.003', 'Post it Warna', NULL, 'Pak', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(43, 18, '1.01.03.01.013.001', 'Isi Staples HD 10', NULL, 'Pak', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(44, 9, '1.01.03.01.004.001', 'Tip ex Kenko', NULL, 'Pak', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(45, 15, '1.01.03.01.010.006', 'Lem Glue Stick Joyko', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(46, 15, '1.01.03.01.010.007', 'Lem Glue Stick Kenko', NULL, 'Buah', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(47, 49, '1.01.03.06.010.001', 'Bateray ABC Alkaline AA Isi 2', NULL, 'Set', NULL, 1, '2026-02-02 09:20:23', '2026-02-02 09:20:23'),
(48, 49, '1.01.03.06.010.002', 'Bateray ABC Alkaline AAA Isi 2', NULL, 'Set', NULL, 1, '2026-02-03 04:22:27', '2026-02-03 04:22:27'),
(49, 31, '1.01.03.04.004.007', 'Tinta Epson Color', NULL, 'Buah', NULL, 1, '2026-02-03 04:25:24', '2026-02-03 04:25:24'),
(50, 31, '1.01.03.04.004.008', 'Tinta Epson Black', NULL, 'Buah', NULL, 1, '2026-02-03 04:25:49', '2026-02-03 04:25:49'),
(51, 39, '1.01.03.04.999.001', 'Power Bank EW 71', NULL, 'Buah', NULL, 1, '2026-02-03 04:27:25', '2026-02-03 06:32:39');

-- --------------------------------------------------------

--
-- Table structure for table `item_units`
--

CREATE TABLE `item_units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `conversion_factor` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_units`
--

INSERT INTO `item_units` (`id`, `item_id`, `name`, `conversion_factor`, `created_at`, `updated_at`) VALUES
(1, 1, 'Dus', 500, '2026-02-02 07:47:57', '2026-02-02 07:47:57'),
(3, 40, 'Pak', 1, '2026-02-04 01:59:56', '2026-02-04 01:59:56'),
(4, 4, 'Rim', 1, '2026-02-04 02:00:32', '2026-02-04 02:00:32'),
(5, 6, 'Dus', 1, '2026-02-11 02:19:09', '2026-02-11 02:19:09'),
(6, 36, 'Roll', 1, '2026-02-11 02:23:57', '2026-02-11 02:23:57'),
(7, 3, 'Rim', 1, '2026-02-11 02:41:43', '2026-02-11 02:41:43');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(4, '0001_01_01_000002_create_jobs_table', 2),
(5, '2026_01_06_080729_add_indexes_for_performance', 3),
(6, '2026_01_07_042839_add_unit_price_invoice_to_submissions_table', 4),
(7, '2026_01_07_043802_add_item_name_to_submissions_table', 5),
(8, '2026_01_07_062011_make_receive_date_nullable_in_submissions_table', 6),
(9, '2026_01_07_064353_add_timestamps_to_approvals_table', 7),
(10, '2026_01_07_070131_fix_approvals_table_columns', 8),
(11, '2026_01_07_070729_fix_approvals_trigger', 9),
(12, '2026_01_07_070943_update_approvals_trigger_handle_null_item', 10),
(13, '2026_01_07_081047_fix_stock_movements_trigger_column', 11),
(14, '2026_01_08_000000_drop_unused_tables', 12),
(15, '2026_01_13_020200_add_updated_at_to_notifications_table', 12),
(16, '2026_01_13_100000_add_prefix_to_categories_table', 13),
(17, '2026_01_13_064406_add_code_prefix_to_categories_table', 14),
(18, '2026_01_16_092150_add_transaction_type_to_submissions_table', 15),
(19, '2026_01_16_092648_update_approvals_trigger_handle_out_transaction', 16),
(20, '2026_01_16_093101_create_stock_requests_table', 17),
(21, '2026_01_19_005515_remove_transaction_type_from_submissions_table', 18),
(22, '2026_01_19_005559_remove_transaction_type_from_submissions_table', 18),
(23, '2026_01_19_005644_update_approvals_trigger_incoming_only', 19),
(24, '2026_01_19_100000_add_inactive_fields_to_items_table', 20),
(25, '2026_01_20_032344_add_is_active_to_categories_table', 21),
(26, '2026_01_20_111816_create_transfers_table', 22),
(27, '2026_01_23_000001_remove_threshold_columns', 23),
(28, '2026_01_23_000002_update_stock_alert_trigger', 24),
(29, '2026_01_23_030000_update_categories_hierarchical_structure', 25),
(30, '2026_01_23_030100_update_items_remove_supplier', 25),
(31, '2026_01_23_040000_rename_warehouse_to_unit', 25),
(32, '2026_01_27_083123_convert_all_timestamps_to_wib', 25),
(33, '2026_01_27_150939_drop_view_transfer_summary', 25),
(34, '2026_01_28_000000_create_item_units_table', 25),
(35, '2026_01_28_000001_add_conversion_factor_to_submissions_table', 25),
(36, '2026_01_28_000002_update_after_insert_approvals_trigger', 25),
(37, '2026_01_28_000003_create_compat_views_for_warehouses', 26),
(38, '2026_01_28_000004_add_warehouse_id_compat_to_stock_requests', 27),
(39, '2026_01_28_000005_add_warehouse_id_compat_to_stocks', 28),
(40, '2026_01_28_000006_add_warehouse_id_compat_to_stock_movements', 29),
(41, '2026_01_28_000007_add_warehouse_id_compat_to_submissions', 30),
(42, '2026_02_02_140000_add_unit_fields_to_stock_requests', 31),
(43, '2026_01_28_000001_update_approval_trigger_use_conversion_factor', 32),
(47, '2026_02_04_000001_fix_after_insert_approvals_trigger_unit_id', 33),
(48, '2026_02_08_145041_add_missing_columns_to_users_table', 34),
(49, '2026_02_08_150000_create_transfers_table', 35),
(50, '2026_02_11_000001_add_received_proof_to_stock_requests', 36);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `reference_type`, `reference_id`, `is_read`, `read_at`, `created_at`, `updated_at`) VALUES
(13, 9, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah ditugaskan ke gudang: Inspektorat III oleh Super Admin', 'user', 6, 0, NULL, '2026-01-22 09:00:00', '2026-01-22 09:00:00'),
(14, 9, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah ditugaskan ke gudang: Inspektorat III oleh Super Admin', 'user', 6, 0, NULL, '2026-01-22 09:00:00', '2026-01-22 09:00:00'),
(15, 9, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah dihapus dari gudang: Bagian Umum oleh Super Admin', 'user', 6, 0, NULL, '2026-01-22 09:00:00', '2026-01-22 09:00:00'),
(16, 9, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah dihapus dari gudang: Bagian Umum oleh Super Admin', 'user', 6, 0, NULL, '2026-01-22 09:00:00', '2026-01-22 09:00:00'),
(27, 4, 'password_reset', 'Password Direset', 'Password Anda telah direset oleh Super Admin. Silakan login dengan password baru dan segera menggantinya.', 'user', 6, 0, NULL, '2026-01-26 09:00:00', '2026-01-26 09:00:00'),
(28, 4, 'password_reset', 'Password Direset', 'Password Anda telah direset oleh Super Admin. Silakan login dengan password baru dan segera menggantinya.', 'user', 6, 0, NULL, '2026-01-26 09:00:00', '2026-01-26 09:00:00'),
(29, 4, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 0, NULL, '2026-01-26 09:00:00', '2026-01-26 09:00:00'),
(34, 5, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 1, '2026-01-31 14:48:06', '2026-01-28 08:41:18', '2026-01-31 14:48:06'),
(39, 5, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 0, NULL, '2026-02-02 03:56:14', '2026-02-02 03:56:14'),
(40, 5, 'submission_rejected', 'Submission Rejected', 'Submission Anda ditolak. Alasan: Data tidak lengkap atau tidak valid', NULL, NULL, 0, NULL, '2026-02-02 03:56:38', '2026-02-02 03:56:38'),
(45, 9, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah ditugaskan ke gudang: Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal oleh Super Admin', 'user', 6, 0, NULL, '2026-02-02 04:34:46', '2026-02-02 04:34:46'),
(46, 9, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah ditugaskan ke gudang: Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal oleh Super Admin', 'user', 6, 0, NULL, '2026-02-02 04:34:47', '2026-02-02 04:34:47'),
(47, 9, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah dihapus dari gudang: Inspektorat III oleh Super Admin', 'user', 6, 0, NULL, '2026-02-02 04:34:47', '2026-02-02 04:34:47'),
(48, 9, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah dihapus dari gudang: Inspektorat III oleh Super Admin', 'user', 6, 0, NULL, '2026-02-02 04:34:47', '2026-02-02 04:34:47'),
(49, 9, 'new_submission', 'Submission Barang Baru', 'Submission baru dari pak heri untuk item Binder Clip 200 (5 Lembar) di Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'submission', 20, 0, NULL, '2026-02-02 06:10:35', '2026-02-02 06:10:35'),
(50, 9, 'new_submission', 'Submission Barang Baru', 'Submission baru dari pak heri untuk item Binder Clip 200 (5 Lembar) di Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'submission', 20, 0, NULL, '2026-02-02 06:10:35', '2026-02-02 06:10:35'),
(51, 9, 'new_submission', 'Submission Barang Baru', 'Submission baru dari pak heri untuk item Kertas HVS Bola Dunia A4 80 Gram (120 Lembar) di Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'submission', 21, 0, NULL, '2026-02-02 06:13:00', '2026-02-02 06:13:00'),
(52, 9, 'new_submission', 'Submission Barang Baru', 'Submission baru dari pak heri untuk item Kertas HVS Bola Dunia A4 80 Gram (120 Lembar) di Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'submission', 21, 0, NULL, '2026-02-02 06:13:00', '2026-02-02 06:13:00'),
(57, 9, 'new_submission', 'Submission Barang Baru', 'Submission baru dari Herry Effendi untuk item Stopmap 5002 Diamond ( Kuning ) (7 Lembar) di Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'submission', 22, 0, NULL, '2026-02-02 06:31:19', '2026-02-02 06:31:19'),
(59, 9, 'new_submission', 'Submission Barang Baru', 'Submission baru dari Herry Effendi untuk item Stopmap 5002 Diamond ( Kuning ) (7 Lembar) di Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'submission', 22, 0, NULL, '2026-02-02 06:31:19', '2026-02-02 06:31:19'),
(62, 9, 'new_submission', 'Submission Barang Baru', 'Submission baru dari Herry Effendi untuk item test (80 Pcs) di Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'submission', 23, 0, NULL, '2026-02-02 06:41:33', '2026-02-02 06:41:33'),
(64, 9, 'new_submission', 'Submission Barang Baru', 'Submission baru dari Herry Effendi untuk item test (80 Pcs) di Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'submission', 23, 0, NULL, '2026-02-02 06:41:33', '2026-02-02 06:41:33'),
(70, 4, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah ditugaskan ke gudang: test oleh Super Admin', 'user', 6, 0, NULL, '2026-02-04 01:15:10', '2026-02-04 01:15:10'),
(71, 4, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah ditugaskan ke gudang: test oleh Super Admin', 'user', 6, 0, NULL, '2026-02-04 01:15:10', '2026-02-04 01:15:10'),
(72, 4, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah dihapus dari gudang: Inspektorat II oleh Super Admin', 'user', 6, 0, NULL, '2026-02-04 01:15:10', '2026-02-04 01:15:10'),
(73, 4, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah dihapus dari gudang: Inspektorat II oleh Super Admin', 'user', 6, 0, NULL, '2026-02-04 01:15:10', '2026-02-04 01:15:10'),
(82, 4, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 0, NULL, '2026-02-04 01:38:24', '2026-02-04 01:38:24'),
(83, 4, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 0, NULL, '2026-02-04 01:40:26', '2026-02-04 01:40:26'),
(84, 4, 'submission_rejected', 'Submission Rejected', 'Submission Anda ditolak. Alasan: Data tidak lengkap atau tidak valid', NULL, NULL, 0, NULL, '2026-02-04 01:41:13', '2026-02-04 01:41:13'),
(85, 4, 'submission_rejected', 'Submission Rejected', 'Submission Anda ditolak. Alasan: Data duplikat, submission serupa sudah ada - data tidak di iisi semua jadi saya tolak, dan juga barangnya tidak jelas', NULL, NULL, 0, NULL, '2026-02-04 01:45:43', '2026-02-04 01:45:43'),
(86, 2, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari test untuk Post it Sign Here (20 Pak) di test', 'stock_request', 1, 0, NULL, '2026-02-04 01:59:56', '2026-02-04 01:59:56'),
(87, 2, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari test untuk Kertas HVS Bola Dunia A4 70 Gram (30 Rim) di test', 'stock_request', 2, 0, NULL, '2026-02-04 02:00:32', '2026-02-04 02:00:32'),
(88, 4, 'warning', 'Request Penggunaan Barang Ditolak', 'Request penggunaan Kertas HVS Bola Dunia A4 70 Gram sebanyak 30 telah ditolak. Alasan: kebanyakan', NULL, NULL, 0, NULL, '2026-02-04 02:02:04', '2026-02-04 02:02:04'),
(89, 4, 'info', 'Request Penggunaan Barang Disetujui', 'Request penggunaan Post it Sign Here sebanyak 20 telah disetujui oleh irsyad.', NULL, NULL, 0, NULL, '2026-02-04 02:04:24', '2026-02-04 02:04:24'),
(90, 12, 'info', 'Selamat Datang di Inventory ESDM', 'Akun Anda telah berhasil dibuat oleh Super Admin. Anda sekarang dapat login ke sistem.', NULL, NULL, 0, NULL, '2026-02-05 09:05:50', '2026-02-05 09:05:50'),
(91, 12, 'password_reset', 'Password Direset', 'Password Anda telah direset oleh Super Admin. Silakan login dengan password baru dan segera menggantinya.', 'user', 6, 0, NULL, '2026-02-05 09:13:36', '2026-02-05 09:13:36'),
(92, 4, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah ditugaskan ke gudang: Klinik oleh Laris Siregar', 'user', 12, 0, NULL, '2026-02-10 03:58:25', '2026-02-10 03:58:25'),
(93, 2, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah ditugaskan ke gudang: Klinik oleh Laris Siregar', 'user', 12, 0, NULL, '2026-02-10 03:58:39', '2026-02-10 03:58:39'),
(94, 2, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah dihapus dari gudang: test oleh Laris Siregar', 'user', 12, 0, NULL, '2026-02-10 03:58:39', '2026-02-10 03:58:39'),
(95, 14, 'info', 'Selamat Datang di Inventory ESDM', 'Akun Anda telah berhasil dibuat oleh Super Admin. Anda sekarang dapat login ke sistem.', NULL, NULL, 0, NULL, '2026-02-10 04:01:00', '2026-02-10 04:01:00'),
(96, 14, 'info', 'Penugasan Gudang', 'Anda telah ditugaskan sebagai Admin Gudang untuk mengelola gudang: Klinik.', NULL, NULL, 0, NULL, '2026-02-10 04:01:00', '2026-02-10 04:01:00'),
(97, 14, 'role_changed', 'Perubahan Role', 'Role Anda telah diubah dari Admin Gudang menjadi Staff Gudang oleh Laris Siregar', 'user', 12, 0, NULL, '2026-02-10 04:10:23', '2026-02-10 04:10:23'),
(98, 15, 'info', 'Selamat Datang di Inventory ESDM', 'Akun Anda telah berhasil dibuat oleh Super Admin. Anda sekarang dapat login ke sistem.', NULL, NULL, 0, NULL, '2026-02-10 07:47:49', '2026-02-10 07:47:49'),
(99, 15, 'info', 'Penugasan Gudang', 'Anda telah ditugaskan sebagai Admin Gudang untuk mengelola gudang: Klinik.', NULL, NULL, 0, NULL, '2026-02-10 07:47:49', '2026-02-10 07:47:49'),
(100, 14, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah ditugaskan ke gudang: Klinik oleh Laris Siregar', 'user', 12, 0, NULL, '2026-02-10 07:47:59', '2026-02-10 07:47:59'),
(101, 9, 'new_submission', 'Submission Barang Baru', 'Submission baru dari Herry Effendi untuk item Kertas HVS Bola Dunia A4 70 Gram (20 Rim) di Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'submission', 6, 0, NULL, '2026-02-11 02:14:22', '2026-02-11 02:14:22'),
(102, 11, 'new_submission', 'Submission Barang Baru', 'Submission baru dari Herry Effendi untuk item Kertas HVS Bola Dunia A4 70 Gram (20 Rim) di Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'submission', 6, 0, NULL, '2026-02-11 02:14:23', '2026-02-11 02:14:23'),
(103, 9, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari Herry Effendi untuk Stopmap 5002 Diamond ( Putih ) (5 Dus) di Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'stock_request', 3, 0, NULL, '2026-02-11 02:19:09', '2026-02-11 02:19:09'),
(104, 11, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari Herry Effendi untuk Stopmap 5002 Diamond ( Putih ) (5 Dus) di Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'stock_request', 3, 0, NULL, '2026-02-11 02:19:09', '2026-02-11 02:19:09'),
(105, 10, 'info', 'Request Penggunaan Barang Disetujui', 'Request penggunaan Stopmap 5002 Diamond ( Putih ) sebanyak 5 telah disetujui oleh Hari Tri Subagyo.', NULL, NULL, 0, NULL, '2026-02-11 02:21:13', '2026-02-11 02:21:13'),
(106, 9, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari Herry Effendi untuk Lakban Diamaru Bening (1 Roll) di Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'stock_request', 4, 0, NULL, '2026-02-11 02:23:57', '2026-02-11 02:23:57'),
(107, 11, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari Herry Effendi untuk Lakban Diamaru Bening (1 Roll) di Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'stock_request', 4, 0, NULL, '2026-02-11 02:23:57', '2026-02-11 02:23:57'),
(108, 9, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari Herry Effendi untuk Kertas HVS Bola Dunia A4 80 Gram (5 Rim) di Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'stock_request', 5, 0, NULL, '2026-02-11 02:41:43', '2026-02-11 02:41:43'),
(109, 11, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari Herry Effendi untuk Kertas HVS Bola Dunia A4 80 Gram (5 Rim) di Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'stock_request', 5, 0, NULL, '2026-02-11 02:41:43', '2026-02-11 02:41:43'),
(110, 16, 'info', 'Selamat Datang di Inventory ESDM', 'Akun Anda telah berhasil dibuat oleh Super Admin. Anda sekarang dapat login ke sistem.', NULL, NULL, 0, NULL, '2026-02-11 10:33:30', '2026-02-11 10:33:30'),
(111, 16, 'info', 'Penugasan Gudang', 'Anda telah ditugaskan sebagai Staff Gudang untuk mengelola gudang: Rumah Tangga.', NULL, NULL, 0, NULL, '2026-02-11 10:33:30', '2026-02-11 10:33:30');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('1WEvvJV29cNJpNOCMH2aXkRa1SIwhiuBGc3Z3ztI', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiQ3VFVlU2MFp1TXFnMVdERFNtQnNHMmVlZ0tGUFJPWHFQaGJEUW9qMSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQ4OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vcmVwb3J0cy90cmFuc2FjdGlvbnMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo2O30=', 1770860611);

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `stocks`
--
DELIMITER $$
CREATE TRIGGER `after_stock_update` AFTER UPDATE ON `stocks` FOR EACH ROW BEGIN
                    IF NEW.quantity = 0 THEN
                        INSERT INTO stock_alerts (item_id, warehouse_id, alert_type, current_stock) VALUES (NEW.item_id, NEW.unit_id, 'out_of_stock', NEW.quantity) ON DUPLICATE KEY UPDATE current_stock = NEW.quantity, resolved_at = NULL, created_at = NOW();
                    END IF;
                END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_update_stocks_alert` AFTER UPDATE ON `stocks` FOR EACH ROW BEGIN
                    IF NEW.quantity = 0 AND OLD.quantity != 0 THEN
                        INSERT INTO stock_alerts (item_id, warehouse_id, alert_type, current_stock, threshold, created_at, updated_at) VALUES (NEW.item_id, NEW.unit_id, "out_of_stock", 0, 0, NOW(), NOW());
                    END IF;
                END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `stocks_before_insert_compat` BEFORE INSERT ON `stocks` FOR EACH ROW BEGIN
    
    IF NEW.warehouse_id IS NULL AND NEW.unit_id IS NOT NULL THEN
        SET NEW.warehouse_id = NEW.unit_id;
    END IF;
    
    
    IF NEW.unit_id IS NULL AND NEW.warehouse_id IS NOT NULL THEN
        SET NEW.unit_id = NEW.warehouse_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `stocks_before_update_compat` BEFORE UPDATE ON `stocks` FOR EACH ROW BEGIN
    
    IF NEW.warehouse_id IS NULL AND NEW.unit_id IS NOT NULL THEN
        SET NEW.warehouse_id = NEW.unit_id;
    END IF;
    
    
    IF NEW.unit_id IS NULL AND NEW.warehouse_id IS NOT NULL THEN
        SET NEW.unit_id = NEW.warehouse_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `stock_alerts`
--

CREATE TABLE `stock_alerts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `alert_type` enum('low_stock','out_of_stock') NOT NULL,
  `current_stock` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `movement_type` enum('in','out','transfer_in','transfer_out','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_requests`
--

CREATE TABLE `stock_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `staff_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_name` varchar(50) DEFAULT NULL,
  `conversion_factor` int(11) NOT NULL DEFAULT 1,
  `base_quantity` int(11) DEFAULT NULL COMMENT 'Quantity in base unit (quantity * conversion_factor)',
  `purpose` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `received_proof_image` varchar(255) DEFAULT NULL COMMENT 'Bukti penerimaan barang',
  `received_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu barang diterima',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `staff_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `conversion_factor` int(11) NOT NULL DEFAULT 1,
  `unit` varchar(50) NOT NULL,
  `unit_price` decimal(15,2) DEFAULT NULL,
  `total_price` decimal(15,2) DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nota_number` varchar(100) DEFAULT NULL,
  `receive_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `invoice_photo` varchar(255) DEFAULT NULL,
  `status` enum('draft','pending','approved','rejected') DEFAULT 'draft',
  `is_draft` tinyint(1) DEFAULT 1,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submission_photos`
--

CREATE TABLE `submission_photos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `submission_id` bigint(20) UNSIGNED NOT NULL,
  `photo_type` enum('nota','item_condition') NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `code`, `name`, `contact_person`, `phone`, `email`, `address`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'SUP-001', 'PT Maju Jaya Teknologi', 'Pak Jaya', '08123456789', 'Majujayateknologi@gmail.com', 'jalan binawarga', 1, '2026-01-20 09:00:00', '2026-01-28 11:10:50'),
(2, 'SUP001', 'PT Supplier Utama Indonesia', 'Budi Santoso', '021-12345678', 'info@supplierutama.co.id', 'Jl. Sudirman No. 123, Jakarta Selatan', 1, '2026-01-27 09:00:00', '2026-01-28 11:10:50'),
(3, 'SUP002', 'CV Mitra Sejahtera', 'Siti Aminah', '021-87654321', 'contact@mitrasejahtera.co.id', 'Jl. Gatot Subroto No. 45, Jakarta Pusat', 1, '2026-01-27 09:00:00', '2026-01-28 11:10:50'),
(4, 'SUP003', 'PT Abadi Jaya Sentosa', 'Ahmad Fauzi', '021-55555555', 'sales@abadijaya.co.id', 'Jl. Thamrin No. 67, Jakarta Pusat', 1, '2026-01-27 09:00:00', '2026-01-28 11:10:50'),
(5, 'SUP004', 'UD Berkah Mandiri', 'Dewi Kartika', '021-66666666', 'info@berkahmandiri.co.id', 'Jl. HR Rasuna Said No. 89, Jakarta Selatan', 1, '2026-01-27 09:00:00', '2026-01-28 11:10:50'),
(6, 'SUP005', 'PT Global Trading Nusantara', 'Eko Prasetyo', '021-77777777', 'trading@globalnusantara.co.id', 'Jl. Kuningan Raya No. 12, Jakarta Selatan', 1, '2026-01-27 09:00:00', '2026-01-28 11:10:50');

-- --------------------------------------------------------

--
-- Table structure for table `transfers`
--

CREATE TABLE `transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transfer_number` varchar(255) NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `from_warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `to_warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('draft','waiting_review','waiting_approval','approved','in_transit','waiting_receive','completed','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `requested_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `requested_at` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `rejection_stage` enum('review','approval','receive') DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `unit_name` varchar(255) DEFAULT NULL,
  `conversion_factor` int(10) UNSIGNED DEFAULT 1,
  `base_quantity` int(10) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transfer_photos`
--

CREATE TABLE `transfer_photos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transfer_id` bigint(20) UNSIGNED NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  `photo_type` enum('packing','shipping','receiving') NOT NULL DEFAULT 'packing',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `pic_name` varchar(255) DEFAULT NULL,
  `pic_phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `code`, `name`, `location`, `address`, `pic_name`, `pic_phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'GD-01', 'Inspektorat II', 'Jakarta Selatan', NULL, 'Budi Santoso', '021-3456789', 1, '2026-01-01 19:00:00', '2026-01-21 19:00:00'),
(2, 'GD-02', 'Inspektorat III', 'Jakarta Selatan', NULL, 'Siti Aminah', '031-7654321', 1, '2026-01-01 19:00:00', '2026-01-21 19:00:00'),
(3, 'GD-03', 'Inspektorat IV', 'Jakarta Selatan', NULL, 'Andi Wijaya', '061-4567890', 1, '2026-01-01 19:00:00', '2026-01-21 19:00:00'),
(4, 'GD-04', 'Inspektorat V', 'Jakarta Selatan', NULL, 'Rina Wati', '0411-345678', 1, '2026-01-01 19:00:00', '2026-01-21 19:00:00'),
(5, 'GD-05', 'Bagian Umum', 'Jakarta Selatan', 'Jl. Jenderal Sudirman No. 15, Balikpapan', 'Ahmad Fauzi', '0542-123456', 1, '2026-01-01 19:00:00', '2026-01-18 19:00:00'),
(6, 'GD-06', 'Inspektorat I', 'Jakarta Selatan', NULL, 'Johanes', NULL, 1, '2026-01-12 19:00:00', '2026-01-21 19:00:00'),
(7, 'GD-07', 'Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'Jakarta Selatan', NULL, 'Heri effendi', NULL, 1, '2026-01-14 19:00:00', '2026-01-21 19:00:00'),
(8, 'GD-08', 'Kelompok Kerja Hukum Kepegawaian dan Organisasi', 'Jakarta Selatan', NULL, 'Sudahwan', NULL, 1, '2026-01-14 19:00:00', '2026-01-21 19:00:00'),
(9, 'GD-09', 'Kelompok Kerja Rencana dan Keuangan', 'Jakarta Selatan', NULL, 'Sophni', NULL, 1, '2026-01-14 19:00:00', '2026-01-21 19:00:00'),
(10, 'GD-010', 'Klinik', 'Jakarta Selatan', NULL, 'Amalinda', NULL, 1, '2026-02-10 07:39:36', '2026-02-12 01:43:05'),
(11, 'GD-011', 'Rumah Tangga', 'jakarta selatan', NULL, 'Anwar Rusdi', NULL, 1, '2026-02-11 10:32:55', '2026-02-11 10:32:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `role` enum('super_admin','admin_gudang','staff_gudang') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `role`, `phone`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Super Admin', 'superadmin@esdm.go.id', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi. Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'super_admin', '081234567890', 1, '2026-01-01 19:00:00', '2026-01-28 11:10:50', NULL),
(2, 'irsyad', 'irsyad@gmail.com', NULL, '$2y$12$Odyj01yRdFnP.7XRVpp7MO.HliNXb.JMKDqhkkrJeRg1.nZCWOaSy', NULL, 'admin_gudang', '081234567891', 1, '2026-01-01 19:00:00', '2026-01-28 11:10:50', NULL),
(4, 'staff test', 'staff_test@gmail.com', NULL, '$2y$12$ZoDwO6VcdC6m53L2ExSNou0G.rgqGeWSJjyQkQehGCaLx2hz36DPC', NULL, 'staff_gudang', NULL, 1, '2026-01-01 19:00:00', '2026-02-04 03:10:54', NULL),
(5, 'Gilbert', 'gilbert@gmail.com', NULL, '$2y$12$G9tVvpnX6NPMp82xZD.vROVfkHEfCcXQ2NSsPFKUW1jeT918ywWX2', NULL, 'staff_gudang', '081234567894', 1, '2026-01-01 19:00:00', '2026-02-08 07:51:20', NULL),
(6, 'Super Admin', 'admin@test.com', NULL, '$2y$12$vAZW/vbUjbNYjB/gNOIRgu7KXlJWGGZQgPX5NSys81Em6P2usycxC', '9tbL9weK5o2946kXGDTC1if0z7i7lD9AdzIBnLcx1irdW8ud0O1MQC3pXZYU', 'super_admin', NULL, 1, '2026-01-05 19:00:00', '2026-02-10 04:01:03', NULL),
(7, 'Super Admin', 'admin@gmail.com', NULL, '$2y$12$wPw.kxAX7Am6MVxMNEDLWegz5Ry1LVPxWV/sU8Ggi2/920QdGJS5.', NULL, 'super_admin', NULL, 1, '2026-01-06 19:00:00', '2026-01-28 11:10:50', NULL),
(9, 'rama', 'rama@gmail.com', NULL, '$2y$12$zmfyaM5x.5HF9oIU2Mfc4OCtPAp2CanzqKJwdHJ4dJtzdbYKEwds6', NULL, 'admin_gudang', NULL, 1, '2026-01-12 19:00:00', '2026-01-28 11:10:50', NULL),
(10, 'Herry Effendi', 'herry.effendi@esdm.go.id', NULL, '$2y$12$e/UxRXYxtBSfy001efR77uOwldjRQzvWpMoUwZUD27zgIHS5SNjPe', NULL, 'staff_gudang', NULL, 1, '2026-02-02 04:34:07', '2026-02-02 06:26:50', NULL),
(11, 'Hari Tri Subagyo', 'hari.subagyo@esdm.go.id', NULL, '$2y$12$a4xt8Ir.mfYff4dEmKqLYO069pUbBi3I/5c0itgkFucnoHleVyJgi', NULL, 'admin_gudang', NULL, 1, '2026-02-02 06:28:14', '2026-02-02 06:28:14', NULL),
(12, 'Laris Siregar', 'larissiregar@gmail.com', NULL, '$2y$12$YtOWYsj.hnEpUlZfRmxBY.xdR6cLc2pMw8Wf87NQ683LY5xTBi6gm', NULL, 'super_admin', NULL, 1, '2026-02-05 09:05:50', '2026-02-05 09:13:48', NULL),
(13, 'Admin Gudang', 'admingudang@gmail.com', NULL, '$2y$12$YX0NV5X/9IeYCbdWign4cO4FhclmZY2i.FbmZ8qQ8wvkfAOTUs4qm', NULL, 'admin_gudang', NULL, 1, '2026-02-08 07:51:20', '2026-02-08 07:56:20', '2026-02-08 07:56:20'),
(14, 'Amalinda', 'amalindasetyakartika@gmail.com', NULL, '$2y$12$N2GbdPPYQqCb8HMXHAUCQuPZv2m2rieaD2Bt2VipdKrDdltI6cq8m', NULL, 'staff_gudang', NULL, 1, '2026-02-10 04:01:00', '2026-02-10 04:10:23', NULL),
(15, 'Sandya Zahrannisa', 'sandya.zahrannisa@esdm.go.id', NULL, '$2y$12$Ex5uZrBacv6bLpajWv6Fe.TUmGYyyrd2nwC3rHuC8Aw./n35VnYC.', NULL, 'admin_gudang', NULL, 1, '2026-02-10 07:47:49', '2026-02-10 07:47:49', NULL),
(16, 'Anwar Rusdi', 'anwar.rusdi@esdm.go.id', NULL, '$2y$12$mFx6VlYtje0GRHtySVv4RuMeCnwZT1Rp7g1mFg/bNbP4qAbFt2EXS', NULL, 'staff_gudang', NULL, 1, '2026-02-11 10:33:30', '2026-02-11 10:33:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_warehouses`
--

CREATE TABLE `user_warehouses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_warehouses`
--

INSERT INTO `user_warehouses` (`id`, `user_id`, `warehouse_id`, `created_at`) VALUES
(4, 5, 2, '2026-01-02 02:00:00'),
(9, 5, 1, '2026-01-13 02:00:00'),
(10, 5, 4, '2026-01-13 02:00:00'),
(11, 5, 3, '2026-01-13 02:00:00'),
(12, 5, 5, '2026-01-13 02:00:00'),
(17, 5, 6, '2026-01-16 02:00:00'),
(18, 5, 7, '2026-01-16 02:00:00'),
(19, 5, 8, '2026-01-16 02:00:00'),
(20, 5, 9, '2026-01-16 02:00:00'),
(24, 10, 7, '2026-02-02 04:34:07'),
(25, 9, 7, '2026-02-02 04:34:46'),
(26, 11, 7, '2026-02-02 06:28:14'),
(28, 4, 10, '2026-02-04 01:15:10'),
(32, 15, 10, '2026-02-10 07:47:49'),
(33, 14, 10, '2026-02-10 07:47:59'),
(34, 16, 11, '2026-02-11 10:33:30');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_pending_approvals`
-- (See below for the actual view)
--
CREATE TABLE `view_pending_approvals` (
`warehouse_id` bigint(20) unsigned
,`warehouse_name` varchar(255)
,`pending_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_stock_overview`
-- (See below for the actual view)
--
CREATE TABLE `view_stock_overview` (
`id` bigint(20) unsigned
,`item_code` varchar(50)
,`item_name` varchar(255)
,`category_name` varchar(255)
,`warehouse_name` varchar(255)
,`quantity` int(11)
,`unit` varchar(50)
,`stock_status` varchar(12)
,`last_updated` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `warehouses`
-- (See below for the actual view)
--
CREATE TABLE `warehouses` (
`id` bigint(20) unsigned
,`code` varchar(50)
,`name` varchar(255)
,`location` varchar(255)
,`address` text
,`pic_name` varchar(255)
,`pic_phone` varchar(20)
,`is_active` tinyint(1)
,`created_at` timestamp
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Structure for view `view_pending_approvals`
--
DROP TABLE IF EXISTS `view_pending_approvals`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_pending_approvals`  AS SELECT `w`.`id` AS `warehouse_id`, `w`.`name` AS `warehouse_name`, count(`s`.`id`) AS `pending_count` FROM (`submissions` `s` join `warehouses` `w` on(`s`.`warehouse_id` = `w`.`id`)) WHERE `s`.`status` = 'pending' AND `s`.`is_draft` = 0 GROUP BY `w`.`id`, `w`.`name` ;

-- --------------------------------------------------------

--
-- Structure for view `view_stock_overview`
--
DROP TABLE IF EXISTS `view_stock_overview`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_stock_overview`  AS SELECT `s`.`id` AS `id`, `i`.`code` AS `item_code`, `i`.`name` AS `item_name`, `c`.`name` AS `category_name`, `w`.`name` AS `warehouse_name`, `s`.`quantity` AS `quantity`, `i`.`unit` AS `unit`, CASE WHEN `s`.`quantity` = 0 THEN 'Out of Stock' ELSE 'Normal' END AS `stock_status`, `s`.`last_updated` AS `last_updated` FROM (((`stocks` `s` join `items` `i` on(`s`.`item_id` = `i`.`id`)) join `categories` `c` on(`i`.`category_id` = `c`.`id`)) join `warehouses` `w` on(`s`.`warehouse_id` = `w`.`id`)) WHERE `i`.`is_active` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `warehouses`
--
DROP TABLE IF EXISTS `warehouses`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `warehouses`  AS SELECT `units`.`id` AS `id`, `units`.`code` AS `code`, `units`.`name` AS `name`, `units`.`location` AS `location`, `units`.`address` AS `address`, `units`.`pic_name` AS `pic_name`, `units`.`pic_phone` AS `pic_phone`, `units`.`is_active` AS `is_active`, `units`.`created_at` AS `created_at`, `units`.`updated_at` AS `updated_at` FROM `units` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `approvals`
--
ALTER TABLE `approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_submission_id` (`submission_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_action` (`action`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key_col`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`cache_key`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_category_code` (`code`),
  ADD KEY `idx_category_parent` (`parent_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_item_code` (`code`),
  ADD KEY `idx_item_category` (`category_id`);

--
-- Indexes for table `item_units`
--
ALTER TABLE `item_units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_units_item_id_foreign` (`item_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_notifications_user_id` (`user_id`),
  ADD KEY `idx_notifications_is_read` (`is_read`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_notifications_created_at` (`created_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_item_warehouse` (`item_id`,`unit_id`),
  ADD KEY `idx_item_id` (`item_id`),
  ADD KEY `idx_warehouse_id` (`unit_id`),
  ADD KEY `idx_quantity` (`quantity`),
  ADD KEY `idx_stocks_warehouse_id` (`unit_id`),
  ADD KEY `idx_stocks_item_id` (`item_id`),
  ADD KEY `idx_stocks_warehouse_item` (`unit_id`,`item_id`);

--
-- Indexes for table `stock_alerts`
--
ALTER TABLE `stock_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_id` (`item_id`),
  ADD KEY `idx_warehouse_id` (`warehouse_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_id` (`item_id`),
  ADD KEY `idx_warehouse_id` (`unit_id`),
  ADD KEY `idx_movement_type` (`movement_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_stock_movements_warehouse_id` (`unit_id`),
  ADD KEY `idx_stock_movements_item_id` (`item_id`),
  ADD KEY `idx_stock_movements_created_by` (`created_by`),
  ADD KEY `idx_stock_movements_created_at` (`created_at`),
  ADD KEY `idx_stock_movements_warehouse_date` (`unit_id`,`created_at`);

--
-- Indexes for table `stock_requests`
--
ALTER TABLE `stock_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_requests_item_id_foreign` (`item_id`),
  ADD KEY `stock_requests_staff_id_status_index` (`staff_id`,`status`),
  ADD KEY `stock_requests_warehouse_id_status_index` (`unit_id`,`status`),
  ADD KEY `stock_requests_approved_by_index` (`approved_by`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `idx_warehouse_id` (`unit_id`),
  ADD KEY `idx_staff_id` (`staff_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_is_draft` (`is_draft`),
  ADD KEY `idx_submitted_at` (`submitted_at`),
  ADD KEY `idx_submissions_staff_id` (`staff_id`),
  ADD KEY `idx_submissions_warehouse_id` (`unit_id`),
  ADD KEY `idx_submissions_status` (`status`),
  ADD KEY `idx_submissions_is_draft` (`is_draft`),
  ADD KEY `idx_submissions_warehouse_status` (`unit_id`,`status`),
  ADD KEY `idx_submissions_submitted_at` (`submitted_at`);

--
-- Indexes for table `submission_photos`
--
ALTER TABLE `submission_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_submission_id` (`submission_id`),
  ADD KEY `idx_photo_type` (`photo_type`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `transfers`
--
ALTER TABLE `transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transfers_transfer_number_unique` (`transfer_number`),
  ADD KEY `transfers_item_id_foreign` (`item_id`),
  ADD KEY `transfers_from_warehouse_id_foreign` (`from_warehouse_id`),
  ADD KEY `transfers_to_warehouse_id_foreign` (`to_warehouse_id`),
  ADD KEY `transfers_requested_by_foreign` (`requested_by`),
  ADD KEY `transfers_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `transfers_approved_by_foreign` (`approved_by`),
  ADD KEY `transfers_received_by_foreign` (`received_by`);

--
-- Indexes for table `transfer_photos`
--
ALTER TABLE `transfer_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transfer_photos_transfer_id_foreign` (`transfer_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `units_code_unique` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `user_warehouses`
--
ALTER TABLE `user_warehouses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_warehouse` (`user_id`,`warehouse_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_warehouse_id` (`warehouse_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `approvals`
--
ALTER TABLE `approvals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `item_units`
--
ALTER TABLE `item_units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `stock_alerts`
--
ALTER TABLE `stock_alerts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `stock_requests`
--
ALTER TABLE `stock_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `submission_photos`
--
ALTER TABLE `submission_photos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transfers`
--
ALTER TABLE `transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transfer_photos`
--
ALTER TABLE `transfer_photos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `user_warehouses`
--
ALTER TABLE `user_warehouses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `approvals`
--
ALTER TABLE `approvals`
  ADD CONSTRAINT `approvals_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `approvals_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `item_units`
--
ALTER TABLE `item_units`
  ADD CONSTRAINT `item_units_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `stocks_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stocks_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_alerts`
--
ALTER TABLE `stock_alerts`
  ADD CONSTRAINT `stock_alerts_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `stock_alerts_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  ADD CONSTRAINT `stock_movements_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `stock_requests`
--
ALTER TABLE `stock_requests`
  ADD CONSTRAINT `stock_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_requests_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_requests_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_requests_warehouse_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  ADD CONSTRAINT `submissions_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `submissions_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `submission_photos`
--
ALTER TABLE `submission_photos`
  ADD CONSTRAINT `submission_photos_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transfers`
--
ALTER TABLE `transfers`
  ADD CONSTRAINT `transfers_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transfers_from_warehouse_id_foreign` FOREIGN KEY (`from_warehouse_id`) REFERENCES `units` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transfers_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transfers_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transfers_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transfers_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transfers_to_warehouse_id_foreign` FOREIGN KEY (`to_warehouse_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transfer_photos`
--
ALTER TABLE `transfer_photos`
  ADD CONSTRAINT `transfer_photos_transfer_id_foreign` FOREIGN KEY (`transfer_id`) REFERENCES `transfers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_warehouses`
--
ALTER TABLE `user_warehouses`
  ADD CONSTRAINT `user_warehouses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_warehouses_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
