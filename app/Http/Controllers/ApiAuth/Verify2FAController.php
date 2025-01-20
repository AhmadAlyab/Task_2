<?php

namespace App\Http\Controllers\ApiAuth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiresponseTrait;
use App\Http\Traits\CacheTrait;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FA\Google2FA;

class Verify2FAController extends Controller
{
    use ApiresponseTrait;
    use CacheTrait;


    public function verifyTwoFactorAuthentication(Request $request)
    {
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey(Cache::get("secret".$request->email),
                 $request->secret);

        if ($valid) {
            return $this->ApiResponse([
                'massege' => 'login successful',
            ],500);
        } else {
            return $this->ApiResponse([
                'massege' => 'the QR code is not correct',
            ],404);
        }
    }

}
