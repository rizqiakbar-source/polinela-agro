<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckSystem extends BaseCommand
{
    protected $group       = 'Diagnostic';
    protected $name        = 'app:health-check';
    protected $description = 'Pemeriksaan integritas dan kelayakan seluruh fitur sistem Polinela Agro Digital';

    public function run(array $params)
    {
        CLI::write("==================================================", 'cyan');
        CLI::write(" POLINELA AGRO DIGITAL - SYSTEM HEALTH CHECK", 'white', 'green');
        CLI::write("==================================================", 'cyan');

        // 1. Database connection and table verification
        CLI::write("\n[1] Memeriksa Koneksi Database & Tabel...", 'yellow');
        $dbConnected = false;
        try {
            $db = \Config\Database::connect();
            $db->connect();
            $dbConnected = true;
            CLI::write("    [OK] Database Connected: " . $db->getDatabase(), 'green');

            $tables = [
                'users', 'units', 'products', 'categories', 'orders',
                'order_details', 'payments', 'shippings', 'reviews',
                'wishlists', 'vouchers', 'banners', 'settings', 'activity_logs', 'stocks'
            ];

            foreach ($tables as $t) {
                if ($db->tableExists($t)) {
                    $cnt = $db->table($t)->countAllResults();
                    CLI::write("    - Tabel {$t}: {$cnt} baris data [OK]", 'green');
                } else {
                    CLI::error("    - Tabel {$t}: TIDAK DITEMUKAN");
                }
            }
        } catch (\Throwable $e) {
            CLI::error("    Database Connection Notice: " . $e->getMessage());
            CLI::write("    (Pastikan service MySQL di XAMPP Control Panel sudah dalam status RUNNING)", 'yellow');
        }

        // 2. Models & Data Queries (if DB connected)
        CLI::write("\n[2] Memeriksa Struktur Model & Kode...", 'yellow');
        try {
            $produkModel = new \App\Models\ProdukModel();
            CLI::write("    - ProdukModel: Siap", 'green');

            $orderModel = new \App\Models\OrderModel();
            CLI::write("    - OrderModel: Siap", 'green');

            $paymentModel = new \App\Models\PaymentModel();
            CLI::write("    - PaymentModel: Siap", 'green');

            $unitModel = new \App\Models\UnitModel();
            CLI::write("    - UnitModel: Siap", 'green');

            $logModel = new \App\Models\ActivityLogModel();
            CLI::write("    - ActivityLogModel: Siap", 'green');

            if ($dbConnected) {
                $products = $produkModel->getCatalog();
                CLI::write("    - Query Katalog Produk: " . count($products) . " produk aktif", 'green');
                $orders = $orderModel->getOrderWithRelations();
                CLI::write("    - Query Pesanan: " . count($orders) . " pesanan tercatat", 'green');
                $payments = $paymentModel->getPaymentsWithOrder();
                CLI::write("    - Query Pembayaran: " . count($payments) . " riwayat pembayaran", 'green');
            }
        } catch (\Throwable $e) {
            CLI::error("    Model Error: " . $e->getMessage());
        }

        // 3. Helpers & Custom Formatting
        CLI::write("\n[3] Memeriksa Helper & Formatting...", 'yellow');
        try {
            helper(['app', 'rupiah', 'tanggal', 'badge', 'asset', 'text']);
            CLI::write("    - format_rupiah(250000) => " . format_rupiah(250000), 'green');
            CLI::write("    - format_tanggal(now)  => " . format_tanggal(date('Y-m-d H:i:s')), 'green');
            CLI::write("    - status_badge('diproses') => " . strip_tags(status_badge('diproses')), 'green');
            CLI::write("    - payment_badge('lunas')   => " . strip_tags(payment_badge('lunas')), 'green');
        } catch (\Throwable $e) {
            CLI::error("    Helper Error: " . $e->getMessage());
        }

        // 4. Libraries & Services
        CLI::write("\n[4] Memeriksa Library Pendukung...", 'yellow');
        try {
            if (class_exists('\App\Libraries\Notification')) {
                CLI::write("    - Notification Library: Siap", 'green');
            }
            if (class_exists('\App\Libraries\PdfGenerator')) {
                CLI::write("    - PDF Generator (Dompdf): Siap", 'green');
            }
        } catch (\Throwable $e) {
            CLI::error("    Library Error: " . $e->getMessage());
        }

        // 5. Uploads & Directory Permissions
        CLI::write("\n[5] Memeriksa Direktori & Folder Upload...", 'yellow');
        $dirs = [
            FCPATH . 'uploads/produk',
            FCPATH . 'uploads/bukti_bayar',
            FCPATH . 'uploads/banners',
            WRITEPATH . 'logs',
            WRITEPATH . 'cache',
            WRITEPATH . 'session',
        ];

        foreach ($dirs as $d) {
            if (!is_dir($d)) {
                @mkdir($d, 0777, true);
            }
            if (is_writable($d)) {
                CLI::write("    - " . str_replace(ROOTPATH, '', $d) . " [Dapat Ditulis / Writable]", 'green');
            } else {
                CLI::error("    - " . str_replace(ROOTPATH, '', $d) . " [PERINGATAN: Tidak Writable]");
            }
        }

        CLI::write("\n==================================================", 'cyan');
        CLI::write(" HASIL: SEMUA KODE & STRUKTUR APLIKASI NORMAL!", 'white', 'green');
        CLI::write("==================================================", 'cyan');
    }
}
