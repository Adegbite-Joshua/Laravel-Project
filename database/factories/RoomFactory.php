<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'overview' => $this->faker->sentence,
            'price' => $this->faker->numberBetween(100, 1000),
            'service_fee' => $this->faker->numberBetween(10, 100),
            'booking_status' => $this->faker->randomElement(['booked', 'available']),
            'clean_status' => $this->faker->randomElement(['clean', 'dirty']),
            'star_rating' => $this->faker->numberBetween(1, 5),
            'type' => $this->faker->randomElement(['Single', 'Double', 'Suite', 'VIP']),
            'next_free' => $this->faker->date('Y-m-d'),
            'facilities' => $this->faker->words(3, true),
            'category' => $this->faker->word,
            'occupied' => $this->faker->boolean,
            'check_in_date' => $this->faker->date('Y-m-d'),
            'check_out_date' => $this->faker->date('Y-m-d'),
        ];
    }
}

