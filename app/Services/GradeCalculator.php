<?php

namespace App\Services;

class GradeCalculator
{
    public static function finalScore(int $identifikasi, int $analisis, int $solusi): float
    {
        return round(($identifikasi + $analisis + $solusi) / 3, 2);
    }
}