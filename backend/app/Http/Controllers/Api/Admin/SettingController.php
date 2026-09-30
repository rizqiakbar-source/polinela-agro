<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $settings = Setting::all()->pluck('setting_value', 'setting_key');

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->all();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => is_array($value) ? json_encode($value) : $value]
            );
        }

        if ($request->user()) {
            ActivityLog::create([
                'user_id'   => $request->user()->id,
                'aksi'      => 'Update Pengaturan Sistem',
                'modul'     => 'Pengaturan',
                'ip_address'=> $request->ip(),
                'user_agent'=> $request->userAgent(),
                'deskripsi' => 'Memperbarui konfigurasi sistem & toko',
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Pengaturan sistem berhasil disimpan.',
        ]);
    }
}
