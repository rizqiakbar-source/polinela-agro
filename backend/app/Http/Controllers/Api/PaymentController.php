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

        // Check ownership if not admin
        $user = $request->user();
        if ($user && $user->role === 'konsumen' && $order->user_id !== $user->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki akses ke pesanan ini.',
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

        // Extract internal order ID or Order Number
        // e.g. "ORD-20260929-123-172760000" or "ORDER-5-172760000"
        $parts = explode('-', $orderIdRaw);
        $orderIdentifier = $parts[0] ?? $orderIdRaw;
        if (count($parts) >= 3 && $parts[0] === 'ORD') {
            $orderIdentifier = "{$parts[0]}-{$parts[1]}-{$parts[2]}";
        }

        $order = Order::where('order_number', $orderIdentifier)
            ->orWhere('id', $orderIdentifier)
            ->first();

        if (!$order) {
            Log::warning("Midtrans Webhook: Order not found for identifier '{$orderIdentifier}'");
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
                $order->status = 'dibatalkan';
                $order->save();

                if ($order->payment) {
                    $order->payment->status = 'ditolak';
                    $order->payment->save();
                }

                DB::commit();
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
     */
    public function finishPayment(Request $request, $id)
    {
        $order = Order::with(['payment'])->findOrFail($id);

        $oldStatus = $order->status;
        $order->status = 'diproses';
        $order->save();

        $payment = Payment::firstOrNew(['order_id' => $order->id]);
        $payment->metode = $request->input('payment_type', 'midtrans_snap');
        $payment->status = 'lunas';
        $payment->verified_at = now();
        $payment->catatan = 'Pembayaran otomatis diverifikasi via Midtrans Snap';
        $payment->save();

        // Trigger WhatsApp & Email Notification di Background (Instant response ke UI)
        dispatch(function () use ($order, $oldStatus) {
            try {
                NotificationService::notifyOrderStatusChanged($order, $oldStatus);
            } catch (\Exception $ne) {
                Log::error('Notification dispatch error in finishPayment: ' . $ne->getMessage());
            }
        })->afterResponse();

        return response()->json([
            'status'  => 'success',
            'message' => 'Pembayaran Midtrans berhasil diverifikasi.',
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
                    $order->status = 'diproses';
                    $order->save();

                    $payment = Payment::firstOrNew(['order_id' => $order->id]);
                    $payment->metode = "midtrans_{$paymentType}";
                    $payment->status = 'lunas';
                    $payment->verified_at = now();
                    $payment->catatan = 'Pembayaran otomatis diverifikasi via Midtrans Sync';
                    $payment->no_transaksi = $midtransTxId;
                    $payment->save();

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
