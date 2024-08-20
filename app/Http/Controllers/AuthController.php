<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Traits\HttpResponses;
use Auth;
use Cloudinary\Cloudinary;
use Hash;
use Illuminate\Http\Request;

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

        return $this->success([
            "user" => $user,
        ], "Account created successfully");
    }

    public function user() {
        return $this->success(Auth::user(), null, 200);
    }
}
