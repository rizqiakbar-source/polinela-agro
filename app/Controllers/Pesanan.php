<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\OrderDetailModel;
use App\Models\PaymentModel;
use App\Models\ShippingModel;
use App\Models\ProdukModel;
use App\Models\StockModel;
use App\Models\SettingModel;
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
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $userId = session()->get('user_id');
        $status = $this->request->getGet('status');

        $orders = $this->orderModel->getOrderWithRelations(null, $userId);
        if ($status) {
            $orders = array_filter($orders, fn($o) => $o['status'] === $status);
        }

        $data = [
            'title'          => 'Riwayat Pesanan Saya - Polinela Agro Digital',
            'orders'         => $orders,
            'current_status' => $status,
        ];

        return view('frontend/riwayat', $data);
    }

    public function detail($orderNumber)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $order = $this->orderModel->where('order_number', $orderNumber)->first();
        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Pesanan tidak ditemukan.');
        }

        // Pastikan hanya pemilik atau admin yang bisa melihat
        $userRole = session()->get('user_role');
        if ($order['user_id'] != session()->get('user_id') && !in_array($userRole, ['superadmin', 'admin_unit', 'pimpinan'])) {
            session()->setFlashdata('error', 'Anda tidak berhak melihat pesanan ini.');
            return redirect()->to(base_url('pesanan'));
        }

        $details  = $this->orderDetailModel->getDetailsByOrderId($order['id']);
        $payment  = $this->paymentModel->where('order_id', $order['id'])->first();
        $shipping = $this->shippingModel->where('order_id', $order['id'])->first();

        $settingModel = new SettingModel();
        $bankAccounts = json_decode($settingModel->getByKey('bank_accounts', '[]'), true);

        $unitModel = new \App\Models\UnitModel();
        $unit = !empty($order['unit_id']) ? $unitModel->find($order['unit_id']) : null;

        // Cek pesanan multi-toko (1x Pembayaran bersama)
        $siblingOrders = [];
        $totalTrxAmount = (float) $order['grand_total'];
        if ($payment && !empty($payment['no_transaksi'])) {
            $relatedPayments = $this->paymentModel->where('no_transaksi', $payment['no_transaksi'])->findAll();
            if (count($relatedPayments) > 1) {
                $siblingOrderIds = array_column($relatedPayments, 'order_id');
                $allUserOrders = $this->orderModel->getOrderWithRelations(null, $order['user_id']);
                $siblingOrders = array_values(array_filter($allUserOrders, fn($o) => in_array($o['id'], $siblingOrderIds)));
                $totalTrxAmount = array_sum(array_column($siblingOrders, 'grand_total'));
            }
        }

        $data = [
            'title'            => "Pesanan #{$order['order_number']} - Polinela Agro Digital",
            'order'            => $order,
            'unit'             => $unit,
            'details'          => $details,
            'payment'          => $payment,
            'shipping'         => $shipping,
            'bank_accounts'    => $bankAccounts,
            'sibling_orders'   => $siblingOrders,
            'total_trx_amount' => $totalTrxAmount,
        ];

        return view('frontend/detail_pesanan', $data);
    }

    public function uploadBukti()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $orderId = (int) $this->request->getPost('order_id');
        $order = $this->orderModel->find($orderId);

        if (!$order || $order['user_id'] != session()->get('user_id')) {
            return redirect()->back()->with('error', 'Pesanan tidak valid.');
        }

        $file = $this->request->getFile('bukti_bayar');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Silakan pilih foto bukti pembayaran.');
        }

        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return redirect()->back()->with('error', 'Format gambar harus JPG, JPEG, PNG, atau WEBP.');
        }

        $newName = 'bukti_' . $order['order_number'] . '_' . time() . '.' . $file->getExtension();
        $targetDir = FCPATH . 'uploads/bukti_bayar';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $file->move($targetDir, $newName);

        // Cek apakah pesanan ini memiliki transaksi bersama (Multi-Toko 1x Bayar)
        $currentPayment = $this->paymentModel->where('order_id', $orderId)->first();
        $relatedPayments = [];
        if ($currentPayment && !empty($currentPayment['no_transaksi'])) {
            $relatedPayments = $this->paymentModel->where('no_transaksi', $currentPayment['no_transaksi'])->findAll();
        }

        if (!empty($relatedPayments)) {
            foreach ($relatedPayments as $rp) {
                $this->paymentModel->update($rp['id'], [
                    'bukti_bayar' => $newName,
                    'bank'        => $this->request->getPost('bank_asal') ?: 'Transfer Bank',
                    'atas_nama'   => $this->request->getPost('atas_nama_pengirim') ?: 'Pengirim',
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
                $this->orderModel->update($rp['order_id'], [
                    'status'     => 'menunggu_verifikasi',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $relOrder = $this->orderModel->find($rp['order_id']);
                if ($relOrder) {
                    Notification::sendToAdmins('Bukti Pembayaran Diunggah', "Konsumen telah mengunggah bukti bayar untuk pesanan #{$relOrder['order_number']}.", base_url('admin/pembayaran'), $relOrder['unit_id'] ? (int) $relOrder['unit_id'] : null);
                    log_system_activity('Upload Bukti', 'Pembayaran', "Upload bukti bayar untuk pesanan #{$relOrder['order_number']}");
                }
            }
        } else {
            // Update pembayaran & status pesanan tunggal
            $this->paymentModel->where('order_id', $orderId)->set([
                'bukti_bayar' => $newName,
                'bank'        => $this->request->getPost('bank_asal') ?: 'Transfer Bank',
                'atas_nama'   => $this->request->getPost('atas_nama_pengirim') ?: 'Pengirim',
                'updated_at'  => date('Y-m-d H:i:s'),
            ])->update();

            $this->orderModel->update($orderId, [
                'status'     => 'menunggu_verifikasi',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            Notification::sendToAdmins('Bukti Pembayaran Diunggah', "Konsumen telah mengunggah bukti bayar untuk pesanan #{$order['order_number']}.", base_url('admin/pembayaran'), $order['unit_id'] ? (int) $order['unit_id'] : null);
            log_system_activity('Upload Bukti', 'Pembayaran', "Upload bukti bayar untuk pesanan #{$order['order_number']}");
        }

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah! Mohon menunggu verifikasi dari Admin Unit.');
    }

    public function konfirmasiTerima($orderId)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $order = $this->orderModel->find($orderId);
        if (!$order || $order['user_id'] != session()->get('user_id')) {
            return redirect()->back()->with('error', 'Pesanan tidak valid.');
        }

        $this->orderModel->update($orderId, [
            'status'     => 'selesai',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->shippingModel->where('order_id', $orderId)->set([
            'status'     => 'sampai',
            'updated_at' => date('Y-m-d H:i:s'),
        ])->update();

        log_system_activity('Pesanan Diterima', 'Pesanan', "Konsumen mengonfirmasi penerimaan pesanan #{$order['order_number']}");

        return redirect()->back()->with('success', 'Terima kasih! Pesanan telah selesai. Silakan beri ulasan dan rating produk.');
    }

    public function batalkan($orderId)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $order = $this->orderModel->find($orderId);
        if (!$order || $order['user_id'] != session()->get('user_id')) {
            return redirect()->back()->with('error', 'Pesanan tidak valid.');
        }

        if ($order['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Pesanan yang sedang diproses atau dikirim tidak dapat dibatalkan.');
        }

        // Kembalikan stok produk
        $details = $this->orderDetailModel->where('order_id', $orderId)->findAll();
        $produkModel = new ProdukModel();
        $stockModel = new StockModel();

        foreach ($details as $d) {
            $produkModel->where('id', $d['product_id'])->increment('stok', (int) $d['qty']);
            $produkModel->where('id', $d['product_id'])->decrement('total_terjual', (int) $d['qty']);

            $p = $produkModel->find($d['product_id']);
            $stockModel->insert([
                'product_id' => $d['product_id'],
                'tipe'       => 'masuk',
                'qty'        => $d['qty'],
                'sisa_stok'  => $p['stok'],
                'keterangan' => "Pengembalian stok dari pembatalan pesanan #{$order['order_number']}",
                'user_id'    => session()->get('user_id'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->orderModel->update($orderId, [
            'status'     => 'dibatalkan',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        log_system_activity('Batal Pesanan', 'Pesanan', "Konsumen membatalkan pesanan #{$order['order_number']}");

        return redirect()->back()->with('success', 'Pesanan berhasil dibatalkan dan stok produk telah dikembalikan.');
    }

    public function invoice($orderNumber)
    {
        $order = $this->orderModel->where('order_number', $orderNumber)->first();
        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Invoice tidak ditemukan.');
        }

        $details   = $this->orderDetailModel->getDetailsByOrderId($order['id']);
        $payment   = $this->paymentModel->where('order_id', $order['id'])->first();
        $shipping  = $this->shippingModel->where('order_id', $order['id'])->first();
        $customer  = (new \App\Models\UserModel())->find($order['user_id']);
        $unitModel = new \App\Models\UnitModel();
        $unit      = !empty($order['unit_id']) ? $unitModel->find($order['unit_id']) : null;

        $data = [
            'title'    => "Invoice #{$order['order_number']} - Polinela Agro Digital",
            'order'    => $order,
            'unit'     => $unit,
            'details'  => $details,
            'payment'  => $payment,
            'shipping' => $shipping,
            'customer' => $customer,
        ];

        return view('frontend/invoice', $data);
    }

    public function invoicePdf($orderNumber)
    {
        $order = $this->orderModel->where('order_number', $orderNumber)->first();
        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Invoice tidak ditemukan.');
        }

        $details   = $this->orderDetailModel->getDetailsByOrderId($order['id']);
        $payment   = $this->paymentModel->where('order_id', $order['id'])->first();
        $shipping  = $this->shippingModel->where('order_id', $order['id'])->first();
        $customer  = (new \App\Models\UserModel())->find($order['user_id']);
        $unitModel = new \App\Models\UnitModel();
        $unit      = !empty($order['unit_id']) ? $unitModel->find($order['unit_id']) : null;

        $data = [
            'order'    => $order,
            'unit'     => $unit,
            'details'  => $details,
            'payment'  => $payment,
            'shipping' => $shipping,
            'customer' => $customer,
        ];

        $html = view('admin/laporan/invoice_template', $data);
        return PdfGenerator::generate($html, 'Invoice_' . $order['order_number'], true, 'A4', 'portrait');
    }
}
