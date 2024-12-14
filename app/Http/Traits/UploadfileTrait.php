<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait UploadfileTrait
{
    public function uploadFile($request)
    {
        Storage::put('public/images/',$request['profil_photo']);
    }
}
