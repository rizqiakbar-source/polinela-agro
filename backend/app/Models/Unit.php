<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'units';

    protected $fillable = [
        'nama_unit',
        'slug',
        'deskripsi',
        'gambar',
        'pic_nama',
        'pic_telepon',
        'lokasi_kebun',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'unit_id');
    }

    public function admins()
    {
        return $this->hasMany(User::class, 'unit_id')->where('role', 'admin_unit');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'unit_id');
    }
}
