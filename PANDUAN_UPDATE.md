# Panduan Update Sistem Inventory

## 📋 Ringkasan Perubahan

1. ✅ **Struktur Kategori Hierarki** - Kategori sekarang mendukung parent-child dengan kode seperti `1.01.03.01.001`
2. ✅ **Auto-generate Kode Kategori** - Sistem otomatis membuat kode untuk sub-kategori baru
3. ✅ **Pencarian Kategori Dinamis** - Saat input barang, bisa cari dan tambah kategori baru
4. ✅ **Bahasa Indonesia** - Semua label dan pesan sudah dalam Bahasa Indonesia
5. ⏳ **Warehouse → Unit** - Perubahan nama "Gudang/Warehouse" menjadi "Unit"

## 🚀 Langkah Instalasi

### 1. Backup Database Terlebih Dahulu
```sql
-- Backup database melalui phpMyAdmin atau command line
mysqldump -u root -p inventory_esdm > backup_before_update.sql
```

### 2. Jalankan Migration
```bash
cd "d:\magang\projek\inventory system\inventory-system"
php artisan migrate
```

Migration yang akan dijalankan:
- `2026_01_23_030000_update_categories_hierarchical_structure.php` - Update struktur kategori
- `2026_01_23_030100_update_items_remove_supplier.php` - Update struktur items
- `2026_01_23_040000_rename_warehouse_to_unit.php` - Rename warehouse ke unit (OPSIONAL)

### 3. Import Data Kategori
Jalankan SQL script untuk mengisi data kategori:
```sql
-- Buka file: database/import_categories.sql di phpMyAdmin
-- Atau jalankan via command line:
mysql -u root -p inventory_esdm < database/import_categories.sql
```

### 4. Update Composer (jika diperlukan)
```bash
composer dump-autoload
```

### 5. Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 📁 File yang Sudah Diubah

### Models
- ✅ `app/Models/Category.php` - Tambah relasi parent/child dan method generate kode
- ✅ `app/Models/User.php` - Tambah relasi units()
- ✅ `app/Models/Unit.php` - Model baru (copy dari Warehouse)

### Controllers
- ✅ `app/Http/Controllers/SuperAdmin/CategoryController.php` - Tambah fitur hierarki & API

### Views - Kategori
- ✅ `resources/views/admin/categories/index.blade.php` - Tampil hierarki
- ✅ `resources/views/admin/categories/create.blade.php` - Form dengan parent select & auto-generate
- `resources/views/admin/categories/edit.blade.php` - Perlu update (lihat create.blade.php)

### Views - Items
- ✅ `resources/views/admin/items/create.blade.php` - Pencarian kategori dinamis + modal tambah sub-kategori

### Routes
- ✅ `routes/web.php` - Tambah route API categories/search & generate-code

### Migrations
- ✅ `database/migrations/2026_01_23_030000_update_categories_hierarchical_structure.php`
- ✅ `database/migrations/2026_01_23_030100_update_items_remove_supplier.php`
- ✅ `database/migrations/2026_01_23_040000_rename_warehouse_to_unit.php`

### SQL Import
- ✅ `database/import_categories.sql` - Data kategori lengkap

## 🔧 Fitur Baru

### 1. Input Barang dengan Pencarian Kategori

Flowchart yang sudah diimplementasikan:
```
┌─────────────────────┐
│  Mulai Input Barang │
└──────────┬──────────┘
           │
           ▼
    ┌──────────────┐
    │ Cari Kategori│ (Ketik nama/kode)
    └──────┬───────┘
           │
           ▼
    ┌──────────────────────────┐
    │ Apakah Kategori Ditemukan?│
    └─────┬────────────────┬───┘
         YA               TIDAK
          │                 │
          ▼                 ▼
   ┌──────────────┐   ┌─────────────────┐
   │Pilih Kategori│   │Buat Sub-Kategori│
   └──────┬───────┘   └────────┬────────┘
          │                     │
          │            ┌────────▼────────┐
          │            │Pilih Induk      │
          │            └────────┬────────┘
          │                     │
          │            ┌────────▼────────┐
          │            │Generate Kode    │
          │            │(Auto: .016)     │
          │            └────────┬────────┘
          │                     │
          │            ┌────────▼────────┐
          │            │Simpan Kategori  │
          │            └────────┬────────┘
          └──────────────┬──────┘
                         │
                         ▼
                ┌────────────────┐
                │Input Detail    │
                │(Nama, Merk,    │
                │Satuan, dll)    │
                └───────┬────────┘
                        │
                        ▼
                ┌───────────────┐
                │Simpan Barang  │
                └───────────────┘
```

**Cara Penggunaan:**
1. Masuk ke halaman Tambah Barang
2. Di field "Kategori", ketik nama atau kode (misal: "Alat Tulis")
3. Sistem akan menampilkan hasil pencarian
4. **Opsi 1:** Klik kategori untuk memilih
5. **Opsi 2:** Klik tombol "+ Tambah Sub" untuk membuat sub-kategori baru
6. Jika buat sub-kategori:
   - Modal akan terbuka
   - Kode otomatis di-generate (misal: `1.01.03.01.016`)
   - Isi nama sub-kategori
   - Klik "Simpan & Gunakan"
7. Lanjutkan mengisi detail barang lainnya

### 2. Manajemen Kategori Hierarki

**Tampilan Index:**
- Kategori ditampilkan dengan indentasi sesuai level
- Ada tombol "+ Sub" untuk menambah sub-kategori langsung
- Kode kategori ditampilkan dalam format `code`

**Tambah Kategori:**
- Pilih kategori induk (opsional)
- Jika ada induk, kode otomatis di-generate
- Jika kategori root, harus input kode manual (misal: `1.01.03`)

## ⚠️ Perubahan Warehouse → Unit (OPSIONAL)

Migration sudah dibuat tapi **BELUM DIJALANKAN** karena mempengaruhi banyak file.

### Jika Ingin Menjalankan Perubahan ini:

1. **Jalankan migration:**
```bash
php artisan migrate
# Migration akan otomatis rename tabel warehouses -> units
# dan kolom warehouse_id -> unit_id di semua tabel terkait
```

2. **Update semua Controller yang menggunakan Warehouse:**
```php
// SEBELUM:
use App\Models\Warehouse;
$warehouses = Warehouse::all();
$user->warehouses

// SESUDAH:
use App\Models\Unit;
$units = Unit::all();
$user->units
```

3. **Update semua View:**
- Ganti semua teks "Gudang" / "Warehouse" menjadi "Unit"
- Ganti variabel `$warehouses` menjadi `$units`
- Ganti route `admin.warehouses.*` menjadi `admin.units.*`

### File yang Perlu Diubah (Jika Pakai Unit):
- Controllers: `WarehouseController`, `StockController`, `SubmissionController`, dll
- Views: semua view di folder `admin/warehouses/`, `gudang/`, `staff/`
- Routes: `routes/web.php`
- Models: `Stock`, `Submission`, `StockRequest`, `Transfer`, `StockMovement`

## 📝 Testing

### 1. Test Tambah Kategori
```
1. Login sebagai Super Admin
2. Masuk ke "Kategori"
3. Klik "Tambah Kategori"
4. Pilih induk: "1.01.03.01 - ALAT TULIS KANTOR"
5. Klik tombol "Auto" untuk generate kode
6. Isi nama: "Spidol Permanen"
7. Simpan
8. Verifikasi kode otomatis: 1.01.03.01.016 (atau nomor berikutnya)
```

### 2. Test Input Barang dengan Pencarian
```
1. Masuk ke "Barang" > "Tambah Barang"
2. Di field Kategori, ketik: "Alat Tulis"
3. Verifikasi muncul hasil: "1.01.03.01.001 - Alat Tulis"
4. Klik tombol "+ Tambah Sub"
5. Verifikasi kode auto-generate: 1.01.03.01.017
6. Isi nama sub: "Spidol Warna"
7. Klik "Simpan & Gunakan"
8. Verifikasi kategori terpilih
9. Lengkapi form barang dan simpan
```

### 3. Test Hierarki Kategori
```
1. Buka halaman "Kategori"
2. Verifikasi tampilan indentasi sesuai level
3. Klik tombol "+ Sub" pada kategori tertentu
4. Verifikasi kode otomatis di-generate
```

## 🐛 Troubleshooting

### Error: "Class 'App\Models\Unit' not found"
**Solusi:** Jalankan `composer dump-autoload`

### Error: "SQLSTATE[42S02]: Base table or view not found: 'units'"
**Solusi:** Migration warehouse->unit belum dijalankan. Gunakan `warehouses` dulu atau jalankan migration.

### Error: "Call to undefined method generateNextSubCategoryCode()"
**Solusi:** Clear cache dengan `php artisan cache:clear` dan `composer dump-autoload`

### Kategori tidak muncul di dropdown
**Solusi:** Import data kategori dengan menjalankan `database/import_categories.sql`

### AJAX search tidak bekerja
**Solusi:** 
1. Cek di browser console (F12) untuk error JavaScript
2. Verifikasi route `admin.categories.search` ada di `routes/web.php`
3. Jalankan `php artisan route:cache`

## 📞 Kontak

Jika ada masalah, silakan dokumentasikan:
1. Error message lengkap
2. File yang bermasalah
3. Step yang dilakukan sebelum error
4. Screenshot jika perlu
