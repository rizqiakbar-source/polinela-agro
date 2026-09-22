<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama', 'email', 'password', 'no_hp', 'role', 'foto', 'unit_id', 'alamat', 'is_active'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getWithUnit($id = null)
    {
        $builder = $this->select('users.*, units.nama_unit')
                        ->join('units', 'units.id = users.unit_id', 'left');
        if ($id) {
            return $builder->where('users.id', $id)->first();
        }
        return $builder->orderBy('users.id', 'ASC')->findAll();
    }
}
