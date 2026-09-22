<?php

namespace App\Controllers\Payment;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Libraries\Xendit as XenditLib;

class Xendit extends BaseController
{
    /**
     * Buat invoice payment link Xendit
     */
    public function invoice($orderNumber)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $orderModel = new OrderModel();
        $order = $orderModel->where('order_number', $orderNumber)->first();

        if (!$order) {
            return $this->response->setJSON(['error' => 'Pesanan tidak ditemukan'])->setStatusCode(404);
        }

        $invoice = XenditLib::createInvoice([
            'external_id'  => $order['order_number'],
            'amount'       => (int) $order['grand_total'],
            'payer_email'  => session()->get('user_email'),
        ]);

        return $this->response->setJSON($invoice);
    }
}
