<?php

namespace App\Http\Controllers\ApiAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ApiresponseTrait;
use App\Models\User;
use App\Exceptions\CustomException;
use App\Http\Requests\ApiAuth\LoginRequest;
use App\Http\Traits\CacheTrait;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    use ApiresponseTrait;
    use CacheTrait;

    public function login(Request $request)
    {
        // get user
        $user = User::where('email',$request->identifier)
                      ->orWhere('number_phone',$request->identifier)->first();

        // check user is exists and password is right
        if (!$user || !Hash::check($request->password,$user->password)) {
            throw new CustomException("invaild identifier or password");
        }
        // create QR code to 2FA
        $this->TowFactorAuthentication($user->email);

        // create access token for 10 min
        $token = $user->createToken('access_token', ['*'], now()->addMinutes(10))->plainTextToken;

        $this->Cache('token:'.$user->id,$token,10);

        return $this->ApiResponse([
            'massege' => 'login successful',
            'access_token' => $token,
            'user' => $user,
        ]);

    }

}
