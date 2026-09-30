<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_details';
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'nama_produk',
        'harga',
        'qty',
        'subtotal',
        'berat',
    ];

    protected $casts = [
        'order_id'   => 'integer',
        'product_id' => 'integer',
        'harga'      => 'float',
        'qty'        => 'integer',
        'subtotal'   => 'float',
        'berat'      => 'integer',
    ];

    protected $appends = [
        'harga_satuan',
        'jumlah',
    ];

    public function getHargaSatuanAttribute()
    {
        return $this->harga;
    }

    public function getJumlahAttribute()
    {
        return $this->qty;
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
