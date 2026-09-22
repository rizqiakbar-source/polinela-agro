<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\ProdukModel;
use App\Models\UserModel;
use App\Models\UnitModel;
use App\Models\SusResponseModel;
use App\Models\LaporanModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $role   = session()->get('user_role');
        $unitId = session()->get('user_unit_id');

        $orderModel   = new OrderModel();
        $produkModel  = new ProdukModel();
        $userModel    = new UserModel();
        $unitModel    = new UnitModel();
        $susModel     = new SusResponseModel();
        $laporanModel = new LaporanModel();

        // 1. Metrik Statistik
        $orderQuery = $orderModel;
        $productQuery = $produkModel->where('deleted_at IS NULL');

        if ($role === 'admin_unit' && $unitId) {
            $orderQuery->where('unit_id', $unitId);
            $productQuery->where('unit_id', $unitId);
        }

        $totalProduk  = $productQuery->countAllResults(false);
        $stokKritis   = (clone $productQuery)->where('stok <= stok_min')->countAllResults();
        $totalPesanan = (clone $orderQuery)->countAllResults();

        // Hitung total pendapatan (status diproses, dikirim, selesai)
        $db = \Config\Database::connect();
        $incomeBuilder = $db->table('orders')
                            ->selectSum('grand_total', 'omset')
                            ->whereIn('status', ['diproses', 'dikirim', 'selesai']);
        if ($role === 'admin_unit' && $unitId) {
            $incomeBuilder->where('unit_id', $unitId);
        }
        $incomeRow = $incomeBuilder->get()->getRow();
        $totalPendapatan = (float) ($incomeRow->omset ?? 0);

        // 2. Pesanan Terbaru
        $recentOrders = $orderModel->getOrderWithRelations(null, null, ($role === 'admin_unit') ? $unitId : null);
        $recentOrders = array_slice($recentOrders, 0, 6);

        // 3. Produk Terlaris
        $topProducts = $laporanModel->getProdukTerlaris(5, ($role === 'admin_unit') ? $unitId : null);

        // 4. Data Tren Penjualan Bulanan untuk Chart.js
        $monthlyChartData = $this->getMonthlySalesData($role === 'admin_unit' ? $unitId : null);

        // 5. Ringkasan SUS (System Usability Scale)
        $susSummary = $susModel->getSummary();

        // 6. Unit Usaha (khusus superadmin & pimpinan)
        $unitStats = ($role !== 'admin_unit') ? $unitModel->getUnitsWithStats() : [];

        $data = [
            'title'            => 'Dashboard Analitik - Polinela Agro Digital',
            'role'             => $role,
            'total_produk'     => $totalProduk,
            'stok_kritis'      => $stokKritis,
            'total_pesanan'    => $totalPesanan,
            'total_pendapatan' => $totalPendapatan,
            'recent_orders'    => $recentOrders,
            'top_products'     => $topProducts,
            'monthly_chart'    => $monthlyChartData,
            'sus_summary'      => $susSummary,
            'unit_stats'       => $unitStats,
        ];

        return view('admin/dashboard', $data);
    }

    private function getMonthlySalesData($unitId = null)
    {
        $db = \Config\Database::connect();
        $year = date('Y');

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $totals = array_fill(0, 12, 0);

        $builder = $db->table('orders')
                      ->select('MONTH(created_at) as bulan, SUM(grand_total) as total')
                      ->where('YEAR(created_at)', $year)
                      ->whereIn('status', ['diproses', 'dikirim', 'selesai']);

        if ($unitId) {
            $builder->where('unit_id', $unitId);
        }

        $results = $builder->groupBy('MONTH(created_at)')->get()->getResultArray();

        foreach ($results as $row) {
            $m = (int) $row['bulan'];
            if ($m >= 1 && $m <= 12) {
                $totals[$m - 1] = (float) $row['total'];
            }
        }

        return [
            'labels' => $labels,
            'totals' => $totals,
        ];
    }
}
