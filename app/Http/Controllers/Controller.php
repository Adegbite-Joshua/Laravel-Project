<?php

namespace App\Http\Controllers;

use Cloudinary\Cloudinary;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary(config('cloudinary.cloud_url'));
    }

    function saveFile($file, $folder='files'){
        if (preg_match('/^data:image\/(\w+);base64,/', $file)) {
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $file);
            $imageData = base64_decode($imageData);
            $tempFilePath = tempnam(sys_get_temp_dir(), 'cloudinary_upload');
            file_put_contents($tempFilePath, $imageData);

            $uploadedFile = $this->cloudinary->uploadApi()->upload($tempFilePath, [
                'folder' => $folder,
            ]);

            unlink($tempFilePath);
            return $uploadedFile['secure_url'];
        } else {
            $uploadedFile = $this->cloudinary->uploadApi()->upload($file, [
                'folder' => $folder,
            ]);

            return $uploadedFile['secure_url'];
        }
    }
}
