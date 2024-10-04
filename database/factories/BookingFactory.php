<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'room_id' => Room::factory(),
            'room' => $this->faker->text(),
            'amount' => $this->faker->randomDigit() * 9,
            'ref' => $this->faker->lexify('ref??????'),
            'status' => $this->faker->randomElement(['pending', 'success', 'failed', 'checked_in', 'checked_out']),
            'user_id' => User::factory(),
            'check_in_date' => $this->faker->dateTimeBetween('now', '+1 week'),
            'check_out_date' => $this->faker->dateTimeBetween('+1 week', '+2 weeks'),
        ];     
    }
}
