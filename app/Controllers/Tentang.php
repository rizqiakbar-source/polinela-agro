<?php

namespace App\Controllers;

use App\Models\UnitModel;

class Tentang extends BaseController
{
    public function index()
    {
        $unitModel = new UnitModel();
        $units = $unitModel->where('is_active', 1)->findAll();

        $data = [
            'title' => 'Tentang Polinela Agro Digital - Pasar Digital Produk Perkebunan Kampus',
            'units' => $units,
        ];

        return view('frontend/tentang', $data);
    }
}
