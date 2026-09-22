# 🌿 Polinela Agro Digital

**Platform E-Commerce Komoditas Perkebunan & Pertanian Politeknik Negeri Lampung**

[![PHP Version](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net/)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.x-orange.svg)](https://codeigniter.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## 📋 Deskripsi

**Polinela Agro Digital** adalah aplikasi web marketplace berbasis **CodeIgniter 4** yang dikembangkan untuk mendukung kegiatan pemasaran dan penjualan produk komoditas perkebunan & pertanian dari unit-unit usaha **Teaching Factory (TEFA)** Politeknik Negeri Lampung.

Sistem ini mendukung multi-unit usaha dengan fitur e-commerce lengkap mulai dari katalog produk, keranjang belanja, checkout multi-metode pembayaran, pengelolaan pesanan, hingga laporan dan evaluasi sistem melalui **System Usability Scale (SUS)**.

---

## ✨ Fitur Utama

### 🛒 Frontend (Konsumen)
- **Katalog Komoditas** — Pencarian & filter produk perkebunan/pertanian
- **Keranjang Belanja** — Penambahan qty dinamis & kalkulasi subtotal
- **Checkout Multi-Metode** — Transfer Manual, COD, Midtrans Snap, Xendit
- **Riwayat Pesanan** — Detail pesanan, cetak invoice PDF, upload bukti bayar
- **Pelacakan Resi** — Tracking status pengiriman real-time
- **Ulasan & Rating** — Beri ulasan produk setelah pesanan selesai
- **Wishlist** — Simpan produk favorit
- **Profil & Alamat** — Manajemen akun dan alamat pengiriman
- **Kuisioner SUS** — Evaluasi usability sistem

### 🏢 Backend (Admin & Pimpinan)
- **Dashboard Dinamis** — Grafik omset, pesanan terbaru, stok kritis
- **Manajemen Produk** — CRUD produk dengan upload multi-gambar
- **Manajemen Pesanan** — Update status, cetak surat jalan, input resi
- **Verifikasi Pembayaran** — Konfirmasi transfer manual & gateway otomatis
- **Laporan Komprehensif** — Penjualan, stok, keuangan, pelanggan (PDF & Excel)
- **Multi-Unit Usaha** — Isolasi data per unit TEFA
- **Manajemen User** — RBAC (Superadmin, Admin Unit, Pimpinan, Konsumen)
- **Voucher & Banner** — Promosi dan visual marketing
- **Pengaturan Toko** — Konfigurasi SEO, kontak, rekening bank
- **Database Backup** — Backup & restore data MySQL
- **Activity Log** — Jejak audit aktivitas sistem

---

## 🛠️ Teknologi

| Komponen           | Teknologi                                  |
|--------------------|---------------------------------------------|
| **Framework**      | CodeIgniter 4.x                             |
| **Bahasa**         | PHP 8.2+                                    |
| **Database**       | MySQL 8.0 / MariaDB 10.11                   |
| **Frontend**       | Bootstrap 5, SweetAlert2, Chart.js          |
| **Payment Gateway**| Midtrans Snap (Sandbox), Xendit (Sandbox)   |
| **Testing**        | PHPUnit 10                                  |
| **Containerization** | Docker + Docker Compose                   |
| **Web Server**     | Apache 2.4 (mod_rewrite)                    |

---

## 📦 Instalasi

### Prasyarat
- PHP 8.2+ dengan ekstensi: `curl`, `intl`, `mbstring`, `mysqli`, `pdo_mysql`
- Composer 2.x
- MySQL 8.0+ / MariaDB 10.11+
- Apache 2.4 dengan `mod_rewrite` aktif

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/polinela/agro-digital.git polinela-agro
cd polinela-agro

# 2. Install dependensi PHP
composer install

# 3. Konfigurasi environment
cp .env.example .env
# Edit .env sesuai konfigurasi database lokal Anda

# 4. Buat database
mysql -u root -e "CREATE DATABASE polinela_agro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Jalankan migrasi database
php spark migrate

# 6. Jalankan seeder data awal
php spark db:seed DatabaseSeeder

# 7. Jalankan development server
php spark serve
```

### Instalasi via Docker

```bash
docker-compose up -d --build
```

Akses aplikasi di `http://localhost:8080/`

---

## 🔑 Akun Demo

| Role          | Email                      | Password   |
|---------------|----------------------------|------------|
| Superadmin    | superadmin@polinela.ac.id  | admin123   |
| Admin Unit    | admin.kebun@polinela.ac.id | admin123   |
| Pimpinan      | pimpinan@polinela.ac.id    | admin123   |
| Konsumen      | konsumen@demo.com          | demo123    |

---

## 📂 Struktur Folder

```
polinela-agro/
├── app/
│   ├── Config/          # Konfigurasi (Routes, Database, Auth, Payment)
│   ├── Controllers/     # Controller Frontend & Admin
│   │   ├── admin/       # Panel Admin (Dashboard, Produk, Laporan, dll)
│   │   └── Payment/     # Controller Pembayaran (Manual, COD, Midtrans, Xendit)
│   ├── Database/
│   │   ├── Migrations/  # 20 migrasi tabel
│   │   └── Seeds/       # Data seeder demo
│   ├── Filters/         # Auth, Admin, Superadmin, RateLimit filters
│   ├── Helpers/         # Helper utilitas (app, auth, date, format, dll)
│   ├── Libraries/       # Library modular (Midtrans, Xendit, QR, Barcode, dll)
│   ├── Models/          # 22 model Eloquent-style
│   └── Views/           # View templates (frontend, admin, auth, components)
├── public/              # Document root Apache
│   ├── assets/          # CSS, JS, gambar
│   ├── robots.txt       # SEO crawling rules
│   └── sitemap.xml      # Sitemap XML
├── tests/               # PHPUnit test suite
│   ├── unit/            # Unit tests
│   └── feature/         # Feature/integration tests
├── docs/                # Dokumentasi proyek
├── writable/            # Logs, cache, session, uploads
├── Dockerfile           # Kontainer PHP 8.2 + Apache
├── docker-compose.yml   # Orchestrasi multi-container
├── phpunit.xml.dist     # Konfigurasi PHPUnit 10
└── composer.json        # Dependensi PHP
```

---

## 🧪 Pengujian

```bash
# Jalankan seluruh test suite
php vendor/bin/phpunit

# Jalankan unit tests saja
php vendor/bin/phpunit --testsuite unit

# Jalankan feature tests saja
php vendor/bin/phpunit --testsuite feature

# Cek syntax PHP semua file
Get-ChildItem -Path "app" -Recurse -Filter "*.php" | ForEach-Object { php -l $_.FullName }
```

---

## 📊 Evaluasi SUS (System Usability Scale)

Sistem menyediakan modul kuisioner **SUS** untuk evaluasi usability oleh pengguna. Hasil evaluasi mencakup:
- **Skor SUS** (0-100)
- **Grade** (A, B, C, D, F)
- **Kategori** (Excellent, Good, OK, Poor, Awful)
- **Statistik** rata-rata, median, dan distribusi responden

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

## 👥 Tim Pengembang

**Politeknik Negeri Lampung** — TEFA Agro Digital

- Program Studi Teknik Informatika / Manajemen Informatika
- Teaching Factory (TEFA) Perkebunan & Pertanian

---

> 🌱 *"Menghubungkan produk perkebunan unggulan Polinela dengan pasar digital"*
