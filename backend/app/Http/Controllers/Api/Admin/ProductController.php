<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Stock;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role;
        $unitId = $user->unit_id;

        $query = Product::with(['unit', 'category']);

        if ($role === 'admin_unit' && $unitId) {
            $query->where('unit_id', $unitId);
        }

        $search = $request->get('search') ?? $request->get('q');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('id', 'desc')->paginate($request->get('per_page', 20));

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $products,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $role = $user->role;
        $unitId = ($role === 'admin_unit') ? $user->unit_id : ($request->unit_id ?? $user->unit_id);

        $validated = $request->validate([
            'nama_produk' => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'harga'       => 'required|numeric|min:0',
            'satuan'      => 'nullable|string|max:50',
            'berat_gram'  => 'nullable|integer|min:1',
            'deskripsi'   => 'nullable|string',
            'gambar'      => 'nullable|image|max:5120',
        ]);

        $stok = $request->input('stok') ?? $request->input('stok_awal') ?? 10;
        $isActive = $request->has('is_active') ? $request->boolean('is_active') : true;

        $filename = 'default-product.png';
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = 'prod_' . time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
            $uploadDir = public_path('uploads/produk');
            if (!file_exists($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $file->move($uploadDir, $filename);
        }

        $slug = Str::slug($validated['nama_produk']) . '-' . Str::random(4);

        $product = Product::create([
            'unit_id'      => $unitId,
            'category_id'  => $validated['category_id'],
            'nama_produk'  => $validated['nama_produk'],
            'slug'         => $slug,
            'deskripsi'    => $validated['deskripsi'] ?? null,
            'harga'        => $validated['harga'],
            'stok'         => (int) $stok,
            'satuan'       => $validated['satuan'] ?? 'Pcs',
            'berat_gram'   => $validated['berat_gram'] ?? 500,
            'gambar_utama' => $filename,
            'status'       => $isActive ? 'aktif' : 'nonaktif',
            'total_terjual'=> 0,
        ]);

        // Catat stok awal
        if ($product->stok > 0) {
            Stock::create([
                'product_id' => $product->id,
                'tipe'       => 'masuk',
                'qty'        => $product->stok,
                'sisa_stok'  => $product->stok,
                'keterangan' => 'Stok awal produk baru',
                'user_id'    => $user->id,
            ]);
        }

        ActivityLog::create([
            'user_id'   => $user->id,
            'aksi'      => 'Tambah Produk',
            'modul'     => 'Produk',
            'ip_address'=> $request->ip(),
            'user_agent'=> $request->userAgent(),
            'deskripsi' => "Menambahkan produk '{$product->nama_produk}' ke unit #{$unitId}",
        ]);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Produk berhasil ditambahkan!',
            'data'    => $product->load(['unit', 'category']),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Produk tidak ditemukan.'], 404);
        }

        if ($user->role === 'admin_unit' && $product->unit_id != $user->unit_id) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'nama_produk' => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'harga'       => 'required|numeric|min:0',
            'satuan'      => 'nullable|string|max:50',
            'berat_gram'  => 'nullable|integer|min:1',
            'deskripsi'   => 'nullable|string',
            'gambar'      => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = 'prod_' . time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
            $uploadDir = public_path('uploads/produk');
            if (!file_exists($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $file->move($uploadDir, $filename);
            $product->gambar_utama = $filename;
        }

        $stokLama = $product->stok;
        $stokBaru = $request->has('stok') ? (int) $request->input('stok') : ($request->has('stok_awal') ? (int) $request->input('stok_awal') : $stokLama);

        $product->nama_produk = $validated['nama_produk'];
        $product->category_id = $validated['category_id'];
        $product->harga = $validated['harga'];
        $product->stok = $stokBaru;
        $product->satuan = $validated['satuan'] ?? $product->satuan;
        $product->berat_gram = $validated['berat_gram'] ?? $product->berat_gram;
        $product->deskripsi = $validated['deskripsi'] ?? $product->deskripsi;
        
        if ($request->has('is_active')) {
            $product->status = $request->boolean('is_active') ? 'aktif' : 'nonaktif';
        } elseif ($request->has('status')) {
            $product->status = $request->input('status');
        }

        if ($user->role === 'superadmin' && $request->filled('unit_id')) {
            $product->unit_id = $request->input('unit_id');
        }

        $product->save();

        // Catat penyesuaian stok jika berbeda
        if ($stokBaru !== $stokLama) {
            $selisih = $stokBaru - $stokLama;
            Stock::create([
                'product_id' => $product->id,
                'tipe'       => ($selisih > 0) ? 'masuk' : 'keluar',
                'qty'        => abs($selisih),
                'sisa_stok'  => $stokBaru,
                'keterangan' => 'Penyesuaian stok oleh admin',
                'user_id'    => $user->id,
            ]);
        }

        ActivityLog::create([
            'user_id'   => $user->id,
            'aksi'      => 'Edit Produk',
            'modul'     => 'Produk',
            'ip_address'=> $request->ip(),
            'user_agent'=> $request->userAgent(),
            'deskripsi' => "Memperbarui produk '{$product->nama_produk}' (Stok: {$stokBaru})",
        ]);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Produk berhasil diperbarui.',
            'data'    => $product->load(['unit', 'category']),
        ]);
    }

    public function updateStock(Request $request, $id)
    {
        $user = $request->user();
        $product = Product::findOrFail($id);

        if ($user->role === 'admin_unit' && $product->unit_id != $user->unit_id) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'stok' => 'required|integer|min:0',
        ]);

        $stokLama = $product->stok;
        $stokBaru = (int) $validated['stok'];
        $product->stok = $stokBaru;
        $product->save();

        if ($stokBaru !== $stokLama) {
            $selisih = $stokBaru - $stokLama;
            Stock::create([
                'product_id' => $product->id,
                'tipe'       => ($selisih > 0) ? 'masuk' : 'keluar',
                'qty'        => abs($selisih),
                'sisa_stok'  => $stokBaru,
                'keterangan' => 'Update stok cepat oleh admin unit',
                'user_id'    => $user->id,
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Stok produk berhasil diperbarui!',
            'data'    => $product,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Produk tidak ditemukan.'], 404);
        }

        if ($user->role === 'admin_unit' && $product->unit_id != $user->unit_id) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $nama = $product->nama_produk;
        $product->delete();

        ActivityLog::create([
            'user_id'   => $user->id,
            'aksi'      => 'Hapus Produk',
            'modul'     => 'Produk',
            'ip_address'=> $request->ip(),
            'user_agent'=> $request->userAgent(),
            'deskripsi' => "Menghapus produk '{$nama}'",
        ]);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Produk berhasil dihapus.',
        ]);
    }
}
