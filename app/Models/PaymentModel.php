<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table            = 'payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'order_id', 'metode', 'bank', 'no_rekening_tujuan', 'atas_nama',
        'no_transaksi', 'bukti_bayar', 'status', 'verified_by', 'verified_at', 'catatan'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getPaymentsWithOrder($status = null, $unitId = null)
    {
        $builder = $this->select('payments.*, orders.order_number, orders.unit_id, orders.grand_total, orders.grand_total as jumlah, units.nama_unit, users.nama as customer_nama, users.email as customer_email, verifier.nama as verifier_nama')
                        ->join('orders', 'orders.id = payments.order_id', 'left')
                        ->join('units', 'units.id = orders.unit_id', 'left')
                        ->join('users', 'users.id = orders.user_id', 'left')
                        ->join('users as verifier', 'verifier.id = payments.verified_by', 'left');

        if ($status) {
            $builder->where('payments.status', $status);
        }

        if ($unitId) {
            $builder->where('orders.unit_id', $unitId);
        }

        return $builder->orderBy('payments.id', 'DESC')->findAll();
    }
}
