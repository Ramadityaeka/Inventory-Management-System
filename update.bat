@echo off
echo ========================================
echo  SISTEM INVENTORY - UPDATE SCRIPT
echo ========================================
echo.
echo Script ini akan:
echo 1. Menjalankan migration database
echo 2. Import data kategori
echo 3. Clear cache Laravel
echo.
echo PASTIKAN:
echo - MySQL/MariaDB sudah running
echo - Database 'inventory_esdm' sudah ada
echo - Sudah backup database
echo.
pause

cd /d "d:\magang\projek\inventory system\inventory-system"

echo.
echo ========================================
echo  Step 1: Menjalankan Migration
echo ========================================
php artisan migrate

echo.
echo ========================================
echo  Step 2: Import Data Kategori
echo ========================================
echo Silakan import file: database/import_categories.sql
echo melalui phpMyAdmin atau MySQL command:
echo mysql -u root -p inventory_esdm < database\import_categories.sql
echo.
pause

echo.
echo ========================================
echo  Step 3: Clear Cache
echo ========================================
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload

echo.
echo ========================================
echo  SELESAI!
echo ========================================
echo.
echo Update berhasil! Silakan:
echo 1. Buka browser dan login ke sistem
echo 2. Test fitur tambah barang dengan pencarian kategori
echo 3. Test tambah sub-kategori baru
echo.
echo Dokumentasi lengkap: PANDUAN_UPDATE.md
echo.
pause
