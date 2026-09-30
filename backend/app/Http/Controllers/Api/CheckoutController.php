<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Shipping;
use App\Models\Stock;
use App\Models\Voucher;
use App\Models\Setting;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function validateVoucher(Request $request)
    {
        $kode = strtoupper(trim($request->get('kode') ?? $request->get('kode_voucher') ?? ''));
        $subtotal = (float) ($request->get('subtotal') ?? $request->get('total') ?? 0);

        if (empty($kode)) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Kode voucher harus diisi.'], 422);
        }

        $voucher = Voucher::where('kode', $kode)
            ->where('is_active', true)
            ->first();

        if (!$voucher) {
            return response()->json(['status' => 'error', 'success' => false, 'valid' => false, 'message' => 'Kode voucher tidak valid atau tidak ditemukan.'], 404);
        }

        $now = date('Y-m-d');
        if ($voucher->tgl_mulai && $voucher->tgl_mulai > $now) {
            return response()->json(['status' => 'error', 'success' => false, 'valid' => false, 'message' => 'Voucher belum dapat digunakan.'], 400);
        }
        if ($voucher->tgl_berakhir && $voucher->tgl_berakhir < $now) {
            return response()->json(['status' => 'error', 'success' => false, 'valid' => false, 'message' => 'Voucher telah kedaluwarsa.'], 400);
        }
        if ($voucher->kuota && $voucher->terpakai >= $voucher->kuota) {
            return response()->json(['status' => 'error', 'success' => false, 'valid' => false, 'message' => 'Kuota pemakaian voucher telah habis.'], 400);
        }
        if ($subtotal < $voucher->min_belanja) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'valid'   => false,
                'message' => 'Minimal belanja untuk voucher ini adalah Rp ' . number_format($voucher->min_belanja, 0, ',', '.'),
            ], 400);
        }

        $diskon = ($voucher->tipe === 'persen')
            ? ($subtotal * ($voucher->diskon / 100))
            : $voucher->diskon;

        if ($voucher->max_diskon && $diskon > $voucher->max_diskon) {
            $diskon = $voucher->max_diskon;
        }

        $diskon = min($diskon, $subtotal);

        return response()->json([
            'status'       => 'success',
            'success'      => true,
            'valid'        => true,
            'message'      => "Voucher '{$voucher->nama}' berhasil digunakan!",
            'data'         => [
                'voucher'      => $voucher,
                'diskon'       => (float) $diskon,
                'nilai_diskon' => (float) $diskon,
                'kode'         => $voucher->kode,
            ],
            'nilai_diskon' => (float) $diskon,
            'diskon'       => (float) $diskon,
            'kode'         => $voucher->kode,
        ]);
    }

    public function process(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;

        $penerimaNama = $request->get('nama_penerima') ?? $request->get('penerima_nama') ?? $user->nama;
        $penerimaHp = $request->get('no_hp') ?? $request->get('penerima_telepon') ?? $user->no_hp;
        $alamatLengkap = $request->get('alamat_lengkap') ?? $request->get('alamat') ?? $user->alamat;
        $kota = $request->get('kota') ?? 'Bandar Lampung';
        $kodePos = $request->get('kode_pos') ?? '35144';
        
        $rawMetode = $request->get('metode_pembayaran') ?? $request->get('metode_bayar') ?? 'transfer';
        $metodeMap = [
            'midtrans'      => 'midtrans',
            'transfer_bank' => 'transfer',
            'transfer'      => 'transfer',
            'qris'          => 'qris',
            'cod'           => 'cod',
            'va'            => 'va',
            'ewallet'       => 'ewallet',
            'cc'            => 'cc',
        ];
        $metodeBayar = $metodeMap[strtolower($rawMetode)] ?? 'midtrans';
        
        $bank = ($metodeBayar === 'midtrans') ? 'Midtrans (VA / QRIS / E-Wallet)' : ($request->get('bank') ?? 'Bank Mandiri (114-00-1234567-8)');
        $kurirOption = $request->get('ekspedisi') ?? $request->get('shipping_option') ?? 'kurir_polinela';
        $voucherCode = trim($request->get('kode_voucher') ?? $request->get('voucher_kode') ?? '');
        $catatan = $request->get('catatan_pembeli') ?? $request->get('catatan') ?? null;

        if (empty($penerimaNama) || empty($penerimaHp) || empty($alamatLengkap)) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Nama penerima, nomor telepon, dan alamat lengkap wajib diisi.',
            ], 422);
        }

        $cartItems = Cart::with(['product.unit'])
            ->where('user_id', $userId)
            ->whereHas('product', fn($q) => $q->whereNull('deleted_at'))
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Keranjang belanja Anda masih kosong.',
            ], 400);
        }

        // Kelompokkan produk berdasarkan unit toko
        $unitGroups = [];
        $overallSubtotal = 0;
        foreach ($cartItems as $item) {
            $p = $item->product;
            if (!$p) continue;

            $uId = $p->unit_id ?: 1;
            $unitGroups[$uId][] = $item;
            $overallSubtotal += ($p->harga * $item->qty);
        }

        $numUnits = count($unitGroups);

        // Tarif Ongkir per unit
        $ongkirPerUnit = ($kurirOption === 'ambil_sendiri') ? 0 : 10000;
        $totalOngkir = $ongkirPerUnit * $numUnits;

        // Diskon Voucher
        $totalDiskon = 0;
        if (!empty($voucherCode)) {
            $voucher = Voucher::where('kode', strtoupper($voucherCode))->where('is_active', true)->first();
            if ($voucher && $overallSubtotal >= $voucher->min_belanja) {
                $totalDiskon = ($voucher->tipe === 'persen')
                    ? ($overallSubtotal * ($voucher->diskon / 100))
                    : $voucher->diskon;
                if ($voucher->max_diskon && $totalDiskon > $voucher->max_diskon) {
                    $totalDiskon = $voucher->max_diskon;
                }
                $totalDiskon = min($totalDiskon, $overallSubtotal);
                $voucher->increment('terpakai');
            }
        }

        $sharedTrxNumber = 'TRX-' . strtoupper(Str::random(8));
        $createdOrders = [];
        $createdOrderModels = [];

        DB::beginTransaction();
        try {
            foreach ($unitGroups as $uId => $groupItems) {
                $unitSubtotal = 0;
                $unitBerat = 0;
                foreach ($groupItems as $it) {
                    $unitSubtotal += ($it->product->harga * $it->qty);
                    $unitBerat += (($it->product->berat_gram ?: 500) * $it->qty);
                }

                $ratio = $overallSubtotal > 0 ? ($unitSubtotal / $overallSubtotal) : (1 / $numUnits);
                $unitDiskon = round($totalDiskon * $ratio);
                $unitOngkir = $ongkirPerUnit;
                $unitGrandTotal = max(0, ($unitSubtotal + $unitOngkir - $unitDiskon));

                $orderNumber = 'PNA-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                // 1. Simpan Order
                $order = Order::create([
                    'order_number' => $orderNumber,
                    'user_id'      => $userId,
                    'unit_id'      => $uId,
                    'total_produk' => $unitSubtotal,
                    'ongkir'       => $unitOngkir,
                    'diskon'       => $unitDiskon,
                    'kode_voucher' => $voucherCode ?: null,
                    'grand_total'  => $unitGrandTotal,
                    'status'       => 'menunggu_pembayaran',
                    'catatan'      => $catatan,
                ]);

                // 2. Simpan Order Details & Potong Stok
                foreach ($groupItems as $item) {
                    $prod = $item->product;

                    OrderDetail::create([
                        'order_id'    => $order->id,
                        'product_id'  => $prod->id,
                        'nama_produk' => $prod->nama_produk,
                        'harga'       => $prod->harga,
                        'qty'         => $item->qty,
                        'subtotal'    => $prod->harga * $item->qty,
                        'berat'       => ($prod->berat_gram ?: 500) * $item->qty,
                    ]);

                    $prod->decrement('stok', $item->qty);
                    $prod->increment('total_terjual', $item->qty);

                    Stock::create([
                        'product_id' => $prod->id,
                        'tipe'       => 'keluar',
                        'qty'        => $item->qty,
                        'sisa_stok'  => max(0, $prod->fresh()->stok),
                        'keterangan' => "Pesanan #{$orderNumber}",
                        'user_id'    => $userId,
                    ]);
                }

                // 3. Simpan Payment (Linked with shared transaction ID)
                Payment::create([
                    'order_id'           => $order->id,
                    'metode'             => $metodeBayar,
                    'bank'               => $bank,
                    'no_rekening_tujuan' => '114-00-1234567-8',
                    'atas_nama'          => 'Polinela Agro Perkebunan',
                    'no_transaksi'       => $sharedTrxNumber,
                    'bukti_bayar'        => null,
                    'status'             => 'pending',
                ]);

                // 4. Simpan Shipping
                Shipping::create([
                    'order_id'         => $order->id,
                    'kurir'            => $kurirOption,
                    'layanan'          => 'Reguler Kampus',
                    'no_resi'          => null,
                    'ongkir'           => $unitOngkir,
                    'estimasi'         => '1-2 hari',
                    'penerima_nama'    => $penerimaNama,
                    'penerima_telepon' => $penerimaHp,
                    'alamat_lengkap'   => $alamatLengkap,
                    'kota'             => $kota,
                    'kode_pos'         => $kodePos,
                    'status'           => 'pending',
                ]);

                $createdOrders[] = [
                    'id'           => $order->id,
                    'order_number' => $order->order_number,
                    'no_pesanan'   => $order->order_number,
                    'unit_id'      => $uId,
                    'grand_total'  => $unitGrandTotal,
                    'total_akhir'  => $unitGrandTotal,
                ];

                $createdOrderModels[] = $order;
            }

            // 5. Bersihkan keranjang belanja
            Cart::where('user_id', $userId)->delete();

            DB::commit();

            // Trigger Notifikasi Otomatis di Background setelah response dikirim (Instant < 200ms ke user)
            dispatch(function () use ($createdOrderModels, $sharedTrxNumber) {
                try {
                    \App\Services\NotificationService::notifyConsolidatedCheckout($createdOrderModels, $sharedTrxNumber);
                } catch (\Exception $ne) {
                    \Illuminate\Support\Facades\Log::warning("Notification error: " . $ne->getMessage());
                }
            })->afterResponse();

            ActivityLog::log('Checkout', 'Transaksi', "Checkout transaksi #{$sharedTrxNumber} (" . count($createdOrders) . " unit order).");

            $firstOrderId = !empty($createdOrders) ? $createdOrders[0]['id'] : null;
            $firstOrder = $firstOrderId ? Order::with(['details', 'shipping', 'user', 'unit', 'payment'])->find($firstOrderId) : null;
            $whatsappUrl = $firstOrder ? \App\Services\NotificationService::generateWhatsAppUrl($firstOrder, 'admin') : null;

            // Generate Snap Token Instan jika metode Midtrans (0 ms loading delay di client)
            $snapToken = null;
            if ($firstOrder && $metodeBayar === 'midtrans') {
                try {
                    $snapToken = \App\Services\MidtransService::createSnapToken($firstOrder);
                } catch (\Exception $se) {
                    \Illuminate\Support\Facades\Log::warning("Snap Token Gen Error: " . $se->getMessage());
                }
            }

            return response()->json([
                'status'            => 'success',
                'success'           => true,
                'message'           => 'Pesanan berhasil dibuat!',
                'data'              => [
                    'sharedTrxNumber'  => $sharedTrxNumber,
                    'transaction_code' => $sharedTrxNumber,
                    'orders'           => $createdOrders,
                    'created_orders'   => $createdOrders,
                    'total_amount'     => max(0, $overallSubtotal + $totalOngkir - $totalDiskon),
                    'whatsapp_url'     => $whatsappUrl,
                    'snap_token'       => $snapToken,
                    'client_key'       => \App\Services\MidtransService::getClientKey(),
                    'snap_url'         => \App\Services\MidtransService::getSnapJsUrl(),
                    'first_order_id'   => $firstOrderId,
                ],
                'snap_token'        => $snapToken,
                'client_key'        => \App\Services\MidtransService::getClientKey(),
                'snap_url'          => \App\Services\MidtransService::getSnapJsUrl(),
                'first_order_id'    => $firstOrderId,
                'whatsapp_url'      => $whatsappUrl,
                'transaction_code'  => $sharedTrxNumber,
                'sharedTrxNumber'   => $sharedTrxNumber,
                'orders'            => $createdOrders,
                'created_orders'    => $createdOrders,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
