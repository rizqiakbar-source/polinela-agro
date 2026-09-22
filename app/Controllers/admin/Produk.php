<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\ProdukModel;
use App\Models\KategoriModel;
use App\Models\UnitModel;
use App\Models\ProductImageModel;
use App\Models\StockModel;

class Produk extends BaseController
{
    protected $produkModel;
    protected $kategoriModel;
    protected $unitModel;
    protected $stockModel;
    protected $imageModel;

    public function __construct()
    {
        $this->produkModel   = new ProdukModel();
        $this->kategoriModel = new KategoriModel();
        $this->unitModel     = new UnitModel();
        $this->stockModel    = new StockModel();
        $this->imageModel    = new ProductImageModel();
    }

    public function index()
    {
        $role   = session()->get('user_role');
        $unitId = session()->get('user_unit_id');

        $products   = $this->produkModel->getAdminProducts($role === 'admin_unit' ? $unitId : null);
        $categories = $this->kategoriModel->where('is_active', 1)->findAll();
        $units      = $this->unitModel->where('is_active', 1)->findAll();

        $data = [
            'title'      => 'Kelola Produk Perkebunan - Admin Polinela Agro Digital',
            'products'   => $products,
            'categories' => $categories,
            'units'      => $units,
            'role'       => $role,
        ];

        return view('admin/produk/index', $data);
    }

    public function create()
    {
        $role   = session()->get('user_role');
        $unitId = session()->get('user_unit_id');

        if ($this->request->getMethod() === 'POST' || $this->request->getMethod() === 'post') {
            $rules = [
                'nama_produk' => 'required|min_length[3]|max_length[200]',
                'category_id' => 'required|numeric',
                'harga'       => 'required|numeric|greater_than[0]',
                'stok'        => 'required|numeric|greater_than_equal_to[0]',
                'satuan'      => 'required',
                'berat_gram'  => 'required|numeric',
            ];

            if ($role === 'superadmin') {
                $rules['unit_id'] = 'required|numeric';
            }

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $namaProduk = trim($this->request->getPost('nama_produk'));
            $slugBase   = url_title($namaProduk, '-', true);
            $slug       = $slugBase;
            $count = 1;
            while ($this->produkModel->where('slug', $slug)->first()) {
                $slug = $slugBase . '-' . $count;
                $count++;
            }

            $assignedUnit = ($role === 'admin_unit') ? $unitId : (int) $this->request->getPost('unit_id');

            // Handle Gambar Utama
            $file = $this->request->getFile('gambar_utama');
            $gambarName = 'default-product.png';
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $gambarName = 'prod_' . time() . '_' . $file->getRandomName();
                $targetDir = FCPATH . 'uploads/produk';
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                $file->move($targetDir, $gambarName);
            }

            $stokAwal = (int) $this->request->getPost('stok');

            $productId = $this->produkModel->insert([
                'unit_id'       => $assignedUnit,
                'category_id'   => (int) $this->request->getPost('category_id'),
                'nama_produk'   => $namaProduk,
                'slug'          => $slug,
                'deskripsi'     => trim($this->request->getPost('deskripsi') ?? ''),
                'harga'         => (float) $this->request->getPost('harga'),
                'berat_gram'    => (int) $this->request->getPost('berat_gram'),
                'satuan'        => trim($this->request->getPost('satuan')),
                'stok'          => $stokAwal,
                'stok_min'      => (int) ($this->request->getPost('stok_min') ?: 5),
                'gambar_utama'  => $gambarName,
                'status'        => $this->request->getPost('status') ?: 'aktif',
                'featured'      => $this->request->getPost('featured') ? 1 : 0,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

            // Simpan gambar ke product_images
            $this->imageModel->insert([
                'product_id' => $productId,
                'image_url'  => $gambarName,
                'is_primary' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Catat stok awal
            if ($stokAwal > 0) {
                $this->stockModel->insert([
                    'product_id' => $productId,
                    'tipe'       => 'masuk',
                    'qty'        => $stokAwal,
                    'sisa_stok'  => $stokAwal,
                    'keterangan' => 'Stok awal produk baru',
                    'user_id'    => session()->get('user_id'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            log_system_activity('Tambah Produk', 'Produk', "Menambahkan produk baru: {$namaProduk}");

            return redirect()->to(base_url('admin/produk'))->with('success', 'Produk perkebunan berhasil ditambahkan!');
        }

        $categories = $this->kategoriModel->where('is_active', 1)->findAll();
        $units      = $this->unitModel->where('is_active', 1)->findAll();

        $data = [
            'title'      => 'Tambah Produk Baru - Polinela Agro Digital',
            'categories' => $categories,
            'units'      => $units,
            'role'       => $role,
            'my_unit_id' => $unitId,
        ];

        return view('admin/produk/form', $data);
    }

    public function edit($id)
    {
        $role   = session()->get('user_role');
        $unitId = session()->get('user_unit_id');

        $product = $this->produkModel->find($id);
        if (!$product) {
            return redirect()->to(base_url('admin/produk'))->with('error', 'Produk tidak ditemukan.');
        }

        if ($role === 'admin_unit' && $product['unit_id'] != $unitId) {
            return redirect()->to(base_url('admin/produk'))->with('error', 'Anda hanya dapat mengedit produk dari unit usaha Anda sendiri.');
        }

        if ($this->request->getMethod() === 'POST' || $this->request->getMethod() === 'post') {
            $rules = [
                'nama_produk' => 'required|min_length[3]|max_length[200]',
                'category_id' => 'required|numeric',
                'harga'       => 'required|numeric|greater_than[0]',
                'satuan'      => 'required',
                'berat_gram'  => 'required|numeric',
            ];

            if ($role === 'superadmin') {
                $rules['unit_id'] = 'required|numeric';
            }

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $updateData = [
                'category_id' => (int) $this->request->getPost('category_id'),
                'nama_produk' => trim($this->request->getPost('nama_produk')),
                'deskripsi'   => trim($this->request->getPost('deskripsi') ?? ''),
                'harga'       => (float) $this->request->getPost('harga'),
                'berat_gram'  => (int) $this->request->getPost('berat_gram'),
                'satuan'      => trim($this->request->getPost('satuan')),
                'stok_min'    => (int) ($this->request->getPost('stok_min') ?: 5),
                'status'      => $this->request->getPost('status') ?: 'aktif',
                'featured'    => $this->request->getPost('featured') ? 1 : 0,
                'updated_at'  => date('Y-m-d H:i:s'),
            ];

            if ($role === 'superadmin') {
                $updateData['unit_id'] = (int) $this->request->getPost('unit_id');
            }

            // Gambar baru jika diunggah
            $file = $this->request->getFile('gambar_utama');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $gambarName = 'prod_' . time() . '_' . $file->getRandomName();
                $targetDir = FCPATH . 'uploads/produk';
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                $file->move($targetDir, $gambarName);
                $updateData['gambar_utama'] = $gambarName;
            }

            $this->produkModel->update($id, $updateData);

            log_system_activity('Edit Produk', 'Produk', "Memperbarui data produk: {$updateData['nama_produk']}");

            return redirect()->to(base_url('admin/produk'))->with('success', 'Produk berhasil diperbarui.');
        }

        $categories = $this->kategoriModel->where('is_active', 1)->findAll();
        $units      = $this->unitModel->where('is_active', 1)->findAll();

        $data = [
            'title'      => 'Edit Produk - Polinela Agro Digital',
            'product'    => $product,
            'categories' => $categories,
            'units'      => $units,
            'role'       => $role,
            'my_unit_id' => $unitId,
        ];

        return view('admin/produk/form', $data);
    }

    public function delete($id)
    {
        $role   = session()->get('user_role');
        $unitId = session()->get('user_unit_id');

        $product = $this->produkModel->find($id);
        if (!$product) {
            return redirect()->to(base_url('admin/produk'))->with('error', 'Produk tidak ditemukan.');
        }

        if ($role === 'admin_unit' && $product['unit_id'] != $unitId) {
            return redirect()->to(base_url('admin/produk'))->with('error', 'Anda tidak berhak menghapus produk unit lain.');
        }

        $this->produkModel->delete($id); // Soft delete

        log_system_activity('Hapus Produk', 'Produk', "Menghapus produk: {$product['nama_produk']}");

        return redirect()->to(base_url('admin/produk'))->with('success', 'Produk berhasil dihapus (soft delete).');
    }

    public function adjustStock()
    {
        $productId = (int) $this->request->getPost('product_id');
        $tipe      = $this->request->getPost('tipe'); // masuk, keluar, penyesuaian
        $qty       = (int) $this->request->getPost('qty');
        $keterangan= trim($this->request->getPost('keterangan') ?? '');

        $product = $this->produkModel->find($productId);
        if (!$product) {
            return redirect()->back()->with('error', 'Produk tidak ditemukan.');
        }

        $newStock = $product['stok'];
        if ($tipe === 'masuk') {
            $newStock += $qty;
        } elseif ($tipe === 'keluar') {
            if ($qty > $product['stok']) {
                return redirect()->back()->with('error', 'Jumlah stok keluar tidak boleh melebihi stok yang ada.');
            }
            $newStock -= $qty;
        } elseif ($tipe === 'penyesuaian') {
            $newStock = $qty;
        }

        $this->produkModel->update($productId, ['stok' => $newStock]);

        $this->stockModel->insert([
            'product_id' => $productId,
            'tipe'       => $tipe,
            'qty'        => $qty,
            'sisa_stok'  => $newStock,
            'keterangan' => $keterangan ?: "Penyesuaian stok manual ({$tipe})",
            'user_id'    => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        log_system_activity('Penyesuaian Stok', 'Stok', "Penyesuaian stok produk {$product['nama_produk']} tipe: {$tipe}, sisa stok: {$newStock}");

        return redirect()->back()->with('success', 'Stok produk berhasil diperbarui.');
    }
}
