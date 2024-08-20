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
}
