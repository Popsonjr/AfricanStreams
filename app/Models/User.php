<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Contracts\Providers\JWT;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'email_verified_at',
        'verification_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return [];
    }

    public function favorites() {
        return $this->hasMany(Favorite::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }

    public function lists()
    {
        return $this->hasMany(ListModel::class);
    }

    public function subscriptions() {
        return $this->hasMany(Subscription::class);
    }

    public function watchHistories()
    {
        return $this->hasMany(WatchHistory::class);
    }

    // Accessor for status (active/inactive based on deleted_at)
    public function getStatusAttribute()
    {
        return is_null($this->deleted_at) ? 'active' : 'inactive';
    }

    // Accessor for subscribed (yes/no based on active subscription)
    public function getSubscribedAttribute()
    {
        return $this->subscriptions()->where('status', 'active')->exists() ? 'yes' : 'no';
    }

    public function lastActivityLog()
    {
        return $this->hasOne(\App\Models\ActivityLog::class)->orderByDesc('activity_date')->orderByDesc('activity_time');
    }
}