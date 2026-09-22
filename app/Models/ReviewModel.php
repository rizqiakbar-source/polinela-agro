<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table            = 'reviews';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'product_id', 'order_id', 'user_id', 'rating', 'komentar', 'status', 'reply'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getProductReviews($product_id, $status = 'approved')
    {
        $builder = $this->select('reviews.*, users.nama as user_nama, users.foto as user_foto')
                        ->join('users', 'users.id = reviews.user_id', 'left')
                        ->where('reviews.product_id', $product_id);

        if ($status) {
            $builder->where('reviews.status', $status);
        }

        return $builder->orderBy('reviews.id', 'DESC')->findAll();
    }

    public function getAllReviewsWithDetails($status = null)
    {
        $builder = $this->select('reviews.*, users.nama as user_nama, users.email as user_email, products.nama_produk, products.gambar_utama')
                        ->join('users', 'users.id = reviews.user_id', 'left')
                        ->join('products', 'products.id = reviews.product_id', 'left');

        if ($status) {
            $builder->where('reviews.status', $status);
        }

        return $builder->orderBy('reviews.id', 'DESC')->findAll();
    }
}
