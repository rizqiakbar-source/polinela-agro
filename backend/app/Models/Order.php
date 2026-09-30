<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'user_id',
        'unit_id',
        'total_produk',
        'ongkir',
        'diskon',
        'kode_voucher',
        'grand_total',
        'status',
        'catatan',
    ];

    protected $casts = [
        'user_id'      => 'integer',
        'unit_id'      => 'integer',
        'total_produk' => 'float',
        'ongkir'       => 'float',
        'diskon'       => 'float',
        'grand_total'  => 'float',
    ];

    protected $appends = [
        'no_pesanan',
        'total_harga',
        'ongkos_kirim',
        'total_akhir',
    ];

    public function getNoPesananAttribute()
    {
        return $this->order_number;
    }

    public function getTotalHargaAttribute()
    {
        return $this->total_produk;
    }

    public function getOngkosKirimAttribute()
    {
        return $this->ongkir;
    }

    public function getTotalAkhirAttribute()
    {
        return $this->grand_total;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'order_id');
    }

    public function shipping()
    {
        return $this->hasOne(Shipping::class, 'order_id');
    }
}
