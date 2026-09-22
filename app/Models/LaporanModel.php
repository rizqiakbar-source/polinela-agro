<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanModel extends Model
{
    // Laporan 1: Penjualan per periode
    public function getPenjualan($startDate = null, $endDate = null, $unitId = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('orders')
                      ->select('orders.*, users.nama as customer_nama, users.email as customer_email, payments.metode, payments.status as payment_status, units.nama_unit')
                      ->join('users', 'users.id = orders.user_id', 'left')
                      ->join('payments', 'payments.order_id = orders.id', 'left')
                      ->join('units', 'units.id = orders.unit_id', 'left');

        if ($startDate) {
            $builder->where('DATE(orders.created_at) >=', $startDate);
        }
        if ($endDate) {
            $builder->where('DATE(orders.created_at) <=', $endDate);
        }
        if ($unitId) {
            $builder->where('orders.unit_id', $unitId);
        }

        return $builder->orderBy('orders.id', 'DESC')->get()->getResultArray();
    }

    // Laporan 2: Produk Terlaris
    public function getProdukTerlaris($limit = 10, $unitId = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('products')
                      ->select('products.id, products.nama_produk, products.harga, products.satuan, categories.nama_kategori, units.nama_unit,
                                COALESCE(SUM(order_details.qty), products.total_terjual) as terjual,
                                COALESCE(SUM(order_details.subtotal), products.total_terjual * products.harga) as pendapatan')
                      ->join('categories', 'categories.id = products.category_id', 'left')
                      ->join('units', 'units.id = products.unit_id', 'left')
                      ->join('order_details', 'order_details.product_id = products.id', 'left')
                      ->where('products.deleted_at IS NULL');

        if ($unitId) {
            $builder->where('products.unit_id', $unitId);
        }

        return $builder->groupBy('products.id')
                       ->orderBy('terjual', 'DESC')
                       ->limit($limit)
                       ->get()
                       ->getResultArray();
    }

    // Laporan 3: Stok Produk
    public function getStokProduk($unitId = null, $stokKritis = false)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('products')
                      ->select('products.*, categories.nama_kategori, units.nama_unit')
                      ->join('categories', 'categories.id = products.category_id', 'left')
                      ->join('units', 'units.id = products.unit_id', 'left')
                      ->where('products.deleted_at IS NULL');

        if ($unitId) {
            $builder->where('products.unit_id', $unitId);
        }
        if ($stokKritis) {
            $builder->where('products.stok <= products.stok_min');
        }

        return $builder->orderBy('products.stok', 'ASC')->get()->getResultArray();
    }

    // Laporan 4: Pelanggan
    public function getPelanggan()
    {
        $db = \Config\Database::connect();
        return $db->table('users')
                  ->select('users.id, users.nama, users.email, users.no_hp, users.created_at,
                            COUNT(orders.id) as total_pesanan,
                            COALESCE(SUM(orders.grand_total), 0) as total_belanja')
                  ->join('orders', 'orders.user_id = users.id AND orders.status = "selesai"', 'left')
                  ->where('users.role', 'konsumen')
                  ->groupBy('users.id')
                  ->orderBy('total_belanja', 'DESC')
                  ->get()
                  ->getResultArray();
    }

    // Laporan 5: Keuangan (Omset harian / bulanan)
    public function getKeuangan($year = null)
    {
        $db = \Config\Database::connect();
        $year = $year ?: date('Y');

        return $db->table('orders')
                  ->select('DATE(created_at) as tanggal, COUNT(id) as total_transaksi, SUM(grand_total) as total_pendapatan')
                  ->where('YEAR(created_at)', $year)
                  ->whereIn('status', ['diproses', 'dikirim', 'selesai'])
                  ->groupBy('DATE(created_at)')
                  ->orderBy('tanggal', 'DESC')
                  ->get()
                  ->getResultArray();
    }

    // Laporan 6: Per Unit Usaha
    public function getPerUnit()
    {
        $db = \Config\Database::connect();
        return $db->table('units')
                  ->select('units.id, units.nama_unit, units.pj_nama, units.kontak,
                            COUNT(DISTINCT products.id) as total_produk,
                            COALESCE(SUM(order_details.qty), 0) as total_item_terjual,
                            COALESCE(SUM(order_details.subtotal), 0) as total_omset')
                  ->join('products', 'products.unit_id = units.id AND products.deleted_at IS NULL', 'left')
                  ->join('order_details', 'order_details.product_id = products.id', 'left')
                  ->groupBy('units.id')
                  ->orderBy('total_omset', 'DESC')
                  ->get()
                  ->getResultArray();
    }
}
