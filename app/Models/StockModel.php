<?php

namespace App\Models;

use CodeIgniter\Model;

class StockModel extends Model
{
    protected $table            = 'stocks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'product_id', 'tipe', 'qty', 'sisa_stok', 'keterangan', 'user_id', 'created_at'
    ];

    protected $useTimestamps = false;

    public function getStockHistory($product_id = null, $unit_id = null)
    {
        $builder = $this->select('stocks.*, products.nama_produk, products.satuan, users.nama as user_nama, units.nama_unit')
                        ->join('products', 'products.id = stocks.product_id', 'left')
                        ->join('units', 'units.id = products.unit_id', 'left')
                        ->join('users', 'users.id = stocks.user_id', 'left');

        if ($product_id) {
            $builder->where('stocks.product_id', $product_id);
        }
        if ($unit_id) {
            $builder->where('products.unit_id', $unit_id);
        }

        return $builder->orderBy('stocks.id', 'DESC')->findAll();
    }
}
