<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminRequest;
use App\Mail\CustomEmail;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mail;

class AdminController extends Controller
{
    protected $table = 'admins';
    use HttpResponses;

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => ['required', 'email', 'max:100'],
            'password' => ['required', 'string'],
        ]);

        // Attempt to authenticate the admin
        if (!Auth::guard('admin')->attempt($request->only('email', 'password'))) {
            return $this->error("", "Credentials do not match", 401);
        }

        $admin = Auth::guard('admin')->user();

        // Generate OTP
        $otp = rand(100000, 999999);

        $details = [
            'subject' => 'Your OTP Code for [Hotel Name] Admin Login',
            'body' => '
            <div style="font-family: Arial, sans-serif; color: #333;">
                <h1 style="color: #4CAF50;">Hello, ' . $admin->first_name . '!</h1>
                <p>Your One-Time Password (OTP) for logging into [Hotel Name] admin panel is:</p>

                <div style="text-align: center; margin: 20px 0;">
                    <h2 style="color: #4CAF50;">' . $otp . '</h2>
                </div>

                <p>This OTP will expire in 10 minutes. Please use it to complete your login process.</p>

                <p>If you did not request this login, please contact our support team immediately.</p>

                <p>Warm regards,</p>
                <p>The [Hotel Name] Team</p>

                <p style="margin-top: 20px; font-size: 12px; color: #777;">
                    © ' . date('Y') . ' [Hotel Name]. All rights reserved. | <a href="https://example.com/privacy-policy" style="color: #4CAF50;">Privacy Policy</a>
                </p>
            </div>
        ',
        ];

        // Save OTP and expiration timestamp to the admin record
        $admin->otp = $otp;
        $admin->otp_expires_at = now()->addMinutes(10); // OTP expires in 10 minutes
        $admin->save();

        // Mail::to($admin->email)->send(new CustomEmail($details));

        return $this->success("OTP sent to your email. Please verify.", [
            "admin_id" => $admin->id,
            "message" => "OTP has been sent to your email.",
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $this->validate($request, [
            'email' => ['required', 'email', 'max:100'],
            'otp' => ['required', 'digits:6'],
        ]);

        $admin = Admin::where('email', '=', $request->email)->first();
        // $admin = Admin::find($request->admin_id);

        if (!$admin) {
            return $this->error("", "Admin not found", 404);
        }

        // Check if the OTP is correct and hasn't expired
        if ($admin->otp !== $request->otp || Carbon::parse($admin->otp_expires_at)->isPast()) {
            return $this->error("", "Invalid or expired OTP", 401);
        }

        // Clear the OTP fields after successful verification
        $admin->otp = null;
        $admin->otp_expires_at = null;
        $admin->save();

        // Issue a token
        $token = $admin->createToken("login token for " . $admin->id)->plainTextToken;

        return $this->success([
            "admin" => $admin,
            "token" => $token,
        ], "OTP verified successfully");
    }

    public function logout(Request $request)
    {

    }

    public function register(StoreAdminRequest $request)
    {
        $request->validated($request->all());

        $imageUrl = $this->saveFile($request->image, 'admin_images');

        $request->merge(['password' => Hash::make($request['password']), 'image' => $imageUrl, 'nin_number' => "1234567898"]);

        $admin = Admin::create($request->all());

        return $this->success([
            "admin" => $admin,
        ], "Account created successfully");
    }

    public function user()
    {
        $admin = Auth::guard('admin')->user();
        return $this->success($admin, null, 200);
    }

    public function getMetrics(Request $request)
    {
        $created_today = Booking::whereDate('created_at', Carbon::today())->count();
        $checked_out_today = Booking::where('status', 'check_out')->whereDate('updated_at', Carbon::today())->count();
        $available_rooms = Room::where('occupied', false)->count();
        $all_rooms = Room::count();

        $single_metric = Booking::whereHas('room', function ($query) {
            $query->where('category', 'Single');
        })->count();
        $double_metric = Booking::whereHas('room', function ($query) {
            $query->where('category', 'Double');
        })->count();
        $suite_metric = Booking::whereHas('room', function ($query) {
            $query->where('category', 'Suite');
        })->count();
        $vip_metric = Booking::whereHas('room', function ($query) {
            $query->where('category', 'VIP');
        })->count();

        $currentYear = Carbon::now()->year;

        $bookingsByMonth = Booking::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', $currentYear)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('count', 'month');

        $monthlyBookings = array_fill(1, 12, 0);

        foreach ($bookingsByMonth as $month => $count) {
            $monthlyBookings[$month] = $count;
        }

        return $this->success([
            'check_in' => $created_today,
            'check_out' => $checked_out_today,
            'available_rooms' => $available_rooms,
            'occupied_rooms' => $all_rooms - $available_rooms,
            'room_metrics' => [
                'Single' => $single_metric,
                'Double' => $double_metric,
                'Suite' => $suite_metric,
                'VIP' => $vip_metric,
            ],
            'monthlyBookings' => $monthlyBookings,
        ]);
    }
}
