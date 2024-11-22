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
use Session;

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
    
        $otp = rand(100000, 999999);
        $admin->otp = $otp;
        $admin->otp_expires_at = now()->addMinutes(10); // OTP expires in 10 minutes
        $admin->save();
    
        // Store the admin ID in the session for later use
        Session::put('admin_id', $admin->id);
    
        // Uncomment to send OTP email
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
            'password' => ['required', 'string'],
            'otp' => ['required', 'digits:6'],
        ]);
    
        // Attempt to re-authenticate the admin with the given credentials
        if (!Auth::guard('admin')->attempt($request->only('email', 'password'))) {
            return $this->error("", "Credentials do not match", 401);
        }
    
        $admin = Auth::guard('admin')->user();
    
        // Ensure the admin is found after re-authentication
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
    
    
    public function user()
    {
        // Retrieve the admin ID from the session
        $adminId = Session::get('admin_id');
    
        if (!$adminId) {
            return $this->error("", "Admin not authenticated", 401);
        }
    
        // Get the authenticated admin using the admin guard
        $admin = Auth::guard('admin')->user();
    
        if (!$admin) {
            return $this->error("", "Admin not found", 404);
        }
    
        return $this->success($admin, null, 200);
    }
    

    public function update(Request $request, Admin $admin)
    {
        $this->validate( $request,
            ['first_name'=> ["string", "max:40"],
            'last_name'=> ["string", "max:40"],
            'email'=> ["email", "max:100", "unique:admins"],
            'password' => ["string"],
            'image' => ["string"],
            'used_google_oauth' => ["boolean"],
            'gender' => ["string", "in:male,female"],
            'city'=> ["string", "max:40"],
            'zip_code'=> ["string", "max:40"],
            'address'=> ["string", "max:255"]]
        );
        if ($request->password) {
            $request->merge(['password' => Hash::make($request['password'])]);
        }

        $admin->update($request);

        return response()->json([
            'message' => 'Details updated successfully',
            'details' => $admin,
        ]);
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

        
        $categories = ['single', 'double', 'suite', 'vip'];

        
        $rooms_data = [];

        foreach ($categories as $category) {
            
            $totalRooms = Room::where('category', $category)->count();

            $averagePrice = Room::where('category', $category)->average('price');

            $bookedCount = Room::where('category', $category)
                ->where('booking_status', 'booked') 
                ->count();

                $rooms_data[$category] = [
                'price' => $averagePrice ?: 0, // Default to 0 if no rooms exist
                'booked' => $bookedCount,
                'total_rooms' => $totalRooms
            ];
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
            'rooms' => $rooms_data
        ]);
    }
}
