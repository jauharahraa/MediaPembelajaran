<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rubric extends Model
{
    protected $fillable = [
        'case_id',
        'identifikasi_indikator', 'identifikasi_kriteria',
        'analisis_indikator', 'analisis_kriteria',
        'solusi_indikator', 'solusi_kriteria',
    ];
    public function case() { return $this->belongsTo(CaseStudy::class, 'case_id'); }
}
