<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Otorisasi kepemilikan pesanan (Anti-IDOR)
     */
    private function canAccessOrder(Order $order, $user): bool
    {
        if (!$user) {
            return false;
        }

        // Superadmin & Pimpinan memiliki hak akses penuh
        if (in_array($user->role, ['superadmin', 'pimpinan'])) {
            return true;
        }

        // Admin Unit hanya boleh mengakses pesanan unit kelolaannya
        if ($user->role === 'admin_unit') {
            return !empty($user->unit_id) && (int) $order->unit_id === (int) $user->unit_id;
        }

        // Konsumen / Pelanggan hanya boleh mengakses pesanannya sendiri
        return (int) $order->user_id === (int) $user->id;
    }

    /**
     * Dapatkan Snap Token Midtrans untuk pesanan
     */
    public function getSnapToken(Request $request, $id)
    {
        $order = Order::with(['details', 'shipping', 'user', 'unit', 'payment'])
            ->where(function ($q) use ($id, $request) {
                $q->where('id', $id)
                  ->orWhere('order_number', $id);
            })
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        // Otorisasi Anti-IDOR
        if (!$this->canAccessOrder($order, $request->user())) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses ke pesanan ini.',
            ], 403);
        }

        $snapToken = MidtransService::createSnapToken($order);

        return response()->json([
            'status'     => 'success',
            'data'       => [
                'order_id'   => $order->id,
                'order_no'   => $order->order_number,
                'snap_token' => $snapToken,
                'client_key' => MidtransService::getClientKey(),
                'snap_url'   => MidtransService::getSnapJsUrl(),
            ],
            'snap_token' => $snapToken,
            'client_key' => MidtransService::getClientKey(),
            'snap_url'   => MidtransService::getSnapJsUrl(),
        ]);
    }

    /**
     * Webhook / Callback Handler dari Midtrans
     */
    public function handleCallback(Request $request)
    {
        $payload = $request->all();
        Log::info('Midtrans Webhook Received:', $payload);

        $orderIdRaw        = $payload['order_id'] ?? '';
        $statusCode        = $payload['status_code'] ?? '';
        $grossAmount       = $payload['gross_amount'] ?? '';
        $signatureKey      = $payload['signature_key'] ?? '';
        $transactionStatus = $payload['transaction_status'] ?? '';
        $paymentType       = $payload['payment_type'] ?? 'midtrans';

        // Validasi Keamanan Signature Key Midtrans (SHA512 - Anti-Bypass)
        $serverKey = MidtransService::getServerKey();
        if (!empty($serverKey)) {
            if (empty($signatureKey)) {
                Log::warning("Midtrans Webhook REJECTED: Missing signature key for Order identifier '{$orderIdRaw}'");
                return response()->json(['status' => 'error', 'message' => 'Signature key is required.'], 403);
            }

            $expectedSignature = hash('sha512', $orderIdRaw . $statusCode . $grossAmount . $serverKey);
            if (!hash_equals($expectedSignature, (string) $signatureKey)) {
                Log::warning("Midtrans Webhook REJECTED: Invalid signature key for Order identifier '{$orderIdRaw}'");
                return response()->json(['status' => 'error', 'message' => 'Invalid signature key.'], 403);
            }
        }

        // Resolve all target orders (supports consolidated multi-unit snap_order_id & TRX- prefix)
        $targetOrders = $this->resolveOrdersFromWebhook($orderIdRaw);

        if ($targetOrders->isEmpty()) {
            Log::warning("Midtrans Webhook: Order not found for identifier '{$orderIdRaw}'");
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        DB::beginTransaction();
        try {
            if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
                $transitioned = $this->markOrdersAsPaid(
                    $targetOrders,
                    $paymentType,
                    'Pembayaran otomatis diverifikasi via Midtrans Webhook',
                    $orderIdRaw
                );
                DB::commit();

                foreach ($transitioned as $entry) {
                    try {
                        NotificationService::notifyOrderStatusChanged($entry['order'], $entry['old_status']);
                    } catch (\Exception $ne) {
                        Log::error('Notification dispatch error in webhook: ' . $ne->getMessage());
                    }
                }
                Log::info("Midtrans Webhook marked " . count($transitioned) . " order(s) LUNAS for '{$orderIdRaw}'.");
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $this->cancelMidtransOrders($targetOrders, $transactionStatus);
                DB::commit();
                Log::info("Midtrans Webhook marked {$targetOrders->count()} order(s) DIBATALKAN for '{$orderIdRaw}'.");
            } else {
                DB::commit();
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Midtrans Callback Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Konfirmasi Pembayaran Berhasil Setelah Snap Selesai di Client
     * Dilengkapi verifikasi langsung ke server Midtrans untuk mencegah manipulasi client
     */
    public function finishPayment(Request $request, $id)
    {
        $order = Order::with(['payment', 'details'])->findOrFail($id);

        // Otorisasi Anti-IDOR
        if (!$this->canAccessOrder($order, $request->user())) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses ke pesanan ini.'], 403);
        }

        $targetOrders = $this->resolveSiblingOrdersForPayment($order);
        $midtransTxId = $order->snap_order_id ?? $order->payment?->no_transaksi ?? $order->order_number;
        $midtransStatus = MidtransService::checkTransactionStatus($midtransTxId);

        if (!$midtransStatus && $order->order_number) {
            $midtransStatus = MidtransService::checkTransactionStatus($order->order_number);
        }

        $trxStatus = $midtransStatus['transaction_status'] ?? '';
        $paymentType = $midtransStatus['payment_type'] ?? $request->input('payment_type', 'midtrans_snap');

        // Hanya ubah status menjadi lunas jika Midtrans benar-benar mengonfirmasi settlement / capture
        if (in_array($trxStatus, ['settlement', 'capture'])) {
            $transitioned = DB::transaction(fn () => $this->markOrdersAsPaid(
                $targetOrders,
                $paymentType,
                'Pembayaran terverifikasi via Midtrans Snap',
                $midtransTxId
            ));

            dispatch(function () use ($transitioned) {
                foreach ($transitioned as $entry) {
                    try {
                        NotificationService::notifyOrderStatusChanged($entry['order'], $entry['old_status']);
                    } catch (\Exception $ne) {
                        Log::error('Notification dispatch error in finishPayment: ' . $ne->getMessage());
                    }
                }
            })->afterResponse();

            $freshOrder = $order->fresh()->load(['payment', 'details', 'shipping', 'unit']);
            return response()->json([
                'status'         => 'success',
                'message'        => 'Pembayaran Midtrans berhasil diverifikasi dan pesanan sedang diproses.',
                'order'          => $freshOrder,
                'sibling_orders' => Order::with(['unit', 'payment', 'shipping'])
                    ->whereIn('id', $targetOrders->pluck('id'))
                    ->where('id', '!=', $order->id)
                    ->get(),
            ]);
        }

        return response()->json([
            'status'  => 'pending',
            'message' => 'Status pembayaran Anda saat ini sedang diproses. Mohon selesaikan pembayaran.',
            'order'   => $order->fresh()->load(['payment', 'details', 'shipping', 'unit']),
        ]);
    }

    /**
     * Sinkronisasi Real-Time Status Midtrans ke Database
     */
    public function checkAndSyncStatus(Request $request, $id)
    {
        $order = Order::with(['payment', 'details'])->where('id', $id)->orWhere('order_number', $id)->first();
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        // Otorisasi Anti-IDOR
        if (!$this->canAccessOrder($order, $request->user())) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses ke data pesanan ini.'], 403);
        }

        $targetOrders = $this->resolveSiblingOrdersForPayment($order);

        // Cari transaksi di Midtrans
        $midtransTxId = $order->snap_order_id ?? $order->payment?->no_transaksi ?? $order->order_number;
        $midtransStatus = MidtransService::checkTransactionStatus($midtransTxId);

        if (!$midtransStatus && $order->order_number) {
            $midtransStatus = MidtransService::checkTransactionStatus($order->order_number);
        }

        if ($midtransStatus) {
            $trxStatus = $midtransStatus['transaction_status'] ?? '';
            $paymentType = $midtransStatus['payment_type'] ?? 'midtrans';

            if (in_array($trxStatus, ['settlement', 'capture'])) {
                $transitioned = DB::transaction(fn () => $this->markOrdersAsPaid(
                    $targetOrders,
                    $paymentType,
                    'Pembayaran otomatis diverifikasi via Midtrans Sync',
                    $midtransTxId
                ));

                if (!empty($transitioned)) {
                    dispatch(function () use ($transitioned) {
                        foreach ($transitioned as $entry) {
                            try {
                                NotificationService::notifyOrderStatusChanged($entry['order'], $entry['old_status']);
                            } catch (\Exception $ne) {
                                Log::error('Notification dispatch error in checkAndSyncStatus: ' . $ne->getMessage());
                            }
                        }
                    })->afterResponse();
                }
            } elseif (in_array($trxStatus, ['cancel', 'deny', 'expire'])) {
                DB::transaction(fn () => $this->cancelMidtransOrders($targetOrders, $trxStatus));
            }
        }

        return response()->json([
            'status'          => 'success',
            'order'           => $order->fresh()->load(['payment', 'details', 'shipping', 'unit']),
            'sibling_orders'  => Order::with(['unit', 'payment', 'shipping'])
                ->whereIn('id', $targetOrders->pluck('id'))
                ->where('id', '!=', $order->id)
                ->get(),
            'midtrans_status' => $midtransStatus,
        ]);
    }

    /**
     * Kumpulkan seluruh sub-order yang berbagi satu pembayaran Midtrans (snap_order_id / no_transaksi TRX-...).
     */
    private function resolveSiblingOrdersForPayment(Order $order)
    {
        if (!empty($order->snap_order_id)) {
            $bySnap = Order::with(['payment', 'details'])
                ->where('snap_order_id', $order->snap_order_id)
                ->where('user_id', $order->user_id)
                ->get();
            if ($bySnap->count() > 1) {
                return $bySnap;
            }
        }

        $sharedTrx = $order->payment?->no_transaksi;
        if (!empty($sharedTrx) && str_starts_with($sharedTrx, 'TRX-')) {
            $orderIds = Payment::where('no_transaksi', $sharedTrx)->pluck('order_id');
            if ($orderIds->count() > 1) {
                $siblings = Order::with(['payment', 'details'])
                    ->whereIn('id', $orderIds)
                    ->where('user_id', $order->user_id)
                    ->get();
                if ($siblings->isNotEmpty()) {
                    return $siblings;
                }
            }
        }

        return collect([$order]);
    }

    /**
     * Resolve order(s) dari order_id callback/webhook Midtrans.
     */
    private function resolveOrdersFromWebhook(string $orderIdRaw)
    {
        $bySnap = Order::with(['details', 'payment'])
            ->where('snap_order_id', $orderIdRaw)
            ->get();
        if ($bySnap->isNotEmpty()) {
            return $bySnap;
        }

        if (str_starts_with($orderIdRaw, 'TRX-')) {
            $baseTrx = preg_replace('/-\d{8,}$/', '', $orderIdRaw);
            $orderIds = Payment::where('no_transaksi', $baseTrx)->pluck('order_id');
            if ($orderIds->isNotEmpty()) {
                return Order::with(['details', 'payment'])->whereIn('id', $orderIds)->get();
            }
        }

        $order = Order::with(['details', 'payment'])
            ->where('order_number', $orderIdRaw)
            ->orWhere('id', is_numeric($orderIdRaw) ? (int) $orderIdRaw : 0)
            ->first();

        if (!$order) {
            $parts = explode('-', $orderIdRaw);
            if (count($parts) >= 3 && in_array($parts[0], ['PNA', 'ORD'])) {
                $orderIdentifier = "{$parts[0]}-{$parts[1]}-{$parts[2]}";
                $order = Order::with(['details', 'payment'])->where('order_number', $orderIdentifier)->first();
            } elseif (count($parts) >= 2 && $parts[0] === 'ORDER') {
                $order = Order::with(['details', 'payment'])->where('id', (int) $parts[1])->first();
            }
        }

        return $order ? collect([$order]) : collect();
    }

    /**
     * Tandai seluruh order dalam transaksi Midtrans sebagai lunas/diproses tanpa menimpa no_transaksi TRX-...
     */
    private function markOrdersAsPaid($orders, string $paymentType, string $catatan, string $fallbackTrxId): array
    {
        $transitioned = [];
        foreach ($orders as $ord) {
            if (in_array($ord->status, ['diproses', 'dikirim', 'selesai'])) {
                continue;
            }
            $oldStatus = $ord->status;
            $ord->status = 'diproses';
            $ord->save();

            $payment = Payment::firstOrNew(['order_id' => $ord->id]);
            $payment->metode = str_starts_with($paymentType, 'midtrans_') ? $paymentType : "midtrans_{$paymentType}";
            $payment->status = 'lunas';
            $payment->verified_at = now();
            $payment->catatan = $catatan;
            if (empty($payment->no_transaksi)) {
                $payment->no_transaksi = $fallbackTrxId;
            }
            $payment->save();

            $transitioned[] = ['order' => $ord->fresh(), 'old_status' => $oldStatus];
        }
        return $transitioned;
    }

    /**
     * Batalkan seluruh order pada transaksi Midtrans yang ditolak/kedaluwarsa & pulihkan stok.
     */
    private function cancelMidtransOrders($orders, string $transactionStatus): void
    {
        foreach ($orders as $ord) {
            $prevStatus = $ord->status;
            if ($prevStatus === 'dibatalkan' || in_array($prevStatus, ['diproses', 'dikirim', 'selesai'])) {
                continue;
            }
            $ord->status = 'dibatalkan';
            $ord->save();

            if ($ord->payment) {
                $ord->payment->status = 'ditolak';
                $ord->payment->catatan = "Pembayaran Midtrans ditolak / kadaluarsa ({$transactionStatus})";
                $ord->payment->save();
            }

            \App\Services\StockService::restoreOrderStock(
                $ord,
                "Pengembalian stok dari Midtrans ({$transactionStatus}) #{$ord->order_number}"
            );
        }
    }
}
