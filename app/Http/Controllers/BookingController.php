<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    // List all bookings
    public function index()
    {
        $bookings = Booking::all();
        return response()->json($bookings);
    }

    // Store a new booking
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|integer',
            'amount' => 'required|integer',
            'user_id' => 'required|integer',
            'date_in' => 'required|date',
            'date_out' => 'required|date',
        ]);

        $booking = Booking::create($validated);

        return response()->json([
            'message' => 'Booking created successfully',
            'booking' => $booking,
        ], 201); // 201 status code for created resource
    }

    // Show a single booking
    public function show(Booking $booking)
    {
        // $booking = Booking::find($id);

        if ($booking) {
            return response()->json($booking);
        }

        return response()->json([
            'message' => 'Booking not found',
        ], 404); // 404 status code for not found
    }

    // Update an existing booking
    public function update(Request $request, Booking $booking)
    {
        // $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        $validated = $request->validate([
            'room_id' => 'required|integer',
            'amount' => 'required|integer',
            'user_id' => 'required|integer',
            'date_in' => 'required|date',
            'date_out' => 'required|date',
        ]);

        $booking->update($validated);

        return response()->json([
            'message' => 'Booking updated successfully',
            'booking' => $booking,
        ]);
    }

    // Delete a booking
    public function destroy($id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted successfully',
        ]);
    }
}
