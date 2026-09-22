<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdukModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'unit_id', 'category_id', 'nama_produk', 'slug', 'deskripsi', 
        'harga', 'berat_gram', 'satuan', 'stok', 'stok_min', 'gambar_utama', 
        'status', 'featured', 'rating_avg', 'total_terjual'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getCatalog($filter = [])
    {
        $builder = $this->select('products.*, categories.nama_kategori, categories.slug as kategori_slug, units.nama_unit, units.slug as unit_slug')
                        ->join('categories', 'categories.id = products.category_id', 'left')
                        ->join('units', 'units.id = products.unit_id', 'left')
                        ->where('products.status', 'aktif')
                        ->where('products.deleted_at IS NULL');

        if (!empty($filter['category'])) {
            $builder->groupStart()
                    ->where('categories.slug', $filter['category'])
                    ->orWhere('categories.id', $filter['category'])
                    ->groupEnd();
        }

        if (!empty($filter['unit_id'])) {
            $builder->where('products.unit_id', $filter['unit_id']);
        }

        if (!empty($filter['search'])) {
            $builder->groupStart()
                    ->like('products.nama_produk', $filter['search'])
                    ->orLike('products.deskripsi', $filter['search'])
                    ->groupEnd();
        }

        if (!empty($filter['min_price'])) {
            $builder->where('products.harga >=', $filter['min_price']);
        }
        if (!empty($filter['max_price'])) {
            $builder->where('products.harga <=', $filter['max_price']);
        }

        // Sorting
        $sort = $filter['sort'] ?? 'latest';
        switch ($sort) {
            case 'price_low':
                $builder->orderBy('products.harga', 'ASC');
                break;
            case 'price_high':
                $builder->orderBy('products.harga', 'DESC');
                break;
            case 'popular':
                $builder->orderBy('products.total_terjual', 'DESC');
                break;
            case 'rating':
                $builder->orderBy('products.rating_avg', 'DESC');
                break;
            case 'latest':
            default:
                $builder->orderBy('products.id', 'DESC');
                break;
        }

        $limit = $filter['limit'] ?? 12;
        return $builder->findAll($limit);
    }

    public function getFeatured($limit = 6)
    {
        return $this->select('products.*, categories.nama_kategori, units.nama_unit')
                    ->join('categories', 'categories.id = products.category_id', 'left')
                    ->join('units', 'units.id = products.unit_id', 'left')
                    ->where('products.status', 'aktif')
                    ->where('products.featured', 1)
                    ->where('products.deleted_at IS NULL')
                    ->orderBy('products.rating_avg', 'DESC')
                    ->findAll($limit);
    }

    public function getDetailBySlug($slug)
    {
        return $this->select('products.*, categories.nama_kategori, categories.slug as kategori_slug, units.nama_unit, units.slug as unit_slug, units.lokasi as unit_lokasi, units.kontak as unit_kontak')
                    ->join('categories', 'categories.id = products.category_id', 'left')
                    ->join('units', 'units.id = products.unit_id', 'left')
                    ->where('products.slug', $slug)
                    ->where('products.deleted_at IS NULL')
                    ->first();
    }

    public function getAdminProducts($unit_id = null)
    {
        $builder = $this->select('products.*, categories.nama_kategori, units.nama_unit')
                        ->join('categories', 'categories.id = products.category_id', 'left')
                        ->join('units', 'units.id = products.unit_id', 'left')
                        ->where('products.deleted_at IS NULL');
        if ($unit_id) {
            $builder->where('products.unit_id', $unit_id);
        }
        return $builder->orderBy('products.id', 'DESC')->findAll();
    }
}
