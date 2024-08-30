<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Rooms extends Migration
{
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('overview', 200);
            $table->integer('price');
            $table->integer('service_fee');
            $table->string('booking_status', 15);
            $table->string('clean_status', 15)->default('clean');
            $table->tinyInteger('star_rating')->unsigned();
            $table->enum('type', ['Single', 'Double', 'Suite', 'VIP'])->default('Single');
            $table->string('next_free', 15);
            $table->string('facilities', 150);
            $table->string('category', 15);
            $table->boolean('occupied')->default(false);
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rooms');
    }
}
