<?php

use App\Http\Controllers\ApiAuth\ForgetPasswordController;
use App\Http\Controllers\ApiAuth\LoginController;
use App\Http\Controllers\ApiAuth\LogoutController;
use App\Http\Controllers\ApiAuth\RefreshTokenController;
use App\Http\Controllers\ApiAuth\RegisterController;
use App\Http\Controllers\ApiAuth\Verify2FAController;
use App\Http\Controllers\ApiAuth\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LogoutController::class, 'logout']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/verify-email', [VerifyEmailController::class, 'verifyEmail']);
Route::post('/refreshToken', [RefreshTokenController::class, 'refreshToken']);
Route::post('/reSend', [VerifyEmailController::class, 'reSend']);
Route::post('/forgetPassword', [ForgetPasswordController::class, 'forgetPassword']);
Route::post('/2fa', [Verify2FAController::class, 'verifyTwoFactorAuthentication']);
