<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\UnitModel;
use App\Models\UserModel;

class Unit extends BaseController
{
    protected $unitModel;
    protected $userModel;

    public function __construct()
    {
        $this->unitModel = new UnitModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $units = $this->unitModel->getUnitsWithStats();
        $adminUsers = $this->userModel->where('role', 'admin_unit')->findAll();

        $data = [
            'title'      => 'Kelola Unit Usaha Perkebunan Polinela',
            'units'      => $units,
            'adminUsers' => $adminUsers,
        ];

        return view('admin/unit/index', $data);
    }

    public function create()
    {
        $nama = trim($this->request->getPost('nama_unit'));
        $slug = url_title($nama, '-', true);

        $this->unitModel->insert([
            'nama_unit'  => $nama,
            'slug'       => $slug,
            'deskripsi'  => trim($this->request->getPost('deskripsi') ?? ''),
            'pj_nama'    => trim($this->request->getPost('pj_nama') ?? ''),
            'kontak'     => trim($this->request->getPost('kontak') ?? ''),
            'lokasi'     => trim($this->request->getPost('lokasi') ?? ''),
            'logo'       => 'default-unit.png',
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        log_system_activity('Tambah Unit', 'Unit Usaha', "Menambahkan unit usaha baru: {$nama}");

        return redirect()->to(base_url('admin/unit'))->with('success', 'Unit usaha baru berhasil ditambahkan.');
    }

    public function update($id)
    {
        $nama = trim($this->request->getPost('nama_unit'));
        $this->unitModel->update($id, [
            'nama_unit'  => $nama,
            'deskripsi'  => trim($this->request->getPost('deskripsi') ?? ''),
            'pj_nama'    => trim($this->request->getPost('pj_nama') ?? ''),
            'kontak'     => trim($this->request->getPost('kontak') ?? ''),
            'lokasi'     => trim($this->request->getPost('lokasi') ?? ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('admin/unit'))->with('success', 'Data unit usaha berhasil diperbarui.');
    }

    public function assignAdmin()
    {
        $userId = (int) $this->request->getPost('user_id');
        $unitId = (int) $this->request->getPost('unit_id');

        $user = $this->userModel->find($userId);
        $unit = $this->unitModel->find($unitId);

        if (!$user || !$unit) {
            return redirect()->back()->with('error', 'User atau Unit tidak ditemukan.');
        }

        $this->userModel->update($userId, [
            'unit_id' => $unitId,
            'role'    => 'admin_unit',
        ]);

        log_system_activity('Assign Admin Unit', 'Unit Usaha', "Menugaskan {$user['nama']} sebagai admin untuk {$unit['nama_unit']}");

        return redirect()->to(base_url('admin/unit'))->with('success', "Berhasil menugaskan {$user['nama']} sebagai admin {$unit['nama_unit']}.");
    }

    public function delete($id)
    {
        $this->unitModel->delete($id);
        return redirect()->to(base_url('admin/unit'))->with('success', 'Unit usaha berhasil dihapus.');
    }
}
