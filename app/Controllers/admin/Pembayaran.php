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
        $role   = session()->get('user_role');
        $unitId = session()->get('user_unit_id');
        $status = $this->request->getGet('status');

        $payments = $this->paymentModel->getPaymentsWithOrder($status, ($role === 'admin_unit') ? $unitId : null);

        $data = [
            'title'          => 'Verifikasi Pembayaran - Admin Polinela Agro Digital',
            'payments'       => $payments,
            'current_status' => $status,
            'role'           => $role,
            'unit_id'        => $unitId,
        ];

        return view('admin/pembayaran/index', $data);
    }

    public function verifikasi()
    {
        $role      = session()->get('user_role');
        $myUnitId  = session()->get('user_unit_id');
        $paymentId = (int) $this->request->getPost('payment_id');
        $catatan   = trim($this->request->getPost('catatan') ?? '');

        $payment = $this->paymentModel->find($paymentId);
        if (!$payment) {
            return redirect()->back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $order = $this->orderModel->find($payment['order_id']);
        if (!$order) {
            return redirect()->back()->with('error', 'Data pesanan tidak ditemukan.');
        }

        // Validasi hak akses unit: Admin unit hanya boleh memverifikasi pesanan unit usahanya sendiri
        if ($role === 'admin_unit' && ((int) ($order['unit_id'] ?? 0) !== (int) $myUnitId)) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda hanya berhak memverifikasi pembayaran pesanan dari unit usaha Anda sendiri.');
        }

        // Update payment status ke lunas
        $this->paymentModel->update($paymentId, [
            'status'      => 'lunas',
            'verified_by' => session()->get('user_id'),
            'verified_at' => date('Y-m-d H:i:s'),
            'catatan'     => $catatan ?: 'Pembayaran telah diverifikasi sah oleh admin unit.',
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
        $role      = session()->get('user_role');
        $myUnitId  = session()->get('user_unit_id');
        $paymentId = (int) $this->request->getPost('payment_id');
        $catatan   = trim($this->request->getPost('catatan') ?? 'Bukti pembayaran tidak sesuai atau tidak valid.');

        $payment = $this->paymentModel->find($paymentId);
        if (!$payment) {
            return redirect()->back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $order = $this->orderModel->find($payment['order_id']);
        if (!$order) {
            return redirect()->back()->with('error', 'Data pesanan tidak ditemukan.');
        }

        // Validasi hak akses unit: Admin unit hanya boleh menolak pesanan unit usahanya sendiri
        if ($role === 'admin_unit' && ((int) ($order['unit_id'] ?? 0) !== (int) $myUnitId)) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda hanya berhak menolak pembayaran pesanan dari unit usaha Anda sendiri.');
        }

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
