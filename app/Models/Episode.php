<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id', 'title', 'description', 'duration', 'release_date', 'episode_number', 'banner_image', 'cover_image', 'standard_image', 'thumbnail_image', 'movie_file'
    ];

    public function season() {
        return $this->belongsTo(Season::class);
    }
}