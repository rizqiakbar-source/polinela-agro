<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\KategoriModel;
use App\Models\UnitModel;
use App\Models\BannerModel;
use App\Models\OrderModel;

class Home extends BaseController
{
    public function index()
    {
        $produkModel   = new ProdukModel();
        $kategoriModel = new KategoriModel();
        $unitModel     = new UnitModel();
        $bannerModel   = new BannerModel();
        $orderModel    = new OrderModel();

        $data = [
            'title'             => 'Polinela Agro Digital - Pasar Digital Produk Perkebunan Kampus',
            'banners'           => $bannerModel->getActiveBanners(),
            'categories'        => $kategoriModel->getCategoriesWithCount(),
            'featured_products' => $produkModel->getFeatured(8),
            'latest_products'   => $produkModel->getCatalog(['limit' => 8, 'sort' => 'latest']),
            'units'             => $unitModel->where('is_active', 1)->findAll(),
            'total_transaksi'   => $orderModel->countAllResults(),
            'total_produk'      => $produkModel->where('status', 'aktif')->countAllResults(),
        ];

        return view('frontend/home', $data);
    }
}
