<?php

namespace App\Controllers\Payment;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\PaymentModel;
use App\Libraries\Notification;

class Callback extends BaseController
{
    /**
     * Webhook Handler untuk Midtrans & Gateway Otomatis
     */
    public function midtrans()
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);

        if (empty($data) || empty($data['order_id'])) {
            return $this->response->setJSON(['status' => 'invalid_payload'])->setStatusCode(400);
        }

        $orderNumber       = $data['order_id'];
        $transactionStatus = $data['transaction_status'] ?? '';

        $orderModel   = new OrderModel();
        $paymentModel = new PaymentModel();

        $order = $orderModel->where('order_number', $orderNumber)->first();
        if (!$order) {
            return $this->response->setJSON(['status' => 'order_not_found'])->setStatusCode(404);
        }

        if (in_array($transactionStatus, ['capture', 'settlement'])) {
            $paymentModel->where('order_id', $order['id'])->set([
                'status'       => 'lunas',
                'verified_at'  => date('Y-m-d H:i:s'),
                'catatan'      => 'Pembayaran terverifikasi otomatis via Payment Gateway',
                'updated_at'   => date('Y-m-d H:i:s'),
            ])->update();

            $orderModel->update($order['id'], [
                'status'     => 'diproses',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            Notification::send(
                $order['user_id'],
                'Pembayaran Terkonfirmasi',
                "Pembayaran untuk pesanan #{$orderNumber} telah berhasil diverifikasi. Pesanan Anda segera diproses!",
                base_url('pesanan/detail/' . $orderNumber)
            );

            log_system_activity('Payment Callback', 'Payment', "Pesanan #{$orderNumber} lunas via gateway webhook.");
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            $paymentModel->where('order_id', $order['id'])->set([
                'status'     => 'ditolak',
                'updated_at' => date('Y-m-d H:i:s'),
            ])->update();
        }

        return $this->response->setJSON(['status' => 'success']);
    }
}
