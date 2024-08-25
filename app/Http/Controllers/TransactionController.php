<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
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
        ]);

        $paymentData = [
            'amount' => $request->amount * 100, // Paystack expects the amount in kobo
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

        $paymentDetails = Paystack::verifyPayment($transactionRef);

        if ($paymentDetails['status'] === true) {
            return response()->json(['success' => true, 'data' => $paymentDetails['data']]);
        } else {
            return response()->json(['error' => 'Payment verification failed'], 500);
        }
    }

}
