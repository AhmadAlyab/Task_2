<?php

namespace App\Http\Controllers\ApiAuth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiresponseTrait;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    use ApiresponseTrait;
    public function logout(Request $request)
    {
        // delete tokens for user
        $request->user()->tokens()->delete();
        return $this->ApiResponse(['message'=> 'you are logout']);
    }

}
