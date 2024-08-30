<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminRequest;
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

        if (!Auth::guard('admin')->attempt($request->only('email', 'password'))) {
            return $this->error("", "Credentials do not match", 401);
        }

        $admin = Auth::guard('admin')->user();

        return $this->success("Login successful", [
            "admin" => $admin,
            "token" => $admin->createToken("login token for " . $admin->id)->plainTextToken,
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

    public function user()
    {
        return $this->success(Auth::guard('admin')->user(), null, 200);
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

        $this->success([
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
            'monthlyBookings'=> $monthlyBookings
        ]);
    }
}
