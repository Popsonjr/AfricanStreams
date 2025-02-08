<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'author', 'author_details', 'content', 'iso_639_1', 'reviewable_id', 'reviewable_type', 'url'
    ];

    protected $casts = [
        'author_details' => 'array'
    ];

    public function reviewable() {
        return $this->morphTo();
    }
}