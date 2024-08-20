<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name', 
        'overview', 
        'price',
        'service_fee',
        'booking_status',
        'clean_status',
        'star_rating',
        'type',
        'next_free',
        'facilities',
        'category',
        'occupied',
        'check_in_date',
        'check_out_date'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

     public function images()
     {
         return $this->hasMany(RoomImage::class);
     }
 
     public function reservations()
     {
         return $this->hasMany(Reservation::class);
     }
}
