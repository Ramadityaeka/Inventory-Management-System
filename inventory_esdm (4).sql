-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2026 at 02:39 AM
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
(1, 1, 2, 'approved', NULL, 'Approved by admin', '2026-01-19 20:31:31', '2026-01-19 20:31:31'),
(2, 2, 2, 'approved', NULL, 'Approved by admin', '2026-01-21 23:15:10', '2026-01-21 23:15:10'),
(3, 3, 9, 'approved', NULL, 'Approved by admin', '2026-01-21 23:22:30', '2026-01-21 23:22:30');

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
  `name` varchar(255) NOT NULL,
  `code_prefix` varchar(5) DEFAULT NULL,
  `prefix` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `code_prefix`, `prefix`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'ATK', 'ATK', NULL, NULL, 1, '2026-01-19 20:25:41', '2026-01-19 20:25:41'),
(2, 'Elektronik', 'ETK', NULL, NULL, 1, '2026-01-21 23:19:20', '2026-01-21 23:19:20');

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
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `unit` varchar(50) NOT NULL,
  `min_threshold` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `inactive_reason` enum('discontinued','wrong_input','seasonal') DEFAULT NULL,
  `inactive_notes` text DEFAULT NULL,
  `deactivated_at` timestamp NULL DEFAULT NULL,
  `deactivated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `replaced_by_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `code`, `name`, `category_id`, `supplier_id`, `unit`, `min_threshold`, `description`, `is_active`, `inactive_reason`, `inactive_notes`, `deactivated_at`, `deactivated_by`, `replaced_by_item_id`, `created_at`, `updated_at`) VALUES
(1, 'ATK-2026-001', 'Pulpen', 1, 1, 'box', 10, NULL, 1, NULL, NULL, NULL, NULL, NULL, '2026-01-19 20:28:36', '2026-01-20 04:01:46'),
(2, 'ETK-2026-001', 'Monitor', 2, 1, 'unit', 10, NULL, 1, NULL, NULL, NULL, NULL, NULL, '2026-01-21 23:19:49', '2026-01-21 23:19:49');

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
(26, '2026_01_20_111816_create_transfers_table', 22);

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
(1, 5, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 1, '2026-01-20 03:52:54', '2026-01-19 20:31:31', '2026-01-20 03:52:54'),
(2, 2, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari gilbert untuk Pulpen (12 box) di IRAT 2', 'stock_request', 7, 1, '2026-01-20 09:24:46', '2026-01-20 09:24:09', '2026-01-20 09:24:46'),
(3, 2, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari gilbert untuk Pulpen (12 box) di IRAT 2', 'stock_request', 7, 1, '2026-01-20 09:24:49', '2026-01-20 09:24:09', '2026-01-20 09:24:49'),
(4, 5, 'info', 'Request Penggunaan Barang Disetujui', 'Request penggunaan Pulpen sebanyak 12 telah disetujui oleh Admin Gudang.', NULL, NULL, 1, '2026-01-21 05:04:15', '2026-01-20 09:24:58', '2026-01-21 05:04:15'),
(5, 2, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari gilbert untuk Pulpen (10 box) di IRAT 2', 'stock_request', 8, 1, '2026-01-20 09:29:09', '2026-01-20 09:26:06', '2026-01-20 09:29:09'),
(6, 2, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari gilbert untuk Pulpen (10 box) di IRAT 2', 'stock_request', 8, 1, '2026-01-20 09:29:08', '2026-01-20 09:26:06', '2026-01-20 09:29:08'),
(7, 5, 'warning', 'Request Penggunaan Barang Ditolak', 'Request penggunaan Pulpen sebanyak 10 telah ditolak. Alasan: tesat', NULL, NULL, 1, '2026-01-21 18:38:22', '2026-01-21 05:05:40', '2026-01-21 18:38:22'),
(8, 2, 'password_reset', 'Password Direset', 'Password Anda telah direset oleh Super Admin. Silakan login dengan password baru dan segera menggantinya.', 'user', 6, 1, '2026-01-21 18:37:58', '2026-01-20 23:46:44', '2026-01-21 18:37:58'),
(9, 2, 'password_reset', 'Password Direset', 'Password Anda telah direset oleh Super Admin. Silakan login dengan password baru dan segera menggantinya.', 'user', 6, 1, '2026-01-21 18:37:54', '2026-01-20 23:46:44', '2026-01-21 18:37:54'),
(10, 5, 'password_reset', 'Password Direset', 'Password Anda telah direset oleh Super Admin. Silakan login dengan password baru dan segera menggantinya.', 'user', 6, 1, '2026-01-21 18:38:27', '2026-01-20 23:46:52', '2026-01-21 18:38:27'),
(11, 5, 'password_reset', 'Password Direset', 'Password Anda telah direset oleh Super Admin. Silakan login dengan password baru dan segera menggantinya.', 'user', 6, 1, '2026-01-21 18:38:29', '2026-01-20 23:46:52', '2026-01-21 18:38:29'),
(12, 5, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 0, NULL, '2026-01-21 23:15:10', '2026-01-21 23:15:10'),
(13, 9, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah ditugaskan ke gudang: Inspektorat III oleh Super Admin', 'user', 6, 0, NULL, '2026-01-21 23:21:43', '2026-01-21 23:21:43'),
(14, 9, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah ditugaskan ke gudang: Inspektorat III oleh Super Admin', 'user', 6, 0, NULL, '2026-01-21 23:21:44', '2026-01-21 23:21:44'),
(15, 9, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah dihapus dari gudang: Bagian Umum oleh Super Admin', 'user', 6, 0, NULL, '2026-01-21 23:21:44', '2026-01-21 23:21:44'),
(16, 9, 'warehouse_assignment', 'Penugasan Gudang', 'Anda telah dihapus dari gudang: Bagian Umum oleh Super Admin', 'user', 6, 0, NULL, '2026-01-21 23:21:44', '2026-01-21 23:21:44'),
(17, 5, 'submission_approved', 'Submission Approved', 'Submission Anda telah diapprove oleh admin gudang.', NULL, NULL, 0, NULL, '2026-01-21 23:22:30', '2026-01-21 23:22:30'),
(18, 2, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari gilbert untuk Pulpen (25 box) di Inspektorat II', 'stock_request', 9, 0, NULL, '2026-01-21 23:35:00', '2026-01-21 23:35:00'),
(19, 2, 'new_stock_request', 'Permintaan Barang Keluar Baru', 'Permintaan barang keluar dari gilbert untuk Pulpen (25 box) di Inspektorat II', 'stock_request', 9, 0, NULL, '2026-01-21 23:35:00', '2026-01-21 23:35:00'),
(20, 5, 'info', 'Request Penggunaan Barang Disetujui', 'Request penggunaan Pulpen sebanyak 25 telah disetujui oleh Admin Gudang.', NULL, NULL, 0, NULL, '2026-01-21 23:35:43', '2026-01-21 23:35:43');

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
('45Zy2nT8FLNd2LZ0FDLaKuMd8gDIqIpP2vWolRIb', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYlY0eW0xMEdOQ1lwYURZN1lNcW1PQ3pXdEp6ejdmbmtKSjVzM2szOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ndWRhbmcvc3RvY2stcmVxdWVzdHMvOSI7czo1OiJyb3V0ZSI7czoyNjoiZ3VkYW5nLnN0b2NrLXJlcXVlc3RzLnNob3ciO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO30=', 1769065653),
('C2z1XRCM1a8MUycDl8XBwrftGEcphhbSB95xTeux', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiOFNuWHZCWmZaQkpydjhHdVRaVFhDOEVnbDRIemxkcGw1NU1oVW9CWSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0NToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2d1ZGFuZy9zdG9jay1yZXF1ZXN0cy85Ijt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi93YXJlaG91c2VzIjtzOjU6InJvdXRlIjtzOjIyOiJhZG1pbi53YXJlaG91c2VzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Njt9', 1769131165);

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
(1, 1, 1, 65, '2026-01-21 23:54:06', '2026-01-21 23:54:06'),
(3, 2, 2, 5, '2026-01-22 06:22:30', '2026-01-22 06:22:30');

--
-- Triggers `stocks`
--
DELIMITER $$
CREATE TRIGGER `after_update_stocks_alert` AFTER UPDATE ON `stocks` FOR EACH ROW BEGIN
    IF NEW.quantity <= (SELECT min_threshold FROM items WHERE id = NEW.item_id) AND NEW.quantity > 0 THEN
        INSERT INTO stock_alerts 
        (item_id, warehouse_id, alert_type, current_stock, threshold)
        VALUES 
        (NEW.item_id, NEW.warehouse_id, 'low_stock', NEW.quantity, 
         (SELECT min_threshold FROM items WHERE id = NEW.item_id));
        
    ELSEIF NEW.quantity = 0 THEN
        INSERT INTO stock_alerts 
        (item_id, warehouse_id, alert_type, current_stock, threshold)
        VALUES 
        (NEW. item_id, NEW.warehouse_id, 'out_of_stock', 0, 
         (SELECT min_threshold FROM items WHERE id = NEW.item_id));
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
  `threshold` int(11) NOT NULL,
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
(1, 1, 1, 'in', 50, 'submission', 1, 'Penerimaan barang approved', 2, '2026-01-20 03:31:31'),
(2, 1, 1, 'adjustment', 10, 'manual_adjustment', 2, 'kurang (Penambahan oleh Admin Gudang)', 2, '2026-01-20 03:44:54'),
(3, 1, 1, 'adjustment', -10, 'manual_adjustment', 2, 'digunakan (Pengurangan oleh Admin Gudang)', 2, '2026-01-20 03:47:04'),
(4, 1, 1, 'adjustment', 12, 'manual_addition', 2, 'test (Penambahan manual oleh Admin Gudang)', 2, '2026-01-20 08:11:14'),
(5, 1, 1, 'out', -12, 'stock_request', 7, 'Penggunaan barang approved - digunakan', 2, '2026-01-20 09:24:58'),
(6, 1, 1, 'adjustment', 10, 'manual_adjustment', 2, 'salah input (Penambahan oleh Admin Gudang)', 2, '2026-01-22 03:30:27'),
(7, 1, 1, 'in', 10, 'submission', 2, 'Penerimaan barang approved', 2, '2026-01-22 06:15:10'),
(8, 2, 2, 'in', 5, 'submission', 3, 'Penerimaan barang approved', 9, '2026-01-22 06:22:30'),
(9, 1, 1, 'out', -25, 'stock_request', 9, 'Penggunaan barang approved - digunakan mas bagus', 2, '2026-01-22 06:35:43'),
(10, 1, 1, 'adjustment', 20, 'manual_adjustment', 2, 'salah input (Penambahan oleh Admin Gudang)', 2, '2026-01-22 06:54:06');

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
(7, 1, 1, 5, 12, 'digunakan', NULL, 'approved', 2, NULL, '2026-01-20 09:24:58', '2026-01-20 09:24:09', '2026-01-20 09:24:58'),
(8, 1, 1, 5, 10, 'digunakan', NULL, 'rejected', 2, 'tesat', '2026-01-21 05:05:40', '2026-01-20 09:26:06', '2026-01-21 05:05:40'),
(9, 1, 1, 5, 25, 'digunakan mas bagus', NULL, 'approved', 2, NULL, '2026-01-21 23:35:43', '2026-01-21 23:35:00', '2026-01-21 23:35:43');

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
(1, 1, 'Pulpen', 1, 5, 50, 'box', 10000.00, 500000.00, 1, NULL, NULL, NULL, NULL, 'approved', 0, '2026-01-19 20:30:52', '2026-01-19 20:30:52', '2026-01-19 20:31:31'),
(2, 1, 'Pulpen', 1, 5, 10, 'box', 10000.00, 100000.00, 1, NULL, NULL, NULL, NULL, 'approved', 0, '2026-01-21 23:13:52', '2026-01-21 23:13:52', '2026-01-21 23:15:10'),
(3, 2, 'Monitor', 2, 5, 5, 'unit', 5000000.00, 25000000.00, 1, NULL, NULL, NULL, NULL, 'approved', 0, '2026-01-21 23:20:41', '2026-01-21 23:20:41', '2026-01-21 23:22:30'),
(4, NULL, 'amplop NO 90 paperline', 1, 5, 1, 'pak', 10000.00, 10000.00, 1, NULL, NULL, NULL, NULL, 'pending', 0, '2026-01-21 23:33:29', '2026-01-21 23:33:29', '2026-01-21 23:33:29'),
(5, NULL, 'kertas HVS A4', 1, 5, 5, 'box', 72900.00, 364500.00, 1, NULL, NULL, NULL, NULL, 'pending', 0, '2026-01-21 23:34:16', '2026-01-21 23:34:16', '2026-01-21 23:34:16');

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
(1, 'SUP-001', 'PT Maju Jaya Teknologi', 'Pak Jaya', '08123456789', 'Majujayateknologi@gmail.com', 'jalan binawarga', 1, '2026-01-19 20:27:38', '2026-01-19 20:27:38');

-- --------------------------------------------------------

--
-- Table structure for table `transfers`
--

CREATE TABLE `transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transfer_number` varchar(50) DEFAULT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `from_warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `to_warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` text NOT NULL,
  `requested_by` bigint(20) UNSIGNED NOT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('draft','waiting_review','waiting_approval','approved','in_transit','waiting_receive','completed','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `rejection_stage` enum('gudang_asal','super_admin','gudang_tujuan') DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `shipping_note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 'Super Admin', 'superadmin@esdm.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi. Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', '081234567890', 1, '2026-01-02 07:29:57', '2026-01-02 07:29:57'),
(2, 'Admin Gudang', 'admingudang@gmail.com', '$2y$12$Odyj01yRdFnP.7XRVpp7MO.HliNXb.JMKDqhkkrJeRg1.nZCWOaSy', 'admin_gudang', '081234567891', 1, '2026-01-02 07:29:57', '2026-01-20 23:46:44'),
(4, 'Staff Gudang 1', 'staff1@gmail.com', '$2y$12$c/vJ5f3PUFrzEJE27xN3DuBRh3AFtfpfe7fQhckldw17ZP8eR93TW', 'staff_gudang', '081234567893', 1, '2026-01-02 07:29:57', '2026-01-06 20:27:41'),
(5, 'gilbert', 'gilbert@gmail.com', '$2y$12$9WceKebLAcjHFKbwx2OZPunrwREAuz1lZe4OPqIsLfF53LN7uixLy', 'staff_gudang', '081234567894', 1, '2026-01-02 07:29:57', '2026-01-20 23:46:52'),
(6, 'Super Admin', 'admin@test.com', '$2y$12$A99fTGCgZriqKYkwj5PMQ.dIMlsp4KPrUASFr2tUlxe7Pw.o4gsJi', 'super_admin', NULL, 1, '2026-01-05 19:31:29', '2026-01-06 20:19:42'),
(7, 'Super Admin', 'admin@gmail.com', '$2y$12$wPw.kxAX7Am6MVxMNEDLWegz5Ry1LVPxWV/sU8Ggi2/920QdGJS5.', 'super_admin', NULL, 1, '2026-01-06 20:19:22', '2026-01-06 20:19:22'),
(9, 'rama', 'rama@gmail.com', '$2y$12$zmfyaM5x.5HF9oIU2Mfc4OCtPAp2CanzqKJwdHJ4dJtzdbYKEwds6', 'admin_gudang', NULL, 1, '2026-01-12 19:03:27', '2026-01-12 19:03:27');

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
(3, 4, 1, '2026-01-02 07:29:57'),
(4, 5, 2, '2026-01-02 07:29:57'),
(9, 5, 1, '2026-01-12 18:51:33'),
(10, 5, 4, '2026-01-12 18:51:33'),
(11, 5, 3, '2026-01-12 18:52:04'),
(12, 5, 5, '2026-01-12 18:52:04'),
(17, 5, 6, '2026-01-16 02:09:47'),
(18, 5, 7, '2026-01-16 02:09:47'),
(19, 5, 8, '2026-01-16 02:09:47'),
(20, 5, 9, '2026-01-16 02:09:47'),
(21, 2, 1, '2026-01-18 18:23:37'),
(23, 9, 2, '2026-01-21 23:21:43');

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
,`min_threshold` int(11)
,`stock_status` varchar(12)
,`last_updated` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_transfer_summary`
-- (See below for the actual view)
--
CREATE TABLE `view_transfer_summary` (
`warehouse_id` bigint(20) unsigned
,`warehouse_name` varchar(255)
,`waiting_approval` bigint(21)
,`in_transit` bigint(21)
,`waiting_receive` bigint(21)
,`completed_today` bigint(21)
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
(1, 'GD-01', 'Inspektorat II', 'Jakarta Selatan', NULL, 'Budi Santoso', '021-3456789', 1, '2026-01-02 07:29:57', '2026-01-21 20:24:51'),
(2, 'GD-02', 'Inspektorat III', 'Jakarta Selatan', NULL, 'Siti Aminah', '031-7654321', 1, '2026-01-02 07:29:57', '2026-01-21 20:25:09'),
(3, 'GD-03', 'Inspektorat IV', 'Jakarta Selatan', NULL, 'Andi Wijaya', '061-4567890', 1, '2026-01-02 07:29:57', '2026-01-21 20:25:24'),
(4, 'GD-04', 'Inspektorat V', 'Jakarta Selatan', NULL, 'Rina Wati', '0411-345678', 1, '2026-01-02 07:29:57', '2026-01-21 20:25:35'),
(5, 'GD-05', 'Bagian Umum', 'Jakarta Selatan', 'Jl. Jenderal Sudirman No. 15, Balikpapan', 'Ahmad Fauzi', '0542-123456', 1, '2026-01-02 07:29:57', '2026-01-18 18:31:38'),
(6, 'GD-06', 'Inspektorat I', 'Jakarta Selatan', NULL, 'Johanes', NULL, 1, '2026-01-13 00:36:11', '2026-01-21 20:24:27'),
(7, 'GD-07', 'Bagian Pengelolaan Tindak Lanjut Hasil Pengawasan dan Kepatuhan Internal', 'Jakarta Selatan', NULL, 'Heri effendi', NULL, 1, '2026-01-15 02:12:02', '2026-01-21 20:26:16'),
(8, 'GD-08', 'Kelompok Kerja Hukum Kepegawaian dan Organisasi', 'Jakarta Selatan', NULL, 'Sudahwan', NULL, 1, '2026-01-15 02:12:29', '2026-01-21 20:23:27'),
(9, 'GD-09', 'Kelompok Kerja Rencana dan Keuangan', 'Jakarta Selatan', NULL, 'Sophni', NULL, 1, '2026-01-15 02:13:26', '2026-01-21 20:23:59');

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

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_stock_overview`  AS SELECT `s`.`id` AS `id`, `i`.`code` AS `item_code`, `i`.`name` AS `item_name`, `c`.`name` AS `category_name`, `w`.`name` AS `warehouse_name`, `s`.`quantity` AS `quantity`, `i`.`unit` AS `unit`, `i`.`min_threshold` AS `min_threshold`, CASE WHEN `s`.`quantity` = 0 THEN 'Out of Stock' WHEN `s`.`quantity` <= `i`.`min_threshold` THEN 'Low Stock' ELSE 'Normal' END AS `stock_status`, `s`.`last_updated` AS `last_updated` FROM (((`stocks` `s` join `items` `i` on(`s`.`item_id` = `i`.`id`)) join `categories` `c` on(`i`.`category_id` = `c`.`id`)) join `warehouses` `w` on(`s`.`warehouse_id` = `w`.`id`)) WHERE `i`.`is_active` = 1 ;

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
  ADD KEY `idx_name` (`name`);

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
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_items_category_id` (`category_id`),
  ADD KEY `idx_items_supplier_id` (`supplier_id`),
  ADD KEY `idx_items_min_threshold` (`min_threshold`),
  ADD KEY `items_deactivated_by_foreign` (`deactivated_by`),
  ADD KEY `items_replaced_by_item_id_foreign` (`replaced_by_item_id`);

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
-- Indexes for table `transfers`
--
ALTER TABLE `transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transfers_item_id_foreign` (`item_id`),
  ADD KEY `transfers_from_warehouse_id_foreign` (`from_warehouse_id`),
  ADD KEY `transfers_to_warehouse_id_foreign` (`to_warehouse_id`),
  ADD KEY `transfers_requested_by_foreign` (`requested_by`),
  ADD KEY `transfers_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `transfers_approved_by_foreign` (`approved_by`),
  ADD KEY `transfers_received_by_foreign` (`received_by`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `submission_photos`
--
ALTER TABLE `submission_photos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transfers`
--
ALTER TABLE `transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_deactivated_by_foreign` FOREIGN KEY (`deactivated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `items_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `items_replaced_by_item_id_foreign` FOREIGN KEY (`replaced_by_item_id`) REFERENCES `items` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `transfers`
--
ALTER TABLE `transfers`
  ADD CONSTRAINT `transfers_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transfers_from_warehouse_id_foreign` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transfers_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transfers_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transfers_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transfers_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transfers_to_warehouse_id_foreign` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

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
