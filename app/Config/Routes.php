<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Frontend & Publik
$routes->get('/', 'Home::index');
$routes->get('katalog', 'Produk::index');
$routes->get('produk/(:segment)', 'Produk::detail/$1');
$routes->get('api/search', 'Produk::apiSearch');
$routes->get('tentang', 'Tentang::index');
$routes->get('kontak', 'Kontak::index');
$routes->post('kontak/kirim', 'Kontak::kirim');
$routes->get('lacak', 'Tracking::index');
$routes->get('sus', 'Sus::index');
$routes->post('sus/submit', 'Sus::submit');

// Autentikasi
$routes->match(['get', 'post'], 'login', 'Auth::login', ['filter' => 'ratelimit']);
$routes->match(['get', 'post'], 'register', 'Auth::register');
$routes->get('logout', 'Auth::logout');
$routes->match(['get', 'post'], 'lupa-password', 'Auth::forgotPassword');
$routes->match(['get', 'post'], 'reset-password/(:segment)', 'Auth::resetPassword/$1');
$routes->match(['get', 'post'], 'reset-password', 'Auth::resetPassword');

// Pembayaran & Konfirmasi Cepat
$routes->get('pembayaran/(:segment)', 'Payment\Manual::detail/$1');
$routes->get('konfirmasi-bayar', 'Payment\Manual::konfirmasi');
$routes->post('konfirmasi-bayar/submit', 'Payment\Manual::submitKonfirmasi');
$routes->match(['get', 'post'], 'pesanan/konfirmasi-cod/(:segment)', 'Payment\Cod::konfirmasiCod/$1');
$routes->post('payment/midtrans/token', 'Payment\Midtrans::token');
$routes->post('payment/xendit/invoice', 'Payment\Xendit::createInvoice');
$routes->post('payment/callback', 'Payment\Callback::index');
$routes->post('payment/callback/midtrans', 'Payment\Callback::midtrans');
$routes->post('payment/callback/xendit', 'Payment\Callback::xendit');

// Ulasan Publik
$routes->get('ulasan', 'Ulasan::index');

// Transaksi & Fitur Pengguna Terdaftar (Auth Filter)
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    // Keranjang
    $routes->get('keranjang', 'Keranjang::index');
    $routes->post('keranjang/add', 'Keranjang::add');
    $routes->post('keranjang/update', 'Keranjang::update');
    $routes->get('keranjang/delete/(:num)', 'Keranjang::delete/$1');
    $routes->get('keranjang/count', 'Keranjang::getCount');

    // Checkout
    $routes->get('checkout', 'Checkout::index');
    $routes->post('checkout/voucher', 'Checkout::applyVoucher');
    $routes->post('checkout/process', 'Checkout::process');

    // Pesanan & Invoice
    $routes->get('pesanan', 'Pesanan::index');
    $routes->get('pesanan/detail/(:segment)', 'Pesanan::detail/$1');
    $routes->post('pesanan/upload-bukti', 'Pesanan::uploadBukti');
    $routes->post('pesanan/terima/(:num)', 'Pesanan::konfirmasiTerima/$1');
    $routes->post('pesanan/batal/(:num)', 'Pesanan::batalkan/$1');
    $routes->get('invoice/(:segment)', 'Pesanan::invoice/$1');
    $routes->get('invoice/pdf/(:segment)', 'Pesanan::invoicePdf/$1');

    // Ulasan & Wishlist
    $routes->post('ulasan/store', 'Ulasan::store');
    $routes->get('wishlist', 'Wishlist::index');
    $routes->post('wishlist/toggle', 'Wishlist::toggle');

    // Profil, Alamat & Notifikasi
    $routes->get('profil', 'Profil::index');
    $routes->post('profil/update', 'Profil::update');
    $routes->post('profil/password', 'Profil::changePassword');
    $routes->get('alamat', 'Profil::alamat');
    $routes->post('alamat/save', 'Profil::saveAlamat');
    $routes->get('notifikasi', 'Notifikasi::index');
});

// Panel Manajemen Admin (Superadmin, Admin Unit, Pimpinan)
$routes->group('admin', ['filter' => 'admin'], static function ($routes) {
    $routes->get('/', 'admin\Dashboard::index');
    $routes->get('dashboard', 'admin\Dashboard::index');

    // Produk
    $routes->get('produk', 'admin\Produk::index');
    $routes->match(['get', 'post'], 'produk/create', 'admin\Produk::create');
    $routes->match(['get', 'post'], 'produk/edit/(:num)', 'admin\Produk::edit/$1');
    $routes->get('produk/delete/(:num)', 'admin\Produk::delete/$1');
    $routes->post('produk/stock', 'admin\Produk::adjustStock');

    // Kategori
    $routes->get('kategori', 'admin\Kategori::index');
    $routes->post('kategori/create', 'admin\Kategori::create');
    $routes->post('kategori/update/(:num)', 'admin\Kategori::update/$1');
    $routes->get('kategori/delete/(:num)', 'admin\Kategori::delete/$1');

    // Pesanan
    $routes->get('pesanan', 'admin\Pesanan::index');
    $routes->get('pesanan/detail/(:num)', 'admin\Pesanan::detail/$1');
    $routes->post('pesanan/update-status', 'admin\Pesanan::updateStatus');
    $routes->get('pesanan/surat-jalan/(:num)', 'admin\Pesanan::cetakSuratJalan/$1');

    // Verifikasi Pembayaran
    $routes->get('pembayaran', 'admin\Pembayaran::index');
    $routes->post('pembayaran/verifikasi', 'admin\Pembayaran::verifikasi');
    $routes->post('pembayaran/tolak', 'admin\Pembayaran::tolak');

    // Laporan & Export
    $routes->get('laporan', 'admin\Laporan::index');
    $routes->get('laporan/pdf', 'admin\Laporan::exportPdf');
    $routes->get('laporan/excel', 'admin\Laporan::exportExcel');

    // Review / Moderasi Ulasan
    $routes->get('review', 'admin\Review::index');
    $routes->post('review/status/(:num)', 'admin\Review::updateStatus/$1');
    $routes->get('review/delete/(:num)', 'admin\Review::delete/$1');

    // Modul Khusus Superadmin
    $routes->group('', ['filter' => 'superadmin'], static function ($routes) {
        // Unit Usaha
        $routes->get('unit', 'admin\Unit::index');
        $routes->post('unit/create', 'admin\Unit::create');
        $routes->post('unit/update/(:num)', 'admin\Unit::update/$1');
        $routes->post('unit/assign', 'admin\Unit::assignAdmin');
        $routes->get('unit/delete/(:num)', 'admin\Unit::delete/$1');

        // Pengguna & Hak Akses
        $routes->get('user', 'admin\User::index');
        $routes->post('user/create', 'admin\User::create');
        $routes->get('user/toggle/(:num)', 'admin\User::toggleStatus/$1');
        $routes->get('user/reset/(:num)', 'admin\User::resetPassword/$1');

        // Banners
        $routes->get('banner', 'admin\Banner::index');
        $routes->post('banner/create', 'admin\Banner::create');
        $routes->get('banner/delete/(:num)', 'admin\Banner::delete/$1');

        // Vouchers
        $routes->get('voucher', 'admin\Voucher::index');
        $routes->post('voucher/create', 'admin\Voucher::create');
        $routes->get('voucher/delete/(:num)', 'admin\Voucher::delete/$1');

        // Ongkir
        $routes->get('ongkir', 'admin\Ongkir::index');
        $routes->post('ongkir/save', 'admin\Ongkir::save');

        // Database Backup & Logs
        $routes->get('backup', 'admin\Backup::index');
        $routes->get('backup/download', 'admin\Backup::download');
        $routes->get('log', 'admin\Log::index');

        // Pengaturan Toko & SEO
        $routes->get('pengaturan', 'admin\Pengaturan::index');
        $routes->post('pengaturan/save', 'admin\Pengaturan::save');
    });
});
