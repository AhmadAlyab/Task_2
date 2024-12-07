<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;

trait UploadfileTrait
{
    public function uploadFile(Request $request)
    {
        $profilePhotoPath = null;
        if ($request->hasFile('profil_photo')) {
            $profilePhotoPath = $request->file('profil_photo')->store('profile_photos', 'public');
        }
    }
}
