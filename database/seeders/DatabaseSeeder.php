<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory(10)->create();
        Room::factory()
            ->has(RoomImage::factory()->count(5), 'images')
            ->has(Reservation::factory()->count(3), 'reservations')
            ->count(10)
            ->create();

        Review::factory(20)->create();
        Admin::factory(20)->create();
    }
}
