<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class OngkirController extends Controller
{
    public function index(Request $request)
    {
        $setting = Setting::where('setting_key', 'shipping_rates')->first();
        $rates = $setting ? json_decode($setting->setting_value, true) : null;

        if (!$rates || !is_array($rates)) {
            $rates = [
                ['id' => '1', 'wilayah' => 'Dalam Kampus Polinela (Ambil di TEFA / Free Delivery Gedung)', 'tarif' => 0, 'estimasi' => '1 Jam / Langsung'],
                ['id' => '2', 'wilayah' => 'Kecamatan Rajabasa & Sekitar Kampus', 'tarif' => 5000, 'estimasi' => '1 - 2 Jam'],
                ['id' => '3', 'wilayah' => 'Kota Bandar Lampung (Kurir Kampus / Ojol)', 'tarif' => 12000, 'estimasi' => 'Hari yang sama (Same Day)'],
                ['id' => '4', 'wilayah' => 'Luar Kota Lampung (JNE / J&T / Pos Express)', 'tarif' => 25000, 'estimasi' => '2 - 3 Hari Kerja'],
            ];
        }

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $rates,
        ]);
    }

    public function store(Request $request)
    {
        $rates = $request->input('rates', []);

        Setting::updateOrCreate(
            ['setting_key' => 'shipping_rates'],
            ['setting_value' => json_encode($rates)]
        );

        ActivityLog::create([
            'user_id'   => $request->user()->id,
            'aksi'      => 'Update Tarif Ongkir',
            'modul'     => 'Pengaturan Ongkir',
            'ip_address'=> $request->ip(),
            'user_agent'=> $request->userAgent(),
            'deskripsi' => 'Memperbarui tarif ongkos kirim pengiriman perkebunan kampus',
        ]);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Tarif ongkir pengiriman berhasil diperbarui.',
            'data'    => $rates,
        ]);
    }
}
