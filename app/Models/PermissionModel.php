<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table            = 'permissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'role',
        'module',
        'can_create',
        'can_read',
        'can_update',
        'can_delete',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Memeriksa apakah role tertentu memiliki izin aksi pada modul
     */
    public function can(string $role, string $module, string $action = 'read'): bool
    {
        $perm = $this->where('role', $role)
                     ->where('module', $module)
                     ->first();

        if (!$perm) {
            return false;
        }

        $column = 'can_' . strtolower($action);
        return isset($perm[$column]) && (int)$perm[$column] === 1;
    }

    /**
     * Mengambil seluruh matriks izin berdasarkan role
     */
    public function getRolePermissions(string $role): array
    {
        return $this->where('role', $role)->findAll();
    }
}
