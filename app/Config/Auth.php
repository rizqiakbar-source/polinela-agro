<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Auth extends BaseConfig
{
    /**
     * Waktu berlaku sesi login dalam detik (7200 = 2 jam)
     */
    public int $sessionExpiration = 7200;

    /**
     * Algoritma hash password (PASSWORD_BCRYPT)
     */
    public int|string|null $hashAlgorithm = PASSWORD_BCRYPT;

    /**
     * Cost factor untuk algoritma bcrypt
     */
    public int $hashCost = 10;

    /**
     * Batas percobaan login gagal sebelum rate limit (5 kali per menit)
     */
    public int $maxLoginAttempts = 5;

    /**
     * Waktu pembekuan (lockout) dalam detik setelah mencapai batas percobaan (60 detik)
     */
    public int $lockoutTime = 60;

    /**
     * Role yang diperbolehkan mengakses halaman admin
     */
    public array $adminRoles = ['superadmin', 'admin_unit', 'pimpinan'];
}
