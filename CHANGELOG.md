# Changelog

Semua perubahan signifikan pada proyek **Polinela Agro Digital** didokumentasikan di file ini.

Format mengikuti standar [Keep a Changelog](https://keepachangelog.com/id/1.0.0/).

---

## [1.0.0] - 2026-09-22

### Ditambahkan
- **Modul Autentikasi**: Login, Register, Lupa Password, Reset Password dengan RBAC 4 role (superadmin, admin_unit, pimpinan, konsumen)
- **Katalog Komoditas**: Daftar produk perkebunan/pertanian dengan pencarian, filter kategori, dan pagination
- **Keranjang Belanja**: Penambahan produk, update qty, hapus item dengan kalkulasi otomatis
- **Checkout Multi-Metode**: Transfer Manual (Mandiri, BRI, BNI), COD, Midtrans Snap (Sandbox), Xendit (Sandbox)
- **Manajemen Pesanan**: Riwayat pesanan konsumen, detail pesanan, upload bukti bayar, konfirmasi terima, pembatalan
- **Invoice & Cetak**: Generate invoice pesanan dalam format PDF dan tampilan web
- **Pelacakan Resi**: Tracking status pengiriman pesanan
- **Ulasan & Rating**: Sistem review produk oleh konsumen pasca pesanan selesai
- **Wishlist**: Simpan produk favorit
- **Profil & Alamat**: Manajemen data diri, foto profil, dan alamat pengiriman
- **Kuisioner SUS**: Evaluasi System Usability Scale dengan kalkulasi skor, grade, dan statistik
- **Dashboard Admin**: Grafik omset Chart.js, pesanan terbaru, stok kritis, statistik ringkas
- **Manajemen Produk Admin**: CRUD produk dengan multi-gambar, penyesuaian stok
- **Manajemen Kategori**: CRUD kategori komoditas dengan slug otomatis
- **Verifikasi Pembayaran**: Konfirmasi/tolak transfer manual oleh admin unit
- **Laporan Komprehensif**: 6 jenis laporan (Penjualan, Produk Terlaris, Stok, Pelanggan, Keuangan, Per Unit) dengan ekspor PDF & CSV
- **Multi-Unit Usaha TEFA**: Isolasi data produk & pesanan per unit usaha
- **Manajemen User**: CRUD user, toggle status aktif/nonaktif, reset password
- **Voucher & Diskon**: Sistem voucher dengan kode unik, minimal belanja, masa aktif
- **Banner Promosi**: Upload banner carousel untuk beranda
- **Pengaturan Toko**: Konfigurasi nama toko, deskripsi, kontak, mata uang, rekening bank
- **Ongkir**: Pengelolaan tarif pengiriman per wilayah
- **Database Backup**: Backup & restore database MySQL langsung dari panel admin
- **Activity Log**: Jejak audit seluruh aktivitas penting dalam sistem
- **Notifikasi Internal**: Sistem notifikasi in-app untuk admin dan konsumen
- **SEO**: robots.txt, sitemap.xml, meta tags terstruktur
- **Kontainerisasi**: Dockerfile & docker-compose.yml untuk deployment portabel
- **PHPUnit Test Suite**: Unit tests & feature tests untuk model dan controller utama
- **Dokumentasi**: README, API, Deployment Guide, Test Report, Changelog

### Arsitektur
- Framework: CodeIgniter 4.x
- Database: 20 tabel termigrasi dengan seeder data demo
- Frontend: Bootstrap 5, SweetAlert2, Chart.js
- PHP: 8.2+ dengan ekstensi intl, curl, mbstring, mysqli
- Testing: PHPUnit 10
