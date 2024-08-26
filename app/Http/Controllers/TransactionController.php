<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Unicodeveloper\Paystack\Facades\Paystack;
use Carbon\Carbon;


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
    
        // Check if there is an overlap with existing bookings
        $existingBooking = Room::where('room_id', $request->roomId)
            ->where(function($query) use ($checkInDate, $checkOutDate) {
                $query->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                      ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                      ->orWhere(function ($query) use ($checkInDate, $checkOutDate) {
                          $query->where('check_in_date', '<=', $checkInDate)
                                ->where('check_out_date', '>=', $checkOutDate);
                      });
            });
            // ->exists();
    
        if ($existingBooking) {
            return response()->json(['error' => 'Room is not available for the selected dates'], 409);
        }
    
        // return response()->json(['success' => 'Room is available'], 200);

        $paymentData = [
            'amount' => $request->amount * 100,
            'email' => $request->email,
            'reference' => Paystack::genTranxRef(),
            'callback_url' => route('payment.verify'),
        ];

        $authorizationUrl = Paystack::getAuthorizationUrl($paymentData)->url;


        if ($authorizationUrl) {
            return response()->json(['authorization_url' => $authorizationUrl]);
        } else {
            return response()->json(['error' => 'Payment initialization failed'], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        $transactionRef = $request->query('reference');

        $paymentDetails = Paystack::getPaymentData();

        if ($paymentDetails['status'] === true) {
            return response()->json(['success' => true, 'data' => $paymentDetails['data']]);
        } else {
            return response()->json(['error' => 'Payment verification failed'], 500);
        }
    }

}
