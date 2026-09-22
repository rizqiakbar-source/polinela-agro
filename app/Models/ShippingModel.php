<?php

namespace App\Models;

use CodeIgniter\Model;

class ShippingModel extends Model
{
    protected $table            = 'shippings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'order_id', 'kurir', 'layanan', 'no_resi', 'ongkir', 'estimasi',
        'penerima_nama', 'penerima_telepon', 'alamat_lengkap', 'kota', 'kode_pos', 'status'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
