# QUICK START GUIDE

## 🚀 Langkah Cepat Update Sistem

### 1. Backup Database
```bash
# Via phpMyAdmin: Export database inventory_esdm
# Atau via command line:
mysqldump -u root -p inventory_esdm > backup_$(date +%Y%m%d).sql
```

### 2. Jalankan Update Script
```bash
# Windows:
cd "d:\magang\projek\inventory system\inventory-system"
update.bat

# Manual:
php artisan migrate
php artisan cache:clear
composer dump-autoload
```

### 3. Import Kategori
Buka phpMyAdmin → Database `inventory_esdm` → Import → Pilih file:
```
database/import_categories.sql
```

### 4. Test Fitur Baru

#### Test 1: Pencarian Kategori Dinamis
1. Login sebagai Super Admin
2. Menu **Barang** → **Tambah Barang**  
3. Di field **Kategori**, ketik: `Alat Tulis`
4. Verifikasi muncul hasil search dengan kode dan nama
5. Klik salah satu hasil untuk memilih

#### Test 2: Tambah Sub-Kategori On-the-Fly
1. Masih di form Tambah Barang
2. Ketik kategori yang ingin dijadikan parent
3. Klik tombol **+ Tambah Sub** pada hasil search
4. Modal akan terbuka dengan kode auto-generate
5. Isi nama sub-kategori baru
6. Klik **Simpan & Gunakan**
7. Kategori langsung terpilih di form

#### Test 3: Hierarki Kategori
1. Menu **Kategori**
2. Verifikasi tampilan hierarki dengan indentasi
3. Klik tombol **+ Sub** untuk tambah sub-kategori
4. Verifikasi kode otomatis di-generate

## ✅ Perubahan Utama

### Bahasa Indonesia
- ✅ Semua menu navigasi
- ✅ Label form dan tabel
- ✅ Pesan error dan sukses
- ✅ Dashboard statistik

### Warehouse → Unit
- ✅ Menu "Gudang" → "Unit"
- ✅ Label "Stok Gudang" → "Stok Unit"
- ✅ Dashboard "Total Gudang" → "Total Unit"
- ⚠️ Database migration (opsional, belum dijalankan)

### Kategori Hierarki
- ✅ Struktur parent-child dengan kode (1.01.03.01.001)
- ✅ Auto-generate kode sub-kategori
- ✅ Tampilan tree dengan indentasi
- ✅ Data kategori lengkap 51 items

### Input Barang Enhanced
- ✅ Live search kategori (AJAX)
- ✅ Tambah sub-kategori dari modal
- ✅ Auto-generate kode kategori baru
- ✅ Validasi dan error handling

## 📁 File yang Berubah

### Core Updates
- `app/Models/Category.php` - Hierarki & auto-generate
- `app/Models/User.php` - Support units()
- `app/Models/Unit.php` - Model baru
- `app/Http/Controllers/SuperAdmin/CategoryController.php` - CRUD + API
- `app/Http/Controllers/DashboardController.php` - Support Unit model

### Views Updated
- `resources/views/admin/categories/index.blade.php`
- `resources/views/admin/categories/create.blade.php`
- `resources/views/admin/items/index.blade.php`
- `resources/views/admin/items/create.blade.php`
- `resources/views/admin/warehouses/index.blade.php`
- `resources/views/layouts/navigation.blade.php`
- `resources/views/dashboard/super-admin.blade.php`

### New Files
- `database/migrations/2026_01_23_030000_update_categories_hierarchical_structure.php`
- `database/migrations/2026_01_23_030100_update_items_remove_supplier.php`
- `database/migrations/2026_01_23_040000_rename_warehouse_to_unit.php` (opsional)
- `database/import_categories.sql`
- `update.bat`

## 🔧 Troubleshooting Cepat

### Error Migration
```bash
php artisan migrate:rollback
php artisan migrate
```

### Cache Issues
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
```

### AJAX Search Tidak Jalan
1. Buka browser Console (F12)
2. Check error di Network tab
3. Verifikasi route: `php artisan route:list | grep categories`
4. Clear cache: `php artisan route:cache`

### Kategori Kosong
```sql
-- Import manual via MySQL:
mysql -u root -p inventory_esdm < database/import_categories.sql

-- Atau cek di phpMyAdmin apakah data sudah masuk
SELECT COUNT(*) FROM categories;
```

## 📞 Support

Jika ada error:
1. Screenshot error message
2. Check file `storage/logs/laravel.log`
3. Dokumentasi lengkap: `PANDUAN_UPDATE.md`

## 🎯 Fitur Yang Sudah Jalan

### ✅ Flowchart Input Barang (SESUAI PERMINTAAN)
```
Mulai Input Barang
    ↓
Ketik 'Alat Tulis' → Sistem Tampilkan: 1.01.03.01.001
    ↓
Kode Ada? 
    → YA: Pilih Kategori
    → TIDAK: Buat Sub-Kategori
        → Pilih Induk
        → Generate Next ID (.016)
        → Simpan
    ↓
Input Detail Barang (Nama, Merk, Satuan)
    ↓
Simpan ke Database
```

**Status: ✅ SUDAH DIIMPLEMENTASIKAN LENGKAP!**
