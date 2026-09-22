<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\SettingModel;

class Pengaturan extends BaseController
{
    protected $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    public function index()
    {
        $settings = $this->settingModel->getAllAsMap();

        $data = [
            'title'    => 'Pengaturan Toko & Kampus - Polinela Agro Digital',
            'settings' => $settings,
        ];

        return view('admin/pengaturan/index', $data);
    }

    public function save()
    {
        $fields = [
            'site_name', 'site_tagline', 'campus_address', 
            'contact_phone', 'contact_wa', 'contact_email', 
            'midtrans_client_key'
        ];

        foreach ($fields as $field) {
            $val = $this->request->getPost($field);
            if ($val !== null) {
                $this->settingModel->setVal($field, trim($val), 'general');
            }
        }

        log_system_activity('Update Pengaturan', 'Pengaturan', 'Memperbarui konfigurasi informasi toko & kampus');

        return redirect()->to(base_url('admin/pengaturan'))->with('success', 'Pengaturan aplikasi berhasil disimpan.');
    }
}
