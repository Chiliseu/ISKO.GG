<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_slug',
        'summary',
        'image_url',
    ];
}
