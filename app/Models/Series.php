<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'cover_image'];

    public function getCoverImageAttribute($value) {
        return $value ? config('app.url') . $value : null;
    }

    public function seasons() {
        return $this->hasMany(Season::class);
    }
}