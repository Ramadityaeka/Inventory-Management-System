-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 27, 2026 at 08:09 AM
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
-- Dumping data for table `approvals`
--

INSERT INTO `approvals` (`id`, `submission_id`, `admin_id`, `action`, `rejection_reason`, `notes`, `created_at`, `updated_at`) VALUES
(6, 8, 2, 'approved', NULL, 'Approved by admin', '2026-01-26 02:00:00', '2026-01-26 02:00:00'),
(7, 9, 2, 'approved', NULL, 'Approved by admin', '2026-01-26 02:00:00', '2026-01-26 02:00:00'),
(8, 11, 2, 'approved', NULL, 'Approved by admin', '2026-01-26 02:00:00', '2026-01-26 02:00:00'),
(9, 12, 2, 'approved', NULL, 'Approved by admin', '2026-01-27 04:14:44', '2026-01-27 04:14:44');

--
-- Triggers `approvals`
--
DELIMITER $$
CREATE TRIGGER `after_insert_approvals` AFTER INSERT ON `approvals` FOR EACH ROW BEGIN
                DECLARE v_item_id BIGINT;
                DECLARE v_warehouse_id BIGINT;
                DECLARE v_quantity INT;
                
                
                SELECT item_id, warehouse_id, quantity 
                INTO v_item_id, v_warehouse_id, v_quantity
                FROM submissions
                WHERE id = NEW.submission_id;
                
                IF NEW.action = 'approved' THEN
                    
                    UPDATE submissions 
                    SET status = 'approved'
                    WHERE id = NEW.submission_id;
                    
                    
                    INSERT INTO stocks (item_id, warehouse_id, quantity)
                    VALUES (v_item_id, v_warehouse_id, v_quantity)
                    ON DUPLICATE KEY UPDATE quantity = quantity + v_quantity;
                    
                    
                    INSERT INTO stock_movements 
                    (item_id, warehouse_id, movement_type, quantity, reference_type, reference_id, notes, created_by)
                    VALUES (
                        v_item_id, 
                        v_warehouse_id, 
                        'in', 
                        v_quantity, 
                        'submission', 
                        NEW.submission_id,
                        'Penerimaan barang approved', 
                        NEW.admin_id
                    );
                ELSE
                    
                    UPDATE submissions 
                    SET status = 'rejected'
                    WHERE id = NEW.submission_id;
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
(1, '1.01.03', 'ALAT/BAHAN UNTUK KEGIATAN KANTOR', NULL, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(2, '1.01.03.01', 'ALAT TULIS KANTOR', 1, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(3, '1.01.03.02', 'KERTAS DAN COVER', 1, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(4, '1.01.03.04', 'BAHAN KOMPUTER', 1, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(5, '1.01.03.06', 'ALAT LISTRIK', 1, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(6, '1.01.03.01.001', 'Alat Tulis', 2, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(7, '1.01.03.01.002', 'Tinta Tulis, Tinta Stempel', 2, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(8, '1.01.03.01.003', 'Penjepit Kertas', 2, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(9, '1.01.03.01.004', 'Penghapus/Korektor', 2, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(10, '1.01.03.01.005', 'Buku Tulis', 2, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(11, '1.01.03.01.006', 'Ordner Dan Map', 2, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(12, '1.01.03.01.007', 'Penggaris', 2, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(13, '1.01.03.01.008', 'Cutter (Alat Tulis Kantor)', 2, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(14, '1.01.03.01.009', 'Pita Mesin Ketik', 2, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(15, '1.01.03.01.010', 'Alat Perekat', 2, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(16, '1.01.03.01.011', 'Stadler HD', 2, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(17, '1.01.03.01.012', 'Staples', 2, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(18, '1.01.03.01.013', 'Isi Staples', 2, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(19, '1.01.03.01.014', 'Barang Cetakan', 2, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(20, '1.01.03.01.015', 'Seminar Kit', 2, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(21, '1.01.03.01.999', 'Alat Tulis Kantor Lainnya', 2, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(22, '1.01.03.02.001', 'Kertas HVS', 3, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(23, '1.01.03.02.002', 'Berbagai Kertas', 3, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(24, '1.01.03.02.003', 'Kertas Cover', 3, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(25, '1.01.03.02.004', 'Amplop', 3, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(26, '1.01.03.02.005', 'Kop Surat', 3, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(27, '1.01.03.02.999', 'Kertas Dan Cover Lainnya', 3, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(28, '1.01.03.04.001', 'Continuous Form', 4, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(29, '1.01.03.04.002', 'Computer File/Tempat Disket', 4, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(30, '1.01.03.04.003', 'Pita Printer', 4, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(31, '1.01.03.04.004', 'Tinta/Toner Printer', 4, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(32, '1.01.03.04.005', 'Disket', 4, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(33, '1.01.03.04.006', 'USB/Flash Disk', 4, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(34, '1.01.03.04.007', 'Kartu Memori', 4, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(35, '1.01.03.04.008', 'CD/DVD Drive', 4, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(36, '1.01.03.04.009', 'Harddisk Internal', 4, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(37, '1.01.03.04.010', 'Mouse', 4, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(38, '1.01.03.04.011', 'CD/DVD', 4, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(39, '1.01.03.04.999', 'Bahan Komputer Lainnya', 4, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(40, '1.01.03.06.001', 'Kabel Listrik', 5, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(41, '1.01.03.06.002', 'Lampu Listrik', 5, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(42, '1.01.03.06.003', 'Stop Kontak', 5, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(43, '1.01.03.06.004', 'Saklar', 5, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(44, '1.01.03.06.005', 'Stacker', 5, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(45, '1.01.03.06.006', 'Balast', 5, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(46, '1.01.03.06.007', 'Starter', 5, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(47, '1.01.03.06.008', 'Vitting', 5, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(48, '1.01.03.06.009', 'Accu', 5, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(49, '1.01.03.06.010', 'Batu Baterai', 5, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(50, '1.01.03.06.011', 'Stavol', 5, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00'),
(51, '1.01.03.06.999', 'Alat Listrik Lainnya', 5, NULL, 1, '2026-01-23 02:00:00', '2026-01-23 02:00:00');

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
(3, 49, '1.01.03.06.010.001', 'Batu Baterai AA', 1, 'box', NULL, 1, '2026-01-26 02:00:00', '2026-01-27 02:00:00'),
(4, 49, '1.01.03.06.010.002', 'Batu Baterai AAA', 1, 'box', NULL, 1, '2026-01-26 02:00:00', '2026-01-27 02:00:00'),
(5, 22, '1.01.03.02.001.001', 'kertas HVS A4', 4, 'Dus', NULL, 1, '2026-01-26 02:00:00', '2026-01-27 02:00:00'),
(6, 25, '1.01.03.02.004.001', 'Amplop No 90 Paperline', NULL, 'Pack', NULL, 1, '2026-01-27 04:07:44', '2026-01-27 04:07:44');

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
(28, '2026_01_23_000002_update_stock_alert_trigger', 24);

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
(1, 5, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 1, '2026-01-20 02:00:00', '2026-01-20 02:00:00', '2026-01-20 02:00:00'),
(2, 2, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari gilbert untuk Pulpen (12 box) di IRAT 2', 'stock_request', 7, 1, '2026-01-20 02:00:00', '2026-01-20 02:00:00', '2026-01-20 02:00:00'),
(3, 2, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari gilbert untuk Pulpen (12 box) di IRAT 2', 'stock_request', 7, 1, '2026-01-20 02:00:00', '2026-01-20 02:00:00', '2026-01-20 02:00:00'),
(4, 5, 'info', 'Request Penggunaan Barang Disetujui', 'Request penggunaan Pulpen sebanyak 12 telah disetujui oleh Admin Gudang.', NULL, NULL, 1, '2026-01-21 02:00:00', '2026-01-20 02:00:00', '2026-01-21 02:00:00'),
(5, 2, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari gilbert untuk Pulpen (10 box) di IRAT 2', 'stock_request', 8, 1, '2026-01-20 02:00:00', '2026-01-20 02:00:00', '2026-01-20 02:00:00'),
(6, 2, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari gilbert untuk Pulpen (10 box) di IRAT 2', 'stock_request', 8, 1, '2026-01-20 02:00:00', '2026-01-20 02:00:00', '2026-01-20 02:00:00'),
(7, 5, 'warning', 'Request Penggunaan Barang Ditolak', 'Request penggunaan Pulpen sebanyak 10 telah ditolak. Alasan: tesat', NULL, NULL, 1, '2026-01-22 02:00:00', '2026-01-21 02:00:00', '2026-01-22 02:00:00'),
(8, 2, 'password_reset', 'Password Direset', 'Password Anda telah direset oleh Super Admin. Silakan login dengan password baru dan segera menggantinya.', 'user', 6, 1, '2026-01-22 02:00:00', '2026-01-21 02:00:00', '2026-01-22 02:00:00'),
(9, 2, 'password_reset', 'Password Direset', 'Password Anda telah direset oleh Super Admin. Silakan login dengan password baru dan segera menggantinya.', 'user', 6, 1, '2026-01-22 02:00:00', '2026-01-21 02:00:00', '2026-01-22 02:00:00'),
(10, 5, 'password_reset', 'Password Direset', 'Password Anda telah direset oleh Super Admin. Silakan login dengan password baru dan segera menggantinya.', 'user', 6, 1, '2026-01-22 02:00:00', '2026-01-21 02:00:00', '2026-01-22 02:00:00'),
(11, 5, 'password_reset', 'Password Direset', 'Password Anda telah direset oleh Super Admin. Silakan login dengan password baru dan segera menggantinya.', 'user', 6, 1, '2026-01-22 02:00:00', '2026-01-21 02:00:00', '2026-01-22 02:00:00'),
(12, 5, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 1, '2026-01-26 02:00:00', '2026-01-22 02:00:00', '2026-01-26 02:00:00'),
(13, 9, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah ditugaskan ke gudang: Inspektorat III oleh Super Admin', 'user', 6, 0, NULL, '2026-01-22 02:00:00', '2026-01-22 02:00:00'),
(14, 9, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah ditugaskan ke gudang: Inspektorat III oleh Super Admin', 'user', 6, 0, NULL, '2026-01-22 02:00:00', '2026-01-22 02:00:00'),
(15, 9, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah dihapus dari gudang: Bagian Umum oleh Super Admin', 'user', 6, 0, NULL, '2026-01-22 02:00:00', '2026-01-22 02:00:00'),
(16, 9, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah dihapus dari gudang: Bagian Umum oleh Super Admin', 'user', 6, 0, NULL, '2026-01-22 02:00:00', '2026-01-22 02:00:00'),
(17, 5, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 1, '2026-01-26 02:00:00', '2026-01-22 02:00:00', '2026-01-26 02:00:00'),
(18, 2, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari gilbert untuk Pulpen (25 box) di Inspektorat II', 'stock_request', 9, 1, '2026-01-23 02:00:00', '2026-01-22 02:00:00', '2026-01-23 02:00:00'),
(19, 2, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari gilbert untuk Pulpen (25 box) di Inspektorat II', 'stock_request', 9, 1, '2026-01-23 02:00:00', '2026-01-22 02:00:00', '2026-01-23 02:00:00'),
(20, 5, 'info', 'Request Penggunaan Barang Disetujui', 'Request penggunaan Pulpen sebanyak 25 telah disetujui oleh Admin Gudang.', NULL, NULL, 1, '2026-01-26 02:00:00', '2026-01-22 02:00:00', '2026-01-26 02:00:00'),
(21, 5, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 1, '2026-01-26 02:00:00', '2026-01-23 02:00:00', '2026-01-26 02:00:00'),
(22, 5, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 1, '2026-01-26 02:00:00', '2026-01-23 02:00:00', '2026-01-26 02:00:00'),
(23, 5, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 0, NULL, '2026-01-26 02:00:00', '2026-01-26 02:00:00'),
(24, 5, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 0, NULL, '2026-01-26 02:00:00', '2026-01-26 02:00:00'),
(25, 2, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari gilbert untuk Batu Baterai AA (20 box) di Inspektorat II', 'stock_request', 10, 0, NULL, '2026-01-26 02:00:00', '2026-01-26 02:00:00'),
(26, 2, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari gilbert untuk Batu Baterai AA (20 box) di Inspektorat II', 'stock_request', 10, 0, NULL, '2026-01-26 02:00:00', '2026-01-26 02:00:00'),
(27, 4, 'password_reset', 'Password Direset', 'Password Anda telah direset oleh Super Admin. Silakan login dengan password baru dan segera menggantinya.', 'user', 6, 0, NULL, '2026-01-26 02:00:00', '2026-01-26 02:00:00'),
(28, 4, 'password_reset', 'Password Direset', 'Password Anda telah direset oleh Super Admin. Silakan login dengan password baru dan segera menggantinya.', 'user', 6, 0, NULL, '2026-01-26 02:00:00', '2026-01-26 02:00:00'),
(29, 4, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 0, NULL, '2026-01-26 02:00:00', '2026-01-26 02:00:00'),
(30, 5, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 0, NULL, '2026-01-27 04:14:44', '2026-01-27 04:14:44'),
(31, 5, 'info', 'Request Penggunaan Barang Disetujui', 'Request penggunaan Batu Baterai AA sebanyak 20 telah disetujui oleh irsyad.', NULL, NULL, 0, NULL, '2026-01-27 04:14:58', '2026-01-27 04:14:58');

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
('PtrkhyrhhAsHjxdNtn37LwvvKSUqmD53MTDB9dEg', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQW9yNzdYbFNKS2pjclR2MUpHVG1aSU4yWFJSWHhjMGlLQXd5bG5SMCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ndWRhbmcvcmVwb3J0cy9zdG9jay12YWx1ZXMiO3M6NToicm91dGUiO3M6Mjc6Imd1ZGFuZy5yZXBvcnRzLnN0b2NrLXZhbHVlcyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7fQ==', 1769497737);

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`id`, `item_id`, `warehouse_id`, `quantity`, `last_updated`, `updated_at`) VALUES
(6, 3, 1, 30, '2026-01-27 04:14:58', '2026-01-27 04:14:58'),
(7, 4, 1, 40, '2026-01-25 12:00:00', '2026-01-25 12:00:00'),
(8, 5, 1, 10, '2026-01-25 12:00:00', '2026-01-25 12:00:00'),
(9, 6, 1, 50, '2026-01-27 04:16:58', '2026-01-27 04:16:58');

--
-- Triggers `stocks`
--
DELIMITER $$
CREATE TRIGGER `after_stock_update` AFTER UPDATE ON `stocks` FOR EACH ROW BEGIN
                IF NEW.quantity = 0 THEN
                    INSERT INTO stock_alerts 
                        (item_id, warehouse_id, alert_type, current_stock)
                    VALUES 
                        (NEW.item_id, NEW.warehouse_id, 'out_of_stock', NEW.quantity)
                    ON DUPLICATE KEY UPDATE 
                        current_stock = NEW.quantity,
                        resolved_at = NULL,
                        created_at = NOW();
                END IF;
            END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_update_stocks_alert` AFTER UPDATE ON `stocks` FOR EACH ROW BEGIN
                -- Only create alert when stock reaches 0
                IF NEW.quantity = 0 AND OLD.quantity != 0 THEN
                    INSERT INTO stock_alerts 
                    (item_id, warehouse_id, alert_type, current_stock, threshold, created_at, updated_at)
                    VALUES 
                    (NEW.item_id, NEW.warehouse_id, "out_of_stock", 0, 0, NOW(), NOW());
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
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `movement_type` enum('in','out','transfer_in','transfer_out','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `item_id`, `warehouse_id`, `movement_type`, `quantity`, `reference_type`, `reference_id`, `notes`, `created_by`, `created_at`) VALUES
(13, 3, 1, 'in', 50, 'submission', 8, 'Penerimaan barang approved', 2, '2026-01-25 12:00:00'),
(14, 4, 1, 'in', 40, 'submission', 9, 'Penerimaan barang approved', 2, '2026-01-25 12:00:00'),
(15, 3, 1, 'adjustment', 10, 'manual_adjustment', 2, 'digunakan (Penambahan oleh irsyad)', 2, '2026-01-25 12:00:00'),
(16, 3, 1, 'adjustment', -10, 'manual_adjustment', 2, 'test (Pengurangan oleh irsyad)', 2, '2026-01-25 12:00:00'),
(17, 5, 1, 'in', 10, 'submission', 11, 'Penerimaan barang approved', 2, '2026-01-25 12:00:00'),
(18, 6, 1, 'in', 50, 'submission', 12, 'Penerimaan barang approved', 2, '2026-01-27 04:14:44'),
(19, 3, 1, 'out', -20, 'stock_request', 10, 'Penggunaan barang approved - digunakan', 2, '2026-01-27 04:14:58'),
(20, 6, 1, 'adjustment', 10, 'manual_adjustment', 2, 'test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test (Penambahan oleh irsyad)', 2, '2026-01-27 04:16:45'),
(21, 6, 1, 'adjustment', -10, 'manual_adjustment', 2, 'test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test test (Pengurangan oleh irsyad)', 2, '2026-01-27 04:16:58');

-- --------------------------------------------------------

--
-- Table structure for table `stock_requests`
--

CREATE TABLE `stock_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `staff_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_requests`
--

INSERT INTO `stock_requests` (`id`, `item_id`, `warehouse_id`, `staff_id`, `quantity`, `purpose`, `notes`, `status`, `approved_by`, `rejection_reason`, `approved_at`, `created_at`, `updated_at`) VALUES
(10, 3, 1, 5, 20, 'digunakan', NULL, 'approved', 2, NULL, '2026-01-27 04:14:58', '2026-01-25 12:00:00', '2026-01-27 04:14:58');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `staff_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
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

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `item_id`, `item_name`, `warehouse_id`, `staff_id`, `quantity`, `unit`, `unit_price`, `total_price`, `supplier_id`, `nota_number`, `receive_date`, `notes`, `invoice_photo`, `status`, `is_draft`, `submitted_at`, `created_at`, `updated_at`) VALUES
(8, 3, 'Batu Baterai AA', 1, 5, 50, 'box', 500000.00, 25000000.00, 1, NULL, NULL, NULL, NULL, 'approved', 0, '2026-01-25 12:00:00', '2026-01-25 12:00:00', '2026-01-25 12:00:00'),
(9, 4, 'Batu Baterai AAA', 1, 5, 40, 'box', 56000.00, 2240000.00, 1, NULL, NULL, NULL, NULL, 'approved', 0, '2026-01-25 12:00:00', '2026-01-25 12:00:00', '2026-01-25 12:00:00'),
(10, 4, 'Batu Baterai AAA', 2, 5, 20, 'box', 56000.00, 1120000.00, 1, NULL, NULL, NULL, NULL, 'pending', 0, '2026-01-25 12:00:00', '2026-01-25 12:00:00', '2026-01-25 12:00:00'),
(11, 5, 'kertas HVS A4', 1, 4, 10, 'Dus', 200000.00, 2000000.00, 1, NULL, NULL, NULL, NULL, 'approved', 0, '2026-01-25 12:00:00', '2026-01-25 12:00:00', '2026-01-25 12:00:00'),
(12, 6, 'Amplop No 90 Paperline', 1, 5, 50, 'Pack', 100000.00, 5000000.00, 4, 'NOT-12345678', '2026-01-27', NULL, 'invoice-photos/Bv6rqlYxfJx7SbGMTa9OOpHi4dFb5YZZbCUUo7zb.png', 'approved', 0, '2026-01-27 04:07:44', '2026-01-27 04:07:44', '2026-01-27 04:14:44');

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

--
-- Dumping data for table `submission_photos`
--

INSERT INTO `submission_photos` (`id`, `submission_id`, `photo_type`, `file_path`, `file_name`, `file_size`, `uploaded_at`) VALUES
(1, 12, 'nota', 'submission-photos/ZrJPRb2sArux9fwh6TRUOIorZzJCfYzwWDIMmjoO.jpg', 'WhatsApp Image 2026-01-22 at 14.01.33.jpeg', 189925, '2026-01-27 04:07:44');

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
(1, 'SUP-001', 'PT Maju Jaya Teknologi', 'Pak Jaya', '08123456789', 'Majujayateknologi@gmail.com', 'jalan binawarga', 1, '2026-01-20 02:00:00', '2026-01-20 02:00:00'),
(2, 'SUP001', 'PT Supplier Utama Indonesia', 'Budi Santoso', '021-12345678', 'info@supplierutama.co.id', 'Jl. Sudirman No. 123, Jakarta Selatan', 1, '2026-01-27 02:00:00', '2026-01-27 02:00:00'),
(3, 'SUP002', 'CV Mitra Sejahtera', 'Siti Aminah', '021-87654321', 'contact@mitrasejahtera.co.id', 'Jl. Gatot Subroto No. 45, Jakarta Pusat', 1, '2026-01-27 02:00:00', '2026-01-27 02:00:00'),
(4, 'SUP003', 'PT Abadi Jaya Sentosa', 'Ahmad Fauzi', '021-55555555', 'sales@abadijaya.co.id', 'Jl. Thamrin No. 67, Jakarta Pusat', 1, '2026-01-27 02:00:00', '2026-01-27 02:00:00'),
(5, 'SUP004', 'UD Berkah Mandiri', 'Dewi Kartika', '021-66666666', 'info@berkahmandiri.co.id', 'Jl. HR Rasuna Said No. 89, Jakarta Selatan', 1, '2026-01-27 02:00:00', '2026-01-27 02:00:00'),
(6, 'SUP005', 'PT Global Trading Nusantara', 'Eko Prasetyo', '021-77777777', 'trading@globalnusantara.co.id', 'Jl. Kuningan Raya No. 12, Jakarta Selatan', 1, '2026-01-27 02:00:00', '2026-01-27 02:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin_gudang','staff_gudang') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'superadmin@esdm.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi. Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', '081234567890', 1, '2026-01-01 12:00:00', '2026-01-01 12:00:00'),
(2, 'irsyad', 'irsyad@gmail.com', '$2y$12$Odyj01yRdFnP.7XRVpp7MO.HliNXb.JMKDqhkkrJeRg1.nZCWOaSy', 'admin_gudang', '081234567891', 1, '2026-01-01 12:00:00', '2026-01-25 12:00:00'),
(4, 'Staff Gudang 1', 'staff1@gmail.com', '$2y$12$ZoDwO6VcdC6m53L2ExSNou0G.rgqGeWSJjyQkQehGCaLx2hz36DPC', 'staff_gudang', '081234567893', 1, '2026-01-01 12:00:00', '2026-01-25 12:00:00'),
(5, 'gilbert', 'gilbert@gmail.com', '$2y$12$9WceKebLAcjHFKbwx2OZPunrwREAuz1lZe4OPqIsLfF53LN7uixLy', 'staff_gudang', '081234567894', 1, '2026-01-01 12:00:00', '2026-01-20 12:00:00'),
(6, 'Super Admin', 'admin@test.com', '$2y$12$A99fTGCgZriqKYkwj5PMQ.dIMlsp4KPrUASFr2tUlxe7Pw.o4gsJi', 'super_admin', NULL, 1, '2026-01-05 12:00:00', '2026-01-06 12:00:00'),
(7, 'Super Admin', 'admin@gmail.com', '$2y$12$wPw.kxAX7Am6MVxMNEDLWegz5Ry1LVPxWV/sU8Ggi2/920QdGJS5.', 'super_admin', NULL, 1, '2026-01-06 12:00:00', '2026-01-06 12:00:00'),
(9, 'rama', 'rama@gmail.com', '$2y$12$zmfyaM5x.5HF9oIU2Mfc4OCtPAp2CanzqKJwdHJ4dJtzdbYKEwds6', 'admin_gudang', NULL, 1, '2026-01-12 12:00:00', '2026-01-12 12:00:00');

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
(3, 4, 1, '2026-01-02 02:00:00'),
(4, 5, 2, '2026-01-02 02:00:00'),
(9, 5, 1, '2026-01-13 02:00:00'),
(10, 5, 4, '2026-01-13 02:00:00'),
(11, 5, 3, '2026-01-13 02:00:00'),
(12, 5, 5, '2026-01-13 02:00:00'),
(17, 5, 6, '2026-01-16 02:00:00'),
(18, 5, 7, '2026-01-16 02:00:00'),
(19, 5, 8, '2026-01-16 02:00:00'),
(20, 5, 9, '2026-01-16 02:00:00'),
(21, 2, 1, '2026-01-19 02:00:00'),
(23, 9, 2, '2026-01-22 02:00:00');

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
-- Stand-in structure for view `view_transfer_summary`
-- (See below for the actual view)
--
CREATE TABLE `view_transfer_summary` (
);

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
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
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `code`, `name`, `location`, `address`, `pic_name`, `pic_phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'GD-01', 'Inspektorat II', 'Jakarta Selatan', NULL, 'Budi Santoso', '021-3456789', 1, '2026-01-02 02:00:00', '2026-01-22 02:00:00'),
(2, 'GD-02', 'Inspektorat III', 'Jakarta Selatan', NULL, 'Siti Aminah', '031-7654321', 1, '2026-01-02 02:00:00', '2026-01-22 02:00:00'),
(3, 'GD-03', 'Inspektorat IV', 'Jakarta Selatan', NULL, 'Andi Wijaya', '061-4567890', 1, '2026-01-02 02:00:00', '2026-01-22 02:00:00'),
(4, 'GD-04', 'Inspektorat V', 'Jakarta Selatan', NULL, 'Rina Wati', '0411-345678', 1, '2026-01-02 02:00:00', '2026-01-22 02:00:00'),
(5, 'GD-05', 'Bagian Umum', 'Jakarta Selatan', 'Jl. Jenderal Sudirman No. 15, Balikpapan', 'Ahmad Fauzi', '0542-123456', 1, '2026-01-02 02:00:00', '2026-01-19 02:00:00'),
(6, 'GD-06', 'Inspektorat I', 'Jakarta Selatan', NULL, 'Johanes', NULL, 1, '2026-01-13 02:00:00', '2026-01-22 02:00:00'),
(7, 'GD-07', 'Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'Jakarta Selatan', NULL, 'Heri effendi', NULL, 1, '2026-01-15 02:00:00', '2026-01-22 02:00:00'),
(8, 'GD-08', 'Kelompok Kerja Hukum Kepegawaian dan Organisasi', 'Jakarta Selatan', NULL, 'Sudahwan', NULL, 1, '2026-01-15 02:00:00', '2026-01-22 02:00:00'),
(9, 'GD-09', 'Kelompok Kerja Rencana dan Keuangan', 'Jakarta Selatan', NULL, 'Sophni', NULL, 1, '2026-01-15 02:00:00', '2026-01-22 02:00:00');

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
-- Structure for view `view_transfer_summary`
--
DROP TABLE IF EXISTS `view_transfer_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_transfer_summary`  AS SELECT `w`.`id` AS `warehouse_id`, `w`.`name` AS `warehouse_name`, count(case when `t`.`status` = 'waiting_approval' then 1 end) AS `waiting_approval`, count(case when `t`.`status` = 'in_transit' then 1 end) AS `in_transit`, count(case when `t`.`status` = 'waiting_receive' then 1 end) AS `waiting_receive`, count(case when `t`.`status` = 'completed' then 1 end) AS `completed_today` FROM (`warehouses` `w` left join `transfers` `t` on((`w`.`id` = `t`.`from_warehouse_id` or `w`.`id` = `t`.`to_warehouse_id`) and cast(`t`.`requested_at` as date) = curdate())) GROUP BY `w`.`id`, `w`.`name` ;

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
  ADD UNIQUE KEY `unique_item_warehouse` (`item_id`,`warehouse_id`),
  ADD KEY `idx_item_id` (`item_id`),
  ADD KEY `idx_warehouse_id` (`warehouse_id`),
  ADD KEY `idx_quantity` (`quantity`),
  ADD KEY `idx_stocks_warehouse_id` (`warehouse_id`),
  ADD KEY `idx_stocks_item_id` (`item_id`),
  ADD KEY `idx_stocks_warehouse_item` (`warehouse_id`,`item_id`);

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
  ADD KEY `idx_warehouse_id` (`warehouse_id`),
  ADD KEY `idx_movement_type` (`movement_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_stock_movements_warehouse_id` (`warehouse_id`),
  ADD KEY `idx_stock_movements_item_id` (`item_id`),
  ADD KEY `idx_stock_movements_created_by` (`created_by`),
  ADD KEY `idx_stock_movements_created_at` (`created_at`),
  ADD KEY `idx_stock_movements_warehouse_date` (`warehouse_id`,`created_at`);

--
-- Indexes for table `stock_requests`
--
ALTER TABLE `stock_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_requests_item_id_foreign` (`item_id`),
  ADD KEY `stock_requests_staff_id_status_index` (`staff_id`,`status`),
  ADD KEY `stock_requests_warehouse_id_status_index` (`warehouse_id`,`status`),
  ADD KEY `stock_requests_approved_by_index` (`approved_by`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `idx_warehouse_id` (`warehouse_id`),
  ADD KEY `idx_staff_id` (`staff_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_is_draft` (`is_draft`),
  ADD KEY `idx_submitted_at` (`submitted_at`),
  ADD KEY `idx_submissions_staff_id` (`staff_id`),
  ADD KEY `idx_submissions_warehouse_id` (`warehouse_id`),
  ADD KEY `idx_submissions_status` (`status`),
  ADD KEY `idx_submissions_is_draft` (`is_draft`),
  ADD KEY `idx_submissions_warehouse_status` (`warehouse_id`,`status`),
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
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `approvals`
--
ALTER TABLE `approvals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `stock_alerts`
--
ALTER TABLE `stock_alerts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `stock_requests`
--
ALTER TABLE `stock_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `submission_photos`
--
ALTER TABLE `submission_photos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_warehouses`
--
ALTER TABLE `user_warehouses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `stocks_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stocks_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_alerts`
--
ALTER TABLE `stock_alerts`
  ADD CONSTRAINT `stock_alerts_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `stock_alerts_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `stock_movements_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `stock_requests`
--
ALTER TABLE `stock_requests`
  ADD CONSTRAINT `stock_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_requests_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_requests_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_requests_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `submissions_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `submissions_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `submission_photos`
--
ALTER TABLE `submission_photos`
  ADD CONSTRAINT `submission_photos_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_warehouses`
--
ALTER TABLE `user_warehouses`
  ADD CONSTRAINT `user_warehouses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_warehouses_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
