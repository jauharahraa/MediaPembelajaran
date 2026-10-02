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

    public static function extractYoutubeId(string $url): ?string
{
    $pattern = '~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~';

    return preg_match($pattern, $url, $m) ? $m[1] : null;
}
}
