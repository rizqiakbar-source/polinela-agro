<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Category;
use App\Models\Banner;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::withCount(['products' => function ($q) {
            $q->where('status', 'aktif');
        }])->where('is_active', true)->get();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $units,
        ]);
    }

    public function show($slugOrId)
    {
        $query = Unit::with(['products' => function ($q) {
            $q->where('status', 'aktif');
        }]);

        if (is_numeric($slugOrId)) {
            $unit = $query->where('id', $slugOrId)->first();
        } else {
            $unit = $query->where('slug', $slugOrId)->first();
        }

        if (!$unit) {
            return response()->json(['success' => false, 'message' => 'Unit usaha tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $unit,
        ]);
    }
}
