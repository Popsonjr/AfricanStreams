<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'cover_image', 'banner_image'];

    public function getBannerImageAttribute($value) {
        return $value ? config('app.url') . '/public' . $value : null;
    }
    
    public function getCoverImageAttribute($value) {
        return $value ? config('app.url') . '/public' . $value : null;
    }

    public function seasons() {
        return $this->hasMany(Season::class);
    }
}