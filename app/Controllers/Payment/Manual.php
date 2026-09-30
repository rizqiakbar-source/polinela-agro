<?php

namespace App\Controllers\Payment;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\PaymentModel;
use App\Models\SettingModel;
use App\Libraries\Notification;

class Manual extends BaseController
{
    /**
     * Menampilkan panduan rekening transfer resmi kampus Polinela
     */
    public function index($orderNumber = null)
    {
        return $this->detail($orderNumber);
    }

    public function detail($orderNumber = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $orderModel = new OrderModel();
        $order = $orderNumber ? $orderModel->where('order_number', $orderNumber)->first() : null;

        $settingModel = new SettingModel();
        $bankAccounts = json_decode($settingModel->getByKey('bank_accounts', '[]'), true);

        $data = [
            'title'         => 'Instruksi Transfer Bank - Polinela Agro Digital',
            'order'         => $order,
            'bank_accounts' => $bankAccounts,
        ];

        return view('frontend/pembayaran', $data);
    }

    public function konfirmasi()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $orderNumber = $this->request->getGet('order');
        return $this->detail($orderNumber);
    }

    public function submitKonfirmasi()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $orderId = (int) $this->request->getPost('order_id');
        $orderModel = new OrderModel();
        $order = $orderModel->find($orderId);

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

        $paymentModel = new PaymentModel();
        $paymentModel->where('order_id', $orderId)->set([
            'bukti_bayar' => $newName,
            'bank'        => $this->request->getPost('bank_asal') ?: 'Transfer Bank',
            'atas_nama'   => $this->request->getPost('atas_nama_pengirim') ?: 'Pengirim',
            'updated_at'  => date('Y-m-d H:i:s'),
        ])->update();

        $orderModel->update($orderId, [
            'status'     => 'menunggu_verifikasi',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        Notification::sendToAdmins('Bukti Pembayaran Diunggah', "Konsumen telah mengunggah bukti bayar untuk pesanan #{$order['order_number']}.", base_url('admin/pembayaran'), $order['unit_id'] ? (int) $order['unit_id'] : null);
        log_system_activity('Upload Bukti', 'Pembayaran', "Upload bukti bayar untuk pesanan #{$order['order_number']}");

        return redirect()->to(base_url('pesanan/detail/' . $order['order_number']))->with('success', 'Bukti transfer berhasil dikirim. Menunggu verifikasi admin unit.');
    }
}
