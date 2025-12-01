<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedAd extends Model
{
    protected $fillable = [
        'dealer_id',
        'car_id',
        'start_date',
        'end_date',
        'status',
        'amount_paid',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'amount_paid' => 'decimal:2',
    ];

    // Relationships
    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
