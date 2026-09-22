<?php

namespace App\Controllers;

use App\Models\CartModel;
use App\Models\ProdukModel;
use App\Models\OrderModel;
use App\Models\OrderDetailModel;
use App\Models\PaymentModel;
use App\Models\ShippingModel;
use App\Models\StockModel;
use App\Models\VoucherModel;
use App\Models\SettingModel;
use App\Models\UserModel;
use App\Libraries\Notification;

class Checkout extends BaseController
{
    protected $cartModel;
    protected $produkModel;
    protected $orderModel;
    protected $orderDetailModel;
    protected $paymentModel;
    protected $shippingModel;
    protected $voucherModel;
    protected $settingModel;

    public function __construct()
    {
        $this->cartModel        = new CartModel();
        $this->produkModel       = new ProdukModel();
        $this->orderModel       = new OrderModel();
        $this->orderDetailModel = new OrderDetailModel();
        $this->paymentModel     = new PaymentModel();
        $this->shippingModel    = new ShippingModel();
        $this->voucherModel     = new VoucherModel();
        $this->settingModel     = new SettingModel();
    }

    public function index()
    {
        if (!session()->get('logged_in')) {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu untuk checkout.');
            return redirect()->to(base_url('login'));
        }

        $userId = session()->get('user_id');
        $items  = $this->cartModel->getUserCart($userId);

        if (empty($items)) {
            session()->setFlashdata('error', 'Keranjang belanja Anda masih kosong.');
            return redirect()->to(base_url('katalog'));
        }

        $subtotal = 0;
        $totalBerat = 0;
        foreach ($items as $it) {
            $subtotal += ($it['harga'] * $it['qty']);
            $totalBerat += ($it['berat_gram'] * $it['qty']);
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        $bankAccounts = json_decode($this->settingModel->getByKey('bank_accounts', '[]'), true);
        $shippingRates = json_decode($this->settingModel->getByKey('shipping_rates', '[]'), true);

        $data = [
            'title'          => 'Checkout Pesanan - Polinela Agro Digital',
            'items'          => $items,
            'subtotal'       => $subtotal,
            'total_berat'    => $totalBerat,
            'user'           => $user,
            'bank_accounts'  => $bankAccounts,
            'shipping_rates' => $shippingRates,
        ];

        return view('frontend/checkout', $data);
    }

    public function applyVoucher()
    {
        $code = trim($this->request->getPost('code') ?? '');
        $subtotal = (float) $this->request->getPost('subtotal');

        if (empty($code)) {
            return $this->response->setJSON(['valid' => false, 'message' => 'Masukkan kode voucher.']);
        }

        $result = $this->voucherModel->validateVoucher($code, $subtotal);
        return $this->response->setJSON($result);
    }

    public function process()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $userId = session()->get('user_id');
        $items  = $this->cartModel->getUserCart($userId);

        if (empty($items)) {
            return redirect()->to(base_url('katalog'));
        }

        $rules = [
            'penerima_nama'    => 'required|min_length[3]|max_length[150]',
            'penerima_telepon' => 'required|min_length[10]|max_length[20]',
            'alamat_lengkap'   => 'required|min_length[5]',
            'kota'             => 'required',
            'metode_bayar'     => 'required|in_list[transfer,cod,qris,va]',
            'shipping_option'  => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $subtotal = 0;
        $totalBerat = 0;
        $unitId = null;

        foreach ($items as $item) {
            $subtotal += ($item['harga'] * $item['qty']);
            $totalBerat += ($item['berat_gram'] * $item['qty']);
            if (!$unitId && !empty($item['unit_id'])) {
                $unitId = $item['unit_id'];
            }
        }

        // Hitung Ongkir
        $shippingOption = $this->request->getPost('shipping_option');
        $shippingRates = json_decode($this->settingModel->getByKey('shipping_rates', '[]'), true);
        $ongkir = 12000; // default
        $kurirName = 'Kurir Standar';
        $estimasi = '1-2 hari';

        if (isset($shippingRates[$shippingOption])) {
            $ongkir = (float) $shippingRates[$shippingOption]['tarif'];
            $kurirName = $shippingRates[$shippingOption]['wilayah'];
            $estimasi = $shippingRates[$shippingOption]['estimasi'];
        }

        // Voucher Diskon
        $voucherCode = trim($this->request->getPost('kode_voucher') ?? '');
        $diskon = 0;
        if (!empty($voucherCode)) {
            $voucherCheck = $this->voucherModel->validateVoucher($voucherCode, $subtotal);
            if ($voucherCheck['valid']) {
                $diskon = (float) $voucherCheck['nilai_diskon'];
                // Update pemakaian voucher
                $this->voucherModel->where('kode', $voucherCode)->increment('terpakai', 1);
            }
        }

        $grandTotal = max(0, ($subtotal + $ongkir - $diskon));
        $orderNumber = $this->orderModel->generateOrderNumber();

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Simpan Order
        $orderId = $this->orderModel->insert([
            'order_number' => $orderNumber,
            'user_id'      => $userId,
            'unit_id'      => $unitId,
            'total_produk' => $subtotal,
            'ongkir'       => $ongkir,
            'diskon'       => $diskon,
            'kode_voucher' => $voucherCode ?: null,
            'grand_total'  => $grandTotal,
            'status'       => 'pending',
            'catatan'      => trim($this->request->getPost('catatan') ?? ''),
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        // 2. Simpan Order Details & Potong Stok
        $stockModel = new StockModel();
        foreach ($items as $item) {
            $this->orderDetailModel->insert([
                'order_id'    => $orderId,
                'product_id'  => $item['product_id'],
                'nama_produk' => $item['nama_produk'],
                'harga'       => $item['harga'],
                'qty'         => $item['qty'],
                'subtotal'    => $item['harga'] * $item['qty'],
                'berat'       => $item['berat_gram'] * $item['qty'],
            ]);

            // Kurangi stok produk
            $this->produkModel->where('id', $item['product_id'])->decrement('stok', (int) $item['qty']);
            $this->produkModel->where('id', $item['product_id'])->increment('total_terjual', (int) $item['qty']);

            // Catat mutasi stok
            $currentProduct = $this->produkModel->find($item['product_id']);
            $stockModel->insert([
                'product_id' => $item['product_id'],
                'tipe'       => 'keluar',
                'qty'        => $item['qty'],
                'sisa_stok'  => $currentProduct['stok'],
                'keterangan' => "Pesanan #{$orderNumber}",
                'user_id'    => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // 3. Simpan Payment
        $metodeBayar = $this->request->getPost('metode_bayar');
        $this->paymentModel->insert([
            'order_id'            => $orderId,
            'metode'              => $metodeBayar,
            'bank'                => $this->request->getPost('bank_tujuan') ?: 'Mandiri Polinela',
            'no_rekening_tujuan'  => '114-00-8899123-4',
            'atas_nama'           => 'POLINELA TEFA',
            'no_transaksi'        => 'TRX-' . strtoupper(substr(uniqid(), -6)),
            'bukti_bayar'         => null,
            'status'              => ($metodeBayar === 'cod') ? 'pending' : 'pending',
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);

        // 4. Simpan Shipping
        $this->shippingModel->insert([
            'order_id'          => $orderId,
            'kurir'             => $kurirName,
            'layanan'           => 'Reguler Kampus',
            'no_resi'           => null,
            'ongkir'            => $ongkir,
            'estimasi'          => $estimasi,
            'penerima_nama'     => trim($this->request->getPost('penerima_nama')),
            'penerima_telepon'  => trim($this->request->getPost('penerima_telepon')),
            'alamat_lengkap'    => trim($this->request->getPost('alamat_lengkap')),
            'kota'              => trim($this->request->getPost('kota')),
            'kode_pos'          => trim($this->request->getPost('kode_pos') ?? '35141'),
            'status'            => 'pending',
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        // 5. Bersihkan keranjang
        $this->cartModel->where('user_id', $userId)->delete();

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat memproses pesanan Anda. Silakan coba lagi.');
        }

        // Notifikasi & Log
        Notification::send($userId, 'Pesanan Berhasil Dibuat', "Pesanan #{$orderNumber} telah dibuat. Silakan lakukan pembayaran.", base_url('pesanan/detail/' . $orderNumber));
        Notification::sendToAdmins('Pesanan Baru Masuk', "Pesanan baru #{$orderNumber} senilai " . format_rupiah($grandTotal) . " siap diproses.", base_url('admin/pesanan/detail/' . $orderId));

        log_system_activity('Checkout', 'Transaksi', "Pesanan #{$orderNumber} berhasil dibuat dengan grand total " . format_rupiah($grandTotal));

        session()->setFlashdata('success', "Pesanan Anda berhasil dibuat dengan nomor pesanan {$orderNumber}.");
        return redirect()->to(base_url('pesanan/detail/' . $orderNumber));
    }
}
