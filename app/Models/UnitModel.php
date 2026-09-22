<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitModel extends Model
{
    protected $table            = 'units';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_unit', 'slug', 'deskripsi', 'logo', 'pj_nama', 'kontak', 'lokasi', 'is_active'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getUnitsWithStats()
    {
        return $this->select('units.*, 
            COUNT(DISTINCT products.id) as total_produk, 
            COALESCE(SUM(order_details.qty), 0) as total_terjual,
            COALESCE(SUM(order_details.subtotal), 0) as total_omset')
            ->join('products', 'products.unit_id = units.id AND products.deleted_at IS NULL', 'left')
            ->join('order_details', 'order_details.product_id = products.id', 'left')
            ->groupBy('units.id')
            ->findAll();
    }
}
