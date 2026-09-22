<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\KategoriModel;
use App\Models\UnitModel;
use App\Models\ProductImageModel;
use App\Models\ReviewModel;

class Produk extends BaseController
{
    protected $produkModel;
    protected $kategoriModel;
    protected $unitModel;
    protected $reviewModel;

    public function __construct()
    {
        $this->produkModel   = new ProdukModel();
        $this->kategoriModel = new KategoriModel();
        $this->unitModel     = new UnitModel();
        $this->reviewModel   = new ReviewModel();
    }

    public function index()
    {
        $filter = [
            'category'  => $this->request->getGet('kategori'),
            'unit_id'   => $this->request->getGet('unit'),
            'search'    => $this->request->getGet('q'),
            'sort'      => $this->request->getGet('sort') ?: 'latest',
            'min_price' => $this->request->getGet('min_price'),
            'max_price' => $this->request->getGet('max_price'),
            'limit'     => 24,
        ];

        $products   = $this->produkModel->getCatalog($filter);
        $categories = $this->kategoriModel->getCategoriesWithCount();
        $units      = $this->unitModel->where('is_active', 1)->findAll();

        $data = [
            'title'        => 'Katalog Produk Perkebunan - Polinela Agro Digital',
            'products'     => $products,
            'categories'   => $categories,
            'units'        => $units,
            'filter'       => $filter,
            'total_found'  => count($products),
        ];

        return view('frontend/katalog', $data);
    }

    public function detail($slug)
    {
        $product = $this->produkModel->getDetailBySlug($slug);

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
        }

        $imageModel = new ProductImageModel();
        $images     = $imageModel->where('product_id', $product['id'])->findAll();
        $reviews    = $this->reviewModel->getProductReviews($product['id']);

        // Related products in same category
        $related = $this->produkModel->where('category_id', $product['category_id'])
                                     ->where('id !=', $product['id'])
                                     ->where('status', 'aktif')
                                     ->where('deleted_at IS NULL')
                                     ->findAll(4);

        $data = [
            'title'    => $product['nama_produk'] . ' - Polinela Agro Digital',
            'product'  => $product,
            'images'   => $images,
            'reviews'  => $reviews,
            'related'  => $related,
        ];

        return view('frontend/detail_produk', $data);
    }

    public function apiSearch()
    {
        $q = $this->request->getGet('term');
        if (!$q) {
            return $this->response->setJSON([]);
        }

        $products = $this->produkModel->like('nama_produk', $q)
                                      ->where('status', 'aktif')
                                      ->where('deleted_at IS NULL')
                                      ->findAll(6);

        $results = [];
        foreach ($products as $p) {
            $results[] = [
                'id'    => $p['id'],
                'nama'  => $p['nama_produk'],
                'slug'  => $p['slug'],
                'harga' => format_rupiah($p['harga']),
                'stok'  => $p['stok'],
                'satuan'=> $p['satuan'],
                'foto'  => base_url('assets/img/products/' . ($p['gambar_utama'] ?: 'default-product.png')),
                'url'   => base_url('produk/' . $p['slug']),
            ];
        }

        return $this->response->setJSON($results);
    }
}
