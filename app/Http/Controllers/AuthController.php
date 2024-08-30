<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Mail\CustomEmail;
use App\Models\User;
use App\Traits\HttpResponses;
use Auth;
use Carbon\Carbon;
use Cloudinary\Cloudinary;
use Hash;
use Illuminate\Http\Request;
use Mail;
use URL;

class AuthController extends Controller
{
    use HttpResponses;

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => ['required', 'email', 'max:100'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error("", "Credentials do not match", 401);
        }

        $user = User::where('email', $request->email)->first();

        return $this->success("Account created successfully", [
            "user" => $user,
            "token" => $user->createToken("login token for " . $user->id)->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {

    }

    public function register(StoreUserRequest $request)
    {
        $request->validated($request->all());

        $imageUrl = null;

        if ($request->image) {
            if (preg_match('/^data:image\/(\w+);base64,/', $request->image)) {
                $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $request->image);
                $imageData = base64_decode($imageData);
                $tempFilePath = tempnam(sys_get_temp_dir(), 'cloudinary_upload');
                file_put_contents($tempFilePath, $imageData);

                $uploadedFile = $this->cloudinary->uploadApi()->upload($tempFilePath, [
                    'folder' => 'user_images',
                ]);

                unlink($tempFilePath);
                $imageUrl = $uploadedFile['secure_url'];
            } else {
                $uploadedFile = $this->cloudinary->uploadApi()->upload($request->image, [
                    'folder' => 'user_images',
                ]);

                $imageUrl = $uploadedFile['secure_url'];
            }
        }

        $request->merge(['password' => Hash::make($request['password']), 'image'=> $imageUrl]);

        $user = User::create($request->all());
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        $details = [
            'title' => 'Welcome to [Hotel Name]!',
            'body' => '
                <div style="font-family: Arial, sans-serif; color: #333;">
                    <h1 style="color: #4CAF50;">Welcome to [Hotel Name]!</h1>
                    <p>Dear [Guest Name],</p>
                    <p>Thank you for signing up at [Hotel Name]! We are excited to have you with us. To complete your registration, please verify your email address by clicking the link below.</p>
                    
                    <div style="text-align: center; margin: 20px 0;">
                        <a href="'.$verificationUrl.'" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Verify Your Email Address</a>
                    </div>
                    
                    <p>Once your email is verified, you\'ll have access to exclusive offers, early bookings, and personalized services tailored just for you.</p>
        
                    <img src="https://example.com/welcome-image.jpg" alt="Welcome to [Hotel Name]" style="width: 100%; height: auto; margin-top: 20px;"/>
        
                    <h2 style="color: #4CAF50;">Your Journey Begins Here</h2>
                    <p>As a valued member of our community, we look forward to providing you with an exceptional experience at [Hotel Name]. Whether for a relaxing vacation or a business trip, we have everything you need to make your stay unforgettable.</p>
        
                    <p style="font-style: italic;">"Travel is the only thing you buy that makes you richer." - Anonymous</p>
        
                    <h3 style="color: #4CAF50;">Explore Our Amenities:</h3>
                    <ul>
                        <li>Luxurious Rooms & Suites</li>
                        <li>Fine Dining & Gourmet Cuisine</li>
                        <li>Spa & Wellness Center</li>
                        <li>State-of-the-Art Fitness Facilities</li>
                        <li>Concierge & Tour Services</li>
                    </ul>
        
                    <p>If you have any questions or need assistance, please do not hesitate to contact us.</p>
        
                    <p>Warm regards,</p>
                    <p>The [Hotel Name] Team</p>
                    
                    <div style="text-align: center; margin-top: 30px;">
                        <a href="https://example.com/book-now" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Book Your Stay Now</a>
                    </div>
        
                    <p style="margin-top: 20px; font-size: 12px; color: #777;">
                        © [Year] [Hotel Name]. All rights reserved. | <a href="https://example.com/privacy-policy" style="color: #4CAF50;">Privacy Policy</a>
                    </p>
                </div>
            ',
        ];
        
        
        Mail::to($user->email)->send(new CustomEmail($details));

        return $this->success([
            "user" => $user,
        ], "Account created successfully");
    }

    public function user() {
        return $this->success(Auth::user(), null, 200);
    }
}
