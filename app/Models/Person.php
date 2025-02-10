<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'also_known_as', 'biography', 'birthday', 'deathday',
        'gender', 'homepage', 'imdb_id', 'known_for_department',
        'place_of_birth', 'popularity', 'profile_path', 'adult',
    ];

    protected $casts = [
        'also_known_as' => 'array',
    ];

    public function credits()
    {
        return $this->hasMany(Credit::class);
    }
}