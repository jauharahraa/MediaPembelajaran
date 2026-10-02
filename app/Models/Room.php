<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Room extends Model
{
    protected $fillable = ['teacher_id', 'name', 'subject', 'topic', 'class_level', 'description', 'code', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function teacher()   { return $this->belongsTo(User::class, 'teacher_id'); }
    public function members()   { return $this->belongsToMany(User::class, 'room_members')->withPivot('joined_at')->withTimestamps(); }
    public function materials() { return $this->hasMany(Material::class); }
    public function videos()    { return $this->hasMany(Video::class); }
    public function cases()     { return $this->hasMany(CaseStudy::class)->orderBy('sort_order'); }

    public function hasMember(User $user): bool
    {
        return $this->members()->where('users.id', $user->id)->exists();
    }

    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}