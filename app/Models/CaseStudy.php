<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseStudy extends Model
{
    protected $table = 'cases'; // "Case" adalah kata cadangan PHP

    protected $fillable = [
        'room_id', 
        'title', 
        'narrative', 
        'supporting_info', 
        'instructions', 
        'sort_order'
    ];

    public function room()        { return $this->belongsTo(Room::class); }
    public function rubric()      { return $this->hasOne(Rubric::class, 'case_id'); }
    public function submissions() { return $this->hasMany(CaseSubmission::class, 'case_id'); }
    public function assessments() { return $this->hasMany(TeacherAssessment::class, 'case_id'); }

    // Status dihitung dari data, bukan disimpan, supaya selalu sinkron
    public function statusFor(int $studentId): string
    {
        if ($this->assessments()->where('user_id', $studentId)->exists()) {
            return 'Sudah Dinilai';
        }

        return match ($this->submissions()->where('user_id', $studentId)->count()) {
            0       => 'Belum Dikerjakan',
            1       => 'Menunggu Revisi',
            default => 'Menunggu Penilaian Guru',
        };
    }
}