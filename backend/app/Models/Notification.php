<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'judul',
        'pesan',
        'link',
        'is_read',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function sendToAdmins($judul, $pesan, $link = null, $unitId = null)
    {
        $query = User::where(function ($q) use ($unitId) {
            $q->where('role', 'superadmin');
            if ($unitId) {
                $q->orWhere(function ($sub) use ($unitId) {
                    $sub->where('role', 'admin_unit')->where('unit_id', $unitId);
                });
            } else {
                $q->orWhere('role', 'admin_unit');
            }
        });

        $admins = $query->get();
        foreach ($admins as $admin) {
            self::create([
                'user_id' => $admin->id,
                'judul'   => $judul,
                'pesan'   => $pesan,
                'link'    => $link,
                'is_read' => false,
            ]);
        }
    }
}
