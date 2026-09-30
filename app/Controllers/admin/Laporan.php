<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\LaporanModel;
use App\Models\UnitModel;
use App\Libraries\PdfGenerator;
use App\Libraries\ExcelGenerator;

class Laporan extends BaseController
{
    /** @var LaporanModel */
    protected $laporanModel;

    /** @var UnitModel */
    protected $unitModel;

    public function __construct()
    {
        $this->laporanModel = new LaporanModel();
        $this->unitModel    = new UnitModel();
    }

    public function index()
    {
        $type      = $this->request->getGet('type') ?: 'penjualan';
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');
        $unitId    = $this->request->getGet('unit_id');

        $role = session()->get('user_role');
        if ($role === 'admin_unit') {
            $unitId = session()->get('user_unit_id');
        }

        $reportData = [];
        switch ($type) {
            case 'penjualan':
                $reportData = $this->laporanModel->getPenjualan($startDate, $endDate, $unitId);
                break;
            case 'produk_terlaris':
                $reportData = $this->laporanModel->getProdukTerlaris(20, $unitId);
                break;
            case 'stok':
                $reportData = $this->laporanModel->getStokProduk($unitId);
                break;
            case 'pelanggan':
                $reportData = $this->laporanModel->getPelanggan();
                break;
            case 'keuangan':
                $reportData = $this->laporanModel->getKeuangan(date('Y', strtotime($startDate)));
                break;
            case 'per_unit':
                $reportData = $this->laporanModel->getPerUnit();
                break;
        }

        $units = $this->unitModel->where('is_active', 1)->findAll();

        $data = [
            'title'       => 'Laporan & Rekapitulasi Data - Polinela Agro Digital',
            'type'        => $type,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'unit_id'     => $unitId,
            'units'       => $units,
            'report_data' => $reportData,
            'role'        => $role,
        ];

        return view('admin/laporan/index', $data);
    }

    public function exportPdf()
    {
        $type      = $this->request->getGet('type') ?: 'penjualan';
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');
        $unitId    = $this->request->getGet('unit_id');

        $role = session()->get('user_role');
        if ($role === 'admin_unit') {
            $unitId = session()->get('user_unit_id');
        }

        $reportData = [];
        $title = 'Laporan';
        switch ($type) {
            case 'penjualan':
                $reportData = $this->laporanModel->getPenjualan($startDate, $endDate, $unitId);
                $title = 'Laporan Penjualan Produk Perkebunan';
                break;
            case 'produk_terlaris':
                $reportData = $this->laporanModel->getProdukTerlaris(20, $unitId);
                $title = 'Laporan Produk Perkebunan Terlaris';
                break;
            case 'stok':
                $reportData = $this->laporanModel->getStokProduk($unitId);
                $title = 'Laporan Stok & Inventori Komoditas Perkebunan';
                break;
            case 'pelanggan':
                $reportData = $this->laporanModel->getPelanggan();
                $title = 'Laporan Data Pelanggan & Civitas';
                break;
            case 'keuangan':
                $reportData = $this->laporanModel->getKeuangan(date('Y', strtotime($startDate)));
                $title = 'Laporan Keuangan & Arus Kas Omset';
                break;
            case 'per_unit':
                $reportData = $this->laporanModel->getPerUnit();
                $title = 'Laporan Performa Per Unit Usaha Polinela';
                break;
        }

        $data = [
            'type'        => $type,
            'title'       => $title,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'report_data' => $reportData,
        ];

        $html = view('admin/laporan/pdf_template', $data);
        return PdfGenerator::generate($html, 'Laporan_' . $type . '_' . date('Ymd'), true, 'A4', 'landscape');
    }

    public function exportExcel()
    {
        $type      = $this->request->getGet('type') ?: 'penjualan';
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');
        $unitId    = $this->request->getGet('unit_id');

        $role = session()->get('user_role');
        if ($role === 'admin_unit') {
            $unitId = session()->get('user_unit_id');
        }

        $headers = [];
        $rows = [];

        switch ($type) {
            case 'penjualan':
                $headers = ['No', 'No Pesanan', 'Tanggal', 'Nama Pembeli', 'Email', 'Unit Usaha', 'Total Produk', 'Ongkir', 'Diskon', 'Grand Total', 'Metode Bayar', 'Status'];
                $data = $this->laporanModel->getPenjualan($startDate, $endDate, $unitId);
                $no = 1;
                foreach ($data as $d) {
                    $rows[] = [
                        $no++,
                        $d['order_number'],
                        $d['created_at'],
                        $d['customer_nama'],
                        $d['customer_email'],
                        $d['nama_unit'] ?? 'Polinela',
                        $d['total_produk'],
                        $d['ongkir'],
                        $d['diskon'],
                        $d['grand_total'],
                        strtoupper($d['metode'] ?? '-'),
                        strtoupper($d['status']),
                    ];
                }
                break;

            case 'stok':
                $headers = ['No', 'ID', 'Nama Produk', 'Unit Usaha', 'Kategori', 'Harga', 'Stok Saat Ini', 'Stok Minimum', 'Satuan', 'Status'];
                $data = $this->laporanModel->getStokProduk($unitId);
                $no = 1;
                foreach ($data as $d) {
                    $rows[] = [
                        $no++,
                        $d['id'],
                        $d['nama_produk'],
                        $d['nama_unit'],
                        $d['nama_kategori'],
                        $d['harga'],
                        $d['stok'],
                        $d['stok_min'],
                        $d['satuan'],
                        strtoupper($d['status']),
                    ];
                }
                break;

            case 'produk_terlaris':
                $headers = ['No', 'Nama Produk', 'Unit Usaha', 'Kategori', 'Harga', 'Satuan', 'Total Terjual', 'Total Pendapatan'];
                $data = $this->laporanModel->getProdukTerlaris(50, $unitId);
                $no = 1;
                foreach ($data as $d) {
                    $rows[] = [
                        $no++,
                        $d['nama_produk'],
                        $d['nama_unit'],
                        $d['nama_kategori'],
                        $d['harga'],
                        $d['satuan'],
                        $d['terjual'],
                        $d['pendapatan'],
                    ];
                }
                break;

            case 'pelanggan':
                $headers = ['No', 'Nama', 'Email', 'No Telepon', 'Terdaftar Pada', 'Total Pesanan Selesai', 'Total Belanja'];
                $data = $this->laporanModel->getPelanggan();
                $no = 1;
                foreach ($data as $d) {
                    $rows[] = [
                        $no++,
                        $d['nama'],
                        $d['email'],
                        $d['no_hp'],
                        $d['created_at'],
                        $d['total_pesanan'],
                        $d['total_belanja'],
                    ];
                }
                break;

            default:
                $headers = ['No', 'Item'];
                $rows = [[1, 'Data tidak tersedia untuk tipe ini']];
                break;
        }

        return ExcelGenerator::exportCsv($headers, $rows, 'laporan_' . $type);
    }
}
