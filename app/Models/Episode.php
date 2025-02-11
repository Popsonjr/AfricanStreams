<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id', 'episode_number', 'season_number', 'name', 'overview',
        'still_path', 'air_date', 'runtime', 'vote_average', 'vote_count',
        'production_code', 'crew', 'guest_stars',
    ];

    protected $casts = [
        'crew' => 'array',
        'guest_stars' => 'array',
    ];

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function credits()
    {
        return $this->morphMany(Credit::class, 'creditable');
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }
}