<?php

namespace App\Controllers;

use App\Models\SettingModel;

class Kontak extends BaseController
{
    public function index()
    {
        $settingModel = new SettingModel();
        $settings = $settingModel->getAllAsMap();

        $data = [
            'title'    => 'Hubungi Kami - Polinela Agro Digital',
            'settings' => $settings,
        ];

        return view('frontend/kontak', $data);
    }

    public function kirim()
    {
        $rules = [
            'nama'    => 'required',
            'email'   => 'required|valid_email',
            'subjek'  => 'required',
            'pesan'   => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        return redirect()->back()->with('success', 'Pesan Anda telah berhasil dikirim ke pengelola Polinela Agro Digital. Terima kasih!');
    }
}
