# 📡 API Reference — Polinela Agro Digital

Dokumentasi endpoint API internal yang digunakan oleh frontend dan integrasi pihak ketiga.

**Base URL**: `http://localhost/polinela-agro/public/`

---

## 🔓 Autentikasi

### POST `/login`
Login pengguna dan membuat sesi.

**Request Body** (`application/x-www-form-urlencoded`):
| Parameter  | Tipe   | Wajib | Keterangan              |
|------------|--------|-------|--------------------------|
| `email`    | string | Ya    | Email terdaftar          |
| `password` | string | Ya    | Kata sandi               |

**Response**: Redirect ke `/` (konsumen) atau `/admin/dashboard` (admin).

---

### POST `/register`
Mendaftarkan akun konsumen baru.

**Request Body**:
| Parameter  | Tipe   | Wajib | Keterangan                    |
|------------|--------|-------|--------------------------------|
| `nama`     | string | Ya    | Nama lengkap (min 3 karakter) |
| `email`    | string | Ya    | Email unik                    |
| `password` | string | Ya    | Kata sandi (min 6 karakter)   |
| `no_hp`    | string | Ya    | Nomor HP (min 10 digit)       |
| `alamat`   | string | Tidak | Alamat lengkap                |

**Response**: Auto-login + redirect ke beranda.

---

### GET `/logout`
Menghancurkan sesi pengguna.

**Response**: Redirect ke `/login`.

---

## 🛍️ Katalog & Produk

### GET `/katalog`
Menampilkan halaman katalog produk dengan filter.

**Query Parameters**:
| Parameter   | Tipe    | Keterangan                        |
|-------------|---------|-----------------------------------|
| `search`    | string  | Kata kunci pencarian              |
| `category`  | integer | ID kategori                       |
| `unit`      | integer | ID unit usaha                     |
| `sort`      | string  | Urutan: `terbaru`, `termurah`, `terlaris` |
| `page`      | integer | Halaman paginasi                  |

### GET `/produk/{slug}`
Menampilkan detail produk berdasarkan slug URL.

### GET `/api/search?q={keyword}`
API pencarian produk (JSON response).

**Response** (`application/json`):
```json
{
  "results": [
    {
      "id": 1,
      "nama_produk": "Kopi Robusta Premium",
      "slug": "kopi-robusta-premium",
      "harga": 85000,
      "gambar": "kopi-robusta.jpg"
    }
  ]
}
```

---

## 🛒 Keranjang Belanja

> **Autentikasi diperlukan** — Semua endpoint keranjang memerlukan sesi login aktif.

### GET `/keranjang`
Menampilkan halaman keranjang belanja.

### POST `/keranjang/add`
Menambahkan produk ke keranjang.

| Parameter    | Tipe    | Wajib | Keterangan     |
|-------------|---------|-------|-----------------|
| `product_id`| integer | Ya    | ID produk       |
| `qty`       | integer | Ya    | Jumlah item     |

### POST `/keranjang/update`
Memperbarui jumlah item dalam keranjang.

| Parameter | Tipe    | Wajib | Keterangan         |
|-----------|---------|-------|--------------------|
| `cart_id` | integer | Ya    | ID item keranjang  |
| `qty`     | integer | Ya    | Jumlah baru        |

### GET `/keranjang/delete/{id}`
Menghapus item dari keranjang.

### GET `/keranjang/count`
Mengembalikan jumlah item di keranjang (JSON).

```json
{ "count": 3 }
```

---

## 💳 Checkout & Pembayaran

### GET `/checkout`
Menampilkan halaman checkout.

### POST `/checkout/voucher`
Menerapkan kode voucher diskon.

| Parameter      | Tipe   | Wajib | Keterangan  |
|----------------|--------|-------|-------------|
| `kode_voucher` | string | Ya    | Kode voucher|

### POST `/checkout/process`
Memproses pesanan checkout.

| Parameter          | Tipe   | Wajib | Keterangan                           |
|--------------------|--------|-------|---------------------------------------|
| `metode_pembayaran`| string | Ya    | `transfer`, `cod`, `midtrans`, `xendit` |
| `alamat_lengkap`   | string | Ya    | Alamat lengkap pengiriman            |
| `penerima_nama`    | string | Ya    | Nama penerima                        |
| `penerima_telepon` | string | Ya    | No telepon penerima                  |
| `kota`             | string | Ya    | Kota tujuan                          |
| `catatan`          | string | Tidak | Catatan tambahan                     |
| `kode_voucher`     | string | Tidak | Kode voucher (opsional)              |

---

## 📦 Pesanan

### GET `/pesanan`
Daftar riwayat pesanan konsumen.

### GET `/pesanan/detail/{order_number}`
Detail pesanan beserta status pembayaran dan pengiriman.

### POST `/pesanan/upload-bukti`
Upload bukti transfer pembayaran.

| Parameter  | Tipe | Wajib | Keterangan                |
|------------|------|-------|---------------------------|
| `order_id` | int  | Ya    | ID pesanan                |
| `bukti`    | file | Ya    | File gambar (JPG/PNG, max 2MB) |

### POST `/pesanan/terima/{id}`
Konfirmasi penerimaan barang oleh konsumen.

### POST `/pesanan/batal/{id}`
Membatalkan pesanan (hanya status `menunggu`).

### GET `/invoice/{order_number}`
Menampilkan invoice pesanan dalam format web.

### GET `/invoice/pdf/{order_number}`
Mengunduh invoice dalam format PDF.

---

## ⭐ Ulasan

### GET `/ulasan`
Daftar ulasan produk terbaru.

### POST `/ulasan/store`
Menyimpan ulasan produk.

| Parameter    | Tipe    | Wajib | Keterangan             |
|-------------|---------|-------|-------------------------|
| `product_id`| integer | Ya    | ID produk               |
| `order_id`  | integer | Ya    | ID pesanan              |
| `rating`    | integer | Ya    | Rating 1-5              |
| `komentar`  | string  | Tidak | Komentar ulasan         |

---

## 💚 Wishlist

### GET `/wishlist`
Daftar produk favorit.

### POST `/wishlist/toggle`
Toggle tambah/hapus produk dari wishlist.

| Parameter    | Tipe    | Wajib | Keterangan |
|-------------|---------|-------|-------------|
| `product_id`| integer | Ya    | ID produk   |

---

## 📊 Kuisioner SUS

### GET `/sus`
Menampilkan form kuisioner System Usability Scale.

### POST `/sus/submit`
Mengirim jawaban kuisioner SUS.

| Parameter | Tipe    | Wajib | Keterangan             |
|-----------|---------|-------|-------------------------|
| `q1`-`q10`| integer | Ya    | Jawaban per item (1-5) |
| `nama`    | string  | Tidak | Nama responden          |
| `email`   | string  | Tidak | Email responden         |

**Response**: Redirect ke halaman hasil dengan skor, grade, dan kategori.

---

## 🔔 Webhook Pembayaran

### POST `/payment/callback/midtrans`
Menerima notifikasi callback dari Midtrans Snap.

### POST `/payment/callback/xendit`
Menerima notifikasi callback dari Xendit.

> **Catatan**: Endpoint webhook menerima payload JSON dari payment gateway dan secara otomatis memperbarui status `payments` dan `orders`.

---

## 🛡️ Pelacakan

### GET `/lacak`
Halaman pelacakan pesanan berdasarkan nomor pesanan atau resi.

---

## 📋 Admin API

Seluruh endpoint admin berada di prefix `/admin/` dan memerlukan autentikasi dengan role `superadmin`, `admin_unit`, atau `pimpinan`.

### Laporan
| Method | Endpoint                 | Keterangan              |
|--------|--------------------------|--------------------------|
| GET    | `/admin/laporan`         | Halaman laporan utama   |
| GET    | `/admin/laporan/pdf`     | Export laporan PDF      |
| GET    | `/admin/laporan/excel`   | Export laporan CSV      |

**Query Parameters** (berlaku untuk ketiganya):
| Parameter    | Tipe   | Keterangan            |
|-------------|--------|-----------------------|
| `type`      | string | `penjualan`, `produk_terlaris`, `stok`, `pelanggan`, `keuangan`, `per_unit` |
| `start_date`| date   | Tanggal mulai         |
| `end_date`  | date   | Tanggal akhir         |
| `unit_id`   | integer| Filter per unit usaha |

---

> 📌 **Catatan**: Semua endpoint menggunakan CSRF protection. Untuk request POST, sertakan token CSRF yang tersedia di form view.
