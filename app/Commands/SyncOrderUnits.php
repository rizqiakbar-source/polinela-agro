<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SyncOrderUnits extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'order:sync-units';
    protected $description = 'Sinkronisasi unit_id pada tabel orders dari data produk';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $orders = $db->query("SELECT id, order_number, unit_id FROM orders WHERE unit_id IS NULL OR unit_id = 0")->getResultArray();
        
        CLI::write("Ditemukan " . count($orders) . " pesanan tanpa unit_id.", 'yellow');

        $updated = 0;
        foreach ($orders as $o) {
            $detail = $db->query("SELECT products.unit_id FROM order_details JOIN products ON products.id = order_details.product_id WHERE order_details.order_id = ? LIMIT 1", [$o['id']])->getRowArray();
            if ($detail && !empty($detail['unit_id'])) {
                $db->table('orders')->where('id', $o['id'])->update(['unit_id' => $detail['unit_id']]);
                CLI::write("Pesanan #{$o['order_number']} (ID: {$o['id']}) berhasil disinkronkan ke Unit ID {$detail['unit_id']}", 'green');
                $updated++;
            }
        }

        CLI::write("Selesai! {$updated} pesanan berhasil diperbarui.", 'cyan');
    }
}
