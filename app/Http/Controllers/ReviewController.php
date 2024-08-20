<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reviews = Review::all();
        return $this->success($reviews, null, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'star_rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only('name', 'star_rating', 'review');

        // Handle image upload if provided
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
       
            $data['image'] = $imageUrl;

        $review = Review::create($data);
        return $this->success($review, null, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function show(Review $review)
    {
        return $this->success($review, null, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Review $review)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'star_rating' => 'sometimes|required|integer|min:1|max:5',
            'review' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only('name', 'star_rating', 'review');

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            // Optionally delete the old image if necessary
            if ($review->image) {
                Storage::disk('public')->delete($review->image);
            }
            $path = $request->file('image')->store('reviews', 'public');
            $data['image'] = $path;
        }

        $review->update($data);
        return $this->success($review, null, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function destroy(Review $review)
    {
        // Optionally delete the image from storage
        if ($review->image) {
            Storage::disk('public')->delete($review->image);
        }
        
        $review->delete();
        return $this->success(null, 'Review deleted successfully', 204);
    }
}
