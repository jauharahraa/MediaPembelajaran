<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'room_id', 
        'title', 
        'content', 
        'image_path'
    ];
    public function room() { return $this->belongsTo(Room::class); }
}