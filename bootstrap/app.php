<?php

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->priority([
           'api' => [
                \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
                'throttle:api',
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
           ],
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->withSchedule(function(Schedule $schedule) {
        $schedule->call(function () {
            PersonalAccessToken::where('created_at', '<', Carbon::now()->subDays(3))->delete();
        })->daily();
    })
    ->create();

