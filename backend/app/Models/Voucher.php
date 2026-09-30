<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $table = 'vouchers';

    protected $fillable = [
        'kode',
        'nama_promo',
        'tipe',
        'nilai',
        'min_belanja',
        'kuota',
        'terpakai',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    protected $casts = [
        'nilai'       => 'float',
        'min_belanja' => 'float',
        'kuota'       => 'integer',
        'terpakai'    => 'integer',
        'is_active'   => 'boolean',
    ];
}
