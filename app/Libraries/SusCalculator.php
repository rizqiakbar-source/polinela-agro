<?php

namespace App\Libraries;

class SusCalculator
{
    /**
     * Hitung skor SUS (System Usability Scale)
     * Aturan:
     * - Skor pertanyaan ganjil (1, 3, 5, 7, 9): skor - 1
     * - Skor pertanyaan genap (2, 4, 6, 8, 10): 5 - skor
     * - Total skor per responden = (jumlah skor yang dinormalisasi) * 2.5
     */
    public static function calculate(array $q): array
    {
        $oddSum = ($q['q1'] - 1) + ($q['q3'] - 1) + ($q['q5'] - 1) + ($q['q7'] - 1) + ($q['q9'] - 1);
        $evenSum = (5 - $q['q2']) + (5 - $q['q4']) + (5 - $q['q6']) + (5 - $q['q8']) + (5 - $q['q10']);

        $score = ($oddSum + $evenSum) * 2.5;
        $score = round($score, 2);

        if ($score < 50) {
            $grade = 'F';
            $kategori = 'Poor (Buruk)';
            $color = 'danger';
        } elseif ($score < 68) {
            $grade = 'D/C';
            $kategori = 'OK (Cukup)';
            $color = 'warning';
        } elseif ($score <= 80) {
            $grade = 'B';
            $kategori = 'Good (Baik / Di Atas Rata-rata)';
            $color = 'primary';
        } else {
            $grade = 'A';
            $kategori = 'Excellent (Sangat Unggul)';
            $color = 'success';
        }

        return [
            'score'    => $score,
            'grade'    => $grade,
            'kategori' => $kategori,
            'color'    => $color,
        ];
    }
}
