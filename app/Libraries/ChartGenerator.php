<?php

namespace App\Libraries;

class ChartGenerator
{
    /**
     * Memformat dataset agar siap dikonsumsi oleh Chart.js
     */
    public static function formatLineChart(array $labels, array $data, string $label = 'Penjualan (Rp)'): array
    {
        return [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label'           => $label,
                        'data'            => $data,
                        'borderColor'     => '#2e7d32',
                        'backgroundColor' => 'rgba(46, 125, 50, 0.1)',
                        'borderWidth'     => 2,
                        'fill'            => true,
                        'tension'         => 0.3,
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
            ]
        ];
    }
}
