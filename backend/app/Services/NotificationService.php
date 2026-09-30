<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class NotificationService
{
    /**
     * Kirim Notifikasi WhatsApp Otomatis Headless via Fonnte Gateway API
     */
    public static function sendWhatsAppViaFonnte(?string $targetPhone, string $message): bool
    {
        $token = env('FONNTE_TOKEN', config('services.fonnte.token'));
        if (empty($token) || empty($targetPhone)) {
            return false;
        }

        $formattedPhone = self::formatPhoneNumber($targetPhone);

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(5)->post('https://api.fonnte.com/send', [
                'target'  => $formattedPhone,
                'message' => $message,
                'countryCode' => '62',
            ]);

            if ($response->successful()) {
                Log::info("Fonnte WhatsApp successfully sent to {$formattedPhone}");
                return true;
            } else {
                Log::warning("Fonnte WhatsApp API error: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Fonnte WhatsApp Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format nomor WhatsApp ke format internasional (628...)
     */
    public static function formatPhoneNumber(?string $phone): string
    {
        if (empty($phone)) return '';
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }
        return $clean;
    }

    /**
     * Generate Teks Pesan WhatsApp untuk Konsumen atau Admin Toko
     */
    public static function generateWhatsAppMessage(Order $order, string $type = 'customer', ?string $customNote = null): array
    {
        $order->load(['unit', 'user', 'shipping', 'payment', 'details']);

        $orderNumber = $order->order_number ?: "#{$order->id}";
        $unitName    = $order->unit->nama_unit ?? 'Polinela Agro Digital';
        $customerName = $order->shipping->nama_penerima ?? $order->user->nama ?? 'Pelanggan';
        $customerPhone = $order->shipping->no_hp ?? $order->user->no_hp ?? '';
        $unitPhone   = $order->unit->kontak_wa ?? $order->unit->kontak ?? env('STORE_WHATSAPP', '0881080592737');
        $grandTotal  = 'Rp ' . number_format($order->grand_total, 0, ',', '.');
        $resi        = $order->shipping->no_resi ?? '-';
        $kurir       = strtoupper($order->shipping->ekspedisi ?? 'Kurir Polinela');
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        $orderUrl    = "{$frontendUrl}/pesanan/{$order->id}";

        $itemList = "";
        if ($order->details) {
            foreach ($order->details as $d) {
                $itemList .= "  • {$d->nama_produk} (x{$d->qty}) - Rp " . number_format($d->subtotal, 0, ',', '.') . "\n";
            }
        }

        if ($type === 'admin') {
            $phone = self::formatPhoneNumber($unitPhone);
            $msg = "🌿 *NOTIFIKASI PESANAN MASUK - POLINELA AGRO*\n\n"
                 . "Halo Admin *{$unitName}*,\n"
                 . "Ada pesanan baru yang masuk ke sistem:\n\n"
                 . "📋 *No. Pesanan:* `{$orderNumber}`\n"
                 . "👤 *Pemesan:* {$customerName} ({$customerPhone})\n"
                 . "💰 *Total:* *{$grandTotal}*\n"
                 . "📦 *Rincian Produk:*\n{$itemList}\n"
                 . "🚚 *Kurir:* {$kurir}\n"
                 . "📍 *Alamat:* " . ($order->shipping->alamat_lengkap ?? '-') . "\n\n"
                 . "🔗 *Kelola Pesanan di Panel Admin:* {$frontendUrl}/admin/orders\n\n"
                 . "_Sistem Notifikasi Otomatis Polinela Agro Digital_";
        } else {
            $phone = self::formatPhoneNumber($customerPhone);
            
            if ($order->status === 'dikirim') {
                $msg = "🚚 *PESANAN SEDANG DIKIRIM! - POLINELA AGRO*\n\n"
                     . "Halo Kak *{$customerName}*,\n"
                     . "Kabar baik! Paket produk perkebunan Anda dari *{$unitName}* telah diserahkan ke pihak ekspedisi.\n\n"
                     . "📋 *No. Pesanan:* `{$orderNumber}`\n"
                     . "📦 *Ekspedisi / Kurir:* {$kurir}\n"
                     . "🔖 *Nomor Resi:* *{$resi}*\n"
                     . ($customNote ? "📝 *Catatan Kurir/Admin:* {$customNote}\n" : "")
                     . "\n📦 *Rincian Produk:*\n{$itemList}\n"
                     . "Pantau status pengiriman & invoice resmi Anda di:\n{$orderUrl}\n\n"
                     . "Terima kasih telah berbelanja di *Polinela Agro Digital*! 🌱";
            } elseif ($order->status === 'selesai') {
                $msg = "✅ *PESANAN SELESAI DITERIMA - POLINELA AGRO*\n\n"
                     . "Halo Kak *{$customerName}*,\n"
                     . "Terima kasih banyak telah berbelanja produk Teaching Factory di *{$unitName}* Polinela Agro Digital.\n\n"
                     . "Pesanan `{$orderNumber}` telah selesai diterima. Jangan lupa untuk memberikan rating & ulasan produk Anda di aplikasi ya:\n{$orderUrl}\n\n"
                     . "_Salam hangat dari civitas Politeknik Negeri Lampung!_ 🌾";
            } elseif ($order->payment && $order->payment->status === 'lunas' || in_array($order->status, ['diproses', 'dikirim', 'selesai'])) {
                $address = $order->shipping ? ($order->shipping->alamat_lengkap . ', ' . ($order->shipping->kota ?? '') . ' ' . ($order->shipping->kode_pos ?? '')) : ($order->user->alamat ?? '-');
                $recipient = $order->shipping->nama_penerima ?? $order->user->nama ?? 'Pelanggan';
                $recipientHp = $order->shipping->no_hp ?? $order->user->no_hp ?? '-';

                $msg = "✨ *PEMBAYARAN DIVERIFIKASI (LUNAS) - POLINELA AGRO*\n\n"
                     . "Halo Kak *{$customerName}*,\n"
                     . "Pembayaran untuk pesanan `{$orderNumber}` sebesar *{$grandTotal}* telah BERHASIL diverifikasi *LUNAS* secara otomatis oleh sistem.\n\n"
                     . "📋 *Rincian Pesanan:*\n"
                     . "🏪 *Unit Toko:* {$unitName}\n"
                     . "👤 *Penerima:* {$recipient} ({$recipientHp})\n"
                     . "📍 *Alamat Pengiriman:* {$address}\n"
                     . "🚚 *Ekspedisi / Kurir:* {$kurir}\n\n"
                     . "📦 *Produk yang Dipesan:*\n{$itemList}\n"
                     . "Saat ini pesanan Anda sedang dalam proses penyiapan dan pengemasan (packing) higienis oleh tim unit toko dan akan segera diserahkan ke kurir untuk diantar ke alamat Anda.\n\n"
                     . "Pantau progres pesanan, resi, & cetak invoice resmi Anda di:\n{$orderUrl}\n\n"
                     . "Terima kasih telah berbelanja di *Polinela Agro Digital*! 🌱\n"
                     . "_Teaching Factory Politeknik Negeri Lampung_";
            } else {
                $msg = "🌿 *TAGIHAN PESANAN BARU - POLINELA AGRO*\n\n"
                     . "Halo Kak *{$customerName}*,\n"
                     . "Terima kasih telah memesan produk Teaching Factory di *Polinela Agro Digital*.\n\n"
                     . "📋 *No. Pesanan:* `{$orderNumber}`\n"
                     . "🏪 *Unit Toko:* {$unitName}\n"
                     . "💰 *Total Tagihan:* *{$grandTotal}*\n\n"
                     . "📦 *Rincian Produk:*\n{$itemList}\n"
                     . "💳 *Metode Pembayaran:* Otomatis via Midtrans (Virtual Account BCA/Mandiri/BRI/BNI, QRIS, GoPay, ShopeePay)\n\n"
                     . "Selesaikan pembayaran instan Anda secara online melalui tautan resmi berikut:\n{$orderUrl}\n\n"
                     . "_Pembayaran akan langsung diverifikasi otomatis dalam hitungan detik._\n"
                     . "Terima kasih! 🌱";
            }
        }

        return [
            'phone'   => $phone,
            'message' => $msg,
        ];
    }

    /**
     * Generate Link WhatsApp Pesan untuk Konsumen atau Admin Toko
     */
    public static function generateWhatsAppUrl(Order $order, string $type = 'customer', ?string $customNote = null): string
    {
        $data = self::generateWhatsAppMessage($order, $type, $customNote);
        return "https://wa.me/{$data['phone']}?text=" . rawurlencode($data['message']);
    }

    /**
     * Kirim Notifikasi Email Otomatis (Modern Responsive HTML Template)
     */
    public static function sendOrderEmail(Order $order, string $eventType = 'order_created', ?string $customNote = null): bool
    {
        $order->load(['unit', 'user', 'shipping', 'payment', 'details']);

        $customerEmail = $order->user->email ?? null;
        if (!$customerEmail) return false;

        $orderNumber  = $order->order_number ?: "#{$order->id}";
        $customerName = $order->shipping->nama_penerima ?? $order->user->nama ?? 'Pelanggan';
        $unitName     = $order->unit->nama_unit ?? 'Teaching Factory Polinela';
        $grandTotal   = 'Rp ' . number_format($order->grand_total, 0, ',', '.');
        $frontendUrl  = config('app.frontend_url', 'http://localhost:5173');
        $orderUrl     = "{$frontendUrl}/pesanan/{$order->id}";

        $subjects = [
            'order_created'    => "Tagihan Pesanan #{$orderNumber} - Polinela Agro Digital",
            'payment_verified' => "Pembayaran Berhasil (Lunas) #{$orderNumber} - Polinela Agro Digital",
            'order_shipped'    => "Pesanan #{$orderNumber} Sedang Dikirim! [Resi: " . ($order->shipping->no_resi ?? '-') . "]",
            'order_completed'  => "Pesanan #{$orderNumber} Selesai - Terima Kasih!",
        ];
        $subject = $subjects[$eventType] ?? "Status Pesanan #{$orderNumber} - Polinela Agro Digital";

        $statusTitle = match ($eventType) {
            'order_created'    => "Tagihan Pesanan Baru",
            'payment_verified' => "Pembayaran Telah Diverifikasi (Lunas)",
            'order_shipped'    => "Pesanan Sedang Dalam Pengiriman",
            'order_completed'  => "Pesanan Selesai Diterima",
            default            => "Update Pesanan",
        };

        $statusDesc = match ($eventType) {
            'order_created'    => "Pesanan Anda di unit <strong>{$unitName}</strong> berhasil dibuat. Silakan lakukan pembayaran instan via Midtrans (VA / QRIS / E-Wallet).",
            'payment_verified' => "Pembayaran Anda sebesar <strong>{$grandTotal}</strong> telah berhasil diverifikasi oleh sistem. Pesanan sedang dipersiapkan.",
            'order_shipped'    => "Paket pesanan Anda telah diserahkan ke kurir ekspedisi dengan nomor resi <strong>" . ($order->shipping->no_resi ?? '-') . "</strong>.",
            'order_completed'  => "Terima kasih telah berbelanja di Teaching Factory Polinela Agro Digital. Selamat menikmati produk Anda!",
            default            => "Status pesanan Anda telah diperbarui.",
        };

        $itemRows = "";
        if ($order->details) {
            foreach ($order->details as $d) {
                $itemRows .= "
                <tr>
                    <td style='padding: 8px 0; border-bottom: 1px solid #f1f5f9;'>
                        <strong>{$d->nama_produk}</strong><br/>
                        <span style='color: #64748b; font-size: 12px;'>{$d->qty} item &times; Rp " . number_format($d->harga, 0, ',', '.') . "</span>
                    </td>
                    <td style='padding: 8px 0; border-bottom: 1px solid #f1f5f9; text-align: right; font-weight: bold;'>
                        Rp " . number_format($d->subtotal, 0, ',', '.') . "
                    </td>
                </tr>";
            }
        }

        $shippingAddress = $order->shipping ? ($order->shipping->alamat_lengkap . ', ' . ($order->shipping->kota ?? '') . ' ' . ($order->shipping->kode_pos ?? '')) : ($order->user->alamat ?? '-');
        $courierName = strtoupper($order->shipping->ekspedisi ?? $order->shipping->kurir ?? 'Kurir Polinela');
        $recipientName = $order->shipping->nama_penerima ?? $order->user->nama ?? 'Pelanggan';
        $recipientPhone = $order->shipping->no_hp ?? $order->user->no_hp ?? '-';

        // HTML Email Content
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; margin: 0; padding: 20px; color: #1e293b; }
                .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.06); }
                .email-header { background: linear-gradient(135deg, #15803d 0%, #166534 100%); color: #ffffff; padding: 25px 30px; text-align: center; }
                .email-body { padding: 30px; }
                .order-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin: 20px 0; }
                .btn-cta { display: inline-block; background: #15803d; color: #ffffff !important; padding: 12px 28px; border-radius: 30px; text-decoration: none; font-weight: bold; margin-top: 15px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #64748b; background: #f1f5f9; }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h2 style='margin:0; font-size: 22px; letter-spacing: 0.5px;'>POLINELA AGRO DIGITAL</h2>
                    <p style='margin: 5px 0 0 0; opacity: 0.9; font-size: 13px;'>Teaching Factory Politeknik Negeri Lampung</p>
                </div>
                <div class='email-body'>
                    <h3 style='color: #15803d; margin-top: 0;'>{$statusTitle}</h3>
                    <p>Halo <strong>{$customerName}</strong>,</p>
                    <p>{$statusDesc}</p>
                    
                    <div class='order-box'>
                        <table style='width: 100%; font-size: 14px; border-collapse: collapse; margin-bottom: 12px;'>
                            <tr>
                                <td style='padding: 4px 0; color: #64748b;'>No. Pesanan:</td>
                                <td style='padding: 4px 0; text-align: right; font-weight: bold;'>{$orderNumber}</td>
                            </tr>
                            <tr>
                                <td style='padding: 4px 0; color: #64748b;'>Unit Toko:</td>
                                <td style='padding: 4px 0; text-align: right; font-weight: bold;'>{$unitName}</td>
                            </tr>
                            <tr>
                                <td style='padding: 4px 0; color: #64748b;'>Penerima:</td>
                                <td style='padding: 4px 0; text-align: right; font-weight: bold;'>{$recipientName} ({$recipientPhone})</td>
                            </tr>
                            <tr>
                                <td style='padding: 4px 0; color: #64748b;'>Ekspedisi / Kurir:</td>
                                <td style='padding: 4px 0; text-align: right; font-weight: bold;'>{$courierName}</td>
                            </tr>
                            <tr>
                                <td style='padding: 4px 0; color: #64748b; vertical-align: top;'>Alamat Pengiriman:</td>
                                <td style='padding: 4px 0; text-align: right; font-size: 13px;'>{$shippingAddress}</td>
                            </tr>
                            <tr>
                                <td style='padding: 6px 0; color: #64748b; border-top: 1px solid #e2e8f0;'>Total Pembayaran:</td>
                                <td style='padding: 6px 0; text-align: right; font-weight: bold; font-size: 16px; color: #15803d; border-top: 1px solid #e2e8f0;'>{$grandTotal}</td>
                            </tr>
                        </table>

                        <div style='border-top: 1px dashed #cbd5e1; padding-top: 10px; margin-top: 10px;'>
                            <strong style='font-size: 13px; color: #475569;'>Rincian Produk:</strong>
                            <table style='width: 100%; font-size: 13px; border-collapse: collapse; margin-top: 8px;'>
                                {$itemRows}
                            </table>
                        </div>
                    </div>

                    " . ($customNote ? "<p style='background: #fef9c3; border-left: 4px solid #eab308; padding: 10px; font-size: 13px;'><strong>Catatan:</strong> {$customNote}</p>" : "") . "

                    <div style='text-align: center; margin: 25px 0;'>
                        <a href='{$orderUrl}' class='btn-cta'>Buka Detail & Status Pesanan</a>
                    </div>
                </div>
                <div class='footer'>
                    Politeknik Negeri Lampung • Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung<br/>
                    Email ini dikirim otomatis oleh sistem terpadu Polinela Agro Digital.
                </div>
            </div>
        </body>
        </html>
        ";

        try {
            Mail::html($html, function ($message) use ($customerEmail, $subject) {
                $message->to($customerEmail)
                        ->subject($subject);
            });

            Log::info("Notification Email sent to {$customerEmail} for Order {$orderNumber} ({$eventType})");
            return true;
        } catch (\Exception $e) {
            Log::warning("Failed sending email notification to {$customerEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Trigger Notifikasi Lengkap Saat Pesanan Baru Dibuat
     */
    /**
     * Trigger Notifikasi Saat Pesanan Baru Dibuat
     */
    public static function notifyNewOrder(Order $order, bool $notifyCustomer = true): void
    {
        $order->loadMissing(['unit', 'user', 'shipping', 'payment', 'details']);

        // 1. In-App Notification Konsumen
        Notification::create([
            'user_id' => $order->user_id,
            'judul'   => 'Pesanan Berhasil Dibuat 🌱',
            'pesan'   => "Pesanan #{$order->order_number} untuk {$order->unit->nama_unit} telah dibuat. Silakan lakukan pembayaran.",
            'link'    => "/pesanan/{$order->id}",
            'is_read' => false,
        ]);

        // 2. In-App Notification Admin Unit & Superadmin
        Notification::sendToAdmins(
            "Pesanan Baru Masuk #{$order->order_number} 📦",
            "Pelanggan memesan produk senilai Rp " . number_format($order->grand_total, 0, ',', '.') . " di unit {$order->unit->nama_unit}.",
            "/admin/orders",
            $order->unit_id
        );

        // Notifikasi WA ke Admin Toko
        $adminMsgData = self::generateWhatsAppMessage($order, 'admin');
        self::sendWhatsAppViaFonnte($adminMsgData['phone'], $adminMsgData['message']);

        // 3. Email & WA ke Konsumen (Hanya jika flag true)
        if ($notifyCustomer) {
            self::sendOrderEmail($order, 'order_created');
            $customerMsgData = self::generateWhatsAppMessage($order, 'customer');
            self::sendWhatsAppViaFonnte($customerMsgData['phone'], $customerMsgData['message']);
        }

        // 4. Activity Log
        ActivityLog::log('Buat Pesanan', 'Transaksi', "Membuat pesanan baru #{$order->order_number} senilai Rp " . number_format($order->grand_total, 0, ',', '.'));
    }

    /**
     * Notifikasi Checkout Terpadu (1 Email & 1 WhatsApp untuk Konsumen pada Transaksi Bersama)
     */
    public static function notifyConsolidatedCheckout(array $createdOrderModels, string $sharedTrxNumber): void
    {
        if (empty($createdOrderModels)) return;

        // Jika hanya 1 order unit toko
        if (count($createdOrderModels) === 1) {
            self::notifyNewOrder($createdOrderModels[0], true);
            return;
        }

        // Jika Multi-Store Order (lebih dari 1 unit toko):
        // 1. Beritahu masing-masing admin toko
        foreach ($createdOrderModels as $order) {
            self::notifyNewOrder($order, false); // false = jangan kirim notif konsumen berulang
        }

        // 2. Kirim 1 Notifikasi Konsumen Gabungan (Email & WA)
        $firstOrder = $createdOrderModels[0];
        $firstOrder->loadMissing(['user', 'shipping', 'payment']);

        $customerName = $firstOrder->shipping->nama_penerima ?? $firstOrder->user->nama ?? 'Pelanggan';
        $customerPhone = $firstOrder->shipping->no_hp ?? $firstOrder->user->no_hp ?? '';
        $customerEmail = $firstOrder->user->email ?? null;

        $totalCombined = 0;
        $allItemLines = "";
        $unitNames = [];

        foreach ($createdOrderModels as $ord) {
            $ord->loadMissing(['unit', 'details']);
            $unitNames[] = $ord->unit->nama_unit ?? 'Unit Toko';
            $totalCombined += $ord->grand_total;
            $allItemLines .= "🏪 *Unit: " . ($ord->unit->nama_unit ?? 'Toko') . "* (#{$ord->order_number})\n";
            if ($ord->details) {
                foreach ($ord->details as $d) {
                    $allItemLines .= "  • {$d->nama_produk} (x{$d->qty}) - Rp " . number_format($d->subtotal, 0, ',', '.') . "\n";
                }
            }
            $allItemLines .= "  _Ongkir & Diskon Unit: Rp " . number_format($ord->ongkir - $ord->diskon, 0, ',', '.') . "_\n\n";
        }

        $formattedTotal = 'Rp ' . number_format($totalCombined, 0, ',', '.');
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        $orderUrl = "{$frontendUrl}/pesanan/{$firstOrder->id}";

        // Teks WhatsApp Gabungan
        $msg = "🌿 *TAGIHAN PESANAN MULTI-STORE - POLINELA AGRO*\n\n"
             . "Halo Kak *{$customerName}*,\n"
             . "Pesanan belanja Anda dari *" . implode(', ', array_unique($unitNames)) . "* berhasil dibuat dalam 1 transaksi terpadu.\n\n"
             . "📋 *Kode Transaksi Bersama:* `{$sharedTrxNumber}`\n"
             . "💰 *Total Seluruh Tagihan (1x Bayar):* *{$formattedTotal}*\n\n"
             . "📦 *Rincian Belanja Seluruh Toko:*\n{$allItemLines}"
             . "💳 *Metode Pembayaran:* Otomatis via Midtrans (Virtual Account BCA/Mandiri/BRI/BNI, QRIS, GoPay, ShopeePay)\n\n"
             . "Selesaikan pembayaran online instan Anda melalui tautan resmi berikut:\n{$orderUrl}\n\n"
             . "_Cukup lakukan 1 kali pembayaran untuk semua toko di atas dan pesanan otomatis diverifikasi lunas._\nTerima kasih! 🌱";

        // Kirim 1x WhatsApp
        self::sendWhatsAppViaFonnte($customerPhone, $msg);

        // Kirim 1x Email Gabungan
        if ($customerEmail) {
            $emailSubject = "Tagihan Pesanan #{$sharedTrxNumber} - Polinela Agro Digital";
            $html = "
            <!DOCTYPE html>
            <html>
            <head><meta charset='utf-8'><style>
                body { font-family: 'Segoe UI', sans-serif; background-color: #f8fafc; margin: 0; padding: 20px; color: #1e293b; }
                .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.06); }
                .email-header { background: linear-gradient(135deg, #15803d 0%, #166534 100%); color: #ffffff; padding: 25px 30px; text-align: center; }
                .email-body { padding: 30px; }
                .order-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin: 20px 0; }
                .btn-cta { display: inline-block; background: #15803d; color: #ffffff !important; padding: 12px 28px; border-radius: 30px; text-decoration: none; font-weight: bold; margin-top: 15px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #64748b; background: #f1f5f9; }
            </style></head>
            <body>
                <div class='email-container'>
                    <div class='email-header'>
                        <h2 style='margin:0; font-size: 22px;'>POLINELA AGRO DIGITAL</h2>
                        <p style='margin:5px 0 0; opacity:0.9; font-size: 13px;'>Teaching Factory Politeknik Negeri Lampung</p>
                    </div>
                    <div class='email-body'>
                        <h3 style='color:#15803d; margin-top:0;'>Tagihan Pesanan Multi-Store</h3>
                        <p>Halo <strong>{$customerName}</strong>, pesanan belanja Anda dari berbagai unit toko perkebunan telah dibuat dalam 1 transaksi terpadu.</p>
                        <div class='order-box'>
                            <p style='margin:0 0 8px;'><strong>Kode Transaksi:</strong> {$sharedTrxNumber}</p>
                            <p style='margin:0 0 8px;'><strong>Total Tagihan (1x Bayar):</strong> <span style='color:#15803d; font-size:18px; font-weight:bold;'>{$formattedTotal}</span></p>
                            <p style='margin:0;'><strong>Unit Toko:</strong> " . implode(', ', array_unique($unitNames)) . "</p>
                        </div>
                        <p>Silakan selesaikan pembayaran instan secara otomatis melalui <strong>Midtrans Payment Gateway (VA / QRIS / GoPay / ShopeePay)</strong>.</p>
                        <div style='text-align: center; margin: 25px 0;'>
                            <a href='{$orderUrl}' class='btn-cta'>Lihat Tagihan & Bayar Online</a>
                        </div>
                    </div>
                    <div class='footer'>
                        Politeknik Negeri Lampung • Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung<br/>
                        &copy; " . date('Y') . " Polinela Agro Digital. Hak Cipta Dilindungi.
                    </div>
                </div>
            </body>
            </html>
            ";

            try {
                Mail::html($html, function ($message) use ($customerEmail, $emailSubject) {
                    $message->to($customerEmail)
                            ->subject($emailSubject);
                });
            } catch (\Exception $e) {
                Log::warning("Consolidated Email Error: " . $e->getMessage());
            }
        }
    }

    /**
     * Trigger Notifikasi Saat Status Pesanan / Resi Berubah
     */
    public static function notifyOrderStatusChanged(Order $order, string $oldStatus, ?string $customNote = null): void
    {
        $statusLabels = [
            'diproses'   => 'Sedang Diproses & Dipacking',
            'dikirim'    => 'Sedang Dikirimkan ke Alamat Anda',
            'selesai'    => 'Telah Selesai Diterima',
            'dibatalkan' => 'Telah Dibatalkan',
        ];
        $label = $statusLabels[$order->status] ?? $order->status;

        // 1. In-App Notification (Tercatat di lonceng notifikasi akun web pembeli)
        Notification::create([
            'user_id' => $order->user_id,
            'judul'   => "Status Pesanan: {$label} 📦",
            'pesan'   => "Pesanan #{$order->order_number} ({$order->unit->nama_unit}) kini {$label}." . ($order->shipping?->no_resi ? " Nomor Resi: {$order->shipping->no_resi}" : ""),
            'link'    => "/pesanan/{$order->id}",
            'is_read' => false,
        ]);

        // Jika status adalah 'dikirim', tidak perlu kirim WhatsApp & Email eksternal
        // karena rincian pengiriman lengkap sudah dicakup pada notifikasi Pembayaran LUNAS.
        if ($order->status === 'dikirim') {
            return;
        }

        // 2. Email Notification (Untuk Pembayaran Lunas & Pesanan Selesai)
        $eventType = match ($order->status) {
            'selesai' => 'order_completed',
            default   => 'payment_verified',
        };
        self::sendOrderEmail($order, $eventType, $customNote);

        // 3. WhatsApp Otomatis via Fonnte
        $customerMsgData = self::generateWhatsAppMessage($order, 'customer', $customNote);
        self::sendWhatsAppViaFonnte($customerMsgData['phone'], $customerMsgData['message']);
    }
}
