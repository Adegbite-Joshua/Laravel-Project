<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = ['room_id', 'check_in_date', 'check_out_date'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}

