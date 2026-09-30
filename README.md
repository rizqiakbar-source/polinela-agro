# 🌿 Polinela Agro Digital

**Platform E-Commerce Komoditas Perkebunan & Pertanian Politeknik Negeri Lampung**

[![PHP Version](https://img.shields.io/badge/PHP-8.2+-777BB4.svg?logo=php&logoColor=white)](https://php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20.svg?logo=laravel&logoColor=white)](https://laravel.com/)
[![React](https://img.shields.io/badge/React-19.x-61DAFB.svg?logo=react&logoColor=black)](https://react.dev/)
[![Vite](https://img.shields.io/badge/Vite-6.x-646CFF.svg?logo=vite&logoColor=white)](https://vitejs.dev/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## 📋 Deskripsi Proyek

**Polinela Agro Digital** adalah platform web e-commerce modern yang dikembangkan untuk memfasilitasi pemasaran dan penjualan produk komoditas perkebunan dan pertanian dari berbagai unit usaha **Teaching Factory (TEFA) Politeknik Negeri Lampung**.

Aplikasi ini mengadopsi arsitektur decoupled (*Modern Fullstack*):
- **Frontend**: Single Page Application (SPA) berbasis **React**, **Vite**, dan antarmuka responsif modern.
- **Backend**: RESTful API berbasis **Laravel 12** dan **Laravel Sanctum** untuk autentikasi token.
- **Database**: **MySQL** / MariaDB.

---

## ✨ Fitur Utama

### 🛒 Sisi Konsumen (Frontend Store)
- **Katalog Komoditas Terpadu** — Pencarian, filter per unit kebun/kategori, dan sorting produk.
- **Multi-Store Unified Cart** — Keranjang belanja terpadu multi-toko (bisa beli produk dari beberapa unit sekaligus).
- **Split Checkout Otomatis** — Otomatis membagi sub-order per unit usaha dengan **1x pembayaran terpadu** (Shopee-style checkout).
- **Manajemen Pembayaran** — Mendukung Transfer Manual Bank (dengan 1x upload bukti bayar) & Midtrans Payment Gateway.
- **Riwayat & Pelacakan Pesanan** — Status pemesanan real-time, invoice digital, nomor resi pengiriman.
- **Ulasan & Rating** — Penilaian dan review produk setelah pesanan selesai diterima.
- **Wishlist & Profil** — Simpan produk favorit dan kelola alamat pengiriman.
- **Kuisioner Evaluasi SUS** — Modul kuisioner System Usability Scale untuk pengujian pengguna.

### 🏢 Sisi Manajemen & Toko (Admin Panel)
- **Multi-Unit Isolation** — Admin unit kebun (Kopi, Kakao, Sawit, Bibit) hanya memiliki akses ke data unitnya sendiri.
- **Superadmin Overview** — Manajemen seluruh unit, master data pengguna, banner promosi, voucher diskon, dan pengaturan sistem.
- **Manajemen Pesanan & Resi** — Pemrosesan status pesanan, verifikasi pembayaran, dan input resi ekspedisi.
- **Laporan & Rekap Penjualan** — Laporan omset, produk terlaris, dan rekapitulasi data penjualan.
- **Audit & Activity Log** — Pencatatan log aktivitas sistem.

---

## 🛠️ Arsitektur & Teknologi

| Layer | Teknologi |
|---|---|
| **Frontend** | React (Vite), React Router, Axios, Lucide Icons, Modern CSS |
| **Backend API** | Laravel 12, PHP 8.2+, Laravel Sanctum (Bearer Token) |
| **Database** | MySQL 8.0 / MariaDB |
| **Payment Gateway** | Midtrans Snap & Transfer Bank Manual |
| **Notifikasi** | Fonnte WhatsApp Gateway & SMTP Email Notifier |

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi

### 1. Prasyarat Sistem
- PHP >= 8.2 & Composer
- Node.js >= 18.x & npm
- MySQL / XAMPP (Apache & MySQL)

### 2. Kloning Repository
```bash
git clone https://github.com/rizqiakbar-source/polinela-agro.git
cd polinela-agro
```

### 3. Konfigurasi Backend (Laravel)
```bash
cd backend

# Install dependensi PHP
composer install

# Salin file environment dan atur koneksi database
cp .env.example .env
php artisan key:generate

# Migrasi dan isi database awal
php artisan migrate --seed

# Jalankan server API backend (Port 8000)
php artisan serve --port=8000
```

### 4. Konfigurasi Frontend (React + Vite)
Buka terminal baru di folder `frontend`:
```bash
cd frontend

# Install dependensi Node.js
npm install

# Jalankan development server frontend (Port 5173)
npm run dev
```

### 5. Akses Aplikasi
- **Frontend App**: `http://localhost:5173`
- **Backend API**: `http://localhost:8000/api`

*(Tersedia juga script `start-dev.bat` di direktori utama untuk menjalankan backend dan frontend secara bersamaan).*

---

## 📂 Struktur Direktori

```
polinela-agro/
├── backend/               # Backend API (Laravel 12)
│   ├── app/               # Controllers, Models, Services, Middlewares
│   ├── config/            # Konfigurasi aplikasi & database
│   ├── database/          # Migrasi tabel & Data Seeder
│   ├── routes/            # Definisi API routes (/api)
│   └── storage/           # Penyimpanan upload berkas & log
├── frontend/              # Frontend Web (React + Vite)
│   ├── src/
│   │   ├── api/           # Axios client & endpoint handlers
│   │   ├── components/    # Komponen reusable (Navbar, Footer, Modals)
│   │   ├── context/       # Auth, Cart, Theme Context
│   │   ├── pages/         # Halaman Konsumen & Admin Panel
│   │   └── utils/         # Helper pemformatan & fungsi utilitas
├── docs/                  # Backup database & dokumentasi sistem
└── start-dev.bat          # Script otomasi startup lokal
```

---

## 👥 Tim Pengembang

**Program Studi D3/D4 Manajemen Informatika**  
**Politeknik Negeri Lampung**

| No | Nama | NPM |
|:--:|:---|:---:|
| 1 | **Rizqi Akbar** | 24781086 |
| 2 | **Affan Fazle Mawla** | 24781083 |
| 3 | **Az Zahra Juas Dinda Dinata** | 24781066 |
| 4 | **Putri Sari Rizkiyah** | 24781082 |

---

## 📄 Lisensi
Proyek ini dilisensikan di bawah [MIT License](LICENSE).

