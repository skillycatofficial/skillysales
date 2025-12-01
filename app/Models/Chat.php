<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'customer_id',
        'dealer_id',
        'car_id',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function dealer()
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the last message in the chat
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Get the other participant in the chat
     */
    public function getOtherParticipant($userId)
    {
        if ($this->customer_id == $userId) {
            return $this->dealer;
        }
        return $this->customer;
    }

    /**
     * Get unread message count for a user
     */
    public function unreadCount($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Check if user is a participant in this chat
     */
    public function isParticipant($userId)
    {
        return $this->customer_id == $userId || $this->dealer_id == $userId;
    }
}
