<?php

if (!function_exists('format_rupiah')) {
    function format_rupiah($angka)
    {
        return 'Rp ' . number_format((float) $angka, 0, ',', '.');
    }
}

if (!function_exists('format_tanggal')) {
    function format_tanggal($datetime, $withTime = true)
    {
        if (empty($datetime)) return '-';
        $timestamp = strtotime($datetime);
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $tgl = date('d', $timestamp);
        $bln = $bulan[(int) date('m', $timestamp)];
        $thn = date('Y', $timestamp);
        $jam = date('H:i', $timestamp);

        if ($withTime) {
            return "{$tgl} {$bln} {$thn} {$jam}";
        }
        return "{$tgl} {$bln} {$thn}";
    }
}

if (!function_exists('status_badge')) {
    function status_badge($status)
    {
        $map = [
            'pending'             => ['warning', 'Menunggu Pembayaran'],
            'menunggu_verifikasi' => ['info', 'Menunggu Verifikasi'],
            'diproses'            => ['primary', 'Diproses Unit'],
            'dikirim'             => ['info', 'Sedang Dikirim'],
            'selesai'             => ['success', 'Selesai'],
            'dibatalkan'          => ['danger', 'Dibatalkan'],
        ];

        $class = $map[$status][0] ?? 'secondary';
        $label = $map[$status][1] ?? ucfirst($status);

        return "<span class=\"badge bg-{$class} px-3 py-2 rounded-pill\">{$label}</span>";
    }
}

if (!function_exists('payment_badge')) {
    function payment_badge($status)
    {
        $map = [
            'pending' => ['warning', 'Pending'],
            'lunas'   => ['success', 'Lunas / Terverifikasi'],
            'ditolak' => ['danger', 'Ditolak'],
        ];

        $class = $map[$status][0] ?? 'secondary';
        $label = $map[$status][1] ?? ucfirst($status);

        return "<span class=\"badge bg-{$class} px-3 py-2 rounded-pill\">{$label}</span>";
    }
}

if (!function_exists('current_user')) {
    function current_user()
    {
        $session = session();
        if ($session->get('logged_in')) {
            return [
                'id'       => $session->get('user_id'),
                'nama'     => $session->get('user_nama'),
                'email'    => $session->get('user_email'),
                'role'     => $session->get('user_role'),
                'unit_id'  => $session->get('user_unit_id'),
                'foto'     => $session->get('user_foto'),
            ];
        }
        return null;
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in()
    {
        return (bool) session()->get('logged_in');
    }
}

if (!function_exists('has_role')) {
    function has_role($roles)
    {
        if (!is_logged_in()) return false;
        $userRole = session()->get('user_role');
        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }
        return $userRole === $roles;
    }
}

if (!function_exists('log_system_activity')) {
    function log_system_activity($aksi, $modul, $deskripsi)
    {
        try {
            $user = current_user();
            $userId = $user ? $user['id'] : null;
            $logModel = new \App\Models\ActivityLogModel();
            $logModel->logActivity($userId, $aksi, $modul, $deskripsi);
        } catch (\Exception $e) {
            // Silently ignore log errors
        }
    }
}
