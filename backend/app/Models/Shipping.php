<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    protected $table = 'shippings';

    protected $fillable = [
        'order_id',
        'kurir',
        'layanan',
        'no_resi',
        'ongkir',
        'estimasi',
        'penerima_nama',
        'penerima_telepon',
        'alamat_lengkap',
        'kota',
        'kode_pos',
        'status',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'ongkir'   => 'float',
    ];

    protected $appends = [
        'nama_penerima',
        'no_hp',
        'ekspedisi',
        'status_pengiriman',
    ];

    public function getNamaPenerimaAttribute()
    {
        return $this->penerima_nama;
    }

    public function getNoHpAttribute()
    {
        return $this->penerima_telepon;
    }

    public function getEkspedisiAttribute()
    {
        return $this->kurir;
    }

    public function getStatusPengirimanAttribute()
    {
        return $this->status;
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
