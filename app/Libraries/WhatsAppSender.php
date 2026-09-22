<?php

namespace App\Libraries;

class WhatsAppSender
{
    /**
     * Membuat tautan wa.me pesan pesanan langsung ke nomor admin / konsumen
     */
    public static function createOrderLink(string $phoneNumber, array $orderData): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $orderNumber = $orderData['order_number'] ?? '-';
        $grandTotal  = isset($orderData['grand_total']) ? 'Rp ' . number_format($orderData['grand_total'], 0, ',', '.') : '-';
        $nama        = $orderData['customer_nama'] ?? 'Pelanggan';

        $msg = "Halo Kak {$nama},\n\nTerima kasih telah berbelanja produk perkebunan di *Polinela Agro Digital*.\n\nDetail Pesanan Anda:\n- *No Pesanan:* #{$orderNumber}\n- *Total Bayar:* {$grandTotal}\n\nSilakan cek status dan unduh invoice resmi Anda di:\n" . base_url('pesanan/detail/' . $orderNumber) . "\n\n_Salam Hangat,_\n*Teaching Factory Polinela Agro*";

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($msg);
    }
}
