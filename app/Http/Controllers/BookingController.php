<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Traits\HttpResponses;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Unicodeveloper\Paystack\Facades\Paystack;

class BookingController extends Controller
{
    use HttpResponses;

    public function index(Request $request)
    {
        $start = $request->query('start', 0);
        $end = $request->query('end', null);

        if ($end !== null) {
            $limit = $end - $start;
            $bookings = Booking::skip($start)
                ->take($limit)
                ->get();
        } else {
            $bookings = Booking::all();
        }

        return response()->json($bookings);
    }

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

    public function show(Booking $booking)
    {
        if (Auth::user() && Auth::user()->id != $booking->user_id) {
            return $this->error(null, 'You are not allowed to perform this action', 403);
        }

        response()->json($booking);
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            // 'room_id' => 'integer',
            // 'amount' => 'integer',
            // 'user_id' => 'integer',
            // 'date_in' => 'date',
            // 'date_out' => 'date',
            'status' => 'string',
        ]);

        $booking->update($validated);

        return response()->json([
            'message' => 'Booking updated successfully',
            'booking' => $booking,
        ]);
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted successfully',
        ]);
    }

    public function initializePayment(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'amount' => 'required|numeric',
            'roomId' => 'required|integer',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date',
        ]);

        $room = Room::find($request->roomId);

        if (!$room) {
            return response()->json(['error' => 'Room not found'], 404);
        }

        $checkInDate = Carbon::parse($request->check_in_date, );
        $checkOutDate = Carbon::parse($request->check_out_date);

        $bookings = Booking::where('room_id', $request->roomId)->get();

        foreach ($bookings as $booking) {
            $existingCheckIn = Carbon::parse($booking->check_in_date);
            $existingCheckOut = Carbon::parse($booking->check_out_date);

            if (
                (($checkInDate->between($existingCheckIn, $existingCheckOut)) ||
                    ($checkOutDate->between($existingCheckIn, $existingCheckOut)) ||
                    ($checkInDate <= $existingCheckIn && $checkOutDate >= $existingCheckOut)) &&
                ($booking->status == 'paid')
            ) {
                return response()->json(['error' => 'Room is not available for the selected dates'], 409);
            }
        }

        $paymentData = [
            'amount' => $request->amount * 100,
            'email' => $request->email,
            'reference' => Paystack::genTranxRef(),
            'callback_url' => route('payment.verify'),
        ];

        $transaction = Booking::create([
            'user_id' => auth()->user()->id,
            'room' => $room->name,
            'room_id' => $request->roomId,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'amount' => $request->amount,
            'ref' => $paymentData['reference'],
            'status' => 'pending',
        ]);

        $authorizationUrl = Paystack::getAuthorizationUrl($paymentData)->url;

        if ($authorizationUrl) {
            return response()->json(['authorization_url' => $authorizationUrl, 'transaction' => $transaction]);
        } else {
            return response()->json(['error' => 'Payment initialization failed'], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        try {
            $transactionRef = $request->query('reference');

            $paymentDetails = Paystack::getPaymentData();

            if ($paymentDetails['data']['status'] === 'success') {
                $transaction = Booking::where('ref', $transactionRef)->first();
                // Booking::update('ref', $transactionRef)
                $transaction->update([
                    'status' => 'paid',
                ]);
                return response()->json(['success' => true, 'data' => $paymentDetails, 'transaction' => $transaction]);
            } else {
                return response()->json(['error' => 'Payment verification failed'], 500);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Payment verification failed'], 500);
        }
    }

    public function getBooking(Request $request)
    {
        try {
            $start = $request->input('start', 0);
            $limit = $request->input('limit', 100);

            $bookings = Booking::all()->skip($start)->take($limit);

            return $this->success($bookings, "Successful");
        } catch (\Throwable $th) {
            return $this->error(null, "Something went wrong", 500);
        }
    }

    public function getGuestsBooking(Request $request)
    {
        try {
            // Retrieve pagination parameters from the request (if provided)
            $start = $request->input('start', 0); // Default to 0 if not provided
            $limit = $request->input('limit', 100); // Default to 100 if not provided

            // Fetch bookings with 'success' status and apply start and limit for range
            $bookings = Booking::all()->skip($start)->take($limit);

            return $this->success($bookings, "Successful");
        } catch (\Throwable $th) {
            return $this->error(null, "Something went wrong", 500);
        }
    }

    public function adminCreateBooking(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'amount' => 'required|numeric',
            'roomId' => 'required|integer',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date',
        ]);

        $room = Room::find($request->roomId);
        $user = User::where('email', $request->email)->first();

        if (!$room) {
            return response()->json(['error' => 'Room not found'], 404);
        }
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $checkInDate = Carbon::parse($request->check_in_date, );
        $checkOutDate = Carbon::parse($request->check_out_date);

        $bookings = Booking::where('room_id', $request->roomId)->get();

        foreach ($bookings as $booking) {
            $existingCheckIn = Carbon::parse($booking->check_in_date);
            $existingCheckOut = Carbon::parse($booking->check_out_date);

            if (
                (($checkInDate->between($existingCheckIn, $existingCheckOut)) ||
                    ($checkOutDate->between($existingCheckIn, $existingCheckOut)) ||
                    ($checkInDate <= $existingCheckIn && $checkOutDate >= $existingCheckOut)) &&
                ($booking->status == 'paid')
            ) {
                return response()->json(['error' => 'Room is not available for the selected dates'], 409);
            }
        }

        $transaction = Booking::create([
            'user_id' => $user->id,
            'room' => $room->name,
            'room_id' => $request->roomId,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'amount' => $request->amount,
            'ref' => Paystack::genTranxRef(),
            'status' => 'paid',
        ]);

        return response()->json(['transaction' => $transaction]);
    }
}
