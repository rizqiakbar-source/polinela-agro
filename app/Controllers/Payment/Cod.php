<?php

namespace App\Controllers\Payment;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\PaymentModel;
use App\Libraries\Notification;

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

    public function konfirmasiCod($orderNumber)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $orderModel = new OrderModel();
        $order = $orderModel->where('order_number', $orderNumber)->first();

        if (!$order || $order['user_id'] != session()->get('user_id')) {
            return redirect()->back()->with('error', 'Pesanan tidak valid.');
        }

        $orderModel->update($order['id'], [
            'status'     => 'diproses',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $paymentModel = new PaymentModel();
        $paymentModel->where('order_id', $order['id'])->set([
            'status'     => 'pending',
            'catatan'    => 'Pembayaran COD akan ditagihkan saat pesanan diserahkan.',
            'updated_at' => date('Y-m-d H:i:s'),
        ])->update();

        Notification::sendToAdmins('Pesanan COD Dikonfirmasi', "Konsumen mengonfirmasi pesanan COD #{$order['order_number']}.", base_url('admin/pesanan/detail/' . $order['id']), $order['unit_id'] ? (int) $order['unit_id'] : null);
        log_system_activity('Konfirmasi COD', 'Pesanan', "Konfirmasi pesanan COD #{$order['order_number']}");

        return redirect()->to(base_url('pesanan/detail/' . $order['order_number']))->with('success', 'Pesanan COD berhasil dikonfirmasi dan segera disiapkan oleh unit perkebunan!');
    }
}
