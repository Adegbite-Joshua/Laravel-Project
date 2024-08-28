<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class AdminFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'first_name'=> $this->faker->firstName(),
            'last_name'=> $this->faker->lastName(),
            'email'=> $this->faker->unique()->safeEmail(),
            'image'=> $this->faker->imageUrl(),
            'telephone'=> $this->faker->phoneNumber(),
            'used_google_oauth'=> $this->faker->boolean(),
            'gender'=> $this->faker->randomElement(['male','female']),
            'city'=> $this->faker->city(),
            'zip_code'=> $this->faker->postcode(),
            'address'=> $this->faker->address()
        ];
    }
}
