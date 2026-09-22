<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function login()
    {
        if (session()->get('logged_in')) {
            $role = session()->get('user_role');
            if (in_array($role, ['superadmin', 'admin_unit', 'pimpinan'])) {
                return redirect()->to(base_url('admin/dashboard'));
            }
            return redirect()->to(base_url('/'));
        }

        if ($this->request->getMethod() === 'POST' || $this->request->getMethod() === 'post') {
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $email    = trim($this->request->getPost('email'));
            $password = $this->request->getPost('password');

            $user = $this->userModel->where('email', $email)->first();

            if (!$user) {
                return redirect()->back()->withInput()->with('error', 'Email tidak terdaftar dalam sistem.');
            }

            if (!$user['is_active']) {
                return redirect()->back()->withInput()->with('error', 'Akun Anda sedang dinonaktifkan. Hubungi administrator.');
            }

            if (!password_verify($password, $user['password'])) {
                return redirect()->back()->withInput()->with('error', 'Kata sandi yang Anda masukkan salah.');
            }

            // Set Session
            $sessionData = [
                'user_id'      => $user['id'],
                'user_nama'    => $user['nama'],
                'user_email'   => $user['email'],
                'user_role'    => $user['role'],
                'user_unit_id' => $user['unit_id'],
                'user_foto'    => $user['foto'] ?: 'default-avatar.png',
                'logged_in'    => true,
            ];
            session()->set($sessionData);

            log_system_activity('Login', 'Auth', "User {$user['nama']} ({$user['role']}) berhasil login.");

            session()->setFlashdata('success', "Selamat datang kembali, {$user['nama']}!");

            if (in_array($user['role'], ['superadmin', 'admin_unit', 'pimpinan'])) {
                return redirect()->to(base_url('admin/dashboard'));
            }

            return redirect()->to(base_url('/'));
        }

        return view('auth/login');
    }

    public function register()
    {
        if (session()->get('logged_in')) {
            return redirect()->to(base_url('/'));
        }

        if ($this->request->getMethod() === 'POST' || $this->request->getMethod() === 'post') {
            $rules = [
                'nama'     => 'required|min_length[3]|max_length[150]',
                'email'    => 'required|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[6]',
                'no_hp'    => 'required|min_length[10]|max_length[20]',
                'alamat'   => 'permit_empty',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $userData = [
                'nama'       => trim($this->request->getPost('nama')),
                'email'      => strtolower(trim($this->request->getPost('email'))),
                'password'   => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
                'no_hp'      => trim($this->request->getPost('no_hp')),
                'role'       => 'konsumen',
                'foto'       => 'default-avatar.png',
                'alamat'     => trim($this->request->getPost('alamat') ?? ''),
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $userId = $this->userModel->insert($userData);

            // Auto-login setelah registrasi
            $sessionData = [
                'user_id'      => $userId,
                'user_nama'    => $userData['nama'],
                'user_email'   => $userData['email'],
                'user_role'    => 'konsumen',
                'user_unit_id' => null,
                'user_foto'    => 'default-avatar.png',
                'logged_in'    => true,
            ];
            session()->set($sessionData);

            log_system_activity('Register', 'Auth', "Pendaftaran konsumen baru: {$userData['nama']}");

            session()->setFlashdata('success', "Pendaftaran berhasil! Selamat datang di Polinela Agro Digital, {$userData['nama']}.");
            return redirect()->to(base_url('/'));
        }

        return view('auth/register');
    }

    public function logout()
    {
        $userName = session()->get('user_nama');
        log_system_activity('Logout', 'Auth', "User {$userName} logout dari sistem.");
        session()->destroy();
        return redirect()->to(base_url('login'))->with('success', 'Anda telah berhasil keluar.');
    }

    public function forgotPassword()
    {
        if ($this->request->getMethod() === 'POST' || $this->request->getMethod() === 'post') {
            $email = trim($this->request->getPost('email'));
            $user = $this->userModel->where('email', $email)->first();
            if ($user) {
                return redirect()->back()->with('success', 'Instruksi reset kata sandi telah dikirim ke email Anda (Simulasi).');
            }
            return redirect()->back()->with('error', 'Email tidak ditemukan.');
        }

        return view('auth/forgot_password');
    }

    public function resetPassword($token = null)
    {
        if ($this->request->getMethod() === 'POST' || $this->request->getMethod() === 'post') {
            $rules = [
                'email'            => 'required|valid_email',
                'password'         => 'required|min_length[6]',
                'confirm_password' => 'required|matches[password]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $email    = trim($this->request->getPost('email'));
            $password = $this->request->getPost('password');

            $user = $this->userModel->where('email', $email)->first();
            if (!$user) {
                return redirect()->back()->withInput()->with('error', 'Akun dengan email tersebut tidak ditemukan.');
            }

            $this->userModel->update($user['id'], [
                'password'   => password_hash($password, PASSWORD_BCRYPT),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            log_system_activity('Reset Password', 'Auth', "User {$user['nama']} berhasil menyetel ulang password.");

            return redirect()->to(base_url('login'))->with('success', 'Kata sandi Anda berhasil disetel ulang! Silakan login dengan kata sandi baru.');
        }

        $data = [
            'token' => $token,
            'email' => $this->request->getGet('email') ?: '',
        ];

        return view('auth/reset_password', $data);
    }
}
