<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\BannerModel;

class Banner extends BaseController
{
    protected $bannerModel;

    public function __construct()
    {
        $this->bannerModel = new BannerModel();
    }

    public function index()
    {
        $banners = $this->bannerModel->orderBy('urutan', 'ASC')->findAll();

        $data = [
            'title'   => 'Kelola Banner Promo - Polinela Agro Digital',
            'banners' => $banners,
        ];

        return view('admin/banner/index', $data);
    }

    public function create()
    {
        $file = $this->request->getFile('gambar');
        $gambarName = 'default-banner.jpg';

        if ($file && $file->isValid()) {
            $gambarName = 'banner_' . time() . '_' . $file->getRandomName();
            $targetDir = FCPATH . 'uploads/banner';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $file->move($targetDir, $gambarName);
        }

        $this->bannerModel->insert([
            'judul'      => trim($this->request->getPost('judul')),
            'subjudul'   => trim($this->request->getPost('subjudul') ?? ''),
            'gambar'     => $gambarName,
            'link'       => trim($this->request->getPost('link') ?? 'katalog'),
            'urutan'     => (int) ($this->request->getPost('urutan') ?: 1),
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('admin/banner'))->with('success', 'Banner berhasil ditambahkan.');
    }

    public function delete($id)
    {
        $this->bannerModel->delete($id);
        return redirect()->to(base_url('admin/banner'))->with('success', 'Banner berhasil dihapus.');
    }
}
