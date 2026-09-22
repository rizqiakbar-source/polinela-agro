<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\UnitModel;

class User extends BaseController
{
    protected $userModel;
    protected $unitModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->unitModel = new UnitModel();
    }

    public function index()
    {
        $roleFilter = $this->request->getGet('role');
        $users = $this->userModel->getWithUnit();

        if ($roleFilter) {
            $users = array_filter($users, fn($u) => $u['role'] === $roleFilter);
        }

        $units = $this->unitModel->where('is_active', 1)->findAll();

        $data = [
            'title'       => 'Kelola Pengguna & Hak Akses - Polinela Agro Digital',
            'users'       => $users,
            'units'       => $units,
            'current_role'=> $roleFilter,
        ];

        return view('admin/user/index', $data);
    }

    public function create()
    {
        $rules = [
            'nama'     => 'required|min_length[3]|max_length[150]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[superadmin,admin_unit,konsumen,pimpinan]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $nama = trim($this->request->getPost('nama'));
        $role = $this->request->getPost('role');
        $unitId = ($role === 'admin_unit') ? (int) $this->request->getPost('unit_id') : null;

        $this->userModel->insert([
            'nama'       => $nama,
            'email'      => strtolower(trim($this->request->getPost('email'))),
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'no_hp'      => trim($this->request->getPost('no_hp') ?? ''),
            'role'       => $role,
            'unit_id'    => $unitId,
            'foto'       => 'default-avatar.png',
            'alamat'     => trim($this->request->getPost('alamat') ?? ''),
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        log_system_activity('Tambah User', 'Pengguna', "Menambahkan user baru {$nama} dengan role {$role}");

        return redirect()->to(base_url('admin/user'))->with('success', "Pengguna {$nama} berhasil ditambahkan.");
    }

    public function toggleStatus($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }

        if ($user['id'] == session()->get('user_id')) {
            return redirect()->back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        $this->userModel->update($id, ['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        log_system_activity('Ubah Status User', 'Pengguna', "User {$user['nama']} telah {$statusText}");

        return redirect()->back()->with('success', "Akun {$user['nama']} berhasil {$statusText}.");
    }

    public function resetPassword($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }

        $newPass = 'polinela123';
        $this->userModel->update($id, [
            'password' => password_hash($newPass, PASSWORD_BCRYPT)
        ]);

        log_system_activity('Reset Password', 'Pengguna', "Mereset password user {$user['nama']}");

        return redirect()->back()->with('success', "Password akun {$user['nama']} telah direset ke: <strong>{$newPass}</strong>");
    }
}
