# 📦 Inventory Management System (IMS) dengan Approval System

Sistem Manajemen Inventori berbasis web yang dilengkapi dengan sistem approval bertingkat, multi-warehouse management, dan role-based access control.

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap">
</p>

## ✨ Fitur Utama

### 🔐 Multi-Role Authentication System
- **Super Admin**: Manajemen penuh sistem, user management,
- **Admin Gudang**: Kelola stock, submission
- **Staff**: Pengajuan barang, tracking submission status

### 📊 Stock & Warehouse Management
- Multi-warehouse inventory tracking
- Real-time stock monitoring
- Stock alert & low stock notifications
- Comprehensive stock movement history
- Item categorization & supplier management

### 📝 Approval Workflow System
- Multi-level approval untuk submission barang
- Transfer approval dengan review system
- Tracking status real-time
- Rejection dengan reason tracking
- Email/in-app notifications

### 📈 Reporting & Analytics
- Stock overview & summary reports
- Monthly warehouse reports
- Transfer summary & history
- Export to Excel & PDF
- Audit log untuk semua aktivitas

### 🔔 Notification System
- Real-time in-app notifications
- Approval request alerts
- Stock alert notifications
- Transfer status updates

## 🛠️ Teknologi Stack

- **Framework**: Laravel 11
- **PHP**: 8.2+
- **Database**: MySQL 8.0
- **Frontend**: Bootstrap 5.3, Blade Templates
- **Authentication**: Laravel Breeze (customized)
- **Export**: Maatwebsite/Laravel-Excel, DomPDF
- **Icons**: Bootstrap Icons

## 📋 Requirements

- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Node.js & NPM (untuk asset compilation)
- Git

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/Ramadityaeka/Inventory-Management-System-IMS-dengan-Approval-System.git
cd Inventory-Management-System-IMS-dengan-Approval-System
```

### 2. Install Dependencies
```bash
composer install
npm install && npm run build
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` dan sesuaikan dengan konfigurasi database Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_esdm
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Database Setup

**Opsi A: Menggunakan Migration & Seeder (Recommended)**
```bash
php artisan migrate --seed
```

**Opsi B: Import SQL Dump**
```bash
# Buat database terlebih dahulu
mysql -u root -p -e "CREATE DATABASE inventory_esdm;"

# Import SQL dump
mysql -u root -p inventory_esdm < database/inventory_esdm.sql
```

### 5. Storage Link
```bash
php artisan storage:link
```

### 6. Run Development Server
```bash
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

## 👤 Default User Credentials

### Super Admin
- Email: `admin@test.com`
- Password: `password`

### Admin Gudang
- Email: `admingudang@gmail.com`
- Password: `password`

### Staff
- Email: `gilbert@gmail.com`
- Password: `password`

## 📁 Struktur Project

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AdminGudang/      # Controllers untuk Admin Gudang
│   │   ├── SuperAdmin/        # Controllers untuk Super Admin
│   │   ├── Staff/             # Controllers untuk Staff
│   │   └── Auth/              # Authentication controllers
│   └── Middleware/            # Custom middleware
├── Models/                    # Eloquent models
├── Exports/                   # Excel export classes
└── Providers/                 # Service providers

resources/
├── views/
│   ├── admin/                 # Super Admin views
│   ├── gudang/                # Admin Gudang views
│   ├── dashboard/             # Role-based dashboards
│   ├── auth/                  # Authentication views
│   └── layouts/               # Layout templates

database/
├── migrations/                # Database migrations
└── seeders/                   # Database seeders

routes/
├── web.php                    # Main routes
├── auth.php                   # Authentication routes
└── test.php                   # Testing routes
```

## 🔧 Session Management Fix

Sistem ini sudah dilengkapi dengan perbaikan session management untuk mengatasi masalah "Session Expired":
- Proper CSRF token handling
- Optimized session configuration
- Custom exception handling untuk session errors

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 👨‍💻 Developer

Developed by **Ramadityaeka** Inventory Management System

## 🤝 Contributing

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
