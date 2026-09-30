<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role;
        $unitId = $user->unit_id;

        $query = Payment::with(['order.unit', 'order.user', 'verifier']);

        if ($role === 'admin_unit' && $unitId) {
            $query->whereHas('order', fn($q) => $q->where('unit_id', $unitId));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('order', fn($oq) => $oq->where('order_number', 'like', "%{$search}%"));
            });
        }

        $payments = $query->orderBy('id', 'desc')->paginate($request->get('per_page', 10));

        // Append helper attributes
        $payments->getCollection()->transform(function ($p) {
            $p->jumlah = $p->order ? (float) $p->order->grand_total : 0;
            if ($p->order) {
                $p->order->no_pesanan = $p->order->order_number;
            }
            return $p;
        });

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $payments,
            'meta'    => [
                'current_page' => $payments->currentPage(),
                'last_page'    => $payments->lastPage(),
                'per_page'     => $payments->perPage(),
                'total'        => $payments->total(),
            ],
        ]);
    }

    public function verifikasi(Request $request, $id = null)
    {
        $user = $request->user();
        $paymentId = $id ?? $request->payment_id ?? $request->id;

        $payment = Payment::with('order')->find($paymentId);
        if (!$payment || !$payment->order) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Data pembayaran tidak ditemukan.'], 404);
        }

        // Strict unit check
        if ($user->role === 'admin_unit' && $payment->order->unit_id != $user->unit_id) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Akses ditolak: Anda hanya berhak memverifikasi pembayaran pesanan unit usaha Anda sendiri.',
            ], 403);
        }

        $catatan = $request->catatan ?: 'Pembayaran telah diverifikasi sah oleh admin unit.';

        $payment->update([
            'status'      => 'lunas',
            'verified_by' => $user->id,
            'verified_at' => now(),
            'catatan'     => $catatan,
        ]);

        $payment->order->update([
            'status' => 'diproses',
        ]);

        // Trigger Notifikasi Otomatis Email & In-App Status Lunas
        try {
            \App\Services\NotificationService::notifyOrderStatusChanged(
                $payment->order, 
                'menunggu_verifikasi', 
                'Pembayaran telah diverifikasi sah (LUNAS). Pesanan Anda sedang dipersiapkan dan dikemas.'
            );
        } catch (\Exception $ne) {
            \Illuminate\Support\Facades\Log::warning("Payment verify notification error: " . $ne->getMessage());
        }

        ActivityLog::log('Verifikasi Pembayaran', 'Pembayaran', "Menyetujui verifikasi pembayaran pesanan #{$payment->order->order_number}");

        $whatsappUrl = \App\Services\NotificationService::generateWhatsAppUrl($payment->order, 'customer');

        return response()->json([
            'status'       => 'success',
            'success'      => true,
            'message'      => "Pembayaran pesanan #{$payment->order->order_number} berhasil disetujui (LUNAS). Notifikasi telah dikirim ke pembeli.",
            'whatsapp_url' => $whatsappUrl,
            'data'         => $payment->fresh(['order', 'verifier']),
        ]);
    }

    public function tolak(Request $request, $id = null)
    {
        $user = $request->user();
        $paymentId = $id ?? $request->payment_id ?? $request->id;
        $catatan = $request->alasan ?? $request->catatan ?? 'Bukti pembayaran tidak sesuai atau tidak valid.';

        $payment = Payment::with('order')->find($paymentId);
        if (!$payment || !$payment->order) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Data pembayaran tidak ditemukan.'], 404);
        }

        if ($user->role === 'admin_unit' && $payment->order->unit_id != $user->unit_id) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Akses ditolak: Anda hanya berhak menolak pembayaran pesanan unit usaha Anda sendiri.',
            ], 403);
        }

        $payment->update([
            'status'      => 'ditolak',
            'verified_by' => $user->id,
            'verified_at' => now(),
            'catatan'     => $catatan,
        ]);

        $payment->order->update([
            'status' => 'menunggu_pembayaran',
        ]);

        // In-App Notification Penolakan
        try {
            \App\Models\Notification::create([
                'user_id' => $payment->order->user_id,
                'judul'   => 'Bukti Pembayaran Ditolak ⚠️',
                'pesan'   => "Bukti pembayaran pesanan #{$payment->order->order_number} ditolak. Alasan: {$catatan}. Silakan unggah ulang bukti yang sah.",
                'link'    => "/pesanan/{$payment->order->id}",
                'is_read' => false,
            ]);
        } catch (\Exception $ne) {
            \Illuminate\Support\Facades\Log::warning("Payment reject notification error: " . $ne->getMessage());
        }

        ActivityLog::log('Tolak Pembayaran', 'Pembayaran', "Menolak pembayaran pesanan #{$payment->order->order_number}. Alasan: {$catatan}");

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => "Bukti pembayaran pesanan #{$payment->order->order_number} telah ditolak.",
            'data'    => $payment->fresh(['order', 'verifier']),
        ]);
    }
}
