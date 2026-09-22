<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderDetailModel extends Model
{
    protected $table            = 'order_details';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'order_id', 'product_id', 'nama_produk', 'harga', 'qty', 'subtotal', 'berat'
    ];

    protected $useTimestamps = false;

    public function getDetailsByOrderId($order_id)
    {
        return $this->select('order_details.*, products.gambar_utama, products.slug, products.satuan, products.unit_id, units.nama_unit')
                    ->join('products', 'products.id = order_details.product_id', 'left')
                    ->join('units', 'units.id = products.unit_id', 'left')
                    ->where('order_details.order_id', $order_id)
                    ->findAll();
    }
}
