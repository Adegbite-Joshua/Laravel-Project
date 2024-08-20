<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition()
    {
        return [
            'room_id' => Room::factory(), // Create a room and use its ID
            'check_in_date' => $this->faker->date('Y-m-d'),
            'check_out_date' => $this->faker->date('Y-m-d'),
        ];
    }
}
