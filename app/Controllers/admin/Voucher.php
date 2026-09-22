<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\VoucherModel;

class Voucher extends BaseController
{
    protected $voucherModel;

    public function __construct()
    {
        $this->voucherModel = new VoucherModel();
    }

    public function index()
    {
        $vouchers = $this->voucherModel->orderBy('id', 'DESC')->findAll();

        $data = [
            'title'    => 'Kelola Voucher Promo - Polinela Agro Digital',
            'vouchers' => $vouchers,
        ];

        return view('admin/voucher/index', $data);
    }

    public function create()
    {
        $rules = [
            'kode'   => 'required|is_unique[vouchers.kode]',
            'nama'   => 'required',
            'diskon' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->voucherModel->insert([
            'kode'         => strtoupper(trim($this->request->getPost('kode'))),
            'nama'         => trim($this->request->getPost('nama')),
            'tipe'         => $this->request->getPost('tipe') ?: 'fixed',
            'diskon'       => (float) $this->request->getPost('diskon'),
            'min_belanja'  => (float) ($this->request->getPost('min_belanja') ?: 0),
            'max_diskon'   => (float) ($this->request->getPost('max_diskon') ?: null),
            'kuota'        => (int) ($this->request->getPost('kuota') ?: 100),
            'terpakai'     => 0,
            'tgl_mulai'    => $this->request->getPost('tgl_mulai') ?: null,
            'tgl_berakhir' => $this->request->getPost('tgl_berakhir') ?: null,
            'is_active'    => 1,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('admin/voucher'))->with('success', 'Voucher baru berhasil dibuat.');
    }

    public function delete($id)
    {
        $this->voucherModel->delete($id);
        return redirect()->to(base_url('admin/voucher'))->with('success', 'Voucher berhasil dihapus.');
    }
}
