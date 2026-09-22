<?php

namespace App\Models;

use CodeIgniter\Model;

class VoucherModel extends Model
{
    protected $table            = 'vouchers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kode', 'nama', 'tipe', 'diskon', 'min_belanja', 'max_diskon',
        'kuota', 'terpakai', 'tgl_mulai', 'tgl_berakhir', 'is_active'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function validateVoucher($kode, $totalBelanja)
    {
        $today = date('Y-m-d');
        $voucher = $this->where('kode', strtoupper(trim($kode)))
                        ->where('is_active', 1)
                        ->first();

        if (!$voucher) {
            return ['valid' => false, 'message' => 'Kode voucher tidak ditemukan atau tidak aktif.'];
        }

        if ($voucher['tgl_mulai'] && $voucher['tgl_mulai'] > $today) {
            return ['valid' => false, 'message' => 'Voucher belum berlaku.'];
        }

        if ($voucher['tgl_berakhir'] && $voucher['tgl_berakhir'] < $today) {
            return ['valid' => false, 'message' => 'Masa berlaku voucher telah berakhir.'];
        }

        if ($voucher['kuota'] > 0 && $voucher['terpakai'] >= $voucher['kuota']) {
            return ['valid' => false, 'message' => 'Kuota pemakaian voucher telah habis.'];
        }

        if ($totalBelanja < $voucher['min_belanja']) {
            return [
                'valid' => false, 
                'message' => 'Minimum belanja untuk voucher ini adalah Rp ' . number_format($voucher['min_belanja'], 0, ',', '.')
            ];
        }

        // Hitung nilai diskon
        $nilaiDiskon = 0;
        if ($voucher['tipe'] === 'persen') {
            $nilaiDiskon = ($totalBelanja * $voucher['diskon']) / 100;
            if ($voucher['max_diskon'] && $nilaiDiskon > $voucher['max_diskon']) {
                $nilaiDiskon = $voucher['max_diskon'];
            }
        } else {
            $nilaiDiskon = $voucher['diskon'];
        }

        return [
            'valid'        => true,
            'voucher'      => $voucher,
            'nilai_diskon' => $nilaiDiskon,
            'message'      => 'Voucher berhasil digunakan! Diskon Rp ' . number_format($nilaiDiskon, 0, ',', '.')
        ];
    }
}
