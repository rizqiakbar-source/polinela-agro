<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $items = Cart::with(['product.unit', 'product.category'])
            ->where('user_id', $userId)
            ->whereHas('product', fn($q) => $q->whereNull('deleted_at'))
            ->get();

        // Group by unit toko
        $grouped = [];
        $totalBelanja = 0;
        $totalBerat = 0;
        $totalItems = 0;

        foreach ($items as $item) {
            $product = $item->product;
            if (!$product) continue;

            $unitName = $product->unit->nama_unit ?? 'Unit Usaha Polinela';
            $unitId = $product->unit_id ?? 1;

            $qty = $item->qty ?? 1;
            $subtotal = $product->harga * $qty;
            $berat = ($product->berat_gram ?? 500) * $qty;

            $totalBelanja += $subtotal;
            $totalBerat += $berat;
            $totalItems += $qty;

            if (!isset($grouped[$unitId])) {
                $grouped[$unitId] = [
                    'unit_id'       => $unitId,
                    'unit_name'     => $unitName,
                    'nama_unit'     => $unitName,
                    'unit_subtotal' => 0,
                    'items'         => [],
                ];
            }

            $grouped[$unitId]['unit_subtotal'] += $subtotal;
            $grouped[$unitId]['items'][] = [
                'id'           => $item->id,
                'product_id'   => $product->id,
                'produk_id'    => $product->id,
                'product'      => $product,
                'nama_produk'  => $product->nama_produk,
                'slug'         => $product->slug,
                'harga'        => $product->harga,
                'satuan'       => $product->satuan,
                'berat_gram'   => $product->berat_gram,
                'gambar_utama' => $product->gambar_utama,
                'stok'         => $product->stok,
                'qty'          => $qty,
                'jumlah'       => $qty,
                'subtotal'     => $subtotal,
            ];
        }

        $summary = [
            'total_items'      => $totalItems,
            'subtotal'         => $totalBelanja,
            'total_belanja'    => $totalBelanja,
            'total_berat_gram' => $totalBerat,
            'total_units'      => count($grouped),
        ];

        return response()->json([
            'status'        => 'success',
            'success'       => true,
            'data'          => [
                'groups'  => array_values($grouped),
                'summary' => $summary,
            ],
            'grouped_items' => array_values($grouped),
            'summary'       => $summary,
            'raw_items'     => $items,
            'total_belanja' => $totalBelanja,
            'total_berat'   => $totalBerat,
            'total_count'   => $totalItems,
        ]);
    }

    public function add(Request $request)
    {
        $productId = $request->get('product_id') ?? $request->get('produk_id');
        $qty = (int) ($request->get('qty') ?? $request->get('jumlah') ?? 1);

        if (!$productId) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Produk ID harus diisi.'], 422);
        }

        $userId = $request->user()->id;
        $product = Product::find($productId);
        if (!$product || $product->status !== 'aktif') {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Produk tidak tersedia.'], 400);
        }

        if ($qty > $product->stok) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => "Stok produk tidak mencukupi (Tersedia: {$product->stok}).",
            ], 400);
        }

        $existing = Cart::where('user_id', $userId)->where('product_id', $productId)->first();

        if ($existing) {
            $newQty = $existing->qty + $qty;
            if ($newQty > $product->stok) {
                return response()->json([
                    'status'  => 'error',
                    'success' => false,
                    'message' => "Total kuantitas melebihi stok yang tersedia.",
                ], 400);
            }
            $existing->qty = $newQty;
            $existing->save();
        } else {
            Cart::create([
                'user_id'    => $userId,
                'product_id' => $productId,
                'qty'        => $qty,
            ]);
        }

        $count = Cart::where('user_id', $userId)->sum('qty');

        return response()->json([
            'status'     => 'success',
            'success'    => true,
            'message'    => 'Produk berhasil ditambahkan ke keranjang!',
            'cart_count' => $count,
        ]);
    }

    public function updateQty(Request $request, $id = null)
    {
        $cartId = $id ?? $request->get('cart_id') ?? $request->get('id');
        $qty = (int) ($request->get('qty') ?? $request->get('jumlah') ?? 1);

        $userId = $request->user()->id;
        $cart = Cart::with('product')->where('id', $cartId)->where('user_id', $userId)->first();

        if (!$cart) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Item keranjang tidak ditemukan.'], 404);
        }

        if ($qty > $cart->product->stok) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => "Stok maksimal yang tersedia adalah {$cart->product->stok}.",
            ], 400);
        }

        $cart->qty = $qty;
        $cart->save();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Kuantitas diperbarui.',
        ]);
    }

    public function remove(Request $request, $id)
    {
        $userId = $request->user()->id;
        Cart::where('id', $id)->where('user_id', $userId)->delete();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Produk dihapus dari keranjang.',
        ]);
    }

    public function count(Request $request)
    {
        $count = Cart::where('user_id', $request->user()->id)->sum('qty');
        return response()->json([
            'status'  => 'success',
            'success' => true,
            'count'   => $count,
        ]);
    }
}
