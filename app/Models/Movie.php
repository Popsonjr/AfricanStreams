<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'overview', 'poster_path', 'backdrop_path', 'release_date',
        'vote_average', 'vote_count', 'adult', 'original_language', 'original_title',
        'runtime', 'status', 'production_companies', 'production_countries',
        'tagline', 'budget', 'revenue', 'homepage', 'belongs_to_collection',
        'spoken_languages', 'imdb_id', 'popularity', 'video', 'file_path', 'trailer_url', 'user_id',
    ];

    protected $casts = [
        'production_companies' => 'array',
        'production_countries' => 'array',
        'belongs_to_collection' => 'array',
        'spoken_languages' => 'array',
    ];

    // public  function getTrailerUriAttribute($value) {
    //     return $value ? $value . '?autoplay=1&mute=1&controls=0&loop=1' : null;
    // }

    // public function getBannerImageAttribute($value) {
    //     return $value ? config('app.url') . '/public' . $value : null;
    // }


    public function genres() {
        return $this->belongsToMany(Genre::class, 'genre_movie', 'movie_id', 'genre_id');
    }

    public function credits()
    {
        return $this->morphMany(Credit::class, 'creditable');
    }

    public function favorites()
    {
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