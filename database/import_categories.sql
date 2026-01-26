-- Script SQL untuk import data kategori hierarchical
-- Jalankan script ini setelah menjalankan migration

-- Hapus data kategori lama
TRUNCATE TABLE categories;

-- Reset AUTO_INCREMENT
ALTER TABLE categories AUTO_INCREMENT = 1;

-- Insert data kategori baru sesuai struktur hierarchical
INSERT INTO `categories` (`id`, `code`, `name`, `parent_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '1.01.03', 'ALAT/BAHAN UNTUK KEGIATAN KANTOR', NULL, NULL, 1, NOW(), NOW()),
(2, '1.01.03.01', 'ALAT TULIS KANTOR', 1, NULL, 1, NOW(), NOW()),
(3, '1.01.03.02', 'KERTAS DAN COVER', 1, NULL, 1, NOW(), NOW()),
(4, '1.01.03.04', 'BAHAN KOMPUTER', 1, NULL, 1, NOW(), NOW()),
(5, '1.01.03.06', 'ALAT LISTRIK', 1, NULL, 1, NOW(), NOW()),
(6, '1.01.03.01.001', 'Alat Tulis', 2, NULL, 1, NOW(), NOW()),
(7, '1.01.03.01.002', 'Tinta Tulis, Tinta Stempel', 2, NULL, 1, NOW(), NOW()),
(8, '1.01.03.01.003', 'Penjepit Kertas', 2, NULL, 1, NOW(), NOW()),
(9, '1.01.03.01.004', 'Penghapus/Korektor', 2, NULL, 1, NOW(), NOW()),
(10, '1.01.03.01.005', 'Buku Tulis', 2, NULL, 1, NOW(), NOW()),
(11, '1.01.03.01.006', 'Ordner Dan Map', 2, NULL, 1, NOW(), NOW()),
(12, '1.01.03.01.007', 'Penggaris', 2, NULL, 1, NOW(), NOW()),
(13, '1.01.03.01.008', 'Cutter (Alat Tulis Kantor)', 2, NULL, 1, NOW(), NOW()),
(14, '1.01.03.01.009', 'Pita Mesin Ketik', 2, NULL, 1, NOW(), NOW()),
(15, '1.01.03.01.010', 'Alat Perekat', 2, NULL, 1, NOW(), NOW()),
(16, '1.01.03.01.011', 'Stadler HD', 2, NULL, 1, NOW(), NOW()),
(17, '1.01.03.01.012', 'Staples', 2, NULL, 1, NOW(), NOW()),
(18, '1.01.03.01.013', 'Isi Staples', 2, NULL, 1, NOW(), NOW()),
(19, '1.01.03.01.014', 'Barang Cetakan', 2, NULL, 1, NOW(), NOW()),
(20, '1.01.03.01.015', 'Seminar Kit', 2, NULL, 1, NOW(), NOW()),
(21, '1.01.03.01.999', 'Alat Tulis Kantor Lainnya', 2, NULL, 1, NOW(), NOW()),
(22, '1.01.03.02.001', 'Kertas HVS', 3, NULL, 1, NOW(), NOW()),
(23, '1.01.03.02.002', 'Berbagai Kertas', 3, NULL, 1, NOW(), NOW()),
(24, '1.01.03.02.003', 'Kertas Cover', 3, NULL, 1, NOW(), NOW()),
(25, '1.01.03.02.004', 'Amplop', 3, NULL, 1, NOW(), NOW()),
(26, '1.01.03.02.005', 'Kop Surat', 3, NULL, 1, NOW(), NOW()),
(27, '1.01.03.02.999', 'Kertas Dan Cover Lainnya', 3, NULL, 1, NOW(), NOW()),
(28, '1.01.03.04.001', 'Continuous Form', 4, NULL, 1, NOW(), NOW()),
(29, '1.01.03.04.002', 'Computer File/Tempat Disket', 4, NULL, 1, NOW(), NOW()),
(30, '1.01.03.04.003', 'Pita Printer', 4, NULL, 1, NOW(), NOW()),
(31, '1.01.03.04.004', 'Tinta/Toner Printer', 4, NULL, 1, NOW(), NOW()),
(32, '1.01.03.04.005', 'Disket', 4, NULL, 1, NOW(), NOW()),
(33, '1.01.03.04.006', 'USB/Flash Disk', 4, NULL, 1, NOW(), NOW()),
(34, '1.01.03.04.007', 'Kartu Memori', 4, NULL, 1, NOW(), NOW()),
(35, '1.01.03.04.008', 'CD/DVD Drive', 4, NULL, 1, NOW(), NOW()),
(36, '1.01.03.04.009', 'Harddisk Internal', 4, NULL, 1, NOW(), NOW()),
(37, '1.01.03.04.010', 'Mouse', 4, NULL, 1, NOW(), NOW()),
(38, '1.01.03.04.011', 'CD/DVD', 4, NULL, 1, NOW(), NOW()),
(39, '1.01.03.04.999', 'Bahan Komputer Lainnya', 4, NULL, 1, NOW(), NOW()),
(40, '1.01.03.06.001', 'Kabel Listrik', 5, NULL, 1, NOW(), NOW()),
(41, '1.01.03.06.002', 'Lampu Listrik', 5, NULL, 1, NOW(), NOW()),
(42, '1.01.03.06.003', 'Stop Kontak', 5, NULL, 1, NOW(), NOW()),
(43, '1.01.03.06.004', 'Saklar', 5, NULL, 1, NOW(), NOW()),
(44, '1.01.03.06.005', 'Stacker', 5, NULL, 1, NOW(), NOW()),
(45, '1.01.03.06.006', 'Balast', 5, NULL, 1, NOW(), NOW()),
(46, '1.01.03.06.007', 'Starter', 5, NULL, 1, NOW(), NOW()),
(47, '1.01.03.06.008', 'Vitting', 5, NULL, 1, NOW(), NOW()),
(48, '1.01.03.06.009', 'Accu', 5, NULL, 1, NOW(), NOW()),
(49, '1.01.03.06.010', 'Batu Baterai', 5, NULL, 1, NOW(), NOW()),
(50, '1.01.03.06.011', 'Stavol', 5, NULL, 1, NOW(), NOW()),
(51, '1.01.03.06.999', 'Alat Listrik Lainnya', 5, NULL, 1, NOW(), NOW());
