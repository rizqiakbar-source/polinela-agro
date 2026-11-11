<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Payment;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    /**
     * Kembalikan stok untuk semua item dalam pesanan yang dibatalkan
     */
    public static function restoreOrderStock(Order $order, string $reason, ?int $userId = null): void
    {
        $order->loadMissing('details');

        foreach ($order->details as $d) {
            if (!$d->product_id || $d->qty <= 0) {
                continue;
            }

            $product = Product::where('id', $d->product_id)->lockForUpdate()->first();
            if ($product) {
                $product->increment('stok', $d->qty);

                // Pastikan total_terjual tidak minus
                if ($product->total_terjual >= $d->qty) {
                    $product->decrement('total_terjual', $d->qty);
                } else {
                    $product->update(['total_terjual' => 0]);
                }

                $freshStock = $product->fresh()->stok;

                Stock::create([
                    'product_id' => $product->id,
                    'tipe'       => 'masuk',
                    'qty'        => $d->qty,
                    'sisa_stok'  => $freshStock,
                    'keterangan' => $reason,
                    'user_id'    => $userId,
                ]);

                Log::info("Stock restored for Product #{$product->id} (+{$d->qty}) - Order #{$order->order_number}. Reason: {$reason}");
            }
        }
    }

    /**
     * Cek dan kirim notifikasi jika stok produk mendekati batas minimum
     */
    public static function checkLowStockAlert(Product $product): void
    {
        $stokSekarang = (int) $product->stok;
        $stokMin = (int) ($product->stok_min ?: 5);

        if ($stokSekarang <= $stokMin) {
            try {
                $statusMsg = $stokSekarang <= 0 ? "HABIS (0)" : "tersisa {$stokSekarang} {$product->satuan}";
                Notification::sendToAdmins(
                    "Peringatan Stok Menipis ⚠️",
                    "Produk '{$product->nama_produk}' saat ini {$statusMsg} (Batas minimum: {$stokMin}). Segera lakukan penambahan stok!",
                    "/admin/products",
                    $product->unit_id
                );
            } catch (\Throwable $e) {
                Log::warning("Gagal mengirim notifikasi stok menipis: " . $e->getMessage());
            }
        }
    }

    /**
     * Batalkan otomatis pesanan yang belum dibayar melewati batas waktu (default 24 jam)
     * dan kembalikan seluruh stok barangnya.
     */
    public static function cancelExpiredOrders(int $hours = 24): int
    {
        $expiredThreshold = now()->subHours($hours);

        $expiredOrders = Order::with('details')
            ->whereIn('status', ['menunggu_pembayaran', 'pending'])
            ->where('created_at', '<=', $expiredThreshold)
            ->get();

        $count = 0;

        foreach ($expiredOrders as $order) {
            DB::beginTransaction();
            try {
                // 1. Kembalikan stok
                self::restoreOrderStock(
                    $order,
                    "Pengembalian stok otomatis (Pesanan #{$order->order_number} kadaluarsa > {$hours} jam)"
                );

                // 2. Ubah status pesanan
                $order->update([
                    'status'  => 'dibatalkan',
                    'catatan' => trim(($order->catatan ? $order->catatan . ' | ' : '') . "Dibatalkan otomatis oleh sistem karena melewati batas waktu pembayaran {$hours} jam."),
                ]);

                // 3. Ubah status payment jika ada
                if ($order->payment) {
                    $order->payment->update([
                        'status'  => 'ditolak',
                        'catatan' => 'Kedaluwarsa (Batas waktu pembayaran habis)',
                    ]);
                }

                // 4. Notifikasi ke user
                try {
                    Notification::create([
                        'user_id' => $order->user_id,
                        'judul'   => 'Pesanan Kedaluwarsa ⌛',
                        'pesan'   => "Pesanan #{$order->order_number} telah dibatalkan otomatis karena melewati batas waktu pembayaran {$hours} jam. Stok telah dikembalikan ke etalase.",
                        'link'    => "/pesanan/{$order->id}",
                        'is_read' => false,
                    ]);
                } catch (\Throwable $ne) {
                    Log::warning("Notification expired order error: " . $ne->getMessage());
                }

                DB::commit();
                $count++;
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Gagal membatalkan pesanan kedaluwarsa #{$order->id}: " . $e->getMessage());
            }
        }

        return $count;
    }
}
