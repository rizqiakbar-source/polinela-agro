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

        // First try exact match or id
        $order = Order::with('details')
            ->where('order_number', $orderIdRaw)
            ->orWhere('id', $orderIdRaw)
            ->first();

        // If not found and order_id has timestamp suffix, strip it
        if (!$order) {
            $parts = explode('-', $orderIdRaw);
            if (count($parts) >= 3 && in_array($parts[0], ['PNA', 'ORD'])) {
                $orderIdentifier = "{$parts[0]}-{$parts[1]}-{$parts[2]}";
                $order = Order::with('details')->where('order_number', $orderIdentifier)->first();
            } elseif (count($parts) >= 2 && $parts[0] === 'ORDER') {
                $order = Order::with('details')->where('id', $parts[1])->first();
            }
        }

        if (!$order) {
            Log::warning("Midtrans Webhook: Order not found for identifier '{$orderIdRaw}'");
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        DB::beginTransaction();
        try {
            if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
                // Payment Success / Lunas
                $oldStatus = $order->status;
                $order->status = 'diproses';
                $order->save();

                $payment = Payment::firstOrNew(['order_id' => $order->id]);
                $payment->metode = "midtrans_{$paymentType}";
                $payment->status = 'lunas';
                $payment->verified_at = now();
                $payment->catatan = 'Pembayaran otomatis diverifikasi via Midtrans Webhook';
                $payment->no_transaksi = $orderIdRaw;
                $payment->save();

                DB::commit();

                // Trigger automated email, in-app, and WhatsApp notifications!
                try {
                    NotificationService::notifyOrderStatusChanged($order, $oldStatus);
                } catch (\Exception $ne) {
                    Log::error('Notification dispatch error in webhook: ' . $ne->getMessage());
                }

                Log::info("Order #{$order->id} automatically marked as LUNAS via Midtrans Webhook.");
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $prevStatus = $order->status;
                $order->status = 'dibatalkan';
                $order->save();

                if ($order->payment) {
                    $order->payment->status = 'ditolak';
                    $order->payment->catatan = "Pembayaran Midtrans ditolak / kadaluarsa ({$transactionStatus})";
                    $order->payment->save();
                }

                // Kembalikan stok barang jika pesanan sebelumnya belum dibatalkan
                if ($prevStatus !== 'dibatalkan') {
                    \App\Services\StockService::restoreOrderStock(
                        $order,
                        "Pengembalian stok dari Midtrans Callback ({$transactionStatus}) #{$order->order_number}"
                    );
                }

                DB::commit();
                Log::info("Order #{$order->id} marked as DIBATALKAN via Midtrans Webhook and stock restored.");
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

        $midtransTxId = $order->snap_order_id ?? $order->payment?->no_transaksi ?? $order->order_number;
        $midtransStatus = MidtransService::checkTransactionStatus($midtransTxId);

        if (!$midtransStatus && $order->order_number) {
            $midtransStatus = MidtransService::checkTransactionStatus($order->order_number);
        }

        $trxStatus = $midtransStatus['transaction_status'] ?? '';
        $paymentType = $midtransStatus['payment_type'] ?? $request->input('payment_type', 'midtrans_snap');

        // Hanya ubah status menjadi lunas jika Midtrans benar-benar mengonfirmasi settlement / capture
        if (in_array($trxStatus, ['settlement', 'capture'])) {
            $oldStatus = $order->status;
            DB::transaction(function () use ($order, $paymentType) {
                $order->status = 'diproses';
                $order->save();

                $payment = Payment::firstOrNew(['order_id' => $order->id]);
                $payment->metode = "midtrans_{$paymentType}";
                $payment->status = 'lunas';
                $payment->verified_at = now();
                $payment->catatan = 'Pembayaran terverifikasi via Midtrans Snap';
                $payment->save();
            });

            dispatch(function () use ($order, $oldStatus) {
                try {
                    NotificationService::notifyOrderStatusChanged($order, $oldStatus);
                } catch (\Exception $ne) {
                    Log::error('Notification dispatch error in finishPayment: ' . $ne->getMessage());
                }
            })->afterResponse();

            return response()->json([
                'status'  => 'success',
                'message' => 'Pembayaran Midtrans berhasil diverifikasi dan pesanan sedang diproses.',
                'order'   => $order->fresh()->load(['payment', 'details', 'shipping', 'unit']),
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
        $order = Order::with(['payment'])->where('id', $id)->orWhere('order_number', $id)->first();
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        // Otorisasi Anti-IDOR
        if (!$this->canAccessOrder($order, $request->user())) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses ke data pesanan ini.'], 403);
        }

        // Cari transaksi di Midtrans
        $midtransTxId = $order->snap_order_id ?? $order->payment?->no_transaksi ?? $order->order_number;
        $midtransStatus = MidtransService::checkTransactionStatus($midtransTxId);

        if (!$midtransStatus && $order->order_number) {
            // Coba prefix lain jika timestamp
            $midtransStatus = MidtransService::checkTransactionStatus($order->order_number);
        }

        if ($midtransStatus) {
            $trxStatus = $midtransStatus['transaction_status'] ?? '';
            $paymentType = $midtransStatus['payment_type'] ?? 'midtrans';

            if (in_array($trxStatus, ['settlement', 'capture'])) {
                if ($order->status !== 'diproses' && $order->status !== 'dikirim' && $order->status !== 'selesai') {
                    $oldStatus = $order->status;
                    DB::transaction(function () use ($order, $paymentType, $midtransTxId) {
                        $order->status = 'diproses';
                        $order->save();

                        $payment = Payment::firstOrNew(['order_id' => $order->id]);
                        $payment->metode = "midtrans_{$paymentType}";
                        $payment->status = 'lunas';
                        $payment->verified_at = now();
                        $payment->catatan = 'Pembayaran otomatis diverifikasi via Midtrans Sync';
                        $payment->no_transaksi = $midtransTxId;
                        $payment->save();
                    });

                    dispatch(function () use ($order, $oldStatus) {
                        try {
                            NotificationService::notifyOrderStatusChanged($order, $oldStatus);
                        } catch (\Exception $ne) {
                            Log::error('Notification dispatch error in checkAndSyncStatus: ' . $ne->getMessage());
                        }
                    })->afterResponse();
                }
            }
        }

        return response()->json([
            'status'          => 'success',
            'order'           => $order->fresh()->load(['payment', 'details', 'shipping', 'unit']),
            'midtrans_status' => $midtransStatus,
        ]);
    }
}
