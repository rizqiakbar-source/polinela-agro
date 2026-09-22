<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'aksi', 'modul', 'deskripsi', 'ip_address', 'user_agent', 'created_at'
    ];

    protected $useTimestamps = false;

    public function logActivity($userId, $aksi, $modul, $deskripsi)
    {
        $request = \Config\Services::request();
        return $this->insert([
            'user_id'    => $userId,
            'aksi'       => $aksi,
            'modul'      => $modul,
            'deskripsi'  => $deskripsi,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => substr((string) $request->getUserAgent(), 0, 250),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getLogsWithUser($limit = 50)
    {
        return $this->select('activity_logs.*, users.nama as user_nama, users.role as user_role')
                    ->join('users', 'users.id = activity_logs.user_id', 'left')
                    ->orderBy('activity_logs.id', 'DESC')
                    ->findAll($limit);
    }
}
