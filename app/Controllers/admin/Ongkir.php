<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\SettingModel;

class Ongkir extends BaseController
{
    protected $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    public function index()
    {
        $rates = json_decode($this->settingModel->getByKey('shipping_rates', '[]'), true);

        $data = [
            'title' => 'Kelola Tarif Ongkir & Kurir - Polinela Agro Digital',
            'rates' => $rates,
        ];

        return view('admin/ongkir/index', $data);
    }

    public function save()
    {
        $wilayah  = $this->request->getPost('wilayah');
        $tarif    = $this->request->getPost('tarif');
        $estimasi = $this->request->getPost('estimasi');

        $rates = [];
        if (is_array($wilayah)) {
            for ($i = 0; $i < count($wilayah); $i++) {
                if (!empty($wilayah[$i])) {
                    $rates[] = [
                        'wilayah'  => trim($wilayah[$i]),
                        'tarif'    => (float) $tarif[$i],
                        'estimasi' => trim($estimasi[$i]),
                    ];
                }
            }
        }

        $this->settingModel->setVal('shipping_rates', json_encode($rates), 'shipping');

        log_system_activity('Update Ongkir', 'Pengaturan', 'Memperbarui tarif ongkir pengiriman kampus');

        return redirect()->to(base_url('admin/ongkir'))->with('success', 'Tarif ongkir berhasil disimpan.');
    }
}
