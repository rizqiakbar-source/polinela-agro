<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\KategoriModel;

class Kategori extends BaseController
{
    protected $kategoriModel;

    public function __construct()
    {
        $this->kategoriModel = new KategoriModel();
    }

    public function index()
    {
        $categories = $this->kategoriModel->getCategoriesWithCount();

        $data = [
            'title'      => 'Kelola Kategori Produk - Admin Polinela Agro',
            'categories' => $categories,
        ];

        return view('admin/kategori/index', $data);
    }

    public function create()
    {
        $rules = [
            'nama_kategori' => 'required|min_length[3]|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $nama = trim($this->request->getPost('nama_kategori'));
        $slug = url_title($nama, '-', true);

        $this->kategoriModel->insert([
            'nama_kategori' => $nama,
            'slug'          => $slug,
            'icon'          => $this->request->getPost('icon') ?: 'bi-tag',
            'deskripsi'     => trim($this->request->getPost('deskripsi') ?? ''),
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        log_system_activity('Tambah Kategori', 'Kategori', "Menambah kategori produk: {$nama}");

        return redirect()->to(base_url('admin/kategori'))->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    public function update($id)
    {
        $nama = trim($this->request->getPost('nama_kategori'));
        $this->kategoriModel->update($id, [
            'nama_kategori' => $nama,
            'icon'          => $this->request->getPost('icon') ?: 'bi-tag',
            'deskripsi'     => trim($this->request->getPost('deskripsi') ?? ''),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('admin/kategori'))->with('success', 'Kategori berhasil diperbarui.');
    }

    public function delete($id)
    {
        $this->kategoriModel->delete($id);
        return redirect()->to(base_url('admin/kategori'))->with('success', 'Kategori berhasil dihapus.');
    }
}
