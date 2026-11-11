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

        [$valid, $diskon, $msg] = $voucher->calculateDiscount($subtotal);
        if (!$valid) {
            return response()->json(['status' => 'error', 'success' => false, 'valid' => false, 'message' => $msg], 400);
        }

        return response()->json([
            'status'       => 'success',
            'success'      => true,
            'valid'        => true,
            'message'      => $msg,
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
        $voucherCode = trim($request->get('kode_voucher') ?? $request->get('voucher_kode') ?? $request->get('kode') ?? '');
        $voucherId = $request->get('voucher_id');
        $catatan = $request->get('catatan_pembeli') ?? $request->get('catatan') ?? null;
        $resolvedShipping = $this->resolveShippingOption($request, (string) $kurirOption);

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

        // Validasi kuantitas seluruh item keranjang (Cegah kuantitas negatif/nol)
        foreach ($cartItems as $item) {
            if ((int) $item->qty < 1) {
                return response()->json([
                    'status'  => 'error',
                    'success' => false,
                    'message' => 'Terdapat kuantitas produk tidak valid di keranjang belanja Anda (minimal 1 unit).',
                ], 422);
            }
        }

        // 1. Mulai Database Transaction untuk menjamin ACID & integritas data
        DB::beginTransaction();
        try {
            // 2. Kunci dan validasi stok semua produk di keranjang (Pessimistic Lock - Anti Race Condition)
            $productIds = $cartItems->pluck('product_id')->unique()->toArray();
            
            // SELECT ... FOR UPDATE untuk mengunci baris produk selama transaksi berlangsung
            $lockedProducts = Product::whereIn('id', $productIds)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Hitung total kebutuhan kuantitas per produk dari seluruh item keranjang
            $productDemands = [];
            foreach ($cartItems as $item) {
                $productDemands[$item->product_id] = ($productDemands[$item->product_id] ?? 0) + $item->qty;
            }

            // Validasi ketersediaan dan stok untuk setiap produk
            foreach ($productDemands as $pId => $neededQty) {
                $prod = $lockedProducts->get($pId);

                if (!$prod || $prod->status !== 'aktif') {
                    DB::rollBack();
                    $productName = $cartItems->firstWhere('product_id', $pId)?->product?->nama_produk ?? 'Produk';
                    return response()->json([
                        'status'  => 'error',
                        'success' => false,
                        'message' => "Maaf, produk '{$productName}' saat ini sedang tidak aktif atau tidak tersedia.",
                    ], 400);
                }

                if ($prod->stok < $neededQty) {
                    DB::rollBack();
                    if ($prod->stok <= 0) {
                        return response()->json([
                            'status'  => 'error',
                            'success' => false,
                            'message' => "Maaf, stok untuk produk '{$prod->nama_produk}' sudah habis (baru saja dibeli pelanggan lain).",
                        ], 400);
                    } else {
                        return response()->json([
                            'status'  => 'error',
                            'success' => false,
                            'message' => "Maaf, stok untuk produk '{$prod->nama_produk}' tidak mencukupi (Tersisa: {$prod->stok}, permintaan: {$neededQty}).",
                        ], 400);
                    }
                }
            }

            // Kelompokkan produk berdasarkan unit toko
            $unitGroups = [];
            $overallSubtotal = 0;
            foreach ($cartItems as $item) {
                $p = $lockedProducts->get($item->product_id);
                if (!$p) continue;

                $uId = $p->unit_id ?: 1;
                $unitGroups[$uId][] = $item;
                $overallSubtotal += ($p->harga * $item->qty);
            }

            $numUnits = count($unitGroups);

            // Tarif Ongkir per unit dari konfigurasi shipping_rates (/admin/ongkir)
            $ongkirPerUnit = $resolvedShipping['tarif'];
            $totalOngkir = $ongkirPerUnit * $numUnits;

            // Diskon Voucher (Atomic & aman dalam transaksi; menerima kode_voucher maupun voucher_id)
            $totalDiskon = 0;
            if (!empty($voucherCode) || !empty($voucherId)) {
                $voucherQuery = Voucher::where('is_active', true);
                if (!empty($voucherCode)) {
                    $voucherQuery->where('kode', strtoupper($voucherCode));
                } else {
                    $voucherQuery->where('id', $voucherId);
                }
                $voucher = $voucherQuery->lockForUpdate()->first();

                if (!$voucher) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => 'error',
                        'success' => false,
                        'message' => 'Voucher tidak ditemukan atau sudah tidak aktif.',
                    ], 400);
                }

                [$valid, $diskon, $msg] = $voucher->calculateDiscount($overallSubtotal);
                if (!$valid) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'success' => false, 'message' => $msg], 400);
                }
                $totalDiskon = $diskon;
                $voucherCode = $voucher->kode;
                $voucher->increment('terpakai');
            }

            $sharedTrxNumber = 'TRX-' . strtoupper(Str::random(8));
            $createdOrders = [];
            $createdOrderModels = [];
            $unitIndex = 0;
            $allocatedDiskon = 0;

            foreach ($unitGroups as $uId => $groupItems) {
                $unitIndex++;
                $unitSubtotal = 0;
                $unitBerat = 0;
                foreach ($groupItems as $it) {
                    $p = $lockedProducts->get($it->product_id);
                    $hargaItem = $p ? $p->harga : $it->product->harga;
                    $beratItem = $p ? ($p->berat_gram ?: 500) : (($it->product->berat_gram ?: 500));
                    $unitSubtotal += ($hargaItem * $it->qty);
                    $unitBerat += ($beratItem * $it->qty);
                }

                // Pastikan jumlah diskon seluruh sub-order tepat sama dengan $totalDiskon
                if ($unitIndex === $numUnits) {
                    $unitDiskon = max(0, $totalDiskon - $allocatedDiskon);
                } else {
                    $ratio = $overallSubtotal > 0 ? ($unitSubtotal / $overallSubtotal) : (1 / $numUnits);
                    $unitDiskon = round($totalDiskon * $ratio);
                    $allocatedDiskon += $unitDiskon;
                }
                $unitOngkir = $ongkirPerUnit;
                $unitGrandTotal = max(0, ($unitSubtotal + $unitOngkir - $unitDiskon));

                $orderNumber = 'PNA-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                // Simpan Order
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

                // Simpan Order Details & Potong Stok secara aman
                foreach ($groupItems as $item) {
                    $prod = $lockedProducts->get($item->product_id);

                    OrderDetail::create([
                        'order_id'    => $order->id,
                        'product_id'  => $prod->id,
                        'nama_produk' => $prod->nama_produk,
                        'harga'       => $prod->harga,
                        'qty'         => $item->qty,
                        'subtotal'    => $prod->harga * $item->qty,
                        'berat'       => ($prod->berat_gram ?: 500) * $item->qty,
                    ]);

                    // Potong stok dan catat total terjual
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

                    // Trigger Peringatan Otomatis jika Stok Menipis (<= stok_min)
                    \App\Services\StockService::checkLowStockAlert($prod->fresh());
                }

                // Simpan Payment
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

                // Simpan Shipping
                Shipping::create([
                    'order_id'         => $order->id,
                    'kurir'            => $kurirOption,
                    'layanan'          => $resolvedShipping['layanan'],
                    'no_resi'          => null,
                    'ongkir'           => $unitOngkir,
                    'estimasi'         => $resolvedShipping['estimasi'],
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

            // Bersihkan keranjang belanja
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

    /**
     * Resolve tarif, nama layanan, dan estimasi ongkir dari konfigurasi settings `shipping_rates`
     * dengan fallback aman untuk kode kurir lama (`ambil_sendiri`, `kurir_polinela`, `jne`).
     */
    private function resolveShippingOption(Request $request, string $kurirOption): array
    {
        $setting = Setting::where('setting_key', 'shipping_rates')->first();
        $rates = $setting ? json_decode($setting->setting_value, true) : null;
        if (!is_array($rates) || empty($rates)) {
            $rates = [
                ['id' => '1', 'wilayah' => 'Ambil di Tefa Polinela (Gratis)', 'tarif' => 0, 'estimasi' => 'Langsung ambil di kampus'],
                ['id' => '2', 'wilayah' => 'Kurir Kampus (Lingkungan Polinela / Rajabasa)', 'tarif' => 5000, 'estimasi' => 'Hari yang sama'],
                ['id' => '3', 'wilayah' => 'Bandar Lampung (Kota)', 'tarif' => 12000, 'estimasi' => '1 hari'],
                ['id' => '4', 'wilayah' => 'Luar Kota / Luar Provinsi (JNE/SiCepat)', 'tarif' => 25000, 'estimasi' => '2-3 hari'],
            ];
        }

        $matched = null;
        $rateIndex = $request->input('shipping_rate_index');
        if ($rateIndex !== null && is_numeric($rateIndex) && isset($rates[(int) $rateIndex])) {
            $matched = $rates[(int) $rateIndex];
        }

        if (!$matched && $request->filled('shipping_rate_id')) {
            $rateId = (string) $request->input('shipping_rate_id');
            foreach ($rates as $idx => $r) {
                if ((string) ($r['id'] ?? ($idx + 1)) === $rateId) {
                    $matched = $r;
                    break;
                }
            }
        }

        $norm = strtolower($kurirOption);
        if (!$matched && preg_match('/^zone_(\d+)$/', $norm, $m) && isset($rates[(int) $m[1]])) {
            $matched = $rates[(int) $m[1]];
        }

        if (!$matched) {
            if ($norm === 'ambil_sendiri') {
                foreach ($rates as $r) {
                    if ((float) ($r['tarif'] ?? 0) == 0 || str_contains(strtolower($r['wilayah'] ?? ''), 'ambil')) {
                        $matched = $r;
                        break;
                    }
                }
                if (!$matched) {
                    return ['tarif' => 0.0, 'layanan' => 'Ambil Sendiri di Unit TEFA', 'estimasi' => 'Langsung ambil di kampus'];
                }
            } elseif (str_contains($norm, 'jne') || str_contains($norm, 'luar')) {
                foreach ($rates as $r) {
                    $w = strtolower($r['wilayah'] ?? '');
                    if (str_contains($w, 'jne') || str_contains($w, 'sicepat') || str_contains($w, 'luar')) {
                        $matched = $r;
                        break;
                    }
                }
                if (!$matched) {
                    return ['tarif' => 25000.0, 'layanan' => 'Luar Kota / Ekspedisi (JNE/SiCepat)', 'estimasi' => '2-3 hari'];
                }
            } else {
                foreach ($rates as $r) {
                    $w = strtolower($r['wilayah'] ?? '');
                    if (str_contains($w, 'bandar lampung') || str_contains($w, 'kurir kampus')) {
                        $matched = $r;
                        break;
                    }
                }
                if (!$matched) {
                    return ['tarif' => 10000.0, 'layanan' => 'Kurir Polinela Express', 'estimasi' => '1-2 hari'];
                }
            }
        }

        return [
            'tarif'    => max(0.0, (float) ($matched['tarif'] ?? 0)),
            'layanan'  => (string) ($matched['wilayah'] ?? 'Reguler Kampus'),
            'estimasi' => (string) ($matched['estimasi'] ?? '1-2 hari'),
        ];
    }
}
