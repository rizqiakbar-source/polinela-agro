<?php

namespace App\Controllers\Payment;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Libraries\Midtrans as MidtransLib;

class Midtrans extends BaseController
{
    /**
     * Generate Snap Token Midtrans untuk checkout
     */
    public function token($orderNumber)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $orderModel = new OrderModel();
        $order = $orderModel->where('order_number', $orderNumber)->first();

        if (!$order) {
            return $this->response->setJSON(['error' => 'Pesanan tidak ditemukan'])->setStatusCode(404);
        }

        $snapToken = MidtransLib::getSnapToken([
            'order_id'     => $order['order_number'],
            'gross_amount' => (int) $order['grand_total'],
        ]);

        return $this->response->setJSON([
            'token'        => $snapToken,
            'order_number' => $order['order_number'],
        ]);
    }
}
