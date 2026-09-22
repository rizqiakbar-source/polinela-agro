<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('logged_in')) {
            session()->setFlashdata('error', 'Akses dibatasi. Silakan login terlebih dahulu.');
            return redirect()->to(base_url('login'));
        }

        $role = session()->get('user_role');
        if (!in_array($role, ['superadmin', 'admin_unit', 'pimpinan'])) {
            session()->setFlashdata('error', 'Anda tidak memiliki hak akses ke panel manajemen.');
            return redirect()->to(base_url('/'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
