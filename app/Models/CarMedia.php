<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarMedia extends Model
{
    protected $fillable = [
        'car_id',
        'file_path',
        'file_type',
        'sort_order',
    ];

    // Relationships
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get the full URL for the file path
     * Handles both relative and absolute URLs
     */
    public function getFileUrlAttribute()
    {
        $path = $this->file_path;
        
        // If already a full URL, return as is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        
        // If relative path, convert to full URL
        if (str_starts_with($path, '/')) {
            return url($path);
        }
        
        // Otherwise, assume it's a storage path and use Storage::url
        return url(\Illuminate\Support\Facades\Storage::url($path));
    }

    /**
     * Append the file_url accessor to JSON
     */
    protected $appends = ['file_url'];
}
