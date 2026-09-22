<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriModel extends Model
{
    protected $table            = 'categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_kategori', 'slug', 'icon', 'deskripsi', 'is_active'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getCategoriesWithCount()
    {
        return $this->select('categories.*, COUNT(products.id) as product_count')
                    ->join('products', 'products.category_id = categories.id AND products.deleted_at IS NULL AND products.status = "aktif"', 'left')
                    ->where('categories.is_active', 1)
                    ->groupBy('categories.id')
                    ->findAll();
    }
}
