<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Setting;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::where('is_active', true)
            ->orderBy('urutan', 'asc')
            ->get();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $banners,
        ]);
    }

    public function settings()
    {
        $bankAccounts = json_decode(Setting::getByKey('bank_accounts', '[]'), true);
        $shippingRates = json_decode(Setting::getByKey('shipping_rates', '[]'), true);

        return response()->json([
            'success' => true,
            'data'    => [
                'bank_accounts'  => $bankAccounts,
                'shipping_rates' => $shippingRates,
            ],
        ]);
    }
}
