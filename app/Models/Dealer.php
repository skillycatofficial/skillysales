<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dealer extends Model
{
    protected $fillable = [
        'user_id',
        'shop_name',
        'description',
        'address',
        'latitude',
        'longitude',
        'phone',
        'is_verified',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    public function featuredAds()
    {
        return $this->hasMany(FeaturedAd::class);
    }
}
