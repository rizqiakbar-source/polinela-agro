<?php

namespace App\Controllers;

use App\Models\WishlistModel;

class Wishlist extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $userId = session()->get('user_id');
        $wishlistModel = new WishlistModel();
        $items = $wishlistModel->getUserWishlist($userId);

        $data = [
            'title' => 'Wishlist Produk Favorit - Polinela Agro Digital',
            'items' => $items,
        ];

        return view('frontend/wishlist', $data);
    }

    public function toggle()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'redirect' => base_url('login')]);
        }

        $userId = session()->get('user_id');
        $productId = (int) $this->request->getPost('product_id');

        $wishlistModel = new WishlistModel();
        $existing = $wishlistModel->where('user_id', $userId)
                                  ->where('product_id', $productId)
                                  ->first();

        if ($existing) {
            $wishlistModel->delete($existing['id']);
            $status = 'removed';
            $message = 'Produk dihapus dari wishlist.';
        } else {
            $wishlistModel->insert([
                'user_id'    => $userId,
                'product_id' => $productId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $status = 'added';
            $message = 'Produk berhasil ditambahkan ke wishlist!';
        }

        $count = $wishlistModel->where('user_id', $userId)->countAllResults();

        return $this->response->setJSON([
            'success' => true,
            'status'  => $status,
            'message' => $message,
            'count'   => $count,
        ]);
    }
}
