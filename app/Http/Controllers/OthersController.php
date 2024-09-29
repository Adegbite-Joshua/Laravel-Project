<?php

namespace App\Http\Controllers;

use App\Mail\CustomEmail;
use Illuminate\Http\Request;
use Mail;

class OthersController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function contact_us(Request $request)
    {
        $request->validate([
            'name' => 'string|required',
            'email' => 'string|required|email',
            'message' => 'string|required',
            'telephone' => 'string|nullable',
        ]);

        $emailBody = '
        <div style="font-family: Arial, sans-serif; color: #333;">
            <h2 style="color: #4CAF50;">New Contact Form Message</h2>

            <p><strong>Name:</strong> ' . $request->name . '</p>
            <p><strong>Email:</strong> ' . $request->email . '</p>
            <p><strong>Telephone:</strong> ' . (!empty($request->telephone) ? $request->telephone : 'N/A') . '</p>
            <p><strong>Message:</strong></p>
            <p>' . nl2br(htmlspecialchars($request->message)) . '</p>

            <hr>
            <p>This message was sent from your website\'s contact form.</p>
        </div>
    ';

        $details = [
            'subject' => 'New Message For The Admin',
            'body' => $emailBody,
        ];

        Mail::to('admin@example.com')->send(new CustomEmail($details));

        return response()->json(['message' => 'Your message has been sent successfully!'], 200);
    }

}
