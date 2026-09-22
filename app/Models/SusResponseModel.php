<?php

namespace App\Models;

use CodeIgniter\Model;

class SusResponseModel extends Model
{
    protected $table            = 'sus_responses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'nama_responden', 'role_responden',
        'q1', 'q2', 'q3', 'q4', 'q5', 'q6', 'q7', 'q8', 'q9', 'q10',
        'total_score', 'kategori', 'feedback', 'created_at'
    ];

    protected $useTimestamps = false;

    public function calculateScore(array $answers): array
    {
        // Ganjil: nilai - 1
        $oddSum = ($answers['q1'] - 1) + ($answers['q3'] - 1) + ($answers['q5'] - 1) + ($answers['q7'] - 1) + ($answers['q9'] - 1);
        // Genap: 5 - nilai
        $evenSum = (5 - $answers['q2']) + (5 - $answers['q4']) + (5 - $answers['q6']) + (5 - $answers['q8']) + (5 - $answers['q10']);

        $totalScore = ($oddSum + $evenSum) * 2.5;

        // Tentukan kategori
        if ($totalScore < 50) {
            $kategori = 'Poor';
        } elseif ($totalScore < 68) {
            $kategori = 'OK';
        } elseif ($totalScore <= 80) {
            $kategori = 'Good';
        } else {
            $kategori = 'Excellent';
        }

        return [
            'total_score' => round($totalScore, 2),
            'kategori'    => $kategori,
        ];
    }

    public function getSummary()
    {
        $all = $this->findAll();
        $count = count($all);
        if ($count === 0) {
            return [
                'total_respondents' => 0,
                'average_score'     => 0,
                'kategori'          => 'Belum ada data',
                'responses'         => [],
            ];
        }

        $sumScore = array_sum(array_column($all, 'total_score'));
        $avg = round($sumScore / $count, 2);

        if ($avg < 50) {
            $kategori = 'Poor';
        } elseif ($avg < 68) {
            $kategori = 'OK';
        } elseif ($avg <= 80) {
            $kategori = 'Good';
        } else {
            $kategori = 'Excellent';
        }

        return [
            'total_respondents' => $count,
            'average_score'     => $avg,
            'kategori'          => $kategori,
            'responses'         => $all,
        ];
    }
}
