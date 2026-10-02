<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionFeedback extends Model
{
    protected $table = 'submission_feedbacks'; // "feedback" tidak punya bentuk jamak di Laravel

    protected $fillable = [
        'submission_id',
        'status',
        'feedback_identifikasi',
        'feedback_analisis',
        'feedback_solusi',
        'error_message',
        'attempts_count',
    ];

    public function submission() { return $this->belongsTo(CaseSubmission::class, 'submission_id'); }
}