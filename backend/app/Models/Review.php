<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';

    protected $fillable = [
        'product_id',
        'order_id',
        'user_id',
        'rating',
        'komentar',
        'status',
        'reply',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'order_id'   => 'integer',
        'user_id'    => 'integer',
        'rating'     => 'integer',
    ];

    protected $appends = [
        'ulasan',
    ];

    public function getUlasanAttribute()
    {
        return $this->komentar;
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
