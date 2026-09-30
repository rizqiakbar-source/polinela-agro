<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public static function getServerKey(): string
    {
        return env('MIDTRANS_SERVER_KEY', config('services.midtrans.server_key', ''));
    }

    public static function getClientKey(): string
    {
        return env('MIDTRANS_CLIENT_KEY', config('services.midtrans.client_key', ''));
    }

    public static function isProduction(): bool
    {
        return filter_var(env('MIDTRANS_IS_PRODUCTION', true), FILTER_VALIDATE_BOOLEAN);
    }

    public static function getSnapApiUrl(): string
    {
        return self::isProduction()
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    public static function getSnapJsUrl(): string
    {
        return self::isProduction()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    /**
     * Generate Snap Token untuk Order
     */
    public static function createSnapToken(Order $order): ?string
    {
        $serverKey = self::getServerKey();
        if (empty($serverKey)) {
            Log::warning("Midtrans server key is not configured.");
            return null;
        }

        $order->loadMissing(['user', 'shipping', 'details', 'unit']);

        $orderNumber = $order->order_number ?: "ORDER-{$order->id}";
        // Midtrans requires unique order_id. Append timestamp if retry
        $midtransOrderId = "{$orderNumber}-" . time();

        $items = [];
        if ($order->details && count($order->details) > 0) {
            foreach ($order->details as $d) {
                $items[] = [
                    'id'       => (string) ($d->product_id ?? $d->id),
                    'price'    => (int) $d->harga,
                    'quantity' => (int) $d->qty,
                    'name'     => mb_strimwidth($d->nama_produk ?? 'Produk Pertanian', 0, 45, '...'),
                ];
            }
        }

        // Add shipping fee item if any
        $ongkir = (int) ($order->shipping->ongkir ?? 0);
        if ($ongkir > 0) {
            $items[] = [
                'id'       => 'SHIPPING-FEE',
                'price'    => $ongkir,
                'quantity' => 1,
                'name'     => 'Biaya Ongkir Kurir',
            ];
        }

        // Add discount if any
        $diskon = (int) ($order->diskon ?? 0);
        if ($diskon > 0) {
            $items[] = [
                'id'       => 'DISCOUNT-VOUCHER',
                'price'    => -$diskon,
                'quantity' => 1,
                'name'     => 'Potongan Diskon Voucher',
            ];
        }

        // Recalculate gross amount to match sum of items
        $sumItems = 0;
        foreach ($items as $it) {
            $sumItems += ($it['price'] * $it['quantity']);
        }
        $grossAmount = $sumItems > 0 ? $sumItems : (int) $order->grand_total;

        $customerName = $order->shipping->nama_penerima ?? $order->user->nama ?? 'Pelanggan';
        $customerEmail = $order->user->email ?? 'customer@polinela.ac.id';
        $customerPhone = $order->shipping->no_hp ?? $order->user->no_hp ?? '081234567890';
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        $payload = [
            'transaction_details' => [
                'order_id'     => $midtransOrderId,
                'gross_amount' => (int) $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $customerName,
                'email'      => $customerEmail,
                'phone'      => $customerPhone,
                'shipping_address' => [
                    'first_name' => $customerName,
                    'phone'      => $customerPhone,
                    'address'    => $order->shipping->alamat_lengkap ?? 'Bandar Lampung',
                    'city'       => $order->shipping->kota ?? 'Bandar Lampung',
                    'postal_code'=> $order->shipping->kode_pos ?? '35144',
                    'country_code' => 'IDN',
                ],
            ],
            'item_details' => $items,
            'callbacks' => [
                'finish' => "{$frontendUrl}/pesanan/{$order->id}?status=success",
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            ])->post(self::getSnapApiUrl(), $payload);

            if ($response->successful()) {
                $data = $response->json();
                $snapToken = $data['token'] ?? null;
                
                // Simpan token dan Midtrans Order ID di database untuk auto-sync
                try {
                    $order->update([
                        'snap_token'    => $snapToken,
                        'snap_order_id' => $midtransOrderId,
                    ]);
                    if ($order->payment) {
                        $order->payment->update([
                            'no_transaksi' => $midtransOrderId,
                        ]);
                    }
                } catch (\Exception $saveEx) {
                    // Ignore if column doesn't exist
                }

                Log::info("Midtrans Snap Token generated successfully for Order #{$order->id}: {$snapToken}");
                return $snapToken;
            } else {
                Log::warning("Midtrans Snap API Error: " . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error("Midtrans Snap Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cek Status Transaksi Langsung ke API Midtrans
     */
    public static function checkTransactionStatus(string $orderId): ?array
    {
        $serverKey = self::getServerKey();
        $baseUrl = self::isProduction()
            ? "https://api.midtrans.com/v2/{$orderId}/status"
            : "https://api.sandbox.midtrans.com/v2/{$orderId}/status";

        try {
            $response = Http::withHeaders([
                'Accept'        => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            ])->get($baseUrl);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning("Midtrans checkTransactionStatus error: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Verifikasi Signature Key Notifikasi Midtrans
     */
    public static function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $serverKey = self::getServerKey();
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        return hash_equals($expectedSignature, $signatureKey);
    }
}
