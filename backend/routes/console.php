<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\StockService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Command untuk membatalkan pesanan kedaluwarsa & mengembalikan stok secara otomatis
 * Penggunaan: php artisan orders:cancel-expired 24
 */
Artisan::command('orders:cancel-expired {hours=24}', function ($hours = 24) {
    $this->info("Memeriksa pesanan kedaluwarsa (> {$hours} jam)...");
    $count = StockService::cancelExpiredOrders((int) $hours);
    $this->info("Selesai! {$count} pesanan kedaluwarsa telah dibatalkan & stok produk dikembalikan ke etalase.");
})->purpose('Batalkan pesanan belum lunas yang melewati batas waktu dan kembalikan stoknya.');

// Jalankan otomatis setiap jam di background scheduler
Schedule::command('orders:cancel-expired 24')->hourly();

// Bersihkan token autentikasi yang sudah kedaluwarsa setiap hari
Schedule::command('sanctum:prune-expired --hours=24')->daily();


