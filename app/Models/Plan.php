<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration_months',
        'benefits',
        'amount',
        'interval',
        'active',
        'paystack_plan_code',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}