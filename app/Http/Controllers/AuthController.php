<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Http\Traits\ApiresponseTrait;
use App\Http\Traits\UploadfileTrait;
use App\Mail\Send;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    use ApiresponseTrait;
    use UploadfileTrait;
    public function login(Request $request)
    {
        // vaildate from request
        $validator = Validator::make($request->all(),[
            'identifier' => 'required',
            'password' => 'required|string|min:6',
        ]);
        // return masseges if there error in request
        if ($validator->fails()) {
            return $this->ApiResponse(['errors' => $validator->errors()],422);
        }
        // get user
        $user = User::where('email',$request->identifier)
                      ->orWhere('number_phone',$request->identifier)->first();
        // check user is exists and password is right
        if (!$user || !Hash::check($request->password,$user->password)) {
            throw new CustomException("invaild identifier or password");
        }
        // create access token for 10 min
        $token = $user->createToken('access_token', ['*'], now()->addMinutes(10))->plainTextToken;

        Cache::put('token:'.$user->id,$token,now()->addMinutes(10));

        return $this->ApiResponse([
            'massege' => 'login successful',
            'access_token' => $token,
            'user' => $user,
        ]);

    }

    public function register(Request $request)
    {
        // vaildate from request
        $validator = Validator::make($request->all(),[
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'number_phone' => 'required|string|max:15',
            'password' => 'required|string|min:6',
            'profil_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        // return masseges if there error in request
        if($validator->fails())
        {
            return $this->ApiResponse(['errors' => $validator->errors()],422);
        }

         // make code for verification and cache storage
         $ip = $request->ip();
         $verificationCode = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);

         Cache::put('email_verification:'.$request->email,[
            'code' => $verificationCode,
            'ip'   => $ip,
         ],now()->addMinutes(10));

        // send code in email
        $subject = "Email Verification Code";
        $body = "Your verification code is: $verificationCode";
        Mail::to($request->email)->send(new Send($subject,$body));

        $image = $request->file('profil_photo');
        Cache::put('request: '.$request->email,[
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'number_phone' => $request->number_phone,
            'password'   => $request->password,
            'profil_photo' => file_get_contents($image),
            'image_name' => $image->getClientOriginalName(),
        ],now()->addMinutes(10));
        // sevd email to user
        return $this->ApiResponse(['message' => 'User registered successfully. Please check your email for the verification code.']);

    }

    public function verifyEmail(Request $request)
    {
        // vaildate from request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'verification_code' => 'required|digits:6',
        ]);
        // return masseges if there error in request
        if ($validator->fails()) {
            return $this->ApiResponse(['errors' => $validator->errors()],422);
        }
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

    public function logout(Request $request)
    {
        // delete tokens for user
        $request->user()->tokens()->delete();
        return $this->ApiResponse(['message'=> 'you are logout']);
    }

    public function refreshToken(Request $request)
    {
        // vaildate from request
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
            'id'            => 'required'
        ]);

        // return masseges if there error in request
        if($validator->fails())
        {
            return $this->ApiResponse(['errors' => $validator->errors()],422);
        }

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
