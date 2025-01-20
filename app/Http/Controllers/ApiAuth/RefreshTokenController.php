<?php

namespace App\Http\Controllers\ApiAuth;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiAuth\refreshTokenRequest;
use App\Http\Traits\ApiresponseTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RefreshTokenController extends Controller
{
    use ApiresponseTrait;
    public function refreshToken(refreshTokenRequest $request)
    {
        // get user
        $user = User::where('id',$request->id)->first();
        // check token is right
        $cacheToken = Cache::get('token:'.$user->id);
        if($cacheToken !== $request->refresh_token)
        {
            throw new CustomException("invaild Token");
        }
        // create access token for 20 min
        $Token = $user->createToken('access_token', ['*'], now()->addMinutes(20))->plainTextToken;

        return $this->ApiResponse([
            'access_token' => $Token,
            'token_type' => 'Bearer',
            'expires_in' => 1200,
        ]);
    }

}
