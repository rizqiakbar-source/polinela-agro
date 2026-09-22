<?php

namespace App\Libraries;

class Midtrans
{
    protected string $serverKey;
    protected string $clientKey;
    protected bool $isProduction;

    public function __construct()
    {
        $settingModel = new \App\Models\SettingModel();
        $this->serverKey = $settingModel->getByKey('midtrans_server_key', 'SB-Mid-server-polinela-demo');
        $this->clientKey = $settingModel->getByKey('midtrans_client_key', 'SB-Mid-client-polinela-demo');
        $this->isProduction = false;
    }

    /**
     * Membuat Snap Token atau Simulator QRIS / VA
     */
    public function createTransaction(array $params): array
    {
        $orderId = $params['order_id'] ?? 'ORD-' . time();
        $grossAmount = $params['gross_amount'] ?? 0;

        // Simulasi Snap Token & QRIS payload untuk mode lokal / prototype
        $mockToken = 'SNAP-' . md5($orderId . microtime(true));
        $mockRedirectUrl = base_url('pembayaran/simulasi/' . $orderId);

        return [
            'token'        => $mockToken,
            'redirect_url' => $mockRedirectUrl,
            'order_id'     => $orderId,
            'gross_amount' => $grossAmount,
            'client_key'   => $this->clientKey,
        ];
    }
}
