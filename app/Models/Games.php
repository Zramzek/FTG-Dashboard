<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Games extends Model
{
    protected $fillable = [
        'api_id',
        'title',
        'thumbnail',
        'short_description',
        'game_url',
        'genre',
        'platform',
        'publisher',
        'developer',
        'release_date',
        'freetogame_profile_url',
    ];

    protected $casts = [
        'api_id' => 'integer',
    ];
}
