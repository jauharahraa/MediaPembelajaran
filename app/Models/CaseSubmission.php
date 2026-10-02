<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseSubmission extends Model
{
    protected $fillable = [
        'case_id',
        'user_id',
        'attempt_number',
        'jawaban_identifikasi',
        'jawaban_analisis',
        'jawaban_solusi',
        'submitted_at',
    ];

    protected $casts = ['submitted_at' => 'datetime'];

    public function case()     { return $this->belongsTo(CaseStudy::class, 'case_id'); }
    public function student()  { return $this->belongsTo(User::class, 'user_id'); }
    public function feedback() { return $this->hasOne(SubmissionFeedback::class, 'submission_id'); }
    public function assessment() { return $this->hasOne(TeacherAssessment::class, 'submission_id'); }
}
