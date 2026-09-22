<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'order_number', 'user_id', 'unit_id', 'total_produk', 'ongkir', 
        'diskon', 'kode_voucher', 'grand_total', 'status', 'catatan'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function generateOrderNumber()
    {
        return 'PNA-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }

    public function getOrderWithRelations($id = null, $user_id = null, $unit_id = null)
    {
        $builder = $this->select('orders.*, users.nama as customer_nama, users.email as customer_email, users.no_hp as customer_phone,
            payments.metode as payment_metode, payments.status as payment_status, payments.bukti_bayar, payments.bank,
            shippings.kurir, shippings.layanan, shippings.no_resi, shippings.status as shipping_status, shippings.alamat_lengkap, shippings.penerima_nama, shippings.penerima_telepon, shippings.kota')
            ->join('users', 'users.id = orders.user_id', 'left')
            ->join('payments', 'payments.order_id = orders.id', 'left')
            ->join('shippings', 'shippings.order_id = orders.id', 'left');

        if ($id) {
            $builder->where('orders.id', $id);
            return $builder->first();
        }

        if ($user_id) {
            $builder->where('orders.user_id', $user_id);
        }

        if ($unit_id) {
            $builder->where('orders.unit_id', $unit_id);
        }

        return $builder->orderBy('orders.id', 'DESC')->findAll();
    }
}
