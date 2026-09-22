<?php

namespace App\Libraries;

use App\Models\NotificationModel;

class Notification
{
    public static function send(int $userId, string $judul, string $pesan, ?string $link = null)
    {
        $notifModel = new NotificationModel();
        return $notifModel->insert([
            'user_id'    => $userId,
            'judul'      => $judul,
            'pesan'      => $pesan,
            'link'       => $link,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function sendToAdmins(string $judul, string $pesan, ?string $link = null)
    {
        $userModel = new \App\Models\UserModel();
        $admins = $userModel->whereIn('role', ['superadmin', 'admin_unit'])->findAll();
        foreach ($admins as $admin) {
            self::send($admin['id'], $judul, $pesan, $link);
        }
    }
}
