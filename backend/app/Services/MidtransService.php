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

        $order->loadMissing(['user', 'shipping', 'details', 'unit', 'payment']);

        // Kumpulkan seluruh sub-order dalam 1 transaksi (multi-unit checkout berbagi no_transaksi TRX-...)
        $sharedTrx = $order->payment?->no_transaksi;
        $orders = collect([$order]);
        if (!empty($sharedTrx) && !str_starts_with($sharedTrx, 'PNA-') && !str_starts_with($sharedTrx, 'ORDER-')) {
            $siblingIds = \App\Models\Payment::where('no_transaksi', $sharedTrx)->pluck('order_id');
            if ($siblingIds->count() > 1) {
                $siblingOrders = Order::with(['details', 'shipping', 'unit', 'payment'])
                    ->whereIn('id', $siblingIds)
                    ->where('user_id', $order->user_id)
                    ->where('status', 'menunggu_pembayaran')
                    ->orderBy('id')
                    ->get();
                if ($siblingOrders->isNotEmpty()) {
                    $orders = $siblingOrders;
                }
            }
        }

        $isConsolidated = $orders->count() > 1;
        $orderNumber = $order->order_number ?: "ORDER-{$order->id}";
        $baseIdentifier = ($isConsolidated && !empty($sharedTrx)) ? $sharedTrx : $orderNumber;
        // Midtrans requires unique order_id (max 50 chars). Append timestamp if retry.
        $midtransOrderId = "{$baseIdentifier}-" . time();

        $items = [];
        $totalProductAmount = 0;
        $totalOngkir = 0;
        $totalDiskon = 0;

        foreach ($orders as $o) {
            $unitPrefix = ($isConsolidated && $o->unit) ? '[' . mb_strimwidth($o->unit->nama_unit, 0, 14, '') . '] ' : '';
            if ($o->details && count($o->details) > 0) {
                foreach ($o->details as $d) {
                    $price = (int) round($d->harga);
                    $qty = (int) $d->qty;
                    $items[] = [
                        'id'       => (string) ($d->product_id ?? $d->id),
                        'price'    => $price,
                        'quantity' => $qty,
                        'name'     => mb_strimwidth($unitPrefix . ($d->nama_produk ?? 'Produk Pertanian'), 0, 50, '...'),
                    ];
                    $totalProductAmount += ($price * $qty);
                }
            }
            $totalOngkir += (int) round($o->ongkir ?? ($o->shipping->ongkir ?? 0));
            $totalDiskon += (int) round($o->diskon ?? 0);
        }

        // Add consolidated shipping fee item if any
        if ($totalOngkir > 0) {
            $items[] = [
                'id'       => 'SHIPPING-FEE',
                'price'    => $totalOngkir,
                'quantity' => 1,
                'name'     => $isConsolidated ? "Biaya Ongkir ({$orders->count()} Unit TEFA)" : 'Biaya Ongkir Kurir',
            ];
        }

        // Pastikan diskon tidak membuat total item negatif dan selalu selaras dengan sum(grand_total)
        $positiveTotal = $totalProductAmount + $totalOngkir;
        $cappedDiskon = min($totalDiskon, $positiveTotal);
        if ($cappedDiskon > 0) {
            $items[] = [
                'id'       => 'DISCOUNT-VOUCHER',
                'price'    => -$cappedDiskon,
                'quantity' => 1,
                'name'     => 'Potongan Diskon Voucher',
            ];
        }

        $grossAmount = max(1, $positiveTotal - $cappedDiskon);

        $customerName = $order->shipping->penerima_nama ?? $order->user->nama ?? 'Pelanggan';
        $customerEmail = $order->user->email ?? 'customer@polinela.ac.id';
        $customerPhone = $order->shipping->penerima_telepon ?? $order->user->no_hp ?? '081234567890';
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
            ])->timeout(5)->post(self::getSnapApiUrl(), $payload);

            if ($response->successful()) {
                $data = $response->json();
                $snapToken = $data['token'] ?? null;

                // Simpan snap_token & snap_order_id di seluruh sibling order tanpa menimpa payments.no_transaksi (TRX-...)
                try {
                    Order::whereIn('id', $orders->pluck('id'))->update([
                        'snap_token'    => $snapToken,
                        'snap_order_id' => $midtransOrderId,
                    ]);
                } catch (\Exception $saveEx) {
                    Log::warning("Failed to persist snap metadata: " . $saveEx->getMessage());
                }

                Log::info("Midtrans Snap Token generated ({$orders->count()} order(s), total Rp {$grossAmount}, midtrans_id={$midtransOrderId})");
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
