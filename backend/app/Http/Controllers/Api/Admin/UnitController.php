<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::withCount('products')->orderBy('id', 'asc')->get();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $units,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_unit' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'pj_nama'   => 'nullable|string|max:100',
            'kontak'    => 'nullable|string|max:50',
            'lokasi'    => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['nama_unit']);
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $unit = Unit::create($validated);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Unit TEFA berhasil ditambahkan.',
            'data'    => $unit,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $validated = $request->validate([
            'nama_unit' => 'sometimes|required|string|max:100',
            'deskripsi' => 'nullable|string',
            'pj_nama'   => 'nullable|string|max:100',
            'kontak'    => 'nullable|string|max:50',
            'lokasi'    => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['nama_unit'])) {
            $validated['slug'] = Str::slug($validated['nama_unit']);
        }

        $unit->update($validated);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Unit TEFA berhasil diperbarui.',
            'data'    => $unit,
        ]);
    }

    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Unit TEFA berhasil dihapus.',
        ]);
    }
}
