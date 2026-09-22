<?php

namespace App\Models;

class ShippingRateModel
{
    protected $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    /**
     * Mengambil seluruh daftar tarif ongkir yang aktif
     */
    public function getRates(): array
    {
        $json = $this->settingModel->getByKey('shipping_rates', '[]');
        $rates = json_decode($json, true);

        if (empty($rates)) {
            return [
                [
                    'wilayah'  => 'Area Kampus Polinela (Ambil di Tefa / Antar Gedung)',
                    'tarif'    => 0,
                    'estimasi' => 'Hari yang sama / Langsung',
                ],
                [
                    'wilayah'  => 'Bandar Lampung (Kurir Kampus / Sameday)',
                    'tarif'    => 10000,
                    'estimasi' => '1 hari kerja',
                ],
                [
                    'wilayah'  => 'Luar Kota / Luar Provinsi (JNE / J&T / Pos)',
                    'tarif'    => 25000,
                    'estimasi' => '2-4 hari kerja',
                ],
            ];
        }

        return $rates;
    }

    /**
     * Hitung ongkir berdasarkan opsi dan berat
     */
    public function calculate(int $rateIndex, int $beratGram = 1000): array
    {
        $rates = $this->getRates();
        if (!isset($rates[$rateIndex])) {
            return [
                'tarif'    => 12000,
                'wilayah'  => 'Kurir Reguler',
                'estimasi' => '2-3 hari',
            ];
        }

        $baseRate = (float)$rates[$rateIndex]['tarif'];
        // Pembulatan per 1000 gram jika di luar kampus
        $kg = max(1, ceil($beratGram / 1000));
        $totalTarif = ($baseRate == 0) ? 0 : ($baseRate * $kg);

        return [
            'tarif'    => $totalTarif,
            'wilayah'  => $rates[$rateIndex]['wilayah'],
            'estimasi' => $rates[$rateIndex]['estimasi'],
        ];
    }
}
