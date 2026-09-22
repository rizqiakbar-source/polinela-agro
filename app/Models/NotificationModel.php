<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'judul', 'pesan', 'link', 'is_read', 'created_at'];

    protected $useTimestamps = false;

    public function getUserNotifications($user_id, $limit = 10)
    {
        return $this->where('user_id', $user_id)
                    ->orderBy('id', 'DESC')
                    ->findAll($limit);
    }
}
