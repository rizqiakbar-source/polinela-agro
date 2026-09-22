<?php

namespace App\Controllers;

use App\Models\UserModel;

class Profil extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        $data = [
            'title' => 'Profil Saya - Polinela Agro Digital',
            'user'  => $user,
        ];

        return view('frontend/profil', $data);
    }

    public function update()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $userId = session()->get('user_id');
        $userModel = new UserModel();

        $rules = [
            'nama'   => 'required|min_length[3]|max_length[150]',
            'no_hp'  => 'required|min_length[10]|max_length[20]',
            'alamat' => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'nama'   => trim($this->request->getPost('nama')),
            'no_hp'  => trim($this->request->getPost('no_hp')),
            'alamat' => trim($this->request->getPost('alamat') ?? ''),
        ];

        $file = $this->request->getFile('foto');
        if ($file && $file->isValid()) {
            $newName = 'avatar_' . $userId . '_' . time() . '.' . $file->getExtension();
            $targetDir = FCPATH . 'uploads/avatar';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $file->move($targetDir, $newName);
            $updateData['foto'] = $newName;
            session()->set('user_foto', $newName);
        }

        $userModel->update($userId, $updateData);
        session()->set('user_nama', $updateData['nama']);

        log_system_activity('Update Profil', 'Profil', "User {$updateData['nama']} memperbarui data profil.");

        return redirect()->back()->with('success', 'Profil Anda berhasil diperbarui.');
    }

    public function changePassword()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[6]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        if (!password_verify($this->request->getPost('current_password'), $user['password'])) {
            return redirect()->back()->with('error', 'Kata sandi saat ini tidak cocok.');
        }

        $userModel->update($userId, [
            'password' => password_hash($this->request->getPost('new_password'), PASSWORD_BCRYPT)
        ]);

        return redirect()->back()->with('success', 'Kata sandi berhasil diperbarui.');
    }

    public function alamat()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        // Riwayat alamat dari pengiriman sebelumnya
        $shippingModel = new \App\Models\ShippingModel();
        $orderModel = new \App\Models\OrderModel();
        $userOrders = $orderModel->where('user_id', $userId)->findAll();
        $orderIds = array_column($userOrders, 'id');

        $savedAddresses = [];
        if (!empty($orderIds)) {
            $savedAddresses = $shippingModel->whereIn('order_id', $orderIds)
                                            ->groupBy('alamat_lengkap')
                                            ->orderBy('id', 'DESC')
                                            ->findAll(5);
        }

        $data = [
            'title'           => 'Kelola Alamat Pengiriman - Polinela Agro Digital',
            'user'            => $user,
            'saved_addresses' => $savedAddresses,
        ];

        return view('frontend/alamat', $data);
    }

    public function saveAlamat()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $userId = session()->get('user_id');
        $alamat = trim($this->request->getPost('alamat') ?? '');

        if (empty($alamat)) {
            return redirect()->back()->with('error', 'Alamat tidak boleh kosong.');
        }

        $userModel = new UserModel();
        $userModel->update($userId, ['alamat' => $alamat]);

        return redirect()->back()->with('success', 'Alamat utama berhasil diperbarui.');
    }
}
