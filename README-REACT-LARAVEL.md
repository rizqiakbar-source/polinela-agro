# 🌿 Polinela Agro - Sistem E-Commerce Multi-Store Perkebunan

Aplikasi web e-commerce hasil pertanian & perkebunan Politeknik Negeri Lampung berbasis arsitektur decoupled modern:
- **Frontend:** React + Vite + JavaScript (port `5173`)
- **Backend:** Laravel 12 + PHP REST API (port `8000`)
- **Database:** MySQL (`polinela_agro`)
- **Autentikasi:** Laravel Sanctum (Bearer Token)
- **Komunikasi Data:** REST API JSON

---

## 🚀 Fitur Utama & Bisnis Logik

### 1. Multi-Store / Multi-Unit Perkebunan Mandiri
- Toko terbagi berdasarkan unit usaha:
  - ☕ **Unit Kopi Polinela**
  - 🍫 **Unit Kakao & Cokelat**
  - 🌴 **Unit Kelapa Sawit**
  - 🌱 **Unit Bibit & Hortikultura**
- Setiap Admin Unit toko **hanya memiliki akses ke data unitnya sendiri** (Katalog produk, stok, pesanan masuk, dan verifikasi pembayaran unit terkait).
- Admin Unit Kakao **tidak dapat** melihat atau memverifikasi pesanan/pembayaran Unit Kopi, begitu pula sebaliknya.

### 2. Shopee-Style Unified Cart & 1x Pembayaran Terpadu
- Konsumen dapat menambahkan produk dari berbagai unit toko perkebunan sekaligus ke dalam 1 keranjang belanja.
- Saat checkout:
  - Sistem otomatis membagi (*split*) pesanan menjadi sub-order ke masing-masing unit toko.
  - Sub-order dihubungkan melalui satu kode transaksi bersama (**`sharedTrxNumber`** / `TRX-XXXXXX`).
  - Konsumen **cukup melakukan 1 kali transfer pembayaran** untuk total seluruh produk dan mengunggah **1 bukti transfer**.
  - Status pembayaran di seluruh sub-order terkait langsung diperbarui serentak.

### 3. Role Pengguna & Hak Akses
1. **Superadmin**: Akses penuh ke semua data unit toko, pengguna, laporan menyeluruh, dan penugasan unit.
2. **Admin Unit**: Mengelola produk, stok, pemrosesan pesanan, input nomor resi, dan verifikasi pembayaran khusus unit toko yang ditugaskan.
3. **Pimpinan**: Melihat laporan penjualan dan analisis performa omset per unit toko.
4. **Konsumen**: Menjelajah katalog, belanja multi-toko, 1x checkout, riwayat pesanan, dan upload bukti transfer.

---

## 💻 Cara Menjalankan Aplikasi

### Opsi 1: Menggunakan Script Otomatis
Cukup klik ganda file:
```
start-dev.bat
```

### Opsi 2: Menjalankan Secara Manual

#### 1. Backend (Laravel API)
Buka terminal dan jalankan:
```bash
cd backend
php artisan serve --port=8000
```
API Backend berjalan di: `http://localhost:8000/api`

#### 2. Frontend (React + Vite)
Buka terminal baru dan jalankan:
```bash
cd frontend
npm run dev
```
Frontend React berjalan di: `http://localhost:5173`

---

## 📡 Daftar Endpoint REST API Utama

| Method | Endpoint | Deskripsi | Hak Akses |
|---|---|---|---|
| `POST` | `/api/auth/login` | Login user & dapatkan Sanctum Token | Publik |
| `POST` | `/api/auth/register` | Pendaftaran akun konsumen | Publik |
| `GET` | `/api/products` | Katalog produk (filter unit, kategori, search, sort) | Publik |
| `GET` | `/api/units` | Daftar unit toko perkebunan | Publik |
| `GET` | `/api/cart` | Keranjang belanja (dikelompokkan per toko) | Konsumen |
| `POST` | `/api/cart` | Tambah produk ke keranjang | Konsumen |
| `POST` | `/api/checkout` | Multi-store checkout & split orders | Konsumen |
| `GET` | `/api/orders` | Riwayat pesanan pembeli | Konsumen |
| `GET` | `/api/orders/{id}` | Detail pesanan & info sibling multi-store | Konsumen |
| `POST` | `/api/orders/{id}/upload-bukti` | 1x Upload bukti bayar transaksi bersama | Konsumen |
| `GET` | `/api/admin/dashboard` | Dashboard analitik unit toko | Staff/Admin |
| `GET/POST` | `/api/admin/products` | CRUD produk & stok unit | Admin Unit / Superadmin |
| `GET/PUT` | `/api/admin/orders` | Manajemen pesanan & input nomor resi | Admin Unit / Superadmin |
| `GET/PUT` | `/api/admin/payments` | Verifikasi/tolak pembayaran unit | Admin Unit / Superadmin |
| `GET` | `/api/admin/reports/sales` | Rekapitulasi omset & top produk | Admin / Pimpinan |
| `GET/POST/PUT` | `/api/admin/users` | Kelola pengguna & penugasan unit | Superadmin |
