<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
// Auth Public
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Products & Units Public
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slugOrId}', [ProductController::class, 'show']);

Route::get('/units', [UnitController::class, 'index']);
Route::get('/units/{slugOrId}', [UnitController::class, 'show']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/banners', [BannerController::class, 'index']);
Route::get('/settings/public', [BannerController::class, 'settings']);

/*
|--------------------------------------------------------------------------
| Authenticated Customer & Universal Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth Profile
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Keranjang Belanja
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'add']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/update', [CartController::class, 'updateQty']);
    Route::put('/cart/{id}', [CartController::class, 'updateQty']);
    Route::delete('/cart/{id}', [CartController::class, 'remove']);
    Route::get('/cart/count', [CartController::class, 'count']);

    // Checkout & Voucher
    Route::post('/checkout', [CheckoutController::class, 'process']);
    Route::post('/checkout/process', [CheckoutController::class, 'process']);
    Route::post('/checkout/voucher', [CheckoutController::class, 'validateVoucher']);
    Route::post('/checkout/validate-voucher', [CheckoutController::class, 'validateVoucher']);

    // Pesanan Konsumen
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);
    Route::post('/orders/upload-bukti', [OrderController::class, 'uploadBukti']);
    Route::post('/orders/{id}/upload-bukti', [OrderController::class, 'uploadBukti']);
    Route::match(['put', 'post'], '/orders/{id}/terima', [OrderController::class, 'konfirmasiTerima']);
    Route::match(['put', 'post'], '/orders/{id}/konfirmasi-terima', [OrderController::class, 'konfirmasiTerima']);
    Route::match(['put', 'post'], '/orders/{id}/batal', [OrderController::class, 'batalkan']);
    Route::get('/orders/{id}/whatsapp-link', [OrderController::class, 'whatsappLink']);
    // Pesanan & Pembayaran Midtrans Snap
    Route::get('/payment/midtrans/token/{id}', [PaymentController::class, 'getSnapToken']);
    Route::post('/payment/finish/{id}', [PaymentController::class, 'finishPayment']);
    Route::get('/payment/midtrans/status/{id}', [PaymentController::class, 'checkAndSyncStatus']);

    // Ulasan Produk Konsumen
    Route::post('/products/{id}/reviews', [ProductController::class, 'addReview']);
});

// Midtrans Public Webhook / Callback
Route::post('/payment/callback/midtrans', [PaymentController::class, 'handleCallback']);
Route::post('/payment/midtrans/callback', [PaymentController::class, 'handleCallback']);


/*
|--------------------------------------------------------------------------
| Admin & Pimpinan Protected Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    // Dashboard Stats
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    // Kelola Produk Per Unit / Superadmin
    Route::get('/products', [AdminProductController::class, 'index']);
    Route::post('/products', [AdminProductController::class, 'store']);
    Route::match(['put', 'post'], '/products/{id}', [AdminProductController::class, 'update']);
    Route::put('/products/{id}/stock', [AdminProductController::class, 'updateStock']);
    Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);


    // Kelola Pesanan
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
    Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
    Route::put('/orders/{id}/resi', [AdminOrderController::class, 'updateStatus']);

    // Verifikasi Pembayaran
    Route::get('/payments', [AdminPaymentController::class, 'index']);
    Route::match(['put', 'post'], '/payments/verifikasi', [AdminPaymentController::class, 'verifikasi']);
    Route::match(['put', 'post'], '/payments/{id}/verifikasi', [AdminPaymentController::class, 'verifikasi']);
    Route::match(['put', 'post'], '/payments/tolak', [AdminPaymentController::class, 'tolak']);
    Route::match(['put', 'post'], '/payments/{id}/tolak', [AdminPaymentController::class, 'tolak']);

    // Laporan Penjualan
    Route::get('/reports', [AdminReportController::class, 'index']);
    Route::get('/reports/sales', [AdminReportController::class, 'index']);

    // Manajemen Pengguna (Superadmin)
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::post('/users', [AdminUserController::class, 'store']);
    Route::put('/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);

    // Unit Usaha TEFA
    Route::get('/units', [\App\Http\Controllers\Api\Admin\UnitController::class, 'index']);
    Route::post('/units', [\App\Http\Controllers\Api\Admin\UnitController::class, 'store']);
    Route::put('/units/{id}', [\App\Http\Controllers\Api\Admin\UnitController::class, 'update']);
    Route::delete('/units/{id}', [\App\Http\Controllers\Api\Admin\UnitController::class, 'destroy']);

    // Kategori Produk
    Route::get('/categories', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'index']);
    Route::post('/categories', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'store']);
    Route::put('/categories/{id}', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'destroy']);

    // Voucher & Diskon
    Route::get('/vouchers', [\App\Http\Controllers\Api\Admin\VoucherController::class, 'index']);
    Route::post('/vouchers', [\App\Http\Controllers\Api\Admin\VoucherController::class, 'store']);
    Route::put('/vouchers/{id}', [\App\Http\Controllers\Api\Admin\VoucherController::class, 'update']);
    Route::delete('/vouchers/{id}', [\App\Http\Controllers\Api\Admin\VoucherController::class, 'destroy']);

    // Banner Promosi
    Route::get('/banners', [\App\Http\Controllers\Api\Admin\BannerController::class, 'index']);
    Route::post('/banners', [\App\Http\Controllers\Api\Admin\BannerController::class, 'store']);
    Route::put('/banners/{id}', [\App\Http\Controllers\Api\Admin\BannerController::class, 'update']);
    Route::delete('/banners/{id}', [\App\Http\Controllers\Api\Admin\BannerController::class, 'destroy']);

    // Ulasan Produk
    Route::get('/reviews', [\App\Http\Controllers\Api\Admin\ReviewController::class, 'index']);
    Route::delete('/reviews/{id}', [\App\Http\Controllers\Api\Admin\ReviewController::class, 'destroy']);

    // Pengaturan Sistem
    Route::get('/settings', [\App\Http\Controllers\Api\Admin\SettingController::class, 'index']);
    Route::post('/settings', [\App\Http\Controllers\Api\Admin\SettingController::class, 'update']);

    // Tarif Ongkir & Kurir
    Route::get('/ongkir', [\App\Http\Controllers\Api\Admin\OngkirController::class, 'index']);
    Route::post('/ongkir', [\App\Http\Controllers\Api\Admin\OngkirController::class, 'store']);

    // Backup Database
    Route::get('/backup', [\App\Http\Controllers\Api\Admin\BackupController::class, 'index']);
    Route::post('/backup/create', [\App\Http\Controllers\Api\Admin\BackupController::class, 'create']);

    // Audit Log Aktivitas
    Route::get('/logs', [\App\Http\Controllers\Api\Admin\LogController::class, 'index']);
});

