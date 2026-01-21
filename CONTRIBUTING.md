# Contributing to Inventory Management System

Terima kasih atas minat Anda untuk berkontribusi pada Inventory Management System! Dokumen ini berisi panduan untuk membantu Anda berkontribusi pada project ini.

## 🚀 Cara Berkontribusi

### 1. Fork & Clone Repository
```bash
# Fork repository melalui GitHub UI
# Clone forked repository
git clone https://github.com/YOUR-USERNAME/Inventory-Management-System-IMS-dengan-Approval-System.git
cd Inventory-Management-System-IMS-dengan-Approval-System
```

### 2. Setup Development Environment
```bash
# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --seed
```

### 3. Create Feature Branch
```bash
git checkout -b feature/nama-fitur-anda
# atau
git checkout -b bugfix/nama-bug-yang-diperbaiki
```

### 4. Make Your Changes
- Ikuti coding standards Laravel
- Pastikan kode Anda terdokumentasi dengan baik
- Tulis unit tests jika memungkinkan

### 5. Commit Your Changes
```bash
git add .
git commit -m "feat: menambahkan fitur X"
# atau
git commit -m "fix: memperbaiki bug Y"
```

#### Commit Message Guidelines
Gunakan conventional commits:
- `feat:` untuk fitur baru
- `fix:` untuk bug fixes
- `docs:` untuk perubahan dokumentasi
- `style:` untuk perubahan format kode
- `refactor:` untuk refactoring kode
- `test:` untuk menambah atau memperbaiki tests
- `chore:` untuk maintenance tasks

### 6. Push & Create Pull Request
```bash
git push origin feature/nama-fitur-anda
```

Kemudian buat Pull Request melalui GitHub UI dengan deskripsi yang jelas.

## 📋 Code Standards

### PHP/Laravel
- Ikuti PSR-12 coding standards
- Gunakan type hints dan return types
- Tulis PHPDoc untuk methods dan classes
- Ikuti Laravel best practices

### Blade Templates
- Gunakan Blade components untuk reusable UI
- Proper indentation (4 spaces)
- Pisahkan logic dari presentation

### JavaScript
- Gunakan modern ES6+ syntax
- Comment kode yang complex
- Minimize inline scripts

### Database
- Buat migrations untuk setiap perubahan schema
- Gunakan seeders untuk sample data
- Foreign keys harus memiliki proper constraints

## 🐛 Reporting Bugs

Jika menemukan bug, silakan buat issue dengan informasi berikut:
1. Deskripsi jelas tentang bug
2. Langkah-langkah untuk reproduce
3. Expected behavior vs actual behavior
4. Screenshot jika memungkinkan
5. Environment details (PHP version, Laravel version, dll)

## 💡 Feature Requests

Untuk request fitur baru:
1. Cek apakah fitur sudah ada atau sedang dikerjakan
2. Buat issue dengan label "enhancement"
3. Jelaskan use case dan manfaat fitur
4. Berikan contoh implementasi jika ada

## ✅ Pull Request Checklist

Sebelum submit PR, pastikan:
- [ ] Kode berjalan tanpa error
- [ ] Tests (jika ada) passed
- [ ] Tidak ada konflik dengan main branch
- [ ] Kode mengikuti project standards
- [ ] Dokumentasi diupdate jika perlu
- [ ] Commit messages jelas dan deskriptif

## 🤝 Community Guidelines

- Bersikap sopan dan profesional
- Respect kontributor lain
- Konstruktif dalam feedback
- Fokus pada improvement project

## 📞 Contact

Jika ada pertanyaan, silakan:
- Buat issue di GitHub
- Contact maintainer melalui email

Terima kasih atas kontribusi Anda! 🙏
