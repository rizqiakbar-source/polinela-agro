<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stocks';
    const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'tipe',
        'qty',
        'sisa_stok',
        'keterangan',
        'user_id',
    ];

    protected $casts = [
        'qty'       => 'integer',
        'sisa_stok' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
