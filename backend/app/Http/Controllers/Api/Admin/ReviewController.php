<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Review::with(['user', 'product.unit'])->orderBy('id', 'desc');

        if ($user->role === 'admin_unit' && $user->unit_id) {
            $query->whereHas('product', fn($q) => $q->where('unit_id', $user->unit_id));
        }

        $reviews = $query->paginate($request->get('per_page', 15))->withQueryString();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $reviews,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $review = Review::with('product')->findOrFail($id);

        // Anti-IDOR: Admin Unit hanya berhak menghapus ulasan pada produk unit usahanya sendiri
        if ($user->role === 'admin_unit' && (!empty($user->unit_id) && $review->product && (int) $review->product->unit_id !== (int) $user->unit_id)) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk menghapus ulasan produk unit lain.',
            ], 403);
        }

        $review->delete();

        ActivityLog::create([
            'user_id'   => $user->id,
            'aksi'      => 'Hapus Ulasan',
            'modul'     => 'Ulasan',
            'ip_address'=> $request->ip(),
            'user_agent'=> $request->userAgent(),
            'deskripsi' => "Menghapus ulasan #{$id} pada produk #{$review->product_id}",
        ]);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Ulasan berhasil dihapus.',
        ]);
    }
}
