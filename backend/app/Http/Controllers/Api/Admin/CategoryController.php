<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('products')->orderBy('id', 'asc')->get();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100',
            'deskripsi'     => 'nullable|string',
            'icon'          => 'nullable|string|max:50',
            'is_active'     => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['nama_kategori']);
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $category = Category::create($validated);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan.',
            'data'    => $category,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'nama_kategori' => 'sometimes|required|string|max:100',
            'deskripsi'     => 'nullable|string',
            'icon'          => 'nullable|string|max:50',
            'is_active'     => 'boolean',
        ]);

        if (isset($validated['nama_kategori'])) {
            $validated['slug'] = Str::slug($validated['nama_kategori']);
        }

        $category->update($validated);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Kategori berhasil diperbarui.',
            'data'    => $category,
        ]);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
