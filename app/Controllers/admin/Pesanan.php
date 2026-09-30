<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\OrderDetailModel;
use App\Models\PaymentModel;
use App\Models\ShippingModel;
use App\Models\UserModel;
use App\Libraries\PdfGenerator;
use App\Libraries\Notification;

class Pesanan extends BaseController
{
    protected $orderModel;
    protected $orderDetailModel;
    protected $paymentModel;
    protected $shippingModel;

    public function __construct()
    {
        $this->orderModel       = new OrderModel();
        $this->orderDetailModel = new OrderDetailModel();
        $this->paymentModel     = new PaymentModel();
        $this->shippingModel    = new ShippingModel();
    }

    public function index()
    {
        $role   = session()->get('user_role');
        $unitId = session()->get('user_unit_id');
        $status = $this->request->getGet('status');

        $orders = $this->orderModel->getOrderWithRelations(null, null, ($role === 'admin_unit') ? $unitId : null);

        if ($status) {
            $orders = array_filter($orders, fn($o) => $o['status'] === $status);
        }

        $data = [
            'title'          => 'Kelola Pesanan - Admin Polinela Agro Digital',
            'orders'         => $orders,
            'current_status' => $status,
            'role'           => $role,
        ];

        return view('admin/pesanan/index', $data);
    }

    public function detail($id)
    {
        $role   = session()->get('user_role');
        $unitId = session()->get('user_unit_id');

        $order = $this->orderModel->find($id);
        if (!$order) {
            return redirect()->to(base_url('admin/pesanan'))->with('error', 'Pesanan tidak ditemukan.');
        }

        if ($role === 'admin_unit' && ((int) ($order['unit_id'] ?? 0) !== (int) $unitId)) {
            return redirect()->to(base_url('admin/pesanan'))->with('error', 'Akses ditolak: Pesanan ini berasal dari unit usaha lain.');
        }

        $details  = $this->orderDetailModel->getDetailsByOrderId($id);
        $payment  = $this->paymentModel->where('order_id', $id)->first();
        $shipping = $this->shippingModel->where('order_id', $id)->first();
        $customer = (new UserModel())->find($order['user_id']);

        $data = [
            'title'    => "Detail Pesanan #{$order['order_number']} - Polinela Agro",
            'order'    => $order,
            'details'  => $details,
            'payment'  => $payment,
            'shipping' => $shipping,
            'customer' => $customer,
        ];

        return view('admin/pesanan/detail', $data);
    }

    public function updateStatus()
    {
        $role      = session()->get('user_role');
        $unitId    = session()->get('user_unit_id');
        $orderId   = (int) $this->request->getPost('order_id');
        $newStatus = $this->request->getPost('status');
        $noResi    = trim($this->request->getPost('no_resi') ?? '');

        $order = $this->orderModel->find($orderId);
        if (!$order) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        if ($role === 'admin_unit' && ((int) ($order['unit_id'] ?? 0) !== (int) $unitId)) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda hanya dapat mengubah status pesanan unit usaha Anda sendiri.');
        }

        $this->orderModel->update($orderId, [
            'status'     => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Jika ada nomor resi atau status pengiriman berubah
        if (!empty($noResi)) {
            $this->shippingModel->where('order_id', $orderId)->set([
                'no_resi'    => $noResi,
                'status'     => ($newStatus === 'dikirim') ? 'dikirim' : 'dikemas',
                'updated_at' => date('Y-m-d H:i:s'),
            ])->update();
        }

        Notification::send(
            $order['user_id'], 
            'Pembaruan Status Pesanan', 
            "Status pesanan #{$order['order_number']} telah diperbarui menjadi: " . strtoupper(str_replace('_', ' ', $newStatus)),
            base_url('pesanan/detail/' . $order['order_number'])
        );

        log_system_activity('Update Status Pesanan', 'Pesanan', "Mengubah status pesanan #{$order['order_number']} menjadi {$newStatus}");

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function cetakSuratJalan($id)
    {
        $role   = session()->get('user_role');
        $unitId = session()->get('user_unit_id');

        $order = $this->orderModel->find($id);
        if (!$order) {
            return redirect()->to(base_url('admin/pesanan'))->with('error', 'Pesanan tidak ditemukan.');
        }

        if ($role === 'admin_unit' && ((int) ($order['unit_id'] ?? 0) !== (int) $unitId)) {
            return redirect()->to(base_url('admin/pesanan'))->with('error', 'Akses ditolak: Pesanan ini berasal dari unit usaha lain.');
        }

        $details  = $this->orderDetailModel->getDetailsByOrderId($id);
        $shipping = $this->shippingModel->where('order_id', $id)->first();
        $customer = (new UserModel())->find($order['user_id']);

        $data = [
            'order'    => $order,
            'details'  => $details,
            'shipping' => $shipping,
            'customer' => $customer,
        ];

        $html = view('admin/laporan/surat_jalan_template', $data);
        return PdfGenerator::generate($html, 'SuratJalan_' . $order['order_number'], true, 'A4', 'portrait');
    }
}
