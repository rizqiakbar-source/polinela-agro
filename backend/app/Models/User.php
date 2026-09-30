<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'no_hp',
        'role',
        'foto',
        'avatar',
        'unit_id',
        'alamat',
        'is_active',
    ];

    protected $appends = [
        'foto_url',
        'avatar_url',
        'nama_lengkap',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getFotoUrlAttribute()
    {
        $foto = $this->foto ?? $this->avatar;
        if (!$foto) return null;
        if (str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://')) {
            return $foto;
        }
        return url('uploads/avatars/' . $foto);
    }

    public function getAvatarUrlAttribute()
    {
        return $this->foto_url;
    }

    public function getNamaLengkapAttribute()
    {
        return $this->nama;
    }

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'unit_id'    => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'user_id');
    }
}
