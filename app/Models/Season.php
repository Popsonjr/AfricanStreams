<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'tv_show_id', 'season_number', 'name', 'overview', 'poster_path',
        'air_date', 'episode_count', 'vote_average', '_id',
    ];

    public function tvShow() {
        return $this->belongsTo(TvShow::class);
    }

    public function episodes() {
        return $this->hasMany(Episode::class);
    }

    public function credits() {
        return $this->morphMany(Credit::class, 'creditable');
    }
}