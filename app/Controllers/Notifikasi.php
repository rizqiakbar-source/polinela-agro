<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class Notifikasi extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $userId = session()->get('user_id');
        $notifModel = new NotificationModel();
        $notifications = $notifModel->getUserNotifications($userId, 30);

        // Tandai semua sebagai dibaca
        $notifModel->where('user_id', $userId)->set(['is_read' => 1])->update();

        $data = [
            'title'         => 'Notifikasi Saya - Polinela Agro Digital',
            'notifications' => $notifications,
        ];

        return view('frontend/notifikasi', $data);
    }
}
