<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListItem extends Model
{
    use HasFactory;

    protected $fillable = ['list_id', 'itemable_id', 'itemable_type'];

    public function list()
    {
        return $this->belongsTo(ListModel::class, 'list_id');
    }

    public function itemable()
    {
        return $this->morphTo();
    }
}