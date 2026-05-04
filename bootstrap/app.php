<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            if (file_exists(__DIR__.'/../routes/debug.php') && config('app.debug')) {
                require __DIR__.'/../routes/debug.php';
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'session.auth' => \App\Http\Middleware\CheckApiSession::class,
            'refresh.token' => \App\Http\Middleware\RefreshTokenMiddleware::class,
        ]);
        
        // Apply refresh token middleware globally untuk semua routes
        $middleware->append(\App\Http\Middleware\RefreshTokenMiddleware::class);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
