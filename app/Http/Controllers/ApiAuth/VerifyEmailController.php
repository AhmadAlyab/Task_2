<?php

namespace App\Http\Controllers\ApiAuth;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiAuth\verifyEmailRequest;
use App\Http\Traits\ApiresponseTrait;
use App\Http\Traits\CacheTrait;
use App\Http\Traits\SendMailTrait;
use App\Http\Traits\UploadfileTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VerifyEmailController extends Controller
{
    use UploadfileTrait;
    use ApiresponseTrait;
    use SendMailTrait;
    use CacheTrait;

    public function verifyEmail(verifyEmailRequest $request)
    {
        // check code in cache storage and get it
        $data = Cache::get('email_verification:'.$request->email);

        if ($data['code'] != $request->verification_code) {
            throw new CustomException("Invalid or expired verification code.");
        }
        // check IP in cache storage
        if ($data['ip'] !== $request->ip()) {
            throw new CustomException("Verification attempt from unauthorized IP.");
        }
        // delete data from cache storage
        Cache::forget('email_verification:'.$request->email);

        // get info user to save it
        $Request = Cache::get('request: '.$request->email);

        // save file in disk
        $profilPhoto = $this->uploadFile($Request);

        // save info in table user
        $user = User::create([
            'full_name' => $Request['full_name'],
            'email' => $Request['email'],
            'number_phone' => $Request['number_phone'],
            'password' => bcrypt($Request['password']),
            'profil_photo' => $Request['image_name'],
        ]);

        // get user
        $user = User::where('email',$request->email)->first();

        // create access token for 10 min
        $token = $user->createToken('access_token', ['*'],now()->addMinutes(10))->plainTextToken;

        Cache::put('token:'.$user->id,$token,now()->addMinutes(10));

        return $this->ApiResponse([
               'message' => 'verification code is successfuly',
               'token'   => $token
            ]);
    }

    public function reSend(Request $request)
    {
        if (Cache::has('email_verification:'.$request->email)) {
            // check code in cache storage and get it
            $data = Cache::get('email_verification:'.$request->email);

            // check how many user request a resend
            if (Cache::has('counter'.$request->email)) {
                // get how many user request a resend
                $counter = Cache::get('counter'.$request->email);
                if ($counter["counter"]==2) {
                    // th user can't request a resend
                    return $this->ApiResponse(['message' => "you can't use resend request"]);
                }else {
                    // add counter to number of requests
                    $this->Cache('counter'.$request->email,["counter" => 2],10);
                }
            } else {
                // create counter to first request a resend
                $this->Cache('counter'.$request->email,["counter" => 1],10);
            }

            // send code in email
            $this->sendMail($request->email,"Email Verification Code","Your verification code is: {$data['code']}");
        }

    }

}
