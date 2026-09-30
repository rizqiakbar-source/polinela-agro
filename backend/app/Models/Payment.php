<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'order_id',
        'metode',
        'bank',
        'no_rekening_tujuan',
        'atas_nama',
        'no_transaksi',
        'bukti_bayar',
        'status',
        'verified_by',
        'verified_at',
        'catatan',
    ];

    protected $casts = [
        'order_id'    => 'integer',
        'verified_by' => 'integer',
        'verified_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
