<?php

namespace App\Controllers;

use App\Models\ReviewModel;
use App\Models\ProdukModel;

class Ulasan extends BaseController
{
    public function index()
    {
        $reviewModel = new ReviewModel();
        $reviews = $reviewModel->getAllReviewsWithDetails('approved');

        $userReviews = [];
        if (session()->get('logged_in')) {
            $userId = session()->get('user_id');
            $userReviews = $reviewModel->select('reviews.*, products.nama_produk, products.gambar_utama, products.slug')
                                       ->join('products', 'products.id = reviews.product_id', 'left')
                                       ->where('reviews.user_id', $userId)
                                       ->orderBy('reviews.id', 'DESC')
                                       ->findAll();
        }

        $data = [
            'title'        => 'Ulasan & Testimoni Pelanggan - Polinela Agro Digital',
            'reviews'      => $reviews,
            'user_reviews' => $userReviews,
        ];

        return view('frontend/ulasan', $data);
    }

    public function store()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $userId    = session()->get('user_id');
        $productId = (int) $this->request->getPost('product_id');
        $orderId   = (int) $this->request->getPost('order_id');
        $rating    = max(1, min(5, (int) $this->request->getPost('rating')));
        $komentar  = trim($this->request->getPost('komentar') ?? '');

        if (!$productId || empty($komentar)) {
            return redirect()->back()->with('error', 'Silakan isi rating dan komentar ulasan Anda.');
        }

        $reviewModel = new ReviewModel();
        $reviewModel->insert([
            'product_id' => $productId,
            'order_id'   => $orderId ?: null,
            'user_id'    => $userId,
            'rating'     => $rating,
            'komentar'   => $komentar,
            'status'     => 'approved',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Update rata-rata rating di produk
        $allReviews = $reviewModel->where('product_id', $productId)->where('status', 'approved')->findAll();
        if (!empty($allReviews)) {
            $avg = array_sum(array_column($allReviews, 'rating')) / count($allReviews);
            $produkModel = new ProdukModel();
            $produkModel->update($productId, ['rating_avg' => round($avg, 2)]);
        }

        log_system_activity('Beri Ulasan', 'Produk', "User ID {$userId} memberikan ulasan rating {$rating} bintang.");

        return redirect()->back()->with('success', 'Terima kasih atas ulasan dan rating yang Anda berikan!');
    }
}
