<?php

namespace App\Controllers;

use App\Models\CartModel;
use App\Models\ProdukModel;

class Keranjang extends BaseController
{
    protected $cartModel;
    protected $produkModel;

    public function __construct()
    {
        $this->cartModel   = new CartModel();
        $this->produkModel = new ProdukModel();
    }

    public function index()
    {
        if (!session()->get('logged_in')) {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu untuk mengakses keranjang belanja.');
            return redirect()->to(base_url('login'));
        }

        $userId = session()->get('user_id');
        $items  = $this->cartModel->getUserCart($userId);

        $subtotal = 0;
        $totalBerat = 0;
        foreach ($items as $item) {
            $subtotal += ($item['harga'] * $item['qty']);
            $totalBerat += ($item['berat_gram'] * $item['qty']);
        }

        $data = [
            'title'       => 'Keranjang Belanja - Polinela Agro Digital',
            'items'       => $items,
            'subtotal'    => $subtotal,
            'total_berat' => $totalBerat,
        ];

        return view('frontend/keranjang', $data);
    }

    public function add()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success'  => false,
                'redirect' => base_url('login'),
                'message'  => 'Silakan login terlebih dahulu.'
            ]);
        }

        $productId = (int) $this->request->getPost('product_id');
        $qty       = (int) ($this->request->getPost('qty') ?: 1);

        $product = $this->produkModel->find($productId);
        if (!$product || $product['status'] !== 'aktif') {
            return $this->response->setJSON(['success' => false, 'message' => 'Produk tidak tersedia.']);
        }

        if ($qty > $product['stok']) {
            return $this->response->setJSON(['success' => false, 'message' => 'Stok produk tidak mencukupi (Tersedia: ' . $product['stok'] . ').']);
        }

        $userId = session()->get('user_id');
        $existing = $this->cartModel->where('user_id', $userId)
                                   ->where('product_id', $productId)
                                   ->first();

        if ($existing) {
            $newQty = $existing['qty'] + $qty;
            if ($newQty > $product['stok']) {
                return $this->response->setJSON(['success' => false, 'message' => 'Total kuantitas di keranjang melebihi stok yang tersedia.']);
            }
            $this->cartModel->update($existing['id'], ['qty' => $newQty]);
        } else {
            $this->cartModel->insert([
                'user_id'    => $userId,
                'product_id' => $productId,
                'qty'        => $qty,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $totalItems = $this->cartModel->where('user_id', $userId)->countAllResults();

        return $this->response->setJSON([
            'success'     => true,
            'message'     => 'Produk berhasil ditambahkan ke keranjang!',
            'cart_count'  => $totalItems,
        ]);
    }

    public function update()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $cartId = (int) $this->request->getPost('cart_id');
        $qty    = max(1, (int) $this->request->getPost('qty'));

        $cart = $this->cartModel->find($cartId);
        if (!$cart || $cart['user_id'] != session()->get('user_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Item keranjang tidak valid.']);
        }

        $product = $this->produkModel->find($cart['product_id']);
        if ($qty > $product['stok']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Stok tidak mencukupi. Maksimum pembelian: ' . $product['stok']
            ]);
        }

        $this->cartModel->update($cartId, ['qty' => $qty]);

        $itemSubtotal = $product['harga'] * $qty;

        // Recalculate total
        $userId = session()->get('user_id');
        $items  = $this->cartModel->getUserCart($userId);
        $totalSubtotal = 0;
        foreach ($items as $it) {
            $totalSubtotal += ($it['harga'] * $it['qty']);
        }

        return $this->response->setJSON([
            'success'       => true,
            'item_subtotal' => format_rupiah($itemSubtotal),
            'total_subtotal'=> format_rupiah($totalSubtotal),
            'raw_total'     => $totalSubtotal,
        ]);
    }

    public function delete($cartId)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $cart = $this->cartModel->find($cartId);
        if ($cart && $cart['user_id'] == session()->get('user_id')) {
            $this->cartModel->delete($cartId);
            session()->setFlashdata('success', 'Produk berhasil dihapus dari keranjang.');
        }

        return redirect()->to(base_url('keranjang'));
    }

    public function getCount()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['count' => 0]);
        }
        $count = $this->cartModel->where('user_id', session()->get('user_id'))->countAllResults();
        return $this->response->setJSON(['count' => $count]);
    }
}
