<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $banners = Banner::orderBy('urutan', 'asc')->get();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $banners,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'     => 'required|string|max:200',
            'subjudul'  => 'nullable|string|max:255',
            'gambar'    => 'required|string|max:255',
            'link'      => 'nullable|string|max:255',
            'urutan'    => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['urutan'] = $validated['urutan'] ?? 1;
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $banner = Banner::create($validated);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Banner promosi berhasil ditambahkan.',
            'data'    => $banner,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $validated = $request->validate([
            'judul'     => 'sometimes|required|string|max:200',
            'subjudul'  => 'nullable|string|max:255',
            'gambar'    => 'nullable|string|max:255',
            'link'      => 'nullable|string|max:255',
            'urutan'    => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $banner->update($validated);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Banner promosi berhasil diperbarui.',
            'data'    => $banner,
        ]);
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Banner berhasil dihapus.',
        ]);
    }
}
