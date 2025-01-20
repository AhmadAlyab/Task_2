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
        // create secret from Google2FA
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey(Cache::get("secret".$request->email),
                 $request->secret);

        if ($valid) {
            // get user from cache memory
            $user = Cache::get('user:'.$request->email);
            // create access token for 10 min
            $token = $user->createToken('access_token', ['*'], now()->addMinutes(10))->plainTextToken;

            $this->Cache('token:'.$user->id,$token,10);

            return $this->ApiResponse([
                'massege' => 'login successful',
                'access_token' => $token,
                'user' => $user,
            ],500);

        } else {
            return $this->ApiResponse([
                'massege' => 'the code is not correct',
            ],404);
        }
    }

}
