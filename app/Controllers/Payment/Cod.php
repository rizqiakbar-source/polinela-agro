<?php

namespace App\Controllers\Payment;

use App\Controllers\BaseController;
use App\Models\OrderModel;

class Cod extends BaseController
{
    /**
     * Panduan & konfirmasi bayar di tempat (COD) di area kampus Polinela
     */
    public function index($orderNumber = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $orderModel = new OrderModel();
        $order = $orderNumber ? $orderModel->where('order_number', $orderNumber)->first() : null;

        $data = [
            'title' => 'Panduan Bayar di Tempat (COD) - Polinela Agro Digital',
            'order' => $order,
        ];

        return view('frontend/pembayaran', $data);
    }
}
