<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAssessment extends Model
{
    protected $fillable = [
        'case_id', 
        'user_id', 
        'submission_id', 
        'nilai_identifikasi', 
        'nilai_analisis', 
        'nilai_solusi', 
        'final_score', 
        'comment', 
        'graded_at'
    ];
    
    protected $casts = [
        'graded_at' => 'datetime', 
        'final_score' => 'decimal:2'
    ];

    public function case()       { return $this->belongsTo(CaseStudy::class, 'case_id'); }
    public function student()    { return $this->belongsTo(User::class, 'user_id'); }
    public function submission() { return $this->belongsTo(CaseSubmission::class, 'submission_id'); }
}
