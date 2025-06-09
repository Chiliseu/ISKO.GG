<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_input',
        'summary',
    ];

    /**
     * Always store user_input in lowercase.
     */
    public function setUserInputAttribute($value)
    {
        $this->attributes['user_input'] = strtolower(trim($value));
    }
}
