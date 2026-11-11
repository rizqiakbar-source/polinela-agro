<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $table = 'vouchers';

    protected $fillable = [
        'kode',
        'nama',
        'tipe',
        'diskon',
        'min_belanja',
        'max_diskon',
        'kuota',
        'terpakai',
        'tgl_mulai',
        'tgl_berakhir',
        'is_active',
    ];

    protected $casts = [
        'diskon'       => 'float',
        'min_belanja'  => 'float',
        'max_diskon'   => 'float',
        'kuota'        => 'integer',
        'terpakai'     => 'integer',
        'is_active'    => 'boolean',
        'tgl_mulai'    => 'date',
        'tgl_berakhir' => 'date',
    ];

    /**
     * Reusable & DRY: Hitung dan validasi diskon voucher berdasarkan subtotal belanja.
     * Returns: array [bool $isValid, float $discountAmount, string $message]
     */
    public function calculateDiscount(float $subtotal): array
    {
        $today = date('Y-m-d');
        $tglMulai = $this->tgl_mulai ? $this->tgl_mulai->format('Y-m-d') : null;
        $tglBerakhir = $this->tgl_berakhir ? $this->tgl_berakhir->format('Y-m-d') : null;

        if ($tglMulai && $tglMulai > $today) {
            return [false, 0.0, 'Voucher belum dapat digunakan.'];
        }
        if ($tglBerakhir && $tglBerakhir < $today) {
            return [false, 0.0, 'Voucher telah kedaluwarsa.'];
        }
        if ($this->kuota && $this->terpakai >= $this->kuota) {
            return [false, 0.0, 'Kuota pemakaian voucher telah habis.'];
        }
        if ($subtotal < $this->min_belanja) {
            return [false, 0.0, 'Minimal belanja untuk voucher ini adalah Rp ' . number_format($this->min_belanja, 0, ',', '.')];
        }

        $calcDiskon = ($this->tipe === 'persen')
            ? ($subtotal * ($this->diskon / 100))
            : $this->diskon;

        if ($this->max_diskon && $calcDiskon > $this->max_diskon) {
            $calcDiskon = $this->max_diskon;
        }

        $calcDiskon = min($calcDiskon, $subtotal);

        return [true, (float) $calcDiskon, "Voucher '{$this->nama}' berhasil digunakan!"];
    }
}
