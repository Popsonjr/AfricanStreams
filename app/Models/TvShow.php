<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TvShow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'overview', 'poster_path', 'backdrop_path', 'first_air_date',
        'last_air_date', 'vote_average', 'vote_count', 'adult',
        'original_language', 'original_name', 'number_of_seasons',
        'number_of_episodes', 'status', 'type', 'tagline', 'homepage',
        'in_production', 'created_by', 'episode_run_time', 'languages',
        'networks', 'origin_country', 'production_companies',
        'production_countries', 'spoken_languages', 'last_episode_to_air',
        'next_episode_to_air', 'popularity',
    ];

    protected $casts = [
        'created_by' => 'array',
        'episode_run_time' => 'array',
        'languages' => 'array',
        'networks' => 'array',
        'origin_country' => 'array',
        'production_companies' => 'array',
        'production_countries' => 'array',
        'spoken_languages' => 'array',
        'last_episode_to_air' => 'array',
        'next_episode_to_air' => 'array',
    ];

    public function genres() {
        return $this->belongsToMany(Genre::class, 'genre_tv');
    }

    public function seasons() {
        return $this->hasMany(Season::class);
    }

    public function credits() {
        return $this->morphMany(Credit::class, 'creditable');
    }

    public function favorites() {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function watchlists()
    {
        return $this->morphMany(Watchlist::class, 'watchable');
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
}