<?php

namespace App\Models;

use CodeIgniter\Model;

class CartModel extends Model
{
    protected $table            = 'carts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'product_id', 'qty'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getUserCart($user_id)
    {
        return $this->select('carts.*, products.nama_produk, products.slug, products.harga, products.gambar_utama, products.berat_gram, products.satuan, products.stok, units.nama_unit')
                    ->join('products', 'products.id = carts.product_id', 'left')
                    ->join('units', 'units.id = products.unit_id', 'left')
                    ->where('carts.user_id', $user_id)
                    ->where('products.deleted_at IS NULL')
                    ->findAll();
    }
}
