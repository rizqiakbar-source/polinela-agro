<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipping;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role;
        $unitId = $user->unit_id;

        $query = Order::with(['unit', 'user', 'payment', 'shipping', 'details.product']);

        if ($role === 'admin_unit' && $unitId) {
            $query->where('unit_id', $unitId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $searchTerm = $request->get('search') ?? $request->get('q');
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('order_number', 'like', "%{$searchTerm}%")
                  ->orWhereHas('user', fn($u) => $u->where('nama', 'like', "%{$searchTerm}%")->orWhere('email', 'like', "%{$searchTerm}%"))
                  ->orWhereHas('shipping', fn($s) => $s->where('nama_penerima', 'like', "%{$searchTerm}%")->orWhere('no_hp', 'like', "%{$searchTerm}%"));
            });
        }

        $orders = $query->orderBy('id', 'desc')->paginate($request->get('per_page', 50));

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $orders->items(),
            'orders'  => $orders->items(),
            'meta'    => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $order = Order::with(['unit', 'user', 'payment.verifier', 'shipping', 'details.product'])->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($user->role === 'admin_unit' && $order->unit_id != $user->unit_id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $order,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();
        $order = Order::with(['shipping', 'unit', 'user', 'payment', 'details'])->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($user->role === 'admin_unit' && $order->unit_id != $user->unit_id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'status'  => 'required|in:pending,menunggu_pembayaran,menunggu_verifikasi,diproses,dikirim,selesai,dibatalkan',
            'no_resi' => 'nullable|string',
        ]);

        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        if ($order->shipping) {
            if ($request->filled('no_resi')) {
                $order->shipping->no_resi = $request->no_resi;
            }
            if ($request->status === 'dikirim') {
                $order->shipping->status = 'dikirim';
            } elseif ($request->status === 'selesai') {
                $order->shipping->status = 'selesai';
            } elseif ($request->status === 'diproses') {
                $order->shipping->status = 'dikemas';
            }
            $order->shipping->save();
        }

        $catatanAdmin = $request->get('catatan_admin') ?? $request->get('catatan');

        // Execute notifications safely without interrupting HTTP stream
        try {
            \App\Services\NotificationService::notifyOrderStatusChanged($order, $oldStatus, $catatanAdmin);
        } catch (\Throwable $ne) {
            \Illuminate\Support\Facades\Log::warning("Status notification warning: " . $ne->getMessage());
        }

        try {
            ActivityLog::log('Update Status Pesanan', 'Pesanan', "Mengubah status pesanan #{$order->order_number} menjadi {$request->status}");
        } catch (\Throwable $le) {
            \Illuminate\Support\Facades\Log::warning("Activity log warning: " . $le->getMessage());
        }

        $whatsappUrl = null;
        try {
            $whatsappUrl = \App\Services\NotificationService::generateWhatsAppUrl($order, 'customer', $catatanAdmin);
        } catch (\Throwable $we) {
            \Illuminate\Support\Facades\Log::warning("WhatsApp URL generation warning: " . $we->getMessage());
        }

        return response()->json([
            'status'       => 'success',
            'success'      => true,
            'message'      => 'Status pesanan berhasil diperbarui & notifikasi otomatis telah dikirim.',
            'whatsapp_url' => $whatsappUrl,
            'data'         => $order->fresh(['shipping', 'payment', 'unit', 'user']),
        ]);
    }
}
