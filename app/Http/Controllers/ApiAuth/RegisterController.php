<?php

namespace App\Http\Controllers\ApiAuth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiresponseTrait;
use App\Http\Requests\ApiAuth\RegisterRequest;
use App\Http\Traits\CacheTrait;
use App\Http\Traits\SendMailTrait;

class RegisterController extends Controller
{
    use ApiresponseTrait;
    use SendMailTrait;
    use CacheTrait;

    public function register(RegisterRequest $request)
    {
        // make code for verification and cache storage
        $ip = $request->ip();
        $verificationCode = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);

        $this->Cache('email_verification:'.$request->email,[
            'code' => $verificationCode,
            'ip'   => $ip,
        ],10);

        // send code in email
        $this->sendMail($request->email,"Email Verification Code","Your verification code is: $verificationCode");

        $image = $request->file('profil_photo');
        $this->Cache('request: '.$request->email,[
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'number_phone' => $request->number_phone,
            'password'   => $request->password,
            'profil_photo' => file_get_contents($image),
            'image_name' => $image->getClientOriginalName(),
        ],10);

        // sevd email to user
        return $this->ApiResponse(['message' => 'User registered successfully. Please check your email for the verification code.','code' => $verificationCode]);

    }
}
