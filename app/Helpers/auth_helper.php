<?php

if (!function_exists('logged_user')) {
    function logged_user(?string $field = null)
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return null;
        }

        $user = [
            'id'       => $session->get('user_id'),
            'nama'     => $session->get('user_nama'),
            'email'    => $session->get('user_email'),
            'role'     => $session->get('user_role'),
            'unit_id'  => $session->get('user_unit_id'),
            'foto'     => $session->get('user_foto'),
        ];

        return $field ? ($user[$field] ?? null) : $user;
    }
}

if (!function_exists('is_superadmin')) {
    function is_superadmin(): bool
    {
        return session()->get('user_role') === 'superadmin';
    }
}

if (!function_exists('is_unit_admin')) {
    function is_unit_admin(): bool
    {
        return session()->get('user_role') === 'admin_unit';
    }
}

if (!function_exists('is_pimpinan')) {
    function is_pimpinan(): bool
    {
        return session()->get('user_role') === 'pimpinan';
    }
}

if (!function_exists('is_konsumen')) {
    function is_konsumen(): bool
    {
        return session()->get('user_role') === 'konsumen';
    }
}

if (!function_exists('user_can')) {
    function user_can(string $module, string $action = 'read'): bool
    {
        $role = session()->get('user_role');
        if ($role === 'superadmin') {
            return true;
        }

        try {
            $permissionModel = new \App\Models\PermissionModel();
            return $permissionModel->can($role, $module, $action);
        } catch (\Exception $e) {
            return false;
        }
    }
}
