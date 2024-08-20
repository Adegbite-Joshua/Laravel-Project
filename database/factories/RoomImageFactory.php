<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomImageFactory extends Factory
{
    protected $model = RoomImage::class;

    public function definition()
    {
        return [
            'room_id' => Room::factory(),
            'image' => $this->faker->imageUrl(640, 480, 'rooms', true),
        ];
    }
}
