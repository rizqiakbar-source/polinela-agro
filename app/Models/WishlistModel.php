<?php

namespace App\Models;

use CodeIgniter\Model;

class WishlistModel extends Model
{
    protected $table            = 'wishlists';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'product_id', 'created_at'];

    protected $useTimestamps = false;

    public function getUserWishlist($user_id)
    {
        return $this->select('wishlists.*, products.nama_produk, products.slug, products.harga, products.gambar_utama, products.satuan, products.stok, categories.nama_kategori, units.nama_unit')
                    ->join('products', 'products.id = wishlists.product_id', 'left')
                    ->join('categories', 'categories.id = products.category_id', 'left')
                    ->join('units', 'units.id = products.unit_id', 'left')
                    ->where('wishlists.user_id', $user_id)
                    ->where('products.deleted_at IS NULL')
                    ->findAll();
    }
}
