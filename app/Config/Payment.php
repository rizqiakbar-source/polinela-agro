<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Payment extends BaseConfig
{
    /**
     * Mode operasional: 'sandbox' / 'production'
     */
    public string $mode = 'sandbox';

    /**
     * Konfigurasi Midtrans Payment Gateway
     */
    public array $midtrans = [
        'is_production' => false,
        'server_key'    => 'SB-Mid-server-TEST-KEY-POLINELA',
        'client_key'    => 'SB-Mid-client-TEST-KEY-POLINELA',
        'is_sanitized'   => true,
        'is_3ds'         => true,
    ];

    /**
     * Konfigurasi Xendit Payment Gateway
     */
    public array $xendit = [
        'is_production' => false,
        'secret_key'    => 'xnd_development_TEST_SECRET_POLINELA',
        'public_key'    => 'xnd_public_development_TEST_PUBLIC_POLINELA',
    ];

    /**
     * Rekening Bank Resmi Pengelola Kampus Polinela
     */
    public array $bankAccounts = [
        [
            'bank'       => 'Bank Mandiri',
            'no_rekening'=> '114-00-8899123-4',
            'atas_nama'  => 'TEFA POLINELA AGRO',
        ],
        [
            'bank'       => 'Bank BNI',
            'no_rekening'=> '089-123-4567',
            'atas_nama'  => 'POLITEKNIK NEGERI LAMPUNG',
        ],
        [
            'bank'       => 'Bank BRI',
            'no_rekening'=> '0098-01-002233-50-8',
            'atas_nama'  => 'UNIT PRODUKSI POLINELA',
        ],
    ];

    /**
     * Metode pembayaran yang diaktifkan
     */
    public array $enabledMethods = ['transfer', 'cod', 'qris', 'va'];
}
