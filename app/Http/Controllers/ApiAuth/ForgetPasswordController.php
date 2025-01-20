<?php

namespace App\Http\Controllers\ApiAuth;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiAuth\ForgetPasswordRequest;
use App\Http\Traits\ApiresponseTrait;
use App\Http\Traits\SendMailTrait;
use App\Models\User;
use Illuminate\Http\Request;

class ForgetPasswordController extends Controller
{
    use SendMailTrait;
    use ApiresponseTrait;
    public function forgetPassword(ForgetPasswordRequest $request)
    {
        // get user by email
        $user = User::where('email',$request->email)->first();
        if ($user) {
            // check user is correct
            if ($request->full_name == $user->full_name & $request->email == $user->email &
            $request->number_phone == $user->number_phone ) {
            // create new password and update it
            $password = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);
            $user->update([
                'password' => bcrypt($password),
            ]);
            // send password in email
            $this->sendMail($user->email,"Email New Password","Your Pasword is: $password");
            return $this->ApiResponse([
                'send new password to your email' ,
            ]);
            }
            else{
                throw new CustomException("check again your information");
            }
        }else {
            throw new CustomException("your information is not correct");
        }

    }


}
