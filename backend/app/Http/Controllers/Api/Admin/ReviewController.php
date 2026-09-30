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

        $reviews = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $reviews,
        ]);
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Ulasan berhasil dihapus.',
        ]);
    }
}
