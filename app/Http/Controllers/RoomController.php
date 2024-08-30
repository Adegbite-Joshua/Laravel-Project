<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomImage;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use RoomsImages;

class RoomController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->success(Room::with(['images', 'reservations'])->get(), null, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'overview' => 'required|string',
                'price' => 'required|integer',
                'service_fee' => 'required|integer',
                'booking_status' => 'required|string',
                'clean_status' => 'required|boolean',
                'type' => 'required|string',
                'facilities' => 'required|string',
                'category' => 'required|string',
                'images'=> 'required|array'
            ]);
    
            $images = $request->images;
    
            $room = Room::create($request);
    
            foreach ($images as $image) {
                RoomImage::create([
                    'room_id' => $room['id'],
                    'image' => $image
                ]);
            }
    
            $this->success(null, "Room created successfully", 201);
        } catch (\Throwable $th) {
            $this->error(null, $th->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function show(Room $room)
    {
        $room->load(['images', 'reservations']);

        return $this->success($room, null, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Room $room)
    {
        if (!$room) {
            return response()->json([
                'message' => 'Room not found',
            ], 404);
        }

        $request->validate([
            'name' => 'string',
            'overview' => 'string',
            'price' => 'integer',
            'service_fee' => 'integer',
            'booking_status' => 'string',
            'clean_status' => 'boolean',
            'type' => 'string',
            'facilities' => 'string',
            'category' => 'string',
            'images'=> 'array'
        ]);

        $room->update($request);

        $this->success($room, "Room updated successfully", 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function destroy(Room $room)
    {
        $room->delete();

        $this->success(null, 'Room deleted successfully', 200);
    }
}
