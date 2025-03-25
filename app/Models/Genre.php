<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'status',
    ];

    public function movies() {
        return $this->belongsToMany(Movie::class, 'genre_movie');
    }

    public function tvShows() {
        return $this->belongsToMany(TvShow::class, 'genre_tv');
    }
}