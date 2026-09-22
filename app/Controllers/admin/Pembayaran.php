<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Models\OrderModel;
use App\Libraries\Notification;

class Pembayaran extends BaseController
{
    protected $paymentModel;
    protected $orderModel;

    public function __construct()
    {
        $this->paymentModel = new PaymentModel();
        $this->orderModel   = new OrderModel();
    }

    public function index()
    {
        $status = $this->request->getGet('status');
        $payments = $this->paymentModel->getPaymentsWithOrder($status);

        $data = [
            'title'          => 'Verifikasi Pembayaran - Admin Polinela Agro Digital',
            'payments'       => $payments,
            'current_status' => $status,
        ];

        return view('admin/pembayaran/index', $data);
    }

    public function verifikasi()
    {
        $paymentId = (int) $this->request->getPost('payment_id');
        $catatan   = trim($this->request->getPost('catatan') ?? '');

        $payment = $this->paymentModel->find($paymentId);
        if (!$payment) {
            return redirect()->back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $order = $this->orderModel->find($payment['order_id']);

        // Update payment status ke lunas
        $this->paymentModel->update($paymentId, [
            'status'      => 'lunas',
            'verified_by' => session()->get('user_id'),
            'verified_at' => date('Y-m-d H:i:s'),
            'catatan'     => $catatan ?: 'Pembayaran telah diverifikasi sah oleh admin.',
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        // Update order status ke diproses
        $this->orderModel->update($payment['order_id'], [
            'status'     => 'diproses',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        Notification::send(
            $order['user_id'],
            'Pembayaran Terverifikasi',
            "Pembayaran untuk pesanan #{$order['order_number']} telah diverifikasi LUNAS. Pesanan Anda segera diproses unit perkebunan.",
            base_url('pesanan/detail/' . $order['order_number'])
        );

        log_system_activity('Verifikasi Pembayaran', 'Pembayaran', "Menyetujui verifikasi pembayaran pesanan #{$order['order_number']}");

        return redirect()->back()->with('success', "Pembayaran untuk pesanan #{$order['order_number']} berhasil disetujui (LUNAS).");
    }

    public function tolak()
    {
        $paymentId = (int) $this->request->getPost('payment_id');
        $catatan   = trim($this->request->getPost('catatan') ?? 'Bukti pembayaran tidak sesuai atau tidak valid.');

        $payment = $this->paymentModel->find($paymentId);
        if (!$payment) {
            return redirect()->back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $order = $this->orderModel->find($payment['order_id']);

        $this->paymentModel->update($paymentId, [
            'status'      => 'ditolak',
            'verified_by' => session()->get('user_id'),
            'verified_at' => date('Y-m-d H:i:s'),
            'catatan'     => $catatan,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->orderModel->update($payment['order_id'], [
            'status'     => 'pending',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        Notification::send(
            $order['user_id'],
            'Pembayaran Ditolak',
            "Pembayaran untuk pesanan #{$order['order_number']} ditolak: {$catatan}. Silakan unggah bukti pembayaran yang valid.",
            base_url('pesanan/detail/' . $order['order_number'])
        );

        log_system_activity('Tolak Pembayaran', 'Pembayaran', "Menolak bukti pembayaran pesanan #{$order['order_number']}. Alasan: {$catatan}");

        return redirect()->back()->with('warning', "Pembayaran untuk pesanan #{$order['order_number']} telah ditolak.");
    }
}
