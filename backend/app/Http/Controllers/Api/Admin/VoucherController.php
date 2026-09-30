<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $vouchers = Voucher::orderBy('id', 'desc')->get();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $vouchers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'         => 'required|string|max:50|unique:vouchers,kode',
            'nama'         => 'required|string|max:100',
            'tipe'         => 'required|in:fixed,persen',
            'diskon'       => 'required|numeric|min:0',
            'min_belanja'  => 'nullable|numeric|min:0',
            'max_diskon'   => 'nullable|numeric|min:0',
            'kuota'        => 'nullable|integer|min:0',
            'tgl_mulai'    => 'nullable|date',
            'tgl_berakhir' => 'nullable|date',
            'is_active'    => 'boolean',
        ]);

        $validated['kode'] = strtoupper(trim($validated['kode']));
        $validated['min_belanja'] = $validated['min_belanja'] ?? 0;
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $voucher = Voucher::create($validated);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Voucher berhasil dibuat.',
            'data'    => $voucher,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $voucher = Voucher::findOrFail($id);

        $validated = $request->validate([
            'kode'         => 'sometimes|required|string|max:50|unique:vouchers,kode,' . $id,
            'nama'         => 'sometimes|required|string|max:100',
            'tipe'         => 'sometimes|required|in:fixed,persen',
            'diskon'       => 'sometimes|required|numeric|min:0',
            'min_belanja'  => 'nullable|numeric|min:0',
            'max_diskon'   => 'nullable|numeric|min:0',
            'kuota'        => 'nullable|integer|min:0',
            'tgl_mulai'    => 'nullable|date',
            'tgl_berakhir' => 'nullable|date',
            'is_active'    => 'boolean',
        ]);

        if (isset($validated['kode'])) {
            $validated['kode'] = strtoupper(trim($validated['kode']));
        }

        $voucher->update($validated);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Voucher berhasil diperbarui.',
            'data'    => $voucher,
        ]);
    }

    public function destroy($id)
    {
        $voucher = Voucher::findOrFail($id);
        $voucher->delete();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Voucher berhasil dihapus.',
        ]);
    }
}
