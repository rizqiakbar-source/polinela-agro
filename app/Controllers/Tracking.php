<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\ShippingModel;

class Tracking extends BaseController
{
    public function index()
    {
        $resi = trim($this->request->getGet('resi') ?? '');
        $order = null;
        $shipping = null;

        if ($resi) {
            $orderModel = new OrderModel();
            $shippingModel = new ShippingModel();

            // Cari via order_number atau no_resi
            $order = $orderModel->where('order_number', $resi)->first();
            if ($order) {
                $shipping = $shippingModel->where('order_id', $order['id'])->first();
            } else {
                $shipping = $shippingModel->where('no_resi', $resi)->first();
                if ($shipping) {
                    $order = $orderModel->find($shipping['order_id']);
                }
            }
        }

        $data = [
            'title'    => 'Lacak Pengiriman Pesanan - Polinela Agro Digital',
            'resi'     => $resi,
            'order'    => $order,
            'shipping' => $shipping,
        ];

        return view('frontend/tracking', $data);
    }
}
