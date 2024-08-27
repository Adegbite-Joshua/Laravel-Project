<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Unicodeveloper\Paystack\Facades\Paystack;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
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

        $checkInDate = Carbon::parse($request->check_in_date);
        $checkOutDate = Carbon::parse($request->check_out_date);

        $bookings = Booking::where('room_id', $request->roomId)->get();

        foreach ($bookings as $booking) {
            $existingCheckIn = Carbon::parse($booking->check_in_date);
            $existingCheckOut = Carbon::parse($booking->check_out_date);

            if (
                ($checkInDate->between($existingCheckIn, $existingCheckOut)) ||
                ($checkOutDate->between($existingCheckIn, $existingCheckOut)) ||
                ($checkInDate <= $existingCheckIn && $checkOutDate >= $existingCheckOut)
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
                // $transaction->update([
                //    'status' => 'paid'
                // ]);
                return response()->json(['success' => true, 'data' => $paymentDetails, 'transaction' => $transaction]);
            } else {
                return response()->json(['error' => 'Payment verification failed'], 500);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Payment verification failed'], 500);
        }
    }

}
