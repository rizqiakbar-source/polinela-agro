<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['unit', 'category', 'images'])
            ->where('status', 'aktif');

        // Filter Unit Toko (supports unit_id or unit)
        $unit = $request->get('unit_id') ?? $request->get('unit');
        if (!empty($unit)) {
            if (is_numeric($unit)) {
                $query->where('unit_id', $unit);
            } else {
                $query->whereHas('unit', fn($q) => $q->where('slug', $unit));
            }
        }

        // Filter Kategori (supports category_id or kategori)
        $kategori = $request->get('category_id') ?? $request->get('kategori');
        if (!empty($kategori)) {
            if (is_numeric($kategori)) {
                $query->where('category_id', $kategori);
            } else {
                $query->whereHas('category', fn($q) => $q->where('slug', $kategori));
            }
        }

        // Pencarian Nama / Deskripsi (supports search or q)
        $search = $request->get('search') ?? $request->get('q');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->get('sort', 'terbaru');
        match ($sort) {
            'termurah'  => $query->orderBy('harga', 'asc'),
            'termahal'  => $query->orderBy('harga', 'desc'),
            'terlaris'  => $query->orderBy('total_terjual', 'desc'),
            default     => $query->orderBy('id', 'desc'),
        };

        $perPage = (int) $request->get('per_page', 12);
        $paginator = $query->paginate($perPage);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $paginator,
        ]);
    }

    public function show($slugOrId)
    {
        $query = Product::with(['unit', 'category', 'images', 'reviews.user'])
            ->where('status', 'aktif');

        if (is_numeric($slugOrId)) {
            $product = $query->where('id', $slugOrId)->first();
        } else {
            $product = $query->where('slug', $slugOrId)->first();
        }

        if (!$product) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        // Related Products from same unit
        $related = Product::with(['unit', 'category'])
            ->where('unit_id', $product->unit_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'aktif')
            ->limit(4)
            ->get();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => [
                'product' => $product,
                'related' => $related,
            ],
        ]);
    }

    public function addReview(Request $request, $id)
    {
        $user = $request->user();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'required|string|max:1000',
        ]);

        $product = Product::findOrFail($id);

        $review = \App\Models\Review::updateOrCreate(
            [
                'product_id' => $product->id,
                'user_id'    => $user->id,
            ],
            [
                'rating'       => $validated['rating'],
                'ulasan'       => $validated['ulasan'],
                'is_published' => true,
            ]
        );

        // Update product average rating
        $avg = \App\Models\Review::where('product_id', $product->id)->avg('rating');
        $product->rating_avg = round($avg, 1);
        $product->save();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Ulasan Anda berhasil dikirim!',
            'data'    => $review->load('user'),
        ]);
    }
}

