<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'release_date', 'duration', 'cast', 'genre_id', 'trailer_uri', 'banner_image', 'cover_image', 'standard_image', 'thumbnail_image', 'movie_file', 'type'
    ];

    // protected $appends = ['']

    public  function getTrailerUriAttribute($value) {
        return $value ? $value . '?autoplay=1&mute=1&controls=0&loop=1' : null;
    }

    public function getBannerImageAttribute($value) {
        return $value ? config('app.url') . '/public' . $value : null;
    }

    public function getCoverImageAttribute($value) {
        return $value ? config('app.url') . '/public' . $value : null;
    }

    public function getStandardImageAttribute($value) {
        return $value ? config('app.url') . '/public' .  $value : null;
    }

    public function getThumbnailImageAttribute($value) {
        return $value ? config('app.url') . '/public' .  $value : null;
    }

    public function getMovieFileAttribute($value) {
        return $value ? config('app.url') . '/public' .  $value : null;
    }

    public function genre() {
        return $this->belongsTo(Genre::class);
    }

    public function categories() {
        return $this->belongsToMany(Category::class, 'movie_category', 'movie_id', 'category_id');
    }

    public function relatedMovies() {
        return $this->belongsToMany(Movie::class, 'related_movies', 'movie_id', 'related_movie_id');
    }

    public function seasons() {
        return $this->hasMany(Season::class, 'series_id');
    }
}