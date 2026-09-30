<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'unit_id',
        'category_id',
        'nama_produk',
        'slug',
        'deskripsi',
        'harga',
        'stok',
        'satuan',
        'berat_gram',
        'gambar_utama',
        'status',
        'total_terjual',
    ];

    protected $casts = [
        'unit_id'       => 'integer',
        'category_id'   => 'integer',
        'harga'         => 'float',
        'stok'          => 'integer',
        'berat_gram'    => 'integer',
        'total_terjual' => 'integer',
    ];

    protected $appends = [
        'total_stok',
        'is_active',
        'image_url',
    ];

    public function getTotalStokAttribute()
    {
        return (int) $this->stok;
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'aktif';
    }

    public function getImageUrlAttribute()
    {
        if (!$this->gambar_utama) return null;
        if (str_starts_with($this->gambar_utama, 'http://') || str_starts_with($this->gambar_utama, 'https://')) {
            return $this->gambar_utama;
        }
        return url('uploads/produk/' . $this->gambar_utama);
    }


    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'product_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id');
    }
}
