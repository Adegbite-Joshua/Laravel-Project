<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'star_rating' => $this->faker->numberBetween(0, 5),
            'review' => $this->faker->sentence(),
            'image' => $this->faker->imageUrl,
        ];
    }
}
