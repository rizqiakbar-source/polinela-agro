<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $query = Order::with(['unit', 'payment', 'shipping', 'details.product'])
            ->where('user_id', $userId);

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'menunggu_pembayaran') {
                $query->whereIn('status', ['menunggu_pembayaran', 'pending']);
            } else {
                $query->where('status', $status);
            }
        }

        $orders = $query->orderBy('id', 'desc')->paginate($request->get('per_page', 10));

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $orders,
            'meta'    => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    public function show(Request $request, $orderNumber)
    {
        $user = $request->user();

        $query = Order::with(['unit', 'payment', 'shipping', 'details.product', 'user']);

        if (is_numeric($orderNumber)) {
            $order = $query->where('id', $orderNumber)->orWhere('order_number', $orderNumber)->first();
        } else {
            $order = $query->where('order_number', $orderNumber)->first();
        }

        if (!$order) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        // Hak akses: hanya pemilik atau admin
        if ($order->user_id !== $user->id && !in_array($user->role, ['superadmin', 'admin_unit', 'pimpinan'])) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        // Auto-sync payment status jika pesanan sudah diproses/dikirim/selesai
        if (in_array($order->status, ['diproses', 'dikirim', 'selesai'])) {
            if ($order->payment && $order->payment->status !== 'lunas') {
                $order->payment->update([
                    'status'        => 'lunas',
                    'tanggal_bayar' => $order->payment->tanggal_bayar ?? now(),
                ]);
                $order->load('payment');
            }
        }

        // Cek sibling sub-orders (multi-store single payment)
        $siblingOrders = [];
        $totalTrxAmount = (float) $order->grand_total;

        if ($order->payment && !empty($order->payment->no_transaksi)) {
            $relatedPayments = Payment::where('no_transaksi', $order->payment->no_transaksi)->get();
            if ($relatedPayments->count() > 1) {
                $orderIds = $relatedPayments->pluck('order_id')->toArray();
                $siblingOrders = Order::with(['unit', 'payment', 'shipping'])
                    ->whereIn('id', $orderIds)
                    ->where('id', '!=', $order->id)
                    ->where('user_id', $order->user_id)
                    ->get();
                $totalTrxAmount = (float) Order::whereIn('id', $orderIds)->sum('grand_total');
            }
        }

        return response()->json([
            'status'           => 'success',
            'success'          => true,
            'data'             => [
                'order'            => $order,
                'sibling_orders'   => $siblingOrders,
                'total_trx_amount' => $totalTrxAmount,
            ],
            'order'            => $order,
            'sibling_orders'   => $siblingOrders,
            'total_trx_amount' => $totalTrxAmount,
        ]);
    }

    public function uploadBukti(Request $request, $id = null)
    {
        $orderId = $id ?? $request->get('order_id');

        $request->validate([
            'bukti_bayar' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $user = $request->user();
        $query = Order::where('user_id', $user->id);
        if (is_numeric($orderId)) {
            $order = $query->where(fn($q) => $q->where('id', $orderId)->orWhere('order_number', $orderId))->first();
        } else {
            $order = $query->where('order_number', $orderId)->first();
        }

        if (!$order) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $file = $request->file('bukti_bayar');
        $filename = 'bukti_' . $order->order_number . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        $uploadDir = public_path('uploads/bukti_bayar');
        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }
        $file->move($uploadDir, $filename);

        $payment = Payment::where('order_id', $order->id)->first();
        $bank = $request->get('bank_asal') ?? $payment?->bank ?? 'Transfer Bank';
        $atasNama = $request->get('atas_nama_pengirim') ?? $user->nama;

        // Jika bagian dari multi-store single payment, update seluruh order di transaksi yang sama
        if ($payment && !empty($payment->no_transaksi)) {
            $relatedPayments = Payment::where('no_transaksi', $payment->no_transaksi)->get();

            foreach ($relatedPayments as $rp) {
                $rp->update([
                    'bukti_bayar' => $filename,
                    'bank'        => $bank,
                    'atas_nama'   => $atasNama,
                    'status'      => 'menunggu_konfirmasi',
                    'updated_at'  => now(),
                ]);

                $relOrder = Order::find($rp->order_id);
                if ($relOrder) {
                    $relOrder->update(['status' => 'menunggu_verifikasi']);
                }
            }
        } else {
            if ($payment) {
                $payment->update([
                    'bukti_bayar' => $filename,
                    'bank'        => $bank,
                    'atas_nama'   => $atasNama,
                    'status'      => 'menunggu_konfirmasi',
                ]);
            }
            $order->update(['status' => 'menunggu_verifikasi']);
        }

        ActivityLog::log('Upload Bukti', 'Pembayaran', "Upload bukti bayar untuk pesanan #{$order->order_number}");

        // Notifikasi ke Admin Unit
        try {
            Notification::sendToAdmins(
                "Bukti Pembayaran Baru Diunggah 💳",
                "Konsumen telah mengunggah bukti pembayaran untuk pesanan #{$order->order_number}. Silakan verifikasi.",
                "/admin/payments",
                $order->unit_id
            );
        } catch (\Exception $ne) {
            \Illuminate\Support\Facades\Log::warning("Proof upload notification error: " . $ne->getMessage());
        }

        $whatsappUrl = \App\Services\NotificationService::generateWhatsAppUrl($order, 'admin');

        return response()->json([
            'status'       => 'success',
            'success'      => true,
            'message'      => 'Bukti pembayaran berhasil diunggah! Mohon menunggu verifikasi dari Admin Unit.',
            'bukti_bayar'  => $filename,
            'whatsapp_url' => $whatsappUrl,
        ]);
    }

    public function konfirmasiTerima(Request $request, $id)
    {
        $user = $request->user();
        $order = Order::where(fn($q) => $q->where('id', $id)->orWhere('order_number', $id))
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $order->update(['status' => 'selesai']);

        ActivityLog::log('Konfirmasi Terima', 'Pesanan', "Konsumen mengonfirmasi pesanan #{$order->order_number} telah diterima.");

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Terima kasih! Pesanan telah selesai.',
            'data'    => $order,
        ]);
    }

    public function batalkan(Request $request, $id)
    {
        $user = $request->user();
        $order = Order::with('details')
            ->where(fn($q) => $q->where('id', $id)->orWhere('order_number', $id))
            ->where('user_id', $user->id)
            ->first();

        if (!$order || !in_array($order->status, ['pending', 'menunggu_pembayaran'])) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Pesanan tidak dapat dibatalkan.'], 400);
        }

        // Kembalikan stok produk
        foreach ($order->details as $d) {
            $product = Product::find($d->product_id);
            if ($product) {
                $product->increment('stok', $d->qty);
                $product->decrement('total_terjual', $d->qty);

                Stock::create([
                    'product_id' => $product->id,
                    'tipe'       => 'masuk',
                    'qty'        => $d->qty,
                    'sisa_stok'  => $product->fresh()->stok,
                    'keterangan' => "Pengembalian stok dari pembatalan pesanan #{$order->order_number}",
                    'user_id'    => $user->id,
                ]);
            }
        }

        $order->update(['status' => 'dibatalkan']);

        ActivityLog::log('Batal Pesanan', 'Pesanan', "Konsumen membatalkan pesanan #{$order->order_number}");

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Pesanan berhasil dibatalkan dan stok produk telah dikembalikan.',
        ]);
    }

    public function whatsappLink(Request $request, $id)
    {
        $user = $request->user();
        $target = $request->get('target', 'admin'); // 'admin' | 'customer'

        $order = Order::with(['unit', 'user', 'shipping', 'payment', 'details'])
            ->where(fn($q) => $q->where('id', $id)->orWhere('order_number', $id))
            ->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $url = \App\Services\NotificationService::generateWhatsAppUrl($order, $target);

        return response()->json([
            'status'       => 'success',
            'whatsapp_url' => $url,
            'url'          => $url,
        ]);
    }

    public function resendNotification(Request $request, $id)
    {
        $order = Order::with(['unit', 'user', 'shipping', 'payment', 'details'])
            ->where(fn($q) => $q->where('id', $id)->orWhere('order_number', $id))
            ->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $channel = $request->get('channel', 'all'); // 'email' | 'whatsapp' | 'all'
        $emailSent = false;
        $waUrl = \App\Services\NotificationService::generateWhatsAppUrl($order, 'customer');

        if (in_array($channel, ['email', 'all'])) {
            $emailSent = \App\Services\NotificationService::sendOrderEmail($order, 'order_created');
        }

        return response()->json([
            'status'       => 'success',
            'message'      => 'Notifikasi berhasil diproses!',
            'email_sent'   => $emailSent,
            'whatsapp_url' => $waUrl,
        ]);
    }
}

