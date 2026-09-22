<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Libraries\SusCalculator;

class SusCalculatorTest extends TestCase
{
    public function testMaxScoreCalculation(): void
    {
        // Jawaban ideal: ganjil bernilai 5, genap bernilai 1
        $responses = [
            'q1' => 5, 'q2' => 1, 'q3' => 5, 'q4' => 1, 'q5' => 5,
            'q6' => 1, 'q7' => 5, 'q8' => 1, 'q9' => 5, 'q10' => 1,
        ];

        $result = SusCalculator::calculate($responses);

        $this->assertEquals(100.0, $result['score']);
        $this->assertEquals('A', $result['grade']);
        $this->assertEquals('Excellent (Sangat Unggul)', $result['kategori']);
    }

    public function testNeutralScoreCalculation(): void
    {
        // Jawaban netral: semua bernilai 3
        $responses = [
            'q1' => 3, 'q2' => 3, 'q3' => 3, 'q4' => 3, 'q5' => 3,
            'q6' => 3, 'q7' => 3, 'q8' => 3, 'q9' => 3, 'q10' => 3,
        ];

        $result = SusCalculator::calculate($responses);

        $this->assertEquals(50.0, $result['score']);
        $this->assertEquals('D/C', $result['grade']);
    }

    public function testMinimumScoreCalculation(): void
    {
        // Jawaban paling rendah: ganjil 1, genap 5
        $responses = [
            'q1' => 1, 'q2' => 5, 'q3' => 1, 'q4' => 5, 'q5' => 1,
            'q6' => 5, 'q7' => 1, 'q8' => 5, 'q9' => 1, 'q10' => 5,
        ];

        $result = SusCalculator::calculate($responses);

        $this->assertEquals(0.0, $result['score']);
        $this->assertEquals('F', $result['grade']);
        $this->assertEquals('Poor (Buruk)', $result['kategori']);
    }
}
