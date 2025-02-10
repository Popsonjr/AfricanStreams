<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_id', 'person_id', 'creditable_id', 'creditable_type',
        'department', 'job', 'character', 'order', 'known_for_department',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function creditable()
    {
        return $this->morphTo();
    }
}