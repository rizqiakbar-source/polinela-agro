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
            $payment = $paymentModel->where('order_id', $order['id'])->first();
            $relatedPayments = ($payment && !empty($payment['no_transaksi'])) 
                ? $paymentModel->where('no_transaksi', $payment['no_transaksi'])->findAll() 
                : ($payment ? [$payment] : []);

            foreach ($relatedPayments as $rp) {
                $paymentModel->update($rp['id'], [
                    'status'       => 'lunas',
                    'verified_at'  => date('Y-m-d H:i:s'),
                    'catatan'      => 'Pembayaran terverifikasi otomatis via Payment Gateway',
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);

                $orderModel->update($rp['order_id'], [
                    'status'     => 'diproses',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                $relOrder = $orderModel->find($rp['order_id']);
                if ($relOrder) {
                    Notification::send(
                        $relOrder['user_id'],
                        'Pembayaran Terkonfirmasi',
                        "Pembayaran untuk pesanan #{$relOrder['order_number']} telah berhasil diverifikasi. Pesanan Anda segera diproses!",
                        base_url('pesanan/detail/' . $relOrder['order_number'])
                    );
                    log_system_activity('Payment Callback', 'Payment', "Pesanan #{$relOrder['order_number']} lunas via gateway webhook.");
                }
            }
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            $paymentModel->where('order_id', $order['id'])->set([
                'status'     => 'ditolak',
                'updated_at' => date('Y-m-d H:i:s'),
            ])->update();
        }

        return $this->response->setJSON(['status' => 'success']);
    }
}
