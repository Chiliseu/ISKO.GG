<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'image',
        'rating',
        'platforms',
        'genres',
        'matched_genres',
        'trailer_url',
        'youtube_video_id',
    ];
}
