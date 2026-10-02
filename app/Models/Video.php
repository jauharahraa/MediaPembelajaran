<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'room_id', 
        'title', 
        'youtube_url', 
        'youtube_id'
    ];
    public function room() { return $this->belongsTo(Room::class); }
}
