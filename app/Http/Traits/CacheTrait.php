<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FA\Google2FA;
use App\Mail\Send;
use Illuminate\Support\Facades\Mail;



trait CacheTrait
{
    public function Cache($header,$data,$time)
    {
        Cache::put($header,$data,now()->addMinutes($time));

    }

    public function TowFactorAuthentication($email)
    {
        $google2fa = new Google2FA();

        $secret = $google2fa->generateSecretKey();

        Cache::put("secret".$email,$secret,now()->addMinutes(10));

        Mail::to($email)->send(new Send("QR CODE","the QR code is".$secret));

    }

}
