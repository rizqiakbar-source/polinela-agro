<?php

namespace App\Controllers\Payment;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\PaymentModel;
use App\Models\SettingModel;

class Manual extends BaseController
{
    /**
     * Menampilkan panduan rekening transfer resmi kampus Polinela
     */
    public function index($orderNumber = null)
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
}
