<?php

namespace App\Libraries;

class Xendit
{
    /**
     * Simulasi pembuatan Invoice Payment Link Xendit untuk e-commerce Polinela
     */
    public static function createInvoice(array $params): array
    {
        $externalId = $params['external_id'] ?? 'POL-' . time();
        $amount     = $params['amount'] ?? 0;
        $payerEmail = $params['payer_email'] ?? 'konsumen@polinela.ac.id';

        return [
            'id'           => 'inv_' . md5(uniqid()),
            'external_id'  => $externalId,
            'amount'       => $amount,
            'payer_email'  => $payerEmail,
            'status'       => 'PENDING',
            'invoice_url'  => base_url('pesanan/detail/' . $externalId),
            'expiry_date'  => date('Y-m-d H:i:s', strtotime('+24 hours')),
            'is_simulated' => true,
        ];
    }
}
