<?php

namespace App\Http\Traits;

use App\Mail\Send;
use Illuminate\Support\Facades\Mail;

trait SendMailTrait
{
    public function sendMail($email,$subject,$body)
    {
        Mail::to($email)->send(new Send($subject,$body));
    }
}
