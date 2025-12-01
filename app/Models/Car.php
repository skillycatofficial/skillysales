<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'dealer_id',
        'title',
        'brand',
        'model',
        'year',
        'price',
        'mileage',
        'condition',
        'transmission',
        'fuel_type',
        'color',
        'vin',
        'description',
        'is_featured',
        'is_sold',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_sold' => 'boolean',
    ];

    // Relationships
    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    public function media()
    {
        return $this->hasMany(CarMedia::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function featuredAd()
    {
        return $this->hasOne(FeaturedAd::class);
    }
}
