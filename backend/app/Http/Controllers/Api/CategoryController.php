<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Banner;
use App\Models\Setting;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['products' => function ($q) {
            $q->where('status', 'aktif');
        }])->get();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $categories,
        ]);
    }
}
