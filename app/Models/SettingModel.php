<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['setting_key', 'setting_value', 'setting_group'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByKey($key, $default = null)
    {
        $setting = $this->where('setting_key', $key)->first();
        return $setting ? $setting['setting_value'] : $default;
    }

    public function getAllAsMap()
    {
        $rows = $this->findAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['setting_key']] = $row['setting_value'];
        }
        return $map;
    }

    public function setVal($key, $val, $group = 'general')
    {
        $exists = $this->where('setting_key', $key)->first();
        if ($exists) {
            return $this->update($exists['id'], ['setting_value' => $val]);
        }
        return $this->insert([
            'setting_key'   => $key,
            'setting_value' => $val,
            'setting_group' => $group,
        ]);
    }
}
